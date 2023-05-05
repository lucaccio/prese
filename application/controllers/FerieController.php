<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FerieController
 *
 * @author Luca
 */
class FerieController extends Zend_Controller_Action  {
  
    
    public function init() {
        //$this->_helper->viewRenderer->setNoRender();
        $this->user_id = $this->_request->getParam('user_id');
        
        $this->_mapper = new Application_Model_FerieMapper();
        
        $this->ferie = new Application_Model_Ferie($this->user_id);
    }
        
    
    public function indexAction() {
        
        $oggi = date('Y-m-d');
        $start = '2012-06-30';
        $stop  = '2012-07-25';
        $this->ferie->setDateStart($start);
        $this->ferie->setDateStop($stop);
        $this->ferie->generate();
              
        //$maturate =  $this->ferie->calcolaFerieMaturateAnnoInCorso($start, $stop);
        //echo '<p>Ferie maturate (gg): ' . $maturate .'</p>';
        //echo $this->ferie->getResiduoFerie();
        
        
    }
    
    
    
    public function listAction() {
        
    }
    
    public function updateAction() {
        
        $request = $this->_request;
        if($request->isPost()) {
            
            $precedente = $request->getParam('precedente');
            $goduto     = $request->getParam('goduto'); 
            $residuo    = $request->getParam('residuo');
            $config     = $request->getParam('config');
                         
            for($i = 0; $i <= 2; $i++) {
           
                 $p = str_replace(',', '.', $precedente[$i]) ;
                 $g = str_replace(',', '.', $goduto[$i]);
                 $r = str_replace(',', '.', $residuo[$i] );
                 $c = $config[$i];
                                 
                 $data = array(
                    'precedente' => $p,
                    'goduto'     => $g,
                    'residuo'    => $r
                 );

                 $where = array(
                    'config_id' => $c
                 );
                
                 $this->_mapper->update($data, $where);
                  
            }
        }
        
        $user  = $this->_request->getParam('user_id');
        $utente = new Application_Model_UserMapper();
        $this->view->utente =  $utente->find($user);
        
         
        
        $ferie    = $this->_mapper->findByTipo('ferie',$user, null, true);
        $permessi = $this->_mapper->findByTipo('permesso',$user, null, true);
        $exfest  = $this->_mapper->findByTipo('exfest',$user, null, true);
        $this->view->ferie    = $ferie;
        $this->view->permessi = $permessi;
        $this->view->exferie  = $exfest;
           
    }
    
    
     
    
    
    
}

 