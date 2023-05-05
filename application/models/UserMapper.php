<?php

/**
 * Description of UserMapper
 *
 * @author Luca
 */
class Application_Model_UserMapper extends Prisma_Mapper_Abstract {
    
    
    /* statics */
    
    public static function getMeUser($id)
    {
         $me = new self();
         $user = $me->find($id);
         return $user;
    }
    
    
    
    
    
    public function __construct($id = null) {
        $this->_class = 'Application_Model_DbTable_Users';
    }  
    
    
    /**
     * 27/12/2019
     * Restituisce la sede associata, se presente, altrimenti null.
     */
    public function findByIDSede($IDsede) {
        if(!$IDsede) {
            return null;
        } else {
            return $this->getDbTable()->findByIDsede($IDsede);
        }
            
        
    }



    public function save($data) {
        $this->getDbTable()->save($data);
    }
    
    public function findByUsername($username) {
        return $this->getDbTable()->findByUsername($username);
    }
    
    public function elencoUtenti($active = null) {
       // return $this->getDbTable()->getUsers($active);
        return $this->getAllUsers(null, null);
    }
    
    public function cancellaUtente($userId) {
        $id = (int) $userId;
        return $this->getDbTable()->delete($id);
    }
    
    public function attivaUtente($userId) {
        $id = (int) $userId;
        return $this->getDbTable()->attiva($id);
    }
    
    public function getRole($id) {
        return $this->getDbTable()->getUserRoleById($id);
    }
    
    public function getSostitutiLiberi($inizio, $fine)  {
        return $this->getDbTable()->getSostitutiLiberi($inizio, $fine) ;
    }
    
    public function getUser($user_id) {
        return $this->getDbTable()->find($user_id)->current();
    }
    
    /**
     * 
     * @param type $setAdmin
     * @return \Application_Model_User
     */
    public function getAllUsers($admin = false, $options = null, $assunzione = null, $order = null) {
        //Prisma_Logger::log('allUser');
        $users = array();
        $rowset = $this->getDbTable()->getAllUsers($admin, $options, $assunzione, $order);
        foreach($rowset as $k => $row) {            
            $users[] = $this->_createUserModel($row);
        }
        return $users;        
    }    
       
    
    /**
     * 
     * @param type $user_id
     * @return \Application_Model_User
     */
    public function find($uid) {
       // $o = var_export(debug_backtrace(), true);
        //Prisma_Logger::logToFile( $o);
        //Prisma_Logger::logToFile( __METHOD__);
       // Prisma_Logger::logToFile( $uid );
        if(!$uid) {
            throw new Exception( __METHOD__. " -> manca uid");
        }
        $result =  $this->getDbTable()->find($uid) ;
        if(0 == count($result)) {
            return new Application_Model_User(); 
        }
        $row = $result->current();
        return $this->_createUserModel($row);
    }


    /**
     * @param $row
     * @return Application_Model_User
     */
    private function _createUserModel($row) {
        $user = new Application_Model_User();
        $user->setNome($row->nome)
            ->setCognome($row->cognome)
            ->setEmail($row->email)
            ->setUsername($row->username)
            ->setLevel($row->level_id)
            ->setId($row->user_id)
            ->setActive($row->active)
            ->setAssunzione($row->data_assunzione)
            ->setCessazione($row->data_cessazione)
            ->setContratto($row->contratto_id)
            ->setSede($row->sede_id)
            ->setContractsList( $row->findDependentRowSet('Application_Model_DbTable_UsersContracts') )
            ->setConfigs($row->findDependentRowSet('Application_Model_DbTable_UsersConfigs') )
        ;
        return $user;
    }

    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getLevel($user_id) {
        return $this->getDbTable()->find($user_id)->current()->level_id;
    }
    
    
    /**
     * 
     */    
    public function insert($data) {
         return $this->getDbTable()->insert($data);
    }
    
    /**
     * 
     * @param type $data
     * @param type $where
     * @return type
     */
    public function update($data, $where) {
        return $this->getDbTable()->update($data, $where);
    }
    
    public function findSecret($user_id, $secret) {
        return $this->getDbTable()->findSecret($user_id, $secret);
    }
    
    
    public function getUtentiAssunti($date) {
        $users = array();
        $table =  $this->getDbTable()->getUtentiAssunti($date);
        
        foreach($table as $k => $row) {
            $user = new Application_Model_User();
            $user->setNome($row->nome)
                 ->setCognome($row->cognome)
                 ->setEmail($row->email)
                 ->setUsername($row->username)
                 ->setLevel($row->level_id)
                 ->setId($row->user_id)
                 ->setActive($row->active)
                 ->setAssunzione($row->data_assunzione)
                 ->setCessazione($row->data_cessazione)   
                 ->setContratto($row->contratto_id)
                 ->setSede($row->sede_id)
            ;
            
            //print_r($user);
            
            $users[] = $user;
        }
        return $users;
        
    }
    
    
    public function userGetResidui($where = null) {
        
    }
    
    /**
     * Gateway for table UsersContracts
     * 
     * @param type $uid
     * @param type $date
     * @return type
     */
    public function userGetContractsByDate($uid, $date)
    {
        $this->_class = 'Application_Model_DbTable_UsersContracts';
        return $this->getDbTable()->userGetContractsByDate($uid, $date);
    }
    
    /**
     * 
     */
    public function userGetContracts($uid)
    {
        $this->_class = 'Application_Model_DbTable_UsersContracts';
        return $this->getDbTable()->fetchAll("user_id = $uid");
    }
    
   
    
    
}

 
