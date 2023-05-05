<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contratto
 *
 * @author Luca
 */
class Application_Model_Contratto {
 
    /**
     *
     * @var type 
     */
    protected $_contratto_id;
    
    /**
     *
     * @var type 
     */
    protected $_descrizione;
    
    /**
     *
     * @var type 
     */
    protected $_pieno;
    
    /**
     *
     * @var type 
     */
    protected $_ridotto;
    
    /**
     *
     * @var type 
     */
    protected $_mattina;
    
    /**
     *
     * @var type 
     */
    protected $_sera;
    

      /**
     *
     * @var type 
     */
    protected $_bisettimanale;


    /**
     *
     * @var type 
     */
    protected $_full;
    
    /* dependent rowset */ 
    protected $_details = array();
    
    /* dependent rowset */ 
    protected $_users_contracts_list = null;
    
    
    protected $_empty = false;
    
    protected $_row = null;
    
    
    /**
     * 
     * @param Zend_Db_Table_Row_Abstract $row
     * @return \Application_Model_Contratto
     */
    public function __construct($row = null) 
    {
        if( isset($row) ) 
        {
            if($row instanceof Zend_Db_Table_Row_Abstract) 
            {
                $this->_row = $row;
                $this->_contratto_id = $row->contratto_id;
                $this->_descrizione  = $row->descrizione;
                $this->_pieno        = $row->pieno;
                $this->_ridotto      = $row->ridotto;
                $this->_mattina      = $row->mattina;
                $this->_sera         = $row->sera;
                //19/05/2021
                $this->_bisettimanale = $row->bisettimanale;
                $this->_full         = $row->full;
                try {
                    $this->_details =  $row->findDependentRowset('Application_Model_DbTable_ContrattiDetails');
                   
                    $this->setUsersContracts();
                    
                    if(null == $this->_details) {
                        Prisma_Logger::log( "..." );
                    }
                } catch(Exception $e) {
                    Prisma_Logger::log($e->getMessage());
                }
                
            }
        }  else {
            $this->_empty = true;
        }
        return $this;
    }
    
    
    public function setUsersContracts()
    {
        $this->_users_contract_list =  $this->_row->findDependentRowset('Application_Model_DbTable_UsersContracts');
    }
    
    public function getUsersContracts()
    {
        return $this->_users_contract_list;
    }
    
    
    /**
     * 
     * @return type
     */
    public function getContrattoId() {
        return $this->_contratto_id;
    }
    
    /**
     * 
     * @return type
     */
    public function getId() {
        return $this->_contratto_id;
    }
    
    /**
     * 
     * @return type
     */
    public function getDescrizione() {
        return $this->_descrizione;
    }
    
    
    /**
     * 
     * @return type
     */
    public function getPieno() {
        return $this->_pieno;
    }
    
    /**
     * 
     * @return type
     */
    public function getRidotto() {
        return $this->_ridotto;
    }
    
    /**
     * 
     * @return type
     */
    public function getSaturday() {
        return $this->_ridotto;
    }
    
    /**
     * 
     * @return type
     */
    public function getMattina() {
        return $this->_mattina;
    }
    
    /**
     * 
     * @return type
     */
    public function getSera() {
        return $this->_sera;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isFull() {
        if(1 == $this->_full) {
            return true;
        }
        return false;
    }
    
    
    
    /**
     * 
     * 
     * @return boolean
     */
    public function getDetails() 
    {
       // if(count($this->_details) == 0) {
       //     return false;
       // }
        return $this->_details;
    }
     
    /**
     * 
     * @return boolean
     */
    public function getDetailsMattina()
    {
        if( !isset($this->_details) ) {
            return false;
        }
        return $this->_details[0];
    }
    
    /**
     * 
     * @return boolean
     */
    public function getDetailsMattinaId() {
        
        if( !isset($this->_details) ) {
            return false;
        }
        return $this->_details[0]['id'];
    }
    
    /**
     * 
     * @return boolean
     */
    public function getDetailsSera()
    {
        if( !isset($this->_details) ) {
            return false;
        }
        return $this->_details[1];
    }
    
    
    public function getDetailsSeraId() {
        if( !isset($this->_details) ) {
            return false;
        }
        return $this->_details[1]['id'];
    }
    
    /**
     * 
     * @param type $day
     * @return type
     */
    public function getOreGiornaliere($day) 
    {
        $ore = floatval( $this->_details[0][$day] + $this->_details[1][$day] ) ;
        
        if((int)$ore == 0) {
            return null;
        }
        return $ore;
    }
    
    /**
     * Verifico se è un contratto vuoto
     * 
     * @return type
     */
    public function isEmpty() 
    {
        return $this->_empty;
    }
    
    public function delete()
    {
        if($this->_row)
         $this->_row->delete();
    }
    
    
    
    //19/05/2021

    public function isBisettimanale() {
        return $this->_bisettimanale  ? true : false;
    }
    
}

 
 
 
