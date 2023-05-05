<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RichiestaController
 *
 * @author Luca
 */
class RichiestaController extends Prisma_Controller_Action {
    
    
    public function nuovaAction()
    {
        $request = $this->_request;
        $this->_process($request);
    }
    
    /**
     * 
     * @param Zend_Controller_Request_Abstract $request
     * @throws Exception
     */
    protected function _process($request)
    {
        $this->disableView(); 
        if( (!$request->isPost()) || (!$request instanceof Zend_Controller_Request_Abstract) ) {
             //throw new Exception("No post request!");
             //return;
        }
        $tid = $request->getParam('tid');
        if(!$tid) {
           throw new Exception("Manca il parametro TID, impossibile procedere."); 
        }
        $TM = new Application_Model_TipologiaMapper();
        $tipo = $TM->findById($tid);
        
        if(!$tipo) {
            throw new Exception("Tipologia inesistente, impossibile procedere.");
        }
        
        // recupero il nome della classe associato alla tipologia di richiesta
        $class = trim($tipo->class_name);
 
        if(!$class) {
            throw new Exception("Impossibile determinare il nome della classe della Richiesta.");
        }
        
        try {
            
            $richiesta = new Application_Model_RequestManager();
            
            /* stabilisco il tipo di richiesta */
            $richiesta->addRequest($class);
            
            # valori per la richiesta
            $values = array(
                'start' => '2015-06-03',
                'stop'  => '2014-06-12',
                'uid'   => 97
            );
            
            // ridondante?
            if(!$richiesta->isValid($values)) {
                throw new Exception($richiesta->getMessage());
            }
            
            // salvataggio della richiesta
            $richiesta->save();
        } catch(Exception $e) {
            if(DEBUG_ENABLED)
                Prisma_Logger::log("Errore: " . $e->getMessage(), Zend_Log::CRIT);
            else 
                echo "<b>{$e->getMessage()}</b>";
        }
        
        
    }
    
    
}

 
