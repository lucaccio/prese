<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Date
 *
 * @author Luca
 */
class Prisma_Utility_Date {
     
    
     /**
     * 
     *  Crea un array con il range di date
     * 
     * @param DateTime $start
     * @param DateTime $stop
     * @param type $step
     * @param type $multiarray
     * @return array
     */
    public static function createRangeOfDates($start, $stop, $step = null, $multiarray = false)
    {
        if($step == null) {
            $step = "P1D";
        }
        $val = array(); 
        $interval = new DateInterval($step);  
        $start    = new DateTime($start);
        $stop     = new DateTime($stop);
        $stop->add(new DateInterval('P1D'));
        $period   = new DatePeriod($start, $interval, $stop);
        foreach ( $period as $dt )
        {
            if($multiarray) {
                $value = array(
                    'iso8610'     => $dt->format( "Y-m-d" ),
                    'day_of_week' => strtolower( $dt->format('D') )
                );
                $val[] = $value;
            } else {
                $val[] = $dt->format( "Y-m-d" );
            }
             
        }
        //Prisma_Logger::logToFile(json_encode($val));
        return $val;
    }
    
    /**
     * 
     * @param type $date
     * @return string
     */
    public static function getMonth($date)
    {
        if(is_string($date)) {
            $date = trim($date);
            if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)){
                $array = explode("-", $date);
                
            }
            return  $array[1] ;
        }
    }
    
    
    
}
