<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RichiesteDettagli
 *
 * @author Luca
 */
class Application_Model_DbTable_RichiesteDettagli extends Zend_Db_Table_Abstract {
    
    
    protected $_name = 'richieste_dettagli';
    
    protected $_primary = 'id';
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }
    
    public function findByRequest($rid)
    {
        $sql = $this->select()->where('richiesta_id = ?', $rid);
        return $this->fetchAll($sql);
    }
    
    
    
    
}

 
