<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Action
 *
 * @author Luca
 */
class Prisma_Controller_Action extends Zend_Controller_Action {

    public function init() 
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
            //echo 'LOGGATI';
        }
        $this->user_id = $auth->getIdentity()->user_id;
    }
    
    public function disableLayout($status = true)
    {
        ($status == true) ? $this->_helper->layout->disableLayout() : $this->_helper->layout->enableLayout();
    }
    
    public function disableView($status = true)
    {
        $this->_helper->ViewRenderer->setNoRender($status);
    }
        
    public function disableAll($status = true)
    {
        $this->disableLayout($status);
        $this->disableView($status);
    }
    
    
}


