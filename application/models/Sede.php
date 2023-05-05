<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sede
 *
 * @author Luca
 */
class Application_Model_Sede {
 
    
    protected $_sede_id = null;
    
    protected $_citta = null;
    
    
    
    /**
     * 
     * @param Zend_Db_Table_Row_Abstract $row
     * @return \Application_Model_Sede
     */
    public function __construct(Zend_Db_Table_Row_Abstract $row = null) {
        
        if($row instanceof Zend_Db_Table_Row_Abstract && null != $row)  {
            $this->setSedeId($row->sede_id);           
            $this->setCitta($row->citta);
        }
        return $this;        
    }
     
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Sede
     */
    public function setSedeId($id) {
        $this->_sede_id = $id;
        return $this;
    }
    
    /**
     * 
     * @return type
     */
    public function getSedeId() {
        return $this->_sede_id;
    }
    
    /**
     * 
     * @param type $citta
     * @return \Application_Model_Sede
     */
    public function setCitta($citta) {
        $this->_citta = $citta;
        return $this ;
    }
    
    /**
     * 
     * @return type
     */
    public function getCitta() {
        return $this->_citta;
    }
    
    
    public function __toString() {
        
        echo $this->_citta;
       //return  $this->_citta;
    }
    
    
    
    /**
     * NG (15/04/2014)
     * restituisce un array con le ore divise per il giorno della settimana
     * e 
     * 
     */
    public function orario($day_of_week)
    {
        
    }
    
    /**
     * 
     */
    public function orarioSettimanale()
    {
        
    }
    
    
}

 