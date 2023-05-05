<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Residui
 *
 * @author Luca
 */
class ResiduiController extends Zend_Controller_Action {
    
    public function preDispatch() {
    
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
        }
               
        $this->_user_id  = Zend_Auth::getInstance()->getIdentity()->user_id;
        $this->_userMapper = new Application_Model_UserMapper();
        $this->_user = $this->_userMapper->find( $this->_user_id );
        
        if(false == $this->_user->isActive()) {
            $this->_redirect('auth/logout');
        }
        
    }
    
    public function init(){}
    
    public function indexAction(){}
        
    public function viewAction() {
        $UM = new Application_Model_UserMapper();
        $UR = new Application_Model_UserResiduiMapper();
        $uid  = $this->_request->getParam('user_id');
        $year = $this->_request->getParam('year');
        if(null == $uid) {
            $uid = $this->_user_id;
        }
        if(false == $year) {
            $year = date('Y');
        } elseif( ($year < 2007) || ($year > date('Y')) ) {
            $year = date('Y');
        }
                
        $user = $UM->find($uid);
        $rows = $UR->load($uid, $year);
        $this->view->residui = $rows;
        $this->view->year    = $year;
        $this->view->utente  = $user;
    }
    
    
    public function updateAction() {
         
        //$this->_helper->viewRenderer->setNoRender();
        
        $UM = new Application_Model_UserMapper();
        $UR = new Application_Model_UserResiduiMapper();
        $uid = $this->_request->getParam('user_id');
        $year = $this->_request->getParam('year');
        
        if(false == $year) {
            $year = date('Y');
        } elseif( ($year < 2007) || ($year > date('Y')) ) {
            $year = date('Y');
        }
        
        # salvataggio
        if( $this->_request->isPost() ) {
            $update = false;
            $riga = $this->_request->getParam('riga');
            foreach($riga as $k => $data) {
                $id = $data['id'];
                unset($data['id']);
                #Prisma_Logger::log($data);
                $data = str_replace(',', '.', $data);
                #Prisma_Logger::log($data);
                $where = array('id' => $id);
                
                try {
                    $UR->update($data, $where);
                    $update = true;
                    //echo "<p><b>Dati aggiornati</b></p>";
                }
                catch(Exception $e) {
                    Prisma_Logger::log($e->getMessage());
                    $update = false;
                }
                
            }
            if($update) {
                $this->_helper->FlashMessenger('Dati aggiornati');
                $this->view->messages = $this->_helper->flashMessenger->getMessages();
            }
            
            
        }
        $users = $UM->elencoUtenti(false);
        foreach($users as $k => $u) {
            
            $utenti[$u->getId()] = $u;
            
        }
        
        
        
        $user = $UM->find($uid);
        $rows = $UR->load($uid, $year);
        $this->view->residui = $rows;
        $this->view->year    = $year;
        $this->view->utente  = $user;
        $this->view->users   = $utenti;
        $assunzione = new Zend_Date($user->getAssunzione());
        $this->view->assunzione = $assunzione;
         $now = new Zend_Date();
        
        $x = $now->sub($assunzione);
        $measure = new Zend_Measure_Time($x->toValue(), Zend_Measure_Time::SECOND);
        $measure->convertTo(Zend_Measure_Time::YEAR);

    //echo $measure->getValue();
        $this->view->x = (int)$measure->getValue();
    }
    
    /**
     * 
     * @return type
     */
    public function loadAction() {
        
        $this->_helper->layout->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        if(!$this->_request->isXmlHttpRequest()) {
            return;
        }
        $uid  = $this->_request->getParam('user_id');
        $year = $this->_request->getParam('year');
        $UR = new Application_Model_UserResiduiMapper();
        $rows = $UR->load($uid, $year);
        print_r( Zend_Json::encode($rows) );
    }
    
    /**
     * 
     */
    public function checkAction()
    {
        $arr = array();
        $UM  = new Application_Model_UserMapper();
       
        $TM = new Application_Model_TipologiaMapper();
        $this->view->types = $TM->getAll();
        $users = $UM->elencoUtenti();
        
        $r = $this->_request;
        if($r->isPost()) {
            $tid = $r->getParam('tid');
            $this->view->tid = $tid;
            foreach($users as $k => $user) {
                $EM = new Application_Model_EventiMapper;
                $_2012 = $EM->getAssenze($user->getId(), $tid, '2012');
                $_2013 = $EM->getAssenze($user->getId(), $tid, '2013');
                $_2014 = $EM->getAssenze($user->getId(), $tid, '2014');
                  
                $arr[] = array(
                    'user' => $user->getAnagrafe(),
                    '2012' => $_2012,
                    '2013' => $_2013,
                    '2014' => $_2014
                );
            }
        }
        $this->view->arr = $arr;
    }
    
    
}
 