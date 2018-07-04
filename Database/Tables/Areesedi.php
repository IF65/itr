<?php
    namespace Database\Tables;

	use Database\Database;

	class Areesedi extends Database {

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
                $sql = "CREATE TABLE IF NOT EXISTS `areeSedi` (
                            `societa` varchar(2) NOT NULL DEFAULT '',
							`idArea` int NOT NULL,
							`codiceSede` varchar(4) NOT NULL DEFAULT '',
							PRIMARY KEY (`societa`,`idArea`,`codiceSede`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                $this->pdo->exec($sql);

				return true;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        
        public function eliminaTabella() {
        	try {
                $sql = "DROP TABLE IF EXISTS `areeSedi`;";
                $this->pdo->exec($sql);

				return true;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        
        

        public function salvaRecord($record) {
             try {
                $this->pdo->beginTransaction();

				$sql = "insert ignore into areeSedi
							( societa, idArea, codiceSede )
						values
							( :societa, :idArea, :codiceSede )";
                            
				$stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(	":societa" => $record['societa'],
               							":idArea" => $record['idArea'],
                						":codiceSede" => $record['codiceSede']
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

        public function cancellaRecord($id, $societa, $codiceSede) {
            try {
                $this->pdo->beginTransaction();

                $sql = "delete from areeSedi where id = :id and societa = :societa and codiceSede = :codiceSede";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(":id" => $id, ":societa" => $societa, ":codiceSede" => $codiceSede));
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

                $sql = "delete from areeSedi where societa = :societa";
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

        public function __destruct() {
			parent::__destruct();
        }

    }
?>
