<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AjaxController
 *
 * @author Luca
 */
class AjaxController extends Zend_Controller_Action {
     
    
    public function init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
    
    public function preDispatch() {
    
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
        }
               
        $this->_user_id  = Zend_Auth::getInstance()->getIdentity()->user_id;
        $this->_level_id = Zend_Auth::getInstance()->getIdentity()->level_id;
               
        $this->_um = new Application_Model_UserMapper();
        $this->_user = $this->_um->find( $this->_user_id );
        
        if(false == $this->_user->isActive()) {
            $this->_redirect('auth/logout');
        }
    }
    
    
    /**
     * Carica le risposte standard per rifiutare le richieste
     * 
     */
    public function loadStandardResponseAction() 
    {
        $mapper = new Application_Model_ResponseMapper();
        $entries = $mapper->fetchAll();
        echo Zend_Json::encode($entries);
    }
    
     
    
    /**
     * Invio email di rifiuto richiesta
     * @global type $g_mail_richiesta_refused
     * @return boolean
     */
    public function sendRefusedAction() 
    {
        global $g_mail_richiesta_refused;
        $uid          = $this->_user_id;
        $richiesta_id = $this->_getParam('richiesta_id');
        $response     = trim( $this->_getParam('std_response') );
        $note         = trim( $this->_getParam('note') );

        if($response !== '') {
            $note = $response . '; ' . $note;
        }
        $data = array(
            'richiesta_id' => $richiesta_id,
            'note'   => $note,
            'status' => 3
        );
        $rm = new Application_Model_RichiesteMapper();
        $um = new Application_Model_UserMapper();
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {

            $status = $rm->update($data);
            $row    = $rm->find($richiesta_id); 
            $t_user = $um->find( $row->user_id );
            
            
            ############################################################################
            // ---------   LOGICA PER AGGIORNAMENTO VALORI DETTAGLI/RESIDUI    --------- 
            # recupero la/le tuple, con i giorni da stornare, relative alla richiesta_id
            $m = new Application_Model_DbTable_RichiesteDettagli();
            $rs = $m->findByRequest($richiesta_id);
            # per ogni tupla presente,  aggiorno la tabella richieste_residui, altrimenti non faccio nulla
            if($rs->count() > 0) {
                foreach($rs as $k => $rowDtl) {
                    $data = array(); 
                                       
                    $data['assigned'] = new Zend_DB_Expr("assigned - $rowDtl->days");
                    
                    // se la richiesta è fatta da user allora storno il suo contatore richieste
                    if((int)$row->user_id == (int)$row->created_by_user_id) {
                        $data['assigned_by_user_id'] = new Zend_DB_Expr("assigned_by_user_id - $rowDtl->days");
                    }
                    
                    $where[] = "user_id = $rowDtl->user_id";
                    $where[] = "tipologia_id = $rowDtl->tipologia_id";
                    $where[] = "year = $rowDtl->year";
                    $db->update('richieste_residui', $data, $where);
                    unset($data);
                    unset($where);
                }
            }
            # cancello richieste dettagli dove c'è quella richiesta_id
            $deleted = $db->delete('richieste_dettagli', "richiesta_id = $richiesta_id");
            // --------------------------------------------------------------------------
                       
             
            // INVIO EMAIL
            if(ON == $g_mail_richiesta_refused) {
                    
                $now  = new Zend_Date() ;
                $mail = new Zend_Mail();
                $helper = $this->view->getHelper('tipologia');
                $mail->setFrom(AMMINISTRAZIONE, '[ FerieManager ] Amministrazione Prisma Investimenti')
                        ->setSubject( sprintf("Richiesta n.%s  rifiutata in data %s.", $row->richiesta_id, $now->toString('dd/MM/yy')) );

                $messaggio  = "Richiesta rifiutata per {$t_user->getCognome()} {$t_user->getNome()} : \n\n";
                $messaggio .= "Dettagli della richiesta: \n";
                $messaggio .= "Tipo di richiesta: {$helper->tipologia($row->tipologia_id)} \n";
                $messaggio .= "Dal giorno:  " . Application_Service_Tools::convertToIt($row->dateStart) .  "\n";
                $messaggio .= "Al giorno:   " . Application_Service_Tools::convertToIt($row->dateStop) . "\n";
                $messaggio .= "Totale giorni richiesti: " . $row->giorni . "\n\n";
                $messaggio .= "Note: "   . $note . "\n\n";   
                $messaggio .= INFO_SITO_MSG . "\n\n";
                $messaggio .= DO_NOT_REPLY_MSG;

                if (Zend_Registry::isRegistered('sandbox')) {
                    if(true == Zend_Registry::get('sandbox') ) {
                        $mail->clearFrom();
                        $mail->setFrom(AMMINISTRAZIONE, '[ SANDBOX ] Feriemanager');
                        $mail->addTo( DEVELOPER );
                        Prisma_Logger::log( '[SANDBOX MODE] Invio email al developer: ' . DEVELOPER );
                    } else {
                        $mail->addTo( $t_user->getEmail() );
                    }
                }
                $mail->setBodyText( $messaggio );
                $mail->send();
            }
           
            // LOGGA SU DB L'EVENTO RICHIESTA
            $text = "Richiesta [in lavorazione] #{$richiesta_id} rifiutata dall'utente con id #{$uid}";
            $logData = array(
                'level'       => 'richiesta',
                'facility'    => 'rifiutata',
                'user_id'     => $uid,
                'address'     => $_SERVER['REMOTE_ADDR'],
                'descrizione' => $text
             );
             $logMapper = new Application_Model_LogMapper(); 
             $logMapper->addEvent($logData);
             
            // COMMIT DB
            $db->commit();
            
            echo Zend_Json::encode( array('success' => true) );
        } catch(Exception $e) {
            $db->rollBack();
            echo Zend_Json::encode( array('success' => false, 'error' => $e->getMessage() ) );
        }
    }
    
    /**
     * Cancella una richiesta già accettata
     * 
     */
    public function deleteAcceptedRequestAction()
    {
        $richiesta_id = $this->_request->getParam('id');
        $send_mail    = $this->_request->getParam('send_mail');
        $uid          = $this->_user_id;
        $oggi         = new Zend_Date(date('Y-m-d'));
        
        $AM = new Application_Model_AssenzeMapper();
        $RM = new Application_Model_RichiesteMapper();
        $UM = new Application_Model_UserMapper();
        $TM = new Application_Model_TipologiaMapper();
        
        $a  = $AM->findByRequest($richiesta_id);
         
        $sostituto_id = 0;
        if($a) {
            $t_assenza_id = $a->getAssenzaId();
            $sostituto_id = $a->getSostitutoId();
            $start        = new Zend_Date($a->getDateStart() );
            $stop         = new Zend_Date($a->getDateStop() );
            $user         = $UM->find($a->getUserId());
            $sede         = $user->getSede()->getCitta();
            $tipologia    = $TM->find($a->getTipologiaId());
             
        }
        $row = $RM->find($richiesta_id);
        $obj_assenza = new Application_Model_AssenzaObject($t_assenza_id);         
        
        # cancello richieste accettate/non accettate
        $transaction = new Application_Model_TransactionMapper();
        
        try {
            global $g_mail_sostituzione_delete, $g_mail_assenza_annulled;
            
            #### UPDATE AGGIORNAMENTI AGGREGATI
            $transaction->removeRequest($richiesta_id);
            $sandbox = Zend_Registry::get("sandbox");
            
            ####### Email utente per assenza annullata
            if(!$sandbox) {
                $userMail = $user->getEmail();
            } else {
                $userMail = EMAIL_DEVELOPER;
            }
            
            $validator = new Zend_Validate_EmailAddress();
            if($validator->isValid($userMail)) {
                
                $mailA = new Zend_Mail();
                $pre = "[ FerieManager ]";
                if($sandbox) {
                    $pre = "[ SANDBOX MODE ]";
                }
                
                $mailA->setFrom(AMMINISTRAZIONE, $pre . " Amministrazione Prisma Investimenti");
                $mailA->addTo($userMail);
                $mailA->setSubject( "Richiesta n.$richiesta_id annullata in data " . $oggi->toString('dd/MM/yyyy') );
                $msg = "Attenzione, l'amministrazione ha annulato la tua richiesta n.$richiesta_id  precedentemente accettata.";
                $mailA->setBodyHtml($msg);
               if(ON == $g_mail_assenza_annulled) {
                   $mailA->send();
               }
            }
            ##### FINE EMAIL UTENTE
            
            
            
            if($send_mail == "true") {
                if( $sostituto_id > 0 ) {
                    // se l'assenza è in corso
                    if($stop->compare($oggi) >= 0) {
                        $sostituto = $UM->find($sostituto_id);
                        $t_email   = $email = $sostituto->getEmail();
                        $validator = new Zend_Validate_EmailAddress();
                        if($validator->isValid($email)) {
                            $emailGateway = new Application_Model_Email_Sostituzione($t_email, $obj_assenza, '', $sandbox);
                            try {
                                if(ON == $g_mail_sostituzione_delete) 
                                {
                                    Prisma_Logger::log( 'Sostituzione cancellata => email di notifica inviata. ' );
                                    $emailGateway->sendCanceled();
                                } else 
                                {
                                    Prisma_Logger::log( 'Invio email disabilitato ' ); 
                                }
                            } catch(Exception $e) {
                                #@logga e invia mail oppure email queue
                                Prisma_Logger::log('errore invio email ' . $e->getMessage());
                            }
                        } # validator   
                    } # assenza in corso o da eseguire
                } # sostituro > 0
            } # send mail
            
            
            
            ##### LOGGA SU DB L'EVENTO RICHIESTA
            $text = "Richiesta [accettata] #{$richiesta_id} cancellata dall'utente con id #{$uid}";
            $logData = array(
                'level'       => 'richiesta',
                'facility'    => 'cancellata',
                'user_id'     => $uid,
                'address'     => $_SERVER['REMOTE_ADDR'],
                'descrizione' => $text
            );
            $logMapper = new Application_Model_LogMapper(); 
            $logMapper->addEvent($logData);
            ### FINE LOG
            
            
            echo Zend_Json::encode( array('success' => true) );
        } catch(Exception $e) {
            echo Zend_Json::encode( array('success' => false, 'error' => $e->getMessage() ) );
        }
    }   
    
    /**
     * 
     * Cancella una richiesta in lavorazione
     * 
     */
    public function deleteProcessingRequestAction() {
        
        $richiesta_id = $this->_request->getParam('id');
        //$uid      = $this->_request->getParam('uid');
        $uid          = $this->_user_id;
        $RM = new Application_Model_RichiesteMapper();
        $data = array(
            'richiesta_id' => (int)$richiesta_id,
            'status' => 4
        );
        
        $req = $RM->find($richiesta_id);
      //  if( ( count($req) == 0) ) {
            if( ( !$req ) ) {
            echo Zend_Json::encode( array('success' => false, 'error' => 'Attenzione! La richiesta non risulta presente nel database.' ));
            return;
        } elseif ($req->status != 0) {
            echo Zend_Json::encode( array('success' => false, 'error' => "'Attenzione! La richiesta $richiesta_id risulta in uno stato diverso da 'in lavorazione'") );
            return;
        }
        
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $updated = $RM->update($data);
            
            ############################################################################
            // ---------   LOGICA PER AGGIORNAMENTO VALORI DETTAGLI/RESIDUI    --------- 
            # recupero la/le tuple, con i giorni da stornare, relative alla richiesta_id
            $m = new Application_Model_DbTable_RichiesteDettagli();
            $rs = $m->findByRequest($richiesta_id);
            # per ogni tupla presente,  aggiorno la tabella richieste_residui, altrimenti non faccio nulla
            if($rs->count() > 0) {
                foreach($rs as $k => $rowDel) {
                    $data = array(); 
                    $data['assigned'] = new Zend_DB_Expr("assigned - $rowDel->days");
                    
                    //Prisma_Logger::log($req);
                   // Prisma_Logger::log($req->created_by_user_id);
                    //Prisma_Logger::log($req->user_id);
                    
                    if((int)$req->created_by_user_id == (int)$req->user_id ) {
                       // Prisma_Logger::log('cancello quello creato dall user');
                        $data['assigned_by_user_id'] = new Zend_DB_Expr("assigned_by_user_id - $rowDel->days");
                    } else {
                       // Prisma_Logger::log('NON cancello quello creato dall user');
                    }
                    
                    
                    $where[] = "user_id = $rowDel->user_id";
                    $where[] = "tipologia_id = $rowDel->tipologia_id";
                    $where[] = "year = $rowDel->year";
                    $db->update('richieste_residui', $data, $where);
                    unset($data);
                    unset($where);
                }
            }
            # cancello richieste dettagli dove c'è quella richiesta_id
            $deleted = $db->delete('richieste_dettagli', "richiesta_id = $richiesta_id");
            // --------------------------------------------------------------------------
                                   
            // LOGGA SU DB L'EVENTO RICHIESTA
            $text = "Richiesta [in lavorazione] #{$richiesta_id} cancellata dall'utente con id {$uid} ";
            $logData = array(
                'level'       => 'richiesta',
                'facility'    => 'cancellata',
                'user_id'     => $uid,
                'address'     => $_SERVER['REMOTE_ADDR'],
                'descrizione' => $text
             );
             $logMapper = new Application_Model_LogMapper(); 
             $logMapper->addEvent($logData);
             
             # commit db
             $db->commit();
             echo Zend_Json::encode( array('success' => true, 'message' => "Richiesta $richiesta_id cancellata con successo.") );
        } catch(Exception $e) {
            $db->rollBack();
            echo Zend_Json::encode( array('success' => false, 'error' => $e->getMessage()) );
        }
    }
    
    
    /**
     * Richiesta di cancellazione richiesta accettata
     * 
     * 
     */
    public function askCancellationRequestAcceptedAction()
    {
        $rid = $this->_request->getParam('rid');
        $uid = $this->_request->getParam('uid');
        
        $UM = new Application_Model_UserMapper();
        $user = $UM->find($uid);
        $now = new Zend_Date();
        
        
        $RM = new Application_Model_RichiesteMapper();
        
        $richiesta = $RM->find($rid);
        if(!$richiesta) {
            echo Zend_Json::encode(array('success' => true, 'message' => "Attenzione. Richiesta n.$rid non trovata."));
            return;
        }
        
        $status_richiesta = $richiesta->getStatus();
        switch($status_richiesta) {
            case 3:
                echo Zend_Json::encode(array('success' => true, 'message' => "Attenzione. La richiesta n.$rid risulta rifiutata."));
                return;
                break;
            case 4:
                echo Zend_Json::encode(array('success' => true, 'message' => "Attenzione. La richiesta n.$rid risulta annullata."));
                return;
                break;
        }
        
            
        
        
        $reqM = "Richiesta n.{$rid}<br>";
        $reqM .= "Dal: {$richiesta->getDateStart()->toString('dd/MM/yyyy')}<br>";    
        $reqM .= "Al: {$richiesta->getDateStop()->toString('dd/MM/yyyy')}<br>";    
        $reqM .= "Tipologia: {$richiesta->getTipologia()->getDescrizione()}";
        $subject = "Richiesta di cancellazione ferie/permessi del " . $now->toString('dd/MM/yyyy') ;
        $body = "L'utente {$user->getAnagrafe()} richiede la cancellazione della richiesta numero {$rid} precedentemente accettata dall'amministrazione.<br><br>";
        $body.= "Dettagli:<br> $reqM";
        
        $mail = new Zend_Mail();
        
        $mail->setSubject( $subject );
        $mail->setBodyHtml($body);
        
        // processo di invio email
        $sandbox = Zend_Registry::get('sandbox');
        if($sandbox) {
            $mail->setFrom(AMMINISTRAZIONE, "[ SANDBOX MODE ] FerieManager :: Richiesta Cancellazione");
            $mail->addTo(EMAIL_DEVELOPER);
        } else {
            $mail->setFrom(AMMINISTRAZIONE, "[ FerieManager :: Messaggio Automatico]");
            $mail->addTo(CARLOTTA);
            $mail->addTo(MAURA);
        }
        
        
        try {
            $mail->send();
            echo Zend_Json::encode(array('success' => true, 'message' => 'Email inviata correttamente.'));
        } catch (Exception $e) {
            echo Zend_Json::encode(array('success' => true, 'message' => $e->getMessage()));
        }
    }
    
    public function inserisciAssenzaAction()
    {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        
        $dati = $this->_request->getParam('dati');
        # conto quante chiavi sono presenti e in base a questo faccio la logica
        $numOfArrays = (int) count($dati);
        
        ### algoritmo in base alle chiavi presenti
        $RM = new Application_Model_RichiesteMapper();
        # una sola key
        if( $numOfArrays  == 1 )  {
            $nuova = $dati[0];
            $richiesta_id = (int) $dati[0]['richiesta'];
            $R = $RM->find($richiesta_id);
            
            # se non ho fatto modifiche alla richiesta aggiorno la richiesta esistente
            if( ($R->dateStart == Application_Service_Tools::convertDataItToUs($nuova['inizio']))  &&
                    ($R->dateStop == Application_Service_Tools::convertDataItToUs($nuova['fine'])) ) 
                {
                    # accetto la richiesta originaria 
                    $R->status = ACCETTATO;
                    try {
                        
                        $R->save();
                    } catch(Exception $e) {
                        echo Zend_Json::encode(array('success' => false, 'error' => $e->getMessage()));
                    }
                }
            # se ho modificato la richiesta esistente allora...
            else 
                {
                    try {
                        # annullo la richiesta originaria
                        $R->status = ANNULLATO;
                        $R->save();
                                                                        
                        # recupero le quantita per ogni anno riferibili alla richiesta id
                        $sql = "SELECT * FROM richieste_dettagli WHERE richiesta_id = ?";
                        $tuple = $db->fetchAll($sql, $R->richiesta_id);
                        
                        # per ogni anno recuperato , aggiorno i residui utilizzando le quantita
                        foreach($tuple as $k => $tupla) {
                            $data = array('assigned' => new Zend_Expr("assigned - $tupla->days"));
                            # se la richiesta è stata fatta da user allora storno quelle scelte da lui
                            if((int)$R->user_id === (int)$R->created_by_user_id) {
                                $data['assigned_by_user_id'] =  new Zend_Expr("assigned_by_user_id - $tupla->days");
                            }
                            $db->update('richieste_residui', $data , "richiesta_id = $tupla->year");
                        }
                        # cancello i dettagli vecchi
                        $db->delete('richieste_dettagli', "richiesta_id = $R->richiesta_id");
                        
                    } catch(Exception $e) {
                        echo Zend_Json::encode(array('success' => false, 'error' => $e->getMessage()));
                    }
                }
            
            
        } 
        # key maggiore di 1
        elseif( $numOfArrays  > 1 ) {
            
        }
        # se 0 esco
        else {
            return;
        }
    } 

    /**
     * Inserimento nuovo contratto
     * 
     */
    public function userInsertNewContractAction()
    {
        $params = $this->_request->getParams();
        $cid   = $params['cid'];
        $uid   = $params['uid'];
        $start = $params['start'];
        $stop  = $params['stop'];
        //19/05/2021
        $bisettimanale = $params['bisettimanale'];

        Prisma_Logger::logTofile(json_encode($params));

        if($stop == '') {
            $stop = null;
            $cstop = date('Y-m-d');
        } else {
            $cstop = $stop;
        }
        if($stop != null) {
            if($stop < $start) {
                 echo Zend_Json::encode( array('success' => false, 'error' => 'Errore! La data di fine contratto è antecedente a quella di inizio.') );
                 return;
            }
        }
                
        $cid   = $params['cid'];
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            # cerco incompatibilità
            $sql = "SELECT * FROM users_contracts 
                        WHERE user_id = ? 
                    AND ( '$start' <= stop AND '$cstop' >= start )
                        AND deleted = '0'
                    ";
            Prisma_Logger::logToFile($sql);
            $rs = $db->fetchAll($sql, $uid);
            if(count($rs) > 0) {
                echo Zend_Json::encode( array('success' => false, 'error' => 'Errore! Date incompatibili con contratti inseriti precedentemente.') );
            } else {
                $data_to_update = array('last' => 0);
                $where  = "user_id = {$uid}";
                $db->update('users_contracts', $data_to_update, $where);
                $data_to_update = array('contratto_id' => $cid);
                $db->update('users', $data_to_update, $where);
                
                
                $data = array(
                    'contratto_id' => $cid,
                    'user_id' => $uid,
                    'start' => $start,
                    'stop'  => $stop,
                    'misto' => $bisettimanale, //19/05/2021
                    'last'  => 1
                );
                $db->insert('users_contracts', $data);
                $db->commit();
                echo Zend_Json::encode( array('success' => true, 'message' => 'Contratto inserito correttamente') );
            }
            
        } catch(Exception $e) {
            $db->rollBack();
            echo Zend_Json::encode( array('success' => false, 'error' => $e->getMessage()) );
        }
    }
    
    /**
     * Chisura contratto
     */
    public function userCloseContractAction()
    {
        $params = $this->_request->getParams();
        $id    = $params['id'];
        $stop  = $params['stop'];
        $data = array(
            'stop' => $stop,
            'last' => 1
        );
        
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->update('users_contracts', $data, "id={$id}");
            $db->commit();
            $result = array(
                'success' => true,
                'message' => 'Contratto chiuso!'
            );
            
        } catch(Exception $e) {
            $db->rollBack();
            $result = array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
        echo Zend_Json::encode($result); 
    }
    
    /**
     * Aggiorna contratto
     */
    public function userUpdateContractAction()
    {
        $params = $this->_request->getParams();
        $uid   = $params['uid'];
        $start = $params['start'];
        $stop  = $params['stop'];
        $cid   = $params['cid'];
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            
            $sql = 'SELECT * FROM users_contracts WHERE user_id = ? AND last = 1';
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $rs = $db->fetchAll($sql, $uid);
            if(count($rs) > 0) {
                $row = $rs[0];
                $stop = new Zend_Date($start);
                $stop->subDay(1); 
                $new_stop = $stop->toString('yyyy-MM-dd');
                $data = array(
                    'stop' => $new_stop
                );
                $where  = "user_id = {$uid}";
                $db->update("users_contracts", $data, $where);
                 
                 
            }
            $data_to_update = array('last' => 0);
            $where  = "user_id = {$uid}";
            $db->update('users_contracts', $data_to_update, $where);
            
            $data = array(
                'contratto_id' => $cid,
                'user_id'      => $uid,
                'start'        => $start,
                'last'         => 1
            );
            $db->insert('users_contracts', $data);
            
            $db->commit();
            
            $sql = 'SELECT * FROM users_contracts WHERE user_id = ?';
            $rs = $db->fetchAll($sql, $uid);
            
            $result = array(
                'success' => true,
                'rs'      => $rs
            );
            
            echo Zend_Json::encode($result); 
            
        } catch(Exception $e) {
            $db->rollBack();
            $result = array(
                'success' => false,
                'error'   => $e->getMessage()
            );
            echo Zend_Json::encode($result);
        }
    }
    
    ##############################################
    ## OPZIONI CONFIGURAZIONE VARIABILI DAL DB  ##
    ##############################################
    
    /**
     * 
     */
    public function saveGlobalConfigValueAction()
    {
        $params      = $this->_request->getParams();
        $name        = $params['name'];
        $value       = $params['value'];
        $description = $params['description'];
        $data = array(
            'name' => $name,
            'value' => $value,
            'description' => $description
        );
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->insert('configuration', $data);
            $id = $db->lastInsertId();
            $db->commit();
            $result = array(
                'success' => true,
                'configuration_id' => $id
            );
        } catch (Exception $e) {
            $db->rollBack();
            $result = array(
                'success' => false,
                'error'   => $e->getMessage()
            );
        }
        echo Zend_Json::encode($result);
    }        
        
    /**
     * 
     */
    public function changeGlobalConfigValueAction()
    {
        $params = $this->_request->getParams();
        
        $id     = $params['id'];
        $value  = (int)($params['value']);
            
        $value ? $value = 0 : $value = 1;
        $data_to_update = array('value' => $value);
        $where  = "configuration_id = {$id}";
                
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->update('configuration', $data_to_update, $where);
            $db->commit();
            $result = array(
                'success' => true,
                'value'   => $value
            );
        } catch(Exception $e) {
            $db->rollBack();
            $result = array(
                'success' => false,
                'error'   => $e->getMessage()
            );
        }
        echo Zend_Json::encode($result);
    }
    
    /**
     * 
     */
    public function deleteGlobalConfigValueAction()
    {
        $id = $this->_request->getParam('id');
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->delete('configuration', "configuration_id=$id");
            $db->commit();
            $result = array(
                'success' => true 
            );
        } catch(Exception $e) {
            $db->rollBack();
            $result = array(
                'success' => false,
                'error'   => $e->getMessage()
            );
        }
        echo Zend_Json::encode($result);
    }
    
}
