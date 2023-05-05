<?php
/**
 * Description of Calendario
 *
 * @author luca
 */
class Application_Model_Calendario {
   
    
   protected $_events = array(); 
       
   protected  $_days = array(
        'Lunedì', 
        'Martedì',
        'Mercoledì',
        'Giovedì',
        'Venerdì',
        'Sabato',
        'Domenica'
    );
    
    
    protected $_year ;
    
    protected $_month ;

    protected $_total_days_in_month;
    
    protected $_previous_month;
    
    protected $_next_month;

    protected $_first_day;

    protected $_last_day ;


    /**
     * Application_Model_Calendario constructor.
     * @param $year
     * @param $month
     * @param $events
     * @param null $eventi_giri
     */
    public function __construct($year, $month, $events, $giri = nul, $elenco_feetivita = null)
    {


        //Zend_Debug::dump($elenco_feetivita);
        // inserisco i giri
        if(isset($giri)){
            foreach($giri as $giro) {
                $this->_events[$giro['giorno']] ['giri'] = $giri[ $giro['giorno'] ];
                //unset( $giri[ $giro['giorno'] ] );
            }
        }

        if(isset($elenco_feetivita))
        {
            $feste = $elenco_feetivita['feste'];
            foreach($feste as $date => $values) {
                foreach($values as $k => $festa) {
                   // Zend_Debug::dump($festa);
                   $sede = Application_Service_Tools::sede( $festa['sede'] );

                    $valori = array(
                        'sede' => $sede->getCitta(),
                        'descrizione' => $festa['descrizione'],
                        //'lavorativo'  => $festa['lavorativo']
                    );
                    $this->_events[$date] ['festivita'] [] =  $valori;
                }
            }
        }




        //inserisco gli eventi
        if( isset($events) ) {
            foreach($events as $event) {
                //Zend_Debug::dump($event);
                $this->_events[$event['giorno']]['assenze'][] = $event;
            }
        }
        
        $this->_year = $year;
        
        $this->_month = $month;
        
        $this->_total_days_in_month = date('t', mktime('0','0','0',$this->_month, 1, $this->_year));
        
        $this->_first_day = date('w', mktime('0','0','0',$this->_month, 1, $this->_year));
        
        $this->_previous_month = date($this->_month - 1);
        
        $this->_next_month = date($this->_month + 1);      
                        
        $this->_last_day = date('w', mktime('0','0','0',$this->_month, $this->_total_days_in_month, $this->_year));
        //Zend_Debug::dump($this->_events);
        return $this;
    }
    
    public function getDays() {
        return $this->_days;
    }
    
    public function setFirstDay($day) {
        $this->_first_day = (int)$day;
    }
    
    public function getFirstDay() {
        return $this->_first_day;
    }
    
    public function getPreviousMonth() {
        return $this->_previous_month;
    }
    
    public function getTotalDaysInMonth() {
        return $this->_total_days_in_month;
    }
    
    public function getLastDay() {
        return $this->_last_day;
    }
    
    public function getEvents() {
        return $this->_events;
    }

 
    
    /**
     * 
     * @param type $day
     * @return type
     */
    public function _getDay($day) {
        $x =  date('w', mktime('0','0','0',$this->_month, $day, $this->_year));
        return $x;
    }
    
    
    /**
     * 
     * @param type $month
     * @return type
     */
    public function _getDays($month) {
        return    date('t', mktime('0','0','0',$month, 1, $this->_year));
    }
    
    
    /**
     * 
     * @param type $day
     * @return type
     */
    public function formatDate($day) {
        $x = date('Y-m-d', mktime('0','0','0',$this->_month, $day, $this->_year) );
        //echo $x;
        return $x;
    }
    
    
    
}

 
