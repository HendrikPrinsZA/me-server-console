# me-server-console
Web Server Console Application

### Overview
Some applications require specific modules/configurations in order to run successfully. This application allows you to inspect the current configuration of the web server and determine if those standards are met.

### Installation
Simply dump the files in this repo on your web server.
```sh
cd /var/www/html
git clone https://github.com/HendrikPrinsZA/me-server-console.git
```

### Configuration
Specify the requirements and preferred configurations

**Default configuration**
```json
{
	"configuration": {
		"version": "2.08.01"
	},

	"requirements": [
		{
			"type": "base",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP",
				"description": "",
				"additionalLines": []
			},
			"refresh": { "url": "checkifphpexists.php", "data": {} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": [],
			"initialChecksFailedInformation": [
				"It is essential that PHP is installed and configured correctly for this application to run. Check your server's configuration before you continue."
			]
		},
	    {
			"type": "base",
			"required": true,
			"display": {
				"logo": "apache-logo.png",
				"title": "Apache Module - mod_rewrite",
				"description": "Provides a rule-based rewriting engine to rewrite requested URLs on the fly",
				"additionalLines": []
			},
			"refresh": { "url": "mod_rewrite_test/", "data": {} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": [],
			"initialChecksFailedInformation": [
				"It is essential that mod_rewrite is enabled and configured correctly for this application to run. Check your server's configuration before you continue."
			]
	    },
	    {
			"type": "base",
			"required": true,
			"display": {
				"logo": "authentication-logo.png",
				"title": "Authentication - Setup",
				"description": "This application requires password protection to protect against unauthorized access",
				"additionalLines": []
			},
			"refresh": { "url": "api/authentication/permission", "data": {} },
			"values": { "server": "", "minimum": "Read and write", "recommended": "" },
			"actions": [],
			"initialChecksFailedInformation": [
				"Ensure the application has read and write permission of the config directory."
			]
	    },

		{
			"type": "core",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP Version",
				"description": "",
				"additionalLines": []
			},
			"refresh": { "url": "api/php", "data": {} },
			"values": { "server": "", "minimum": "5.4", "recommended": "5.6" },
			"actions": [
				{
					"type": "open",
	                "title": "Download PHP ini",
	                "endpoint": "/action/phpini"
				},
				{
					"type": "open",
	                "title": "Download PHP Info Page",
	                "endpoint": "/action/phpinfo"
				},
				{
					"type": "open",
	                "title": "Download PHP Error Log",
	                "endpoint": "/action/phperror"
				}
			]
		}
		,{
			"type": "core",
			"required": true,
			"display": {
				"logo": "ioncube-logo.png",
				"title": "ionCube",
				"description": "",
				"additionalLines": []
			},
			"refresh": { "url": "api/ioncube", "data": {} },
			"values": { "server": "", "minimum": "5.0", "recommended": "" },
			"actions": [
				{
					"type": "open",
	                "title": "Download & Install ionCube Loader",
	                "endpoint": "https://www.ioncube.com/loaders.php"
				}
			],
			"initialChecksFailedInformation": [
				"The ionCube loader is marked as an essential requirement to run the application. You can skip and continue as this application does not require it."
			]
		},
		{
			"type": "core",
			"required": true,
			"display": {
				"logo": "ioncube-logo.png",
				"title": "MySQL Version",
				"description": "",
				"additionalLines": []
			},
			"refresh": { "url": "api/mysql", "data": {} },
			"values": { "server": "", "minimum": "5.0", "recommended": "" },
			"actions": [
				{
					"type": "open",
	                "title": "Download & Install ionCube Loader",
	                "endpoint": "https://www.ioncube.com/loaders.php"
				}
			],
			"initialChecksFailedInformation": [
				"The ionCube loader is marked as an essential requirement to run the application. You can skip and continue as this application does not require it."
			]
		}





	   ,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP - xml",
				"description": "Provides fast, non-cached, forward-only access to xml data under PHP 5",
				"additionalLines": [
					"<a href='http://gistpages.com/posts/php_fatal_error_class_domdocument_not_found' target='_blank'>Install php-xml</a>"
				]
			},
			"refresh": { "url": "api/configuration", "data": {"function": "extension_loaded", "parameter": "xml"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": []
	    }

	   ,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP - pdo",
				"description": "The PHP Data Objects (PDO) extension defines a lightweight, consistent interface for accessing databases in PHP.",
				"additionalLines": [
					"<a href='http://php.net/manual/en/pdo.installation.php' target='_blank'>Installing PDO</a>"
				]
			},
			"refresh": { "url": "api/configuration", "data": {"function": "extension_loaded", "parameter": "pdo"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": []
	    }

	   ,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP - dom",
				"description": "The DOM extension allows you to operate on XML documents through the DOM API with PHP 5.",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "extension_loaded", "parameter": "dom"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": []
	    }

	   ,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP - curl",
				"description": "Allows to connect and communicate to many different types of servers with many different types of protocols.",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "extension_loaded", "parameter": "curl"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": []
	    }

	   ,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP - fileinfo",
				"description": "The functions in this module try to guess the content type and encoding of a file by looking for certain magic byte sequences at specific positions within the file.",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "extension_loaded", "parameter": "fileinfo"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": []
	    }
	    
	   ,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-mysql-logo.png",
				"title": "PHP - mysql",
				"description": "PHP offers several MySQL drivers and plugins for accessing and handling MySQL.",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "extension_loaded", "parameter": "mysql"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": []
	    }

		,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP - MB Strings Module",
				"description": "MB Strings Module is a non-default extension. This means it is not enabled by default. You must explicitly enable the module with the configure option.",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "function_exists", "parameter": "mb_strlen"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": [
				{
					"type": "open",
	                "title": "PHP Installation - mbstring",
	                "endpoint": "http://php.net/manual/en/mbstring.installation.php"
				}
			]
	    },
	    {
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP - ZipArchive",
				"description": "",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "class_exists", "parameter": "ZipArchive"} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": []
	    }
	    ,{
			"type": "phpExtension",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP Opcode Caching",
				"description": "OpCode Caches are a performance enhancing extension for PHP. They do this by injecting themselves into the execution life-cycle of PHP and caching the results of the compilation phase for later reuse. It is not uncommon to see a 3x performance increase just by enabling an OpCode cache.",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "ini_get", "parameter": "opcache.enable"} },
			"values": { "server": "", "minimum": "0", "recommended": "" },
			"actions": [
				{
					"type": "open",
	                "title": "Everything You Need To Know About OpCode Caches",
	                "endpoint": "https://support.cloud.engineyard.com/hc/en-us/articles/205411888-PHP-Performance-I-Everything-You-Need-to-Know-About-OpCode-Caches"
				}
			]
	    }



	   ,{
			"type": "phpVariable",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP Variable - display_errors",
				"description": "This determines whether errors should be printed to the screen as part of the output or if they should be hidden from the user.",
				"additionalLines": []
			},
			"refresh": { "url": "api/configuration", "data": {"function": "get_cfg_var", "parameter": "display_errors"} },
			"values": { "server": "", "minimum": "OFF", "recommended": "" },
			"actions": []
	    }

	   ,{
			"type": "phpVariable",
			"required": true,
			"display": {
				"logo": "php-mysql-logo.png",
				"title": "PHP & MySQL Timezones",
				"description": "PHP & MySQL should be configured to have the same timezones",
				"additionalLines": []
			},
			"refresh": { "url": "api/phptimevsmysqltime", "data": {} },
			"values": { "server": "", "minimum": "MATCHING", "recommended": "" },
			"actions": [
				{
					"type": "open",
	                "title": "Setting the Timezone for PHP in the php.ini file",
	                "endpoint": "http://www.inmotionhosting.com/support/website/php/setting-the-timezone-for-php-in-the-phpini-file"
				},
				{
					"type": "open",
	                "title": "Server System Variables - sysvar_time_zone",
	                "endpoint": "http://dev.mysql.com/doc/refman/5.5/en/server-system-variables.html#sysvar_time_zone"
				}
			],
			"initialChecksFailedInformation": [
				"It is critical that PHP and MySQL are both configured to use the same timezone. You can skip and confirm the connection details in the settings."
			]
	    }

		,{
			"type": "base",
			"required": true,
			"display": {
				"logo": "php-logo.png",
				"title": "PHP",
				"description": "",
				"additionalLines": []
			},
			"refresh": { "url": "checkifphpexists.php", "data": {} },
			"values": { "server": "", "minimum": "ON", "recommended": "" },
			"actions": [],
			"initialChecksFailedInformation": [
				"It is essential that PHP is installed and configured correctly for this application to run. Check your server's configuration before you continue."
			]
		}

	]
}
```