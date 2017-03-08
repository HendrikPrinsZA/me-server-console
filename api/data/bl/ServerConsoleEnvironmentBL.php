<?php

class ServerConsoleEnvironmentBL {

	// private createRecord($input) {
	// 	$returnArray = array(
	// 		''
	// 	);

	// }
	/*
	Returns a structured array of environmental information 
	*/
	public function getEnvironmentSummary() {
		$serverConsoleBL = new ServerConsoleBL;

		$objectBase = array();

		$returnArray = array();

		// Simple Details
		$returnArray['phpConfigPath'] = get_cfg_var("cfg_file_path");

		$returnArray['phpVersionInfo'] = $serverConsoleBL->getPHPVersion();

		try {
			$returnArray['mysqlVersionInfo'] = $serverConsoleBL->getMySQLVersion();
		} catch (Exception $e) {
		    $returnArray['mysqlVersionInfo'] = $e->getMessage();
		}

		// Configuration Match Tables (Will probably use in some graphical display - dials)
		$returnArray['phpVariables'] = $serverConsoleBL->checkPHPVariables();
		if (isset($returnArray['phpVariables']['records'])) { $returnArray['phpVariables']['records'] = null; unset($returnArray['phpVariables']['records']); }

		// Configuration Match Tables (Will probably use in some graphical display - dials)
		$returnArray['mysqlVariables'] = $serverConsoleBL->checkMySQLVariables();
		if (isset($returnArray['mysqlVariables']['records'])) { $returnArray['mysqlVariables']['records'] = null; unset($returnArray['mysqlVariables']['records']); }

		return $returnArray;
	}

}


