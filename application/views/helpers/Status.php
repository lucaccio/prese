<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Status
 *
 * @author Luca
 */
class Zend_View_Helper_Status {
 
    public function status($id) {
        
        $status = array(
            
            '0' => 'In lavorazione',
            '1' => 'Accettato',
            '2' => 'Accetato senza sostituto',
            '3' => 'Rifiutato',
            '4' => 'Annullato'          
            
        );
        $id = (int)$id;
        if(array_key_exists($id, $status)) {
            return $status[$id];
        } else {
            
        }
    }
    
}
