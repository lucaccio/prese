<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResiduiMapper
 *
 * @author Luca
 */
class Application_Model_ResiduiMapper extends Prisma_Mapper_Abstract {
     
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Residui';
    }
    
    public function residuiGetAssignedQuantity($where)
    {
         return $this->getDbTable()->getAssignedQuantity($where);
    }
    
     public function residuiGetAssignedQuantityByUser($where)
    {
         return $this->getDbTable()->getAssignedQuantityByUser($where);
    }
    
}

 