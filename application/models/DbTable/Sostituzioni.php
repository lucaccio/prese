<?php

/**
 * Description of Sostituzioni
 *
 * @author Luca
 */
class Application_Model_DbTable_Sostituzioni extends Zend_Db_Table_Abstract {
 
    
    
    protected $_name = 'sostituzioni';
          
    protected $_primary = 'sostituzione_id';
        
        
    public function __construct(){
      $this->_db = Zend_Registry::get('db');
    }
    
    public function insert(array $values) {
        return parent::insert($values);
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $y
     * @param type $m
     * @param type $status
     * @return type
     */
    public function findByUserId($user_id, $y = null, $m = null, $status = null) {
        
        $sql = $this->select()->from(array('s' => 'sostituzioni'));
        $sql->setIntegrityCheck(false);
        $sql->join(array('a' => 'assenze'),  's.assenza_id = a.assenza_id' );
        
        $sql->where('s.user_id = ?', (int)$user_id) ;
        
        if($status == null) {
            if($y != null) {
                  $sql->where('YEAR(a.dateStart) = ?', $y);
             }
             if($m != null && $m >0 && $m < 13) {
                  $sql->where('MONTH(a.dateStart) = ?', $m);
            }    
        } else {
            //CASE STATUS
            //elencare tutti i casi
        }
        //echo $sql;
        $rows = $this->fetchAll($sql);
        //print_r($rows);
        return $rows;
    }
    
    
    /**
     * 
     * @param type $y
     * @param type $m
     */
    public function findByDate($y = null,$m = null) {
        $sql = $this->select();
        $sql->setIntegrityCheck(false);
        $sql->from( array('s' => $this->_name), array('sostituzione_id', 'assenza_id', 'struttura_id') );
        $sql->join( array('a' => 'assenze'), 's.assenza_id=a.assenza_id');
        
        
        if($y != null) {
              $sql->where('YEAR(a.dateStart) = ?', $y);
         }
         if($m != null && $m >0 && $m < 13) {
              $sql->where('MONTH(a.dateStart) = ?', $m);
         }   
        
        $sql->where('s.user_id > ?', 0); 
        $sql->order('a.dateStart ASC');
        //echo $sql;
        $rows = $this->fetchAll($sql);
        //print_r($rows);
        return $rows;
    }
    
    public function findAll() {
        
        $sql = $this->select();
        $sql->setIntegrityCheck(false);
        $sql->from( array('s' => $this->_name), array('sostituzione_id', 'assenza_id', 'struttura_id') );
        
        $sql->join( array('a' => 'assenze'), 's.assenza_id=a.assenza_id');
        
        //echo $sql;
        $rows = $this->fetchAll($sql);
        //print_r($rows);
        return $rows;
        
    }
    
    /**
     * 
     * @param type $assenza_id
     * @return mixed
     */
    public function findByAssenza($assenza_id) {
        $sql = $this->select()->where('assenza_id = ?', $assenza_id);
        $rowset = $this->fetchAll($sql);
        $rowCount = count($rowset);
        if ($rowCount > 0) {
            $row = $rowset->current();
            return $row->sostituzione_id;
        } else {
            return false;
        }    
        
    }
    
    /**
     * 
     * @param mixed $assenza
     * @return type
     */
    public function deleteByAssenza($assenza) {
           /*              
        $sostituzione_id = $this->findByAssenza($assenza);
        if($sostituzione_id) {
            $where = $this->getAdapter()->quoteInto('assenza_id = ?', $assenza);
            parent::delete($where);
            return $sostituzione_id;
        }
        return 0;
        */
        
        if(is_array($assenza)) {
            
            $where = array();
            
            foreach($assenza as $k => $ID) {
               $where =  $this->getAdapter()->quoteInto('assenza_id = ?', $ID);
               $success = parent::delete($where);
               //echo '<p>aggionamento sostituzioni: ' . $success . '</p>'; 
               
            }
            return $success;  
            
            
        }
        
        
        
        
    }
    
    /**
     * 
     * @param type $values
     */
    public function update(array $values, $w = null) {
        $id = $values['sostituzione_id'];
        $where = $this->_db->quoteInto('sostituzione_id = ?', $id);
        unset($values['sostituzione_id']);
        parent::update($values, $where);
    }
    
    
    /**
     * 
     * 
     * @param type $user_id
     * @param type $y
     * @param type $m
     * @param type $status
     * @return type
     */
    public function getSostituzione($user_id = null, $y = null, $m = null, $status = null)
    {
        $sql = $this->select();
        $sql->setIntegrityCheck(false);
        
        
        if($m != null && $m >0 && $m < 13) {
            $sql->from( array('e' => 'eventi') ,array('giorni_effettivi' => 'COUNT(*)'));
            $sql->where('MONTH(e.giorno) = ?', $m);
        } else {
             $sql->from( array('e' => 'eventi') );
        }
        
        if(null != $user_id) {
            $sql->where('e.sostituto_id = ?', (int)$user_id) ;
        } 
         
        $sql->join( array('a' => 'assenze'), 'e.assenza_id=a.assenza_id');
        $sql->join( array('s' => 'sostituzioni'),  'e.assenza_id = s.assenza_id'  , array('sostituzione_id', 'struttura_id') );
        
        if($y != null) {
            $sql->where('YEAR(e.giorno) = ?', $y);
        }
         
        $sql->group('e.assenza_id');
        // echo $sql;
        $rows = $this->fetchAll($sql);
        //print_r($rows);
        return $rows;
         
    }
    
    
    
    
}

 
