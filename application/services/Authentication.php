<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Authentication
 *
 * @author Luca
 */
class Application_Service_Authentication {
     
    protected $_auth;
    
    protected $_authAdapter;
    
    protected $_dbAdapter;
    
    protected $_user;
    
    
    
    public function __construct() {
        
      //  $this->_user = new Application_Model_User();
          
    }
    
    /**
     * 
     * @param type $values
     */
    public function authenticate($values) {
              
        $auth = $this->getAuth();
        $adapter = $this->getAuthAdapter($values);
        $result = $auth->authenticate($adapter) ;
        if($result->isValid()) {
           //$auth->setStorage(new Zend_Auth_Storage_Session('feriemanagerNS'));


           $auth->getStorage()->write($adapter->getResultRowObject(array(
                'user_id',
                'nome',
                'cognome',
                'username',
                'active',
                'level_id',
                'sede_id'
            )));
            return true;
        } 
        
    }
    
    
    /**
     * 
     */
    public function clear() {
        $this->getAuth()->clearIdentity();
    }
    
    /**
     * 
     * @return type
     */
    public function getAuth() {
        
        if(null === $this->_auth) {
            $this->_auth =   Zend_Auth::getInstance();
            
        }
        return $this->_auth;
        
    }
    
    /**
     * 
     */
    public function getAuthAdapter($values) {
           
        
        
        $zendDb = $this->getAdapter();
        
        $tableName = 'users';
        
        $identityColumn = 'username';
        
        $credentialColumn = 'password';
        
        
        $identity = $values['username'];
        $password = $values['password'];
        
        if(null === $this->_authAdapter) {
            $this->_authAdapter = new Zend_Auth_Adapter_DbTable($zendDb, $tableName, $identityColumn, $credentialColumn);
            
            $this->_authAdapter->setIdentity($identity);
            $this->_authAdapter->setCredential($password);
        }
        
        return $this->_authAdapter ;
        
    }
    
    
    /**
     * 
     * @return type
     */
    public function getAdapter() {
        if(null === $this->_dbAdapter) {
            $this->_dbAdapter = Zend_Registry::get('db');
        }
        return $this->_dbAdapter;
        
    }
    
    
}

 
