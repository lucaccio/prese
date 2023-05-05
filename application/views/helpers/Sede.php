<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sede
 *
 * @author Luca
 */
class Zend_View_Helper_Sede {
     
    
    public function sede($id) {
    
        if($id != 0){
            $db = new Application_Model_SediMapper();
            $sede = $db->find($id);
            return ucfirst($sede->getCitta());
        } else {
            return 'NO SEDE';
        }
    }
    
    
}

 
