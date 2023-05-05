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

    
    protected $_ferie_annuali = '26';
    
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
        $this->_ferie_maturate = ($this->_ferie_annuali / 12) * $mesi; 
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
        //echo '<p> Mesi servizio: '. $mesi_servizio .'</p>';
        return $mesi_servizio;
       
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
            //echo date('Y') - 2 .': '. $row->residuo;
        }
       
        
        if( $oggi->isEarlier($dataLimite) ) {
            
            $giorno_residuo = 0;
            $no_giorno_residuo = 0;
            
            //controllo che esista la tupla e restituisco i giorni residui
            
            
            foreach($dataFerie as $k => $v) {
                
                $ferie = new Zend_Date($v);
                if($ferie->isEarlier($dataLimite)) {
                    $giorno_residuo++;
                } else {
                    $no_giorno_residuo++;
                }
                       
                
            }
            
            if($giorno_residuo > 0) {
                //controllo il secondo anno
                // 1) controllo che sia presente il secondo anno
                // e se presente restituisco il numero di giorni residui
                // se il numero di giorni e >= alla mia variabile allora
                // sottraggo dal database i gioni
                //altrimenti controllo ilprimo anno e faccio lo stesso discorso,
               // altrimenti controllo l'anno in corso e verifico di aver maturato 
               // abbastanza giorni per fare le ferie altrimenti non se ne fa nulla
                // se è tutto ok aggiorno tutti i database
                //
                
            } 
            
            if($no_giorno_residuo > 0) {
                //cerco gioni residui nell'anno precedente e, se presenti, verifico che siano abbastanza da sottrarre
                //
                //
                //
                //
            }
             
            
            
            
            
        //CONTROLLO L'ANNO PRECEDENTE A QUELLO ATTUALE    
        } else {
            echo '<p>non puoi usufruire del residuo ' . $annoSecondo . ' - controllo il residuo ' . $annoPrimo .'</p>';
            
            
            $giorni_ferie_richiesti = $days['totale']; 
            
            $row = $this->_dbConfig->findByYear(1, $annoPrimo);
            
            
            if($row) {
                
                echo '<p>Residuo ' . $annoPrimo .': '. $row->residuo .'</p>';
                
                $residuo = $row->residuo;
                
                if($residuo >= $giorni_ferie_richiesti) {
                    
                }elseif($residuo == 0) {
                    
                } else {
                    $giorni_ferie = $giorni_ferie_richiesti - $residuo;
                    if($giorni_ferie > 0) {
                        
                    }
                }
                    
                
                
                
                $giorni_totali_rimanenti = $giorni_ferie_richiesti - $residuo;
                
                if($giorni_totali_rimanenti < 0 ) {
                    $residuo = abs($giorni_totali_rimanenti);
                    $godute = $giorni_totali_richiesti;
                } else {
                    $residuo = 0;
                    $godute  = ($row->godute + $row->residuo);
                }
                
                
                $dataPrec = array(
                    'residuo' => $residuo,
                     'godute' => $godute
                );
                
                $wherePrec = array(
                    'user_id' => 1, 
                    'anno'    => $annoPrimo
                );
                
                if($giorni_totali_rimanenti > 0) {
                    
                    //cerco ferie dell'anno corrente;
                    if($this->_ferie_maturate >= $giorni_totali_rimanenti) {
                        $row = $this->_dbConfig->findByYear(1, $annoInCorso);
                        echo '<p>Residuo '. $annoInCorso . ': ' . $row->residuo .'</p>';
                        
                        //se ho le ferie maturate è non le ho utilizzate tutte
                        if($row->residuo >= $giorni_totali_rimanenti) {
                            $residuo = ($row->residuo - $giorni_totali_rimanenti);
                            $godute  = ($row->godute + $giorni_totali_rimanenti);
                            
                            echo '<p>Utilizzo le ferie maturate - Aggiorno database anno corrente e anno precedente</p>';
                            
                            //AGGIORNO ENTRAMBI I DATABASE
                            $where = array(
                                'user_id' => 1,
                                'anno' => $annoInCorso
                            );
                            
                            $data = array(
                                'residuo' => $residuo,
                                'godute'  => $godute
                            );
                            
                            try {
                                $this->_dbConfig->update($dataPrec, $wherePrec);
                                $this->_dbConfig->update($data, $where);
                            } catch(Exception $e) {
                                $e->getMessage();
                            }
                            
                        } else {
                            echo '<h3>Ferie residue non sufficenti</h3>';
                        } //fine ferie maturate e non utilizzate
                      
                    } else {
                        echo '<h3>Attenzione, monte ferie non sufficiente!</h3>';
                    } //fine cerco ferie anno corrente
                    
                    
                } else {
                    
                    //aggiorna SOLO il database anno precedente
                    try {
                        $this->_dbConfig->update($dataPrec, $wherePrec);
                    } catch(Exception $e) {
                        $e->getMessage();
                    }
                } 
                
                
                
            } else {
                //
                //PROCEDURA SOLO PER L'ANNO CORRENTE PERCHè IL PRECEDENTE NON è CONGRUO
                
            }
                       
        }//fine della procedura anno precedente e anno corrente
        
        
        
        
        
        // controllo residuo secondo anno precendente 
        // 
        // 
        // 
        //controllo residuo anno precedente
        
        
        
        //creo un array con la data effettiva di ogni singolo giorno,
        // esclusa domenica e feste, delle ferie richieste
        /**
         * conto i giorni totali
         * levo dall'array ogni giorno oltre la data limite dei 18 mesi
         * conto i giorni entro il limite e quelli fuori
         * aggiorno il residuo del secondo e primo anno
         * 
         * 
         * 
         * 
         * 
         * 
         */
        
        
        
        
        
        
    }
    
    
    /**
     * 
     * @return boolean
     */
    public function inResiduo() {
        //restituisco true/false a seconda del caso 
        return true;
        
    }
    
    
    
    /**
     * 
     * @param type $year
     */
    public function getResiduo($year) {
        
    }


    
    
    
    
    public function calculate() {
        
        
        echo 'mesi servizio: ' . $this->calcolaMesiServizioEffettivi();
        echo '<br>';
        $this->_ferie_maturate = $this->calcolaFerieMaturateAnnoInCorso();
        echo 'Ferie Maturate nell\'anno in corso: ' . $this->_ferie_maturate;
        echo '<br>';
        
        $days = Application_Service_Tools::getDays( $this->_data_inizio_ferie, $this->_data_fine_ferie )  ;
        //print_r($days);
         
        echo 'gioni ferie richiesti: ' . $days['totale'];
        
       // $this->calcolaResiduo();
        
        $this->getResiduoFerie($days);
        
        
        
    }
    
    
    
}


