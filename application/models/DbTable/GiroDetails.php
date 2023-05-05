<?php

/**
 * Created by PhpStorm.
 * User: Luca
 * Date: 25/05/2016
 * Time: 16.50
 */
class Application_Model_DbTable_GiroDetails extends Zend_Db_Table_Abstract {

    protected $_name    = 'giro_details';

    protected $_primary = 'gd_id';


    protected $_referenceMap    = array(
        'Details' => array(
            'columns'           => 'giro_id',
            'refTableClass'     => 'Application_Model_DbTable_Giro',
            'refColumns'        => 'giro_id',
            'onDelete'          => self::CASCADE
        )
    );

    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

}