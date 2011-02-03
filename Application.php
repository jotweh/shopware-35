<?php
include(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

return array(
	'db' => array(
		'username' => $DB_USER,
		'password' => $DB_PASSWORD,
		'dbname' => $DB_DATABASE,
		'host' => $DB_HOST
	),
	'front' => array(
		'noErrorHandler' => false,
		'throwExceptions' => false,
		'useDefaultControllerAlways' => true,
		'disableOutputBuffering' => false,
		'showException' => true,
	),
	'template' => array(
		'compileCheck' => true,
		'compileLocking' => true,
		'useSubDirs' => false,
		'forceCompile' => false,
		'ignoreNamespace' => false
	),
	'cache' => array(
		'frontendOptions' => array(
    		'automatic_serialization' => true,
    		'automatic_cleaning_factor' => 0,
    		'lifetime' => 3600
    	),
    	'backend' => 'File',
    	'backendOptions' => array(
			'hashed_directory_umask' => 0771,
			'cache_file_umask' => 0644,
			'hashed_directory_level' => 0,
			'cache_dir' => $this->DocPath().'cache/database',
			'file_name_prefix' => 'shopware'
    	),
    	/*
    	'backend' => 'Memcached',
    	'backendOptions' => array(
			'servers' => array(
				array(
					'host' => 'localhost',
					'port' => 11211,
					'persistent' => true,
					'weight' => 1,
					'timeout' => 5,
					'retry_interval' => 15,
					'status' => true,
					'failure_callback' => null
				)
			),
			'compression' => false,
			'compatibility' => false
		)
		*/
	),
	'session' => array(
		'name' => 'SHOPWARESID',
		'cookie_lifetime' => 0,
		'use_trans_sid' => false,
		'gc_probability' => 1,
		'gc_divisor' => 100,
		'save_handler' => 'db'
	),
	/*
	'session' => array(
		...
		'save_handler' => 'memcache',
		'save_path' => 'tcp://localhost:11211?persistent=1&weight=1&timeout=1&retry_interval=15'
	),
	'session' => array(
		...
		'save_handler' => 'files'
	),
	*/
	'backend' => array(
		'refererCheck' => true,
	),
	'phpsettings'=>array(
		'error_reporting'=>E_ALL | E_STRICT,
		'display_errors'=>1,
		'date.timezone'=>'Europe/Berlin'
	)
);