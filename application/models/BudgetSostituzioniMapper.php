<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BudgetSostituzioniMapper
 *
 * @author Luca
 */
 
class Application_Model_BudgetSostituzioniMapper extends Prisma_Mapper_Abstract {
  
    
    
        public function __construct() {
        $this->_class = 'Application_Model_DbTable_BudgetSostituzioni';
        return $this;
    }   
    
    /**
     * 
     * @param type $values
     * @return type
     */
    public function insert($values) {
        return $this->getDbTable()->insert($values);
    }
    
    /**
     * 
     * @param type $data
     * @param type $where
     * @return type
     */
    public function update($data, $where = null) {
        return $this->getDbTable()->update($data, $where);
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function sommaBudget($id) {
        return $this->getDbTable()->sommaBudget($id);
    }
    
    /**
     * 
     * @param type $sostituzione_id
     */
    public function findBySostituzione($id) {
        return $this->getDbTable()->findBySostituzione($id);
    }
    
    /**
     * 
     * @param type $user_id
     */
    public function findByUser($user_id) {
        
    }
    
    /**
     * 
     * @param type $sostituzione_id
     */
    public function deleteBySostituzione($sostituzione_id) {
        return $this->getDbTable()->deleteBySostituzione($sostituzione_id);      
    }
         
    /**
     * 
     * @param type $budget_id
     * @return type
     */
    public function delete($budget_id) {
        return $this->getDbTable()->delete($budget_id); 
    }
    
    /**
     * 
     * @param type $budget_id
     * @return type
     */
    public function find($budget_id) {
        $result =  $this->getDbTable()->find($budget_id);
        if($result->count() > 0 ) {
            return $result->current();
        }
        return false;
    }
    
    
    
    
}

 