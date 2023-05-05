<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContrattiDetails
 *
 * @author Luca
 */
class Application_Model_DbTable_ContrattiDetails extends Zend_Db_Table_Abstract {
     
    protected $_name    = 'contratti_details';
    
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Details' => array(
            'columns'           => 'contratto_id',
            'refTableClass'     => 'Application_Model_DbTable_Contratti',
            'refColumns'        => 'contratto_id',
            'onDelete'          => self::CASCADE
        )
    );
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    } 
    
}

 
