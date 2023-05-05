<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tipologia
 *
 * @author Zack
 */
class Application_Model_DbTable_Tipologia extends Zend_Db_Table
{

    protected $_name = 'tipologia';
    
    protected $_primary = 'tipologia_id';

    protected $_info;


    public function __construct(){

        $this->_db = Zend_Registry::get('db');
        $this->_info = $this->info();

    }

    /**
     * 
     * @param type $id
     */
    public function delete($id) {
        $where = $this->_db->quoteInto('tipologia_id = ?', $id);
        parent::delete($where);
    }    
    
    
    /**
     * Restituisco tutte le tipologie presenti nel db in base a determinati parametri
     * 
     * @param type $active Mostra | Nascondi campo active
     * @param type $show_hidden_fields Mostra | Nascondi campo hidden
     * @return type
     */
    public function getAll($active = 1, $show_hidden_fields = false, $use_on_multi_insert = false) {
        $sql = $this->select()
                ->where('active = ?', $active);
        
        // mostro | nascondo il campo all'user | admin
        if($show_hidden_fields == false) {
            $sql->where('hidden = ?', 0)->order('descrizione ASC');
        } else {
            $sql->order('descrizione_admin ASC');
        }
       
        /**
         * @22 aprile 2020 multi insert
         */
        if($use_on_multi_insert) {
            $cols = $this->_info['cols'];  
            if( in_array( "use_on_multi_insert" , $cols) ) {                
                $sql->where('use_on_multi_insert = ?', 1) ;
            }  
            
        }
        //Prisma_Logger::logToFile($sql);
        return parent::fetchAll($sql);
    }
        
    /**
    *    
    * 16/04/2021
    */
    public function fetchAll($where = NULL, $order = NULL, $count = NULL, $offset = NULL) {
        return parent::fetchAll($where, $order, $count, $offset, $count);
    }


    /**
     * 
     * @param type $id
     * @return type
       
    
    public function find($id) {
        $sql = $this->select()->where('tipologia_id = ?', $id);
          
       // echo $sql;
       // print_r($sql);
               
        $stmt = $this->_db->query($sql);
        $result = $stmt->fetchObject();
        return $result;
        
    }
    */
    
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function findById($id)
    {
        $sql = $this->select()
            ->where('tipologia_id = ?', $id)
            ->where('active = ?', 1)
        ;

        $stmt = $this->_db->query($sql);
        $result = $stmt->fetchObject();
        return $result;
    }


    /**
     * Restituisce l'id del patrono
     * @todo updated 24 marzo 2018
     * @return mixed
     */
    public function getPatronalId()
    {
        $sql = $this->select()
            ->where('patrono = ?', 1)
            ->where('active = ?', 1)
        ;

        $stmt = $this->_db->query($sql);
        $result = $stmt->fetchObject();
        return $result;
    }




}

 
