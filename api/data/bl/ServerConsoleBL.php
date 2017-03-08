<?php

class ServerConsoleBL {

	public function getMySQLVersion() {

	    $value = '-1';
	    if (!empty(DB_HOST)) {
	        $mysql = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	 
	        if (mysqli_connect_errno()) {
	            $value = '-1';
	        } else {
	            $value = mysqli_get_server_info($mysql);
	            mysqli_close($mysql);
	        }

	    } else {
	        // Only available if access is granted to the commandline
	        $output = shell_exec('mysql -V'); 
            preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version); 
	        $value = $version[0];
	    }

	    return array(
	        'success' => true,
	        'value' => $value
	    );
	}

	public function getPHPVersion() {

	    if (defined('PHP_VERSION')) {
	    	$value = PHP_VERSION;
	    } else {
	    	$value = '-1';
	    }
	    // $value = '-1';
	    return array(
	        'success' => true,
	        'value' => $value
	    );
	}

	public function getIoncubeVersion() {
        $value = '-1';
        if (function_exists('ioncube_loader_iversion')) {
            $liv = ioncube_loader_iversion();
            // $value = sprintf("%d.%d.%d", $liv / 10000, ($liv / 100) % 100, $liv % 100);
            $value = sprintf("%d.%d", $liv / 10000, ($liv / 100) % 100); // Only major.minor
        }
	    // $value = '-1';
	    return array(
	        'success' => true,
	        'value' => $value
	    );
	}

	private function custom_check_write_access($locationPrefix = '../../../') {

		$pathArray = array(
			'backend/file_uploads'     => array('expected' => '0775', 'public_file' => 'testfile.txt'),
			'backend/ftp_temp_upload'  => array('expected' => '0775', 'public_file' => 'testfile.txt'),
			'backend/import_uploads'   => array('expected' => '0775', 'public_file' => 'testfile.txt'),
			'backend/version/_import'  => array('expected' => '0775', 'public_file' => 'testfile.txt'),
			'backend/version/uploaded' => array('expected' => '0775', 'public_file' => 'testfile.txt'),
			'config'                   => array('expected' => '0770'),
			'config/onpremise.ini'     => array('expected' => '0660'),
			'onpremise'                => array('expected' => '0770'),
			'onpremise/logs'           => array('expected' => '0770')
		);
		$errors = array();
		$additionalLines = array();

		$paths = array();

		foreach ($pathArray as $path => $details) {
			$error = '';

			$read  = 'n';
			$write = 'n';
			$fileperms = '';
			$expected  = $details['expected'];


			if (!empty($details['public_file'])) {
				$additionalLines[] = $locationPrefix.$path.' <a href="../../'.$path.'/'.trim($details['public_file']).'" target="_blank">Test Download</a>';
			}


			if (file_exists($locationPrefix.$path)) {
				$read  = 'y';

				$fileperms = substr(sprintf('%o', fileperms($locationPrefix.$path)), -4);

				if (intval($fileperms) < intval($expected)) {
					$error .= '- Invalid permissions (Expected '.$expected.'): '.$fileperms;

				} else if (is_writable($locationPrefix.$path)) {
					if (!empty($details['public_file'])) {
						$myfile = fopen($locationPrefix.$path."/testfile.txt", "w");
						fwrite($myfile, "Test file created.\n\n100%");
						fclose($myfile);
					}
					$write = 'y';

				} else {
					$error .= '- Permission denied to write to directory';

				}
			} else {
				$error .= '- Could not find directory';
			}


			if (!empty($error)) {
				$errors[] = $error.': "'.$locationPrefix.$path.'"';
			}

			$paths[] = array(
				'path'         => $path,
				'read'		   => $read,
				'write'		   => $write,
				'expected'	   => $expected,
				'fileperms'	   => $fileperms,
				'success'      => empty($error),
				'msg'          => empty($error) ? 'OK' : $error,
				'error'        => $error
			);

		}

	    return array(
	        'success'      => true,
	        'value'        => empty($errors) ? 1 : -1,
			'successCount' => count($pathArray) - count($errors),
	        'paths'        => $paths,
	        'additionalLines' => $additionalLines,
	        'errors'       => $errors
	    );
	}

	public function checkApacheMPMEnabled() {
		$serverConsoleBL = $this;
		$errors = array();

		$returnArray = array();
		if (
			($returnArray = $serverConsoleBL->getConfigurationValue(array('function' => 'apache_get_modules', 'parameter' => 'mpm_winnt'))) &&
			($returnArray['value'] == 'ON')
		) { } else if (
			($returnArray = $serverConsoleBL->getConfigurationValue(array('function' => 'apache_get_modules', 'parameter' => 'mpm_netware'))) &&
			($returnArray['value'] == 'ON')
		) { } else if (
			($returnArray = $serverConsoleBL->getConfigurationValue(array('function' => 'apache_get_modules', 'parameter' => 'mpm_prefork'))) &&
			($returnArray['value'] == 'ON')
		) { } else if (
			($returnArray = $serverConsoleBL->getConfigurationValue(array('function' => 'apache_get_modules', 'parameter' => 'mpm_worker'))) &&
			($returnArray['value'] == 'ON')
		) { } else if (
			($returnArray = $serverConsoleBL->getConfigurationValue(array('function' => 'apache_get_modules', 'parameter' => 'mpm_event'))) &&
			($returnArray['value'] == 'ON')
		) { }

	    return $returnArray;

	}

	public function checkPHPOpcacheEnabled() {
		$serverConsoleBL = $this;
		$errors = array();

		$returnArray = array();
		if (
			($returnArray = $serverConsoleBL->getConfigurationValue(array('function' => 'ini_get', 'parameter' => 'opcache.enable'))) &&
			($returnArray['value'] == '1')
		) { } else if (
			($returnArray = $serverConsoleBL->getConfigurationValue(array('function' => 'ini_get', 'parameter' => 'apc.enabled'))) &&
			($returnArray['value'] == '1')
		) { }

	    return $returnArray;
	}

	public function getConfigurationValue($input) {
		$modStringsBL = new ModStringsBL;

		$function  = isset($input['function'])  ? trim($input['function'])  : '';
		$parameter = isset($input['parameter']) ? trim($input['parameter']) : '';

		$value = '-1';
		if (($function === 'extension_loaded') && function_exists('extension_loaded')) {

			$value = extension_loaded($parameter) ? 'ON' : 'OFF';

		} else if (($function === 'get_cfg_var') && function_exists('get_cfg_var')) {

			$value = get_cfg_var($parameter);
			if (!$value || (strtolower($value) === 'off')) { $value = 'OFF'; }
			if (strtolower($value) === 'on') { $value = 'ON'; }

		} else if (($function === 'ini_get') && function_exists('ini_get')) {

			$value = ini_get($parameter);

		} else if (($function === 'apache_get_modules') && function_exists('apache_get_modules')) {

			$value = in_array($parameter, apache_get_modules()) ? 'ON' : 'OFF';

		}  else if (($function === 'class_exists') && function_exists('class_exists')) {

			$value = class_exists($parameter) ? 'ON' : 'OFF';

		} else if ($function === 'function_exists') {

			$value = function_exists($parameter) ? 'ON' : 'OFF';

		} else if (($function === 'custom_check_write_access')) {

			return $this->custom_check_write_access();

		}

		// if ($parameter === 'ZipArchive') {
		// 	$value = 'OFF';
		// }

		$returnArray = array(
	        'success' => true,
	        'value' => $value
	    );

		$returnArray = array_merge($returnArray, $modStringsBL->get(array('mapKey' => $parameter)));

	    return $returnArray;
	}

	public function returnPHPIni() {
		header('Expires: 0');
	    header('Cache-control: private');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-disposition: attachment; filename="php.ini"');

    	$file = ';;;;;;;;;;;;;;;;;;;;;;;;
; Generated ini ;
;;;;;;;;;;;;;;;;;;;;;;;;';
		if (function_exists('php_ini_loaded_file')) {
	    	$path = php_ini_loaded_file();
	    	$file .= '
php_ini_loaded_file: "'.$path.'"
;;;;;;;;;;;;;;;;;;;;;;;;

';
			try {
				if (!file_exists($path)) {
	        		$file .= '
Warning: Could not access file at "'.$path.'"
';
				} else {
					$file .= file_get_contents($path); 
				}
			} catch(Exception $e) {    
	        	$file .= '
Exception: '.$e->getCode().': '.$e->getMessage().'
';
			}
		} else {
	        	$file .= '
Function Not Found: php_ini_loaded_file()
';
		}

    	return $file;
	}

	public function returnPHPInfo() {
	    header('Expires: 0');
	    header('Cache-control: private');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-disposition: attachment; filename="phpinfo.html"');

	    phpinfo();
	}

	public function returnPHPErrorLog() {
	    header('Expires: 0');
	    header('Cache-control: private');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Content-Description: File Transfer');
	    header('Content-Type: text/csv');
	    header('Content-disposition: attachment; filename="phperrors.log"');

	    $log_errors = ini_get('log_errors');
	    $file = '';
	    if ($log_errors) {
	        $error_log = ini_get('error_log');
	        error_log('Server Check -> Request Error Log');
	        if (file_exists($error_log) && ($file = file_get_contents($error_log))) {

	        } else {
	            $file = '
Warning: Could not access file at "'.$error_log.'"
';
	        }
	    } else {
	            $file = '
Warning: Could not find "log_errors"
';
	    }
	    return $file;
	}

	private function configure_dbh($db_host, $db_name, $db_user, $db_pass) {
		$dbh   = null;
		$error = '';

		if (empty($db_name)) { 
			$con_db_name = '';
		} else {
			$con_db_name = ';dbname='.$db_name;
		}

        try {
            if ($dbh = new PDO('mysql:host='.$db_host.$con_db_name, $db_user, $db_pass)) {
                $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
                $dbh->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
                $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            }
        }
        catch (PDOException $e) {
        	$error = "[" . (int)$e->getCode() . "] " . $e->getMessage();
        }

        return array(
        	'success' => $dbh !== null ? true : false,
        	'dbh' 	  => $dbh,
        	'error'   => $error
    	);
    }

	private function convertIniFileToArray($path) {
		$iniArray = array();
		if (file_exists($path)) {
			if ($iniArray = parse_ini_file($path)) { }
		}
		return $iniArray;
	}

	private function determineMatchPercentage($val1, $val2) {

		if ($val1 === $val2) {
			return 100;
		}

		if ($val1 == '0' && (in_array(strtolower($val2), array('',null,'false')))) {
			return 100;
		} else if ($val2 == '0' && (in_array(strtolower($val1), array('',null,'false')))) {
			return 100;
		}

		if ($val1 == '-1' && (in_array(strtolower($val2), array('',null,'false')))) {
			return 100;
		} else if ($val2 == '-1' && (in_array(strtolower($val1), array('',null,'false')))) {
			return 100;
		}

		if ($val1 == '1' && (in_array(strtolower($val2), array('true')))) {
			return 100;
		} else if ($val2 == '1' && (in_array(strtolower($val1), array('true')))) {
			return 100;
		}

		if (is_numeric($val1) && is_numeric($val2)) {
			// ( | (V1 - V2) | / ((V1 + V2)/2) ) * 100
			// if ($val1 && $val2)
			$diff = ( abs($val1 - $val2) /  (($val1 + $val2)/2) ) * 100;
			if ($diff > 100) {
				$diff *= -1;
			}
			return $diff;
		}

		if ($val1 == $val2) {
			return 100;
		} else {
			return 0;
		}

	}

	public function checkMySQLVariables() {
		$DB_HOST = DB_HOST;
	    $DB_NAME = DB_NAME;
	    $DB_USER = DB_USER;
	    $DB_PASS = DB_PASS;

	    // Preset Priorities
	    // Define 'high' priority variables. Everything else will default to 'normal'
	    $variable_priorities = array(
	    	'high' => array(
				'character_set_database',
				'character_set_server',
				'character_set_filesystem',
				'character_set_system',
				'character_set_client',
				'character_set_results',
				'character_set_connection',
				'collation_server',
				'collation_database',
				'collation_connection',
				'sql_mode'
    		)
    	);

    	$dbhDetails = $this->configure_dbh(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    	if ($dbhDetails['success'] && ($dbh = $dbhDetails['dbh'])) {

    	} else {
	    	return array(
		        'success'   => false,
		        'records' => array(
		        	array(
		        		'priority' => 'high',
		        		'title'    => 'Connection Details',
		        		'recValue' => "Database server's connection details. ".'mysql -h'.DB_HOST.' -u'.DB_USER.' -p'.DB_PASS,
		        		'value'    => $dbhDetails['error'],
		        		'tag'      => '<i class="fa fa-circle" style="color:red;" title="Failed"></i>'
	        		)
	        	),
		        'errors'    => array('Unable to connect')
    		);
    	}

	    $variables = array();

	    $sql = "SHOW VARIABLES;";
        $sth = $dbh->prepare($sql);
        if ($sth->execute() && ($rows = $sth->fetchAll())) {
        	foreach($rows as $row) {
        		$name = trim($row['Variable_name']);
        		$val  = trim($row['Value']);
        		$variables[$name] = array(
        			'value'    => $val,
        			'recValue' => null
    			);
        	}
        }

        if ($rows = $this->convertIniFileToArray('../config/production_mysql_variables.ini')) {
        	foreach ($rows as $key => $value) {
        		$name = trim($key);
        		$val  = trim($value);

        		if (isset($variables[$name])) {
        			$variables[$name]['recValue'] = $val;
        		} else {
	        		$variables[$name] = array(
	        			'value'     => null,
	        			'recValue'  => $val
	    			);
        		}
        	}
        }

        // Remove environmental specific variables
        $ignore_variables = explode(',', 'version,innodb_version,basedir,character_sets_dir,datadir,general_log_file,hostname,innodb_log_group_home_dir,lc_messages_dir,log_error,pid_file,plugin_dir,port,pseudo_thread_id,relay_log_info_file,shared_memory_base_name,shared_memory,server_id,slave_load_tmpdir,slow_query_log_file,socket,ssl_ca,ssl_cert,ssl_key,tmpdir,version_comment,version_compile_os,version_compile_machine,locked_in_memory,timestamp,named_pipe,ft_boolean_syntax,log_bin');                                          
        foreach ($ignore_variables as $ignore_variable) {
        	if (isset($variables[$ignore_variable])) {
        		unset($variables[$ignore_variable]);
        	}
        }

        foreach ($variables as $key => &$variable) {
        	$variable['title'] = $key;

        	$match = $this->determineMatchPercentage($variable['value'], $variable['recValue']);
    		$variable['match'] = $match;

    		if ($match < 50) {
    			$variable['tag'] = '<i class="fa fa-circle" style="color:red;" title="Failed"></i>';
    		} else if ($match < 100) {
    			$variable['tag'] = '<i class="fa fa-circle" style="color:orange;" title="Warning, below recommended value"></i>';
    		} else {
    			$variable['tag'] = '<i class="fa fa-circle" style="color:green;" title="Success"></i>';
    		}

    		// Assign Priority
    		if (in_array($key, $variable_priorities['high'])) {
    			$variable['priority'] = 'high';
    		} else {
    			$variable['priority'] = 'normal';
    		}
        }

		// Sort the data with priority ascending, match ascending, title ascending
        // Obtain a list of columns
        $variables = array_values($variables);
		foreach ($variables as $key => $row) {
		    $col_priority[$key] = $row['priority'];
		    $col_match[$key] = $row['match'];
		    $col_title[$key] = $row['title'];
		}
		// Do sorting
		array_multisort($col_priority, SORT_ASC, $col_match, SORT_ASC, $col_title, SORT_ASC, $variables);

        // MySQL version Check
		$var = array(
			'title'    => 'MySQL Version',
			'recValue' => '5.4',
			'priority' => 'high'
		);
		$value = $this->getMySQLVersion()['value'];
        $var['value'] = floatVal($value);
        $var['match'] = $this->determineMatchPercentage($var['value'], $var['recValue']);
        if ($var['value'] < $var['recValue']) {
			$var['tag'] = '<i class="fa fa-circle" style="color:red;" title="Failed"></i>';
        } else {
			$var['tag'] = '<i class="fa fa-circle" style="color:green;" title="Success"></i>';
        }
		array_unshift($variables, $var);
		// Get stats
		$total  = 0;
		$failed = 0;
		foreach($variables as $variable) {
			$total++;
			if ($variable['match'] < 100) {
				$failed++;
			}
		}
		$stats = ($total-$failed).'/'.$total.' | '.round(($total-$failed) / $total * 100, 2).'%';


        return array(
	        'success' => true,
	        'records' => $variables,
	        'stats'   => $stats,
	        'errors'  => array(),
	        'summary' => array(
	        	'total' => intval($total),
	        	'passed' => intval($total-$failed),
	        	'failed' => intval($failed),
	        	'percentage' => round(($total-$failed) / $total * 100, 2)
        	)
		);

	}


	public function checkPHPVariables() {
	    $variables = array();

	    // Preset Priorities
	    // Define 'high' priority variables. Everything else will default to 'normal'
	    $variable_priorities = array(
	    	'high' => array(
				'date.timezone',
				'short_open_tag ',
				'safe_mode',
				'safe_mode_exec_dir',
				'safe_mode_allowed_env_vars',
				'safe_mode_protected_env_vars',
				'disable_functions',
				'max_execution_time',
				'error_reporting',
				'variables_order',
				'register_globals',
				'magic_quotes_gpc',
				'magic_quotes_runtime',
				'magic_quotes_sybase',
				'file_uploads',
				'default_charset'
    		)
    	);

        if ($rows = $this->convertIniFileToArray('../config/production_php_variables.ini')) {
        	foreach ($rows as $key => $value) {
        		$name = trim($key);
        		$val  = trim($value);

        		$variables[$name] = array(
        			'value'    => null,
        			'recValue' => $val
    			);
        	}
        }

        if ($rows = ini_get_all(null, false)) {
        	foreach($rows as $key => $value) {
        		$name = trim($key);
        		$val  = trim($value);


        		if (isset($variables[$name])) {
        			$variables[$name]['value'] = $val;
        		}
        // 		else {
	       //  		$variables[$name] = array(
	       //  			'undefined' => true,
	       //  			'recValue'  => 'N/A',
	       //  			'value'     => $val
	    			// );
        // 		}
        	}
        }


        // Remove environmental specific variables
        $ignore_variables = explode(',', 'sendmail_from,always_populate_raw_post_data,pgsql.%,error_reporting,short_open_tag,htscanner.default_docroot,htscanner.config_file,htscanner.default_ttl,register_argc_argv,variables_order,phpd,mssql%,xbithack,default_socket,%_dir%,%_basedir%,error_log,mysql.default_port,mysql.default_socket,xsl.security_prefs,mbstring.http_output,%_path,session.entropy_file,mysqli.default_socket,disable_functions,pdo_mysql.default_socket,soap.%,exif.%,mailparse.%,iconv.%,engine');
        $pattern_ignore_variables = array(); // Generated from above list
        foreach ($ignore_variables as $ignore_variable) {

        	// Pattern
        	if (strpos($ignore_variable, '%') > -1) {
        		$ignore_variable = str_replace('%', '', $ignore_variable);

	        	foreach($variables as $key => $val) {
        			if (strpos($key, $ignore_variable) > -1) {
        				$pattern_ignore_variables[] = $key;
        				// unset($variables[$key]);
        			}
	        	}
	        	continue;
        	}
        	if (isset($variables[$ignore_variable])) {
        		unset($variables[$ignore_variable]);
        	}
        }

        if (count($pattern_ignore_variables)) {
        	foreach($pattern_ignore_variables as $key) {
        		unset($variables[$key]);
        	}
        }

        foreach ($variables as $key => &$variable) {
        	$variable['title'] = $key;

        	$match = $this->determineMatchPercentage($variable['value'], $variable['recValue']);
    		$variable['match'] = $match;

    		if ($match < 50) {
    			$variable['tag'] = '<i class="fa fa-circle" style="color:red;" title="Failed"></i>';
    		} else if ($match < 100) {
    			$variable['tag'] = '<i class="fa fa-circle" style="color:orange;" title="Warning, below recommended value"></i>';
    		} else {
    			$variable['tag'] = '<i class="fa fa-circle" style="color:green;" title="Success"></i>';
    		}

    		// Assign Priority
    		if (in_array($key, $variable_priorities['high'])) {
    			$variable['priority'] = 'high';
    		} else {
    			$variable['priority'] = 'normal';
    		}
        }

		// Sort the data with priority ascending, match ascending, title ascending
        // Obtain a list of columns
        $variables = array_values($variables);
		foreach ($variables as $key => $row) {
		    $col_priority[$key]  = $row['priority'];
		    $col_match[$key] = $row['match'];
		    $col_title[$key] = $row['title'];
		}
		// Do sorting
		array_multisort($col_priority, SORT_ASC, $col_match, SORT_ASC, $col_title, SORT_ASC, $variables);

		// Get stats
		$total  = 0;
		$failed = 0;
		foreach($variables as $variable) {
			$total++;
			if ($variable['match'] < 100) {
				$failed++;
			}
		}
		$stats = ($total-$failed).'/'.$total.' | '.round(($total-$failed) / $total * 100, 2).'%';

        return array(
	        'success' => true,
	        'records' => $variables,
	        'stats'   => $stats,
	        'errors'  => array(),
	        'summary' => array(
	        	'total' => intval($total),
	        	'passed' => intval($total-$failed),
	        	'failed' => intval($failed),
	        	'percentage' => round(($total-$failed) / $total * 100, 2)
        	)
		);

	}


	public function checkPreviousVersions() {
		$serverConsoleBL = $this;

		$files = scandir(WEB_ROOT_DIRECTORY);
		$versions = array();
		foreach ($files as $file) {
			if (is_dir(WEB_ROOT_DIRECTORY.'/'.$file)) {

				if (file_exists(WEB_ROOT_DIRECTORY.'/'.$file.'/config/onpremise.ini')) {


					$version     = $file;
					$variables   = parse_ini_file(WEB_ROOT_DIRECTORY.'/'.$file.'/config/onpremise.ini');
					$permissions = $serverConsoleBL->custom_check_write_access(WEB_ROOT_DIRECTORY.'/'.$file.'/');

					$variableArray = array();
					foreach ($variables as $key => $value) {
						$variableArray[] = array(
							'key' => $key,
							'value' => $value
						);
					}

					 
					if ( (count($variableArray) > 2) && (empty($permissions['errors'])) ) {
						$success = true;
					} else {
						$success = false;
					}

					$versions[] = array(
						'success'        => $success,
						'version'        => $version,
						'variables'      => $variableArray,
						'variablesCount' => count($variableArray),
						'permissions'    => $permissions
					);
				}

			}
		}

		// Check default version
		$default_version = '';
		$default_version_warning = '';
		$iniVariables    = array();
		if (file_exists(WEB_ROOT_DIRECTORY.'/application_default_version.ini')) {
			$iniVariables = parse_ini_file(WEB_ROOT_DIRECTORY.'/application_default_version.ini');

			$default_version = isset($iniVariables['VERSION']) ? trim($iniVariables['VERSION']) : '[NONE]';

		} else {
			$default_version_warning = '[No default version set]';

		}


        return array(
	        'success'                 => true,
	        'versions'                => $versions,
	        'WEB_ROOT_URL'            => WEB_ROOT_URL,
	        'WEB_ROOT_DIRECTORY'      => WEB_ROOT_DIRECTORY,
	        'VERSION'         		  => isset($iniVariables['VERSION']) 	  ? trim($iniVariables['VERSION']) 	    	    : '[NO VERSION DEFINED]',
	        'VERSION_DATE'         	  => isset($iniVariables['VERSION_DATE']) ? '('.trim($iniVariables['VERSION_DATE']).')' : '',
	        'iniVariables'            => $iniVariables,
	        'default_version_warning' => $default_version_warning,
	        'errors'                  => array()
		);
	}


	/*
		The default version is used in the following locations
			/var/www/html/index.php 	- drives root access
			/var/www/html/va/index.php 	- drives root va access
	*/

	public function saveDefaultVersion($input) {

		$version = isset($input['version'])  ? trim($input['version'])  : '';
		$errors  = array();

		$indexFile = '<?php
$iniVariables = array(); 
if (file_exists("application_default_version.ini")) {
	$iniVariables = parse_ini_file("application_default_version.ini");

	if (isset($iniVariables["VERSION"])) {
		header("Location: ".trim($iniVariables["VERSION"])."/");
	}
}
';		
		$VaIndexFile = '<?php
$iniVariables = array(); 
if (file_exists("../application_default_version.ini")) {
	$iniVariables = parse_ini_file("../application_default_version.ini");

	if (isset($iniVariables["VERSION"])) {
		$actual_link = $URL_PROTOCOL."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$actual_link = str_replace("/va/", "/".$iniVariables["VERSION"]."/va/", $actual_link);
		header("Location: ".$actual_link);
	}
}
';		
		$VaHtaccess = '
Options -Indexes

RewriteEngine On

# Some hosts may require you to use the `RewriteBase` directive.
# If you need to use the `RewriteBase` directive, it should be the
# absolute physical path to the directory that contains this htaccess file.
#
# RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
';
		$application_default_version = '
VERSION="'.$version.'"
VERSION_DATE="'.date('Y-m-d H:i:s').'"
';
		// Create/Update root index.php - used to redirect to version
		/* 
		- if index.php exists
		-- if index.php contains 'application_default_version.ini'
		--- ALL GOOD
		-- else if allowed to write to file
		--- Back up original
		--- Overwrite index.php
		-- else
		--- ERROR: NOT ALLOWED TO OVERWRITE index.php
		- else if allowed to write to root
		-- Create the file
		- else 
		-- ERROR: NOT ALLOWED TO CREATE FILE IN ROOT
		*/
		if (file_exists(WEB_ROOT_DIRECTORY.'/index.php')) {

			if (strpos(file_get_contents(WEB_ROOT_DIRECTORY.'/index.php'), 'application_default_version.ini') !== false) {

			} else if (is_writable(WEB_ROOT_DIRECTORY.'/index.php')){

				if (copy(WEB_ROOT_DIRECTORY.'/index.php', WEB_ROOT_DIRECTORY.'/index-BCK-'.date('Ymd_His').'.php')) {

					if (file_put_contents(WEB_ROOT_DIRECTORY.'/index.php', $indexFile)) { } else {
						$errors[] = 'Could not overwrite file "'.WEB_ROOT_DIRECTORY.'/index.php'.'"';
					}

				} else {
					$errors[] = 'Could not backup existing file "'.WEB_ROOT_DIRECTORY.'/index.php'.'"';
				}

			} else {
				$errors[] = 'Not allowed to overwrite existing file "'.WEB_ROOT_DIRECTORY.'/index.php'.'"';
			}	

		} else {

			if (is_writable(WEB_ROOT_DIRECTORY)) {

				if (file_put_contents(WEB_ROOT_DIRECTORY.'/index.php', $indexFile)) { } else {
					$errors[] = 'Could not create file "'.WEB_ROOT_DIRECTORY.'/index.php'.'"';
				}

			} else {
				$errors[] = 'Not allowed to create file "'.WEB_ROOT_DIRECTORY.'/index.php'.'"';
			}

		}

		if (file_exists(WEB_ROOT_DIRECTORY.'/va') && file_exists(WEB_ROOT_DIRECTORY.'/va/index.php') && file_exists(WEB_ROOT_DIRECTORY.'/va/.htaccess')) {

			if (strpos(file_get_contents(WEB_ROOT_DIRECTORY.'/va/index.php'), 'application_default_version.ini') !== false) {

			} else if (is_writable(WEB_ROOT_DIRECTORY.'/va/index.php')){

				if (copy(WEB_ROOT_DIRECTORY.'/va/index.php', WEB_ROOT_DIRECTORY.'/va/index-BCK-'.date('Ymd_His').'.php')) {

					if (file_put_contents(WEB_ROOT_DIRECTORY.'/va/index.php', $indexFile)) { } else {
						$errors[] = 'Could not overwrite file "'.WEB_ROOT_DIRECTORY.'/va/index.php'.'"';
					}

				} else {
					$errors[] = 'Could not backup existing file "'.WEB_ROOT_DIRECTORY.'/va/index.php'.'"';
				}

			} else {
				$errors[] = 'Not allowed to overwrite existing file "'.WEB_ROOT_DIRECTORY.'/va/index.php'.'"';
			}	

		} else {

			if (is_writable(WEB_ROOT_DIRECTORY)) {

				if (!file_exists(WEB_ROOT_DIRECTORY.'/va')) {
					if (mkdir(WEB_ROOT_DIRECTORY.'/va', 0755)) { } else {
						$errors[] = 'Could not create directory "'.WEB_ROOT_DIRECTORY.'/va'.'"';
					}
				} 


				if (file_exists(WEB_ROOT_DIRECTORY.'/va')) {
					if (file_put_contents(WEB_ROOT_DIRECTORY.'/va/index.php', $VaIndexFile)) { } else {
						$errors[] = 'Could not create file "'.WEB_ROOT_DIRECTORY.'/va/index.php'.'"';
					}
					if (file_put_contents(WEB_ROOT_DIRECTORY.'/va/.htaccess', $VaHtaccess)) { } else {
						$errors[] = 'Could not create file "'.WEB_ROOT_DIRECTORY.'/va/index.php'.'"';
					}
				}

			} else {
				$errors[] = 'Not allowed to create file "'.WEB_ROOT_DIRECTORY.'/va/index.php'.'"';
			}

		}


		// Create/Update root application_default_version.ini - used to define default version
		/* 
		- if application_default_version.ini exists
		-- if allowed to write to file
		--- append version & date to application_default_version.ini
		-- else
		--- ERROR: NOT ALLOWED TO OVERWRITE application_default_version.ini
		- else if allowed to write to root
		-- Create the file application_default_version.ini
		--- append version & date to application_default_version.ini
		- else 
		-- ERROR: NOT ALLOWED TO CREATE FILE IN ROOT
		*/

		// INI - Update existing file
		if (file_exists(WEB_ROOT_DIRECTORY.'/application_default_version.ini')) {

			if (is_writable(WEB_ROOT_DIRECTORY.'/application_default_version.ini')) {

				if (file_put_contents(WEB_ROOT_DIRECTORY.'/application_default_version.ini', $application_default_version, FILE_APPEND)) { } else {
					$errors[] = 'Could not overwrite file "'.WEB_ROOT_DIRECTORY.'/application_default_version.ini'.'"';
				}

			} else {
				$errors[] = 'Could not overwrite file "'.WEB_ROOT_DIRECTORY.'/application_default_version.ini'.'"';
			}

		} else {

			if (is_writable(WEB_ROOT_DIRECTORY)) {

				if (file_put_contents(WEB_ROOT_DIRECTORY.'/application_default_version.ini', $application_default_version)) { } else {
					$errors[] = 'Could not create file "'.WEB_ROOT_DIRECTORY.'/application_default_version.ini'.'"';
				}

			} else {
				$errors[] = 'Not allowed to create file "'.WEB_ROOT_DIRECTORY.'/application_default_version.ini'.'"';
			}

		}

		return array(
			'success' => empty($errors),
			'errors'  => $errors
		);
	}

	public function performanceTestApache() {

		// $output = shell_exec('ab -k -n 50000 -c 2 http://localhost/snkpage.html'); 
		$output = shell_exec('ipconfig'); 
        // preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version); 
        // $value = $version[0];

		// sleep(1);

		// $phpBenchmarkBL = new PhpBenchmarkBL();

		// $phpBenchmark = $phpBenchmarkBL->run(false);

		return array(
			'input'        => 'ab -k -n 50000 -c 2 '.WEB_ROOT_URL.'/snkpage.html',
			'phpBenchmark' => $phpBenchmark,
			'output'       => $output
		);

	}

	public function performanceTestPHPMySQL() {
		$errors = array();

	    $timeStart = microtime(true);
	    $timeTaken = -1;

	    if (!empty(DB_HOST)) {
	        $mysql = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	 
	        if (mysqli_connect_errno()) {
	            $errors[] = 'Could not connect to db';
	        } else {

	        	$timeTaken = round(microtime(true) - $timeStart,3);
	            mysqli_close($mysql);
	        }

	    } else {
            $errors[] = 'Could not connect to db';
	    }

	    return array(
	    	'errors'        => $errors,
	    	'success'       => empty($errors),
	    	'expected_time' => 0.5,
	    	'timeTaken'     => $timeTaken
    	);

	}

	public function performanceTestPHP() {

		$result = PhpBenchmark::run();

		$tests = array(
			'conditions' => array(
				'benchmark' => 0.4,
				'value'    => $result['conditions'],
				'result'    => true
			),
			'loops' => array(
				'benchmark' => 0.5,
				'value'    => $result['loops'],
				'result'    => true
			),
			'math' => array(
				'benchmark' => 1.5,
				'value'    => $result['math'],
				'result'    => true
			),
			'string' => array(
				'benchmark' => 3,
				'value'    => $result['string'],
				'result'    => true
			),
			'total' => array(
				'benchmark' => 5.4,
				'value'    => $result['total'],
				'result'    => true
			),
		);

		foreach ($tests as &$test) {
			if ($test['value'] > $test['benchmark']) {
				$test['result'] = false;
			}
		}

		return $tests;
	}

	public function testTimePhpMysqlSync() {
		$errors = array();

	    // $value   = -1;
	    $success = false;

	    $time_php   = '';
	    $time_mysql = '';
	    $time_start = '';
	    $time_taken = '';


    	$dbhDetails = $this->configure_dbh(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    	if ($dbhDetails['success'] && ($dbh = $dbhDetails['dbh'])) {
			$time_php   = date('Y-m-d H:i:s');

		    $time_start = microtime(true);
		    $time_taken = 0;
		    $sql = "SELECT NOW() AS 'time_mysql';";
	        $sth = $dbh->prepare($sql);
	        if ($sth->execute() && ($row = $sth->fetch())) {
				$time_taken = microtime(true) - $time_start;
	        	$time_mysql = $row['time_mysql'];
	        }

	        if (substr($time_mysql, 0, -1) === substr($time_mysql, 0, -1)) {
	        	$success = true;
	        } else {
	        	$errors[] = 'MySQL time ('.$time_mysql.') does not match PHP time ('.$time_php.')';
	        	
	        }

	    } else {

	    	$errors[] = 'Unable to connect';

	    }

	    if ($success) {
	    	$value = 'MATCHING';
	    } else {
	    	$value = empty($errors) ? 'NOT MATCHING' : strtoupper($errors[0]) ;
	    }

	   	// if ()

	    return array(
	    	'value'      => $value,
	    	'success'    => true
	  //   	,
	  //   	'errors'     => $errors,
	  //   	'time_php'   => $time_php,
			// 'time_mysql' => $time_mysql,
			// 'time_start' => $time_start,
			// 'time_taken' => round($time_taken, 4) 
    	);

	}

	public function toolGetDatabasesAndDetails($input) {

		$DB_HOST = DB_HOST;
	    $DB_NAME = DB_NAME;
	    $DB_USER = DB_USER;
	    $DB_PASS = DB_PASS;

	    $invalid_databases = array(
	    	'test',
	    	'information_schema',
	    	'performance_schema',
	    	'mysql'
    	);

    	$databases = array();


    	$dbhDetails = $this->configure_dbh($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    	if ($dbhDetails['success'] && ($dbh = $dbhDetails['dbh'])) {

	    	// GET ALL DATABASES
		    $sql = "SHOW DATABASES;";
	        $sth = $dbh->prepare($sql);
	        if ($sth->execute() && ($rows = $sth->fetchAll())) {
	        	foreach ($rows as $row) {
	        		if (!in_array($row['Database'], $invalid_databases)) {
	        			$databases[$row['Database']] = array('DB_NAME' => $row['Database']);
	        		}
	        	}
	        }

	    	// GET ALL PROCEDURES
		    $sql = "SHOW PROCEDURE STATUS;";
	        $sth = $dbh->prepare($sql);
	        if ($sth->execute() && ($rows = $sth->fetchAll())) {
	        	foreach ($rows as $row) {
	        		$DB_NAME = $row['Db'];
	        		if (isset($databases[$DB_NAME])) {

	        			if (!isset($databases[$DB_NAME]['PROCEDURE'])) {
	        				$databases[$DB_NAME]['PROCEDURE'] = array();
	        			}

	        			$databases[$DB_NAME]['PROCEDURE'][] = $row;
	        		}
	        	}
	        }

	    	// GET ALL FUNCTIONS
		    $sql = "SHOW FUNCTION STATUS;";
	        $sth = $dbh->prepare($sql);
	        if ($sth->execute() && ($rows = $sth->fetchAll())) {
	        	foreach ($rows as $row) {
	        		$DB_NAME = $row['Db'];
	        		if (isset($databases[$DB_NAME])) {

	        			if (!isset($databases[$DB_NAME]['FUNCTION'])) {
	        				$databases[$DB_NAME]['FUNCTION'] = array();
	        			}

	        			$databases[$DB_NAME]['FUNCTION'][] = $row;
	        		}
	        	}
	        }

        	// GET ALL TRIGGERS FOR EACH DATABASE
	        foreach ($databases as $DB_NAME => $database) {
        		$dbh->beginTransaction();
        		$dbh->exec('USE '.$DB_NAME.';');
			    $sql = "SHOW TRIGGERS;";
		        $sth = $dbh->prepare($sql);
		        if ($sth->execute() && ($rows = $sth->fetchAll())) {
		        	foreach ($rows as $row) {
		        		if (isset($databases[$DB_NAME])) {

		        			if (!isset($databases[$DB_NAME]['TRIGGER'])) {
		        				$databases[$DB_NAME]['TRIGGER'] = array();
		        			}

		        			$databases[$DB_NAME]['TRIGGER'][] = $row;
		        		}
		        	}
		        }
		        $dbh->commit();
	        }


        	// GET ALL TABLES FOR EACH DATABASE
	        foreach ($databases as $DB_NAME => $database) {
        		$dbh->beginTransaction();
        		$dbh->exec('USE '.$DB_NAME.';');
			    $sql = "SHOW FULL TABLES;";
		        $sth = $dbh->prepare($sql);
		        if ($sth->execute() && ($rows = $sth->fetchAll())) {
		        	foreach ($rows as $row) {
		        		if (isset($databases[$DB_NAME])) {

		        			if (!isset($databases[$DB_NAME]['TABLE'])) {
		        				$databases[$DB_NAME]['TABLE'] = array();
		        			}

		        			$databases[$DB_NAME]['TABLE'][] = $row;
		        		}
		        	}
		        }
		        $dbh->commit();
	        }


        	// GET ALL INDEXES
    		$dbh->beginTransaction();
    		$dbh->exec('use information_schema;');
		    $sql = "SELECT * FROM statistics;";
	        $sth = $dbh->prepare($sql);
	        if ($sth->execute() && ($rows = $sth->fetchAll())) {
	        	foreach ($rows as $row) {
	        		$DB_NAME = $row['TABLE_SCHEMA'];
	        		if (isset($databases[$DB_NAME])) {

	        			if (!isset($databases[$DB_NAME]['INDEX'])) {
	        				$databases[$DB_NAME]['INDEX'] = array();
	        			}

	        			$databases[$DB_NAME]['INDEX'][] = $row;
	        		}
	        	}
	        }
	        $dbh->commit();

	    } else {
	    	$errors[] = 'Unable to connect';
	    }

	    // Prepare to create output
	    $path     = '../../../backend/file_uploads';
		$uq       = 'db_healthcheck_'.date('Ymd_His').'_'.rand();
	    $rootPath = $path.'/'.$uq;

    	if (
    		file_exists($path) && 
    		is_writable($path) && 
    		class_exists('ZipArchive') &&
    		class_exists('RecursiveIteratorIterator') &&
    		class_exists('RecursiveDirectoryIterator')
		) {
    		// Able to create files and put them in a zip file for downloading

    		if (mkdir($rootPath)) {
	    		foreach ($databases as $DB_NAME => $database)  {
	    			mkdir($rootPath.'/'.strtolower($DB_NAME));
	    			foreach ($database as $key => $rows) {
	    				if (is_array($rows) && count($rows)) {
	    					$exportfile = $rootPath.'/'.strtolower($DB_NAME).'/'.strtolower($DB_NAME).'_'.strtolower($key).'.csv';
							if (!file_exists($exportfile)) {
					            file_put_contents($exportfile, '"'.strip_tags(implode('","', array_keys($rows[0]))).'"'."\n", FILE_APPEND | LOCK_EX);
					        }
							foreach ($rows as $row) {
					            file_put_contents($exportfile, '"'.strip_tags(implode('","', array_values($row))).'"'."\n", FILE_APPEND | LOCK_EX);
							}
	    				}
	    			}
	    		}
    		}

		    $the_folder = $rootPath;
			$zip_file_name = $path.'/'.$uq.'.zip';

			$za = new FlxZipArchive;
			$res = $za->open($zip_file_name, ZipArchive::CREATE);
			if ($res === TRUE) {
			    $za->addDir($the_folder, basename($the_folder));
			    $za->close();
			}

			function rmdir_recursive($dir) {
			    foreach(scandir($dir) as $file) {
			        if ('.' === $file || '..' === $file) continue;
			        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
			        else unlink("$dir/$file");
			    }
				try {
			    	rmdir($dir);
			    } catch(Exception $e) { }
			}

			if (file_exists($the_folder)) {
				rmdir_recursive($the_folder);
			}

			return array(
				'output' => 'zip',
				'filename' => $uq.'.zip'
			);


    	} else if (file_exists($path) && is_writable($path)) {
    		// Only allowed to create one big file with everything
			$exportfile = $path.'/'.$uq.'.csv';
            file_put_contents($exportfile, "DATABASES:\n", FILE_APPEND | LOCK_EX);
            file_put_contents($exportfile, '"'.strip_tags(implode('","', array_keys($databases))).'"'."\n", FILE_APPEND | LOCK_EX);
            file_put_contents($exportfile, "\n\n", FILE_APPEND | LOCK_EX);
    		foreach ($databases as $DB_NAME => $database)  {
    			file_put_contents($exportfile, "\nDATABASE '".$DB_NAME."'\n", FILE_APPEND | LOCK_EX);
    			foreach ($database as $key => $rows) {
    				if (is_array($rows) && count($rows)) {
    					file_put_contents($exportfile, "\nDATABASE '".$DB_NAME."'->'".$key."\n", FILE_APPEND | LOCK_EX);
			            file_put_contents($exportfile, '"'.strip_tags(implode('","', array_keys($rows[0]))).'"'."\n", FILE_APPEND | LOCK_EX);
						foreach ($rows as $row) {
				            file_put_contents($exportfile, '"'.strip_tags(implode('","', array_values($row))).'"'."\n", FILE_APPEND | LOCK_EX);
						}
           
    				}
    			}
    		}
    	}

		return array(
			'output' => 'csv',
			'filename' => $uq.'.csv'
		);
	}

}
