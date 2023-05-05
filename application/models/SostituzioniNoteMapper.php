<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SostituzioniNoteMapper
 *
 * @author Luca
 */
class Application_Model_SostituzioniNoteMapper extends Prisma_Mapper_Abstract {
  
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_SostituzioniNote';
        return $this;
    }
    
    public function insert($data){
        return $this->getDbTable()->insert($data);
        /*
        if(!$status) {
            throw new Exception('errore inserimento nel db');
        } 
        return $status;
        */
    }
    
}

 