<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FestivitaController
 *
 * @author Luca
 */
 
class FestivitaController extends Zend_Controller_Action 
{
        
    public function preDispatch() {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
            echo 'LOGGATI';
        }
        $this->user_id = $auth->getIdentity()->user_id;
    }
    
    public function init() {
        //$this->_helper->viewRenderer->setNoRender();
        $this->_dbMapper = new Application_Model_FestivitaMapper();
    }
            
    public function indexAction() {
           echo __METHOD__; 
    }
    
    public function listAction() {
        $list = $this->_dbMapper->findAll();
        $this->view->list = $list;
    }
    
    public function addAction() {
       
        $request = $this->_request;
        if($request->isPost()) {
            if($request->getParam('token') != $_SESSION['token'] || 
                        !isset($_SESSION['token'])) {
                        
                echo '<h4>Impossibile riinviare modulo</h4>';
                return;
            }
            
            $descrizione = $request->getParam('descrizione');
            $giorno      = $request->getParam('giorno');
            $mese        = $request->getParam('mese');  
            $lavorativo  = $request->getParam('lavorativo'); 
            $festa       = $request->getParam('festa'); 
            $sede_id     = 0;
            
            if((0 == $giorno) || (0 == $mese)) {
                echo '<h3>Selezionare giorno e mese</h3>';
                return;
            }
            
            
            $data = array(
                'descrizione' => $descrizione,
                'giorno'      => $giorno,
                'mese'        => $mese,
                'lavorativo'  => $lavorativo
            );
            
            if($festa == 3) {
                $sede_id = $request->getParam('sede_id');
                if($sede_id == 0) {
                    echo '<h3>Selezionare la sede</h3>';
                }
                $val = array(
                    'sede_id' => $sede_id,
                    'patrono' => 1
                );
            } elseif($festa == 1) {
                $val = array(
                    'sede_id'   => $sede_id,
                    'nazionale' => 1
                );
            } elseif($festa == 2) {
                $val = array(
                    'sede_id'          => $sede_id,
                    'infrasettimanale' => 1
                );
            }
            
            $data = array_merge($data, $val);
            
            
            
            
            try {
                $this->_dbMapper->insert($data);
                $this->_redirect('/festivita/list');
            } catch(Exception $e) {
                echo $e->getMessage();
            }
            
        }
        
        
        
        $sedi = new Application_Model_SediMapper();
        $this->view->sedi = $sedi->getAll();
        $_SESSION['token'] = $token = Application_Service_Tools::generaToken();
        $this->view->token = $token;
    }
    
    public function deleteAction() {
        
    }
    
    public function updateAction() {
        Prisma_Logger::logToFile("FestivitaController::updateAction" );
        $request = $this->_request ;
        $fid     = $request->getParam('id');
        $obj     = $this->_dbMapper->find($fid);
        $sedi = new Application_Model_SediMapper();
        $this->view->sedi = $sedi->getAll();
        $this->view->festa = $obj;
        
        if($request->isPost()) {
            Prisma_Logger::logToFile("FestivitaController::updateAction postRequest" );
            $id          = $request->getParam('festivita_id');
            $descrizione = $request->getParam('descrizione');
            $giorno      = $request->getParam('giorno');
            $mese        = $request->getParam('mese');  
            $festa       = $request->getParam('festa'); 
            $sede_id     = $request->getParam('sede_id');
            $lavorativo  = $request->getParam('lavorativo');
            
            if($festa == 3) {
                $val = array(
                    'patrono' => 1
                );
            } elseif($festa == 1) {
                $val = array(
                    'nazionale' => 1
                );
            } elseif($festa == 2) {
                $val = array(
                    'infrasettimanale' => 1
                );
            }
            
            $data = array(
                'festivita_id'=> $id,
                'descrizione' => $descrizione,
                'giorno'      => $giorno,
                'mese'        => $mese,
                'sede_id'     => $sede_id,
                'lavorativo'  => $lavorativo
            );
            
            $data = array_merge($data, $val);
            
            try {    

                /**
                 *  Se l'ggiornamento della festività riguarda il Patrono
                 *  allora devo aggiornare la tab users_config               
                 */                
                if($festa == 3) {
                    Prisma_Logger::logToFile("Aggiorno il  Patrono" );
                    $um = new Application_Model_UserMapper();
                    $user = $um->findByIDSede($sede_id);
                    $configs = array(
                        'user_id' => $user->user_id,
                        'user_values' => array(
                          'sede_lavoro' =>  $sede_id,
                          'patrono_lavorativo' =>  $lavorativo
                    ));
                    Prisma_Logger::logToFile(" aggiorno db usersconfigs"  );
                    $db = new Application_Model_DbTable_UsersConfigs();
                    $db->insertOrUpdate($configs);
                }  
                
                // aggiorna la tabella           
                $this->_dbMapper->update($data);
                $this->_redirect('/festivita/list');
            } catch(Exception $e) {
                Prisma_Logger::logToFile(" errro"  );
                Prisma_Logger::logToFile($e->getMessage() );
                echo $e->getMessage();
            }
        }
    }
    
    
    
    
    
}
