<?php
require_once(dirname(dirname(__FILE__)).'/Enlight/Enlight/Enlight.php');

class Shopware extends Enlight
{
	protected $old_path;
	
	public function __construct($environment='production', $options=null)
	{
		$this->old_path = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
				
		if($options===null) {
			/*
			$options = array(
				'phpsettings'=>array(
					'error_reporting'=>E_ALL | E_STRICT,
					'display_errors'=>1,
					'date.timezone'=>'Europe/Berlin'
				)
			);
			*/
			$options = $this->old_path.'Application.php';
		}
		$options = $this->loadConfig($options);
		
		$options['app'] = __CLASS__;
		$options['app_path'] = dirname(__FILE__);
		
		parent::__construct($environment, $options);
		

	}
	
	public function OldPath()
	{
		return $this->old_path;
	}
	
	public function DocPath()
	{
		return $this->old_path;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Template_TemplateManager
	 */
	public function Template()
	{
		return $this->_bootstrap->getResource('Template');
	}
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Components_Db_Adapter_Pdo_Mysql
	 */
	public function Db()
	{
		return $this->_bootstrap->getResource('Db');
	}
	/**
	 * Enter description here...
	 *
	 * @return Enlight_Components_Session_Namespace
	 */
	public function Session()
	{
		return $this->_bootstrap->getResource('Session');
	}
}

/**
 * Enter description here...
 *
 * @return Shopware
 */
function Shopware()
{
	static $instance;
	if(!isset($instance))
	{
		$instance = Shopware::Instance();
	}
	return $instance;
}