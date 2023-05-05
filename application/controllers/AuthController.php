<?php
 

/**
 * Description of LoginController
 *
 * @author Luca
 */
class AuthController extends Zend_Controller_Action
{
    
    
    
    public function indexAction() {}
    
    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_authService = new Application_Service_Authentication();
    }
    
    /**
     * 
     */
    public function loginAction() {
         
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
             $this->_helper->redirector->gotoUrl('/index/index');    
        }
                
        $request = $this->getRequest();
        if($request->isPost()) {
            
            $username = $request->getParam('username');
            $password = $request->getParam('password');
            
            //fatto il 19 ottobre perchè si verificava un probblema alla scadenza della session
            if('' == $username || '' == $password) {
                $auth = Zend_Auth::getInstance();
                $auth->clearIdentity();
                $this->_helper->redirector->gotoUrl('/index/index');
            }
            
            $values = array(
                'username' => $username,
                'password' => md5($password)
            );
            
            if( true === $this->_authService->authenticate($values) ) {
                
                $UM = new Application_Model_UserMapper();
                $user = $UM->findByUsername($username);
                $userId = $user['user_id'] ;
                $user = $UM->find($userId);
                $text = "Login effettuato dall'utente " . $user->getAnagrafe(); 
                $logMap = new Application_Model_LogMapper();
                $logData = array(
                    'level'       => 'auth',
                    'facility'    => 'login',
                    'user_id'     =>  $user->getId(),
                    'address'     => $_SERVER['REMOTE_ADDR'],
                    'descrizione' => $text
                );
                $logMap->addEvent($logData);
                $this->_helper->redirector->gotoUrl('/index/index');
            } 
            $this->view->errormessage = 'Autenticazione incorretta';
        }
    }
    
    /**
     * 
     */
    public function logoutAction() {
        
        $userId  = Zend_Auth::getInstance()->getIdentity()->user_id;
        $this->_authService->clear();
        $UM = new Application_Model_UserMapper(); 
        $user = $UM->find($userId);
        $text = "Logout effettuato dall'utente " . $user->getAnagrafe(); 
        $logMap = new Application_Model_LogMapper();
        $logData = array(
            'level'       => 'auth',
            'facility'    => 'logout',
            'user_id'     =>  $user->getId(),
            'address'     => $_SERVER['REMOTE_ADDR'],
            'descrizione' => $text
        );
        $logMap->addEvent($logData);
        $this->_helper->redirector('login');
    }
}


