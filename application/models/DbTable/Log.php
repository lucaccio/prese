<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Log
 *
 * @author Luca
 */
class Application_Model_DbTable_Log extends Zend_Db_Table_Abstract {
    
    protected $_name = 'log';
    
    protected $_primary = 'log_id';
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }
    
    public function add($data) {
        parent::insert($data);
    }
    
    
    
}