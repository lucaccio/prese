<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BudgetController
 *
 * @author Luca
 */
class BudgetController extends Zend_Controller_Action {
    
    
    public function init() {
        $this->_table = new Application_Model_BudgetSostituzioniMapper();
    }
    
    public function indexAction()
    {
        $request = $this->_request;
        $sostituzione_id  = $request->getParam('sostituzione_id');
        $SM = new Application_Model_SostituzioniMapper();
        $sostituzione = $SM->find($sostituzione_id);
        $AO = new Application_Model_AssenzaObject($sostituzione->getAssenza()->getAssenzaId());
        $rows = $this->_table->findBySostituzione($sostituzione_id);
        $this->view->sid = $sostituzione_id;
        $this->view->rows = $rows;
        $this->view->ao = $AO;
    }
    
    
    
    /**
     * @category get http request
     * Funxione che inserisce budget e invia email in get http request mode
     * inserisco budget e invio email
     * @return type
     */
    public function addAction() {
        
        $request = $this->_request;
        if($request->isPost()) {
            if($request->getParam('token') != $_SESSION['token'] || 
                        !isset($_SESSION['token'])) {
                        
                echo '<h4>Impossibile reinviare modulo</h4>';
                
            } else {
                
                $sostituzione_id  = $request->getParam('sostituzione_id');
                $importo = $request->getParam('importo');
                $date    = $request->getParam('date');
                
                if(null == trim($date) or null == trim($importo)){
                    echo '<h4>Inserire i dati</h4>';
                    $this->_helper->redirector->gotoUrl('/budget/add/sostituzione_id/'.$sostituzione_id);
                    return;
                } else {
                    
                    $importo = str_replace(',', '.', $importo);
                    $date_it = Application_Service_Tools::convertDataItToUs($date);
                    $data = array(
                        'sostituzione_id' => $sostituzione_id, 
                        'importo'         => $importo,
                        'date'            => $date_it
                    );
                    
                    $SM = new Application_Model_SostituzioniMapper();
                    $sostituzione = $SM->find($sostituzione_id);
                    
                    $t_email = $sostituzione->getUser()->getEmail();
                    $sandboxSubject = '';
                    if( Zend_Registry::isRegistered('sandbox') ) {
                    $sandbox = Zend_Registry::get('sandbox');
                    if($sandbox) {
                        $sandboxSubject = 'SANDBOX MODE';
                        $t_email = DEVELOPER;
                    }
                    }
                    $validator = new Zend_Validate_EmailAddress();
                 
                    if(!$validator->isValid($t_email)) {
                        Prisma_Error::insert('indirizzo email non valido ' . $t_email);
                        $this->_response->clearBody();
                        $this->_response->clearHeaders();
                        $this->_response->appendBody( Zend_Json::encode('indirizzo email non valido') );
                        #il numero di code restituisce il nome dell'errore su console
                        $this->_response->setHttpResponseCode(500)->sendResponse();
                        exit;
                    }
                    $AO = new Application_Model_AssenzaObject($sostituzione->getAssenza()->getAssenzaId());
                    $dal  = new Zend_Date( $sostituzione->getAssenza()->getDateStart());
                    $al   = new Zend_Date( $sostituzione->getAssenza()->getDateStop() ) ;
                    $mail = new Zend_Mail();
                    $mail->addTo($t_email);
                    $msg  =  "Assegnato budget relativa alla sostituzione numero: " . $sostituzione_id .  "\n\n";
                    $msg .= "IMPORTO: Euro " .  $importo . "\n\n";
                    $msg .= "Dettagli sostituzione: \n";
                    $msg .= "Tipo: " . $AO->getTipologia()->getDescrizione() . "\n";
                    $msg .= "Sede: " .  ucfirst( $AO->getLocalita() ). "\n";
                    $msg .= "Dal:  " . $dal->toString('dd MMMM YYYY') . "\n";
                    $msg .= "Al:   " . $al->toString('dd MMMM  YYYY')  . "\n\n";
                    $msg .= "PER MAGGIORI INFORMAZIONI VERIFICARE SULLA PROPRIA PAGINA DEL SITO http://62.149.161.214/feriemanager/ \n\n";
                    $msg .= "NON RISPONDERE A QUESTA EMAIL";
                    
                    $mail->setBodyText($msg) ;
                    $mail->setSubject('Assegnato budget per la sostituzione n. ' . $sostituzione_id . ". Messaggio del " . new Zend_Date());
                    $mail->setFrom('feriemanager@gmail.com', "[ $sandboxSubject Prisma Investimenti Spa - Amministrazione ]");
                    
                    try {
                        $this->_table->insert($data);
                        $mail->send();
                        #@todo: aggiungere flash message per conferma errore invio email
                        $this->_helper->redirector->gotoUrl('/budget/list/id/'.$sostituzione_id);
                    } catch (Zend_Db_Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
        
        $_SESSION['token'] = $token = Application_Service_Tools::generaToken();
        $this->view->id    = $sostituzione_id = $this->_request->getParam('sostituzione_id');
        $this->view->token = $token;
    }
    
    public function viewAction() {}
    
    public function listAction() {
        $id = $this->_request->getParam('id');
        $rows = $this->_table->findBySostituzione($id);
        
        $this->view->list = $rows;
        $this->view->id = $id;
    }
    
    public function updateAction() {
        $id  = $this->_request->getParam('budget_id');
        $request = $this->_request;
        if($request->isPost()) {
            $importo = $request->getParam('importo');
            $data = array(
                'importo' => $importo,
                'date'    => date('Y-m-d')
            );
            $this->_table->update($data, $id);
            echo "<h4>Dati aggiornati</h4>";
        }
        $row = $this->_table->find($id);
        if(null != $row) {
            $this->view->importo = $row->importo;
        }
    }
    
    public function removeAction() {
        $this->_helper->viewRenderer->setNoRender();
        $id  = $this->_request->getParam('budget_id');
        $sostituzione_id = $this->_request->getParam('sostituzione_id');
        $rowDeleted = $this->_table->delete($id);
        if(!$rowDeleted) {
            echo '<h4>impossibile cancellare la riga</h4>';
            return ;
        }
        $this->_helper->redirector->gotoUrl('/budget/list/id/'.$sostituzione_id);
        return true;
    }
    
    
    /**
     * @category AJAX Request
     * salva il budget in ajax mode
     * 
     */
    public function inviaBudgetAction(){
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
         if($this->_request->isPost()) {
             
             if($this->_request->isXmlHttpRequest()) {
                 $sostituzione_id =  (int) $this->_request->getParam('sostituzione_id') ;
                 $importo =  $this->_request->getParam('importo')  ;
                 $SM = new Application_Model_SostituzioniMapper();
                 $sostituzione = $SM->find($sostituzione_id);
                 $t_email  =  $sostituzione->getUser()->getEmail();
                 $sandboxSubject = '';
                 if( Zend_Registry::isRegistered('sandbox') ) {
                    $sandbox = Zend_Registry::get('sandbox');
                    if($sandbox) {
                        $sandboxSubject = 'SANDBOX MODE';
                        $t_email = DEVELOPER;
                    }
                 }
                 $validator = new Zend_Validate_EmailAddress();
                 if(!$validator->isValid($t_email)) {
                     Prisma_Error::insert('indirizzo email non valido ' . $t_email);
                     $this->_response->clearBody();
                     $this->_response->clearHeaders();
                     $this->_response->appendBody( Zend_Json::encode('indirizzo email non valido') );
                     #il numero di code restituisce il nome dell'errore su console
                     $this->_response->setHttpResponseCode(500)->sendResponse();
                     exit;
                 }
                                  
                  
                 $data = array( 
                    'sostituzione_id' => $sostituzione_id,
                    'importo' => $importo,
                     'date'   => date('Y-m-d')
                 );
                 
                 $AO = new Application_Model_AssenzaObject($sostituzione->getAssenza()->getAssenzaId());
                 $dal  = new Zend_Date( $sostituzione->getAssenza()->getDateStart());
                 $al   = new Zend_Date( $sostituzione->getAssenza()->getDateStop() ) ;
                 $mail = new Zend_Mail();
                 $mail->addTo($t_email);
                 $msg  =  "Assegnato budget relativo alla sostituzione numero: " . $sostituzione_id .  "\n\n";
                 $msg .= "Importo Euro: " .  $importo . "\n\n";
                 $msg .= "Dettagli sostituzione: \n";
                 $msg .= "Tipo di assenza : " . $AO->getTipologia()->getDescrizione() . "\n";
                 $msg .= "Sede: " .  ucfirst( $AO->getLocalita() ). "\n";
                 $msg .= "Dal:  " . $dal->toString('dd MMMM YYYY') . "\n";
                 $msg .= "Al:   " . $al->toString('dd MMMM  YYYY')  . "\n\n";
                 $msg .= "PER MAGGIORI INFORMAZIONI VERIFICARE SULLA PROPRIA PAGINA DEL SITO http://62.149.161.214/feriemanager/ \n\n";
                 $msg .= "NON RISPONDERE A QUESTA EMAIL";
                 
                 $mail->setBodyText($msg) ;
                 $mail->setSubject('Assegnato budget per la sostituzione n. ' . $sostituzione_id . ". Messaggio del " . new Zend_Date());
                 $mail->setFrom('feriemanager@gmail.com', "[ $sandboxSubject Prisma Investimenti Spa - Amministrazione ]");
                 
                 try{
                     $last_insert_id = $this->_table->insert($data);
                     try{
                        //$google = Zend_Registry::get('google');
                        $mail->send();
                        Prisma_Logger::log("Email inviata a: " . $t_email);
                        $value = array(
                            'id'  => $last_insert_id,
                            'msg' => 'Email inviata correttamente'
                        );
                        
                        print_r( Zend_Json::encode($value) );
                        
                     }catch(Exception $e) {
                        Prisma_Error::insert($e);
                        echo Zend_Json::encode($e->getMessage());
                     }
                 } catch(Zend_Db_Exception $e) {
                     Prisma_Error::insert($e);
                     $this->_response->clearBody();
                     $this->_response->clearHeaders();
                     $this->_response->appendBody(Zend_Json::encode($e->getMessage()));
                     #il numero di code restituisce il nome dell'errore su console
                     $this->_response->setHttpResponseCode(500)->sendResponse();
                     exit;
                     
                 }
             }
    
         }
    }
        
    /**
     * @category AJAX Request
     * 
     */
    public function deleteAction() {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        if($this->_request->isPost()) {
             if($this->_request->isXmlHttpRequest()) {
                $budget_id =  (int) $this->_request->getParam('budget_id') ;
                $row = $this->_table->find($budget_id);
                if($row) {
                    $deleted = $this->_table->delete($budget_id);
                    if($deleted) {
                        $value  = array(
                            'id'  => $budget_id,
                            'msg' => 'Budget cancellato correttamente.'
                        );
                        print_r(Zend_Json::encode($value));
                    }
                }
             }
        }
    }
    
    
    
}
    
    
    
    
 


