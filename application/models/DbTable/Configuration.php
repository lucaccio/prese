<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Configuration
 *
 * @author Luca
 */
class Application_Model_DbTable_Configuration extends Zend_Db_Table_Abstract {
     
    protected $_name    = 'configuration';
    
    protected $_primary = 'configuration_id';
    
    public function __construct($config = array()) {
        //parent::__construct($config);
        $this->_db = Zend_Registry::get('db');
    }
    

}