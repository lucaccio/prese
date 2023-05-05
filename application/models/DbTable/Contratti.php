<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contratto
 *
 * @author Luca
 */
class Application_Model_DbTable_Contratti extends Zend_Db_Table_Abstract {
    
   
    protected $_name    = 'contratti';
    
    protected $_primary = 'contratto_id';
  
    protected $_dependentTables = array(    
                'Application_Model_DbTable_ContrattiDetails' , 
                'Application_Model_DbTable_UsersContracts'
              );
    
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }  
    
    public function fetchAll($where = null, $order = null, $count = null, $offset = null) {
        return parent::fetchAll($where, $order, $count, $offset);
    }
    
    public function findBy($where = null, $order = null, $count = null, $offset = null) {
        //$rs = parent::fetchAll($where, $order, $count, $offset);
        $sql = $this->select();
        if($where) {
            $sql->where("descrizione = ?", $where);
        }
        $rs = $this->fetchAll($sql);
        //Prisma_Logger::getQuery();
        return $rs;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function findByUser($id)
    {
        $sql = $this->select() 
                ->where("user_id = ?", (int)$id);
        $rs = $this->fetchAll($sql);
        return $rs;
    }
    
    
    public function delete($where) {
        return parent::delete($where);
    }
    
}