<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Request
 *
 * @author Luca
 */
class Application_Model_RequestManager {
    
    
    protected $_mapper;
    
    protected $_class = null;
    
    protected $_message;
    
    protected $_check = false;
    
    /*
     * quando salvo, 
    protected $_user_id;
    
    protected $_tipologia_id;
    */
    
    
    public function __construct($id = null)
    { }
    
    public function setRequest(Application_Model_Request_Interface $class_name) 
    {
       # s
    }
    
    /**
     * 
     */
    public function addRequest($class)
    {
        $className = $this->_setClassName($class);
        if(!$className instanceof Application_Model_Request_Interface) {
            throw new Application_Model_Request_Exception("Invalid class type.");
        }
        $this->_class = $className;
    }
    
    /**
     * 
     * @param type $class
     * @return \className
     */
    protected function _setClassName($class)
    {
        $class     = ucfirst( strtolower($class) );
        $namespace = "Application_Model_Request";
        $className = $namespace . "_" . $class;
        if(!class_exists($className)) {
            throw new Zend_Exception("La classe \"$className\" non esiste");
        }
        try {
            $instance = new $className();
            return $instance;
        } catch (Exception $ex) {
               return false;
        }
    }
    
    
    /**
     * 
     */
    public function isValid($values) 
    {
        
        $instance = $this->_class;
        $result = true;
        $this->_check = true;
        if(!$instance->isValid($values)) {
            $result = false;
            $this->_check = false;
            $this->_message = $instance->getMessage();
        }
        return $result;
    }
    
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    
    /**
     * 
     */
    public function get($id) {}
    
    /**
     * 
     */
    public function save($values = null)
    {
        if(!$values) {
            throw new Exception("Mancano i dati della richiesta");
        }
        if(!$this->_class) {
            $this->_message = "Innestare una classe di tipo Richiesta";
        }
        if( !$this->isValid($values) ){
             throw new Exception($this->getMessage());
        }
        
        $mapper = $this->getMapper();
        // $mapper->save($object);
        echo "Richiesta salvata";
    }
    
    /**
     * 
     */
    public function setMapper($map)
    {
        $this->_mapper = $map;
        return $this;
    }
    
    /**
     * 
     */
    public function getMapper()
    {
        if(null === $this->_mapper) {
            $this->_mapper = $this->_setDefaultMapper();
        }
        return $this->_mapper;
    }
    
    /**
     * 
     */
    protected function _setDefaultMapper()
    {
        $this->_mapper = 'Application_Model_RichiesteMapper';
    }
}

 
