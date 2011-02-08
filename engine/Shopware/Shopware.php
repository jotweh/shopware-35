<?php
require_once(dirname(dirname(__FILE__)).'/Enlight/Enlight/Enlight.php');

class Shopware extends Enlight
{
	protected $old_path;
	
	public function __construct($environment='production', $options=null)
	{
		Shopware($this);
		
		$this->ds = DIRECTORY_SEPARATOR;
		$this->old_path = dirname(dirname(dirname(__FILE__))).$this->ds;
				
		if($options===null) {
			$options = $this->old_path.'Application.php';
		}
		$options = $this->loadConfig($options);
		
		$options['app'] = __CLASS__;
		$options['app_path'] = dirname(__FILE__);
				
		parent::__construct($environment, $options);
	}
	
	public function OldPath($path=null)
	{
		if($path!==null) {
			$path = str_replace('_', $this->ds, $path);
			return $this->old_path.$path.$this->ds;
		}
		return $this->old_path;
	}
	
	public function DocPath($path=null)
	{
		return $this->OldPath($path);
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
function Shopware($newInstance=null)
{
	static $instance;
	if(isset($newInstance)) {
		$oldInstance = $instance;
		$instance = $newInstance;
		return $oldInstance;
	}
	elseif(!isset($instance)) {
		$instance = Shopware::Instance();
	}
	return $instance;
}