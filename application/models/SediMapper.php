<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SediMapper
 *
 * @author Luca
 */
class Application_Model_SediMapper extends Prisma_Mapper_Abstract {
 
    
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Sedi';
    }
    
    public function add() {}
    
    public function get() {}
    
    public function view($id) {}
    
    public function getAll() {
        return $this->getDbTable()->getAll();
    }
    
    
    public function findByName($name) {
        return $this->getDbTable()->findByName($name);
    }
    
    public function findByUser($user_id) {
        return $this->getDbTable()->findByUser($user_id);
    }
    
    public function insert($data) {
        return $this->getDbTable()->insert($data);
    }
    
    public function update($data, $where) {
        return $this->getDbTable()->update($data, $where);
    }

    /**
     * @param $sede_id
     * @return Application_Model_Sede
     * @throws Exception
     */
    public function find($sede_id) {
        $rows = $this->getDbTable()->find($sede_id) ;
        if(  $rows->count() > 0  ) {
            $row = $rows->current() ;
            $obj = new Application_Model_Sede($row);
            return $obj;           
        } else /*restituisco un oggetto vuoto*/ {
            $obj = new Application_Model_Sede();
            return $obj; 
        } 
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function fetchAll() {
        return  $this->getDbTable()->fetchAll( );
    }
    
    
    
    
}
