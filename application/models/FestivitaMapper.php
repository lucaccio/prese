<?php

/**
 * Description of FestivitaMapper
 *
 * @author Luca
 * 
 */
class Application_Model_FestivitaMapper extends Prisma_Mapper_Abstract 
{
    
    
    public function __construct() {
        $this->_class = 'Application_Model_DbTable_Festivita';
    } 
    
    public function findAll($lavorativo = null) {
        return $this->getDbTable()->findAll($lavorativo);
    }
    
    //restituisce un array di festivita
    public function getFestivita($sede = null) {
        return $this->getDbTable()->holidays($sede);
    }

    /**
     * @04/12/2020
     */
    public function getFestivitaLavorative($lavorativo) {
        return $this->getDbTable()->holidaysLavorativi($lavorativo);
    }
    

    
    public function insert($data) {
        return $this->getDbTable()->insert($data);
    }
    
    public function update($data) {
        return $this->getDbTable()->update($data);
    }


    /**
     * Ritorna un oggetto Festivita
     * @param $id
     * @return Application_Model_Festivita
     *
     */
    public function find($id) {
        $rows = $this->getDbTable()->find($id);
        if($rows->count() > 0) {
            $row = $rows->current();
            $obj = new Application_Model_Festivita($row);
            return $obj;    
        } else {
            Prisma_Logger::logToFile("NO FESTIVITA PER ID: $id");           
            echo 'no festivita';
        }
    }

    /**
     *
     * @param date $date
     *
     * @return
     */
    public function findPatronalSaint($sede_id = 0) {
        return $this->getDbTable()->findPatronalSaint($sede_id);
    }




    /**
     * Restituisce un array date -> descrizione delle feste
     * @since 26 marzo 2018
     * @return array
     */
    public function getFesteNazionaliEPerSede($periodo = null, $sede_id = null, $calcoloPasqua = false ) {


        if($periodo) {
            if (is_array($periodo)) {
                $mese = isset($periodo['mese']) ? $periodo['mese'] : date('n');
                $anno = isset($periodo['anno']) ? $periodo['anno'] : date('Y');
            } else {
                if ($periodo == null || $periodo > 12 || $periodo < 1) {
                    $mese = date('n');
                    $anno = date('Y');
                }
            }
        } else {
            $mese = date('n');
            $anno = date('Y');
        }

        if($sede_id == null) {

            //25/luglio/2019
            $sede_id = 1;

            //@todo impostare la sede di default oppure di amministrazione
            Prisma_Logger::logToFile("Da sistemare: assegnare al dipendente una sede per il patrono", true, 'error.log', 3);
           // throw new Exception("impossibile recuperare il patrono, manca la sede associata al dipendente");
        }

        //@todo potrei trasformarlo in zend_date
        $when = array(
            'mese' => $mese,
            'anno' => $anno
        );


        // elenca le feste nazionali e quelle citta dei negozi, in un determinato mese dell'anno
        $result = $this->getDbTable()->elencaFestiviNazionaliEPerSedeMensile($when, $sede_id);
        //Prisma_Logger::logToFile($result);
        $elenco    = array();
        $festivita = array();



        foreach($result as $k => $row) {
            $m = strlen($row->mese) < 2 ? "0".$row->mese : $row->mese ;
            $d = strlen($row->giorno) < 2 ? "0".$row->giorno : $row->giorno ;
            $date = "$anno-$m-$d";
            $festivita[$date] = array(
                "descrizione" => $row->descrizione,
                "lavorativo" => $row->lavorativo,
                "nazionale" => $row->nazionale,
                'infrasettimanale' => $row->infrasettimanale,
                "patrono" =>$row->patrono,
                'date' =>  $date
                );
        }


        if($calcoloPasqua) {
            $pasqua    = $this->calcolaPasqua($anno);
            if(date('m', strtotime($pasqua)) == $mese) {
                $festivita[$pasqua] = array(
                    "descrizione" => "Pasqua",
                    "lavorativo"  => 0,
                    "nazionale"   => 0,
                    'infrasettimanale' =>0, //non dovrebbe essere pagata in quanto cade di domenica sempre
                    "patrono"     => 0,
                    'date' =>  $pasqua
                );
            }
        }



        $pasquetta = $this->calcolaPasquetta($anno);
        if(date('m', strtotime($pasquetta)) == $mese) {
            $festivita[$pasquetta] = array(
                "descrizione" => "Pasquetta",
                "lavorativo" => 0,
                "nazionale" => 0,
                'infrasettimanale' =>1,
                    "patrono" => 0,
                'date' => $pasquetta
            );
        }

        $elenco['sede'] = $sede_id;
        $elenco['totali'] = 0;
        $elenco['feste'] = $festivita;
        $elenco['totali'] = count($elenco['feste']);
       // $this->ordinaElencoPerData($elenco);
        //Prisma_Logger::logToFile($elenco);
        return $elenco;

    }


    /**
     * @param null $mese
     * @return array
     * @throws Exception
     */
    public function getFestePerMese($mese = null) {

        if($mese == null || $mese > 12 || $mese < 1) {
              $mese = date('n');
        }


        $result = $this->getDbTable()->findAllByMonth($mese );
        $elenco = array();
        $festivita = array();
        $y = date('Y');
        foreach($result as $k => $row) {
            $m = strlen($row->mese) < 2 ? "0".$row->mese : $row->mese ;
            $d = strlen($row->giorno) < 2 ? "0".$row->giorno : $row->giorno ;
            $date = "$y-$m-$d";
            $festivita[$date][] = array(
                'sede' => $row->sede_id,
                "descrizione" => $row->descrizione,
                "lavorativo" => $row->lavorativo,
                "nazionale" => $row->nazionale,
                'infrasettimanale' => $row->infrasettimanale,
                "patrono" =>$row->patrono,
                'date' =>  $date

            );
        }
        $pasqua    = $this->calcolaPasqua($y);
        $pasquetta = $this->calcolaPasquetta($y);

        if(date('m', strtotime($pasqua)) == $mese) {
            $festivita[$pasqua] []= array(
                'sede' => $row->sede_id,
                "descrizione" => "Pasqua",
                "lavorativo"  => 0,
                "nazionale"   => 0,
                'infrasettimanale' =>0,
                "patrono"     => 0,
                'date' =>  $pasqua
            );
        }
        if(date('m', strtotime($pasquetta)) == $mese) {
            $festivita[$pasquetta][] = array(
                'sede' => $row->sede_id,
                "descrizione" => "Pasquetta",
                "lavorativo" => 0,
                "nazionale" => 0,
                'infrasettimanale' =>1,
                "patrono" => 0,
                'date' => $pasquetta
            );
        }


        $elenco['totali'] = 0;
        $elenco['feste'] = $festivita;
        $elenco['totali'] = count($elenco['feste']);
        // $this->ordinaElencoPerData($elenco);

        return $elenco;

    }








    /**
     * Esegue un ordimanmento di un array in base alla data
     *
     * @since 26 marzo 2018
     * @param $elenco
     */
    public function ordinaElencoPerData(&$elenco) {
        usort($elenco['feste'] , function($a, $b){
            return strtotime($a['date']) - strtotime($b['date'] );
        });
    }


    /**
     * calcola il giorno di Pasqua in formatop iso8610
     *
     * @since 26 marzo 2018
     *
     */
    public function calcolaPasqua($year = null) {
        if(!$year){
            $year = date('Y');
        }
        return  date('Y-m-d',easter_date($year));
    }

    /**
     * @param null $year
     * @return false|string
     */
    public function calcolaPasquetta($year = null) {
        if(!$year){
            $year = date('Y');
        }
        return  date('Y-m-d', strtotime('+1 day', strtotime($this->calcolaPasqua($year))));
    }


    
}


