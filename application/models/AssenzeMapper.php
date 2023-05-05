<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AssenzeMapper
 *
 * @author Zack
 */
class Application_Model_AssenzeMapper extends Prisma_Mapper_Abstract {
   
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Assenze';
    } 
    
    public function getAssenzaById($assenza_id) {
        return $this->getDbTable()->find($assenza_id)->current();
    }
    
    public function save($data) {
        $this->getDbTable()->save($data);
    }
    
    public function getAssenze($id, $where = null, $order = null) {
        return  $this->getDbTable()->findByUserId($id, $where, $order);
    }
    
    public function insert($values) {
        return  $this->getDbTable()->insert($values);
    }
    
    public function update($values) {
        return  $this->getDbTable()->update($values);
    }
    
    public function findDoubleRoleByDate($user_id, $start, $stop) {
        return  $this->getDbTable()->findDoubleRoleByDate($user_id, $start, $stop);
    }
    
    public function isAbsent($uid, $start, $stop)
    {
        $rows = $this->getDbTable()->findDoubleRoleByDate($uid, $start, $stop);
        if($rows->count()) {
            return true;
        }
        return false;
    }
    
    
    
    public function find($assenza_id, Application_Model_Assenza $assenza = null) {
         
        $result = $this->getDbTable()->find($assenza_id);
        
        if(0 == count($result)) { return; }
        
        if(null === $assenza) {
            $assenza = new Application_Model_Assenza();
        }
        
        $row = $result->current();
        
        $assenza->setAssenzaID($row->assenza_id) ;
        $assenza->setRichiestaId($row->richiesta_id) ;
        $assenza->setUserId($row->user_id) ;
        $assenza->setSostitutoId($row->sostituto_id) ;
        $assenza->setTipologiaId($row->tipologia_id) ;
        $assenza->setDateStart($row->dateStart) ;
        $assenza->setDateStop($row->dateStop) ;
        $assenza->setGiorni($row->giorni) ; 
        return $assenza;
        
    }
    
    /**
     * 
     * @param int $richiesta_id
     * @return int
     */
    public function deleteByRequest($richiesta_id) {
        return  $this->getDbTable()->deleteByRequest($richiesta_id);
    }
    
    /**
     * 
     * @param type $richiesta_id
     * @return type
     */
    public function findByRequest($richiesta_id) {
        $assenza_id =  $this->getDbTable()->findByRequest($richiesta_id);
        return $this->find($assenza_id);
    }
    
    /**
     * 
     * @param type $inizio
     * @param type $fine
     * @return type
     */
    public function findSostitutiBYDate($inizio, $fine) {
        return $this->getDbTable()->findSostitutiBYDate($inizio, $fine);
    }
    
    public function elencoSostitutiLiberi($i,$f,$t)
    {
        return $this->getDbTable()->elencoSostitutiLiberi($i,$f,$t);
    }
    
    
    
    /**
     * DEPRECATED solo aggiornamento mysql
     */
    public function updateday() {
        //return $this->getDbTable()->updateday();
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $year
     * @return type
     */
    public function getTotalePermessiAssegnatiPerAnno($user_id, $year) {
        return $this->getDbTable()->getTotalePermessiAssegnatiPerAnno($user_id, $year);
    }
    
    /**
     * 
     * @param type $user
     * @param type $year
     * @return type
     */
    public function getTotaleFerieAssegnatePerAnno($user, $year) {
        return $this->getDbTable()->getTotaleFerieAssegnatePerAnno($user, $year);
        
    }
    
    /**
     * 
     */
    public function findByWeekOfYear($week) {
        return $this->getDbTable()->findByWeekOfYear($week);
    }
    
    public function getTotaleGiorniPerRichiesta($reqId)
    {
        return $this->getDbTable()->getTotaleGiorniPerRichiesta($reqId);
    }
    
    /**
     * 
     */
    public function getAssenzeOfWeek($week, $dateControl = null)
    {
        return $this->getDbTable()->getAssenzeOfWeek($week, $dateControl = null);
    }
    
    public function delete($id) {
        $where = "assenza_id = $id";
        return $this->getDbTable()->delete($where);
    }
    
    public function getStorico($where) {
        return $this->getDbTable()->getStorico($where);
    }
    
}

 
