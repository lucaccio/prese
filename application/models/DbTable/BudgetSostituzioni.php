<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BudgetSostituzioni
 *
 * @author Luca
 */
class Application_Model_DbTable_BudgetSostituzioni extends Zend_Db_Table_Abstract {
     
    protected $_name = 'budgetSostituzioni';
    
    protected $_primary = 'budget_id';

    
    
    public function __construct(){
        $this->_db = Zend_Registry::get('db');
    }
    
    
    public function insert($data) {
        return parent::insert($data);
    }
    
    public function sommaBudget($sostituzione_id) {
        $sql = $this->select()
                    ->from($this->_name, array('totale'=> 'SUM(importo)'))
                    ->where('sostituzione_id = ?', $sostituzione_id)
            
            ;
        //echo $sql;
        $row = $this->fetchRow($sql);
        return $row->totale;
        
        //return $row->totale; 
    }
    
    public function findBySostituzione($id) {
        $sql = $this->select()->where('sostituzione_id = ?', $id);
        //echo $sql;
        return $this->fetchAll($sql);
    }
    
    
    /**
     * 
     * @param type $sostituzione_id
     */
    public function deleteBySostituzione($sostituzione_id) {
        $where = $this->getAdapter()->quoteInto('sostituzione_id = ?', $sostituzione_id);
        return parent::delete($where); 
    }
    
    /**
     * 
     * @param type $budget_id
     */
    public function delete($budget_id) {
        $where = $this->getAdapter()->quoteInto('budget_id = ?', $budget_id);
        return parent::delete($where); 
    }
    
    
    
    
    
}

 
