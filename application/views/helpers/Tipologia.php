<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tipologia
 *
 * @author Luca
 */
class Zend_View_Helper_Tipologia {
     
    
    public function tipologia($id) {
        
        $id = (int)$id;
        if($id !== 0) {
            $tipo = new Application_Model_TipologiaMapper();
            $tipo = $tipo->find($id);
            return $tipo->descrizione;
        } else {
            return 'INDEFINITO';
        }
        
        
    }
    
    
    
    
    
}

 
