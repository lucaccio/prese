<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Assenze
 *
 * @author Zack
 */
class Application_Model_DbTable_Assenze extends Zend_Db_Table_Abstract {
     
    protected $_name = 'assenze';
    
    protected $_primary = 'assenza_id';

    
    
    public function __construct(){
        $this->_db = Zend_Registry::get('db');
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function findByUserId($id, $where = null, $order = null) {
        $sql = $this->select()->where('user_id = ?', $id);
        if($where) {
            foreach($where as $k => $value) {
                $sql->where($k . " = ?", $value);
            }
        }
        if($order) {
            $sql->order($order);
        }
        
        return $this->fetchAll($sql);       
    }
     
    /**
     * 
     * @param type $data
     * @return type
     */
    public function insert(array $data) {
        //echo __METHOD__;
        try {
            $id = parent::insert($data);
            return $id;
            
        } catch (Zend_Db_Table_Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * 
     * @param type $values
     */
    public function update(array $values, $where = null) {
        $id = $values['assenza_id'];
        $where = $this->_db->quoteInto('assenza_id = ?', $id);
        unset($values['assenza_id']);
        parent::update($values, $where);
        
    }
    
    
    /**
     * Cerco l'utente per data
     * @param type $user_id
     * @param type $start
     * @param type $stop
     */
    public function findUserBYDate($user_id, $start, $stop) {
    }
    
    
    public function elencoSostitutiLiberi($inizio, $fine, $tipologia_id)
    {
        
        $mattina = 6;
        $sera = 7;
        
        $inizio = Application_Service_Tools::convertDataItToUs($inizio);
        $fine   = Application_Service_Tools::convertDataItToUs($fine);
                
        $sub = $this->select();
        $sub->setIntegrityCheck(false);
        $sub->from('assenze', 'sostituto_id')
             ->where('dateStart <= ?', $fine)
             ->where('dateStop  >= ?', $inizio);
        
        if($tipologia_id == $mattina) {
           $sub->where('tipologia_id <> ?' , $sera);
        }
        if($tipologia_id == $sera) {
           $sub->where('tipologia_id <> ?' , $mattina);
        }
        
        
        
        $sub1 = $this->select();
        $sub1->setIntegrityCheck(false);
        $sub1->from('assenze', 'user_id')
             ->where('dateStart <= ?', $fine)
             ->where('dateStop >= ?', $inizio);               
        
        if($tipologia_id == $mattina) {
           $sub1->where('tipologia_id <> ?' , $sera);
        }
        if($tipologia_id == $sera) {
           $sub1->where('tipologia_id <> ?' , $mattina);
        }
        
        $select = $this->select()->from('users');
        $select->setIntegrityCheck(false);
        $select->where('user_id NOT IN ?', $sub)
               ->where('user_id NOT IN ?', $sub1)
               ->where('level_id = ?', 2)
               ->where('active = ?', 1)
               ->order('cognome');
        $rows = $this->fetchAll($select);
        return $rows ;
    }
    
    
    
    
    /**
     * Cerco i possibili sostituti per il range di data
     * @param type $inizio
     * @param type $fine
     * @return type
     */
    public function findSostitutiBYDate($inizio, $fine) {
        
        
        $inizio = Application_Service_Tools::convertDataItToUs($inizio);
        $fine   = Application_Service_Tools::convertDataItToUs($fine);
        
        
        
     $sub = $this->select();
     
    // echo $sub;
     
     $sub->setIntegrityCheck(false);
     
     $sub->from('assenze', 'sostituto_id')
             ->where('dateStart <= ?', $fine)
             ->where('dateStop >= ?', $inizio);
        
             
             
             
     $sub1 = $this->select();
     $sub1->setIntegrityCheck(false);
                    $sub1->from('assenze', 'user_id')
             ->where('dateStart <= ?', $fine)
             ->where('dateStop >= ?', $inizio);               
                    
                    
     $select = $this->select()->from('users');
     
        $select->setIntegrityCheck(false);
     
                    $select->where('user_id NOT IN ?', $sub)
                    ->where('user_id NOT IN ?', $sub1)
                    ->where('level_id = ?', 2)
                    ->where('active = ?', 1)
                    ->order('cognome');
  //echo $select;
        
     $rows = $this->fetchAll($select);
     
     
     return $rows ;
        
    }
    
    /**
     * cerco l'utente che è anche un sostituto, per data di richiesta
     * il sostituto che vuole le ferie ha doppio controllo perchè è un utente che va in ferie
     * (e quindi non deve essere già in ferie per quel periodo)
     * ma è anche un sostituto
     * e quindi non può andare iun ferie se sta sostituendo
     * 
     * @param type $user_id
     * @param type $start
     * @param type $stop
     */
    public function findDoubleRoleByDate($user_id, $start, $stop) {
        
        $sql = $this->select()
                    ->where('dateStart <= ?', $stop)
                    ->where('? <= dateStop', $start)
                    ->where('user_id = ? OR sostituto_id = ?', $user_id, $user_id);
                    //->orWhere('sostituto_id = ?', $user_id);
        #Prisma_Logger::log( $sql->__toString());
        
        return $this->fetchAll($sql);
        
    }
    
    /**
     * Alias di findDoubleRoleByDate
     */
    public function findIfSubstitute($user_id, $start, $stop) {
        return $this->findDoubleRoleByDate($user_id, $start, $stop);
    }
    
    
    /**
     * 
     * @param type $id
     * @return int
     */
    public function findByRequest($id) {
        $sql = $this->select()->where('richiesta_id = ?', $id);
        $rowSet = $this->fetchAll($sql);
        
        //print_r($rowSet);
        
        if ($rowSet->count() > 0) {
            $assenze = array();
            foreach($rowSet as $row) {
                $assenze[] = $row->assenza_id;
            }
            return $assenze;
            //$row = $rowset->current();
            //return $row->assenza_id;
            
        } else {
            return false;
        }
     }
       
    
    /**
     * 
     * @param int $req_id
     * @return int
     */
    public function deleteByRequest($req_id) {
        
        $where = $this->getAdapter()->quoteInto('richiesta_id = ?', $req_id);
        $assenze = $this->findByRequest($req_id);
        if(is_array($assenze)) {
            //$where = array();
            foreach($assenze as $k => $ID) {
               $where = $this->getAdapter()->quoteInto('assenza_id = ?', $ID);
               $success = parent::delete($where);
              // echo '<p>aggionamento assenze: ' . $success . '</p>'; 
            }
            return $assenze;
        } else {
            return false; 
        }
             
    }
    
    /**
     * 
     * @param type $userId
     * @param type $start
     * @param type $stop
     */
    public function trovaAssenza($userId, $start, $stop) {
        
    }
    
    
    /**
     * DEPRECATED solo per aggiornare una tabella
     * 
     */
    public function updateday() {
        $sql = $this->select();
        
        //echo $sql . '<br>';
        
        $rowset = $this->fetchAll($sql);
        
       foreach($rowset as $k => $v) {
         //  echo 'riga ' . $v->assenza_id . ' = ' . $v->giorni . ' => ';
           
           //$this->_db->beginTransaction();
           try {
               $ok = false;
                if($v->giorni == 0) {
                    $start = $v->dateStart;
                    $stop  = $v->dateStop;
                    $giorni = Application_Service_Tools::getTotalDays($start, $stop);
                    $where = $this->getAdapter()->quoteInto('assenza_id = ?', $v->assenza_id);

                    $data = array(
                        'giorni' => $giorni
                    );
    
                } else {
                    $giorni = $v->giorni ;
                }
              //  echo $giorni . '<br>';
                //if($ok)
              //  echo $m .'<br>';
           
          } catch(Exception $e) {
              echo $e->getMessage();
          }
          
       }
    }
    
    
    /**
     * Restituisco i giorni di permessi assegnati un determinato anno
     * 
     * 
     */
    public function getTotalePermessiAssegnatiPerAnno($user, $year) {
        $sql = $this->select()->from(array('a' => 'assenze'), array('godute' => 'SUM(giorni)'));
        $sql->where('user_id = ?', $user);
        $sql->where('tipologia_id = ?', 6);
        $sql->where('tipologia_id = ?', 7);
        $sql->where('YEAR(dateStart) = ?', $year);
        //SELECT SUM(giorni) FROM assenze WHERE user_id=30 AND tipologia_id=2 AND YEAR(dateStart)=2013
        
        $rows = $this->fetchAll($sql);
         
        
        if($rows->count() > 0) {
            $row = $rows->current();
            if($row->godute > 0) {
                return $row->godute;
            } else {
               return '0';
            }
        }
        
    }
    
    
    public function getTotaleFerieAssegnatePerAnno($user, $year) {
        
        $sql = $this->select()->from(array('a' => 'assenze'), array('godute' => 'SUM(giorni)'));
        $sql->where('user_id = ?', $user);
        $sql->where('tipologia_id = ?', 2);
        $sql->where('YEAR(dateStart) = ?', $year);
        //SELECT SUM(giorni) FROM assenze WHERE user_id=30 AND tipologia_id=2 AND YEAR(dateStart)=2013
        
        $rows = $this->fetchAll($sql);
        
        if($rows->count() > 0) {
          
            $row = $rows->current();
            if($row->godute > 0) {
                 
                return $row->godute;
            } else {
               
               return '0';
            }
        }
       
    }
    
    
    public function findByWeekOfYear($week, $user_id = null) {
       
        $sql = $this->select();
        $sql->where('WEEKOFYEAR(dateStart) = ?', $week);
        $sql->where('TIMESTAMP(dateStart) > NOW()');
        $sql->where('user_id = ?', (int)  $user_id);
        $rows = $this->fetchAll($sql);
        return $rows;
    }
    
    /**
     * 
     * @param type $reqId
     * @return boolean
     */
    public function getTotaleGiorniPerRichiesta($reqId)
    {
        $sql = $this->select()->from(array('a' => 'assenze'), array('godute' => 'SUM(giorni)'));
        $sql->where('richiesta_id = ?',$reqId);
        $rows = $this->fetchAll($sql);
        if($rows->count() > 0) {
            $row = $rows->current();
            return $row->godute;
        }
        return false;
    }
    
    /**
     * Restituisce l'elenco delle sostituzioni da effettuare per una determinata settimana dell'anno
     * 
     * @param type $week (questo week è il successivo al week del dateControl, di norma)
     * @param type $dateControl
     * @return type
     */
    public function getAssenzeOfWeek($week, $dateControl = null) {
    
        if(null === $dateControl) {
            $dateControl = date('Y-m-d');
        }
        
        $sql = $this->select()
                    ->where('WEEK(dateStart, 3) = ?', $week)
                    ->where('sostituto_id > ?', 0)
                ->where('dateStart >= ?', $dateControl);
        
        $rows = $this->fetchAll($sql);
        return $rows; 
    }
    
    /**
     * 
     * @param type $where
     * @return type
     */
    public function getStorico($where) 
    {
        $sql = $this->select()->setIntegrityCheck(false)
                ->from( array('a' => $this->_name) )
                ->join( array('e' => 'eventi'), 'e.assenza_id=a.assenza_id', array('gg' => 'COUNT(e.evento_id)') ) 
                ->join( array('r' => 'richieste'), 'r.richiesta_id=a.richiesta_id')  
                ->join( array('s' => 'sostituzioni'), 'a.assenza_id=s.assenza_id', array('sostituzione_id')) ;
        if(is_array($where)) {
            
            if(true == $where['user_id']) {
                $uid = (int)$where['user_id'];
                $sql->where('a.user_id = ?', $uid );
            }
            if(true == $where['tipologia_id']) {
                $tid = (int)$where['tipologia_id'];
                $sql->where('a.tipologia_id = ?', $tid );
            }
            if(true == $where['month']) {
                $month = (int)$where['month'];
                $sql->where('MONTH(e.giorno) = ?', $month );
            }
            $year = $where['year'];
            $sql->where('YEAR(e.giorno) = ?', $year);
        }
                
        $sql->group('e.assenza_id');
        //Prisma_Logger::log($sql->__toString());
        
        $rs = $this->fetchAll($sql);
        return $rs;
        
    }
    
    
    
    
    
}

 
