<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author Luca
 */
abstract class Prisma_Db_Table_Abstract extends Zend_Db_Table_Abstract {


    public function getQueryProfiler() {
     //   $this->_db->getProfiler()->setEnabled(true);
     //   $lastQuery = $this->_db->getProfiler()->getLastQueryProfile()->getQuery() ;
     //   Prisma_Logger::logToFile($lastQuery, true, "profiler.log");
    }


   
}
 
