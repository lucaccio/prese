<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Festivita
 *
 * @author Zack
 */
class Application_Model_DbTable_Festivita extends Zend_Db_Table_Abstract 
{   
    
    protected $_name = 'festivita';
    
    protected $_primary = 'festivita_id';
    
    /**
     * 
     */
    public function __construct() {
      $this->_db = Zend_Registry::get('db');
    } 
        
    /**
     * 
     * @return type
     */
    public function findAll($lavorativo) {
        
        (null == $lavorativo) ? $lavorativo = 0 : $lavorativo = 1;
        
        $sql = $this->select();
                    //->where('lavorativo = ?', $lavorativo);
        $sql->order('mese ASC');
        $sql->order('giorno ASC');
        return $this->fetchAll($sql);
    }


    /**
     * Trova tutte le festivita di un preciso mese
     * @param null $month
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAllByMonth($mese = null) {
        if($mese == null || $mese > 12 || $mese < 1) {
           $mese = date('n');
        }

        $sql = $this->select();
        $sql->where('mese = ?', $mese);
        $sql->order('giorno ASC');
        return $this->fetchAll($sql);
    }




    /**
     * 
     * @param type $data
     * @return type
     */
    public function update(array $data, $where) {
        Prisma_Logger::logToFile("table update"  );
        $id = $data['festivita_id'];
        unset($data['festivita_id']);
        $where = $this->getAdapter()->quoteInto('festivita_id = ?', $id);
        return parent::update($data, $where);
               
    }


    /**
     * Ritorna un arary con una festivita per riga
     * @param null $sede_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function holidays($sede_id = null) {
        
    // SELECT `festivita`.* FROM `festivita` WHERE (nazionale = 1  OR  infrasettimanale = 1) OR (sede_id = '2' AND lavorativo = 0 AND patrono=1)
             
        $nazionale = 1;
        $infrasettimanale = 1;
        $sql = $this->select()
                ->where("nazionale = $nazionale OR infrasettimanale = $infrasettimanale")                 
                ;
               
        if(null != $sede_id) {
            $lavorativo = 0;
            $patrono    = 1;
            $sql->orWhere("sede_id = $sede_id AND lavorativo = $lavorativo AND patrono = $patrono");
             
        }
        //echo $sql .'<br>';
       // Prisma_Logger::logToFile($sql);
        return $this->fetchAll($sql);
    }


     
    /**
     * @04/12/2020
     */
    public function holidaysLavorativi($value = true) {
        $nazionale = 1;
        $infrasettimanale = 1;
        $lavorativo = $value ? 1 : 0;
        $sql = $this->select()
                ->where("nazionale = $nazionale OR infrasettimanale = $infrasettimanale") 
                ->where("lavorativo = $lavorativo")                
                ;
        //SELECT `festivita`.* FROM `festivita` WHERE (nazionale = 1 OR infrasettimanale = 1) AND lavorativo = ?        
        return $this->fetchAll($sql);
    }



    /**
     * @param int $sede_id
     * @return bool|Zend_Db_Table_Row_Abstract
     */
    public function findPatronalSaint($sede_id = 0) 
    {
        if($sede_id == 0) {
            return false;
        }
        $sql = $this->select()
                ->where('sede_id = ?', $sede_id)
                ->where('patrono = ?', 1);
        
        $rows = $this->fetchAll($sql);
        
        if( $rows->count() ) {
            $row = $rows->current();
            return $row;
        }
        return false;
        
    }
    


    /***
     * Restituisce le feste nazionali e le feste di una specifica sede
     * @since 26 marzo 18
     *
     */
    public function elencaFestiviNazionaliEPerSedeMensile($periodo = null, $sede_id = null) {
        if($sede_id == null) {
            throw new Exception('Sede id non presente');
        }

        if($periodo == null) {
            throw new Exception('Manca il mese.');
        }

        //Zend_Debug::dump($periodo);

        $mese = $periodo['mese'];
        $anno = $periodo['anno'];

        $sql = $this->select()
            ->where('nazionale = ?', 1)
            ->where('mese = ?', $mese)
            ->orWhere('sede_id = ?', $sede_id)
            ->where('mese = ?', $mese)
            ->order('mese')
            ->order('giorno  ASC')
        ;

        // echo $sql;
        $rows = $this->fetchAll($sql);
        return $rows;
    }

    
}

 
