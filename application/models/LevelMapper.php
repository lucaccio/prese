<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LevelMapper
 *
 * @author Luca
 */
class Application_Model_LevelMapper extends Prisma_Mapper_Abstract 
{
    
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Level';
        return $this;
    } 
    
    
    public function findByLevelId($level_id) {
        
        return $this->getDbTable()->find($level_id)->current();
        
    }
    
    
    public function find($level_id) {
         
        $rows = $this->getDbTable()->find($level_id) ;
              
        
        
        if( $rows->count() > 0 ) {
           $row = $rows->current();
           $level = new Application_Model_Level($row);
           return $level;
        } else {
           $level = new Application_Model_Level();
           return $level;
        }
        
        
        /*
        $row = $result->current();
        $level->setLevelId($row->level_id)
              ->setDescrizione($row->descrizione);
                 // print_r($level);
        return $level;
        */
    }
    
    public function fetchAll() {
        return $this->getDbTable()->fetchAll();
    }
    
    
    
    
    
    
    
}


