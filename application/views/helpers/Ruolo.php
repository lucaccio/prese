<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ruolo
 *
 * @author Luca
 */
class Zend_View_Helper_Ruolo {
 
    public function ruolo($id) {
    $id = (int)$id;
        if($id !== 0) {
            $utente = new Application_Model_UserMapper();
            $utente = $utente->getRole($id);
            return $utente;
        } else {
            return 'impossibile stabilire il ruolo';
        }
    }
    
}

 
