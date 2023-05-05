<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FerieMapper
 *
 * @author Zack
 */
class Application_Model_FerieMapper extends Prisma_Mapper_Abstract  {
     
    
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Config';
    } 
    
    public function findByAnno($anno) {
        
    }
    
    public function find($id) {
        return $this->getDbTable()->find($id)->current();
    }
    
    public function findByTipo($tipo, $id = null, $year = null, $create = false) {
        return $this->getDbTable()->findByTipo($tipo, $id, $year, $create);
    }
    
    
    public function update($data, $where) {
        return $this->getDbTable()->update($data, $where);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
}

 
