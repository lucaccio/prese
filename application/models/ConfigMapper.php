<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConfigMapper
 *
 * @author Luca
 */
class Application_Model_ConfigMapper extends Prisma_Mapper_Abstract {
    
 
        
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Config';
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
     * @param type $user_id
     */
    public function findByUser($user_id) {
        $id =  (int)($user_id);
        $config = new Application_Model_Config();
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $year
     * @return type
     */
    public function findByYear($user_id, $year) {
        return $this->getDbTable()->findByYear($user_id, $year);
    }
       
    /**
     * 
     * @param type $user_id
     * @param type $year
     */
    public function getGiorniFerieDisponibili($user_id, $year) {
        
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $year
     */
    public function getResiduoFerieDisponibili($user_id, $year) {
        
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $year
     */
    public function getRiportoFerieDisponibili($user_id, $year) {
        
    }
    
    
   
    
    
    
}

 
