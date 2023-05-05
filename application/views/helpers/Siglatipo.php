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
class Zend_View_Helper_Siglatipo {
     
    
    public function siglatipo($id) {
               
        $tipo = new Application_Model_TipologiaMapper();
        $id = (int)$id;
        
        if($id !== 0) {
             
           $tipo = $tipo->findById($id);
           
           
           //print_r($tipo);
           
           return $tipo->getSigla() ;
            
        } else {
            return 'SOST';
        }
        
        
    }
    
    
    
    
    
}