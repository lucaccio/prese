<?php
/**
 * Description of PrimanotaMapper
 *
 * @author Luca
 */
class Application_Model_PrimanotaMapper extends Prisma_Mapper_Abstract {
 
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Primanota';
    }
        
    public function save($data) {
        return $this->getDbTable()->save($data);
    }
    
    public function findBySostituzioneId($id) {
        return $this->getDbTable()->findBySostituzioneId($id);
    }
        
    public function findByCompoundKey($values) {
        
        $rows = $this->getDbTable()->findByCompoundKey($values) ;
        $pn   = new Application_Model_Primanota();
        
        if( count($rows) > 0 ) {
            $row = $rows->current();
            $pn->setPrimanotaId($row->primanota_id);
            $pn->setSostituzioneId($row->sostituzione_id);
            $pn->descrizione = $row->descrizione;
            $pn->data        = $row->data ;
            $pn->importo     = $row->importo ;
            $pn->cassa       = $row->cassa ;
            $pn->banca       = $row->banca ;
            $pn->note        = $row->note ;
                       
        }
        
        return $pn;
        
        
    }
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Primanota
     */
    public function find($id) {
        $row = $this->getDbTable()->find($id) ;
               
        $pn   = new Application_Model_Primanota($row);
        return $pn;
    }
    
    public function sommaCassa($sostituzione_id) {
        return $this->getDbTable()->sommaCassa($sostituzione_id);
    }
    
    /**
     * 
     * @param type $sostituzione_id
     */
    public function deleteBySostituzione($sostituzione_id) {
        return $this->getDbTable()->deleteBySostituzione($sostituzione_id);      
    }
    
    /**
     * 
     * @param type $pid
     * @return type
     */
    public function delete($pid) {
        return $this->getDbTable()->delete($pid);
    }
    
    
    
    
    
    
}

 
