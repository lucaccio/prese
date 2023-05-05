<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserList
 *
 * @author Luca
 */
class Application_Model_UserList extends ArrayObject {
    
    
    protected $_users  = array() ;
    
    
    
    
    public function __destruct()
    {
       // $this->print_me();
    }
    
    protected function print_me()
    {
        //Prisma_Logger::log($this);
    }
    
    /**
     * 
     */
    public function __construct(array $users = null)
    { 
        if($users !== null) {
            $this->addUsers($users);
        }
        return $this;
        
    }
    
    /**
     * 
     */
    public function addUser(Application_Model_User $user)
    {
        $this->_add($user);
        return $this;
    }
    
    /**
     * 
     * @param array $users
     * @throws Exception
     */
    public function addUsers(array $users)
    {
        if(!is_array($users)) {
            throw new Exception('il parametro deve essere di tipo array');
        }
        
        foreach($users as $k => $user) {
            
            if(!$user instanceof Application_Model_User) {
                throw new Exception('il valore deve eseere un oggetto di tipo Application_Model_User');
            }
            $this->_add($user);
        }
        return $this;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function getUserById($id) 
    {
        foreach($this->_users as $k => $userId) {
            if((int)$id === (int)$user_id)
                return $this->_users[$k];
        }
        
    }
    
    /**
     * 
     * @param type $id
     * @throws Exception
     */
    public function find($id)
    {
        if( !array_key_exists($id, $this->_users) ) {
            throw new Exception('id non trovato');
        }
        
    }
    
    public function getUsers()
    {
        return $this->_users;
    }
    
    /**
     * 
     * @param Application_Model_User $user
     */
    protected function _add(Application_Model_User $user) 
    {
        /*
        $this->_users[] = array(
            'id' => $user->getId(),
            'user' => $user
        );
        */
        $this->_users[ $user->getId() ] = $user
        ;
        
    }
   
    
    public function hasNext()
    {
         
    }
    
    
    
}
