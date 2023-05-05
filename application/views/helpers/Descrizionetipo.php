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
class Zend_View_Helper_Descrizionetipo {
     
    
    public function descrizionetipo($id) {
               
        $tipo = new Application_Model_TipologiaMapper();
        $id = (int)$id;
        
        if($id !== 0) {
             
            $tipo = $tipo->find($id);
            //return $tipo->getDescrizione();
            $desc = $tipo->getDescrizione();
            if( (null == $desc) || ('' == $desc)  ) {
                return 'Non Disp.';
            } else {
                return $desc;
            }
            
        } else {
            return 'INDEFINITO';
        }
        
        
    }
    
    
    
    
    
}

 
