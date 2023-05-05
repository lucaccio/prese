<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogMapper
 *
 * @author Luca
 */
class Application_Model_LogMapper extends Prisma_Mapper_Abstract {
    
 
        
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Log';
        return $this;
    }   

    
    public function addEvent($data) {
        $this->getDbTable()->add($data);    
    }
          
    
    
    
}
