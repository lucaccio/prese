<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AssenzaObject
 *
 * @author Luca
 */
class Application_Model_AssenzaObject {
   
    
    protected $_assenza_id;
    
    protected $_richiesta;
    
    protected $_user;
    
    protected $_sostituto;
    
    protected $_tipologia;
    
    protected $_giorni;
    
    protected $_dateStart;
    
    protected $_dateStop;
    
    public function __construct($id)
    {
        $mapper = new Application_Model_AssenzeMapper();
        $row = $mapper->getAssenzaById($id);
        Prisma_Logger::logToFile("uid: " . $row->user_id );
        $this->_assenza_id = $id;
        $this->setDateStart($row->dateStart);
        $this->setDateStop($row->dateStop);
        $this->setGiorni($row->giorni);

        /* mi avvalgo di userMapper */
        $this->setUser($row->user_id);



        if($row->sostituto_id) {
            /* mi avvalgo di userMapper */
            $this->setSostituto($row->sostituto_id);

        }
        $this->setTipologia($row->tipologia_id);
        $this->setRichiesta($row->richiesta_id);
    }
    
    
    public function getId() {
        return $this->_assenza_id;
    }
    
    public function getAssenzaId() {
        return $this->_assenza_id;
    }
        
    #@TODO richiesta object
    public function setRichiesta($id)
    {
        $mapper = new Application_Model_RichiesteMapper();
        $this->_richiesta = $mapper->find($id);
    }
    
    public function getRichiesta()
    {
        return $this->_richiesta;
    }
       
    
    public function setUser($id) 
    {
        $mapper = new Application_Model_UserMapper();
        $this->_user = $mapper->find($id);
    }
    
    public function getUser()
    {
        return $this->_user;
    }
    
    public function getLocalita()
    {
        return $this->_user->getSede()->getCitta();
    }
    
    public function setSostituto($id) 
    {
        $mapper = new Application_Model_UserMapper();
        $this->_sostituto = $mapper->find($id);
    }
            
    public function getSostituto()
    {
        return $this->_sostituto;
    }
    
    public function setTipologia($id) 
    {
        $mapper = new Application_Model_TipologiaMapper();
        $this->_tipologia = $mapper->find($id);
    }
    
    public function getTipologia()
    {
        return $this->_tipologia;
    }
    
    public function getDescrizione()
    {
        return $this->_tipologia->getDescrizione();
    }
    
    public function setGiorni($giorni) 
    {
        $this->_giorni = $giorni;
        
    }
    
    public function getGiorni()
    {
        return $this->_giorni;
    }
    
    public function setDateStart($start)
    {
        $this->_dateStart = $start;
    }
    
    public function getDateStart()
    {
        return new Zend_Date( $this->_dateStart );
    }
    
    public function setDateStop($stop)
    {
        $this->_dateStop = $stop;
    }
    
    public function getDateStop()
    {
        return new Zend_Date( $this->_dateStop );
    }
    
    
    
}

 
