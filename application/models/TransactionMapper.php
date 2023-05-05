<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Transaction
 *
 * @author Luca
 */
class Application_Model_TransactionMapper  {
    
    /**
     *
     * @var type 
     */
    protected $_dbRichieste;
    
    /**
     *
     * @var type 
     */
    protected $_dbAssenze;
    
    /**
     *
     * @var type 
     */
    protected $_dbResidui;
    
    /**
     *
     * @var type 
     */
    protected $_dbSostituzioni;
    
    /**
     *
     * @var type 
     */
    protected $_dbConfig;        
    
    /**
     *
     * @var type 
     */
    protected $_lastAssenzaId;
    
    /**
     *
     * @var type 
     */
    protected $_lastId;
    
    /**
     *
     * @var type 
     */
    protected $_lastSostituzioneId;




    protected $_dbUsersConfigs;


    /**
     * 
     */
    public function __construct() {
        $this->_dbRichieste    = new Application_Model_RichiesteMapper();
        $this->_dbResidui      = new Application_Model_UserResiduiMapper();
        $this->_dbAssenze      = new Application_Model_AssenzeMapper();
        $this->_dbSostituzioni = new Application_Model_SostituzioniMapper();
        $this->_dbConfig       = new Application_Model_ConfigMapper();
        $this->_dbEventi       = new Application_Model_EventiMapper();
        $this->_dbUser         = new Application_Model_UserMapper();
        $this->_dbBudgetSostituzioni       = new Application_Model_BudgetSostituzioniMapper();
        $this->_dbPrimanota    = new Application_Model_PrimanotaMapper();



    }
    
    /**
     * 
     * @param type $values
     */
    public function assegna($values) {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            if(is_array($values)) {

                foreach($values as $k => $v) {
                   $class = $v['table']; 
                   $name = '_db'. ucfirst($class);
                   $table = $this->$name;
                   $method = $v['method'];
                                      
                   if('budgetSostituzioni' == $class) {
                       $v['values'];
                       $var = array(
                           'sostituzione_id' => $this->_lastId
                       ); 
                       $v['values'] = array_merge($v['values'], $var);
                   }
                   
                   if('sostituzioni' == $class) {
                       $v['values'];
                       $var = array(
                           'assenza_id' => $this->_lastAssenzaId
                       );
                       $v['values'] = array_merge($v['values'], $var);
                   }

                   $this->_lastId = $table->$method($v['values']);
                   
                   if('assenze' == $class) {
                       $this->_lastAssenzaId = $this->_lastId;
                       //inserisci evento
                       $evento = new Application_Model_Evento();
                       $rows = $evento->creaEventoDaAssenza($this->_lastAssenzaId);
                       $this->_dbEventi->insertMultiple($rows);
                   } 
               }           
             }
             $db->commit();
        
        } catch(Exception $e) {
               $db->rollBack();
               echo $e->getMessage();
        }
    }
    
    /**
     * Inserisco il nuovo utente e ne creo i residui
     * 
     * @param type $data
     */
    public function insertNewUser($data) {
        
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {

            $user_config = $data['user_config'];
            unset($data['user_config']);

            /** inserisco in users */
            $user_id = $this->_dbUser->insert($data);

            /** inserisco in users */
            $residuo = new Application_Model_Residuo();
            $residuo->crea($user_id);
            
            /* users_contracts */
            $uc_data = array(
                'contratto_id' => $data['contratto_id'],
                'user_id'      => $user_id,
                'start'        => $data['data_assunzione'],
                'stop'         => $data['data_cessazione'],
                'last'         => 1
            );

            /** inserisco nei contratti  */
            $db->insert('users_contracts', $uc_data);

            /** inserisco nella configurazione */
            $user_config['user_id'] = $user_id;
            $user_config['user_values'] = json_encode($user_config['user_values'] );
            /** inserisco in users */
            //Prisma_Logger::logToFile($user_config, true, "newuser");
            $db->insert('users_configs', $user_config);

            /* commit */
            $db->commit();
 
        } catch (Exception $e) {
            $db->rollBack();
            echo $e->getMessage();
        }
    }
    
    
    /**
     * Per eliminare qualisiasi richiesta accettata o meno
     * Procedura che elimina qualsiasi riga di qualsiasi tabella relativa a una richiesta
     * @param type $richiesta_id
     * @param bool $rollback - se impostato a true non effettuo il commit 
     */
    public function removeRequest($richiesta_id, $rollback = false) {
     
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            # annullo la richiesta (staus = 4)
            $values = array(
                'richiesta_id' => $richiesta_id,
                'status' => 4
            );
            $this->_dbRichieste->update($values);
                               
            ###################
            #### procedura per recuperare i valori da stornare dai residui residui
            ###################
            $reqObj = $this->_dbRichieste->find($richiesta_id);
            #Prisma_Logger::log($reqObj);
            # todo: come comportarsi se siamo prima della nuova situazione attuale
            $valueToUpdate = $reqObj->value_to_update;
            $tabToUpdate   = $reqObj->tab_to_update;
            $user_id = (int)$reqObj->getUserId();
            
            if(Prisma_Utility_Validate::isNull($user_id)) {
                throw new Exception("Errore, manca l'id dell'utente per annullare la richiesta");
            }
            
            # 1) prelevo l'anno dal dateStart
            $date = new Zend_Date($reqObj->getDateStart());
            $yearOfDateStart = $date->toString(Zend_Date::YEAR);
            
            # se la tabella 'richieste' contiene i valori dei residui, procedo... 
            if( ( (int)$valueToUpdate > 0 ) && ( null !== $tabToUpdate ) ) {
                $data = array(
                    'goduto' => new Zend_Db_Expr("goduto - $valueToUpdate"),
                    'totale' => new Zend_Db_Expr("totale + $valueToUpdate")
                );
                
                $currentYear = date('Y');
                $row = $this->_dbResidui->findTypeByUserAndYear($tabToUpdate, $user_id, $yearOfDateStart);
                if(!$row) {
                    $row = $this->_dbResidui->findTypeByUserAndYear($tabToUpdate, $user_id, $currentYear);
                }
                if(false !== $row) {
                    $this->_dbResidui->update($data, array('id' => $row->id));
                }
                
            } else 
            # ... altrimenti, la tupla della tab 'richieste' è stata inserita prima della nuova funzione residui...
            { 
                # 2) controllo il tipo di assenza da stornare
                $tipo = $reqObj->getTipologia();
                
                
                
                if($tipo->isFerie()) {
                    $tabToUpdate = 'FERIE';
                     
                    # 3) verifico la presenza della tupla relativa a quel tipo di assenza
                    $row = $this->_dbResidui->findTypeByUserAndYear($tabToUpdate, $user_id, $yearOfDateStart);
                    
                    ### se non trovo la riga del residuo per il particolare anno allora lo storno avviene nella tabella dell'anno in corso
                    if(!$row) {
                        $currentYear = date('Y');
                        $row = $this->_dbResidui->findTypeByUserAndYear($tabToUpdate, $user_id, $currentYear);
                    }
                               
                    if($row) {
                        # 4) conto i giorni effettivi assegnati
                        $ggTotali = $this->_dbAssenze->getTotaleGiorniPerRichiesta($richiesta_id);
                        //Prisma_Logger::log($ggTotali);
                        # 5) aggiorno (storno)
                        $data = array(
                            'goduto' => new Zend_Db_Expr("goduto - $ggTotali"),
                            'totale' => new Zend_Db_Expr("totale + $ggTotali")
                        );
                        //Prisma_Logger::log($data);
                        $where = array('id' => $row->id);
                        $status = $this->_dbResidui->update($data, $where);
                    }
                }elseif($tipo->permesso()) {
                    $ore = 0;
                    if( (int)$reqObj->getTipologia()->getFulltime() == 2)   {
                       $ore =  $reqObj->getUserObj()->getContratto()->getMattina();
                    } else if( (int)$reqObj->getTipologia()->getFulltime() == 3)  {
                       $ore =  $reqObj->getUserObj()->getContratto()->getSera();        
                    }

                    if($ore == '') {
                        $ore = 0;
                    }


                    $data = array(
                        'goduto' => new Zend_Db_Expr("goduto - $ore"),
                        'totale' => new Zend_Db_Expr("totale + $ore")
                    );
                    //Prisma_Logger::log($ore);
                    //Prisma_Logger::log($data);
                    $tabToUpdate = 'PERMESSO';
                    $row = $this->_dbResidui->findTypeByUserAndYear($tabToUpdate, $user_id, $yearOfDateStart);
                    if($row) {
                        if( ($row->maturato > 0 ) ) {        
                            $where = array('id' => $row->id);
                            $status = $this->_dbResidui->update($data, $where);
                        } else {
                            $tabToUpdate = 'EX-FEST';
                            $row = $this->_dbResidui->findTypeByUserAndYear($tabToUpdate, $user_id, $yearOfDateStart);
                            if($row) {
                                $where = array('id' => $row->id);
                                $status = $this->_dbResidui->update($data, $where);
                            }
                        }
                        //Prisma_Logger::log($where);
                        //Prisma_Logger::log($status);
                    } else {
                        $tabToUpdate = 'EX-FEST';
                        $row = $this->_dbResidui->findTypeByUserAndYear($tabToUpdate, $user_id, $yearOfDateStart);
                        if($row) {
                            $where = array('id' => $row->id);
                            $status = $this->_dbResidui->update($data, $where);
                        }
                        //Prisma_Logger::log($where);
                        //Prisma_Logger::log($status);
                    }
                }
                
                #$totale_da_stornare = $this->_dbAssenze->getGiorniByRequest($richiesta_id);
            }
            # ===============================================
            # ==== fine procedura aggiornamento users residui
            Prisma_Logger::logToFile( "cancello assenza ed eventi" );
            $assenze = $this->_dbAssenze->deleteByRequest($richiesta_id);
            Prisma_Logger::logToFile( "Assenza id da cancellare: " . json_encode($assenze));
            if($assenze == '') {
                Prisma_Logger::logToFile( "Assenza id nullo " );
            }
            $this->_dbEventi->deleteByAssenza($assenze);
            $sostituzione_id = $this->_dbSostituzioni->deleteByAssenza($assenze);
            
            # todo: cancellare anche qui?
            // $this->_dbBudgetSostituzioni->deleteBySostituzione($sostituzione_id);
            // $this->_dbPrimanota->deleteBySostituzione($sostituzione_id);
            
            ############################################################################
            // ---------   LOGICA PER AGGIORNAMENTO VALORI DETTAGLI/RESIDUI    --------- 
            # recupero la/le tuple, con i giorni da stornare, relative alla richiesta_id
            $m = new Application_Model_DbTable_RichiesteDettagli();
            $rs = $m->findByRequest($richiesta_id);
            # per ogni tupla presente,  aggiorno la tabella richieste_residui, altrimenti non faccio nulla
            if($rs->count() > 0) {
                foreach($rs as $k => $rowDlt) {
                    
                    unset($data);
                    unset($where);
                    $data = array();
                    $data['assigned'] = new Zend_DB_Expr("assigned - $rowDlt->days");
                    
                    // se l'utente che ha creato la richiesta è l'operatore o sostituto
                    // allora sottraggo i giorni che solo lui può richiedere
                    if((int)$reqObj->created_by_user_id == (int)$reqObj->getUserId() ) {
                        Prisma_Logger::log('cancello quello creato dall user');
                        $data['assigned_by_user_id'] = new Zend_DB_Expr("assigned_by_user_id - $rowDlt->days");
                    } else {
                        Prisma_Logger::log('NON cancello quello creato dall user');
                    }
                    
                    
                    $where[] = "user_id = $rowDlt->user_id";
                    $where[] = "tipologia_id = $rowDlt->tipologia_id";
                    $where[] = "year = $rowDlt->year";
                    try {
                        //$p = $db->setProfiler(true);
                      $db->update('richieste_residui', $data, $where);
                    } catch(Exception $e) {
                        Prisma_Logger::log($e->getMessage() );
                    }
                    unset($data);
                    unset($where);
                }
            }
            # cancello richieste dettagli dove c'è quella richiesta_id
            $deleted = $db->delete('richieste_dettagli', "richiesta_id = $richiesta_id");
            // --------------------------------------------------------------------------
            
            
            
            if($rollback === true) {
                $db->rollBack();
            } else {
                $db->commit();
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            Prisma_Logger::log($e->getLine());
            echo $e->getMessage();
        }
    }
    
    
    
     
    
    
    
    
    
}

 
