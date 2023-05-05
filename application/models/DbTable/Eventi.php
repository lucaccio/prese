<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Evento
 *
 * @author Luca
 */
class Application_Model_DbTable_Eventi extends Zend_Db_Table_Abstract {
   
    protected $_name    = 'eventi';
    
    protected $_primary = 'evento_id';
    
    
    /**
     * 
     */
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    }    
    
    
    /**
     * Trovo gli eventi per utente o per sostituto
     * @param type $user_id
     * @param boolean $sostituto
     * @param type $periodo
     * @return type
     */
    public function findByUserId($user_id, $sostituto = false, $periodo = null) {


        $sql = $this->select()->from(array('e' => 'eventi'));
        $sql->setIntegrityCheck(false);
        $sql->join( array('a' => 'assenze'), 'a.assenza_id = e.assenza_id' );
        
        if(is_array($periodo)) {
            foreach($periodo as $k => $array) {
                if(array_key_exists('function', $array) ) {
                    $sql->where($array['function'] .'(e.' . $array['colname'] . ') = ?', $array['value']);
                } else {
                    $sql->where('e.' . $array['colname'] . ' = ?', $array['value']);
                }          
            }
        }
        
        $sql->where('e.user_id = ?', $user_id);
        if($sostituto == true) {
            $sql->orWhere('e.sostituto_id = ?', $user_id);
        }
        
       // Zend_Debug::dump($sql->__toString());

        return $this->fetchAll($sql);        
    }


    /**
     *
     * @param $data
     * @return array
     * @internal param string $year
     *
     */
    public function findByDate($data) {

        $sql = $this->select()->from(array('e' => 'eventi'));
        $sql->setIntegrityCheck(false);
        if(isset($data['user_id'])) {
            $sql->where('e.user_id = ?', $data['user_id']);
        }
               
        if(isset($data['sostituto_id'])) {
            $sql->orWhere('e.sostituto_id = ?', $data['user_id']);
        }
                
        $sql->where('YEAR(e.giorno) = ?', $data['year']);
        $sql->where('MONTH(e.giorno) = ?', $data['month']);

        // ASSENZA
        //@15/04/2021 aggiungo la colonna qta
        $sql->join( array('a' => 'assenze'),  'a.assenza_id = e.assenza_id', array('tipologia_id', 'qta'));

        $result = $this->fetchAll($sql) ;
        $eventi = array();
        foreach($result as $row) {
            //$dbTipo = new Application_Model_AssenzeMapper();
            //$assenza = $dbTipo->find($row->assenza_id);
            $eventi[] = array(
                //  @updated at 30/12/2019
                'ID_evento'    => $row->evento_id,
                'giorno'       => $row->giorno,
                'user_id'      => $row->user_id,  
                'sostituto_id' => $row->sostituto_id, 
                'assenza_id'   => $row->assenza_id,
                // @TODO updated 24/03/2018
                'tipologia_id' => $row->tipologia_id,
                'assenza'      => 1,
                'qta_in_ore' => $row->qta
            );
        }
        //  Zend_Debug::dump($sql->getAdapter()->getProfiler()->getLastQueryProfile());
        return $eventi;
    }


    /** */
    public function findAllByDate($users) { }
    
    /**
     * posso cancellare con condizioni multiple
     * @param type $data
     */
    public function delete($data) {
        if(is_array($data)) {
            $where = array();
            foreach($data as $column => $value) {
                $where[] = $this->quoteInto($colums . ' = ?', $value);
            }
        }
        return parent::delete($where);
    }
     
    /**
     * 
     * @param type $data
     * @return type
     * @throws Zend_Db_Table_Row_Exception
     */
    public function insertMultiple($data) {
        //$rowset = $this->_db->createRowset();
        if(!is_array($data)) {
            throw new Zend_Db_Table_Row_Exception('La variabile deve essere di tipo array');
            return;
        }
        
        foreach ($data as $tuple) { 
            $row = $this->createRow($tuple); 
            //$rowset->addRow($row);
           // Prisma_Logger::logToFile("inserisco: " . $row->giorno);
            $row->save(); 
        }
    }
    
    
    /**
     * 
     * @param mixed $assenza
     * @return type
     */
    public function deleteByAssenza($assenza) {
        if(!is_array($assenza)) {
            $where = $this->getAdapter()->quoteInto('assenza_id = ?', $assenza);
            $success = parent::delete($where);
            //$profiler = Zend_Registry::get('profiler');
            //$query = $profiler->getLastQueryProfile();
            // Prisma_Logger::logToFile( $query->getQuery() );
            return $success;
        } else {
            $where = array();
            foreach($assenza as $k => $ID) {
               $where =  $this->getAdapter()->quoteInto('assenza_id = ?', $ID);
               $success = parent::delete($where);
              // echo '<p>aggionamento eventi: ' . $success . '</p>'; 
            }
            return $success;
        }
    }
    
    
    /**
     * 
     * @param type $values
     */
    public function updateByAssenza($values)  {
        $id = $values['assenza_id'];
        $where = $this->_db->quoteInto('assenza_id = ?', $id);
        unset($values['assenza_id']);
        parent::update($values, $where);
        
    }
    
    
    
    /**
     * Restituisce il totale assenze di un determinato tipo di un utente in un determinato anno
     * @param type $user
     * @param type $periodo
     * @param type $tipo
     * @return boolean
     */
    public function getNumAssenze($user = null, $periodo = null, $tipo = null)
    {
        $permesso = false;
        if(is_array($tipo)) {
            $permesso = true;
        }
        if($permesso == false) {
            $sql = $this->select()->from(array('e' => 'eventi'), array('totale' => 'COUNT(*)'));
        } else {
            $sql = $this->select()->from(array('e' => 'eventi'));
        }
        $sql->setIntegrityCheck(false);
        $sql->join( array('a' => 'assenze'), 'a.assenza_id = e.assenza_id' );
        $sql->where('YEAR(e.giorno) = ?', $periodo);
        $sql->where('e.user_id = ?', $user);
        if(is_array($tipo)) {
            $sql->where( sprintf( 'a.tipologia_id BETWEEN %d AND %d',$tipo[0], $tipo[1] ) );
        } else {
            $sql->where('a.tipologia_id = ?', $tipo);
        }
        #Prisma_Logger::log($sql->__toString());
        $rowset = $this->fetchAll($sql);
        
        if($rowset->count()) {
            if($permesso == false) {
                return $rowset->current();
            } else { 
                return $rowset;
            }
        }
        return false;
    }
    
    
    /**
     * Crea la maschera del calendario in richieste/add ?
     * 
     * @param type $range
     * @param type $uid
     * @return boolean
     * 
     * @copyright (c) 2013-06-17, Luca
     */
    public function trovaEventoPerRangeDiDateEdUtente($range, $uid) 
    {
        $sql = $this->select()->from(array('e' => 'eventi'));
        $sql->setIntegrityCheck(false);
        $sql->join( array('a' => 'assenze'), 'a.assenza_id = e.assenza_id', array('tipo' => 'a.tipologia_id') );
        $sql->where("e.user_id = '" . $uid . "' OR  e.sostituto_id =  '" . $uid . "'");
        $sql->where("e.giorno BETWEEN '" . $range['start'] ."' AND '". $range['stop']."'");
        $sql->order('e.giorno ASC');
        # Prisma_Logger::log($sql->__toString());
        return $this->fetchAll($sql);
    }
    
    
    /**
     * 
     * @param type $data
     * @return type
     */
    public function eventsFindBy($data)
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
     * 
     * @param type $uid
     * @param type $tipo
     * @param type $year
     * @return type
     */
    public function contaAssegnatePerUtenteEdAnno($uid, $tipo, $year)
    {
         $sql = $this->select();
         
         $sql->setIntegrityCheck(false);
         
         $sql->from(array('e' => $this->_name), array('totale' => 'COUNT(e.evento_id)'));
         
         $sql->join(array('a' => 'assenze'), 'e.assenza_id=a.assenza_id');
         
         $sql->where('e.user_id = ?', $uid);
         $sql->where('YEAR(e.giorno) = ?', $year);
         $sql->where('a.tipologia_id = ?', $tipo);

        // Prisma_Logger::log($sql->__toString());
         
         return $this->fetchAll($sql);
         
         
    }
    
    /**
     * 
     */
    public function getAssenze($uid,$tid,$year)
    {
        $sql = $this->select()->from(array('e' => 'eventi'));
        $sql->setIntegrityCheck(false);
        $sql->join( array('a' => 'assenze'), 'a.assenza_id = e.assenza_id' );
        $sql->where('e.user_id = ?', $uid);
        $sql->where('a.tipologia_id = ?', $tid);
        $sql->where('YEAR(giorno) = ?', $year);
        $rows = $this->fetchAll($sql);
        //Prisma_Logger::log($rows);
        return  $rows->count();
    }





    /**
     * 
     * @param type $uid
     * @param type $range
     */
    public function userGetByRange($uid,$range) 
    {
        $start = $range['start'];
        $stop  = $range['stop'] ;
        $sql   = $this->select()->from(array('e' => 'eventi')); 
        $sql->setIntegrityCheck(false);
        $sql->join( array('a' => 'assenze'), 'a.assenza_id = e.assenza_id' );
        $sql->join( array('t' => 'tipologia'), 't.tipologia_id = a.tipologia_id' );
        $sql->joinLeft(array('u' => 'users'), 'e.sostituto_id = u.user_id', array('sostituto' =>'CONCAT(nome, \' \', cognome)') ); 
        $sql->where('e.user_id = ?', $uid);
        $sql->where("e.giorno BETWEEN '" . $range['start'] ."' AND '". $range['stop']."'");
        $sql->order('e.giorno ASC');

       // Prisma_Logger::logToFile($sql);

        return $rows = $this->fetchAll($sql);
    }
    
    
    
    
}


