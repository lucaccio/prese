<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SedeUser
 *
 * @author Luca
 */
class Zend_View_Helper_SedeUser {
     
    
    public function sedeUser($user_id) {
      
        if($user_id != 0){
            $db = new Application_Model_SediMapper();
            
             
            
            $sede = $db->findByUser($user_id);
            if(is_object($sede)) {
                return ucfirst($sede->citta);
            } else {
                return 'NO SEDE';
            }
        } else {
            return 'NO SEDE';
        }
    }
    
    
}
