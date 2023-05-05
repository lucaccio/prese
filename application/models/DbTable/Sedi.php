<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sedi
 *
 * @author Luca
 */
class Application_Model_DbTable_Sedi extends Zend_Db_Table_Abstract {
    
    
    protected $_name = 'sedi';
    
    protected $_primary = 'sede_id';
    
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }
    
    
    /**
     * 
     * @param type $name
     * @return boolean
     */
    public function findByName($name) {
        $sql = $this->select()
                    ->where('citta = ?', $name)
               ;
        $result = $this->fetchAll($sql);
        if( count($result) > 0 ) {
            return $result->current()->sede_id;
        }
        return false;
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function findByUser($user_id) {
        $name = array('u' => 'users');
        $cond = 'u.sede_id = s.sede_id';
        
        $sql = $this->select()->from(array('s' => $this->_name))
                   ->setIntegrityCheck(false)
                ->join($name, $cond)
                ->where('u.user_id = ?', $user_id);
        
        
        
        $row =  $this->fetchAll($sql )->current();
        return $row;
        
    }
    
      
    public function getAll() {
        $sql = $this->select()->order('citta ASC');
        return parent::fetchAll($sql);
    }
    /*
    public function fetchAll() {
         echo __METHOD__;
        $sql = $this->select()->order('citta ASC');
        return parent::fetchAll();
         
    }
    */
    
    
           
    
    /**
     * 
     * @param array $data
     * @param type $where
     * @return type
     */
    public function update(array $data, $where) {
        $where = $this->_db->quoteInto('sede_id = ?', $where);
       
        return parent::update($data, $where);
    }
            
    
    
    
}