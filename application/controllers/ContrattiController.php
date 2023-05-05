<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContrattiController
 *
 * @author Luca
 */
class ContrattiController  extends Zend_Controller_Action {
    
    
    public function init() {
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function indexAction() {
        $this->_redirect('contratti/list');
    }
    
    /**
     * Nuovo contratto
     */
    public function newAction() 
    {
        $this->_helper->viewRenderer->setNoRender(false);
        $req = $this->_request;
        if($req->isPost()) {
            $params = $req->getParams();
            $this->_save($params,'insert');
        }
    }
    
    /**
     * Elenco contratti esistenti
     */
    public function listAction() 
    {
        $this->_helper->viewRenderer->setNoRender(false);
        $map = new Application_Model_ContrattiMapper();
        $list = $map->fetchAll();
        $this->view->list = $list;
    }
    
    /**
     * Modifica un contratto
     * 
     */
    public function editAction()
    {
        $this->_helper->viewRenderer->setNoRender(false);
        $req = $this->_request;
        
        $id =  $req->getParam('id');
        if( !is_numeric($id) ) {
            throw new Exception("Errore: l'id contratto non è corretto.");
        }
         
        $map = new Application_Model_ContrattiMapper();
        try {
            $contratto = $map->find($id);
            if($contratto->getId()  ==  false) {
                throw new Exception("Errore: nessun contratto con questo id.");
            }
            if( (int)count($contratto->getDetails()) == 0 ){
                $db = Zend_Registry::get('db');
                $data = array(
                    'contratto_id' => $contratto->getId(),
                    'ref'          => 'mattina'
                );
                $db->insert('contratti_details', $data);
                $data = array(
                    'contratto_id' => $contratto->getId(),
                    'ref'          => 'sera'
                );
                $db->insert('contratti_details', $data);
                $contratto = $map->find($id);
            }
        } catch(Exception $e) {
            throw new Exception("Errore: impossibile trovare la riga richiesta.");
        }
        
        
        if($req->isPost()) {

            $params = $req->getParams();
            $this->_save($params,'update');
        }
        
        $this->view->contratto = $contratto;
    }
    
    /**
     * 
     * @param type $params
     */
    protected function _save($params, $type)
    {
       // Zend_Debug::dump($params);
        if(null === $type) {
            throw new Exception("Errore: usare uno dei due {insert | update}");
        }
        $ref = array('mon','tue','wed','thu','fri','sat');
        $mattina = array('ref' => 'mattina');
        $sera    = array('ref' => 'sera');
        foreach( $ref as $val) {
            
            $m = Application_Service_Tools::commaToPoint($params[$val]['mattina']);
            $s = Application_Service_Tools::commaToPoint($params[$val]['sera']);
            $mattina[$val] = $m;
            $sera[$val]    = $s; 
        }
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        # salvataggio
        try {
            $descrizione = trim($params['descrizione']);
            $data = array(
                'descrizione' => $descrizione,
            'bisettimanale' => isset($params['bisettimanale']) ? 1 : 0
            
            );
            $CM = new Application_Model_ContrattiMapper();   
                    
            switch ($type) {
                # salvo
                case 'insert' :
                    $rs = $CM->findBY( $descrizione );
                    if($rs->count()) {
                        echo "<script>
                            var dialog = Dialog.init();
                            dialog.open('Errore. Nome contratto presente nel database.');
                            </script>";
                        return;
                    }
                    $db->insert('contratti', $data);
                    $contratto_id = $db->lastInsertId();
                    $mattina['contratto_id'] = $contratto_id;
                    $sera['contratto_id'] = $contratto_id;
                    $db->insert('contratti_details', $mattina);
                    $db->insert('contratti_details', $sera);
                    $db->commit();
                break;
                # aggiorno
                case 'update':
                    $rs = $CM->findBY( $descrizione );
                    if($rs->count()) {
                        $row = $rs->current();
                        if($params['contratto_id'] != $row->contratto_id) {
                            echo "<script>
                                var dialog = Dialog.init();
                                dialog.open('Errore. Nome contratto presente nel database.');
                                </script>";
                            return;
                        }
                    }
                    $db->update('contratti', $data, "contratto_id = {$params['contratto_id']}");
                    $db->update('contratti_details', $mattina, "id = {$params['id_details_mattina']}");
                    $db->update('contratti_details', $sera, "id = {$params['id_details_sera']}");
                    $db->commit();
                break;
                # errore
                default:
                    throw new Exception ("Errore: usare insert o update.");
                break;
            }
            # redirect
           $this->_redirect('contratti/list');
        } catch(Exception $e) {
            $db->rollBack();
            Prisma_Logger::log( $e->getMessage() );
        }
    }
    
    
    public function deleteAction()
    {
        $req = $this->_request;
        if($req->isDispatched()) {
            $fc = Zend_Controller_Front::getInstance();
            $bs = $fc->getBaseUrl();    
            $id =  $req->getParam('id');
            if( !is_numeric($id) ) {
                echo "<script>
                    var dialog = Dialog.init('alert', null , {'redirect' : true, 'href' : '{$bs}/contratti/list'});
                    dialog.open('Id contratto non corretto.');
                    </script>";
                    return;
            }
            $map = new Application_Model_ContrattiMapper();
            $contratto = $map->find($id);
            
            if($contratto->isEmpty()) 
            {
                echo "<script>
                    var dialog = Dialog.init('alert', null , {'redirect' : true, 'href' : '{$bs}/contratti/list'});
                    dialog.open('Contratto inesistente.');
                    </script>";
                return;
            }
            
            /* se il contratto è in uso a qualche utente allora non lo cancello */
            $users_contracts = $contratto->getUsersContracts() ;    
            if($users_contracts->count()) 
            {
                //Prisma_Logger::log( $users_contracts );
                echo "<script>
                    var dialog = Dialog.init('alert', null , {'redirect' : true, 'href' : '{$bs}/contratti/list'});
                    dialog.open('Contratto in uso da uno o più utenti. Eliminazione annullata.');
                    </script>";
                return;
            }
            
            try {
                $contratto->delete();
                echo "<script>
                    var dialog = Dialog.init('alert', null , {'redirect' : true, 'href' : '{$bs}/contratti/list'});
                    dialog.open('Contratto eliminato.');
                    </script>";
                
            } catch(Exception $ex) {
                echo "<script>
                    var dialog = Dialog.init('alert', null , {'redirect' : true, 'href' : '{$bs}/contratti/list'});
                    dialog.open({$ex->getMessage()});
                    </script>";
            }
             
        } else {
            # redirect
            $this->_redirect('contratti/list');
        }
    }
    
    
    
    
}



