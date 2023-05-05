<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsersContracts
 *
 * @author Luca
 */
class Application_Model_DbTable_UsersConfigs extends Prisma_Db_Table_Abstract {


    /**
     * @var string
     */
    protected $_name = 'users_configs';

    /**
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @var array
     */
    protected $_referenceMap    = array(
        'User' => array(
            'columns'           => 'user_id',
            'refTableClass'     => 'Application_Model_DbTable_Users',
            'refColumns'        => 'user_id' 
        ),
    );

    /**
     * Application_Model_DbTable_UsersConfigs constructor.
     * @throws Zend_Exception
     */
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }


    /**
     * @param array $values
     */
    public function insertOrUpdate(array $data) {

        $uid   = $data['user_id'];
        $newUserValues = $data['user_values'];

        $found = $this->findByUser($uid);

        if($found) {
           //echo "<p>#####AGGIORNO $uid##########</p>";
            $where = $this->getAdapter()->quoteInto('user_id = ?', $uid);
            $oldUserValues = json_decode($found->user_values, true);
            //Zend_Debug::dump($oldUserValues);
            //Prisma_Logger::logToFile($oldUserValues, true, 'migrazione');
            if(is_array($oldUserValues)) {
                $newUserValues = array_merge($oldUserValues, $newUserValues);
            }

            $encodedUserValues = json_encode($newUserValues);
            return parent::update(array('user_values' => $encodedUserValues), $where);
        } else {
            $data['user_values'] = json_encode($newUserValues);
            return parent::insert($data);
        }
    }


    /**
     * @param $uid
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findByUser($uid) {
        $sql = $this->select()
            ->where("user_id = ?", $uid);
        $row = $this->fetchRow($sql);
        return $row;
    }

    
}
