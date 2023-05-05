<?php
/**
 * Description of Configuration
 *
 * @author Luca
 */
class Prisma_Controller_Plugin_Configuration 
                    extends Zend_Controller_Plugin_Abstract 
{
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
         
        try {
            $table   = new Application_Model_DbTable_Configuration();
            $rowset  = $table->fetchAll();     
            $globals = array();
            foreach($rowset as $key => $value)
            {
                $globals[$value->name] = $value->value;
            }
            //global $globals;
            //Prisma_Logger::logToFile($rowset->toArray());
            //Prisma_Logger::log($globals);
            Zend_Registry::set('globals', $globals);
            //Prisma_Logger::logToFile($rowset->toArray());
        } catch (Exception $ex) {
            Prisma_Logger::log($ex->getMessage());
        }
        
    }
    
} 