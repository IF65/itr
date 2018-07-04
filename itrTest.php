<?php
	@ini_set('memory_limit','256M');

	include(__DIR__.'/Database/bootstrap.php');

	use Database\Tables\Vendite;
	use Database\Tables\Aree;
	use Database\Tables\Areesedi;
	use Database\Tables\Sedi;

	$vendite = new Vendite($sqlDetails);
	$aree = new Aree($sqlDetails);
	$areeSedi = new Areesedi($sqlDetails);
	$sedi = new Sedi($sqlDetails);

    $elencoAree = $aree->elenco('08');
	$elencoSedi = $sedi->elenco('08');
	
	$societa = array('societa' => '08', 'aree' => $elencoAree, 'sedi' => $elencoSedi);

	print_r($societa);
