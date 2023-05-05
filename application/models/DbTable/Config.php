<?php
 
/**
 * Description of Config
 *
 * @author Luca
 */
class Application_Model_DbTable_Config extends Zend_Db_Table_Abstract {
     
    protected $_name = 'config';
    
    protected $_primary = 'config_id';

    
    
    public function __construct(){
        $this->_db = Zend_Registry::get('db');
    }
    
    /**
     * 
     * @param type $user_id
     */
    public function findByUser($user_id) {
        
    }
    
    /**
     * 
     * @param type $year
     * @return boolean
     */
    public function findByYear($user_id, $year) {
        $sql = $this->select()
                ->where('user_id = ?', $user_id)
                ->where('anno = ?', $year);
        
        //echo $sql;
         
        $row = $this->fetchAll($sql);
       // print_r($row);
        if(count($row) > 0) {
             
           return $row->current();
        }
        return false;
         
    }
    
    /**
     * 
     * @param array $data
     * @param type $where
     */
    public function update(array $data, $where = null) {
             
        if(is_array($where)) {
            foreach($where as $k => $value) {
                $w = $this->_db->quoteInto($k . ' = ?', $value);
            }
        } else {
            if(is_array($data['where'])) {
                $where = $data['where'];
                unset($data['where']);
                foreach($where as $k => $value) {
                    $w = $this->_db->quoteInto($k . ' = ?', $value);
                }
            }
        }
                 
        try {
            $x = parent::update($data, $w);
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * 
     * @param int $tipo 
     * @param int $user_id
     * @param date $year
     * @param boolean $create_on_error
     * @return Zend_Db_Table_Row
     */
    public function findByTipo($tipo, $user_id , $year, $create_on_empty = false) {
            
        if($tipo == 'ferie') {
            $tipo = 2;
        } elseif($tipo == 'permesso') {
            $tipo = 5;
        } elseif($tipo == 'exfest') {
            $tipo = 5;
        } else { //OKKIO QUI
           // $tipo = 0;
        }
        
        $sql = $this->select()
                ->where('tipo = ?', $tipo);
        
        if(null != $user_id) {
            $sql->where('user_id = ?', $user_id);
        }
        
        if(null == $year) {
            $year = date('Y');
        }
        
        $sql->where('anno = ?', $year);
              
        $row = $this->fetchAll($sql);
        //se la tupla non è presente, prima la creo e poi la restituisco 
        if($row->count() > 0) {
            return $row->current();
        } else {
           if(true == $create_on_empty) {
                $row = $this->createRow();
                $row->tipologia_id = $tipo;
                $row->user_id      = $user_id;
                $row->anno         = $year;
                $x = $row->save();
                return $this->findByTipo($tipo, $user_id ,  $year);
           }
        }
    }
    
    
    
    
    
    
}

 
