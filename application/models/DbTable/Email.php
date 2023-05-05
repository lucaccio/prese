<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Email
 *
 * @author Luca
 */
class Application_Model_DbTable_Email extends Zend_Db_Table_Abstract {
   
   
    protected $_name    = 'email';
    
    protected $_primary = 'email_id';
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    } 
    
    
    
    
}

 
