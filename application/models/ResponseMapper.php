<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResponseMapper
 *
 * @author Luca
 */
class Application_Model_ResponseMapper extends Prisma_Mapper_Abstract {
 
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Response';
    }
        
    public function fetchAll($where = null) {
        return $this->getDbTable()->fetchAll($where);
    }
    
    public function insert($data) {
        return $this->getDbTable()->insert($data);
    }
    
    public function delete($where) {
        return $this->getDbTable()->delete($where);
    }
    
}
