<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RichiesteMapper
 *
 * @author Zack
 */
class Application_Model_RichiesteMapper extends Prisma_Mapper_Abstract {
       
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Richieste';
    }
    

    /**
     * @22 aprile 2020
     * 
     * 
     * esegue il salvataggio senza effetuare controlli di alcun genere (semplice insert)
     */
    public function salvataggioMultiploSenzaControlli($data) {
        try { 
            return $this->getDbTable()->insert($data);
         } catch (Zend_Db_Table_Exception $e) {
            return $e->getMessage();
         }        
    }


    
    /**
     * 
     * @global type $g_enable_request_permesso_mattina_sera_on_same_day
     * @param type $data
     * @return richiesta_id
     * @throws Zend_Db_Table_Exception
     */
    public function save($data) {
        
        global $g_enable_request_permesso_mattina_sera_on_same_day;
        
        if( null == array_key_exists('richiesta_id', $data) ) {
            
            // se esiste una richiesta con: 
            // stato = in lavorazione/accettato / in accettazione
            // allora non posso aggiungerne un'altra uguale
            $stato = array(0,1,2);        
            $tipologia_id = (int)$data['tipologia_id'];
            $control = $this->getDbTable()->select()
                       ->where('user_id = ?', $data['user_id'])
                       ->where('dateStart = ?', $data['dateStart'])
                       ->where('dateStop = ?', $data['dateStop'])
                       ->where('status IN (?)', $stato);
             
            if(ON == $g_enable_request_permesso_mattina_sera_on_same_day) {
                if($tipologia_id == PERMESSO_MATTINA) {
                    $control->where('tipologia_id <> ?', PERMESSO_SERA);
                } elseif($tipologia_id == PERMESSO_SERA) {
                    $control->where('tipologia_id <> ?', PERMESSO_MATTINA);
                }
            }
            
           Prisma_Logger::logToFile($control->__toString());
            
            $row = $this->getDbTable()->fetchRow($control);
            Prisma_Logger::logToFile("x: " . serialize($row));
            /**  
            if(is_countable($row)) {
                if(1 == count($row)) {
                    throw new Zend_Db_Table_Exception('<h4>Attenzione, richiesta esistente</h4>');
                }
            }
            */
            if( $row ) {
                throw new Zend_Db_Table_Exception('<h4>Attenzione, richiesta esistente</h4>');
            }

            try { 
                return $this->getDbTable()->insert($data);
             } catch (Zend_Db_Table_Exception $e) {
                echo $e->getMessage();
             }    
        } else {
            $id = (int) $data['richiesta_id'];
            try {
                echo 'AGGIORNO';
                $this->getDbTable()->update($data, array('richiesta_id = ?' => $id));
                
                return $id;
                
            } catch (Zend_Db_Table_Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    
    
    public function fetchAll($w = null, $o=null,$l = null ) {
        return $this->getDbTable()->fetchAll( $w, $o , $l  );
    }
    
    public function requestFindBy( $data ) {
        return $this->getDbTable()->requestFindBy( $data );
    }
    
    
    /**
     * 
     * @param (int) $user_id
     * @return Zend_Db_Table_Rowset
     */
    public function findByUserId($user_id, $status, $year, $month, $order = null) {
        return $this->getDbTable()->findByUserId((int)$user_id, $status, $year, $month, $order);
    }
    
    /**
     * 
     * @param int $user_id
     * @param int $status
     * @return Zend_Db_Table_Rowset
     */
    public function findByStatus($user_id, $status) {
        return $this->getDbTable()->findByStatus((int)$user_id, (int)$status);
    }
        
    public function findAllRequest($year = null) {
            return $this->getDbTable()->findAllRequest($year);
    }
        
    public function findByRequestId($request_id) {
        return $this->getDbTable()->findByRequestId($request_id);
    }
        
    public function findRequestByStatus($status, $year, $month, $user = null, $tipologia = null) {
        return $this->getDbTable()->findRequestByStatus($status, $year, $month, $user, $tipologia);
    }
    
    public function findRequestByStatusNA($status, $year, $month, $user = null, $tipologia = null) {
        return $this->getDbTable()->findRequestByStatusNA($status, $year, $month, $user, $tipologia);
    }
    
    public function countRequestNextYear() {
        return $this->getDbTable()->countRequestNextYear();
    }
    
    /**
     * 
     * @param type $richiesta_id
     * @return boolean
     */
    public function find($richiesta_id) 
    {
        $rs = $this->getDbTable()->find($richiesta_id);
        if($rs->count() == 0) {
            return false;
        }
        return $rs->current();
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $year
     * @param type $tipo
     * @param type $status
     * @return type
     */
    public function giorniFerieConcessi($user_id, $year, $tipo, $status) {
        return $this->getDbTable()->giorniFerieConcessi($user_id, $year, $tipo, $status);
    }
    
    public function aggiorna($data) {
        return $this->getDbTable()->aggiorna($data);
    }
    
    public function insert($values) {
        echo __CLASS__ . __METHOD__ ;
    }
    
    public function update($values) {
        return $this->getDbTable()->update($values);
    }
    
    public function delete($request_id) {
        return $this->getDbTable()->delete($request_id);
    }
    
    public function giorniAssegnati($user_id, $anno = null, $status = null) {
        return $this->getDbTable()->giorniAssegnati($user_id, $anno, $status);
    }
        
    /**
     * Trasforma la zend row in application/model/richiesta
     * @param Zend_Db_Table_Row_Abstract $row
     */
    public function toObject(Zend_Db_Table_Row_Abstract $row)
    {
        
        
        
    }
    
    
    
    
    
    
    
}

 