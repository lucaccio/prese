<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Errors
 *
 * @author Luca
 */
class Application_Model_DbTable_Errors  extends Zend_Db_Table_Abstract {
     
    
    protected $_name = 'errors';
    
    protected $_primary = 'id';
    
    public function __construct() 
    {
        $this->_db = Zend_Registry::get('db');
    }
    
    
}

 
