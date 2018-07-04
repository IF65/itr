<?php
    namespace Database\Tables;

	use Database\Database;

	class Aree extends Database {

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
                $sql = "CREATE TABLE IF NOT EXISTS `aree` (
							`id` int NOT NULL,
							`societa` varchar(2) NOT NULL DEFAULT '',
							`descrizione` varchar(255) NOT NULL DEFAULT '',
							`direzionale` tinyint(4) NOT NULL DEFAULT '1',
							`ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							PRIMARY KEY (`id`,`societa`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                $this->pdo->exec($sql);

				return true;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        
        public function eliminaTabella() {
        	try {
                $sql = "DROP TABLE IF EXISTS `aree`;";
                $this->pdo->exec($sql);

				return true;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        
        

        public function salvaRecord($record) {
             try {
                $this->pdo->beginTransaction();

				$sql = "insert into aree
							( id, societa, descrizione, direzionale )
						values
							( :id, :societa, :descrizione, :direzionale )
                        on duplicate key update
                            descrizione = :descrizione, direzionale = :direzionale";
				$stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(	":id" => $record['id'],
               							":societa" => $record['societa'],
                						":descrizione" => $record['descrizione'],
                                        ":direzionale" => $record['direzionale']
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

        public function cancellaRecord($id, $societa) {
            try {
                $this->pdo->beginTransaction();

                $sql = "delete from aree where id = :id and societa = :societa";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":id" => $id, ":societa" => $societa));
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

                $sql = "delete from aree where societa = :societa";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":societa" => $societa));
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

                $sql = "select a.`id`,a.`descrizione`,s.`codice`
                        from `aree` as a join areeSedi as l on a.`id`=l.`idArea` join sedi as s on s.`codice`=l.`codiceSede`
                        where a.`societa`= :societa";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":societa" => $societa));
                
                $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // creo l'elenco delle aree
                $aree = array();
                foreach ($data as &$row) {
                    $aree[$row['id']] = $row['descrizione'];
                }

                $elenco = array();
                foreach ($aree as $key => $value) {
                    $sedi = array();
                    foreach ($data as &$row) {
                        if ($row['id'] == $key) {
                            array_push($sedi, $row['codice']);
                        }
                    }
                    $elenco[$key] = array('descrizione' => $value, 'sedi' => $sedi);
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
