<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Application_Model_Sostituzione
 *
 * @author Luca
 */
class Application_Model_Sostituzione extends Prisma_Model_Object {
    
    
    protected $_sostituzione_id;
    
    
    
    protected $_assenza;
    
    protected $_user;
    
    protected $_sostituto;
    
    protected $_file_id;
    
    /**
     *
     * @var type Object
     */
    protected $_struttura;
    
    protected $_budget;
    
    protected $_dateStart;
    
    protected $_dateStop;
    
    protected $_giorni;
    
    protected $_sede_name;
    
    public $giorni_effettivi;
    /**
     * 
     * @param Zend_Db_Table_Row_Abstract $data
     * @return \Application_Model_Sostituzione
     */
    public function __construct($data = null) {
               
        if(null != $data) {
            if($data instanceof Zend_Db_Table_Row_Abstract) {
              //  print_r($data);
                 $this->setId($data->sostituzione_id);
                 $this->setAssenza($data->assenza_id);
                 $this->setUser($data->user_id);
                 if(isset($data->sostituto_id)) {            
                    $this->setSostituto($data->sostituto_id);
                 }
                 $this->setStruttura($data->struttura_id);
            }
        }
        return $this;
    }
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Sostituzione
     */
    public function setId($id) {
         $this->_sostituzione_id = (int)$id;
         return $this;
    }
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Sostituzione
     */
    public function setAssenza($id) {
         $assenzaDb = new Application_Model_AssenzeMapper();
         $this->_assenza = $assenzaDb->find((int)$id);
         return $this;
    }
    
    
    public function setUser($id) {
         $userDb = new Application_Model_UserMapper();
         $this->_user = $userDb->find((int)$id);
         return $this;
    }
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Sostituzione
     */
    public function setSostituto($id) {
         $userDb = new Application_Model_UserMapper();
         $this->_sostituto = $userDb->find((int)$id);
         return $this;
    }
     
     
    public function getId() {
         return $this->_sostituzione_id;
    }
    
    public function getAssenza() {
         return $this->_assenza;
    }
    
    /**
     * Restituise l'oggetto User riferito al Sostituto
     * @return \Application_Model_User
     */
    public function getUser() {
         
        if($this->_user instanceof Application_Model_User) {
            return $this->_user;    
        } 
        throw new Exception('errore user');
        
        return new Application_Model_User();
         
        //return $this->_user;   
        
        
    }
    
    public function getSostituto() {
         return $this->_sostituto;
    }
    
    public function setDateStart($date) {
         $this->_dateStart = $date;
         return $this;
    }
    
    public function setSede($name) {
         if($name != '') {
         $this->_sede_name = $name;
         return $this;
         }
    }
     
      
     
     
    public function getSede() {
         return $this->_sede_name;
    }
          
    public function getDateStart() {
        return $this->_dateStart;
    }
     
    public function setDateStop($date) {
        $this->_dateStop = $date;
        return $this;
    }
     
    public function getDateStop() {
         return $this->_dateStop;
    }
     
    public function setGiorni($giorni) {
        $this->_giorni = $giorni;
        return $this;
    }
    
    public function getGiorni() {
        return $this->_giorni;
    }
    
    /**
     * 
     * @param type $user_id
     * @return boolean
     */
     public function isOwner($user_id) {
         
         $user = $this->getUser();
         if((int) $user->getId() === (int)$user_id) {
             return true;
         }
         return false;
     } 
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Sostituzione
     */
    public function setStruttura($id) {
        $strutt = new Application_Model_Struttura($id);
        $this->_struttura = $strutt;
        return $this;
    }
    
    /**
     * 
     * @return type
     */
    public function getStruttura() {
        return $this->_struttura;
    }
    
    /**
     * Permette di ordinare un array di oggetti per data di inizio
     * 
     * @param Application_Model_Sostituzione $a
     * @param Application_Model_Sostituzione $b
     * @return int
     */
    static public function orderByDate(Application_Model_Sostituzione $a, Application_Model_Sostituzione $b) {
        if($a->getDateStart() == $b->getDateStart()) {
            return 0;
        }
        return ($a->getDateStart() < $b->getDateStart()) ? -1 : 1;
    }
    
    # todo: check se la sostituzione è stata eseguita (cioè se la data fine è inferiore a oggi)
    public function isSostituzioneEseguita()
    {
        
    }
    
    
}

 
