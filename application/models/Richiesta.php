<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Richiesta
 *
 * @author Luca
 */
class Application_Model_Richiesta {
    
    
    protected $_richiesta_id;
    
    protected $_user_id;
    
    protected $_tipologia_id;
    
    protected $_dateStart;
    
    protected $_dateStop;
    
    protected $_giorni;
    
    protected $_status;
    
    protected $_note;        
    
    
    
    public function __construct($id = null)
    {
        if((int)$id > 0) {
             $map = new Application_Model_RichiesteMapper();
             $richiesta = $RM->find($rid);
        }
    }
    
    public function setRichiestaId($id) {
        $this->_richiesta_id = (int)$id;
        return $this;
    }
    
    public function getRichiestaId() {
        return $this->_richiesta_id;
    }
    
    public function getId() {
        return $this->_richiesta_id;
    }
    
    public function setUserId($id) {
        $this->_user_id = (int)$id;
        return $this;
    }
    
    public function getUserId() {
        return $this->_user_id;
    }    
    
    public function getTipologia()
    {
        
        
    }
    
    
    
    
    
    
    
    
    
}

 
