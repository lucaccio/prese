<?php
/**
 * Description of Calendario
 *
 * @author luca
 */
class Application_Model_Calendario1 {
   
    
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
     * 
     * @param type $year
     * @param type $month
     * @param type $events
     */
    public function __construct($year, $month, $events)
    {
 
        if( isset($events) ) {
            foreach($events as $event) {
                $this->_events[$event['giorno']][] = $event;
            }
        }
        
        $this->_year = $year;
        
        $this->_month = $month;
        
        $this->_total_days_in_month = date('t', mktime('0','0','0',$this->_month, 1, $this->_year));
        
        $this->_first_day = date('w', mktime('0','0','0',$this->_month, 1, $this->_year));
        
        
        $this->_previous_month = date($this->_month - 1);
        
        $this->_next_month = date($this->_month + 1);      
                        
        $this->_last_day = date('w', mktime('0','0','0',$this->_month, $this->_total_days_in_month, $this->_year));
        
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
     * 
     */
    protected function generate()
    {
        
       
       echo '<table width=\'100%\'>';
       echo '<tbody>';
       
       foreach($this->getDays() as $day) {
        
           echo '<th align=\'left\'>'.$day.'</th>';
       
           
       }
       
       echo '</tbody>';
       
       echo '<tr>';
       
       //se è domenica allora 0 diventa 7
       $this->getFirstDay() == 0 ? $this->setFirstDay(7) : $this->getFirstDay();
       
        //genero i giorni del mese precedente per completare il calendario
       $prev_days = $this->_getDays($this->getPreviousMonth()) - $this->getFirstDay() + 2 ;
       
       for($i = 1; $i < $this->getFirstDay(); $i++) {
           echo '<td  style="color:#c0c0c0">'.$prev_days.'</td>';
           $prev_days++;
       }
       
       
       //okkio qui che non stampa l'evento di ogni domenica
       for($i = 1; $i<=$this->getTotalDaysInMonth(); $i++) {
                    
           echo '<td>' . $i; 
                     
           if( array_key_exists( $this->formatDate($i), $this->getEvents()) ){
                      $e = $this->getEvents();
               foreach($e[$this->formatDate($i)] as $k => $v) {
                    echo '<div>
                        
                        [FERIE] Operatore: '. $v['user_id'].' => Sostituito da: ' . $v['sostituto_id'] . 
                        '</div>'; 
                  
               }
                         
           }
           
           echo '</td>';
           if( 0 == (int) $this->_getDay($i)) {
              // echo '<td>'.$i.'</td></tr><tr>';
               
               echo '</tr><tr>';
               continue;
           }
           
       }
       
       // numeri per completare il calendario
       if( 0 != (int)$this->getLastDay() ) {
           
           $r = 7 - $this->getLastDay();
           
           for($i = 1; $i <= $r; $i++ ) {
               echo '<td  style="color:#c0c0c0">'.$i;
               
               echo '</td>';
           }
           
       }
       
       
       echo '</tr>';
       echo '</table>';
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
    
    
    public function render() {
        return $this->generate();
    }
    
    
}

 
