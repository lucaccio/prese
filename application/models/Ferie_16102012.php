<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ferie
 *
 * @author Zack
 */
class Application_Model_Ferie {
    
    const TOTALE_GIORNI_FERIE_ANNUI = 26;
    
    //protected $_ferie_annuali = '26';
    
    protected $_ferie_maturate;
    
    protected $_data_inizio_ferie;
    
    protected $_data_fine_ferie;
    
    protected $_data_inizio_lavoro;
    
    protected $_data_fine_lavoro;
    
    /**
     * 
     * @param type $start
     * @param type $stop
     */
    public function __construct($start = null, $stop = null) {
        $this->_dbConfig = new Application_Model_ConfigMapper();
        $this->setDateStart($start);
        $this->setDateStop($stop);
        return $this;
    }
    
    /**
     * 
     * @param type $date
     */
    public function setDateStart($date) {
        $this->_data_inizio_ferie = $date;
        return $this;
    }
    
    /**
     * 
     * @param type $date
     */
    public function setDateStop($date) {
        $this->_data_fine_ferie   = $date;
        return $this;
      
    }
    
    /**
     * Calcolo i giorni di ferie maturati
     * @param type $inizio
     * @param type $fine
     * @return int/string
     */
    public function calcolaFerieMaturateAnnoInCorso($inizio = null, $fine = null) {
        
        if((null == $inizio) &&  (null == $fine)) {
            //primo giorno dell'anno
            $inizio = date('Y-m-d', mktime(0,0,0,1,1,date('Y')));
            //data di oggi
            $fine   = date('Y-m-d');
        }
        
        $mesi = $this->calcolaMesiServizioEffettivi($inizio, $fine);
        $this->_ferie_maturate = (self::TOTALE_GIORNI_FERIE_ANNUI / 12) * $mesi ; 
        return $this->_ferie_maturate;
         
    }
    
    /**
     * 
     * @param type $inizio
     * @param type $fine
     * @return type
     */
    public function calcolaMesiServizioEffettivi($inizio = null , $fine = null) {
        
        if((null == $inizio) &&  (null == $fine)) {
            //primo giorno dell'anno
            $inizio = date('Y-m-d', mktime(0,0,0,1,1,date('Y')));
            //data di oggi
            $fine   = date('Y-m-d');
            
            
            
            
        }
            
        
        $mesi_servizio = '0';
        $start = new Zend_Date($inizio);
        $stop  = new Zend_Date($fine);
                
         //calcolo mesi si servizio nell'anno in corso
        $mesi_servizio = $stop->toString('M') - $start->toString('M') + 1 ;
        if( date('d', mktime(0,0,0, 1, 16, 2000)) <= $start->get(Zend_Date::DAY) ) {
            $mesi_servizio--;
        } 
                
        if( date('d', mktime(0,0,0, 1, 14, 2000)) >= $stop->get(Zend_Date::DAY) ) {
            $mesi_servizio--;
        } 
        echo '<p> Mesi servizio: '. $mesi_servizio .'</p>';
        
        
        
        return $mesi_servizio  ;
       
    }
    
    /**
     * Cerco il residuo di gg ferie nei max 18 mesi precedenti 
     * 
     * 
     */
    public function getResiduoFerie($days) {
        
        //devo controllare che oggi non sia un giorno oltre i 18 mesi, se non lo è, devo controllare che le date
        // di richiesta non superino il limite - se la data di partenza supera il limite allora il secondo anno manco lo considero
        // se ne inizio ne fine supera il limite allora controllo il residuo
        // se il primo non supera il limite ma il secondo si allora devo fare tutto l'algoritmo
        
        
        $giorni_di_ferie_richiesti = $days['totale'];
        
        $inizioF = '';
        $fineF   = '';
        
        
        $oggi = date('Y-m-d');
        $oggi = new Zend_Date($oggi);
        
        //questo va messo in una costante
        $meseLimite     = '07';
        $giornoLimite   = '01';
        
        $annoInCorso    = date('Y');
        $annoPrimo      = $annoInCorso - 1;
        $annoSecondo    = $annoInCorso - 2; 
        
        $dataLimite  = mktime(0,0,0,$meseLimite, $giornoLimite, $annoInCorso);
                
        $dataLimite  = new Zend_Date($dataLimite);
        $row = $this->_dbConfig->findByYear(1, $annoSecondo);
        
        
        if($row) {
            $residuo = $row->residuo;
        }
       
        
        if( $oggi->isEarlier($dataLimite) ) {
            
          
            
            $giorno_residuo = 0;
            foreach($days['giorni'] as $giornoFerie) {
                
                $ferie = new Zend_Date($giornoFerie);
                if($ferie->isEarlier($dataLimite)) {
                    $giorno_residuo++;
                }
                       
                
            }
            
        
            
            
            
        //CONTROLLO L'ANNO PRECEDENTE A QUELLO ATTUALE    
        } else {
            echo '<p>non puoi usufruire del residuo ' . $annoSecondo . ' - controllo il residuo ' . $annoPrimo .'</p>';
            
            
            $giorni_ferie_richiesti = $days['totale']; 
            
            $ferie_anno_in_corso =  $this->calcolaFerieMaturateAnnoInCorso($inizio, $fine);
            
            
            $row = $this->_dbConfig->findByYear(1, $annoPrimo);
            $row = $this->_dbConfig->findByYear(1, $annoPrimo);
            
            if($row) {
                
                echo '<p>Residuo ' . $annoPrimo .': '. $row->residuo .'</p>';
                
                $residuo = $row->residuo;
                
                if($residuo >= $giorni_ferie_richiesti) {
                    //aggiorno solo il penultimo anno
                    return true;
                }elseif($residuo == 0) {
                    
                } else {
                    $giorni_ferie = $giorni_ferie_richiesti - $residuo;
                    if($giorni_ferie > 0) {
                        //aggiorno penultimo anno 
                    }
                }
                    
                             
            }   
        }
        
        
        
        
        
    }
    
    /**
     * 
     * @param type $value
     * @return boolean
     */
    public function getResiduo($value) {
        $year = $value;
        $row = $this->_dbConfig->findByYear(1, $year);
        if($row) {
            return $row->residuo;
        }
        return 0;
    }
    
    public function getGodute($value) {
        $year = $value;
        $row = $this->_dbConfig->findByYear(1, $year);
        if($row) {
            return $row->goduto;
        }
        return 0;
    }
    
    
    /**
     * 
     */
    public function calculate() {
        
         
        $days = Application_Service_Tools::getDays( $this->_data_inizio_ferie, $this->_data_fine_ferie )  ;
        
        //print_r($days);
        echo 'giorni richiesti: ' . $days['totale'] .'<br>';
        $this->_ferie_maturate_anno_in_corso   = $this->calcolaFerieMaturateAnnoInCorso('2012-06-25', '2012-08-31');
        
        $this->_residuo_anno_zero              = $this->getResiduo(date('Y'));
        
        $this->_godute_anno_zero               = $this->getGodute(date('Y')); 
                
        $this->_residuo_anno_uno               = $this->getResiduo(date('Y') - 1);
        
        $oggi = date('Y-m-d');
        
        $dataLimite  = mktime(0,0,0,7, 1, date('Y') );
        
        $dataLimite  = new Zend_Date($dataLimite);
                
        $giorno_residuo_anno_due = 0;
        $giorni_prima = 0;
        
        foreach($days['giorni'] as $giornoFerie) {
            $ferie = new Zend_Date($giornoFerie);
                if($ferie->isEarlier($dataLimite)) {
                    ++$giorni_prima;
                }
        }
        //non va perchè devo inserire ferie di mesi passati
       // if( $oggi->isEarlier($dataLimite) ) {  
        if($giorni_prima > 0 ) {
            $this->_residuo_anno_due  = $this->getResiduo(date('Y') - 2);
            
            if( $this->_residuo_anno_due >= $giorni_prima ) {
                 
                  $this->_residuo_anno_due = $giorni_prima;
                //il residuo va a zero e le godute vengono sommate con giorni_prima
                //sempre che possa salvare tutto
            } else {
                //superfluo ma è solo er chiarezza
                 $this->_residuo_anno_due  = $this->_residuo_anno_due;
                
            }
        }  else {
            $this->_residuo_anno_due  = 0;
        } 
            
       // }
      
        /*
       echo '<br>';
       echo '<br>0 ' .  ($this->_ferie_maturate_anno_in_corso  -  $this->_godute_anno_zero) ;
       echo '<br>1 ' .  $this->_residuo_anno_uno;
       echo '<br>2 ' .  $this->_residuo_anno_due;
       echo '<br>' ;
       */
        
       $totale_ferie_maturate = ($this->_ferie_maturate_anno_in_corso  - $this->_godute_anno_zero)
                                + ( isset($this->_residuo_anno_uno) ? $this->_residuo_anno_uno : 0 ); 
                                + ( isset($this->_residuo_anno_due) ? $this->_residuo_anno_due : 0 );
        
        
             
         echo "<p>Maturate: $totale_ferie_maturate</p>";
         //echo ($this->_ferie_maturate_anno_in_corso  - $this->_godute_anno_zero);
        
        
        
        if($totale_ferie_maturate >= $days['totale']) {
             
            
            //aggiorno e salvo tutto
            //compreso lo storico per una eventuale procedura contraria   
            
            
           if($this->_residuo_anno_due > 0) {
               //salvo db secondo anno e sottraggo  $this->_residuo_anno_due da residuo
               echo '<br>sottraggo anno due: ' . $this->_residuo_anno_due; 
                $days['totale'] -= $this->_residuo_anno_due;
           } 
           if($days['totale'] > 0) { 
                if($this->_residuo_anno_uno > 0) {
                    //salvo db primo anno levando da residuo $this->_residuo_anno_uno

                    echo '<br>sottraggo anno uno: ' . $this->_residuo_anno_uno; 
                    if($days['totale'] >= $this->_residuo_anno_uno) {
                         
                         $days['totale'] -= $this->_residuo_anno_uno;
                         $this->_residuo_anno_uno = 0;
                        echo "<p>Rimane residuo anno 2011: $this->_residuo_anno_uno</p>";
                        echo "<p>Rimane da sottrarre: ". $days['totale']."</p>";
                    } else {
                      
                         $this->_residuo_anno_uno -= $days['totale'];
                         $days['totale'] = 0;
                         echo "<p>Rimane residuo anno 2011: $this->_residuo_anno_uno</p>";
                    }
                    
                }
           }
           
           if($days['totale'] > 0) { 
                 $residuo = $this->_ferie_maturate_anno_in_corso - $this->_godute_anno_zero;
               if($residuo >= $days['totale'] ) {
                  $godute = $residuo - $days['totale'] ;
                  $days['totale'] = 0;
                  echo "<p>Rimane residuo anno 2012: $godute</p>";
               } else {
                   echo 'errore non hai residuo';
               }
           }
           
           
           
           //salvo db principale con $days['totale'] aggiornato
            
            
        }  else {
            echo 'non hai maturato abbastanza ferie';
        }
       
        
      
        
        
        
        
        
    }
    
    
    
}


