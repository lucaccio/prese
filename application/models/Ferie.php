<?php

/**
 * Description of Ferie
 *
 * @author Luca
 */


class Application_Model_Ferie {
    
    
    
    const TOTALE_GIORNI_FERIE_ANNUI = 26;
    
    protected $_user_id;
    
    protected $_user;
    
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
    public function __construct($user_id, $start = null, $stop = null) {
                
        $this->_user_id = $user_id;
        $this->_dbUser = new Application_Model_UserMapper();
        $this->_user = $this->_dbUser->find($this->_user_id);
        
        // print_r($this->_user);
        
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
    public function calcolaMaturato($inizio = null, $fine = null) {
        $assunzione = $this->_user->getAssunzione();
        //print_r($this->_user); 
        
        $array = explode('-', $assunzione);
               
        if(date('Y') == $array[0]) {
           $inizio = $assunzione;
        }
        
        if( (null == $inizio) ) {
            //primo giorno dell'anno
            $inizio = date('Y-m-d', mktime(0,0,0,1,1,date('Y')));
        }
        if( (null == $fine) ) {
            //data di oggi
            //$fine   = date('Y-m-d');
            $fine = date('Y-m-d', mktime(0,0,0, date('m') , 1  ,date('Y')));
        }
         
        $mesi = $this->calcolaMesi($inizio, $fine);
        $this->_ferie_maturate = (self::TOTALE_GIORNI_FERIE_ANNUI / 12) * $mesi ; 
        return $this->_ferie_maturate;
         
    }
    
    /**
     * 
     * @param type $inizio
     * @param type $fine
     * @return type
     */
    public function calcolaMesi($inizio = null , $fine = null) {
        
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
            $mesi_servizio++;
        } 
       // echo '<p> Mesi servizio: '. $mesi_servizio .'</p>';
               
        return $mesi_servizio  ;
       
    }
    
    public function generate() {
        
        $residuoTotale = date('Y') . ": " . round( (  $this->getResiduo( date('Y') - 1 ) 
                                                +  self::TOTALE_GIORNI_FERIE_ANNUI 
                                                - $this->getGoduto(date('Y'))) , 2 );
        
        echo "<p></p>";
        
        
        echo "<p>Periodo: " . (date('m') - 1 ) . "</p>";
       // echo "<p>Mesi: ".$this->calcolaMesi()."</p>";
         echo "<p>Residuo anno precedente: " . $this->getResiduo( date('Y') - 1 ) . "</p>";
         echo "<p>Maturato: ". round($this->calcolaMaturato(), 2) ."</p>";
         echo "<p>Goduto: ". $this->getGoduto(date('Y')) ."</p>";
         echo "<p>Residuo " . date('Y') . ": " . round( (  $this->getResiduo( date('Y') - 1 ) +  $this->calcolaMaturato() - $this->getGoduto(date('Y'))) , 2 ) . "</p>";
         echo "<p>Da Maturare: " . (self::TOTALE_GIORNI_FERIE_ANNUI - $this->calcolaMaturato()). "</p>";
         echo "<p>Residuo Totale " . $residuoTotale . "</p>";
         echo "<p></p>";
         echo "<p></p>";
         echo "<p></p>";
         
         
         $days = Application_Service_Tools::getDays( $this->_data_inizio_ferie, $this->_data_fine_ferie )  ;
         
        //se i gioni sono inferiori uguali a l residuo totale allora aggiono il database
        
        if($days['totale'] > $residuoTotale) {
            echo "<p>Non ho abbastanza gioni di ferie</p>";
        } else {
              echo "<p>Totale: " . $days['totale'] . "</p>";
            echo "<p>Salvo e aggiorno il db</p>";
        }
        return $this;
    }
        
    /**
     * 
     * @param type $value
     * @return boolean
     */
    public function getResiduo($value) {
        $year = $value;
        $row = $this->_dbConfig->findByYear($this->_user_id, $year);
        if($row) {
            return $row->residuo;
        }
        return 0;
    }
    
    /**
     * 
     * @param type $value
     * @return int
     */
    public function getGoduto($value) {
        $year = $value;
        $row = $this->_dbConfig->findByYear($this->_user_id, $year);
        if($row) {
            return $row->goduto;
        }
        return 0;
    }
    
    
    
    
}