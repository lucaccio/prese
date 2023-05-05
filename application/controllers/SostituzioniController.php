<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SostituzioniController
 *
 * @author Luca
 */
class SostituzioniController extends Zend_Controller_Action
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
       $this->_table = new Application_Model_SostituzioniMapper();
       $year    = $this->_getParam('year');
       $month   = $this->_getParam('month');
       $user    = $this->_getParam('user');
       $stato   = $this->_getParam('stato');
       
       
       ('' == $year)   ? $year = date('Y')  : $year = $year;
       ('' == $month)  ? $month = date('n') : $month = $month;
       $this->year  = $year;
       $this->month = $month;
       $this->user  = $user;
    }
    
    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();
    }
        
    public function elencoAction() {
        //$um   = new Application_Model_UserResiduiMapper();    
        //$user = new Application_Model_UserMapper();
        $elenco = $this->_table->elenco($this->_user_id, $this->year, $this->month);
        //nel caso di salvataggio da primanota
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->messages = $flashMessenger->getMessages();
        $this->view->elenco   = $elenco;
        $this->view->year     = $this->year;
        $this->view->month    = $this->month;
    }

    
    public function visualizzaAction() {
        $sostituzione_id = $this->_getParam('sostituzione');
    }
    
    /**
     * Elenco sostituzioni
     */
    public function listsAction() {
        $UM = new Application_Model_UserMapper();
        $opt = array(
             "level_id" => 2,
             "active"   => 1
        );
        //carico l'elenco dei sostituti
        $this->view->users = $UM->getAllUsers(false, $opt);
                
        if($this->user > 0) {
            $list  = $this->_table->elenco($this->user, $this->year, $this->month);
            $utente = $UM->find($this->user);
            $this->view->utente = $utente;
        } else {
            $list = $this->_table->findByDate($this->year, $this->month);
        }
        
        $this->view->list  = $list;
        $this->view->year  = $this->year;
        $this->view->month = $this->month;
     } 
    
     /*
      * sandbox
      */
     public function listAction()
     {
        $UM = new Application_Model_UserMapper();
        $opt = array(
             "level_id" => 2,
             "active"   => 1
        );
        //carico l'elenco dei sostituti
        $this->view->users = $UM->getAllUsers(false, $opt);
                
        if($this->user > 0) {
            //Prisma_Logger::log("user: "  . $this->user);
            $list  = $this->_table->elenco($this->user, $this->year, $this->month);
            $utente = $UM->find($this->user);
            $this->view->utente = $utente;
        } else {
            //Prisma_Logger::log("no user ");
            //$list = $this->_table->findByDate($this->year, $this->month);
            $list  = $this->_table->elenco(null, $this->year, $this->month);
        }
        
        $this->view->list  = $list;
        $this->view->year  = $this->year;
        $this->view->month = $this->month;
     }
     
     
    /**
     * 
     * @throws Exception
     */ 
    public function editAction() 
    {
        $sostituzione_id = (int) $this->_request->getParam('sostituzione_id') ;
         
        if(!is_int($sostituzione_id) or ($sostituzione_id == 0)) {
             throw new Exception('Errore nel numero della sostituzione');
        }  
         
        $sostituzione = $this->_table->find($sostituzione_id);
         
        $assenza_id = $sostituzione->getAssenza()->getAssenzaId();
        $inizio     = $sostituzione->getAssenza()->getDateStart();
        $fine       = $sostituzione->getAssenza()->getDateStop();
         
        $userMap    = new Application_Model_UserMapper();
        $operatore  = $userMap->find($sostituzione->getAssenza()->getUserId());
          
        $assenzeMap = new Application_Model_AssenzeMapper();
        $sostitutiLiberi = $assenzeMap->findSostitutiBYDate($inizio, $fine);
         
         
        $sede = (trim($operatore->getSede()->getCitta()) != '') ? trim($operatore->getSede()->getCitta()) : "NESSUNA SEDE";
         
        $sostituto = (trim($sostituzione->getUser()->getAnagrafe()) != '') ? trim($sostituzione->getUser()->getAnagrafe()) : "NESSUN SOSTITUTO";
         
        $data = array(
             'operatore' => $operatore->getAnagrafe(),
             'sede' => $sede,
             'dal'  => $inizio,
             'al'   => $fine,
             'sostituto' => $sostituto
         );
         
         $this->view->assign($data);
         $this->view->liberi = $sostitutiLiberi;
         
         $request = $this->getRequest();
         if($request->isPost()) 
         {
            $token = $request->getParam('token');
            if('' != $token) {
                if($token == $_SESSION['form_token']) {
                    $sostituto_id    = $request->getParam('sostituto_id');
                    if($sostituto_id >= 0) 
                    {
                        //update assenza
                        $data = array(
                            'assenza_id'   => $assenza_id,
                            'sostituto_id' => $sostituto_id 
                        );
                        $assenzeMap->update($data);
                    
                        # update eventi
                        $eventMap = new Application_Model_EventiMapper();
                        $eventMap->updateByAssenza($data);

                        # update sostituzione
                        $data = array(
                            'sostituzione_id' => $sostituzione_id,
                            'user_id' => $sostituto_id 
                        );
                        
                        try {
                            $this->_table->update($data);
                            $this->_helper->flashMessenger->addMessage(array(
                                    'success' => 'Sostituzione aggiornata con successo'));
                        } catch(Exception $e) {
                            $this->_helper->flashMessenger->addMessage(array(
                                'error' => 'Attenzione. Non è possibile aggiornare la sostituzione'));
                        }
                    }
                        $obj    =  new Application_Model_AssenzaObject($assenza_id);
                    
                    # INVIO EMAIL  
                    if(Zend_Registry::get('sendmail') == true) 
                    {
                        $sent = true;
                        $validatorMail = new Zend_Validate_EmailAddress();
                    
                        # email di sostituzione annullata
                        $old_sid = $sostituzione->getAssenza()->getSostitutoId();
                        if($old_sid > 0) 
                        {
                            $oldMail = trim($sostituzione->getUser()->getEmail());
                            
                            if($validatorMail->isValid($oldMail) ) 
                            {
                                $mail = new Zend_Mail();
                                $mail->setFrom(AMMINISTRAZIONE, "[ Prisma Investimenti Spa - Amministrazione ]");
                                # messaggio    
                                $messaggio = '';
                                $messaggio  = "E' stata annullata la sostituzione N. " . $sostituzione_id . " precedentemente programmata.\n\n";
                                $messaggio .= "Tipo: " . $obj->getDescrizione() . "\n";
                                $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
                                $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
                                $messaggio .= "Giorni: " . $obj->getGiorni() . "\n";
                                $messaggio .= "Sede: " . $obj->getLocalita() . "\n\n";

                                $mail->setSubject('Sostituzione annullata');
                                $mail->setBodyText($messaggio);

                                try {
                                    if( Zend_Registry::isRegistered('sandbox') ) 
                                    {
                                        if( true == Zend_Registry::get('sandbox') ) {
                                            $mail->clearFrom();
                                            $mail->setFrom(AMMINISTRAZIONE, "[ SANDBOX ] Feriemanager");
                                            $mail->addTo(DEVELOPER);
                                            $msg = "ATTENZIONE - MESSAGGIO DI PROVA.\n\n";
                                            $msg .= $messaggio;
                                            $mail->setBodyText($msg);
                                        } else {
                                            $mail->addTo($oldMail);
                                        }
                                    } else /* production mode */{
                                        $mail->addTo($oldMail);
                                    }
                                    # send mail            
                                    $mail->send();
                                } catch(Zend_Mail_Transport_Exception $e) {
                                    $sent = false;
                                    echo  ' Impossibile inviare l\'email a '  . $sostituzione->getUser()->getAnagrafe();
                                }
                            } else /* se la mail è invalida */{
                                $this->_helper->flashMessenger->addMessage(array(
                                    'error-mail' => 'Attenzione, impossibile inviare il messaggio a ' . $sostituzione->getUser()->getAnagrafe() . '. Verificare la sua email'));
                            }
                        }
                        
                        /**
                        * Email di nuova sostituzione
                        * 
                        * @todo: inviare email se c'è budget
                        */
                        if($sostituto_id > 0) 
                        {
                            $newSostituto = $userMap->find($sostituto_id);
                            $newMail = trim($newSostituto->getEmail());
                            
                            if($validatorMail->isValid($newMail) ) 
                            {
                                $newmail = new Zend_Mail();
                                $newmail->setFrom(AMMINISTRAZIONE, "[ Prisma Investimenti Spa - Amministrazione ]");

                                $newmail->setSubject('Nuova sostituzione programmata');
                                $messaggio = '';
                                $messaggio  = "Dettagli sostituzione: \n\n";
                                $messaggio .= "Tipo: " . $obj->getDescrizione() . "\n";
                                $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
                                $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
                                $messaggio .= "Giorni: " . $obj->getGiorni() . "\n";
                                $messaggio .= "Sede: " . ucfirst( $obj->getLocalita() ) . "\n\n";
                                $newmail->setBodyText($messaggio);
                            
                                try {
                                    if( Zend_Registry::isRegistered('sandbox') ) 
                                    {
                                        if( true == Zend_Registry::get('sandbox') ) {
                                            $newmail->clearFrom();
                                            $newmail->setFrom(AMMINISTRAZIONE, "[ SANDBOX ] Feriemanager");
                                            $newmail->addTo(DEVELOPER);
                                            $msg = "ATTENZIONE - MESSAGGIO DI PROVA.\n\n";
                                            $msg .= $messaggio;
                                            $newmail->setBodyText($msg);
                                        } else {
                                            $newmail->addTo($newMail);
                                        }
                                    } else {
                                        $newmail->addTo($newMail);
                                    }
                                    $newmail->send();

                                    $BM = new Application_Model_BudgetSostituzioniMapper();
                                    $rowset = $BM->findBySostituzione($sostituzione_id);
                                    if(count($rowset) > 0) 
                                    {
                                        $importo  = 0;
                                        foreach($rowset as $k => $row) {
                                            $importo += $row->importo;
                                        }

                                        $m = new Zend_Mail(); 
                                        $m->setFrom(AMMINISTRAZIONE, "[ Prisma Investimenti Spa - Amministrazione ]");
                                        $m->addTo($newMail);
                                        $m->setSubject('Budget assegnato');

                                        $messaggio = '';
                                        $messaggio = "Assegnato budget per la sostituzione n." . $sostituzione_id . "\n\n";
                                        $messaggio .= "Totale assegnato: Euro " . $importo . "\n\n";

                                        $messaggio .= "Dettagli sostituzione: \n";
                                        $messaggio .= "Tipo: " . $obj->getDescrizione() . "\n";
                                        $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
                                        $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
                                        $messaggio .= "Giorni: " . $obj->getGiorni() . "\n";
                                        $messaggio .= "Sede: " . ucfirst( $obj->getLocalita() ) . "\n\n";
                                        $m->setBodyText($messaggio);

                                        if( Zend_Registry::isRegistered('sandbox') ) 
                                        {
                                            if( true == Zend_Registry::get('sandbox') ) {
                                                $m->clearFrom();
                                                $m->setFrom(AMMINISTRAZIONE, "[ SANDBOX ] Feriemanager");
                                                $m->addTo(DEVELOPER);
                                            } else {
                                                $m->addTo($newMail);
                                            }
                                        } else {
                                            $m->addTo($newMail);
                                        }
                                        $m->send();
                                    }
                                } catch(Zend_Mail_Transport_Exception $e) {
                                    $sent = false;
                                    echo $e->getMessage();
                                }
                            } else {
                                $this->_helper->flashMessenger->addMessage(array(
                                    'error-mail' => 'Attenzione, impossibile inviare il messaggio a ' . $newSostituto->getAnagrafe(). '. Verificare la sua email'));
                            }
                        } /* @end: (sostituto > 0) */ 
                    } /* @end: (sandmail == true) */
                    $this->_redirect('/sostituzioni/list');
                } /* @end: token */
            } /* @end: token */
         } /*@end: request->isPost() */
         $token = Application_Service_Tools::generaToken();
         $_SESSION['form_token'] = $token;
         $this->view->token = $token;
    }
    
    /**
     * 
     */
    public function inviaNotaAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
         if($this->_request->isPost()) {
             
             if($this->_request->isXmlHttpRequest()) {
                 $sostituzione_id =  (int) $this->_request->getParam('sostituzione_id') ;
                 $note = utf8_decode($this->_request->getParam('note')) ;
                 $SM = new Application_Model_SostituzioniMapper();
                 $sostituzione = $SM->find($sostituzione_id);
                 $t_email  =  $sostituzione->getUser()->getEmail();
                 $sandboxSubject = '';
                 if( Zend_Registry::isRegistered('sandbox') ) 
                 {
                    $sandbox = Zend_Registry::get('sandbox');
                    if($sandbox) 
                    {
                        $sandboxSubject = '[ SANDBOX ] Feriemanager';
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
                 
                 
                 $SN = new Application_Model_SostituzioniNoteMapper();
                 $data = array( 
                    'sostituzione_id' => $sostituzione_id,
                    'note' => $note
                 );
                 
                 $AO = new Application_Model_AssenzaObject($sostituzione->getAssenza()->getAssenzaId());
                 $dal  = new Zend_Date( $sostituzione->getAssenza()->getDateStart());
                 $al   = new Zend_Date( $sostituzione->getAssenza()->getDateStop() ) ;
                 $mail = new Zend_Mail();
                 $mail->addTo($t_email);
                 $msg  =  "Invio nota relativa alla sostituzione numero: " . $sostituzione_id .  "\n\n";
                 $msg .= "NOTE: " .  $note . "\n\n";
                 $msg .= "Dettagli sostituzione: \n";
                 $msg .= "Tipo: " . $AO->getTipologia()->getDescrizione() . "\n";
                 $msg .= "Sede: " .  ucfirst( $AO->getLocalita() ). "\n";
                 $msg .= "Dal:  " . $dal->toString('dd MMMM YYYY') . "\n";
                 $msg .= "Al:   " . $al->toString('dd MMMM  YYYY')  . "\n\n";
                 $msg .= "PER MAGGIORI INFORMAZIONI VERIFICARE SULLA PROPRIA PAGINA DEL SITO http://62.149.161.214/feriemanager/";
                 $mail->setBodyText($msg) ;
                 $mail->setSubject('Nota per la sostituzione n. ' . $sostituzione_id . ". Messaggio del " . new Zend_Date());
                 $mail->setFrom('feriemanager@gmail.com', "[ $sandboxSubject Prisma Investimenti Spa - Amministrazione ]");
                 
                 
                 
                 try{
                     $SN->insert($data);
                     try {
                        //$google = Zend_Registry::get('google');
                        $mail->send();
                        echo Zend_Json::encode('Email inviata correttamente');
                        #@todo: qui il salvataggio nel db
                        try {
                            
                        } catch(Exception $e) {
                            
                        }
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
     * Modifica multipla
     * @throws Exception
     */
    public function multiplaAction() 
    {
         $SID =  (int) $this->_request->getParam('sostituzione_id') ;
         if(!is_int($SID) or ($SID == 0)) {
             throw new Exception('Errore nel numero della sostituzione');
         }  
         $this->view->sid = $SID;
         
         # oggetto sostituzione
         $sObj = $this->_table->find($SID);
        
         # oggetto assenza
         $AID =  $sObj->getAssenza()->getAssenzaId();
         $aObj = new Application_Model_AssenzaObject($AID);
                  
         //sede sostituzione
         $sede = ucfirst($aObj->getUser()->getSede()->getCitta());
         
         # sede id
         $sede_id =  $aObj->getUser()->getSede()->getSedeId();
         
         # sostituto attuale
         $this->view->attuale = $sObj->getUser();
         
         # recupero le date di inizio/fine
         $i =  $sObj->getAssenza()->getDateStart();
         $f =  $sObj->getAssenza()->getDateStop();
         
         $days = Application_Service_Tools::getArrayOfActualDays($i, $f, 'Y-m-d' , $sede_id);
         $values = array();
         $UM = new Application_Model_UserMapper();
         foreach($days as $k => $day) {
             # con questa action
             # il giorno di inizio e fine deve necessariamente essere lo stesso
             $sostituti = $UM->getSostitutiLiberi($day, $day);
             $values[$day] = $sostituti;
             $sostituti = null;
         }
         $this->view->values = $values;
         
         
         # --------- Salvataggio ---------------
         if($this->_request->isPost()) {
            $day = $this->_request->getParam('day');
            //converto le date in US
            foreach($day as $k => $v) {
                $g[] = Application_Service_Tools::convertDataItToUs($v);
            }
            $sostituto = $this->_request->getParam('sostituto');
            $sostituzioni = array();
            $totale = count($g) - 1;
            $start = false;
            $check = false;
            for($i = 0; $i <= $totale; $i++) {
                $j = $i + 1;
                if(isset($sostituto[$j])) {
                    $num = $sostituto[$j];
                } else {
                    $num = -1;
                }
                    if($sostituto[$i] != ($num)) {
                        if(false === $start) {
                            $start = $i;
                        }
                        $sostituzioni[] = array(
                            'inizio'    => $g[$start],
                            'fine'      => $g[$i],
                            'sostituto' => $sostituto[$i]  
                         );  
                        $start = false;
                        $check = false;
                         //break;
                     } else {
                        if($check == false) {
                            $start = $i;
                            $check = true;
                        } 
                    }
            }
            //print_r($sostituzioni);
         
         //-------------------------------------
         //             TRY
         //-------------------------------------   
         $db = Zend_Registry::get('db');
         $db->beginTransaction();
         try {
             $sandbox = Zend_Registry::get("sandbox");
             $assenzaId   = $aObj->getId();
             $richiestaId = $aObj->getRichiesta()->getId();
             $tipologiaId = $aObj->getTipologia()->getId();
             $userId      = $aObj->getUser()->getId();
             
             $AM = new Application_Model_AssenzeMapper();
             $EM = new Application_Model_EventiMapper();
             $SM = new Application_Model_SostituzioniMapper();
             //delete
             $AM->delete($assenzaId);
             $EM->deleteByAssenza($assenzaId);
             $SM->deleteByAssenza($assenzaId);
             
        
             $sostituto = $UM->find( $aObj->getSostituto()->getId() );
             
             # email sostituzione cancellata
             if($sostituto->getId() > 0 ) 
             {
                 global  $g_mail_sostituzione_new, $g_mail_sostituzione_delete;
                 
                 $oggi = new Zend_Date();
                 $stop = new Zend_Date( $f );
                 if($stop->compare($oggi) < 0) {
                        Prisma_Logger::log('invio email annullato: sostituzione già effettuata');
                 } else {
                        # invio email al sostituto
                        $t_assenza    = $aObj;
                        $t_email      = $sostituto->getEmail();
                        $validator    = new Zend_Validate_EmailAddress();
                        if($validator->isValid($t_email)) 
                        {
                            # check
                            if(ON == $g_mail_sostituzione_delete) 
                            {
                                $emailGateway = new Application_Model_Email_Sostituzione($t_email, $t_assenza, null, $sandbox);
                                $emailGateway->sendCanceled();
                                Prisma_Logger::log('email si sostituzione cancellato inviata a: ' . $sostituto->getAnagrafe() );
                            }  
                        } 
                 }
              } # fine
                    
              
             
             
             
             //INIZIO NUOVO INSERT (salvataggio di della sostituzione modificata)
             foreach($sostituzioni as $k => $sostituzione) {
                $i = $sostituzione['inizio'];
                $f = $sostituzione['fine'];
               
                $aValue = array(
                    'richiesta_id' => $richiestaId,
                    'user_id'      => $userId,
                    'tipologia_id' => $tipologiaId,
                    'sostituto_id' => $sostituzione['sostituto'],
                    'dateStart'    => $i,
                    'dateStop'     => $f,
                    'giorni'       => Application_Service_Tools::getTotalDays($i, $f, 'Y-m-d', $sede_id),
                    'date_insert'  => date('Y-m-d H:i:s')
                );
                
                # inserisco la tupla assenza
                $newAID = $AM->insert($aValue); 
                # aggiungo all'array anche l'assenza_id per poter creare l'oggetto assenza e inviarlo per email
                $sostituzioni[$k]['assenza_id'] = $newAID; 
                
                $sValue = array(
                    'assenza_id'  => $newAID,
                    'user_id'     => $sostituzione['sostituto'],
                    'date_insert' => date('Y-m-d H:i:s')
                );
                # inserisco la tupla sostituzione
                $SM->insert($sValue);
                
                # inserisco evento
                $evento = new Application_Model_Evento();
                $rows   = $evento->creaEventoDaAssenza($newAID);
                $EM->insertMultiple($rows);
             }
             //FINE INSERT
             
             //TODO: log
                        
             # inserisco email di nuova sostituzione
             foreach($sostituzioni as $k => $v) {
                Prisma_Logger::log($sostituzioni);
                $sid       =  $v['sostituto'];
                $sostituto = $UM->find( $sid );
                $dati = array(
                    'Oggetto' =>  'Nuova sostituzione programmata <br>',
                    'Dal'  => Application_Service_Tools::convertDataUsToIt($v['inizio']),
                    'Al'   => Application_Service_Tools::convertDataUsToIt($v['fine']),
                    'Sede' => $sede,
                    'Tipo' => $aObj->getTipologia()->getDescrizione()
                 );
                
                if($sid > 0) 
                {
                    $oggi = new Zend_Date();
                    $stop = new Zend_Date( $v['fine'] );
                    if($stop->compare($oggi) < 0) {
                        Prisma_Logger::log('invio email annullato: sostituzione già effettuata');
                    } else {
                        # invio email al sostituto
                        $t_email   = $sostituto->getEmail();
                        $aid = (int)$v['assenza_id'];
                        $t_assenza = new Application_Model_AssenzaObject($aid);
                        $validator = new Zend_Validate_EmailAddress();
                        if($validator->isValid($t_email)) 
                        {
                            # check
                            if(ON == $g_mail_sostituzione_new) 
                            {
                                $emailGateway = new Application_Model_Email_Sostituzione($t_email, $t_assenza, null, $sandbox);
                                $emailGateway->sendNew();
                                Prisma_Logger::log('Invio email al sostituto: ' . $t_email);
                            }  
                        } 
                    }
                }
             }
             $db->commit();
             $this->_helper->flashMessenger->addMessage(array(
                         'success' => 'Sostituzione aggiornata con successo'));
            } catch(Exception $e) {
                    $this->_helper->flashMessenger->addMessage(array(
                        'error' => 'Errore. Non è possibile aggiornare la sostituzione' . $e->getMessage()));
                    $db->rollBack();
            }
            $this->_redirect('sostituzioni/list/');
         }
         
         
         
         
    }
    
    
    
    
    
}


