<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Level
 *
 * @author Luca
 */
class Application_Model_DbTable_Level extends Zend_Db_Table_Abstract {
    
    protected $_name = 'level';
    
    protected $_primary = 'level_id';
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }
    /*
    public function fetchAll() {
        return $this->getDbTable()->fetchAll();
    }
    */
}

 
