<?php
/**
 * 
 */
class IndexController extends Prisma_Controller_Action
{
    
    public function init()
    {
       //$this->_helper->viewRenderer->setNoRender();
    }

    
    
    public function indexAction()
    {
       //$this->disableAll();
        
    }
    
    public function phpinfoAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        phpinfo();
    }
    
    
    
    

}

