<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utente
 *
 * @author Luca
 */
class Zend_View_Helper_Utente {
     
    
    public function utente($id, $cognome = false, $onlyname=false) {
        
        $id = (int)$id;

        if($id !== 0) {

            $utente = new Application_Model_UserMapper();

            $utente = $utente->find($id);
            

            if($cognome) {
                //26/04/2021      
                if($onlyname) {
                    return ucfirst($utente->getNome())   ;
                }
                return ucfirst(  ucfirst($utente->getCognome() . ' ' . $utente->getNome())  )  ;
            }
            if($onlyname) {
                return ucfirst($utente->getNome())   ;
            }
            return ucfirst($utente->getNome()) . ' ' . ucfirst($utente->getCognome())  ;



        } else {
            //return '...';
            return false;
        }
    }
    
    
    
}

 
