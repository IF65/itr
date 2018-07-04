<?php
    namespace Database\Tables;

	use Database\Database;

	class Vendite extends Database {

        public function __construct($sqlDetails) {
        	try {
				parent::__construct($sqlDetails);

                self::creaTabella();

            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }

		public function creaTabella() {
        	try {
                $sql = "CREATE TABLE IF NOT EXISTS `vendite` (
							`data` date NOT NULL,
							`codice` varchar(4) NOT NULL DEFAULT '',
							`venduto` decimal(11,2) NOT NULL,
							`vendutoInPromo` decimal(11,2) NOT NULL,
							`margine` decimal(11,2) NOT NULL,
							`clienti` smallint(6) unsigned NOT NULL,
							`passaggi` smallint(6) unsigned NOT NULL,
							`oreLavorate` decimal(5,1) unsigned NOT NULL,
							`ultimoScontrino` varchar(8) NOT NULL DEFAULT '',
							`chiuso` tinyint(4) NOT NULL DEFAULT '0',
							`quadrato` tinyint(4) NOT NULL DEFAULT '0',
							`ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							`tsMod` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
							PRIMARY KEY (`data`,`codice`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                $this->pdo->exec($sql);

				return true;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }

        public function salvaRecord($record) {
             try {
                $this->pdo->beginTransaction();

				$sql = "insert into vendite
							( data, codice, venduto, vendutoInPromo, margine, clienti, passaggi, oreLavorate, ultimoScontrino, chiuso, quadrato )
						values
							( :data, :codice, :venduto, :vendutoInPromo, :margine, :clienti, :passaggi, :oreLavorate, :ultimoScontrino, :chiuso, :quadrato )
                        on duplicate key update
                            venduto = :venduto, vendutoInPromo = :vendutoInPromo, margine = :margine, clienti = :clienti, passaggi = :passaggi,
                            oreLavorate = :oreLavorate, ultimoScontrino = :ultimoScontrino, chiuso = :chiuso, quadrato = :quadrato";
				$stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(	":data" => $record['data'],
                						":codice" => $record['codice'],
                                        ":venduto" => $record['venduto'],
                                        ":vendutoInPromo" => $record['vendutoInPromo'],
                                        ":margine" => $record['margine'],
                                        ":clienti" => $record['clienti'],
                                        ":passaggi" => $record['passaggi'],
                                        ":oreLavorate" => $record['oreLavorate'],
                                        ":ultimoScontrino" => $record['ultimoScontrino'],
                                        ":chiuso" => $record['chiuso'],
                                        ":quadrato" => $record['quadrato']
									)
							   );

                $stmt->closeCursor();

                $this->pdo->commit();

				return 0;
            } catch (PDOException $e) {
             	$this->pdo->rollBack();
                return 1;
            }
        }

         public function verificaSeRecordModificato($record) {
            try {
            	$sql = "select venduto, vendutoInPromo, margine, clienti, passaggi, oreLavorate, ultimoScontrino, chiuso, quadrato
                        from vendite
                        where data = :data and codice = :codice";
				$stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":data" => $record['data'],":codice" => $record['codice']));
                $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);
				//$stmt = null;
				return json_encode($data);
                if ($row['venduto'] < $record['venduto'] || $row['vendutoInPromo'] != $record['vendutoInPromo'] || $row['margine'] != $record['margine'] ||
                    $row['clienti'] != $record['clienti'] || $row['passaggi'] != $record['passaggi'] || $row['oreLavorate'] != $record['oreLavorate'] ||
                    $row['ultimoScontrino'] != $record['ultimoScontrino'] || $row['chiuso'] != $record['chiuso'] || $row['quadrato'] != $record['quadrato']) {
                    return json_encode($row);
                }

                //return 0;

                return json_encode($row);
            } catch (PDOException $e) {
                 return 0;
            }
        }

        public function cancellaRecord($data, $codice) {
            try {
                $this->pdo->beginTransaction();

                $sql = "delete from vendite where data = :data and codice => :codice";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":data" => $data, ":codice" => $codice));
                $stmt->closeCursor();

                $this->pdo->commit();

                return true;
            } catch (PDOException $e) {
                $this->pdo->rollBack();

                die($e->getMessage());
            }
        }

        public function esportaFile($rows) {
            $csv = '';

            foreach ($rows as $record) {
                $csv .= $record['data']."\t";
                $csv .= $record['codice']."\t";
                $csv .= $record['venduto']."\t";
                $csv .= $record['vendutoInPromo']."\t";
                $csv .= $record['clienti']."\t";
                $csv .= $record['passaggi']."\t";
                $csv .= $record['oreLavorate']."\t";
                $csv .= $record['ultimoScontrino']."\t";
                $csv .= $record['chiuso']."\t";
                $csv .= $record['quadrato']."\n";
            }

            file_put_contents ( "/itr/export.txt" , $csv);
        }

        public function ricerca($queryJson) {
            try {
                $query = json_decode($queryJson, true);

            	$draw = $query["draw"];
                $dataCorrente = $query["dataCorrente"];
            	$dataInizio = $query["dataInizio"];
            	$dataFine = $query["dataFine"];
            	$dataCorrenteAP = $query["dataCorrenteAP"];
            	$dataInizioAP = $query["dataInizioAP"];
            	$dataFineAP = $query["dataFineAP"];

                $sql = "select
                			n.`societa` `codiceSocieta`,
                			n.`codice` `codiceCed`,
                            n.`codice_interno` `codice`,
                            sum(case when v.`data`>= '$dataInizio' and v.`data`<= '$dataCorrente' then case when v.`chiuso`=0 then 1 else 0 end else 0 end) `giorniApertura`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataCorrenteAP' then case when v.`chiuso`=0 then 1 else 0 end else 0 end) `giorniAperturaAP`,
                            sec_to_time(sum(case when v.`data`= '$dataCorrente' then TIME_TO_SEC(v.`ultimoScontrino`) else 9 end)) `oraUltimoscontrino`,
                            sum(case when v.`data`= '$dataCorrente' then v.`venduto` else 0 end) `giornataVenduto`,
                            sum(case when v.`data`= '$dataCorrente' then v.`clienti` else 0 end) `giornataClienti`,
                            sum(case when v.`data`= '$dataCorrente' then v.`passaggi` else 0 end) `giornataPassaggi`,
                            sum(case when v.`data`>= '$dataInizio' and v.`data`<= '$dataCorrente' then v.`venduto` else 0 end) `venduto`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataCorrenteAP' then v.`venduto` else 0 end) `vendutoAP`,
                            sum(case when v.`data`>= '$dataInizio' and v.`data`<= '$dataCorrente' then v.`clienti` else 0 end) `clienti`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataCorrenteAP' then v.`clienti` else 0 end) `clientiAP`,
                            sum(case when v.`data`>= '$dataInizio' and v.`data`<= '$dataCorrente' then v.`passaggi` else 0 end) `passaggi`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataCorrenteAP' then v.`passaggi` else 0 end) `passaggiAP`,
                            sum(case when v.`data`>= '$dataInizio' and v.`data`<= '$dataCorrente' then v.`oreLavorate` else 0 end) `oreLavorate`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataCorrenteAP' then v.`oreLavorate` else 0 end) `oreLavorateAP`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataFineAP' then v.`venduto` else 0 end) `vendutoOb`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataFineAP' then v.`clienti` else 0 end) `clientiOb`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataFineAP' then v.`passaggi` else 0 end) `passaggiOb`,
                            sum(case when v.`data`>= '$dataInizioAP' and v.`data`<= '$dataFineAP' then v.`oreLavorate` else 0 end) `oreLavorateOb`
                        from itr.`vendite` as v join archivi.negozi as n on n.`codice`=v.codice
                        where (v.`data`>= '$dataInizio' and v.`data`<= '$dataFine') or (v.`data`>= '$dataInizioAP' and v.`data`<= '$dataFineAP')
                        group by 1,2,3
                        order by lpad(substr(n.`codice_interno`,3),3,'0')";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();

                $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $recordsTotali = count($data);

				//return json_encode(array("sql" => $sql), true);
				return json_encode(array("draw" => $draw, "recordsTotal"=>$recordsTotali*1,"recordsFiltered"=>$recordsTotali*1,"data"=>$data), true);

            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        
        public function vendutoSettimanale($queryJson) {
            try {
                $query = json_decode($queryJson, true);

            	$draw = $query["draw"];
                $societa = $query["societa"];

                $sql = "select v.`codice`, yearweek(v.`data`,1) `settimana`, sum(v.`venduto`) `venduto`, sum(v.`clienti`) `clienti`,sum(v.`passaggi`) `passaggi`,sum(v.`oreLavorate`)` oreLavorate`
                        from vendite as v where v.`data`>='2017-01-01' and v.`codice` like :societa group by 1,2 order by 1,2";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":societa" => $societa.'%'));

                $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                $negozi = array();
                foreach ($data as &$row) {
                     $settimana = array($row['settimana'] => array('venduto' => $row['venduto'], 'clienti' => $row['clienti'], 'passaggi' => $row['passaggi'], 'oreLavorate' => $row['oreLavorate']));
                    if (array_key_exists($row['codice'],$negozi)) {
                        $negozi[$row['codice']] += $settimana;
                    } else {
                        $negozi[$row['codice']] = $settimana;
                    }
                }

				return json_encode(array("draw" => $draw, "negozi"=>$negozi), true);

            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }

        public function __destruct() {
			parent::__destruct();
        }

    }
?>
