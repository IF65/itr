<?php
	@ini_set('memory_limit','256M');

	//recupero i parametri inviati in formato json
	$rowData = file_get_contents('php://input');
	//$rowData = file_get_contents('/Users/if65/Desktop/test.txt');

	$request  = json_decode($rowData, true);

	include(__DIR__.'/Database/bootstrap.php');

	use Database\Tables\Vendite;
	use Database\Tables\Aree;
	use Database\Tables\Areesedi;
	use Database\Tables\Sedi;

	$vendite = new Vendite($sqlDetails);
	$aree = new Aree($sqlDetails);
	$areeSedi = new Areesedi($sqlDetails);
	$sedi = new Sedi($sqlDetails);

    // impostazioni generali
	//--------------------------------------------------------------------------------
    $timeZone = new DateTimeZone('Europe/Rome');

	if ($request["functionName"] == "getDataV13") {
		foreach ($request["rows"] as &$row) {
		    $record = array();
			$record['stato'] = $row['error']*1;
			$record['codice'] = $row['negozio'];
			$record['data'] = $row['data'];
			$record['venduto'] = $row['venduto']*1;
			$record['vendutoInPromo'] = 0;
			$record['margine'] = 0;
			$record['clienti'] = $row['clienti']*1;
			$record['ultimoScontrino'] = $row['oraUltimoScontrino'];
			$record['passaggi'] = $row['passaggi']*1;
			$record['oreLavorate'] = $row['oreLavorate']*1;
			if ($row['chiuso'] == 'False') {
				$record['chiuso'] = 1;
			} else {
				$record['chiuso'] = 0;
			}
			if ($row['quadrato'] == 'True') {
				$record['quadrato'] = 1;
			} else {
				$record['quadrato'] = 0;
			}

			$errore = $vendite->salvaRecord($record);

			echo $vendite->salvaRecord($record);
		}
	}

	if ($request["functionName"] == "aggiornaIncassi") {

		$query = array();
		$query["draw"] = $request['draw'];
		$query["dataCorrente"] = $request['dataCorrente'];
		$query["dataInizio"] = $request['dataInizio'];
		$query["dataFine"] = $request['dataFine'];
		$query["dataCorrenteAP"] = $request['dataCorrenteAP'];
		$query["dataInizioAP"] = $request['dataInizioAP'];
		$query["dataFineAP"] = $request['dataFineAP'];

		header('Content-Type: Application/json;charset=utf-8');
		$result = $vendite->ricerca(json_encode($query), true);

		echo $result;
	}

	
	if ($request["functionName"] == "vendutoSettimanale") {

		$query = array();
		$query["draw"] = $request['draw'];
		$query["societa"] = $request['societa'];

		$result = $vendite->vendutoSettimanale(json_encode($query), true);

		echo $result;
	}

	if ($request["functionName"] == "aggiornaSedi") {
		
		$societa = $request['societa'];
		$aree->cancellaRecords($societa);
		$areeSedi->cancellaRecords($societa);
		$sedi->cancellaRecords($societa);
		
		foreach ($request["aree"] as &$area) {
			$area['societa'] = $societa;
			
			$result = $aree->salvaRecord($area);
			
			foreach ($area["sedi"] as &$sede) {
				$result =$areeSedi->salvaRecord(array('societa'=>$societa, 'idArea'=>$area['id'], 'codiceSede'=>$sede));
			}
		}
		
		foreach ($request["sedi"] as &$sede) {
			$result = $sedi->salvaRecord($sede);
		}
			
		echo true;
	}
	
	if ($request["functionName"] == "elencoSocieta") {
		$elencoAree = $aree->elenco('08');
		$elencoSedi = $sedi->elenco('08');
	
		echo json_encode(array('08' => array('descrizione' => 'Supermedia S.p.A.', 'aree' => $elencoAree, 'sedi' => $elencoSedi)));
	}


