<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Assenze
 *
 * @author  
 */
class Application_Model_DbTable_Richieste extends Zend_Db_Table_Abstract {
     
    protected $_name = 'richieste';
    
    protected $_primary = 'richiesta_id';
    
    protected $_rowClass = 'Application_Model_DbTable_Richieste_Row';
    
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }
    
    /**
     * Restituisce le richieste dell'utente
     * 
     * @param type $user_id
     * @param type $status
     * @param type $year
     * @param type $month
     * @param type $order
     * @return type
     */
    public function findByUserId($user_id, $status, $year, $month, $order = null) {
        
        $sql = $this->select();
        $sql->setIntegrityCheck(false); 
        $sql->from(array('r' => 'richieste'));
        $sql->where('r.user_id = ?', $user_id);
        
        if($status == 1) {
            $sql->join(array('a' => 'assenze'), 'a.richiesta_id=r.richiesta_id', 
                    array('a.sostituto_id','a.giorni', 'data_inizio' => 'a.dateStart','data_fine' => 'a.dateStop'));
        }
        
        $sql->where('r.status = ?', (int) $status)
                    ->where('YEAR(r.dateStart) = ?', $year);
        if($month > 0) {
            $sql->where('MONTH(r.dateStart) = ?', $month);
        }
        if(null == $order) {
            $sql->order('r._update DESC');
        } else {
            $sql->order($order);
        }
        //echo $sql;
        return $this->fetchAll($sql);
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $status
     * @param type $order
     * @return type
     */
    public function findByStatus($user_id, $status, $order = 'DESC') {
        
        $sql = $this->select()
                    ->where('user_id = ?', $user_id)
                    ->where('status = ?', $status)
                    ->order('dateStart ' . $order);
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Elenca tutte le richieste
     * @param type $year
     * @return type
     */
    public function findAllRequest($year = null, $status = null, $user_id = null) {
        
        (null === $year) ? $year = date('Y') : $year;
        
        $sql =  $this->select();
        $sql = $this->select()->setIntegrityCheck(false);
        
                 $sql->from(array('r' => 'richieste'), array(
                     'giorni',
                     'richiesta_id',
                     'user_id',
                     'status',
                     'inizio' => "DATE_FORMAT(r.dateStart,\"%d-%m-%Y\")", 
                     'fine' => "DATE_FORMAT(r.dateStop,\"%d-%m-%Y\")"))
                     ->join(array('u' => 'users'),
                                'r.user_id = u.user_id',
                                array('nome', 'cognome'))  
                      ->join(array('l' => 'level'),
                                'l.level_id = u.level_id',
                                array('ruolo' => 'descrizione')) 
                      ->join(array('t' => 'tipologia'),
                                't.tipologia_id = r.tipologia_id',
                                array('tipologia' => 'descrizione'))    
                      ->where('YEAR(dateStart) = ?', $year)
                      ->where('r.status = ?', 0)
                    ;
         
        $result =  $this->fetchAll($sql);
        // print_r($result);
        return $result;
             
    }
    
    /**
     * 
     * @param type $request_id
     * @return type
     */
    public function findByRequestId($request_id) {
        return $this->find($request_id)->current();
    }
    
    /**
     * 
     * @param int $stato
     * @param type $year
     * @return Zend ResultSet
     */
    public function findRequestByStatus($status = null, $year = null, $month = null, $user_id = null, $tipologia = null) {
        
        (null == $status) ? $status = 0 : $status;
        (null == $year)   ? $year  = date('Y') : $year;
        (null == $month)  ? $month = 0 : $month;
              
        $sql = $this->select()
                    ->where('status = ?', (int) $status)
                    ->where('YEAR(dateStart) = ?', $year);
        
        if($month > 0) {
            $sql->where('MONTH(dateStart) = ?', $month);
        }
        
        if($user_id > 0) {
            $sql->where('user_id = ?', $user_id);
        }
        
        if( (null != $tipologia) && ($tipologia > 0) ) {
            $sql->where('tipologia_id = ?', $tipologia);
        }
        
        $sql->order('_update ASC');
        #Prisma_Logger::log($sql->__toString());
        $result = parent::fetchAll($sql);
        return $result;
    }
    
    
    /**
     * STATUS = NON ACCETTATO
     * @param int $status
     * @param type $year
     * @param int $month
     * @return type
     */
    public function findRequestByStatusNA($status = null, $year = null, $month = null, $user_id = null, $tipologia = null) {
        
        (null == $status) ? $status = 0 : $status;
        (null == $year)   ? $year  = date('Y') : $year;
        (null == $month)  ? $month = 0 : $month;
           /*     
        $sql = $this->select()
                    ->where('status = ?', (int) $status)
                    ->where('YEAR(dateStart) = ?', $year);
        
        if($month > 0) {
            $sql->where('MONTH(dateStart) = ?', $month);
        }
        //$sql->order('_update DESC');
        $sql->order('_update ASC');
        //echo $sql;    
        */
        
        $sql = $this->select();
        $sql->setIntegrityCheck(false);
        $sql->from(array('r' => 'richieste'),array('richiesta_id', 'r.status', 'r.dateStart','r._update','r.created_by_user_id'));
        
        if($status == 2) {
            $sql->where('r.status = ?', 1);
            $sql->joinLeft(array('a' => 'assenze'), 'a.richiesta_id=r.richiesta_id');
            $sql->where('a.sostituto_id = ?', 0);
        } else {
            $sql->where('r.status = ?', $status);
        }
        $sql->where('YEAR(r.dateStart) = ?', $year);
        
        if($month > 0) {
            $sql->where('MONTH(r.dateStart) = ?', $month);
        }
        
        if($user_id > 0) {
            $sql->where('r.user_id = ?', $user_id);
        }
        
        # ...aggiunto il 2013-10-17
        if($tipologia) {
            $sql->where('r.tipologia_id = ?', $tipologia);
        }
        
        $sql->order('r._update ASC');    
       # Prisma_Logger::log($sql->__toString());
        
        //return $this->fetchAll($sql);
            
        
        $result = parent::fetchAll($sql);
        return $result;
        
    }
    
    
    
    
    
    
    
    /**
     * 
     * @param type $data
     */
    public function aggiorna($data) {
        $id = $data['richiesta_id'];
        array_splice($data, 0, 1);
        $where = $this->getAdapter()->quoteInto('richiesta_id = ?', $id);
        parent::update($data, $where);
    }
    
    /**
     * 
     * @param type $values
     */
    public function update(array $values, $where = null) {
         $id = $values['richiesta_id'];
       #  array_splice($values, 0, 1);
         unset($values['richiesta_id']);
         $where = $this->getAdapter()->quoteInto('richiesta_id = ?', $id);
         return parent::update($values, $where); 
    } 
    
    /**
     * 
     * @param type $id
     */
    public function delete($id) {
        $where = $this->getAdapter()->quoteInto('richiesta_id = ?', $id);
        return parent::delete($where);
    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    public function insert(array $data) {
        return parent::insert($data);
    }
    
    
    /**
     * attenzione (i giorni vengono calcolati considerando anche se si esce dall'anno corrente e si va al successivo)
     *
     * 
     * @param type $user_id
     * @param type $year
     * @param type $tipo
     * @param type $status
     */
    public function giorniFerieConcessi($user_id, $year, $tipo = 2, $status = 1) {
        $sql = $this->select()
                ->from(array('r' => 'richieste'),
                                array('giorniTotali' => 'SUM(giorni)'))
                    ->where('r.user_id = ?', $user_id)
                    ->where('YEAR(dateStart) = ? ' , $year)
                    ->where('tipologia_id = ?', $tipo)
                    ->where('status = ?', $status);
                    
       // echo $sql;
        
       // $rowSet = $this->fetchAll($sql);
        //return $this->fetchAll($sql);
       // $row = $rowSet->current();
       // echo $row->giorniTotali;
       
       
    }
    
    
    /**
     * Trova le richieste in base a determinati parametri
     * @param int $uid
     * @param int $status
     * @param type $y
     * @param type $m
     */
    public function findRequest($uid , $status, $y,$m) {
        
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $anno
     * @param int $status
     * @return boolean
     */
    public function giorniAssegnati($user_id, $year = null, $status = null) 
    {
        
        $sql = $this->select(array('r' => 'richieste'), array("SUM(giorni)"  => "assegnate"));
        $sql->where('user_id = ?', $user_id);
        
        if(null == $year) {
            $year = date('Y');
        }
        if(null == $status) {
            $status = 1;
        }
        $sql->where('YEAR(dateStart)', $year);
        $sql->where('status = ?', $status);
        
        $rows = $this->fetchAll($sql);
        
        if($rows->count()) {
            $row = $rows->current();
            return $row->concesse;
        }
        return false;
    } 
    
    
    /**
     * Cerco delle tuple in base a dei valori passati in un array
     * @param type $data
     * @return type
     */
    public function requestFindBy( $data ) 
    {
        $sql = $this->select();
        if(is_array($data)) {
            foreach( $data as $k => $v ) {
                 $sql->where("$k = ?", $v);
            }
        }
        return $this->fetchAll( $sql );
    }
    
    /**
     * Conta le richieste dell'anno successivo all'attuale
     * 
     * @return type
     */
    public function countRequestNextYear()
    {
        $year   = date('Y') + 1;
        $status = 0;
        $sql  = $this->select()->where('status = ?', (int) $status);
        $sql->where('YEAR(dateStart) = ?', $year);
        $rs = parent::fetchAll($sql);
        return $rs->count();
    }
    
    
}

 
