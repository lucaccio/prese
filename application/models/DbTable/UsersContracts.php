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
class Application_Model_DbTable_UsersContracts extends Prisma_Db_Table_Abstract {
    
    
    protected $_name = 'users_contracts';
    
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Contratto' => array(
            'columns'           => 'contratto_id',
            'refTableClass'     => 'Application_Model_DbTable_Contratti',
            'refColumns'        => 'contratto_id' 
        ),
        'User' => array(
            'columns'           => 'user_id',
            'refTableClass'     => 'Application_Model_DbTable_Users',
            'refColumns'        => 'user_id' 
        ),
        'Details' => array(
            'columns'           => 'contratto_id',
            'refTableClass'     => 'Application_Model_DbTable_ContrattiDetails',
            'refColumns'        => 'contratto_id' 
        ),
    );
        
    public function __construct() {
      $this->_db = Zend_Registry::get('db');
    }
 
    /**
     * 
     * @todo da fare: ( (stop IS NULL) OR (stop = '0000-00-00')
     * 
     * @param type $uid
     * @param type $date
     * @return type
     */
    public function userGetContractsByDate($uid, $date)
    {
        $sqla = " SELECT * FROM `users_contracts` "
            . "WHERE (DATE_FORMAT('$date','%Y-%m') "
            . "BETWEEN DATE_FORMAT(start,'%Y-%m') "
            . "AND  ( IF( stop IS NULL,DATE_FORMAT('2099-12-31','%Y-%m'),DATE_FORMAT(stop,'%Y-%m') ) ) ) "
            . "AND user_id='$uid' "
            . "ORDER BY start DESC ";
        
       // print_r($sql);
        
        
        $sql = $this->select()
            ->where("DATE_FORMAT('$date','%Y-%m') BETWEEN DATE_FORMAT(start,'%Y-%m') AND  ( IF( stop IS NULL,DATE_FORMAT('2099-12-31','%Y-%m'),DATE_FORMAT(stop,'%Y-%m')))")
            ->where('user_id = ?', $uid)
            ->order("start DESC");
             //print_r($sql->__toString());
        $rs = $this->fetchAll($sql);
        return $rs;
         
    }
    
}
