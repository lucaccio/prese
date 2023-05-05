<?php

/**
 * Description of Level
 *
 * @author Luca
 */
class Application_Model_Level {
     
    
    protected $_level_id;
    
    protected $_descrizione;
    
    
    public function __construct(Zend_Db_Table_Row_Abstract $row = null) {
        
        if($row instanceof Zend_Db_Table_Row_Abstract) {
            $this->setLevelId($row->level_id);
            $this->setDescrizione($row->descrizione);
            return $this;
        } else {
            return $this;
        }
   }
    
    
    public function setLevelId($id) {
        $this->_level_id = (int)($id);
        return $this;
    }
    
    public function setDescrizione($descrizione) {
        $this->_descrizione = (string)$descrizione;
        return $this;
    }
    
    public function getLevelId() {
        return $this->_level_id;
    }
    
    public function getDescrizione() {
        return $this->_descrizione;
    }
    
}

 