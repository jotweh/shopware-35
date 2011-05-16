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
abstract class Shopware_Controllers_Backend_ExtJs extends Enlight_Controller_Action
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
		$request = $this->Request();
		
		switch (true) {
			case $request->view!==null:
				$path = 'view/' . $request->view;
				break;
			case $request->store!==null:
				$path = 'store/' . $request->store;
				break;
			case $request->model!==null:
				$path = 'model/' . $request->model;
				break;
			case $request->controller!==null:
				$path = 'controller/' . $request->controller;
				break;
			default:
				return;
		}
		
		$moduleName = $this->Front()->Dispatcher()->formatModuleName($request->getModuleName());
		$controllerName = $this->Front()->Dispatcher()->formatControllerName($request->getControllerName());
		
		$path = $moduleName . '/' . $controllerName . '/' . $path;
				
		$path = Zend_Filter::filterStatic($path, 'Word_CamelCaseToUnderscore');
		$path = Zend_Filter::filterStatic($path, 'StringToLower');
		$path = Zend_Filter::filterStatic($path, 'PregReplace', array('#[^a-z_/]#'));
		
		$this->View()->loadTemplate($path . '.js');
		$this->Response()->setHeader('Content-Type', 'application/javascript;charset=iso-8859-1');
	}
	
	/**
	 * Index action method
	 */
	public function viewAction()
	{
		$this->loadAction();
	}
	
	/**
	 * Index action method
	 */
	public function modelAction()
	{
		$this->loadAction();
	}
	
	/**
	 * Index action method
	 */
	public function controllerAction()
	{
		$this->loadAction();
	}
}