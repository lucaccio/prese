<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StruttureMapper
 *
 * @author Luca
 */
class Application_Model_StruttureMapper extends Prisma_Mapper_Abstract {
    
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Strutture';
    }
         
    public function insert($data) {
        return $this->getDbTable()->insert($data);
    }
    
    public function fetchAll() {
        return $this->getDbTable()->fetchAll();
    }
    
    public function fetchAllToJson() {
        $rows = $this->getDbTable()->fetchAll();
        return Zend_Json::encode($rows);
    }
    
    public function update($data, $where) {
        return $this->getDbTable()->update($data, $where);
    }
    
    public function find($id) {
        $row = $this->getDbTable()->find($id);
        if($row->count() > 0) {
            return $row->current();
        }
        return false;
    }
    
    
    
    
    
}

 
