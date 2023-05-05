<?php

/**
 * Created by PhpStorm.
 * User: Luca
 * Date: 25/05/2016
 * Time: 16.50
 */
class Application_Model_DbTable_Giro extends Zend_Db_Table_Abstract {

    protected $_name    = 'giro';

    protected $_primary = 'giro_id';


    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }

    protected $_dependentTables = array(
        'Application_Model_DbTable_GiroDetails' ,
    );


    /**
     * @param $data
     * @return array
     */
    public function findByDate($data) {
        $sql = $this->select();
        $sql->where('YEAR(giorno)  = ?', $data['year']);
        $sql->where('MONTH(giorno) = ?', $data['month']);
        $result = $this->fetchAll($sql) ;
        $events = array();

        foreach($result as $row) {
            // $events[] = array
            $events[$row->giorno] = array(
                'giorno'       => $row->giorno,
                'giro'      => 1
            );
        }
        return $events;
    }

    /**
     * @param $date
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function findDate($date) {
        $sql = $this->select();
        $sql->where('giorno = ?', $date);
        $result = $this->fetchAll($sql) ;
        if(!$result->count()) {
            return false;
        }
        return $result->current();
    }


    




}