<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContrattoMapper
 *
 * @author Luca
 */
class Application_Model_ContrattiMapper extends Prisma_Mapper_Abstract {
    
 
        
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Contratti';
        return $this;
    }  
    
    public function fetchAll()
    {
        return $this->getDbTable()->fetchAll();
    }
    
    public function findBy($where = null) {
        $result = $this->getDbTable()->findBy($where);
        return  $result;
    }
    
    public function find($id) {
        $rows = $this->getDbTable()->find($id);
                
        if($rows->count() > 0) {
            $row = $rows->current();
            $obj = new Application_Model_Contratto($row);
            return $obj;           
        } else /*restituisco un oggetto vuoto*/ {
            $obj = new Application_Model_Contratto();
            return $obj; 
        }
    }
    
    public function delete($where) {
        return $this->getDbTable()->delete($where);
    }
    
    public function findByUser($uid)
    {
        $rows = $this->getDbTable()->findByUser($id);
    }
    
    
    
    
}
