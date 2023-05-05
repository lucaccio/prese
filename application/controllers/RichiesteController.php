<?php
/**
 * Description of RichiesteController
 *
 * @author  
 */

require_once APPLICATION_PATH . '/models/Email/Sostituzione.php';
require_once APPLICATION_PATH . '/models/Email/Richiesta.php';
require_once APPLICATION_PATH . '/models/Email/Assenza.php';

class RichiesteController extends Zend_Controller_Action {
    
    
    public function preDispatch() {
    
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
        }
               
        $this->_user_id  = Zend_Auth::getInstance()->getIdentity()->user_id;
        $this->_level_id = Zend_Auth::getInstance()->getIdentity()->level_id;
               
        $this->_userMapper = new Application_Model_UserMapper();
        $this->_user = $this->_userMapper->find( $this->_user_id );
        
        if(false == $this->_user->isActive()) {
            $this->_redirect('auth/logout');
        }
        
    }
           
    public function init() {
       //$this->_helper->viewRenderer->setNoRender();
       $this->_table       = new Application_Model_RichiesteMapper();
       $this->_assenze     = new Application_Model_AssenzeMapper();
       $this->_dbTipologia = new Application_Model_TipologiaMapper();
       $this->_fm = $this->_helper->getHelper('FlashMessenger');
       //Prisma_Logger::log($this->_request->getParams());
    }
    
    
    public function storicoInserimentiAction() {
$elenco = $this->_table->fetchAll(null, "_update DESC", 20);
         $this->view->elenco = $elenco;

    }

    public function ajaxgetrequestAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
         

        $id = $this->_getParam('request');
        $request = $this->_table->find($id);
        echo Zend_Json::encode(array(
            "success" => true,
            "data" => (array)$request->toArray()
        ));
            
         
    }



    /**
     * 20/04/2020 
     * Multiple Action
     * Inserimento di un particolare tipo di assenza assegnata 
     * a più dipendenti alla volta
     * 
     * dalla tabella eventi verranno eliminate eventuali assenze precedentemente inserite
     * relative alla stessa data, perciò non risulteranno più nel calendario
     *    
     */
    public function multipleAction() {

        global $g_enable_check_residui_for_user;
        $globals = Zend_Registry::get('globals');


        //Prisma_Logger::log($globals);

        $request = $this->getRequest();
        //elenco di tutti gli utenti
        $dbUsers = new Application_Model_UserMapper();
        $em = new Application_Model_EventiMapper();
        $this->view->users = $dbUsers->getAllUsers(false, 1);

        //elenco di tutte le tipologie di assenze
        $tipologie = new Application_Model_TipologiaMapper();
        $this->view->tipologie = $tipologie->getAll(1, true, true);



        if( $request->isPost() ) {     

            $params = $request->getParams();

            //array di dipendenti (solo id)
            $users = $params['users'];

            // cancello eventuali assenze già inserite
            $deleteOld = isset($params['deleteOld']) ? true : false;

            $tid   = $params['tipologia_id'];  

            $quant = $params['quantita'];
 
            // sistemo le date
            $dateStart = new DateTime($params['start']);
            $dateStop  = new DateTime($params['stop']);
            $start = $dateStart->format('Y-m-d');
            $stop  = $dateStop->format('Y-m-d');

            // database init
            $db = Zend_Registry::get('db');
            //$db->getProfiler()->setEnabled(true);
            $db->beginTransaction();
            try {
            
                foreach($users as $k => $uid) {                   

                   // elimino gli eventi relativi a utente e date richieste
                    if($deleteOld) {
                         
                        $sql = "DELETE FROM  eventi 
                                    WHERE user_id = '$uid'
                                AND ( giorno BETWEEN '$start' AND '$stop')";
                        //print_r($sql);
                        $stmt =  $db->query($sql); 
                       // $rs = $stmt->fetchAll();
                       // print_r(count($rs));
                       // $db->rollBack();
                    }

                    
                    //$int = $dateStop->getTimestamp() - $dateStart->getTimestamp();
                   // if ($int < 0) {
                      //  echo "<h4>Attenzione: La data di inizio è successiva a quella di fine</h4>";
                     //   return;
                   // }
    
                   
    
                     
                    //se faccio la richiesta di 1 giorno, controllo che non sia festivo
                    $totaleGiorniFerieRichiesti = Application_Service_Tools::getTotalDays($start, $stop);
                   
                    $ip_remote_addr = $_SERVER["REMOTE_ADDR"];
                    $data = array(
                        'user_id'      => $uid,
                        'tipologia_id' => $tid,
                        'dateStart'    => $start,
                        'dateStop'     => $stop,
                        'giorni'       => $totaleGiorniFerieRichiesti,
                        'qta'          => $totaleGiorniFerieRichiesti,
                        'status'       => '1', //imposto accettato di default trattandosi di richiesta immediata
                        'date_insert' => date('Y-m-d H:i:s'),
                        'created_by_user_id' => $this->_user_id,
                        'ip_remote_addr' => $ip_remote_addr
                    );
                    
                    $RM = new Application_Model_RichiesteMapper();
                    $rid = $RM->salvataggioMultiploSenzaControlli($data);
                    # ----------------------------------------  



                    # TABELLA ASSENZA: inserisco immediatamente l'assenza dopo la richiesta 
                    $assenza = array(
                        'richiesta_id' => $rid,
                        'user_id'      => $uid,
                        'sostituto_id' => 0,
                        'tipologia_id' => $tid,
                        'dateStart'    => $start,
                        'dateStop'     => $stop,
                        'giorni'       => $totaleGiorniFerieRichiesti,
                        'is_oraria'    => 0, 
                        'qta'          => $totaleGiorniFerieRichiesti,
                        'date_insert'  => date('Y-m-d H:i:s')
                    );                                                            
                    $AM = new Application_Model_AssenzeMapper();
                    $lastid = $AM->insert($assenza);
                    # ----------------------------------------    

                    # TABELLA EVENTI: inserisco gli eventi in base ai dati presi dalla tabella ASSENZE
                    $evento    = new Application_Model_Evento();
                    $rows      = $evento->creaEventoDaAssenza($lastid);                    
                    $eventiMap = new Application_Model_EventiMapper();
                    $eventiMap->insertMultiple($rows);
                    # ----------------------------------------
                    
                    # sostituzioni: inserisco la sostituzione anche se è senza sostituto
                    $sostituzione = array(
                        'assenza_id'   => $lastid,  // POSSO METTERE L'ID DELLA RICHIESTA?
                        'user_id'      => 0, //$sostituto_id
                        'struttura_id' => 0,
                        'date_insert'  => date('Y-m-d H:i:s')
                    );
                    $sostituzioniMap = new Application_Model_SostituzioniMapper();
                    $sostituzioniMap->insert($sostituzione);
                    # ---------------------------------------
                  
            


                } //fine foreach per ogni utente

                //$db->rollBack();

                $db->commit();    


                echo "<div style=\"
                    text-align:center; 
                    padding: 8px; 
                    color: #004d00; 
                    background-color: #e6ffe6;
                    width: 400px;
                    margin: auto;
                    margin-top: 5px;
                    border: 1px solid #73AD21;\">
                    <p><b>Dati inseriti correttamente!</b></p>
                </div>";
                  


            } catch(Exception $e) {
                echo "<div style=\"
                    text-align:center; 
                    padding: 8px; 
                    color: #992600; 
                    background-color: #ffd9cc;
                    width: 300px;
                    margin: auto;
                    margin-top: 5px;
                    border: 1px solid #e63900;\">
                    <p><b>" . $e->getMessage() . "</b></p>
                </div>";
                $db->rollBack();
            }
        }
    }

















    public function indexAction() {}
     
    /**
     * Visualizza lo storico delle assenze
     */
    public function storicoAction() 
    {
        $request = $this->_request;
        $status = $this->_getParam('status');
        $year   = $this->_getParam('year');
        $month   = $this->_getParam('month');
        ('' == $status) ? $status = 0 : $status = $status;
        ('' == $year)   ? $year = date('Y') : $year = $year;
        ('' == $month)  ? $month = 0 : $month = $month;    
        
        $user_id= Zend_Auth::getInstance()->getIdentity()->user_id;
        if($request->isPost()) {
            $table = new Application_Model_RichiesteMapper();
            $storico = $table->findByUserId($user_id, $status, $year, $month, 'dateStart ASC');
            $this->view->storico = $storico; 
            //$giorni = $table->giorniFerieConcessi($user_id, $year, 2, 1);
        } else {
            $table = new Application_Model_RichiesteMapper();
            $storico = $table->findByUserId($user_id, $status, $year, $month, 'dateStart ASC');
            $this->view->storico = $storico; 
        }
        $this->view->user_id = $user_id;    
        $this->view->status  = $status;
        $this->view->year    = $year;
        $this->view->month   = $month;
     }
       
    /**
     * Stato richieste
     * 
     */
    public function statoAction() {
        $status = $this->_getParam('status');
        $stato = $this->_table->findByStatus($this->_user_id, $status);
               
        $num = count($stato);
       
        if( $num > 0 ) {
            foreach($stato as $row) {
                echo $row->dateStart . ' => ' . $row->dateStop . '<br>';
            }
        } else {
            echo 'NESSUN DATO PRESENTE';
        }
    }
    
    
    
    public function manutenzioneAction() { }
    





    
    /**
     * ACTION PER FARE UNA RICHIESTA DA PARTE DEGLI OPERATORI/SOSTITUTI
     */
    public function nuovaAction() 
    {
        
        # GLOBALS
        global $g_enable_email, 
               $g_mail_richiesta_new, 
               $g_enable_free_substitute_check,
               $g_enable_check_residui_for_user,
               $g_enable_blocco_giorni,
               $g_enable_save_on_table,
               $g_user_block_insert_new_request
                            
        ;  
        
        $request = $this->getRequest();
        
        // disabilito l'inserimento assenze e rimando a una pagina di manutenzione
        if(ON ==  $g_user_block_insert_new_request) {
            // on
            $now = new Zend_Date();
            $stopManutenzione = new Zend_Date('2013-10-16 10:30:01');
            if( $now->getTimestamp() < $stopManutenzione->getTimestamp() ) {
                $this->_redirect('/richieste/manutenzione');
            }
        }
                
        # init mapper
        $tipologie = new Application_Model_TipologiaMapper();
        $UR        = new Application_Model_UserResiduiMapper();
        
        # views
        $this->view->residui = $UR->getResidui( $this->_user );
        // recupero le tipologie visualizzabili dall'operatore
        $this->view->tipologie = $tipologie->getAll();
        $this->view->level_id  = $this->_level_id; 
       
        
        
        //PROCESSO DI VERIFICA DI RICHIESTA ASSENZE               
        if( $request->isPost() ) { 
                        
            $tipologia_id = $request->getParam('tipologia_id');
            $this->view->start = $start = $request->getParam('start');
            $this->view->stop  = $stop  = $request->getParam('stop');
            $note_user    = Prisma_Utility_String::removeSign(trim($request->getParam('note_user')));
             
            if(null == $tipologia_id) {
                // echo '<h4>Selezionare Tipo di assenza</h4>';
                echo "<script>alert('Attenzione! Selezionare Tipo Richiesta')</script>";
                return;
            } 
            $tipo = $this->_dbTipologia->find($tipologia_id);    

             
            $mydate = new Application_Model_MyDate($start, $stop);
           
            
            
            //---- SE LE DATE NON SONO INVERTITE OVVERO SE L'INIZIO è INFERIORE ALLA FINE
            if($mydate->verify()) {
                
                if($this->_user->getLevelName() == 'Operatore') {
                    
                    # controllo la presenza di sostituti liberi ( la legge 104 non ha questo controllo )
                    if(ON == $g_enable_free_substitute_check) {
                        if(!$tipo->isLegge104()) {
                            $sostituti = new Application_Model_UserMapper();
                            $elencoSostituti = $sostituti->getSostitutiLiberi($mydate->getStart('Y-m-d'), $mydate->getStop('Y-m-d')); 
                            if( $elencoSostituti->count() == 0) {
                                echo "<script>alert('Attenzione, per questo periodo non ci sono sostituti liberi')</script>";
                
                                //echo "<h4>Attenzione, per questo periodo non ci sono sostituti liberi</h4>";
                                return;
                            }
                        }
                    }
                }      
                
                
                ##############    PATRONO
                // il sostituto non può inserire il patrono
                // 
                
                if($tipo->isPatrono()) {
                    
                    if($this->_user->getLevelName() == 'Sostituto') {
                        echo "<script>alert('Il sostituto non può inserire assenze relative al Santo Patrono')</script>";
                        return;
                    }
                    
                    if(!$mydate->same()) {
                        return;
                    }
                    
                    $patrono = new Application_Model_FestivitaMapper();
                    $patronalday = $patrono->findPatronalSaint($this->_user->getSede()->getSedeId());
                    $check = $patronalday->mese .  '-' . $patronalday->giorno ;
                    
                    
                    
                    if($check != $mydate->getStart('n-d')) {
                        //echo $patronalday . '<br>';
                        //echo $mydate->getStart('n-d');
                        echo "<script>alert('La data inserita non risulta patrono della tua sede')</script>";
                        return;
                        //echo 'La data inserita non risulta patrono della tua sede';
                        //return;
                    }
                     
                    if( $patronalday->lavorativo == 1 ) {
                        echo "<script>alert('Attenzione, il giorno del Santo Patrono risulta lavorativo, contattare l\'amministrazione')</script>";
                        return; 
                    }
                    
                    $giorni = 1;
                }
                
                ###############    LEGGE 104
                if($tipo->isLegge104()) {
                   
                    if(!$mydate->same()) {
                        echo "<script>alert('Il giorno iniziale e finale deve essere lo stesso')</script>";
                        return; 
                    } 
                    $giorni = 1;
                   
                }
                
                
                ###############    PERMESSI
                if($tipo->permesso()) {
                    $check = $this->_assenze->findDoubleRoleByDate($this->_user_id, $mydate->getStart('Y-m-d'), $mydate->getStop('Y-m-d')) ;
                    
                    if( count($check) > 0)  {
                        if( ( $tipologia_id == 6 ) && ( $check->current()->tipologia_id == 7) ) {
                            #allora proseguo
                        } elseif( ( $tipologia_id == 7 ) && ( $check->current()->tipologia_id == 6) ) {
                            #allora proseguo
                        } else {
                            echo "<script>alert('Attenzione, periodo di richiesta già utilizzato')</script>";
                        return; 
                        }
                        
                    }
                    
                    # fulltime si riferisce alla colonna di tipologia
                    if($tipo->getFulltime() == 2) {
                        $ore = $this->_user->getContratto()->getMattina();
                    } elseif($tipo->getFulltime() == 3) {
                        $ore = $this->_user->getContratto()->getSera();
                    }

                    $pe = new Application_Model_Permesso($mydate, $this->_user);
                    $giorni = $pe->getTotale();
                    if($giorni == 0) {
                         echo "<script>alert('Attenzione, il totale dei giorni è inferiore a uno.')</script>";
                        return; 
                    }
                    
                    if($mydate->isSaturday($mydate->getStart('Y-m-d'))) {
                        echo '<b>Il sabato non puoi richiedere permessi ma solo ferie</b>';
                        return ;
                    }
                    
                    $date = new Zend_Date($mydate->getStart('Y-m-d'));
                    $yearToCheck = $date->toString(Zend_Date::YEAR);
                    $options = array(
                        'tipo' => 'PERMESSO',
                        'year' => $yearToCheck
                    );
                    $t_residuo = $UR->findByUser($this->_user_id, $options);
                    
                    if($t_residuo != null) {
                        if( ($t_residuo->maturato == 0) || ($t_residuo->totale < $ore) ) {
                            $options['tipo'] = 'EX-FEST';
                            $t_residuo = $UR->findByUser($this->_user_id, $options);
                            if($t_residuo != null) {
                                if($t_residuo->totale < $ore ) {
                                    echo "<h4>Attenzione il totale di ore che puoi ancora utilizzare  è " . (int) $t_residuo->totale ."</h4>";
                                    return;
                                }
                            } else {
                                echo "<h4>Attenzione, la tabella residui $options[tipo] non risulta presente, contattare l'amministrazione</h4>";
                                return;
                            }
                         }
                    
                     }
                } ############## fine permessi
                 
                
                ######################   FERIE   ################
                if($tipo->isFerie()) {
                                        
                    $check = $this->_assenze->findDoubleRoleByDate($this->_user_id, $mydate->getStart('Y-m-d'), $mydate->getStop('Y-m-d')) ;
                    if( count($check) > 0)  {
                        echo '<h4>Attenzione! Non puoi usare questo periodo di ferie perche già utilizzato</h4>';
                        return ;
                    }
                    $giorni = $mydate->countActualDays($this->_user->getSede()->getSedeId());
                    if($giorni == 0) {
                         echo '<h4>Il totale dei giorni richiesti risulta inferiore a 1</h4>';
                         return;
                    }
                    $options = array('tipo' => 'FERIE');
                    
                    
                        ####################################################################
                        # ------------   INSERIMENTO DETTAGLI E RESIDUI RICHIESTE ----------   
                        $t_days = Application_Service_Tools::getArrayOfActualDays($start, $stop);
                        
                        //
                        $entries = array();
                        foreach($t_days as $k => $date) {
                            $_year = substr($date, 0, 4);  // year
                            if(!array_key_exists($_year, $entries)) {
                                $entries[$_year] = 0 ;
                            }
                            $entries[$_year]++;
                        } # end
                        $mapper = new Application_Model_ResiduiMapper(); 
                        
                        
                        foreach($entries as $k => $v) {
                            $where = array (
                                'year'         => $k,
                                'tipologia_id' => FERIE, 
                                'user_id'      => $this->_user_id 
                            );
                            $q        = $mapper->residuiGetAssignedQuantity($where);
                            $q_by_uid = $mapper->residuiGetAssignedQuantityByUser($where);
                            // controllo residui ON | OFF
                            if(ON == $g_enable_check_residui_for_user) {
                                $assigned_by_user_id = ($q_by_uid + $v);
                                $assigned = ($q + $v);
                                
                                //limite ferie inseribili
                                if( MAX_USER_INSERT_FERIE < $assigned_by_user_id ) {
                                    $msg = "Attenzione! Puoi inserire fino a un massimo di " . MAX_USER_INSERT_FERIE . " giorni di ferie per ogni anno solare.";
                                    echo "<script>alert('" . $msg . "');</script>";
                                    return;
                                }
                                
                                if( MAX_FERIE < $assigned ) {
                                    $msg = "Attenzione, non hai sufficenti giorni di ferie disponibili per l\'anno $k";
                                    echo "<script>alert('" . $msg . "');</script>";
                                    return;
                                }
                            } # fine $g_enable_check residui( ON | OFF )
                        } # fine foreach
                        # ------------ FINE INSERIMENTO DETTAGLI E RESIDUI RICHIESTE ----------
                        
                        
               } # fine check ferie
                
                
                ##### MALATTIA  -  INFORTUNI
                if($tipo->isMaternita() or $tipo->isMalattia() or $tipo->isInfortunio() or $tipo->isAllattamento()) {
                    $check = $this->_assenze->findDoubleRoleByDate($this->_user_id, $mydate->getStart('Y-m-d'), $mydate->getStop('Y-m-d')) ;
                    if( count($check) > 0)  {
                        echo '<b>Non puoi usare questo periodo di ferie perche già utilizzato</b>';
                        return ;
                    }
                    $giorni = $mydate->countActualDays($this->_user->getSede()->getSedeId());
                }                             
                
                $ip_remote_addr = $_SERVER["REMOTE_ADDR"];
              
                #SALVATAGGIO DELLA RICHIESTA DA PARTE DELL'OPERATORE
                $data = array(
                    'user_id'      => $this->_user_id,
                    'tipologia_id' => $request->getParam('tipologia_id'),
                    'dateStart'    => $mydate->getStart('Y-m-d'),
                    'dateStop'     => $mydate->getStop('Y-m-d'),
                    'giorni'       => $giorni,
                    'date_insert'  => date('Y-m-d H:i:s'),
                    'status'       => '0',
                    'note_user'    => $note_user ? $note_user : "nessuna nota",
                    'created_by_user_id' => $this->_user_id,
                    'ip_remote_addr' => $ip_remote_addr
                        
                );

                $table = new Application_Model_RichiesteMapper(); 
                 
                ##########   PROCEDURA DI SALVATAGGIO  ###########
                
                
                if(ON == $g_enable_save_on_table) {
                    try {
                       $richiesta_id = $table->save($data);
                       if($tipo->isFerie()) {
                           $db = Zend_Registry::get('db');
                           //$db->getProfiler()->setEnabled(true);
                           $db->beginTransaction();
                           try {
                               foreach($entries as $k => $v) {
                                   
                                   //inserisco dettaglio della richiesta
                                   $data = array(
                                        'richiesta_id' => $richiesta_id,
                                        'user_id'      => $this->_user_id,
                                        'tipologia_id' => FERIE,
                                        'year'         => $k,
                                        'days'         => $v,
                                        'inserted'     => time(),
                                        'status'       => 0
                                   );
                                   $db->insert('richieste_dettagli', $data);
                                                                      
                                   //aggiorno residui delle richieste
                                   $data = array();
                                   $data['assigned'] = new Zend_DB_Expr("assigned + $v");
                                   $data['assigned_by_user_id'] = new Zend_DB_Expr("assigned_by_user_id + $v");
                                   $where[] = "user_id = $this->_user_id";
                                   $where[] = "tipologia_id = " . FERIE;
                                   $where[] = "year = $k";
                                   $db->update('richieste_residui',$data,$where);
                                   unset($where);
                                   unset($data);
                                }
                                 $db->commit();  
                            } catch(Exception $e) {
                                $db->rollBack();
                            }
                        }
                        $this->_fm->addMessage('Salvataggio eseguito');

                        ##### INVIO EMAIL AVVISO ( SE RICHIESTO )
                        if(ON == $g_enable_email)
                        {
                            if(Zend_Registry::get('sendmail') == true) {
                                $mail = new Prisma_Mail();
                                $validator = new Zend_Validate_EmailAddress();
                                $alert = "";
                                if ($validator->isValid(trim($this->_user->getEmail()))) {
                                    $fromEmail = $this->_user->getEmail();
                                } else {
                                    //recupero email dalla configurazione
                                    $fromEmail = 'feriemanager@gmail.com';
                                    $alert = "ATTENZIONE - L'email dell'utente risulta errata o inesistente , verificare nel software Feriemanager sezione Utenti";
                                } 
                                $mail->setFrom($fromEmail, $this->_user->getAnagrafe());

                                ########    INVIO EMAIL ALL'AMMINISTRATORE ( SE RICHIESTO )
                                if(Zend_Registry::get('sendmailAdmin') == true) {
                                   $mail->addTo('maura.prisma@gmail.com');
                                   $mail->addTo('carlotta.prisma@gmail.com'); 
                                }
                                #########    INVIO EMAIL ALLO SVILUPPATORE ( SE RICHIESTO )
                                if(Zend_Registry::get('sendmailDeveloper') == true) {
                                    $mail->addTo("lucauda.prisma@gmail.com");
                                }

                                if('' == $note_user) {
                                    $note_user = "Nessuna nota inserita";
                                }
                                #if(ON == $g_enable_email){}
                                $mail->setSubject('Nuova richiesta inserita da ' . $this->_user->getAnagrafe());
                                $userIp = $_SERVER['REMOTE_ADDR'];
                                $messaggio  = $this->_user->getAnagrafe() . " ha effettuato una nuova richiesta in data ". date('d/m/Y H:i:s') . "<br><br>";
                                $messaggio .= "Tipo: " . $tipo->getDescrizione() . "<br>";
                                $messaggio .= "Dal: " . $mydate->getStart('d/m/Y'). "<br>";
                                $messaggio .= "Al:  " . $mydate->getStop('d/m/Y') . "<br>";
                                $messaggio .= "Note:  " . $note_user . "<br><br>";
                                $messaggio .= "IP:  " . $userIp . "<br><br>";
                                $messaggio .= $alert . "<br>";
                                $mail->setBodyHtml($messaggio);
                                $mail->send();
                            }    
                        }

                        # ------------------ fine invio email  ----------------------


                        // --------- LOGGA SU DB L'EVENTO RICHIESTA -----------
                        $TM = new Application_Model_TipologiaMapper();
                        $tipo = $TM->find($request->getParam('tipologia_id'));
                        $text = "Nuova richiesta di " . $tipo->Descrizione . 
                                " inserita dall'utente " . $this->_user->getAnagrafe() . 
                                " per il periodo dal ". $mydate->getStart('d/m/Y') . 
                                " al " . $mydate->getStop('d/m/Y') . ""
                                ;

                        $logData = array(
                          'level'   => 'richiesta',
                          'facility'    => 'nuova',
                          'user_id' => $this->_user_id,
                          'address' => $_SERVER['REMOTE_ADDR'],
                          'descrizione' => $text
                        );

                        $logMapper = new Application_Model_LogMapper(); 
                        $logMapper->addEvent($logData);
                        //-----------------------------------------------------

                        $this->_helper->flashMessenger->addMessage(array(
                             'success' => 'Richiesta inoltrata con successo')
                        );
                        
                        # se tutto ok redirigo 
                        $this->_redirect('/richieste/storico');

                     } catch (Exception $e) {
                       //echo "<h4>Una richiesta per queste date risulta già inoltrata e non ancora evasa, inserisci periodi differenti</h4>";
                        echo $e->getMessage();
                        $this->view->start = $start;
                        $this->view->stop  = $stop;
                     }  
                 
                } else {
                    //Prisma_Logger::log ( "global_enable_save_on_table = OFF" ) ;
                }#  fine salvataggio  
                 
                 
        } # fine verifica date
          //  $this->view->messages = $this->_fm->getMessages();
        } # fine richiesta di ferie
         
        
       
        
        
    }
     
    
    
    
    /**
     * SOLO ADMIN
     */
    public function visualizzaAction() {
        $richiesta_id = $this->_getParam('richiesta_id');
    }
    
    /**
     * ADMIN 
     * 
     */
    public function listAction() {
        
        // Prisma_Logger::log($this->_getAllParams());
        $user_id = $this->_getParam('user_id');
        $status  = $this->_getParam('status');
        $year    = $this->_getParam('year');
        $month   = $this->_getParam('month');
        $tipologia   = (int)$this->_getParam('tipo');
        ('' == $status) ? $status = 0 : $status = $status;
        ('' == $year)   ? $year = date('Y') : $year = $year;
        ('' == $month)  ? $month = date('n') : $month = $month;    
        ('' == $tipologia) ? $tipologia = 0 : $tipologia = $tipologia; 
        
        
        
        // devo separare la lista delle richieste accettate e quindi effettive da tutte le altre;
        // le prime andranno a controllare gli eventi inseriti e quindi effettivi
        // tutte le altre leggeranno semplicenemte dalla tabella richiesta
        // cerco nelle costanti per stabilire che status ha la richiesta
       
        # recupero i giorni da tabella: (eventi + assenze)
        if($status == ACCETTATO) {
            
            $AM = new Application_Model_AssenzeMapper();
            $where = array(
                'user_id'      => $user_id,
                'tipologia_id' => $tipologia,
                'month'        => $month,
                'year'         => $year
            );
             
            $elenco = $AM->getStorico($where);
        }
        elseif($status == 2) {
           $elenco = $this->_table->findRequestByStatusNA($status, $year, $month, $user_id, $tipologia);
        } else {
           $elenco = $this->_table->findRequestByStatus($status, $year, $month, $user_id, $tipologia);
        }
         
        $this->view->status = $status;
        $this->view->year   = $year;
        $this->view->month  = $month;
        $this->view->tipo   = $tipologia;
        
        $TM = new Application_Model_TipologiaMapper();
        // mostro tutte le tipologie, comprese quelle visibili solo ad admin
        $tipologie = $TM->getAll(1,1) ;
        //Prisma_Logger::log($tipologie);
        $this->view->tipi  = $tipologie;
        
        //Prisma_Logger::log($elenco);
        
        $this->view->elenco = $elenco;
        $this->view->user_id =$user_id;
        
        $this->view->uid =  $this->_user_id; 
        
        $users = $this->_userMapper->getAllUsers(false, null); 
        
        $usersArr = array();
        foreach($users as $k => $row) {
            $usersArr[$row->getId()] = $row->getAnagrafe(); 
        }
        $this->view->users  = $usersArr;
        
        // controllo richieste non evase per l'anno successivo al presente 
        $nextYearRequest = $this->_table->countRequestNextYear();
        $this->view->nextYearRequest = $nextYearRequest;
    }
    
       
    
    /**
     * ACTION PER ACCETTARE O MENO UNA RICHIESTA DI FERIE
     * O UN PERMESSO
     * @deprecated since version 1.1
     */
    public function modificaAction() {
            
        $richiesta_id = $this->_getParam('richiesta_id');
        $richiesta    = $this->_table->findByRequestId((int)$richiesta_id);
        
        $richiedente = $this->_userMapper->find( $richiesta->user_id );
       
        if($richiesta) {
            $check = $this->_assenze->findDoubleRoleByDate($richiesta->user_id, $richiesta->dateStart, $richiesta->dateStop) ;
            if( count($check) > 0)  {
                $this->view->messaggio  = '<p><b>ATTENZIONE! L\'utente in questo periodo è occupato </b></p>';
                return;
            }
            $sostituti = new Application_Model_UserMapper();
            $this->view->sostituti = $sostituti->getSostitutiLiberi($richiesta->dateStart, $richiesta->dateStop); 
            $this->view->richiesta = $richiesta;
        }
        
        
        //SALVATAGGIO
        //se la richiesta è accettata allora salva su assenze, altrimenti aggiorna la richiesta
             
        $request = $this->getRequest();
                       
        if( $request->isPost() ) {
            
           $params       = $request->getParams();
           $richiesta_id = $params['richiesta_id'];
           $user_id      = $params['user_id'];
           $tipologia_id = $params['tipologia_id'];
           $dateStart    = $params['dateStart'];
           $dateStop     = $params['dateStop'];
           $status       = $params['status'];
           $sostituto_id = $params['sostituto_id'];
           $note         = $params['note'];
        //   $hotel        = $params['hotel'];
       //    $budget       = $params['budget'];
           
           
           $arrRichiesta = array(
                   'richiesta_id' => $richiesta_id,
                   'status'       => $status,
                   'note'         => $note
           );
           
           
           
           //OKKIO QUI DA RIVEDERE ASSOLUTAMENTE
           //ACCETTATO / ACCETTATO SENZA SOSTIUTUZIONE
           if( '1' === (string)$params['status'] or '2' === (string)$params['status'] )   {
                
                //FARE IL CALCOLO GIORNI QUI
                $giorni        = $params['giorni'];
                $tipoObj       = $this->_dbTipologia->find($tipologia_id);
                
                //OKKIO
                //se non è patrono controllo il residuo di ferie/permessi
                if(!$tipoObj->isPatrono() && !$tipoObj->isLegge104()) {
                    $tbFerieMapper = new Application_Model_FerieMapper();
                    $assenza       = $tbFerieMapper->findByTipo($tipoObj->getTipo(), $user_id, date('Y'), false); 
                }
           
                $arrAssenza    = array(
                    'richiesta_id' => $richiesta_id,
                    'user_id'      => $user_id,
                    'sostituto_id' => $sostituto_id,
                    'tipologia_id' => $tipologia_id,
                    'dateStart'    => $dateStart,
                    'dateStop'     => $dateStop
                );
                     
                $arrSostituzione = array(
                    'user_id' => $sostituto_id,
                );
            
           
                
                 //SALVO TUTTO
                $values = array( 
                            array(
                                'table'  => 'richieste',
                                'method' => 'update',
                                'values' => $arrRichiesta
                             ),
                             array(
                                'table'  => 'assenze',
                                'method' => 'insert',
                                'values' => $arrAssenza    
                             ),
                             array(
                                'table'  => 'sostituzioni',
                                'method' => 'insert',
                                'values' => $arrSostituzione
                             ),
                           //  array(
                            //     'table' => 'budgetSostituzioni',
                           //      'method'=> 'insert',
                           //      'values'=> $arrBudget
                           //  )
                            
                  );
                
                
                //se è un permesso
                if($tipoObj->isPermesso()) {
                    if($tipoObj->getFulltime() == 2) {
                       $ore = ($giorni * $richiedente->getContratto()->getMattina());
                    } elseif($tipoObj->getFulltime() == 3) {
                       $ore = ($giorni * $richiedente->getContratto()->getSera()); 
                    }
                        
                    $arrConfig      = array(
                                'residuo' => ($assenza->residuo - $ore),
                                'goduto'  => ($assenza->goduto  + $ore),
                                'where'  => array(
                                         'config_id' => $assenza->config_id
                                )
                    );
                                        
                    $values[] = array(
                              'table'  => 'config',
                              'method' => 'update',
                              'values' => $arrConfig,
                     ); 
                    
                }
                
                //se è una ferie
                if($tipoObj->isFerie()) {
                    $arrConfig      = array(
                                'residuo' => ($assenza->residuo - $giorni),
                                'goduto'  => ($assenza->goduto  + $giorni),
                                'where'  => array(
                                         'config_id' => $assenza->config_id
                                     )
                            );
                     $values[] = array(
                              'table'  => 'config',
                              'method' => 'update',
                              'values' => $arrConfig,
                     ); 
                    
                }
                
                //SE E' PATRONO
                //if($tipoObj->isPatrono()) {}
                //
                
                
                 $transaction = new Application_Model_TransactionMapper();

                 try {
                     
                     //print_r($values);
                     
                        $transaction->assegna($values);
                        if($sostituto_id > 0) {
                            $check = $params['sendmail'];
                            if($check == 1) {
                                //INVIO EMAIL AL SOSTITUTO
                                $sostituto = $this->_userMapper->find($sostituto_id);
                                $email     = $sostituto->getEmail();
                                #$segreteria = "segreteria@kcbasil.eu";
                                $validator = new Zend_Validate_EmailAddress();
                                if($validator->isValid($email)) {
                                    $mail = new Zend_Mail();
                                    $mail->setFrom(AMMINISTRAZIONE, 'Segreteria Prisma Investimenti');
                                    $mail->addTo($email);
                                    $mail->setBodyText("E' stata programmata una nuova sostituzione, verifica online");
                                    $mail->setSubject('Nuova sostituzione programmata');
                                    $mail->send();
                                } 
                            }
                        }
                        $this->_helper->redirector->gotoUrl('/richieste/list');
                  } 
                  catch(Zend_Db_Table_Exception $e) {
                        echo $e->getMessage();
                  }
               
           } else {
               //AGGIORNO SOLO LA RICHIESTA
               $this->_table->aggiorna($arrRichiesta);
               $this->_helper->redirector->gotoUrl('/richieste/list');
           } 
        } //FINE IF 
    }
    
    
    /**
     * @deprecated in favore di ajax/deleteAcceptedRequestAction
     * Annulla un'assenza 
     * invia email solo al sostituto, ma solo se glielo dico io
     * se la data fine è già passata non invio comunque
     * 
     * @global type $g_mail_sostituzione_delete
     * @return type
     */    
    public function annullaAction() {
        
        $this->_helper->viewRenderer->setNoRender();
        $oggi = new Zend_Date(date('Y-m-d'));
        $richiesta_id = $this->_getParam('richiesta_id');
        
        # parametro preso dal GET
        $invioemail   = false;
        $invioemail   = (boolean) $this->_getParam('invioemail');
        Prisma_Logger::log('status invioemail: ' . $invioemail);
        
        $AM = new Application_Model_AssenzeMapper();
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
        $row = $this->_table->find($richiesta_id);
        $obj_assenza = new Application_Model_AssenzaObject($t_assenza_id);         
        
        # cancello richieste accettate/non accettate
        $transaction = new Application_Model_TransactionMapper();
        
        try {
            global $g_mail_sostituzione_delete;
            $transaction->removeRequest($richiesta_id);
            $sandbox = Zend_Registry::get("sandbox");
              
            # variabile settata dall'url GET
            # se true invio l'email al sostituto altrimenti cancello senza invio email
            if($invioemail) {
                
                
                #TODO: inviare l'email all'utente?
                
                
                //se si tratta di assenza senza sostituto
                if( $sostituto_id == 0 ) {
                     Prisma_Logger::log('nessun sostituto, nessuna email al sostituto');
                }
                # se la sostituzione è già stata fatta non invio email
                elseif($stop->compare($oggi) < 0) {
                    Prisma_Logger::log('invio email annullato: sostituzione già effettuata');
                } else {
                    # invio email sostituto
                    $sostituto = $this->_userMapper->find($sostituto_id);
                    $t_email   = $email = $sostituto->getEmail();
                    $validator = new Zend_Validate_EmailAddress();
                    if($validator->isValid($email)) 
                    {
                        $emailGateway = new Application_Model_Email_Sostituzione($t_email, $obj_assenza, '', $sandbox);
                        try 
                        {
                            if(ON == $g_mail_sostituzione_delete) 
                            {
                                Prisma_Logger::log( 'Sostituzione cancellata => email di notifica inviata. ' );
                                $emailGateway->sendCanceled();
                            } else 
                            {
                                Prisma_Logger::log( 'Invio email disabilitato ' ); 
                            }
                        } catch(Exception $e) 
                        {
                            #@logga e invia mail oppure email queue
                            Prisma_Logger::log('errore invio email ' . $e->getMessage());
                        }
                    } # validator   
                }
            }
            //Prisma_Logger::log($this->_request->getParams());
            // print_r($this->_request->getParams());
            $status = $this->_getParam('status');
            $year   = $this->_getParam('year');
            $month  = $this->_getParam('month');
            #serve per la select degli utenti in /richieste/list
            $user_id  = $this->_getParam('user_id');
            /*
            $parametri = array(
                'status' => $status,
                'year'   => $year,
                'month'  => $month
            );
            */
            #$this->redirect("/richieste/list/year/$year/month/$month/status/$status/user_id/$user_id");
            $action = 'list';
            $controller = 'richieste';
            $module = 'default';
            $params = array(
                'year'    => $year,
                'month'   => $month,
                'status'  => $status,
                'user_id' => $user_id
            );
            $this->_forward($action, $controller, $module, $params);
            
        } # end try
        catch(Zend_Db_Table_Exception $e) 
        {
            echo $e->getMessage();
        }
     }
    
    
     
     
    /**
     *  SOLO PER ADMIN (aggiunge una richiesta di ferie 'in lavorazione' a un utente)
     *  
     * L'Operatore utilizza il metodo nuovaAction
     * 
     */
    public function addAction()
    {

            global $g_enable_check_residui_for_user;
            $globals = Zend_Registry::get('globals');

            //Prisma_Logger::log($globals);

            $request = $this->getRequest();
            //elenco di tutti gli utenti
            $dbUsers = new Application_Model_UserMapper();
            $em = new Application_Model_EventiMapper();
            $this->view->users = $dbUsers->getAllUsers(false, 1);

            $range = array();
            $format = 'yyyy-MM-dd';
            $now = new Zend_Date();

            //---isGET
            if ($request->isGet())  {

                $userId = $request->getParam('user_id');
                $start = $request->getParam('start');
                $stop = $request->getParam('stop');
                $tipologia_id = $request->getParam('tipologia_id');

                $this->view->start = $start;
                $this->view->stop = $stop;
                $month_select = $request->getParam('month_select');

                if (null != $month_select) {
                    $now->set($month_select, Zend_Date::MONTH);
                }
            } //fine isGET

            $this->view->now = $now;
            $last = $now->get(Zend_Date::MONTH_DAYS);
            $dStart = $now->set(1, Zend_Date::DAY)->toString($format);
            $dStop = $now->set($last, Zend_Date::DAY)->toString($format);

            $rangeToFind = array(
                'start' => $dStart,
                'stop' => $dStop
            );

            # inizializzo l'array con gli indici
            for ($i = 1; $i <= $last; $i++) {
                $range[$now->set($i, Zend_Date::DAY)->toString($format)] = array();
            }

            //Prisma_Logger::log($range);

            $sostituti = $this->view->sostituti = $dbUsers->getAllUsers(false, $options = array('level_id' => 2));
            foreach ($sostituti as $k => $user) {
                $uid = $user->getId();
                $found = $em->trovaEventoPerRangeDiDateEdUtente($rangeToFind, $uid);
                if ($found) {
                    foreach ($found as $k => $row) {
                        $idx = $row->giorno;

                        # @todo: attenzione lo stesso giorno ci possono   essere due sostituzioni per due permessi
                        # usare array_key exist
                        # se risultano due sostituzioni, segnare il tipo come ferie in modo da colorare completamente il tassello
                        if (array_key_exists($uid, $range[$idx])) {
                            $range[$idx][$uid] = FERIE;
                        } else {
                            $range[$idx][$uid] = $row->tipo;
                        }


                    }
                }
            } // fine GET

            $this->view->range = $range;

            //elenco di tutte le tipologie di assenze
            $tipologie = new Application_Model_TipologiaMapper();
            $this->view->tipologie = $tipologie->getAll(1, true);


            // ----------------- INVIO RICHIESTA  --------------------
            if ($request->isPost()) 
            {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();
                 
                $qta = 0;

                //recupero parametri
                $userId       = $request->getParam('user_id');
                $t_start      = $start = $request->getParam('start');
                $t_stop       = $stop = $request->getParam('stop');
                $tipologia_id = $request->getParam('tipologia_id');

                /*
                if($tipologia_id == 6 || $tipologia_id == 7) {
                    $qta          = $request->getParam('quantita');
                }

                */
                if(  $request->getParam('quantita')  !== null ) {
                    $qta          = $request->getParam('quantita');
                }


                // oggetto tipologia
                $tipoMapper   = new Application_Model_TipologiaMapper();
                $tipo         = $tipoMapper->find($tipologia_id);

                if (null == $userId) {
                    $this->view->start = $start;
                    $this->view->stop  = $stop;
                    // echo '<h4>Attenzione, selezionare utente.</h4>';
                   // echo "<script>alert('Attenzione, selezionare utente.');</script>";
                   echo Zend_Json::encode( array('success' => false, 'message'=> "Selezionare utente") );
                    return;
                }

                if (null == $tipologia_id) {
                    $this->view->start = $start;
                    $this->view->stop = $stop;
                   // echo '<h4>Selezionare Tipologia di assenza</h4>';
                   echo Zend_Json::encode( array('success' => false, 'message'=> "Selezionare assenza") );
                    return;
                }

                try {
                    $mydate = new Application_Model_MyDate($start, $stop);
                } catch(Exception $e) {
                    echo Zend_Json::encode( array('success' => false, 'message'=> $e->getMessage()) );
                    return;
                }

                if ($mydate->verify()) {

                    $idOperatore = $userId;
                    $operatore = $dbUsers->find($idOperatore);
                    if ($operatore->getLevelName() == 'Operatore') {
                        if (!isset($globals['abilita_ricerca_sostituti_liberi'])) {
                            Prisma_Logger::logToFile(__FILE__ . "::" . __LINE__. "  ] Global value: 'abilita_ricerca_sostituti_liberi' not found");
                        } else {
                            if ($globals['abilita_ricerca_sostituti_liberi'] == 1) {
                                //se non trovo sostituti per la richiesta dell'operatore non inoltro la richiesta
                                $sostituti = new Application_Model_UserMapper();
                                $elencoSostituti = $sostituti->getSostitutiLiberi($mydate->getStart('Y-m-d'), $mydate->getStop('Y-m-d'));
                                if ($elencoSostituti->count() == 0) {
                                    //echo "<h4>Attenzione, per questo periodo non ci sono sostituti liberi</h4>";
                                    echo Zend_Json::encode( array('success' => false, 'message'=> "Attenzione, per questo periodo non ci sono sostituti liberi") );
                                    return;
                                }
                            }
                        }
                    }

                    $check = $this->_assenze->findDoubleRoleByDate($operatore->getId(), $mydate->getStart('Y-m-d'), $mydate->getStop('Y-m-d'));
                    if (count($check) > 0) {

                        if (($tipologia_id == 6) && ($check->current()->tipologia_id == 7)) {
                            #allora proseguo
                        } elseif (($tipologia_id == 7) && ($check->current()->tipologia_id == 6)) {
                            #allora proseguo
                        } else {
                           // echo '<h4>In questo periodo l\'utente risulta assente...richiesta annullata!</h4>';

                           echo Zend_Json::encode( array('success' => false, 'message'=> "In questo periodo l\'utente risulta assente...richiesta annullata!") );
                                   
                            return;
                        }
                    }
                } else {
                    //restituisce l'errore sulle date inserite
                  //  echo $mydate->getMessage();
                    echo Zend_Json::encode( array('success' => false, 'message'=>  $mydate->getMessage()) );
                      return;     
                }

                //creo date object
                $dateStart = new DateTime($start);
                $dateStop  = new DateTime($stop);

                $int = $dateStop->getTimestamp() - $dateStart->getTimestamp();
                if ($int < 0) {
                    //echo "<h4>Attenzione: La data di inizio è successiva a quella di fine</h4>";
                    echo Zend_Json::encode( array('success' => false, 'message'=> "Attenzione: La data di inizio è successiva a quella di fine") );
                           
                    return;
                }

                $start = $dateStart->format('Y-m-d');
                $stop  = $dateStop->format('Y-m-d');

                if( ($tipo->isMalattia() == 0) &&  ($tipo->isMaternita() == 0) ) {
                    //se faccio la richiesta di 1 giorno, controllo che non sia festivo
                    $totaleGiorniFerieRichiesti = Application_Service_Tools::getTotalDays($start, $stop);
                    if ((0 == $totaleGiorniFerieRichiesti)
                        && (!Application_Service_Tools::isHolidayLavorativo($start)
                            || Application_Service_Tools::isSunday($start))
                    ) {
                      //  echo "<h4>La data è festiva</h4>";
                        echo Zend_Json::encode( array('success' => false, 'message'=> "La data inserita è festiva!") );
                  
                        return;
                    }
                } else {
                    $totaleGiorniFerieRichiesti = Application_Service_Tools::getTotalDays($start, $stop, false);
                }

                //Prisma_Logger::logToFile(" giorni: $totaleGiorniFerieRichiesti");

                // VALIDO PER IL SOSTITUTO E L'IMPIEGATO
                //se sono un sostituto devo controllare che nei giorni che richiedo
                //per le ferie non sia occupato a sostituire qualcuno
                $check = $this->_assenze->findDoubleRoleByDate($userId, $start, $stop);

                # permetto di inserire una richiesta di permesso mattina/sera anche se è presente una assenza di permesso sera/mattina
                if (count($check) > 0) {
                    if (($tipologia_id == 6) && ($check->current()->tipologia_id == 7)) {
                        #allora proseguo
                    } elseif (($tipologia_id == 7) && ($check->current()->tipologia_id == 6)) {
                        #allora proseguo
                    } else {

                        //echo "<h4>L\'utente è occupato o in ferie in questo periodo</h4>";
                        echo Zend_Json::encode( array('success' => false, 'message'=> "L\'utente è occupato o in ferie in questo periodo") );
                  
                        return;
                    }
                }

                # dati da salvare
                $ip_remote_addr = $_SERVER["REMOTE_ADDR"];
                $data = array(
                    'user_id'      => $userId,
                    'tipologia_id' => $request->getParam('tipologia_id'),
                    'dateStart'    => $start,
                    'dateStop'     => $stop,
                    'giorni'       => $totaleGiorniFerieRichiesti,
                    'qta'          => ($qta > 0) ? $qta : $totaleGiorniFerieRichiesti,
                    'status'       => '0',
                    'date_insert' => date('Y-m-d H:i:s'),
                    'created_by_user_id' => $this->_user_id,
                    'ip_remote_addr' => $ip_remote_addr,
                    //'is_oraria' => $request->getParam('is_oraria')
                );
                # salvo tutto
                $table = new Application_Model_RichiesteMapper();



                try {


                    ####################################################################
                    # ------------   INSERIMENTO DETTAGLI E RESIDUI RICHIESTE ----------
                    Application_Service_Tools::emptyFerieEffettive();
                    $t_days = Application_Service_Tools::getArrayOfActualDays($t_start, $t_stop);

                    $entries = array();
                    foreach ($t_days as $k => $date) {
                        $_year = substr($date, 0, 4);  // year
                        if (!array_key_exists($_year, $entries)) {
                            $entries[$_year] = 0;
                        }
                        $entries[$_year]++;
                    } # end
                    $mapper = new Application_Model_ResiduiMapper();

                    foreach ($entries as $k => $v) {
                        $where = array(
                            'year' => $k,
                            'tipologia_id' => FERIE,
                            'user_id' => $userId
                        );
                        $q = $mapper->residuiGetAssignedQuantity($where);


                        //  controllo residui ON | OFF
                        if(isset($g_enable_check_residui_for_admin)) {
                            if (ON == $g_enable_check_residui_for_admin) {
                            //  $assigned = ($q + $v);
                                if (MAX_FERIE < $assigned) {
                                    $msg = "Attenzione, l\'utente non dispone di sufficenti giorni di ferie disponibili per l\'anno $k";
                                    echo "<script>alert('" . $msg . "');</script>";
                                    return;
                                }
                            }  # fine $g_enable_check residui( ON | OFF )
                        }


                    } # fine foreach
                    # ------------ FINE INSERIMENTO DETTAGLI E RESIDUI RICHIESTE ----------

                    $richiesta_id = $id = $table->save($data);




                    // INSERISCO I DETTAGLI DELLA RICHIESTA
                    if ($tipo->isFerie()) {

                        $db = Zend_Registry::get('db');
                        //$db->getProfiler()->setEnabled(true);
                        $db->beginTransaction();
                        try {
                            // Prisma_Logger::log($entries );
                            foreach ($entries as $k => $v) {
                                unset($where);
                                unset($data);
                                //inserisco dettaglio della richiesta
                                $data = array(
                                    'richiesta_id' => $richiesta_id,
                                    'user_id' => $userId,
                                    'tipologia_id' => FERIE,
                                    'year' => $k,
                                    'days' => $v,
                                    'inserted' => time(),
                                    'status' => 0
                                );
                                $db->insert('richieste_dettagli', $data);

                                //aggiorno residui delle richieste
                                $data = array();
                                $data['assigned'] = new Zend_DB_Expr("assigned + $v");
                                $where[] = "user_id = $userId";
                                $where[] = "tipologia_id = " . FERIE;
                                $where[] = "year = $k";
                                $db->update('richieste_residui', $data, $where);
                                unset($where);
                                unset($data);
                            }
                            $db->commit();
                        } catch (Exception $e) {
                            $db->rollBack();
                        }
                    } // fine tipo->isFerie()


                    $this->_fm->addMessage('Salvataggio eseguito con successo nella tupla n: ' . $id);
                    $this->view->messages = $this->_fm->getMessages();

                    // LOGGA SU DB L'EVENTO RICHIESTA
                    $TM = new Application_Model_TipologiaMapper();
                    $tipo = $TM->find($request->getParam('tipologia_id'));
                    $operatore = $this->_userMapper->find($userId);
                    $text = "Nuova richiesta di " . $tipo->Descrizione .
                        " inserita da ADMIN per l'utente " . $operatore->getAnagrafe() .
                        " per il periodo dal " . Application_Service_Tools::convertDataUsToIt($start) .
                        " al " . Application_Service_Tools::convertDataUsToIt($stop);

                    $logData = array(
                        'level' => 'richiesta',
                        'facility' => 'nuova',
                        'user_id' => $this->_user->getId(),
                        'address' => $_SERVER['REMOTE_ADDR'],
                        'descrizione' => $text
                    );

                    $logMapper = new Application_Model_LogMapper();
                    $logMapper->addEvent($logData);
                    //----------------------------------------------


                 //   $this->_helper->redirector->gotoUrl('/richieste/list');


                    echo Zend_Json::encode( array('success' => true, 'message'=> "Richiesta inserita con successo.") );
                  
                    return;



                } catch (Zend_Db_Table_Exception $e) {
                    $logData = array(
                        'level' => 'richiesta',
                        'facility' => 'nuova',
                        'user_id' => $this->_user->getId(),
                        'address' => $_SERVER['REMOTE_ADDR'],
                        'descrizione' => $e
                    );
                    $logMapper = new Application_Model_LogMapper();
                    $logMapper->addEvent($logData);
                    Prisma_Logger::logToFile($e->getMessage());
                   // echo $e->getMessage();
                    echo Zend_Json::encode( array('success' => false, 'message'=> $e->getMessage() ) );
                  
                }
            } // ----------  FINE INVIO RICHIESTA POST ( req->isPost() ) -----------



    }
  
















    /**
     * @deprecated in favore di ajax/deleteProcessingRequestAction
     * 
     * FUNZIONE ADecho $e->getMessage();MIN
     * Elimina una richiesta 'in lavorazione' senza invio email
     * Usato in caso di errore di richiesta es: date sbagliate
     */
    public function eliminaAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $richiesta_id = $this->_getParam('richiesta_id');
        $data = array(
            'richiesta_id' => (int)$richiesta_id,
            'status' => 4
        );
        
        $req = $this->_table->find($richiesta_id);
        if( !isset($req) || ($req->status != 0) ) {
            echo "<h4>Errore. Impossibile annullare la richiesta</h4>";
            return;
        }
        $status = $this->_table->update($data);
         
        if($status == 0) {
            echo "<h4>Impossibile aggiornare la tabella richieste</h4>";
            return;
        }
            // LOGGA SU DB L'EVENTO RICHIESTA
        $text = "Richiesta #" . $richiesta_id . 
                            " cancellata dall'utente " . $this->_user->getAnagrafe() ;
                             
                    
        $logData = array(
                      'user_id' => $this->_user_id,
                      'address' => $_SERVER['REMOTE_ADDR'],
                      'descrizione' => $text
                    );
                    
         $logMapper = new Application_Model_LogMapper(); 
         $logMapper->addEvent($logData);
          
         $this->_helper->redirector->gotoUrl('/richieste/list');
    }
  
        
    /**
     * FUNZIONE DELL'OPERATORE
     * 
     */
    public function cancellaAction() {
        $this->_helper->viewRenderer->setNoRender();
        $richiesta_id = $this->_getParam('richiesta_id');
        $data = array(
            'richiesta_id' => (int)$richiesta_id,
            'status' => 4
        );
        $dataNew = array(
            'status' => 4
        );
        $req = $this->_table->find($richiesta_id);
        
        # opzione valida per richieste successive al 331 dicembre 2013
        if(date('Y') > '2013') {
            if((int)$req->created_by_user_id !== (int)$this->_user_id) {
                echo "<h4>Questa richiesta è stata inserita dall'amministrazione e non è possibile fare richiesta di cancellazione.</h4>";
                return;
            }
        }
        
        if( !isset($req) ) {
            echo "<h4>Richiesta non presente nel database</h4>";
            return;
        } elseif(  ($req->status != 0) ) {
            if($req->status == 4) {
                echo "<h4>Richiesta annullata</h4>";
                return;
            } else {
                echo "<h4>Impossibile annullare la richiesta, contatta l'amministrazione</h4>";
                return;
            }
        }
        
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        $updated = 0;
        try {
            //salta il mio overrride in richieste perchè non passa dal mio file
            # aggiorno la tab richieste
            $updated = $db->update('richieste', $dataNew, "richiesta_id =  $richiesta_id ");
            
            ############################################################################
            // ---------   LOGICA PER AGGIORNAMENTO VALORI DETTAGLI/RESIDUI    --------- 
            # recupero la/le tuple, con i giorni da stornare, relative alla richiesta_id
            $m = new Application_Model_DbTable_RichiesteDettagli();
            $rs = $m->findByRequest($richiesta_id);
            # per ogni tupla presente,  aggiorno la tabella richieste_residui, altrimenti non faccio nulla
            if($rs->count() > 0) {
                foreach($rs as $k => $row) {
                    $data = array(); 
                    $data['assigned'] = new Zend_DB_Expr("assigned - $row->days");
                    $data['assigned_by_user_id'] = new Zend_DB_Expr("assigned_by_user_id - $row->days");
                    $where[] = "user_id = $row->user_id";
                    $where[] = "tipologia_id = $row->tipologia_id";
                    $where[] = "year = $row->year";
                    $db->update('richieste_residui', $data, $where);
                    unset($data);
                    unset($where);
                }
            }
            # cancello richieste dettagli dove c'è quella richiesta_id
            $deleted = $db->delete('richieste_dettagli', "richiesta_id = $richiesta_id");
            // --------------------------------------------------------------------------
            
            
            # commit
            $db->commit();
                  
            
        } catch(Exception $e) {
            Prisma_Logger::log("error: " . $e);
            $db->rollBack();
        }
        
        
        
        //$status = $this->_table->update($data);
         
        if($updated == 0) {
            echo "<h4>Impossibile aggiornare il database, contatta l'amministrazione</h4>";
            return;
        } else {
            Prisma_Logger::log('aggiornato');
        }
            // LOGGA SU DB L'EVENTO RICHIESTA
        $text = "Richiesta #" . $richiesta_id . 
                            " cancellata dall'utente " . $this->_user->getAnagrafe() ;
                             
                    
        $logData = array(
            'level'   => 'richiesta',
            'facility'    => 'deleted',
            'user_id' => $this->_user_id,
            'address' => $_SERVER['REMOTE_ADDR'],
            'descrizione' => $text
        );
                    
         $logMapper = new Application_Model_LogMapper(); 
         $logMapper->addEvent($logData);
         
         # redirect 
         $this->_helper->redirector->gotoUrl('/richieste/storico');
         
    }  
    
    
    
    
    
    /**
     * 
     * 
     */
    public function rifiutaAction() {
        global $g_mail_richiesta_refused;
        $this->_helper->_layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $req = $this->_request;
        if($req->isXmlHttpRequest()) 
        {
            $richiesta_id = $this->_getParam('richiesta_id');
            $response     = trim( $this->_getParam('std_response') );
            $note         = $this->_getParam('note');
            
            if($response !== '') {
                $note = $response + ' ' + $note;
            }
            
            
            $data = array(
                'richiesta_id' => $richiesta_id,
                'note'   => $note,
                'status' => 3
            );
            $status = $this->_table->update($data);
            $row    = $this->_table->find($richiesta_id);
            $t_user = $this->_userMapper->find($row->user_id);
             
            if($status) {
                if(OFF == $g_mail_richiesta_refused) {
                    return false;
                }
                 
                $email = new Application_Model_Email_Richiesta($t_user, $row);
                $email->sendRefused();
            }
        }
        
        
    }  
      
    /**
     * [ADMIN]
     */  
    public function assegnaAction() {

        try {
            $richiesta_id = $this->_getParam('richiesta_id');
            //$rielabora    = (bool) $this->_getParam('elaborate') ;

            $richiesta    = $this->_table->findByRequestId((int)$richiesta_id);
            $URM     = new Application_Model_UserResiduiMapper();
            if(!$richiesta) {
                Prisma_Logger::logToFile("richiesta mancante");
                throw new Exception("richiesta mancante");
                return;
            }

            $options = array(
                'user_id' => $richiesta->getUserId(),
                'year'    => date('Y')
            );


            //### necesario per recuperare le festivita per sede
            $UM = new Application_Model_UserMapper();
            $user = $UM->find($richiesta->getUserId());
            $sedeId = null;
            if($user->hasSede()) {
                $sedeId = $user->getSede()->getSedeId();
            }
            //###

            $residui = $URM->fetchAll($options) ;
            #Prisma_Logger::log($residui);
            if($richiesta->status == 0) {
                $this->view->richiesta    = $richiesta;
                $this->view->actualDays   = Application_Service_Tools::getArrayOfActualDays($richiesta->dateStart, $richiesta->dateStop, 'd-m-Y');
                $this->view->start        = Application_Service_Tools::convertDataUsToIt($richiesta->dateStart);
                $this->view->stop         = Application_Service_Tools::convertDataUsToIt($richiesta->dateStop);
                $this->view->quantita     = $richiesta->qta;
                $this->view->effettivi     = Application_Service_Tools::sottraiFeste($richiesta->dateStart, $richiesta->dateStop,'Y-m-d',true,$sedeId);
                $this->view->tipologia_id = $richiesta->tipologia_id;
                $this->view->residui      = $residui;
            } else {
                $this->_helper->viewRenderer->setNoRender();
                echo "<h3>Richiesta già evasa!</h3>";
            }
        } catch(Exception $e) {
            Prisma_Logger::logToFile($e->getMessage());
        }

    }
      
    /**
     * [OPERATORE] RICHIESTA ANNULLAMENTO
     * 
     */  
    public function annullarichiestaAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $request = $this->getRequest();
        
        // ajax mode
        if($request->isXmlHttpRequest()) {
            $user_id      = $request->getParam('user_id');
            $richiesta_id = $request->getParam('richiesta_id');
            $note         = trim($request->getParam('note'));
            
            if($note == '') { $note = "Nessuna annotazione inserita"; }
                        
            $reqObj =  $this->_table->find($richiesta_id); 
            //verifico che la richiesta appartenga effettivamente all'utente che la inoltra
            //se è abilitato procedo
            if($user_id == $reqObj->getUserId()) {
                //verifico che ci sia il permesso di inviare le email
                if(Zend_Registry::get('sendmail') == true) {
                    
                    $toEmail = array('' => DEVELOPER);
                    
                    //------ DA FARE
                    if( Zend_Registry::get('sendmailAdmin') == false) {
                        //TODO: mettere una email predefinita
                        //$toEmail[] = 'luca@puntooro.eu';
                    } else {
                        //TODO: recuperare l'elenco degli indirizzi ai quali inoltrare la richiesta
                        //$toMail[];
                    }
                                        
                    $mail = new Prisma_Mail();
                    $validator = new Zend_Validate_EmailAddress();
                    
                    if ($validator->isValid($this->_user->getEmail())) {
                        $fromEmail = $this->_user->getEmail();
                    } else {
                        //recupero email dalla configurazione
                       $fromEmail = 'server.prisma@gmail.com';
                    }  
                        $messaggio = $this->_user->getAnagrafe() . " richiede l'annullamento di ferie/permessi precedentemente accettati.<br><br>";
                        $messaggio.= "Richiesta n: " . $richiesta_id ."<br>";
                        $messaggio.= "Dal: " . Application_Service_Tools::convertDataUsToIt($reqObj->dateStart)."<br>";
                        $messaggio.= "Al:  " . Application_Service_Tools::convertDataUsToIt($reqObj->dateStop) ."<br><br>";
                        $messaggio.= "Note dell'utente: " . $note . "<br><br>";
                        $messaggio.= "IP: " . $_SERVER["REMOTE_ADDR"];
                        
                        $mail->setBodyHtml($messaggio);
                        $mail->setFrom($fromEmail, $this->_user->getAnagrafe() );
                        
                        foreach($toEmail as $k => $email) {
                            $mail->addTo($email);
                        }
                        
                        $mail->setSubject('Richiesta di annullamento ferie/permessi');
                        $logMap = new Application_Model_LogMapper();
                            
                            
                        try {
                            
                            $text = "Richiesta di annullamento per la Richiesta #" . $richiesta_id . 
                            " eseguita dall'utente " . $this->_user->getAnagrafe() ;
                            $logData = array(
                                'level'   => 'richiesta',
                                'facility'    => 'richiesta_annullamento',
                                'user_id' => $this->_user_id,
                                'address' => $_SERVER['REMOTE_ADDR'],
                                'descrizione' => $text
                              );
                            $logMap->addEvent($logData);
                            
                            //se non metto setFRom la mail viene inviata lo stesso
                            $mail->send();
                            
                        } catch(Exception $e) {
                             
                           echo Zend_Json::encode($e->getMessage());
                            //TODO: loggare l'errore e rivedere meglio $.ajax->error: di storico.phtml
                            
                            $data = array(
                                'level'       => 'richiesta',
                                'facility'    => 'error',
                                'user_id'     => $this->_user_id,
                                'address'     => $_SERVER['REMOTE_ADDR'],
                                'descrizione' => '[ERRORE] ' . $e->getMessage() . ' ' . __METHOD__  . ''
                              );
                            try {
                                $logMap->addEvent($data);
                            } catch(Exception $e) {
                                echo Zend_Json::encode($e->getMessage()); 
                            }
                        }
                        
                    } else {
                        //inivio email disabilitato
                    }
                }
            } else {
                echo "Utente non abilitato a questa richiesta " . $reqObj->getUserId();
            }
        }
   
        
      
        
         
   
      
      
      
  
        
        
        
        
   
    
    
    
    
}

 
