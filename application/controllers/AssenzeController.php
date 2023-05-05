<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AssenzeController
 *
 * @author Luca
 */
require_once APPLICATION_PATH . '/models/Email/Sostituzione.php';
require_once APPLICATION_PATH . '/models/Email/Richiesta.php';
require_once APPLICATION_PATH . '/models/Email/Assenza.php';

class AssenzeController extends Zend_Controller_Action 
 {
   
    public function init() {
        $this->_helper->_layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_db = new Application_Model_AssenzeMapper();
        //$this->_user_id  = Zend_Auth::getInstance()->getIdentity()->user_id;        
    }
              
    public function listAction() {
        $this->_helper->_layout->enableLayout();
        //$this->_helper->viewRenderer->setNoRender(false);
    }
    
    public function cercaAction() {
        $i = trim($this->_request->getParam("start"));
        $f = trim($this->_request->getParam("stop"));
        $t = trim($this->_request->getParam("tipologia_id"));
        //$sostituti = $this->_db->findSostitutiBYDate($i, $f);
        $sostituti = $this->_db->elencoSostitutiLiberi($i, $f, $t);        
        echo Zend_Json::encode($sostituti);
        //print_r($s);
    }
    
    
    
    /**
     * Funzione invocata da un'ajax request della view
     */
    public function insertAction() {
        
        global $g_enable_check_residui_for_admin;
        $now = new Zend_Date();

        //recupero l'array di dati da richieste/assegna.phtml
        $rowset = $this->_request->getParam('dati');


         //Zend_Debug::dump($rowset);

        //Prisma_Logger::logToFile(  $dump);

        //ob_start();
        //var_dump($rowset);
        //$output = ob_get_clean();

        $richiesteMap = new Application_Model_RichiesteMapper();
        //Prisma_Logger::log('array javascriot presenti: ' . count($rowset));
        $UR = new Application_Model_UserResiduiMapper();   
        
        //per ogni ARRAY nella COLLECTION
        $i = 0;
        foreach($rowset as $row)
        {
            $i++;
            //Prisma_Logger::log('richiesta numero: ' . $row['richiesta']);
            //Prisma_Logger::log('riga: ' . $i);
            $richiesta_id = $row['richiesta'];
            Prisma_Logger::logToFile("Richiesta id:"  . $richiesta_id  );
            $requestObj   = $richiesteMap->find($richiesta_id);
            
            Prisma_Logger::logToFile("tipo :" . json_encode((array) $requestObj->getTipologia() )  );




            # se la richiesta risulta già evasa allora passo alla successiva (serve per evitare doppioni)
            #@todo: bug - se la sostituzione è doppia e metto continue allora non processa la riga
            if($requestObj->status == 1) { return; }
            
            #todo: attenzione questa procedura non avvisa della mancanza di ferie permessi disponibili ma blocca la procedura anche se non sembra
            # eseguo un controllo sui residui
            $enum = '';

            
            
            
            $status_id = (int)$row['status'];
            # se accetto senza sostituto allora il sostituto_id = 0
            if($status_id == 2) {
                $sostituto_id = 0;
            } elseif($status_id == 1) {
                $sostituto_id = $row['sostituto'];
            }
            $user_id = $row['utente'];
            // @TODO: attenzione a removeSign ereg_replace deprecato
            $annotazioni  = Prisma_Utility_String::removeSign( trim($row['note']) );
            
            // TAB ASSENZE
            $assenza = array(
                'richiesta_id' => $richiesta_id,
                'user_id'      => $row['utente'],
                'sostituto_id' => $sostituto_id,
                'tipologia_id' => $row['tipologia'],
                'dateStart'    => Application_Service_Tools::convertDataItToUs($row['inizio']),
                'dateStop'     => Application_Service_Tools::convertDataItToUs($row['fine']),
                'date_insert'  => date('Y-m-d H:i:s')

            );
            # checkbox on/off di invio email al sostituto
            $t_email_to_substitute = $row['email'];
            
            $oggi = new Zend_Date(date('Y-m-d'));
            $stop = new Zend_Date( $row['fine'] );

            // per malattia/maternità devo aggiungere la domenica
            if( $requestObj->getTipologia()->isMalattia() || $requestObj->getTipologia()->isMaternita() ) {
                $giorni = Application_Service_Tools::getTotalDays($assenza['dateStart'], $assenza['dateStop'],false);
            } else {               
                // @since 24/03/18
                // se il richiedente ha una sede allora il calcolo dei giorni deve tenere conto anche del patrono
                $UM =  new Application_Model_UserMapper();
                $user  = $UM->find($row['utente']);
                if($user->hasSede()) {
                    $sedeId = $user->getSede()->getSedeId();
                    Prisma_Logger::logToFile("l'utente ha una sede: "  . $sedeId);
                    // calcolo i giorni con eventuale patrono
                    $giorni = Application_Service_Tools::getTotalDays($assenza['dateStart'], $assenza['dateStop'], true, $sedeId);
                } else {
                    $giorni = Application_Service_Tools::getTotalDays($assenza['dateStart'], $assenza['dateStop']);
                }
            }

            # inserisco i giorni per ogni tipo di accettazione
            $assenza['giorni'] = $giorni;

/*
            //@refactoring 03/06/2016 aggiunta quantita per permettere una flessibilità nei permessi
            if($row['tipologia'] == PERMESSO_MATTINA || $row['tipologia'] == PERMESSO_SERA) {                
               // $assenza['qta']  = $row['qta'];
                //12/04/2021
                //$assenza['qta']  = $row['qta'] * $giorni;
            } else {
             //   $assenza['qta']  = $giorni;
            }
*/
           
            /**
             * se l'assenza è oraria registro quante ore ho segnato
             * 
             * @12/04/2021
             */
            if( $requestObj->getTipologia()->isAssenzaOraria() ) {
                $assenza['is_oraria'] = 1;
                $assenza['qta']       = $row['qta']; 
            } else {
                $assenza['qta']       = $giorni;
            }

            $db = Zend_Registry::get('db');
            $db->beginTransaction();
            try {
                Prisma_Logger::logToFile("##### START TRANSACTION");
                # assenze: inserisco assenza
                $lastid = $this->_db->insert($assenza);
                # -------------------------------------
                
                # sostituzioni: inserisco la sostituzione anche se è senza sostituto
                $sostituzione = array(
                    'assenza_id'   => $lastid,  // POSSO METTERE L'ID DELLA RICHIESTA?
                    'user_id'      => $row['sostituto'], //$sostituto_id
                    'struttura_id' => $row['struttura_id'] ? $row['struttura_id'] : 0,
                    'date_insert'  => date('Y-m-d H:i:s')
                );
                $sostituzioniMap = new Application_Model_SostituzioniMapper();
                $sostituzioniMap->insert($sostituzione);
                # ---------------------------------------
                
                # eventi: inserisco gli eventi
                $evento    = new Application_Model_Evento();
                $rows      = $evento->creaEventoDaAssenza($lastid);
                $eventiMap = new Application_Model_EventiMapper();
                $eventiMap->insertMultiple($rows);
                # ----------------------------------------
               
                # salvataggio
                //Prisma_Logger::log('committing...');
                $db->commit();
                Prisma_Logger::logToFile("##### COMMIT: END TRANSACTION");
                 
            } catch(Exception $e) {
                $db->rollBack();
                $checkpoint = "assenze::insert::1 -> ";
                Prisma_Logger::logToFile($checkpoint . $e->getMessage());
                Prisma_Logger::log( $e->getMessage());
                Prisma_Logger::logToFile("##### ERROR ROLLBACK: END TRANSACTION");
            }
                        # se inserisco in ritardo rispetto a oggi qualche assenza passata,
            # per problemi tecnici etc, l'email non viene inviata a nessuno
            # Prisma_Logger::log('stop: ' . $stop->getTimestamp());
            # Prisma_Logger::log('oggi: ' . $oggi->getTimestamp());
            if($stop->compare($oggi) < 0) {
                //Prisma_Logger::log('invio email annullato: sostituzione già effettuata');
            } else {
                ####################################
                ########## INVIO EMAIL NUOVA ASSENZA
                ####################################
                try {
                    Prisma_Logger::logToFile("######  INVIO EMAIL NUOVA ASSENZA");
                    global $g_mail_assenza_accepted, $g_mail_sostituzione_new;
                    $sandbox = Zend_Registry::get("sandbox");

                    $UM   = new Application_Model_UserMapper();

                    $t_user_id      = $row['utente'];
                    Prisma_Logger::logToFile("user: " . $t_user_id);

                    $t_user         = $UM->find($t_user_id);

                    $t_email_user   = $t_user->getEmail();
                    Prisma_Logger::logToFile("email user: " . $t_email_user);

                    $t_assenza_id   = $lastid;
                    Prisma_Logger::logToFile("assenza id : " . $t_assenza_id);

                    /* xxx */
                    $t_assenza    =  new Application_Model_AssenzaObject($t_assenza_id);
                    Prisma_Logger::logToFile("assenza: " . json_encode($t_assenza));

                    # va messo prima del sostituto
                    if(ON == $g_mail_assenza_accepted)
                    {
                        $mail_to_user = new Application_Model_Email_Richiesta($t_user, $t_assenza, null, $sandbox);
                        $mail_to_user->sendAccepted();
                    }

                    if($sostituto_id > 0)
                    {
                        # se non seleziono la checkbox non invio l'email al sostituto
                        if(true == $t_email_to_substitute)
                        {
                            # invio email al sostituto
                            $t_sostituto_id = $row['sostituto'];

                            Prisma_Logger::logToFile("sostituto: " . $t_sostituto_id);
                            $sostituto = $UM->find($t_sostituto_id);

                            $t_email   = $sostituto->getEmail();
                            $validator = new Zend_Validate_EmailAddress();
                            if($validator->isValid($t_email))
                            {
                                # check
                                if(ON == $g_mail_sostituzione_new)
                                {
                                    $emailGateway = new Application_Model_Email_Sostituzione($t_email, $t_assenza, $annotazioni, $sandbox);
                                    $emailGateway->sendNew();
                                    //Prisma_Logger::log('Invio email al sostituto: ' . $t_email);
                                }
                            }
                        }
                    }
                    Prisma_Logger::logToFile("######FINE INVIO EMAIL");
                } catch(Exception $e) {

                    Prisma_Logger::logToFile($e->getMessage() );

                }

             }# --------------  fine invio email  ------------------
            Prisma_Logger::logToFile("######FINE FOREACH ROWSET");
        }# ------------ fine foreach -------------- 



        
        ###########################################################
        ################ AGGIORNAMENTO RESIDUI  ###################
        ###########################################################
        $richiesta = array();
        if('' != $enum) {
            
            $options = array('tipo' => $enum);
            $residuo = $UR->findByUser($requestObj->getUserId(), $options);
            
            //Prisma_Logger::log($residuo);


            //@todo non funziona il valore di ritorno del contratto per il totale
            $tot = 0;
            if($requestObj->getTipologia()->getSigla() == 'FE') {
                $tot = $requestObj->getGiorni();                
            } elseif( $requestObj->getTipologia()->getSigla() == 'PM' ) {
                $tot = $requestObj->getUserObj()->getContratto()->getMattina();
            } elseif( $requestObj->getTipologia()->getSigla() == 'PS' ) {
                $tot = $requestObj->getUserObj()->getContratto()->getSera();
            }



            if($tot =='') {
                $tot = 0;
                Prisma_Logger::logToFile( "Attenzione non trovo i dati contratto per " . $requestObj->getUserObj()->getCognome() . "" . $requestObj->getUserObj()->getNome()  );
            }


            $data = array(
                'goduto' => new Zend_Db_Expr("goduto + $tot"),
                'totale' => new Zend_Db_Expr("totale - $tot"),
            );
             
            
            $where = array(
                'user_id' => $residuo->user_id,
                'tipo' => $enum,
                'year' => date('Y')
            );
            
            $UR->update($data, $where);
            
            $richiesta['tab_to_update']   = $enum;
            $richiesta['value_to_update'] = $tot;
             
        } else {
             $richiesta['tab_to_update']   = '';
             $richiesta['value_to_update'] = 0;
        }
        Prisma_Logger::logToFile( "TAB TO UPDATE " .  $richiesta['tab_to_update'] );
        # richieste: aggiorno lo status della richiesta a accettato
        $rid = $richiesta['richiesta_id'] =  $row['richiesta'];
        $richiesta['status']       = 1; 
        try {
            Prisma_Logger::log(json_encode($richiesta));
            $richiesteMap->update($richiesta);
        }  catch(Exception $e) {
            
            Prisma_Logger::log($e->getMessage());
            $checkpoint = "assenze aggiornamento richiesta";
            Prisma_Logger::logToFile( $checkpoint . $e->getMessage() );

        }
        
        
        
        $uData = array('status' => 1);
        $uWhere = "richiesta_id = $rid";
        $db->beginTransaction();
        try {
            $updated = $db->update('richieste_dettagli', $uData , $uWhere);
            $db->commit();
        } catch(Exception $e) {
            $db->rollBack();
            Prisma_Logger::log($e->getMessage());
            $checkpoint = "assenze insert 2 -> ";
            Prisma_Logger::logToFile( $checkpoint . $e->getMessage() );

        }
        
        $text = "Richiesta di $enum accettata per l'utente " . $requestObj->getUser() . 
                " in data " . $now->toString('dd/MM/yyyy HH:mm:ss'); 
        $logMap = new Application_Model_LogMapper();
        $logData = array(
            'level'       => 'richiesta',
            'facility'    => 'accettata',
            'user_id'     => $requestObj->getUserId(), 
            'address'     => $_SERVER['REMOTE_ADDR'],
            'descrizione' => $text
        );
        $logMap->addEvent($logData);
        
        
        //Prisma_Logger::log($richiesta);
        echo Zend_Json::encode('Richiesta salvata con successo.');
        
        
       # ------------------------------------------  
        
    }# fine insertAction
 












    public function deleteAction() {
        //prelevo l'id della richiesta e aggiorno tutte le tabelle
    }
    
    
    public function updatedayAction() {
        $this->_db->updateday();
    }
    
    
    
    
}


