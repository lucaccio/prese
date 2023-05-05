<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Residui
 *
 * @author Luca
 */
class Application_Model_Residuo {
     
    protected $_gateway;
    
    public function __construct() {
        $this->_gateway = new Application_Model_UserResiduiMapper();
    }
    
    /**
     * Crea residui per il nuovo utente, in base alla data di assunzione
     * @param type $uid
     * @param type $year
     * @param type $tipo
     */
    public function crea($uid){
        
        if( (null === $uid) || '' === $uid) {
            throw new Exception("<h4>Attenzione, impossibile recuperare l'id utente</h4>");
        }   
        
        $val = array(); 
        $thisYear = date('Y');
                        
        $UM   = new Application_Model_UserMapper();
        $user = $UM->find($uid);
        
        if($user->isAdmin()) {
            return;
        }
        
        $dataAssunzione = $user->getAssunzione();
        
        $date    = new Zend_Date($dataAssunzione);
        $c_year  = $date->toString(Zend_Date::YEAR);
        $c_month = $date->toString(Zend_Date::MONTH_SHORT);
        $c_day   = $date->toString(Zend_Date::DAY_SHORT);
        
        # eseguo l'algo
        if($c_year == $thisYear) {
            
            ($c_day >= 17) ? $t_month = $c_month + 1 : $t_month = $c_month;
             
            if($t_month < 12)  {
                $t_month = 12 - $t_month;
            } elseif($t_month == 12) {
                $t_month = 1;
            } elseif($t_month > 12) {
                $t_month = 0;
            }
                        
            $t_ferie  = $t_month * GG_FERIE_MESE;
            $t_exfest = $t_month * ORE_EXFEST_MESE;
            
            $val['FERIE'] = array(
                'maturato' => $t_ferie,
                'totale'   => $t_ferie
            );
            $val['PERMESSO'] = array(
                'maturato' => 0,
                'totale'   => 0
            );
            $val['EX-FEST'] = array(
                'maturato' => $t_exfest,
                'totale'   => $t_exfest 
            );
        } else {
             $val['FERIE'] = array(
                'maturato' => 0,
                'totale'   => 0
            );
            $val['PERMESSO'] = array(
                'maturato' => 0,
                'totale'   => 0
            );
            $val['EX-FEST'] = array(
                'maturato' => 0,
                'totale'   => 0 
            );
        } # fine algo
                
        $stdTipo = array('FERIE','PERMESSO','EX-FEST');
        $now = date('Y-m-d H:i:s');
        $stdValue = array(
            'user_id'      => $uid,
            'year'         => $thisYear,
            'precedente'   => 0.00,
            'maturato'     => 0.00,
            'goduto'       => 0.00,
            'totale'       => 0.00,
            'date_created' => $now
        );
        $URM = new Application_Model_UserResiduiMapper();
        $URM->delete("user_id = $uid");
        foreach($stdTipo as $k => $tipo) {
            $stdValue['tipo']     = $tipo;
            $stdValue['maturato'] = $val[$tipo]['maturato'];
            $stdValue['totale']   = $val[$tipo]['totale'];
            Prisma_Logger::log($stdValue);
            $URM->insert($stdValue);
        }
    }
    
    
    
    /**
     * 
     * @param type $uid
     * @param type $year
     * @param type $tipo
     */
    public function get($uid, $year, $tipo){
        
    }
    
    
    
    /**
     * Creo il residuo annuale (a inizio anno)
     * @param type $uid
     * @param type $year
     * @param type $type
     */
    public function creaResiduoAnnuale($user, $year, $type) {
        $types = array('FERIE', 'PERMESSO','EX-FEST');
        $prevYear = $year - 1;
        $uid = $user->getId();
        
        $old = $this->getResiduoAnnoPrecedente($uid, $prevYear, $type);
        $residuo = 0;
        if($old) {
            $residuo = $old->totale;
        }
        
        switch($type) {
            case 'FERIE':
                $totale = MAX_FERIE;
                break;
            case 'EX-FEST':
                $totale = $this->calcolaOreExfestAnnuali( $user );
                break;
            case 'PERMESSO':
                $totale = $this->calcolaOrePermessiAnnuali( $user, $year );
                break;
            default:
                $totale = 0;
                break;
        }
        
        $data = array(
            'user_id'      => $uid,
            'tipo'         => $type,
            'year'         => $year,
            'precedente'   => $residuo,
            'maturato'     => $totale,
            'totale'       => $residuo + $totale,
            'date_created' => date('Y-m-d H:i:s')
        );    
        try {
            $where = array(
                'user_id'      => $uid,
                'year'         => $year,
                'tipo'         => $type
            );
            
            $did = $this->_gateway->delete($where);
            
            $lastid = $this->_gateway->insert($data);
            echo "inserimento $lastid ok \n\r";
        } catch(Exception $e) {
            echo $e->getMessage() . "\n\r";
            
        }
        
    }
    
    /**
     * 
     * @param type $uid
     * @param type $year
     * @param type $type
     * @return type
     */
    public function getResiduoAnnoPrecedente($uid, $year, $type) {
        return $this->_gateway->findTypeByUserAndYear($type, $uid, $year);
    }
    
    /**
     *  
     * @param type $user
     * @param type $year
     * @return float
     */
    public function calcolaOrePermessiAnnuali($user, $year) {
        $ore = 0; 
        $dipendenti = TOTALE_DIPENDENTI;
        
        echo $user->getAnagrafe() . "  ( ". $user->getDataAssunzione() . " ) \n\r"; 
        for($m = 1; $m <= 12; $m++) {
            if($m < 10) {
                $m = '0' . $m;
            }
            $format = $year . '-' . $m . '-'  . '01';
            $date = new Zend_Date($format);
            $lastDayofMonth = $date->get(Zend_Date::MONTH_DAYS);
            $date->setDay($lastDayofMonth);
            $anzianita = $user->getAnzianitaServizio($date);
            //echo "mese numero $m anzianita: " . $anzianita . " ";
            if($dipendenti < 15) {
                /*
                if( ($anzianita >= 2) && ($anzianita <= 4) ) {
                    $ore += 2.33;
                } elseif($anzianita > 4) {
                    $ore += 4.66;
                } else {
                    $ore += 0; 
                }
                */
                
            } elseif($dipendenti >= 15) {
                
                
                /*
                if( ($anzianita >= 2) && ($anzianita <= 4) ) {
                    
                    $ore += 5.32;
                } elseif($anzianita > 4) {
                    $ore += 5.32;
                } else {
                    $ore += 0; 
                }
                */
                
                if( ($anzianita >= 2) ) {
            
                    if( $user->getContratto()->isFull() ) {
                        $ore += 5.77;
                    } else {
                        $ore += 5.32;
                    }
                }
                
                
                

            }  else {
                throw new Exception("impossibile determinare il numero di dipendenti per il calcolo dei permessi");
            }
            
             //echo "  ore $ore \n\r";
            
        }
        echo " totale ore $ore \n\n\r";
        return $ore;
    }
    
    /**
     * 
     * @param type $user
     * @return real
     */
    public function calcolaOreExfestAnnuali($user) {
        $ore = 0; 
        if( $user->getContratto()->isFull() ) {
            $ore += MAX_EXFEST_FULL;
        } else {
            $ore += MAX_EXFEST_SHORT;
        }
        
        //echo "ex-fest: $ore\n\r";
        return $ore;
    }
    
    
    
}
 
