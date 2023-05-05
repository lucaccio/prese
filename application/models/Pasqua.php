<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pasqua
 *
 * @author Luca
 */
class Application_Model_Pasqua {
 
    private static $aM = array(22, 22, 23, 23, 24, 24);
    private static $aN = array(2, 2, 3, 4, 5, 5);
 
    /**
     * Pasqua::get()
     * 
     * Il metodo get restituise un array 
     * contenente il mese ed il giorno 
     * della Pasqua riferita all'anno 
     * passato al metodo attraverso $year
     *  
     * @param integer $year
     * @return array
     */
    public static function get($year) {
 
        $a = $year % 19;
        $b = $year % 4;
        $c = $year % 7;
 
        $aIndex= floor($year/100)-15;
 
        $d = (19 * $a + self::$aM[$aIndex]) % 30;
        $e = (2 * $b + 4 * $c + 6 * $d + self::$aN[$aIndex]) % 7;
 
        $day = 22 + $d + $e;
        $month = 3;
 
        if ($day > 31) {
            $month = 4;
            $day -= 31;
        }
 
        /**
         * Eccezioni:
         * - Se la data risultante dalla formula è il 26 aprile, 
         *   allora la Pasqua cadrà il giorno 19 aprile;
         * - Se la data risultante dalla formula è il 25 aprile 
         *   e contemporaneamente d = 28, e = 6 e a > 10, 
         *   allora la Pasqua cadrà il 18 aprile. 
         */
        if ($month == 4 && $day == 26) {
            $day = 19;
        } elseif ($month == 4 && $day == 26 && $d == 28 && $e == 6 && $a > 10) {
            $day = 18;
        } 
        
        if( $day < 10 ) $day = '0'.$day;
        if( $month < 10 ) $month = '0'.$month;
        return array('day'=>$day,'month'=>$month);
 
    }
    
    /**
     * 
     * @param type $years
     * @return type unixTtimestamp
     */
    public static function pasquetta($years) {
        $pasqua = self::get($years);
        $pasqua = new DateTime($years .'-'.$pasqua['month'].'-'.$pasqua['day']);
        $pasqua->add(new DateInterval('P1D'));
        $pasquetta = $pasqua->getTimestamp();  
        return $pasquetta;
    }
    
    
    
    
    
    
}