<?php
/**
 * 
 * 
 * Attenzione, le variabili static, se all'interno di un loop, 
 * non si cancellano a ogni loop, in quanto static
 * 
 * 
 */
class Application_Service_Tools {
    
    
    
    protected static $_festivita = array();
    
    /**
     *  array delle feste nel periodo considerato
     * @var type 
     */
    protected static $_festeInFerieEffettive = array();
    
    /**
     * array con i giorni di ferie scorporati delle festivita
     * @var type 
     */
    protected static $_ferieEffettive = array();
    
    /**
     * Array col dettaglio dei giorni lavorati o festivi
     * 
     * @var array
     */
    protected static $_range = array();
    
    
    /**
     * Genera un array con tutte le date nel range che fornisco
     * 
     * D = Domenica
     * H = Holiday
     * L = Lavorativo
     * EM = Pasquetta
     * 
     * @param type $first
     * @param type $last
     * 
     * @return array
     */
    public static function generateArrayOfDays($first, $last)
    {
        $start = new Zend_Date($first, Zend_Date::ISO_8601);
        $stop  = new Zend_Date($last, Zend_Date::ISO_8601);
        
        for ( (int)$ts = $start->getTimestamp(); 
                    (int)$ts <= $stop->getTimestamp();   
                            (int)$ts =  $start->addDay(1)->getTimestamp() 
            )  
        { 
            $d = $start->toString('yyyy-MM-dd');
            if(0 ===  (int) $start->get(Zend_Date::WEEKDAY_DIGIT) )  {
                self::$_range[$d] = 'D';
                continue;
            } elseif(self::isHoliday($ts)) {
                self::$_range[$d]  = 'H';
                continue;
            } elseif(1 ===  (int) $start->get(Zend_Date::WEEKDAY_DIGIT)) {
                // inserisco di default 'L'
                self::$_range[$d] = 'L';
                // anno corrente
                $year = $start->get(Zend_Date::YEAR_8601);
                // la Pasqua ricade tra il 22 marzo e il 25 aprile di ogni anno.
                $marzo  = "$year-03-22";
                $aprile = "$year-04-25";
                // controllo se ricado nel range di Pasqua               
                if( ($d >= $marzo) && ($aprile >= $d) ) 
                {
                    if( self::isEasterMonday($ts) ) {
                        self::$_range[$d] = 'EM';
                        continue;
                    } 
                }  
            } else { // se non è domenica né festivo, né lunedi
                self::$_range[$d]  = 'L'   ;
            }
        }
        return self::$_range;  
    }
    
    /**
     *
     * @24 marzo 2018 rifattorizzo le firme
     *
     *
     * In quale formato sono le date?
     * @param type $inizio
     * @param type $fine
     * @return int 
     */
    public static function sottraiFeste($inizio, $fine, $format = 'Y-m-d', $sottraiFeste = true, $sedeId = null ) {
        
        /*
        Prisma_Logger::logToFile("SEDE: " . $sedeId);
       
        $festa = array();
        $festivita = new Application_Model_FestivitaMapper();
        $nonlavorativi = $festivita->getFestivita(0);
        
        //popolo l'array
        foreach($nonlavorativi as $row) {
            $festa[$row->mese . '-' . $row->giorno]  = ''; //04/12/2020 a cosa serve?
        }
*/
        // totale giorni
        $start      = new DateTime($inizio);
        $stop       = new DateTime($fine);
        $intervallo = $start->diff($stop);
        $int        = $intervallo->format('%a');
        $totale     = $int + 1; 
        
        for ((int)$i = $start->getTimestamp();
             (int)$i <= $stop->getTimestamp();
             (int)$i =  $start->modify('+1 day')->getTimestamp()) {
                
                $formattata = date($format, $i);
                // sottraggo feste e domeniche a seconda che si tratti di ferie o malattia maternità
                if($sottraiFeste) {
                    if (self::isSunday($i)) {
                        Prisma_Logger::logToFile("è domenica");
                        $totale--;
                        self::$_festeInFerieEffettive[] = $formattata;
                        continue;
                    } elseif (!self::isHolidayLavorativo($i)) { //@04/12/2020

                        Prisma_Logger::logToFile("non è festa lavorativa");                        
                        if (self::isHoliday($i, $sedeId) ) {
                            Prisma_Logger::logToFile(" è festa ");
                            $totale--;
                            self::$_festeInFerieEffettive[] = $formattata;
                            continue;
                        }

                    } elseif (self::isHoliday($i, $sedeId)) {
                      //  Prisma_Logger::logToFile(" è festa ");
                       // $totale--;
                     //   self::$_festeInFerieEffettive[] = $formattata;
                        continue;
                    } elseif (self::isEasterMonday($i)) {
                        Prisma_Logger::logToFile(" è pasqua ");
                        $totale--;
                        self::$_festeInFerieEffettive[] = $formattata;
                        continue;
                    }
                }
                self::$_ferieEffettive[]  = $formattata;
                //echo $a++ . ') '. date('Y-m-d', $i) .' <br>';
                
                   //riempire l'array se si vuole sapere i giorni reali
            //$self::ferieEffettive[$i] = '';
        }
        Prisma_Logger::logToFile("Totale giorni utili: " . $totale);
        return $totale;
    }
      
    
    /**
     * 
     * @param type $start
     * @param type $stop
     * @return type
     */
    public static function getTotalDays($start, $stop, $sottraiFeste = true, $sedeId = null) {
        return self::sottraiFeste($start, $stop, 'Y-m-d', $sottraiFeste, $sedeId);
    }
    
    /**
     * Ritorna i giorni effettivi come array
     * @param type $start
     * @param type $stop
     * @return type
     */
    public static function getArrayOfActualDays($start, $stop, $format = 'Y-m-d') 
    {
        self::sottraiFeste($start, $stop, $format);
        return self::$_ferieEffettive;
    }
    
    /**
     * azzero la variabile
     */
    public static function emptyFerieEffettive()
    {
        self::$_ferieEffettive = array();
    }
    
    
    /**
     * 
     * @param type $start
     * @param type $stop
     * @return type
     */
    public static function getDays($start, $stop) {
        $totale = self::sottraiFeste($start, $stop);
        $giorni =  self::$_ferieEffettive;
        $feste =  self::$_festeInFerieEffettive;
        
        $result = array(
            'totale' => $totale,
            'giorni' => $giorni,
            'feste'  => $feste
        );
        
        return $result;
    }
    
    
    
    
     /**
     * 
     * @param type $date
     * @return boolean
     */
    public static function isSunday($date) {
       
        $date = self::check($date);
        if(0 == (int)date('w', $date) ) {
                return true;
            }
        return false; 
    }
    
    /**
     * 
     * @param type $date
     * @return boolean
     */
    public static function isSaturday($date) {
       
        $date = self::check($date);
        if(6 == (int)date('w', $date) ) {
            return true;
        }
        return false;        
    }
    
    
    /**
     * @deprecated
     * @return boolean
     */
    public function isHolidaysOld($date) {
         self::$_festivita = array();
        //se la data è in formato anno - mese - giorno
        //la converto in unix time
        if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
            $date = new DateTime($date);
            $date = $date->getTimestamp();
        }
         
        $festivita = new Application_Model_FestivitaMapper();
        $nonlavorativi = $festivita->getFestivita(0);
        
        //popolo l'array
        foreach($nonlavorativi as $row) {
            self::$_festivita[$row->mese . '-' . $row->giorno]  = '';
        }
                
        $date = date('n-j', $date);
        
        if(array_key_exists($date, self::$_festivita)) {
            return true;
        }
        return false;
    }	
    
     /**
     * 
     * 
     * @param type $date
     * @param null $sede_id
     * @return boolean
     */
    public static function isHoliday($date, $sede_id = null) {
        self::$_festivita = array();
        if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
            $date = new DateTime($date);
            $date = $date->getTimestamp();
        }
        $festivita = new Application_Model_FestivitaMapper();
        $nonlavorativi = $festivita->getFestivita($sede_id);

        //popolo l'array
        foreach($nonlavorativi as $row) {
           self::$_festivita[$row->mese . '-' . $row->giorno]  = '';
           //Prisma_Logger::logToFile($row->mese . '-' . $row->giorno . " lavaroativo : " . $row->lavorativo);
        }
                
        $date = date('n-j', $date);
        if(array_key_exists($date, self::$_festivita)) {
            //Prisma_Logger::logToFile("ritorno true");
            return true;
        }
        return false;
    }
    

    /**
     * @04/12/2020
     * Cerco se la festivita nazionale è lavorativa
     * return boolean
     */
    public static function isHolidayLavorativo($date) {
        self::$_festivita = array();
        if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
            $date = new DateTime($date);
            $date = $date->getTimestamp();
        }
        $festivita = new Application_Model_FestivitaMapper();
        $results = $festivita->getFestivitaLavorative(true);

        //popolo l'array
        foreach($results as $row) {
           self::$_festivita[$row->mese . '-' . $row->giorno]  = '';
        }
                
        $date = date('n-j', $date);
        if(array_key_exists($date, self::$_festivita)) {
            return true;
        }
        return false;
    }

    
    /**
     * Patrono
     * @param type $date
     * @param type $sede_id
     * @return boolean
     */
    public static function isPatronSaint($date, $sede_id) {
        $date = explode('-', $date);
        $mese   = self::removeZeroFromNumber(trim($date[1]));
        $giorno = self::removeZeroFromNumber(trim($date[2]));
        $date   = $mese.'-'.$giorno;
               
        $festivita = new Application_Model_FestivitaMapper();
        $result = $festivita->findPatronalSaint($sede_id);
        if($result == $date) {
            return true;
        }
        return false;
        
    }

    /**
     * Patrono
     * @param type $date
     * @param type $sede_id
     * @return boolean
     * @date 23 marzo 2018
     */
    public static function patrono($date, $sede_id) {
        if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
            $date = new DateTime($date);
            $date = $date->getTimestamp();
        }

        // $date = explode('-', $date);
        //$ese   = self::removeZeroFromNumber(trim($date[1]));
        //$giorno = self::removeZeroFromNumber(trim($date[2]));

       // $date   = $date->format('n') . '-' . $date->format('j');
        $date = date('n-j', $date);
        $festivita = new Application_Model_FestivitaMapper();
        $result = $festivita->findPatronalSaint($sede_id);

        if($result) {
            $c = $result->mese . '-' . $result->giorno;
           // Prisma_Logger::logToFile("date il patrono è: " . $date);
           // Prisma_Logger::logToFile("confronta il patrono è: " . $c);
            if($c == $date) {
                return true;
            }
            return false;
        }
        return false;

    }
    
    
        
    /**
     * 
     * @return type
     */
    public static function generaToken() {
       // return md5(date('Y-m-d H:i:s'));
        return md5( uniqid(mt_rand(), true) );
    }
    
   /**
    * 
    * @param type $date
    * @return boolean
    */ 
   function checkDateFormat($date) {
     
    if (preg_match ("/^([0-9]{2})-([0-9]{2})-([0-9]{4})$/", $date, $parts))
    {
         if(checkdate($parts[2],$parts[3],$parts[1]))
            return true;
         else
           return false;
    }
    else
      return false;
    }
    
    /**
     * 
     * @param type $date
     * @return type
     */
    public static function check($date) {
        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
           
            if(is_long($date)) {
                return $date;
            }
            
            
        } else {
            $date = new DateTime($date);
            $date = $date->getTimestamp();
            return $date;
        }
        
    }
    
   function pasqua($aa, & $mm, & $gg) { 
       $gm = Array(22, 22, 23, 23, 24, 24); 
       $da = Array(2, 2, 3, 4, 5, 5); 
       $a = $aa % 19; 
       $b = $aa % 4; 
       $c = $aa % 7; 
       $i = floor($aa / 100) - 15; 
       $d = (19 * $a + $gm[$i]) % 30; 
       $e = (2 * $b + 4 * $c + 6 * $d + $da[$i]) % 7; 
       $gg = 22 + $d + $e;  
       $mm = 3; 
       if ($gg > 31) { 
           $mm = 4; 
           $gg -= 31; 
           
           } 
     }
     
     public static function isEasterMonday($date) {
         if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
             $year = substr($date, 0, 4);
             $datario = new Zend_Date($date);
             $timestamp = $datario->getTimestamp();
         } else {
             //se $date è in formato timestamp;
             $year = date('Y', $date);
             $timestamp = $date;
         }
               
         //return timestamp value
         $easterMonday = Application_Model_Pasqua::pasquetta($year);
         
        // print_r($easterMonday);
        //  print_r($timestamp);
         if($timestamp == $easterMonday) {
             return true;
         }
         return false;
     }
     
     
     /**
      * TODO: permettere anche il tipo di formattazione
      * Converte la data DD-MM-AAAA in formato Us YYYY-MM-DD
      * @param type $data
      * @return string
      */
     public static function convertDataItToUs($date) {
         $date = trim($date);
         if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
            return $date; 
         }
         $array = explode("-", $date); 
         $date_us = $array[2]."-".$array[1]."-".$array[0]; 
         return $date_us; 
     }
     
     /**
      * TODO: permettere anche il tipo di formattazione
      * Converte la data YYYY-MM-DD in formato italiano DD-MM-AAAA
      * @param type $data
      * @return string
      * 
      */
     public static function convertDataUsToIt($date) {
         $date = trim($date);
         if(preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/", $date)){
           return $date; 
         }
         $array = explode("-", $date); 
         $date_it = $array[2]."-".$array[1]."-".$array[0]; 
         return $date_it; 
     }
     
     /**
      * 
      * @param type $number
      * @return type
      */
     public function removeZeroFromNumber($number) {
          if($number < 10) {
            if(preg_match("/^[0-9]{2}$/", $number)){
                //restituisco il secondo valore
                $number = substr($number,1,1);
            }
          } 
          return $number;
     }
     
     public static function convertToIt($date)
     {
         
         $date = trim($date);
         if(preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/", $date)){
           return $date; 
         }
         $array = explode("-", $date); 
         $date_it = $array[2]."-".$array[1]."-".$array[0]; 
         return $date_it; 
         
         
     }
   
     
     /**
      * Genera un array dove la chiave è una data in formato ISO8601
      * dando cpome parametro il mese e l'anno
      * 
      * @param type $mixed
      */
    public static function listOfDays($mixed, $get_non_working_days = false)
    {
        # 
        if(null === $mixed) {
            $year  = date('Y');
            $month = date('n');
        }
        #  
        if(is_array($mixed)) {
            $month = $mixed['month'];
            $year  = $mixed['year'];
        }
        # creo la lista da un singolo giorno passato in ISO8601
        if(is_string($mixed)) {
            $delimiter = '-';
            $values = explode($delimiter, $mixed);
            $year   = $values[0]; 
            $month  = $values[1];
        }
        
        $date = new Zend_Date();
        $date->setDay('1') 
             ->setMonth($month)
                ->setYear($year)
                   ->setTime( '00:00:00' )
        ;
        #first day
        $first_day_of_month = $date->toString('yyyy-MM-dd'); 
        $date->addMonth('1')->subDay('1');
        $last = $date->toString('dd');
        #last day
        $last_day_of_month = $date->toString('yyyy-MM-dd'); 
        $date->setDay('1');
        
        self::emptyFerieEffettive();
        
        if($get_non_working_days) {
            return self::generateArrayOfDays($first_day_of_month,$last_day_of_month);
        } else {
            return self::getArrayOfActualDays($first_day_of_month,$last_day_of_month);
        }
        
    }
     
     
    public static function commaToPoint($number)
    {
        $result = str_replace(',','.', $number);
        return $result;
    }
      
    
    
    public static function sede($id) {
        $m = new Application_Model_SediMapper()
            ;
        return $m->find($id);
    }
 

}
