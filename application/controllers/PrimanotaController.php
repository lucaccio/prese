<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PrimanotaController
 *
 * @author Luca
 */
class PrimanotaController extends Zend_Controller_Action {

    
    public function predispatch() {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
        }
        $this->_user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
    }
    
    
    public function init() {
       //$this->_helper->viewRenderer->setNoRender();
       $this->_table   = new Application_Model_PrimanotaMapper();
       $this->_tableBS = new Application_Model_BudgetSostituzioniMapper();
       $this->_dbSostituzioni = new Application_Model_SostituzioniMapper();
       $this->_sostituzione_id = (int)$this->_getParam('sostituzione_id');
       if(isset($this->_sostituzione_id)) {
            $this->sostituzioneObj = $this->_dbSostituzioni->find($this->_sostituzione_id);
            if(!is_object($this->sostituzioneObj)) {
                $this->_redirect('/sostituzioni/elenco');
            }
       }
    }
    
    public function indexAction() {
        
    }
    
    public function nuovaAction() {
        
                                
        $sostituzione_id = $this->_getParam('sostituzione_id');
        $this->view->importo = $totaleBudget = $this->_tableBS->sommaBudget($sostituzione_id);
        $totaleCassa = $this->_table->sommaCassa($sostituzione_id);
        
        $this->view->disponibile = $residuo = ($totaleBudget - $totaleCassa); 
        $this->view->sostituzione_id = (int)$sostituzione_id;
               
        $request = $this->getRequest();
        if($request->isPost()) {
            
            $token = $request->getParam('token');
                       
            if('' != $token) {
                
                if($token == $_SESSION['form_token']) {
                   $this->view->token = $token;
                   $sostituzione_id = $request->getParam('sostituzione_id');
                   $descrizione     = $request->getParam('descrizione');
                   if($descrizione == '') {
                       echo '<h3>Inserire descrizione</h3>';  
                       return;
                   }
                   //
                   // CONTROLLO DATA
                   //
                   $data = trim($request->getParam('data')) ;
                   if('' == $data) {
                       $data = date('Y-m-d');
                   } else {
                       $validator = new Zend_Validate_Date(array('format' => 'dd-mm-yyyy'));
                       if(!$validator->isValid($data)) {
                           echo '<h3>Errore nella data</h3>';  
                       return;
                       } else {
                           $data = Application_Service_Tools::convertDataItToUs($data);
                       }
                       
                   }
                   
                   $importo         = $request->getParam('importo');
                   $cassa           = $request->getParam('cassa');
                   $banca           = $request->getParam('banca');
                   $note            = $request->getParam('note');
                   
                   $data = array(
                       'sostituzione_id' => $sostituzione_id,
                       'descrizione'     => $descrizione,
                       'data'            => $data,
                       'importo'         => $importo,
                       'cassa'           => $cassa,
                       'banca'           => $banca,
                       'note'            => $note
                       
                   );
                   
                   $this->_table->save($data);       
                                     
                 //  $flashMessenger = $this->_helper->getHelper('FlashMessenger');
                 //  $flashMessenger->addMessage('Salvataggio eseguito correttamente');
                   //$this->view->messages = $flashMessenger->getMessages();
                   $this->_redirect('/sostituzioni/elenco');
                   
                   
                } else {
                    $flashMessenger = $this->_helper->getHelper('FlashMessenger');
                    $flashMessenger->addMessage('Salvataggio non eseguito');
                    $this->view->messages = $flashMessenger->getMessages();   
                }
                
                $token = Application_Service_Tools::generaToken();
                $_SESSION['form_token'] = $token;
                $this->view->token = $token;
               
            }
                        
        } else {
            $token = Application_Service_Tools::generaToken();
            $_SESSION['form_token'] = $token;
            $this->view->token = $token;
        
        
            
            
        }
        
        
        
        
    }
    
    public function viewAction() {
               
        if($this->sostituzioneObj->isOwner($this->_user_id)) {
            $elenco = $this->_table->findBySostituzioneId((int)$this->_sostituzione_id);
            $this->view->elenco = $elenco;
            
            $BS = new Application_Model_BudgetSostituzioniMapper();
            $importo = $BS->sommaBudget($this->_sostituzione_id);
            $this->view->importo = $importo;
            $usciteCassa = $this->_table->sommaCassa( (int)$this->_sostituzione_id );
            $this->view->disponibile = $importo - $usciteCassa;
            
        } else {
            echo '<h3>Sostituzione non dell\'utente o sostituzione inesistente</h3>';
        }
    }
    
    
    //MODIFICA UNA PRIMANOTA SERCONDO L'ID PRIMANOTA
    public function editAction() {
        
        //se la sostituzione appartiene all'utente allora proseguo
        if($this->sostituzioneObj->isOwner($this->_user_id)) {
         
            $primanota_id                = (int)$this->_getParam('primanota_id');
            $values = array(
                'primanota_id'    => $primanota_id,
                'sostituzione_id' => $this->_sostituzione_id
            );
            
            $primanotaObj = $this->_table->findByCompoundKey($values);
             
            if($primanotaObj->isComponentOf($this->_sostituzione_id)) {
                $this->view->sostituzione_id = $primanotaObj->getSostituzioneId();
                $this->view->primanota_id    = $primanotaObj->getPrimanotaId();
                $request                     = $this->getRequest();
                
                //AGGIORNO I DATI
                if($request->isPost()) {
                   
                   $primanota_id    = $request->getParam('primanota_id'); 
                   $sostituzione_id = $request->getParam('sostituzione_id');
                   $descrizione     = $request->getParam('descrizione');
                   if($descrizione == '') {
                       echo '<h3>Inserire descrizione</h3>';  
                       return;
                   }
                   
                   $data = trim($request->getParam('data')) ;
                   if('' == $data) {
                       $data = date('Y-m-d');
                   } else {
                       $validator = new Zend_Validate_Date(array('format' => 'dd-mm-yyyy'));
                       if(!$validator->isValid($data)) {
                           echo '<h3>Errore nella data</h3>';  
                       return;
                       } else {
                           $data = Application_Service_Tools::convertDataItToUs($data);
                       }
                       
                   }
                   
                   
                   
                   
                   $importo         = $request->getParam('importo');
                   $cassa           = $request->getParam('cassa');
                   $banca           = $request->getParam('banca');
                   $note            = $request->getParam('note');
                   
                   $data = array(
                       'primanota_id'    => $primanota_id,
                       'sostituzione_id' => $sostituzione_id,
                       'descrizione'     => $descrizione,
                       'data'            => $data,
                      /* 'importo'         => $importo,*/
                       'cassa'           => $cassa,
                       'banca'           => $banca,
                       'note'            => $note
                       
                   );               
                 //print_r($data);
                   try {
                     $this->_table->save($data);  
                     $primanotaObj = $this->_table->find($primanota_id); 
                     
                      $this->_redirect('/primanota/view/sostituzione_id/' . $sostituzione_id);
                     echo '<h4>Dati salvati con successo</h4>';
                     
                   } catch (Zend_Db_Table_Exception $e) {
                       echo $e->getMessage();
                   }
                }
                
                //VISUALIZZO I DATI
                $this->view->primanota    = $primanotaObj;
                
                
                
                
                
            } else {
                $this->_helper->viewRenderer->setNoRender();
                echo '<h3>Primanota non dell\'utente</h3>';
            }
           
        } else {
            $this->_helper->viewRenderer->setNoRender();
            echo '<h3>Sostituzione non dell\'utente</h3>';
        }
        
        
        
    }
    
    public function deleteAction() {
        
        if($this->sostituzioneObj->isOwner($this->_user_id)) {
         
            $primanota_id = (int)$this->_getParam('primanota_id');
            $sid = (int)$this->_getParam('sostituzione_id');
            $this->_table->delete($primanota_id);
            $this->_redirect('/primanota/view/sostituzione_id/' . $sid);
        }
        
        
        
    }
    
    
    
    
    
}


