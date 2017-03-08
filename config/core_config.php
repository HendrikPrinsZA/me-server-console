<?php
if (!isset($_SESSION)) { session_start(); }
ini_set('display_errors', '1');     # don't show any errors...
error_reporting(E_ALL | E_STRICT);  # ...but do log them

$developer_config = __DIR__.'/developer_config.php';
if (file_exists($developer_config)) {
	require_once $developer_config;	
}

defined('CURRENT_PATH')               or define('CURRENT_PATH',               str_replace('\\', '/', realpath('./')));
defined('ROOT_PATH')                  or define('ROOT_PATH',                  str_replace('\\', '/', realpath(__DIR__.'/..')));
defined('URL_PROTOCOL')               or define('URL_PROTOCOL',               @($_SERVER['HTTPS'] != 'on' ) ? 'http' : 'https');
defined('ROOT_URL')                   or define('ROOT_URL',                   str_replace(rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/', URL_PROTOCOL.'://'.rtrim($_SERVER['HTTP_HOST'], '/').'/', ROOT_PATH)); 
defined('ROOTPATH')	          		  or define('ROOTPATH', 			  	  ROOT_URL.'/');
defined('URL_ROOT')                   or define('URL_ROOT',                   ROOT_URL);
defined('FILE_ROOT')                  or define('FILE_ROOT',                  realpath(__DIR__.'/../..'));

defined('CONFIG_DIRECTORY_PATH')      or define('CONFIG_DIRECTORY_PATH',      realpath(CURRENT_PATH.'/../config'));
defined('AUTH_CONFIG_PATH')           or define('AUTH_CONFIG_PATH',           realpath(CONFIG_DIRECTORY_PATH.'/auth_config.ini'));


if (file_exists(AUTH_CONFIG_PATH)) {

    $auth_ini_array = parse_ini_file(AUTH_CONFIG_PATH);

    if (isset($auth_ini_array['FIRSTNAME']))    { defined('FIRSTNAME')    or define('FIRSTNAME',    $auth_ini_array['FIRSTNAME']);    }
    if (isset($auth_ini_array['LASTNAME']))     { defined('LASTNAME')     or define('LASTNAME',     $auth_ini_array['LASTNAME']);     }
    if (isset($auth_ini_array['EMAILADDRESS'])) { defined('EMAILADDRESS') or define('EMAILADDRESS', $auth_ini_array['EMAILADDRESS']); }
   
}
