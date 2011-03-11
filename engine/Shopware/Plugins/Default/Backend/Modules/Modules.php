<?php
/**
 * Backend modules controller
 */
class Shopware_Controllers_Backend_Modules extends Enlight_Controller_Action
{
	/**
	 * Init controller method 
	 */
	public function init()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
	}
	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		$path = Shopware()->OldPath().ltrim($this->Request()->getPathInfo(), '/');
		
		if(!file_exists($path)) {
			return;
		}
		
		if(Shopware()->Plugins()->Backend()->Modules()->shouldInclude()) {
			$org = getcwd();
			chdir(dirname($path));
			include($path);
			chdir($org);
		}
	}
}