<?php
/**
 * Description of MyDate
 *
 * @author Luca
 */
class Application_Model_MyDate {
   
    
    /**
     *
     * @var type 
     */
    protected $_start;
    
    /**
     *
     * @var type 
     */
    protected $_stop;
    
    /**
     *
     * @var type 
     */
    protected $_dateStart;
    
    /**
     *
     * @var type 
     */
    protected $_dateStop;
    
    /**
     *
     * @var type 
     */
    protected $_totalDays;       
    
    /**
     *
     * @var type 
     */
    protected $_actualDays = array();
    
    /**
     *
     * @var type 
     */
    protected $_festivita = array();
        
    /**
     *
     * @var type 
     */
    protected $_message;
    
    // iso_8601
    protected $_default_format =  'yyyy-MM-dd';  
    
    /**
     * 
     * @param mixed $start
     * @param mixed $stop
     */
    public function __construct($start, $stop) 
    {
        
        if(true == $this->validate($start) && true == $this->validate($stop))
        {
            if($start instanceof Zend_Date) {
                $start = $start->toString($this->_default_format);
            }
            if($stop instanceof Zend_Date) {
                $stop = $stop->toString($this->_default_format);
            }
            
            $this->_start     = $start = new DateTime($start);
            $this->_stop      = $stop  = new DateTime($stop);
            $this->_totalDays = $start->diff($stop);
            $this->_dateStart = $start->getTimestamp();
            $this->_dateStop  = $stop->getTimestamp();
        }
    }
    
    /**
     * 
     * @param type $date
     */
    public function validate($date) {
        
        if(null == $date) {
            throw new Application_Model_MyDate_Exception(__CLASS__ . " Inserire entrambe le date");
        }
        
        if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
            return true;
        } elseif(preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/", $date)) {
            return true;
        } elseif($date instanceof Zend_Date) {
            return true;
        } else {
           throw new Application_Model_MyDate_Exception('La data ha un formato non valido');
        }
    }

    /**
     * molto simile a validate ma lo uso senza exception
     * 21/04/2021
     * @param type $date
     */
    public function valida($date) {
        
        if(null == $date) {
            return false;
        }
        
        if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
            return true;
        } elseif(preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/", $date)) {
            return true;
        } elseif($date instanceof Zend_Date) {
            return true;
        } else {
           return false;
        }
    }

        
    /**
     * 
     * @return boolean
     */
    public function following() {
                
        if($this->_dateStart < $this->_dateStop) {
            return true;
        }
        throw new Application_Model_MyDate_Exception('La data Inizio è successiva alla data Fine');
        //$this->_message = 'La data di inizio deve essere precedente a quella di fine';
        return false;
    }
    
    /**
     * 
     * @return boolean
     */
    public function same() {
        if($this->_dateStart == $this->_dateStop) {
            return true;
        }
        //TODO: controllare come visualizzare meglio questo
        throw new Application_Model_MyDate_Exception('Il giorno di inizio e fine deve essere lo stesso');
        return false;
    }
    
    
    
    /**
     * Verifica che la data di inizio non sia successiva a quella di fine
     * @return boolean
     */
    public function verify() {
        if($this->_dateStart <= $this->_dateStop) {
            return true;
        }
        throw new Application_Model_MyDate_Exception('La data Inizio è successiva alla data Fine');
        return false;
    }

    
    
    public function countTotalDays() {
        $t =  $this->_totalDays->format('%R%a days') + 1 ;
        //echo "<p>totale: $t</p>";
        return $t;
        
    }
    
    public function countActualDays($sede_id = null) {
        
        //$festa = array();
        //$festivita = new Application_Model_FestivitaMapper();
        
        // qui mi serve l'id dell'operatore per sapere se ha una sede specifica
        // se ce l'ha allora devo vedere quali feste comandate esistono per quella sede
        // e dare il calcolo effettivo dei giorni di ferie escludendo gli eventuali non lavorativi
        
       // $nonlavorativi = $festivita->getFestivita(0);
        
        //popolo l'array
        //foreach($nonlavorativi as $row) {
       //     $festa[$row->mese . '-' . $row->giorno]  = '';
       // }
        
        $totale = $this->countTotalDays();
        $inizio = clone $this->_start;
        for ((int)$i = $this->_dateStart; (int)$i <= $this->_dateStop; (int)$i =  $inizio->modify('+1 day')->getTimestamp()) {
            $formattata = date('Y-m-d', $i);
            if($this->isSunday($i)) {
                $totale-- ;
                continue;
            } elseif($this->isHoliday($i, $sede_id)) {
                $totale--;
                continue;
            }
           if($this->isEasterMonday($i)) {
                $totale--;
                continue;
           }
           $this->_actualDays[]  = $formattata;
        }
        //echo "<p>totale finale: $totale</p>";
        
        return $totale;
    }
     
    /**
     * Controlla se è sabato
     * @param type $date
     * @return boolean
     */
    public function isSaturday($date) {
        
        if(Prisma_Utility_Validate::isDateFormat($date)) {
            $date = new DateTime($date);
            $date = $date->getTimestamp();
        }                 
        if(6 == (int)date('w', $date) ) {
                return true;
            }
        return false; 
    }
    
    /**
     * Controlla se è domenica
     * @param type $date
     * @return boolean
     */
    public function isSunday($date) {
        if(!is_long($date)) {
            $date = new DateTime($date);
            $date = $date->getTimestamp();
        }        
        if(0 == (int)date('w', $date) ) {
                return true;
            }
        return false; 
    }
    
    /**
     * Controlla se è Pasqua
     * @param type $date
     * @return boolean
     */
    public function isEaster($date) {
      $year = date('Y', $date);
         $easter = Application_Model_Pasqua::get($year);
         if($date == mktime(0,0,0, $easter['month'], $easter['day'], $year)) {
             return true;
         }
         return false;  
    }
    
    /**
     * Controlla se è pasquetta
     * @param type $date
     * @return boolean
     */
    public function isEasterMonday($date) {
         $year = date('Y', $date);
         $easterMonday = Application_Model_Pasqua::pasquetta($year);
         if($date == $easterMonday) {
             return true;
         }
         // echo '<p>non è pasquetta</p>';
         return false;
    }
    
    /**
     * 
     * @param type $date
     * @param null $sede_id
     * @return boolean
     */
    public function isHoliday($date, $sede_id = null) {
        $festivita = new Application_Model_FestivitaMapper();
        $nonlavorativi = $festivita->getFestivita($sede_id);
        
        //popolo l'array
        foreach($nonlavorativi as $row) {
           $this->_festivita[$row->mese . '-' . $row->giorno]  = '';
        }
                
        $date = date('n-j', $date);
        
        if(array_key_exists($date, $this->_festivita)) {
            return true;
        }
        return false;
    }
    
    
    /**
     * 
     * @return type
     */
    public function getMessage() {
        return $this->_message;
    }
    
    
    public function getStart($format = null) {
        if(null == $format) {
            return $this->_start;  
        } 
        return $this->_start->format($format);
        
    }
    
    public function getStop($format = null) {
        if(null == $format) {
            return $this->_stop;  
        } 
        return $this->_stop->format($format);
    }
    
    public function is() {
        
    } 
    
    
}

 