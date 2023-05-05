<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ActiveUser
 *
 * @author Luca
 */
class Zend_View_Helper_ActiveUser {
   
    
    public function activeUser($active) {
         
       $active = (int)$active;
        
       switch ($active) {
            
        case 0:
            return 'CANCELLATO';
            break;
        case 1:
            return 'ATTIVO';
            break;
        default:
            return 'INDEFINITO';
            break;
       }
            
    }
    
    
}
