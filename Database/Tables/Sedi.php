<?php
    namespace Database\Tables;

	use Database\Database;

	class Sedi extends Database {

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
                $sql = "CREATE TABLE IF NOT EXISTS `sedi` (
                        `codice` varchar(4) NOT NULL DEFAULT '',
                        `codiceCA` varchar(6) NOT NULL DEFAULT '',
                        `codiceInterno` varchar(4) NOT NULL DEFAULT '',
                        `codiceOrdinamento` int(11) NOT NULL DEFAULT '0',
                        `dataApertura` date NOT NULL,
                        `dataChiusura` date NOT NULL,
                        `descrizione` varchar(255) NOT NULL DEFAULT '',
                        `eliminata` tinyint(4) NOT NULL DEFAULT '0',
                        `ip` varchar(30) NOT NULL DEFAULT '',
                        `tipo` varchar(2) NOT NULL DEFAULT '',
                        `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`codice`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                $this->pdo->exec($sql);

				return true;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        
        public function eliminaTabella() {
        	try {
                $sql = "DROP TABLE IF EXISTS `sedi`;";
                $this->pdo->exec($sql);

				return true;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        
        

        public function salvaRecord($record) {
             try {
                $this->pdo->beginTransaction();

				$sql = "insert into sedi
							( `codice`,`codiceCA`,`codiceInterno`,`codiceOrdinamento`,`dataApertura`,`dataChiusura`,`descrizione`,`eliminata`,`ip`,`tipo`  )
						values
							( :codice, :codiceCA, :codiceInterno, :codiceOrdinamento, :dataApertura, :dataChiusura, :descrizione, :eliminata, :ip, :tipo )
                        on duplicate key update
                            codice = :codice, codiceCA = :codiceCA, codiceInterno = :codiceInterno, codiceOrdinamento = :codiceOrdinamento, dataApertura = :dataApertura,
                            dataChiusura = :dataChiusura, descrizione = :descrizione, eliminata = :eliminata, ip = :ip, tipo = :tipo";
				$stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(	":codice" => $record['codice'],
               							":codiceCA" => $record['codiceCA'],
                						":codiceInterno" => $record['codiceInterno'],
                                        ":codiceOrdinamento" => $record['codiceOrdinamento'],
                                        ":dataApertura" => $record['dataApertura'],
                                        ":dataChiusura" => $record['dataChiusura'],
                                        ":descrizione" => $record['descrizione'],
                                        ":eliminata" => $record['eliminata'],
                                        ":ip" => $record['ip'],
                                        ":tipo" => $record['tipo'],
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

        public function cancellaRecord($codice) {
            try {
                $this->pdo->beginTransaction();

                $sql = "delete from sedi where codice = :codice";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":codice" => $codice));
                $stmt->closeCursor();

                $this->pdo->commit();

                return true;
            } catch (PDOException $e) {
                $this->pdo->rollBack();

                die($e->getMessage());
            }
        }
        
        public function cancellaRecords($societa) {
            try {
                $this->pdo->beginTransaction();

                $sql = "delete from sedi where codice like :societa";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":societa" => $societa."%"));
                $stmt->closeCursor();

                $this->pdo->commit();

                return true;
            } catch (PDOException $e) {
                $this->pdo->rollBack();

                die($e->getMessage());
            }
        }
        
        public function elenco($societa) {
            try {
                $this->pdo->beginTransaction();

                $sql = "select * from sedi where codice like :societa";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":societa" => $societa.'%'));
                
                $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                //,,,,,,,,,
                $elenco = array();
                foreach ($data as &$row) {
					$elenco[$row['codice']] = array(
														'codiceCA' => $row['codiceCA'], 
														'codiceInterno' => $row['codiceInterno'], 
														'codiceOrdinamento' => $row['codiceOrdinamento'], 
														'dataApertura' => $row['dataApertura'], 
														'dataChiusura' => $row['dataChiusura'], 
														'descrizione' => $row['descrizione'], 
														'eliminata' => $row['eliminata'], 
														'ip' => $row['ip'], 
														'tipo' => $row['tipo']
													);
                }
                
                return $elenco;
            } catch (PDOException $e) {
                $this->pdo->rollBack();

                die($e->getMessage());
            }
        }

        public function __destruct() {
			parent::__destruct();
        }

    }
?>
