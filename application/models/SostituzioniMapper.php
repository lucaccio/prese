<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SostituzioniMapper
 *
 * @author Luca
 */
class Application_Model_SostituzioniMapper extends Prisma_Mapper_Abstract {
 
    
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Sostituzioni';
    }
    
    
    public function findByDate($y, $m) {
        $rows =  $this->getDbTable()->findByDate($y,$m);
        if($rows instanceof Zend_Db_Table_Rowset_Abstract) {
            
            $objects = array();
            
            foreach($rows as $k => $row) {
                $object = new Application_Model_Sostituzione($row);
                $objects[] = $object;
            }
            return $objects;
        }
    }
    
    /**
     * 
     * @return \Application_Model_Sostituzione
     */
    public function findAll() {
        $rows =  $this->getDbTable()->findAll();
        
        if($rows instanceof Zend_Db_Table_Rowset_Abstract) {
            
            $objects = array();
            
            foreach($rows as $k => $row) {
                $object = new Application_Model_Sostituzione($row);
                $objects[] = $object;
            }
            return $objects;
        }
     }
     
    /**
     * 
     * @param type $values
     * @return type
     */
    public function insert($values) {
        return  $this->getDbTable()->insert($values);
    }
    
    public function update($values) {
        return  $this->getDbTable()->update($values);
    }
    
    /**
     * 
     * @param type $user_id
     * @return \Application_Model_Sostituzione
     */
    public function elenco($user_id, $y = null, $m = null, $status = null) 
    {
        
       // $result = $this->getDbTable()->findByUserId( $user_id , $y, $m, $status);
           $result =  $this->getDbTable()->getSostituzione($user_id, $y, $m, $status);    
        if(0 == count($result)) {
            return;
        } 
              
        $user         = new Application_Model_User();
        $entries      = array();
        $sostituzioni = array();
        foreach($result as $row) {
           
            $assenza      = new Application_Model_Assenza($row->assenza_id);
            
            $sedeMapper = new Application_Model_SediMapper();
            
            $sede = $sedeMapper->findByUser($assenza->getUserId());
            if($sede) {
                $citta = $sede->citta;
            } else {
                $citta = 0;
            }
            //print_r($sede);
            //Prisma_Logger::log($row);
            $sostituzione = new Application_Model_Sostituzione($row);
            $sostituzione->setDateStart($assenza->getDateStart())   
                         ->setDateStop($assenza->getDateStop())
                         ->setGiorni($assenza->getGiorni())
                         //->setGiorniEffettivi($row->giorni_effettivi)
                         ->setSede($citta);
            
            if(isset($row->giorni_effettivi)) {
                $sostituzione->giorni_effettivi = $row->giorni_effettivi;
            }
            
            
            $sostituzioni[] = $sostituzione;
        } 
        
        //ordina per data di inizio        
        usort($sostituzioni, array('Application_Model_Sostituzione' , 'orderByDate'));
        return $sostituzioni;
            
    }
    
    /**
     * 
     * @param type $sostituzione_id
     * @return \Application_Model_Sostituzione
     */
    public function find($sostituzione_id) {
        
        $row = $this->getDbTable()->find($sostituzione_id);
         
        if(count($row) > 0) {
            $row = $row->current();
            $obj = new Application_Model_Sostituzione();

            $obj->setId($row->sostituzione_id)
                ->setUser($row->user_id)
                ->setAssenza($row->assenza_id)
                ->setSostituto($row->user_id)
            ;

            return $obj;
        }
        
    }
    
    /**
     * 
     * @param mixed $assenza
     * @return type
     */
    public function deleteByAssenza($assenza) {
        return  $this->getDbTable()->deleteByAssenza($assenza);
    }
    
    
     public function get($user_id = null, $y = null, $m = null, $status = null) {
        return  $this->getDbTable()->getSostituzione($user_id, $y, $m, $status);
    }
    
}
 
