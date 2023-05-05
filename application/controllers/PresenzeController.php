<?php
/**
 * Created by PhpStorm.
 * User: Fabiola
 * Date: 26/03/2018
 * Time: 17:58
 */

class PresenzeController extends Zend_Controller_Action
{

    public function init() {
        $this->_helper->_layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_db = new Application_Model_AssenzeMapper();


    }


    public function indexAction() {
        /**
         * @TODO Prelevare mese e anno da un form
         */
        $mese = date('n');
        $anno = date('Y');


        $mese = "4";
        $anno = "2018";

        $date = new Zend_Date();
        $date->setDay(1);
        $date->setMonth($mese);
        $date->setYear($anno);
        // throw new Exception($date->toString('Y-MMMM-d'));
        //monica
        $uid = 18;


        $cartellino = new Application_Model_Cartellino_CartellinoBase($uid, $date);

        $cartellino = new Application_Model_Cartellino_CartellinoOre($cartellino);

        $cartellino = new Application_Model_Cartellino_CartellinoAssenze($cartellino);

        $cartellino = new Application_Model_Cartellino_CartellinoFestivita($cartellino);

        $data = $cartellino->genera();

       //Zend_Debug::dump(debug_backtrace());
       // Zend_Debug::dump( $data['totale'] );
       //  Zend_Debug::dump( $data['cartellino'] );

        $this->stampa($data);
    }


    public function stampa($dati) {
        $c = $dati['cartellino'];
        echo "<table>";
        echo "<tr align='center'>";
        foreach($c as $k => $value) {
            echo "<td >$k</td>";
        }
        echo "</tr>";
        echo "<tr align='center'>";
        foreach($c as $k => $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
        echo "</table>";
    }




    /**
     * @throws Zend_Exception
     */
    public function azzeraDBUserConfigs() {
        $db = Zend_Registry::get('db');

        $drop   = "DROP TABLE IF EXISTS `users_configs`";

        $truncate = "TRUNCATE TABLE `users_configs`";

        $create = "CREATE TABLE `users_configs` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `user_id` int(11) NOT NULL,
                     `user_values` varchar(255) COLLATE utf8_bin NOT NULL,
                     PRIMARY KEY (`id`),
                     UNIQUE KEY `user_id` (`user_id`)
                      ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin";


        $stmt = $db->query($drop);
        // $stmt->execute();
        $stmt = $db->query($create);
        //$stmt->execute();

    }

    public function migraAction() {

        $this->azzeraDBUserConfigs();

        $table = new Application_Model_DbTable_UsersConfigs();
        $UM  = new Application_Model_UserMapper();
        $users  = $UM->getAllUsers(null,null,null,"user_id ASC");

        //$user = $UM->find(37);

        foreach ($users as $k => $user)
        {
            $sede = $user->getSede()->getSedeId();
            $id = $user->getId();

            $nome = $user->getAnagrafe();
            Prisma_Logger::logToFile("Sede utente $nome ($id) : $sede",true, 'migrazione');
            if(!$sede) {
                $sede = 1;
                Prisma_Logger::logToFile("Sede mancante, imposto a  : $sede",true, 'migrazione');
            }
            $F = new Application_Model_FestivitaMapper();
            $patrono = $F->findPatronalSaint($sede);
            if($patrono) {
                $lavorativo = $patrono->lavorativo;
                Prisma_Logger::logToFile("Patrono lavorativo : $lavorativo",true, 'migrazione');
            } else {
                Prisma_Logger::logToFile("Patrono mancante per sede  : $sede",true, 'migrazione');
            }
            //$rs = $table->creaRowset();
           // $r = $table->createRow();
            $value = array(
                'user_id' => $id,
                'user_values'   =>  array(
                        'sede_lavoro' => $sede,
                        'patrono_lavorativo' => $lavorativo
                    ),
            );

            try {
                $table->insertOrUpdate($value);
                echo "<p>ok $id</p>";
            } catch(Exception $e) {
                Prisma_Logger::logToFile("errore", true, 'migrazione');
                echo($e->getMessage());
            }

            Prisma_Logger::logToFile("inserisco nel db",true, 'migrazione');
        }
    }


}