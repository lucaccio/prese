<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Residui
 *
 * @author Luca
 */
class Application_Model_DbTable_Residui extends Zend_Db_Table_Abstract {
    
     
    protected $_name    = "richieste_residui";
    
    protected $_primary = "id";
     
    
    
    
    public function __construct(){
        $this->_db = Zend_Registry::get('db');
    }
    
    
    /**
     * Restituisce la quantita 
     * @param type $where
     */
    public function getAssignedQuantity($where) 
    {
        $sql = $this->select()->from($this->_name, array('assigned'));
        foreach($where as $k => $v) {
            $sql->where("$k = ?", $v);
        }
        $row = $this->fetchRow($sql);
        if(!$row) {
            $assigned = 0;
            $this->createNewRow($where);
            return $assigned;
        }
        return $row->assigned;
    }
    
    public function getAssignedQuantityByUser($where) 
    {
        $sql = $this->select()->from($this->_name, array('assigned_by_user_id'));
        foreach($where as $k => $v) {
            $sql->where("$k = ?", $v);
        }
        $row = $this->fetchRow($sql);
        if(!$row) {
            $assigned_by_user_id = 0;
            $this->createNewRow($where);
            return $assigned_by_user_id;
        }
        return $row->assigned_by_user_id;
    }
    
    
    /**
     * Crea nuova riga, se non esiste
     * @param type $data
     */
    public function createNewRow($data)
    {
        return $this->insert($data);
    }
    
    
    
    
    
}


