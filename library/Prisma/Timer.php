<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Timer
 *
 * @author Luca
 */
class Prisma_Timer {
    
    
    /**
     *
     * @var array 
     */
    protected static $_start = array();   
    
    /**
     *
     * @var type 
     */
    protected static $_stop = array();   
    
    /**
     *
     * @var type 
     */
    protected static $_elapsed = array();   
    
    
    /**
     * 
     * @param int $key
     */
    public static function start($key = null)
    {
        if(!$key) {
            $key = 0;
        }  
        $start_time = explode(' ', microtime());
        self::$_start[$key] = $start_time[1] + $start_time[0];
        
    }
    
    /**
     * 
     * @param int $key
     */
    public static function stop($key = null)
    {
        if(!$key) {
            $key = 0;
        }
        $stop_time = explode(' ', microtime());        
        self::$_stop[$key]  = $stop_time[1] + $stop_time[0];
        self::$_elapsed[$key]  = self::$_stop[$key]  - self::$_start[$key] ;
         
        
    }
     
    /**
     * 
     * @param type $precision
     * @param type $with_text
     * @param string $text
     * @param int $key
     * @return string
     */
    public static function elapsedTime($precision = 5, $with_text = false, $text = null, $key = null)
    {
       if(!$key) { $key = 0 ; }
       $elapsed_time = round( self::$_elapsed[$key] , $precision);
       if($with_text) {
           if(!$text) {
               $text = "Elapsed Time: ";
           }
           return  $text . $elapsed_time ;
       } 
       return $elapsed_time ;
    }        
    
}
