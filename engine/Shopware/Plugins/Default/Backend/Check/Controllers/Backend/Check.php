<?php
/**
 * Shopware Check Controller
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_Check extends Enlight_Controller_Action
{	
	/**
	 * Pre dispatch action method
	 */
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index', 'skeleton'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();	
		}
	}

	/**
	 * Index action method
	 */
	public function indexAction()
	{
	}
	
	/**
	 * Skeleton action method
	 */
	public function skeletonAction()
	{
	}

	/**
	 * Returns check paths data action
	 */
	public function checkPathListAction()
	{
		$list = new Shopware_Components_Check_Path();
		echo Zend_Json::encode(array('data'=>$list->toArray(), 'count'=>count($list)));
	}
	
	/**
	 * Returns check paths data action
	 */
	public function checkFileListAction()
	{
		$list = new Shopware_Components_Check_File();
		echo Zend_Json::encode(array('data'=>$list->toArray(), 'count'=>count($list)));
	}
	
	/**
	 * Returns check system data action
	 */
	public function checkSystemListAction()
	{
		$list = new Shopware_Components_Check_System();
		echo Zend_Json::encode(array('data'=>$list->toArray(), 'count'=>count($list)));
	}
}