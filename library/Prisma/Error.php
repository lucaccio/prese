<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Error
 *
 * @author Luca
 */
class Prisma_Error {
  
    
    static function insert($msg) {
        
        $db = new Application_Model_DbTable_Errors();
        $data = array(
                'error' => $msg
             );
        $db->insert($data);
    }
    
    
    
    
    
}

 
