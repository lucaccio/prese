<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mese
 *
 * @author Luca
 */
class Zend_View_Helper_Mese {
   
 
    protected $_mesi = array(
                "1" => "Gennaio", 
                "2" => "Febbraio",
                "3" => "Marzo",
                "4" => "Aprile",
                "5" => "Maggio",
                "6" => "Giugno",
                "7" => "Luglio",
                "8" => "Agosto",
                "9" => "Settembre",
                "10" => "Ottobre" ,
                "11" => "Novembre",
                "12" => "Dicembre"

    );
 
    
    public function mese($num) { 
     
        $num = (int)$num;
        if($num >= 1 or $num < 13) {
            return $this->_mesi[$num];
        }
        
    }
    
    
    
    
}

 
