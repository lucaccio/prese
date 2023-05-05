<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StampaMese
 *
 * @author Luca
 */
class Zend_View_Helper_StampaMese {
    
    
    private $mesi = array(
                    '1' => 'Gennaio',
                    '2' => 'Febbraio',
                    '3' => 'Marzo',
                    '4' => 'Aprile',
                    '5' => 'Maggio',
                    '6' => 'Giugno',
                    '7' => 'Luglio',
                    '8' => 'Agosto',
                    '9' => 'Settembre',
                    '10' => 'Ottobre',
                    '11' => 'Novembre',
                    '12' => 'Dicembre'
                );
    
    
    public function stampaMese($mese) {
        return $this->mesi[(int)$mese];
    }
    
    
     
}

 
