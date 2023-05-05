<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FormatDate
 *
 * @author Luca
 */
class Zend_View_Helper_FormatDate {
   
    
    public function formatDate($date, $format='d-m-Y', $translate = true) {
        
        $date = new DateTime($date);
        //return $date->format($format);
        return $date->format($format);
        
        /*
        if($translate) {
            $pattern = array(
                "January" => "Gennaio"
            );
            
            while( list($search, $replace) = each($pattern) ) {
                $content = preg_replace($search, $replace, $x);
            }
            return $content;
            
        }
        */
        
        
        
        // INSTALLARE IL LOCALE NEL SERVER PER FAR FUNZIONARE IL CODICE SOTTOSTANTE
        //setlocale(LC_TIME, 'it_IT');
        //echo strftime("%e %B %G");
        
        
    }
    
    
}

 
