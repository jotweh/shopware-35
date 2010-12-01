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
		'forceCompile' => false
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
    	)
	),
	'backend' => array(
		'refererCheck' => true,
	),
	'phpsettings'=>array(
		'error_reporting'=>E_ALL | E_STRICT,
		'display_errors'=>1,
		'date.timezone'=>'Europe/Berlin'
	)
);