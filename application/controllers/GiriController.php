<?php

/**
 * Created by PhpStorm.
 * User: Luca
 * Date: 25/05/2016
 * Time: 18.14
 */
class GiriController extends Prisma_Controller_Action
{


    public function indexAction() {

        $mese = date('n');
        $anno = date('Y');
        $request = $this->getRequest();
        if( $request->isPost() ) {
            $mese = $this->getParam('mese');
            $anno = $this->getParam('anno');
        }

        $date = new Zend_Date();
        $date->set($mese, Zend_Date::MONTH);
        $date->set($anno, Zend_Date::YEAR);
        $date->set(1, Zend_Date::DAY);
        $date->set('02:00:00',Zend_Date::TIMES);
        $table = new Application_Model_DbTable_Giro();
        $value['year']  =  $date->toString('YYYY');
        $value['month'] =  $date->toString('MM');
        $rs = $table->findByDate($value);
        $this->view->date_presenti = $rs;
        $this->view->date = $date;
    }

    
    public function addAction() {

        $this->disableAll();
        $request = $this->getRequest();
        if($request->isXmlHttpRequest()) {
            $tm = $request->getParam('timestamp');
            $date = new Zend_Date($tm);
            $string = $date->toString('YYYY-MM-dd');

            $table = new Application_Model_DbTable_Giro();

            $row = $table->findDate($string);
            if($row) {
                $table->delete(array('giro_id = ?' => $row->giro_id));
                $response = 'del';
            } else {
                $table->insert(array('giorno' => $string));
                $response = 'add';
            }
            echo Zend_Json::encode( $response );
        }



    }
   


}