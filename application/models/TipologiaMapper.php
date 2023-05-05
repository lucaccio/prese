<?php
/**
 * Description of TipologiaMapper
 *
 * 
 * 
 * 
 * @author Luca
 */
class Application_Model_TipologiaMapper extends Prisma_Mapper_Abstract
{
    
    
    public function __construct() {
        $this->_class   = 'Application_Model_DbTable_Tipologia';
      //  $this->_dbTable = 'Application_Model_DbTable_Tipologia';
    }    
    
    public function fetchAll ($where = NULL, $order = NULL, $count = NULL, $offset = NULL)  {
        return $this->getDbTable()->fetchAll($where, $order, $count, $offset, $count);
    }


    /**
     * 
     * @param type $data
     * @throws Exception
     */   
    public function save($data) {
        
        //questo è compito del form
        if( 2 !== strlen($data['sigla'])) {
                throw new Exception('La sigla deve essere di 2 caratteri alfanumerici');
        }
        
        
        if( null == array_key_exists('tipologia_id', $data) ) {
                    
            $row = $this->getDbTable()->fetchRow(
                $this->getDbTable()->select()
                    ->where('sigla = ?', $data['sigla'])
                     
                );
            
            if(1 == count($row)) {
                    throw new Exception('Attenzione, sigla esistente');
            }
             
            $this->getDbTable()->insert($data);
            
        } else {
            $id = (int) $data['tipologia_id'];
            $this->getDbTable()->update($data, array('tipologia_id = ?' => $id));
        }
    }
       
    /**
     * 
     * @param type $id
     */    
    public function delete($id) {
        $results =  $this->getDbTable()->delete($id);
    }
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Tipologia
     */
    public function find($id) {
        if(!$id) {
            Prisma_Logger::logToFile("manca l'id tipologia");
        }
        $rows = $this->getDbTable()->find($id);
        if($rows->count() == 0) { return $rows; }
        $row = $rows->current();
        $obj = new Application_Model_Tipologia($row);
        return $obj;
    }
       
    /**  
     * 
     * @param use_on_multi_insert: elenca solo le tipologie ammesse per inserimenti multi utente
     * @return array \Application_Model_Tipologia
     */
    public function getAll($active = 1, $show_hidden_fields = false, $use_on_multi_insert = false)
    {
        //ritorna un oggetto
        $results =  $this->getDbTable()->getAll($active, $show_hidden_fields, $use_on_multi_insert);
                
        $tipi = array();
        foreach($results as $res) {
            //Prisma_Logger::logToFile(serialize($res));
            $tipologia = new Application_Model_Tipologia();
            $tipologia->setId($res->tipologia_id);
            $tipologia->setSigla($res->sigla);
            $tipologia->setDescrizione($res->descrizione);
            $tipologia->setPatrono($res->patrono);
            $tipologia->setDescrizioneAdmin($res->descrizione_admin);
            $tipologia->setHidden($res->hidden);
            if( property_exists($res, "use_on_multi_insert") ) {
               $tipologia->setMultiInsert($res->use_on_multi_insert);
            }
        //    Prisma_Logger::logToFile("res: " . $res->assenza_oraria);
            //@date 12/04/2021
            if( property_exists($res, "assenza_oraria") ) {
                //non riesco a farlo funzionare
             // $tipologia->setAssenzaOraria($res->assenza_oraria);
             }


             $tipologia->setAssenzaOraria($res->assenza_oraria);
            //ritorno un array di tipo Application_Model_Tipologia 
            $tipi[] = $tipologia;
        }
        // array di Application_Model_Tipologia
        return $tipi;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function findById($id) {
        $id = (int)$id;
        $rs = $this->getDbTable()->find($id);
        if($rs->count() == 0 ) {
            return false;
        }
        return $rs->current();
    }
    
}

 
