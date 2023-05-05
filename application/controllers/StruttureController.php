<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StruttureController
 *
 * @author Luca
 */
class StruttureController extends Zend_Controller_Action {
     
    
    public function init() {
        $this->mapper = new Application_Model_StruttureMapper();
    }
    
    public function indexAction() {
        //$this->_helper->viewRenderer->setNoRender(true);
    }
            
    public function addAction() {
        //$this->_helper->viewRenderer->setNoRender();
        $req = $this->_request;
        if($req->isPost()) {
            $values = $req->getParams();
            if($values['token'] == $_SESSION['token']) {
            
                unset($values['controller']);
                unset($values['module']);
                unset($values['action']);
                unset($values['submit']);
                unset($values['token']);
                if('' !== trim($values['denominazione'])) {
                    $data = array();
                    foreach ($values as $k => $v) {
                        $data[$k] = $v;
                    }
                    $lastInsertId = $this->mapper->insert($data);
                    unset($_SESSION['token']);
                    $this->_redirect('/strutture/list');
                }
            }  
        }
        $token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));
        $this->view->token = $token;
    }
    
    
    
    public function listAction() {
        //$this->_helper->viewRenderer->setNoRender(false);
        $elenco = $this->mapper->fetchAll();
        $this->view->elenco = $elenco;
    }
    
    public function loadAction() {
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        if($this->_request->isXmlHttpRequest()) {
            $elenco = $this->mapper->fetchAllToJson();
            print_r($elenco);
        }
    }
    
    public function updateAction() {
        $req = $this->_request;
        $id = $req->getParam('id');
        if($req->isPost()) {
            $value = $req->getParams();
            if($value['token'] == $_SESSION['token']) {
                $data = array(
                    'denominazione' => $value['denominazione'],
                    'citta'         => $value['citta'],
                    'indirizzo'     => $value['indirizzo'],
                    'telefono'      => $value['telefono'],
                    'email'         => $value['email'],
                    'coordinate'    => $value['coordinate']
                );
        
                $where = array(
                    'struttura_id = ?' => $id 
                );
        
                try {
                    $this->mapper->update($data, $where);
                    unset($_SESSION['token']);
                    $this->_redirect('/strutture/list');
                } catch(Exception $e) {
                    echo $e->getMessage();
                }
           }
       }
       $struttura = $this->mapper->find($id);
       if(is_object($struttura)) {
            $this->view->struttura = $struttura;
            $token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));
            $this->view->token = $token;
       }
     }
  
}
 
 
