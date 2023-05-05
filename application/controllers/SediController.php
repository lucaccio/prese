<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SediController
 *
 * @author Luca
 */
class SediController extends Zend_Controller_Action
{
    
    
     public function predispatch() {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
            //echo 'LOGGATI';
            
        }
        
        $this->_user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
    }
    
    
    public function init() {
       //$this->_helper->viewRenderer->setNoRender();
       $this->table = new Application_Model_SediMapper();
       
    }
    
    public function indexAction() {
        
    }
    
    public function listAction() {
        $this->view->sedi = $this->table->getAll();
    }
    
    public function addAction() {
           
        $request = $this->_request;
        if($request->isPost()) {
             
            $citta = $request->getParam('citta');
            
            if('' != $citta) {
                $sede_id =  $this->table->findByName($citta);

                if(false == $sede_id){
                    $data = array(
                        'citta' => $citta
                    );
                    $id = $this->table->insert($data);
                    echo '<h4>Sede salvata</h4>';
                } else {
                    echo '<h4>Sede esistente</4>';
                }
            } else {
                echo '<h4>Inserire un nome</h4>';
            }
        }
        $this->view->sedi = $this->table->getAll();
    }
    
    
    public function viewAction(){
        $request = $this->_request;
        if($request->isPost()) {
             
            $citta   = $request->getParam('citta');
            $sede_id = (int) $request->getParam('sede_id');
            if('' != $citta && '' != $sede_id) {
                $sede_id_exist =  $this->table->findByName($citta);

                if(false == $sede_id_exist){
                    $data = array(
                        'citta' => $citta
                    );
                    $id = $this->table->update($data, $sede_id);
                    
                    if(1 == $id) {
                        echo '<h4>Salvataggio eseguito</h4>';
                    } 
                    
                } else {
                    echo '<h4>Nessun cambiamento eseguito!</h4>';
                } 
                    
            } else {
                    echo '<h4>Città o sede non inseriti </h4>';
            }
        }
        
        $sede_id = $request->getParam('sede_id');
        if((int) $sede_id) {
            $sede = $this->table->find($sede_id);
            if($sede) {
                $this->view->citta = $sede->getCitta();
                $this->view->sedi = $this->table->getAll();
            } else {
                echo '<h4>ID sede non esistente</h4>';
            }
        } else {
            echo '<h4>Manca l\'ID sede</h4>';
        }
        
        
    }
    
    
    
    
    
    
}

 
