<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsersResidui
 *
 * @author Luca
 */
class Application_Model_DbTable_UsersResidui extends Zend_Db_Table_Abstract {
     
    protected $_name = 'users_residui';
    
    protected $_primary = 'id';
    
     //protected $_rowClass = 'Application_Model_DbTable_UsersResidui_Row';
    
    public function __construct() {
      $this->_db = Zend_Registry::get('db');
    }
    
    public function update(array $data, $whereArr) {
        $where = array();
        if(is_array($whereArr)) {
            foreach($whereArr as $k => $v) {
                $where[] = $this->_db->quoteInto($k . ' = ?', $v);
            }
        }
        //Prisma_Logger::logToFile( $data );
        //Prisma_Logger::logToFile( $where );
        //$profiler = Zend_Registry::get('profiler');
       // $query = $profiler->getLastQueryProfile();
        //Prisma_Logger::logToFile( $query->getQuery() );
        return parent::update($data, $where);
       // $query = $profiler->getLastQueryProfile();
       // Prisma_Logger::logToFile( $query->getQuery() );
    }
    
    /**
     * 
     * @param type $uid
     * @param type $options
     * @return type
     */
    public function findByUser( $uid, $options = null ) {
        
        !isset($year) ? $year = date('Y') : $year;
        
        $sql = $this->select()
                ->where('user_id = ?', (int)$uid)
                ->where('year = ?', $year);
        if($options) {
            foreach ($options as $k => $v) {
                $sql->where($k .' = ?', $v);
            }
        }
        //Prisma_Logger::log( $sql->__toString() ) ;
        $rowset = $this->fetchAll($sql);
         
        return $rowset;
    }
    
    /**
     * Cerca per utente e anno un particolare tipo di residuo
     *  
     * @param type $type
     * @param type $user_id
     * @param type $year
     */
    public function findTypeByUserAndYear($type, $user_id, $year) {
        
        $sql = $this->select();
        $sql->where('user_id = ?', $user_id);
        $sql->where('year = ?', $year);
        $sql->where('tipo = ?', $type);
        //Prisma_Logger::log($sql);
        $rows = $this->fetchAll($sql);
        if($rows->count() > 0 ) {
            return $rows->current();
        }
        return false;
    }
    
    
}
    
    
    
    