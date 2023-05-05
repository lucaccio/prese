<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ferie
 *
 * @author Zack
 */
class Application_Model_DbTable_Ferie extends Zend_Db_Table_Abstract 
{
    protected $_name = 'ferie';
    
    protected $_primary = 'ferie_id';

    public function __construct() {
        $this-> $this->_db = Zend_Registry::get('db');
    }
}

 
