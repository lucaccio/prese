<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of primanota
 *
 * @author Luca
 */
class Application_Model_DbTable_Primanota extends Zend_Db_Table_Abstract {
 
    protected $_name = 'primanota';
    
    protected $_primary = 'primanota_id';
    
    
    
    public function __construct(){
        $this->_db = Zend_Registry::get('db');
    }
    
    public function findBySostituzioneId($id) {
        
        $sql = $this->select()
                    ->where('sostituzione_id  = ?', $id);
        $rows = $this->fetchAll($sql);
        return $rows;
        
    }
    
    public function findBySostitutoId() {
        
    }
    
    public function save($data) {
        
        if(is_array($data)) {
            
            if(isset($data['primanota_id'])) {
                $id = $data['primanota_id'];
                unset($data['primanota_id']);
                $where[] = $this->_db->quoteInto('primanota_id = ?', $id);
                
                return parent::update($data, $where);
            } else {
                return parent::insert($data);
            }
        }
    }
    
    /**
     * 
     * @param type $values
     */
    public function findByCompoundKey($values) {
        
        $sql = $this->select();
        if(is_array($values)) {
            foreach($values as $k => $v) {
                $sql->where($k . ' = ?', (int)$v );
            }
        }
                
        $rows = $this->fetchAll($sql);
        
        return $rows;
        
    }
    
    public function sommaCassa($sostituzione_id) {
        $sql = $this->select()
                    ->from($this->_name, array('totale'=> 'SUM(cassa)'))
                    ->where('sostituzione_id = ?', $sostituzione_id)
            
            ;
         
        $row = $this->fetchRow($sql);
        //echo $sql;
        return $row->totale;
         
    }
    
    /**
     * 
     * @param type $sostituzione_id
     */
    public function deleteBySostituzione($sostituzione_id) {
        $where = $this->getAdapter()->quoteInto('sostituzione_id = ?', $sostituzione_id);
        return parent::delete($where); 
    }
    
    public function delete($primanota_id) {
        $where = $this->getAdapter()->quoteInto('primanota_id = ?', $primanota_id);
        return parent::delete($where); 
    }
    
}

 
