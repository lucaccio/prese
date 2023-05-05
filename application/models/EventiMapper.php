<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EventiMapper
 *
 * @author Luca
 */
class Application_Model_EventiMapper extends Prisma_Mapper_Abstract  implements Application_Model_Events_Interface {
    
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Eventi';
    } 
    
    /**
     * nell'array va messa la colonna come indice e il valore come value
     * @param array $where
     */
    public function cancella($where) {
        $this->getDbTable()->delete($where);        
    }
    
    /**
     * 
     * @param type $data
     */
    public function insertMultiple($data) {
        $this->getDbTable()->insertMultiple($data);
    }
    
    /**
     * @deprecated
     */
    public function findAllByDate($users) {
        return $this->getDbTable()->findAllByDate($users);
    }
    
    /**
     * 
     * @param int $user_id
     * @param type $periodo
     * @return array
     */
    public function findByUserId($user_id, $sostituto, $periodo = null) {
        
        $rows =  $this->getDbTable()->findByUserId($user_id, $sostituto, $periodo);

        $righe = array();

        foreach($rows as $row) {
          // $i++;
           /*
            $righe[$user_id][$i]['giorno']           = $row->giorno;
            $righe[$user_id][$i]['tipo']             = $row->tipologia_id;
            $righe[$row->sostituto_id][$i]['giorno'] = $row->giorno;
            $righe[$row->sostituto_id][$i]['tipo']   = $row->tipologia_id;
             
            */


           Prisma_Logger::logToFile(json_encode($row));
           $righe[$user_id][$row->giorno] = $row->tipologia_id;
           if($row->sostituto_id) {
                $righe[$row->sostituto_id][$row->giorno]   = 0;
           }
           
        }
        //print_r($righe);
        
        return $righe;
        
    }
    
    
    /**
     * 
     * @param mixed $assenza_id
     * @return type
     */
    public function deleteByAssenza($assenza_id) {
        return  $this->getDbTable()->deleteByAssenza($assenza_id);
    }
    
    
    public function updateByAssenza($values) {
        return  $this->getDbTable()->updateByAssenza($values);
    }
    
    
    /**
     * 
     * @param array $range(start | stop)
     * @param type $uid
     * @return Zend_Db_Table_Rowset
     */
    public function trovaEventoPerRangeDiDateEdUtente($range, $uid) {
        $rows = $this->getDbTable()->trovaEventoPerRangeDiDateEdUtente($range, $uid);
        if($rows->count() == 0) {
            return false;
        }
        return $rows;
    }
            
    
    public function contaAssegnatePerUtenteEdAnno($uid, $tipo, $year)
    {
        return $this->getDbTable()->contaAssegnatePerUtenteEdAnno($uid, $tipo, $year);
    }
    
    
    /**
     * Recupero il totale assenze per utente e tipo
     */
    public function getAssenze($uid, $tid, $year)
    {
        return $this->getDbTable()->getAssenze($uid, $tid, $year);
    }
    
    
    /**
     * funzione per la giornaliera
     *
     */
    public function findEventsByUserAndRangeOfDates($uid, $day_start, $day_stop)
    {
        $range = array();
        $range['start'] = $day_start;
        $range['stop']  = $day_stop;
        $rows = $this->getDbTable()->trovaEventoPerRangeDiDateEdUtente($range, $uid);
        //Prisma_Logger::log($rows);
        return $rows;
    }
    
    /**
     * 
     * @param type $uid
     * @param type $month
     * @param type $year
     */
    public function getUserEventsByMonth($uid, $month, $year)
    {
        (null == $month) ? $month = date('m') : $month;
        (null == $year)  ? $yearh = date('Y') : $year;
        $range = array();
        
        $date = new Zend_Date();
        $date->setDay('1') 
             ->setMonth($month)
                ->setYear($year)
                   ->setTime( '00:00:00' )
                ;
        
        $range['start'] = $date->toString("yyyy-MM-dd");
        
        $date->addMonth('1')->subDay('1');
        $range['stop']  = $date->toString("yyyy-MM-dd");
        
        $rows = $this->getDbTable()->trovaEventoPerRangeDiDateEdUtente($range, $uid);
        //Prisma_Logger::log($rows);
        return $rows;
         
         
         
    }
    
    /**
     * Ritorna le assenze effettuate dall'user in un range di date ( start | stop )
     * 
     * @param int $uid
     * @param array $range
     * @return Zend_Db_Table_Rowset
     */
    public function findByUserAndRange($uid, $range) 
    {
        $rs = $this->getDbTable()->userGetByRange($uid,$range);
        return $rs;
    }
    
}

 
