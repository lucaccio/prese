<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class UserController extends Prisma_Controller_Action 
{
   
    
    public function preDispatch() {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
            echo 'LOGGATI';
        }
        $this->user_id = $auth->getIdentity()->user_id;
    }
   
    public function init() {
        $this->table = new Application_Model_UserMapper();
        
        $this->transactionTable = new Application_Model_TransactionMapper();
        
    }
    
    public function indexAction() {
        $this->_forward('list');
    }
           
    /**
     * 
     */     
    public function listAction() {
               
       $request = $this->_request;
       //parametro di default
       $active = 1;
       $level_id = '';
       
       $levelMap = new Application_Model_LevelMApper();
       $livelli = $levelMap->fetchAll()->toArray();
       $levels = array();
       $levels[0] = 'Tutti i livelli';
       $level_id = 0;
       $options = array();
       if($request->isGet()) {
          $options['active'] = 1;
       }
       
       foreach($livelli as $k => $v) {
           $id   = $v['level_id'];
           $desc = $v['descrizione'];
           $levels[$id] = $desc;
       }
       if($request->isPost() && 'list' == $request->getActionName()) {
           if(!is_null($active = trim($request->getParam('active')))) {  
                if($active != '') {
                     $options['active'] = (int)$active;
                }
           }         
           $level_id =  $request->getParam('level_id');
           if($level_id != 0) $options['level_id'] = $level_id ;
         
       } 
       
       
       $this->view->active = $active;
       $this->view->livelli = $levels;
       $this->view->level_id = $level_id;
       $this->view->elenco = $this->table->getAllUsers($admin = true, $options); 
    }         
    
    /**
     * 
     * @return type
     */
    public function addAction() {
           
       $dbLevel = new Application_Model_LevelMapper();
       $dbSedi  = new Application_Model_SediMapper();
       $dbContratti  = new Application_Model_ContrattiMapper();
       $this->view->level = $dbLevel->fetchAll();
       $this->view->sedi = $dbSedi->getAll();
       $this->view->contratto = $dbContratti->fetchAll(); 
       $request = $this->_request;
       if($request->isPost()) {
            $r = $request->getParams() ;
            # TODO: fare il check dei dati
              
            $dataAss = $r['data_assunzione'];
            $dateAss = new DateTime($dataAss);
            $formatDataAssunzione = $dateAss->format('Y-m-d');

            $formatDataCessazione = null;
            if($r['data_cessazione'] != null) {
                $dataCess = $r['data_cessazione'];
                $dateCess = new DateTime($dataCess);
                $formatDataCessazione = $dateCess->format('Y-m-d');
            }

                       
            $this->view->nome     = $nome            = ucfirst($r['nome']);
            $this->view->cognome  = $cognome         = ucfirst($r['cognome']);
            $this->view->email    = $email           = $r['email'];
            $username             = trim($r['username']) !== '' ? trim($r['username']) : $nome.$cognome ;
            $password             = md5($r['password']);
            $this->view->level_id = $level           = $r['level_id'];
            $this->view->sede_id  = $sede            = $r['sede_id'];
            $this->view->contratto_id = $contratto   = $r['contratto_id'];

            $this->view->da =  $data_assunzione = $formatDataAssunzione;

            $this->view->dc =  $data_cessazione = $formatDataCessazione;

            /** patrono */
            $this->view->patrono_sede_id = $sede_lavoro = $r['patrono_sede_id'];
            $this->view->patrono_lavorativo = $patrono_lavorativo = $r['patrono_lavorativo'];

            if( ($row = count($this->table->findByUsername($username))) == 1 ) {
                echo '<h4>Utente esistente</h4>';
                return;
            };
                 
            if( $contratto == 0 ) {
                echo "<script>
                    var dialog = Dialog.init( );
                    dialog.open('Attenzione. Inserire il tipo di contratto.');
                    </script>";
                    return;
            };
            
            
            $data = array(
                'nome'            => $nome,
                'cognome'         => $cognome,
                'email'           => $email,  
                'username'        => $username,
                'password'        => $password,
                'level_id'        => $level,
                'sede_id'         => $sede,
                'contratto_id'    => $contratto,
                'data_assunzione' => $data_assunzione,
                'data_cessazione' => $data_cessazione,
                'user_config'     => array(
                    'user_values' => array(
                        'sede_lavoro'        => $sede_lavoro,
                        'patrono_lavorativo' => $patrono_lavorativo
                    )),
            );

            $this->transactionTable->insertNewUser($data);

            $this->_forward('list');
       }

    }  
        
    
    
    public function deleteAction() {
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->_request->getParam('user_id');
        $this->table->cancellaUtente($id);
        $this->_forward('list');
    }
    
    public function activeAction() {
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->_request->getParam('user_id');
        $this->table->attivaUtente($id);
        $this->_forward('list');
    }
    
    public function successAction() {
          $this->_helper->viewRenderer->setNoRender();
          echo 'success';
    }  
    
    /**
     * 
     */
    public function viewAction() {
        $id = $this->_request->getParam('user_id');
        $this->view->user = $this->table->find((int)$id);
        
        $levelDb     = new Application_Model_LevelMapper();
        $contrattiDb = new Application_Model_ContrattiMapper();
        $sediDb      = new Application_Model_SediMapper();


        //Prisma_Logger::logToFile($this->view->user->getConfiguration()->sede_lavoro,true,"newuser");

        /**@26 aprile 2018 */
        //$usersConfigsDb = new Application_Model_DbTable_UsersConfigs();

     //   $this->view->configs = $usersConfigsDb->findByUser((int)$id);
        if(isset($this->view->user->getConfiguration()->sede_lavoro)) {
            $this->view->sede_lavoro = $sediDb->find($this->view->user->getConfiguration()->sede_lavoro);
        }


        $this->view->sedi      = $sediDb->getAll();
        $this->view->contratti = $contrattiDb->fetchAll();
        $this->view->level     = $levelDb->fetchAll();
        
        $req = $this->getRequest();
        if($req->isPost()) {
            $email        = trim($req->getParam("email"));
            $validator = new Zend_Validate_EmailAddress();
            if($email != '') {
                if(!$validator->isValid($email)) {
                    echo "<h4>Attenzione, errore nel formato email</h4>";
                    return;
                }
            }
                       
            //fare il controllo di coerenza per la sede
            $sede_id      = $req->getParam('sede_id');
            $contratto_id = $req->getParam('contratto_id');
            $level_id     = $req->getParam('level_id');
            
            $data_assunzione = trim( $req->getParam('data_assunzione') );
            if('' === $data_assunzione) {
                echo "<h4>Attenzione, inserire un a data di assunzione</h4>";
                return;
            }
            
            $array = explode("-", $data_assunzione); 
            $ts = mktime(0,0,0,$array[1],$array[0],$array[2]);
            $da = new Zend_Date($ts) ;
            $data_assunzione = $da->toString('yyyy-MM-dd');
            
            $data_cessazione = trim( $req->getParam('data_cessazione') );
            if('' != $data_cessazione) {
                $array = explode("-", $data_cessazione); 
                $ts = mktime(0,0,0,$array[1],$array[0],$array[2]); 
                $dc = new Zend_Date($ts);
                $data_cessazione = $dc->toString('yyyy-MM-dd');
            }
            
            
            $data = array(
                'email'        => $email,
                'contratto_id' => $contratto_id,
                'level_id'     => $level_id,
                'sede_id'      => $sede_id,
                'data_assunzione' => $data_assunzione,
                'data_cessazione' => $data_cessazione
            );
             
            # modifica password
            $newpw   = trim( $req->getParam('newpw') );
            $renewpw = trim( $req->getParam('renewpw') );
            if($newpw != '') {
                if($newpw === $renewpw) {
                    $data['password'] = md5($newpw);
                } else {
                    echo "<h4>Attenzione, le password non corrispondono</h4>";
                    return;
                }
            }
            
         //   if( ) {
                $this->table->update($data, $id) ;
                $configs = array(
                  'user_id' => $id,
                  'user_values' => array(
                    'sede_lavoro' => $req->getParam('sede_lavoro'),
                    'patrono_lavorativo' =>  $req->getParam('patrono_lavorativo')
                ));
                $db = new Application_Model_DbTable_UsersConfigs();
                $db->insertOrUpdate($configs);

                $this->_forward('list');
          //  } else {
           //     echo "<h4>Nessun dato modificato, non aggiorno nulla.</h4>";
         //       return;
        ///    }
            
            
        }
        
        
    }
    
    public function modifypasswordAction() {
        
        $user = $this->table->find($this->user_id);
        $this->view->user_id   = $user->getId();
        $this->view->nome      = $user->getNome();
        $this->view->cognome   = $user->getCognome();
       
        $this->view->username  = $user->getUsername();
        
        $request = $this->_request;
        
        if($request->isPost()) {
        
            $oldpw   = trim($request->getParam('oldpw'));
            $newpw   = trim($request->getParam('newpw'));
            $renewpw = trim($request->getParam('renewpw'));
            if('' == $oldpw || '' == $newpw || '' == $renewpw) {
                echo '<h4>Inserire tutti i campi</h4>';
                return;
            }
                                     
            if(md5($newpw) == md5($renewpw)) {
                if($user->isSecret(md5($oldpw))) {
                    $password = md5($newpw);
                    $data = array(
                        'password' => $password
                    );
                    $this->table->update($data, $this->user_id); 
                    echo '<h4>Dati aggiornati</h4>';
                    
                    }  else {
                        echo '<h4>La vecchia password è sbagliata</h4>';
                    }             
                } else {
                    echo '<h4>I campi della nuova password non coincidono</h4>';
                }
         }
    }
    
    
    public function modifyemailAction() {
        $user = $this->table->find($this->user_id);
        $this->view->user_id   = $user->getId();
        $this->view->nome      = $user->getNome();
        $this->view->cognome   = $user->getCognome();
        $this->view->email     = $user->getEmail();
        $this->view->username  = $user->getUsername();
        
        $request = $this->_request;
        
        if($request->isPost()) {
            $email = $request->getParam('email');
            if(trim($email) != ''){
                $validate = new Zend_Validate_EmailAddress();
                if(!$validate->isValid($email)) {
                    echo '<h3>Inserire un indirizzo valido email</h3>';
                    return;
                }  
            }
             
            $data = array(
                'email' => $email
            );
            $this->table->update($data, $this->user_id); 
            echo '<h4>Dati aggiornati</h4>';
            $user = $this->table->find($this->user_id);
            $this->view->email     = $user->getEmail();
        }
    }
    
        
    public function storicoAction() {
       
        $users =  $this->table->getAllUsers(false, null);
        $e = new Application_Model_DbTable_Eventi();
        $ur = new Application_Model_UserResiduiMapper();
        
       
        
        $collection = array();
        $tot_ferie = 0;
        $totale_ore = 0;
        foreach($users as $k => $user) 
        {
           $id = $user->getId();
           
           $tot_ferie     = $e->getNumAssenze($id, date('Y'), FERIE)->totale ; 
           $row_permessi  = $e->getNumAssenze($id, date('Y'), array(PERMESSO_MATTINA,PERMESSO_SERA))  ; 
           
           $_totf = 0;
           $_totp = 0;
           $Y = date('Y');
           $opt = array(
               'year' => $Y,
               'tipo' => 'FERIE'
           );
           
           $ferie =  $ur->findByUser($id, $opt);
           
           if($ferie) {
                $_totf = $ferie->goduto;
                $opt = array(
                    'year' => $Y,
                    'tipo' => 'PERMESSO'
                );
           }
           $permesso =  $ur->findByUser($id, $opt);
           if($permesso) {
               if($permesso->maturato > 0) {
                    $_totp += $permesso->goduto;
               } 
               $opt = array(
                    'year' => $Y,
                    'tipo' => 'EX-FEST'
               );
               $permesso =  $ur->findByUser($id, $opt);
               $_totp += $permesso->goduto;
                
           }
           
           
           
           
           
           
           if( $row_permessi  ) 
           {
                foreach($row_permessi as $k => $row) 
                {
                    if($row->tipologia_id == PERMESSO_MATTINA) 
                    {
                        $totale_ore += 3;
                    } elseif($row->tipologia_id == PERMESSO_SERA){
                        $totale_ore += 3.5 ;
                    } 
                }
            }
            
            $value = array(
                'username' => $user->getAnagrafe(),
                'ferie'    => $tot_ferie,
                '_ferie' => $_totf,
                'permessi' => $totale_ore,
                '_permessi' => $_totp
            );
            $tot_ferie  = 0;
            $totale_ore = 0;
            $collection[] = $value;
        }
       
        $this->view->collection = $collection;
        
    }
    
    
    public function checkResiduiAction()
    {
        $users =  $this->table->getAllUsers(false, null);
        $ur = new Application_Model_UserResiduiMapper();
        $this->view->collection = $ur->getResidui($users);
    }
    
    /**
     * Modifica contratto di lavoro utente
     * 
     */
    public function contrattoAction()
    {
        $uid = (int)$this->_request->getParam('user_id');
        if( !(is_int($uid)) || ($uid == '') ) {
            throw new Exception("Manca l'id utente o id non valido, impossibile procedere");
        }
        $user = $this->table->find($uid);
        if($user->getId() === null) {
            throw new Exception("L'utente con id {$uid} è inesistente");
        }
        if($user->isAdmin()) {
             throw new Exception("L'utente amministratore non ha contratto!");
        }
        
        $result = $user->getContractsList();
        
        if(!$result) {
            Prisma_Logger::log("costruisco users_contracts per l'utente");
            $cessazione = $user->getCessazione() ;
            if($cessazione == false) $cessazione = NULL;
            
            $contratto = $user->getContratto();
            if($contratto->isEmpty()) {
                 throw new Exception("Tipo di contratto non trovato, verificare nel database");
            }
            
            $data = array(
                'user_id'      => $user->getId(),
                'contratto_id' => $contratto->getId(),
                'start'        => $user->getAssunzione(),
                'stop'         => $cessazione,
                'last'         => 1
            );
            $db = Zend_Registry::get('db');
            $db->beginTransaction();
            try {
                $db->insert('users_contracts', $data);
                $db->commit();
            } catch (Exception $ex) {
                $db->rollBack();
            }
            
        }
        
        
        
        
        
        # lista contratti presenti
        $map = new Application_Model_ContrattiMapper();
        $list = $map->fetchAll();
        
        
        //$user->getLastInsertedContract();
        
        # views
        $this->view->user      = $user;
        $this->view->contratti = $list;
        $this->view->old_contracts = $user->getOldContracts();
    }
    
    /**
     * NG add user
     */
    public function addUserAction()
    {
        
    }
    
    public function elencoAssenzeAction()
    {
        $uid  = (int)$this->_request->getParam('uid');
        $year = date('Y');
        $um   = new Application_Model_UserMapper();
        $user = $um->getMeUser($uid);
        
        $am = new Application_Model_AssenzeMapper();
        $assenze = $am->getAssenze($uid,array('YEAR(dateStart)' => $year),"dateStart DESC");
        $this->view->user = $user;
        $this->view->assenze = $assenze;
        
        // 24/05/16
        $this->view->totali = array();
        
    }
    
    
    
}
