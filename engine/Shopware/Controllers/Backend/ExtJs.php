<?php
/**
 * Shopware Backend Controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_ExtJs extends Enlight_Controller_Action
{
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		
	}
	
	/**
	 * Index action method
	 */
	public function loadAction()
	{
		$path = $this->Request()->getPathInfo();
		$path = explode('/', $path);
		$path = array_slice($path, 4);
		$path = implode('/', $path);
		
		$path = Zend_Filter::filterStatic($path, 'Word_CamelCaseToUnderscore');
		$path = Zend_Filter::filterStatic($path, 'StringToLower');
		$path = Zend_Filter::filterStatic($path, 'PregReplace', array('#[^a-z_/]#'));

		$this->View()->loadTemplate('backend/' . $path . '.js');
		$this->Response()->setHeader('Content-Type', 'application/javascript;charset=iso-8859-1');
	}
}