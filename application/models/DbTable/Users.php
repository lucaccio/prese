<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Users
 *
 * @author Luca
 */
class Application_Model_DbTable_Users extends Zend_Db_Table_Abstract {
     
    protected $_name = 'users';
    
    protected $_primary = 'user_id';

    protected $_dependentTables = array(
        'Application_Model_DbTable_UsersContracts',
        'Application_Model_DbTable_UsersConfigs'
    );
    
    public function __construct() {
      $this->_db = Zend_Registry::get('db');
    }
       
    public function save($data) {
        return parent::insert($data);       
    }

    /**
     * 27/12/2019
     *  cerca in base all'ID sede
     * 
     */
    public function findByIDSede($IDsede) {        
        $sql = $this->select()->where('sede_id = ?', $IDsede)->where('active = ?', 1);
        $row = $this->fetchRow($sql);
        return $row;
    }
    
    /**
     * 
     */
    public function findByUsername($username) {
        
        $sql = $this->select()
                ->where('username = ?', $username);
             //   ->where('active = ?', 1)
             //   ;
      
        $row = $this->fetchRow($sql);
        return $row;
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $secret
     * @return boolean
     */
    public function findSecret($user_id, $secret) 
    {
        $sql = $this->select()
                ->where('password = ?', $secret)
                ->where('user_id = ?', $user_id)
                ;
        $row = $this->fetchRow($sql);
        
        if(count($row) == 1) { return true; }
        
        return false;
    }
    
    /**
     * 
     * @param type $active
     * @return type
     */    
    public function getUsers($active = null) {
                    
        $sql = $this->select()
                ->from( array('u' => 'users') );
        $sql->setIntegrityCheck(false);         
        $sql->join( array('l' => 'level'), 'u.level_id = l.level_id');
                
        if($active === 0 || $active === 1) {
            $sql->where('active = ?', $active);
            $sql->order('cognome ASC');
        } else {
            $sql->order('active DESC');
        }
                  
        //echo $sql;
        return $this->fetchAll($sql);
    }
    
    /**
     * 
     * @param type $id
     */
    public function delete($id) {
        $id = (int) $id;
        $row = count($this->find($id));
        if( (1 == (int) $row) ) {
            $where = $this->getAdapter()->quoteInto('user_id = ?', $id);
            $data = array(
                'active' => 0
            );
            parent::update($data, $where);
        }
      
    }
    
    /**
     * 
     * @param type $id
     */
    public function attiva($id) {
        $id = (int) $id;
        $row = count($this->find($id));
               
        if( (1 == (int) $row) ) {
            $where = $this->getAdapter()->quoteInto('user_id = ?', $id);
            $data = array(
                'active' => 1
            );
            parent::update($data, $where);
        }
      
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getUserRoleById($user_id) {
        
        $sql = $this->select()->from(array('u' => 'users'));
        $sql->setIntegrityCheck(false);
        $sql->join(array('l' => 'level'),
                    'u.level_id = l.level_id');
        $sql->where('u.user_id = ?', $user_id);
         
        $rowset = $this->fetchAll($sql);
        $row    = $rowset->current();

        return $row->descrizione;
        
    }
    
    /**
     * 
     * @param type $inizio
     * @param type $fine
     * @return type
     */
    public function getSostitutiLiberi($inizio, $fine) {
        
        #------------------------------ 
        # ATTENZIONE 
        # IL $SUB RIGUARDA NON SOLO IL SOSTITUTO_ID MA ANCHE L'USER_ID INFATTI DEVO ASSICURARMI CHE IN UNA CERTA DATA
        # IL SOSTITUTO NON SIA GIA OCCUPATO, MA ANCHE CHE NON SIA LUI STESSO IN FERIE   
        # ------------------------------   
        $sub = $this->select();
        $sub->setIntegrityCheck(false);
        $sub->from('assenze', 'sostituto_id')
                ->where('dateStart <= ?', $fine)
                ->where('dateStop >= ?', $inizio);

        $sub1 = $this->select();
        $sub1->setIntegrityCheck(false);
        $sub1->from('assenze', 'user_id')
                ->where('dateStart <= ?', $fine)
                ->where('dateStop >= ?', $inizio);               

        $select = $this->select()
                       ->where('user_id NOT IN ?', $sub)
                       ->where('user_id NOT IN ?', $sub1)
                       ->where('level_id = ?', 2)
                       ->where('active = ?', 1)
                       ->order('cognome');

        $rows = $this->fetchAll($select);
        return $rows;
    }
    
    /**
     * restituisce tutti gli utenti registrati (eccetto admin se richiesto)
     * @param type $admin
     */
    public function getAllUsers($admin = false, $options, $assunzione = null, $order = null) {
        
        $sql = $this->select();
        
        if(false == $admin) {
            $sql->where('admin = ?', 0);
            $sql->where('active = ?', 1);
            $sql->where('developer = ?', 0);
        }
        if(is_array($options)) {
            foreach($options as $k => $v) {
                $sql->where($k . ' = ?', $v);
            }
        }
        
        if(null !== $assunzione) {
            $y = $assunzione['year'];
            $m = $assunzione['month'];
            
           // $sql->where('YEAR(data_assunzione) <= ?',  $y);
            //$sql->where('MONTH(data_assunzione) <= ?', $m);
            $last = date('t', mktime(0,0,0,$m, 1, $y));
            $sql->where('data_assunzione <= ?', $y .'-'.$m. '-'.$last);
            
        }
        
        if(null != $order) {
            $sql->order($order);
        } else {
           $sql->order('cognome ASC'); 
           $sql->order('nome ASC'); 
        }
        //Prisma_Logger::logToFile( $sql );
        $rows = $this->fetchAll($sql);
       // print_r($rows);
        return $rows;
        
    }
    
    /**
     * 
     * @param type $data
     * @param type $where
     * @return type
     */
    public function update(array $data, $where) {
        $where = $this->_db->quoteInto('user_id = ?', $where);
        return parent::update($data, $where);
    }
    
    
    /**
     * 
     * @param type $date
     * @return type
     */
    public function getUtentiAssunti($limite) {
        $sql = $this->select();
        $sql->where('admin = ?', 0);
        $sql->where('developer = ?', 0);
        $sql->where('active = ?', 1);
        $sql->where('data_assunzione <= ?',   $limite['YEAR'] .'-'.$limite['MONTH'].'-'.'31');
        $sql->order('cognome ASC');
        //echo $sql;
        $rows = $this->fetchAll($sql);
        return $rows;
    }
    
    
    
    
    
}