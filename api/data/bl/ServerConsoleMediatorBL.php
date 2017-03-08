<?php

class ServerConsoleMediatorBL {

	public function triggerFunction($input) {
	    $serverConsoleEnvironmentBL = new ServerConsoleEnvironmentBL;
	    $serverConsolePasswordBL = new ServerConsolePasswordBL;
	    $serverConsoleBL = new ServerConsoleBL;

		$function  = isset($input['function'])  ? trim($input['function'])  : '';
		$parameter = isset($input['parameter']) ? trim($input['parameter']) : '';

		switch ($function) {
			case 'extension_loaded':
			case 'get_cfg_var':
			case 'ini_get':
			case 'apache_get_modules':
			case 'class_exists':
			case 'function_exists':
				$result = $serverConsoleBL->getConfigurationValue($input);
			break;

			default:
				$result = array(
					'success' => false
				);
			break;
		}
		
		return $result;
	}

}