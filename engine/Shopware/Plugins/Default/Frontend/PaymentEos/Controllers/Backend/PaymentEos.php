<?php
/**
 * Eos payment controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_PaymentEos extends Shopware_Controllers_Backend_ExtJs
{	
	/**
	 * Pre dispatch action method
	 */
	public function preDispatch()
	{
		if(!in_array($this->Request()->getActionName(), array('index', 'skeleton', 'view', 'model', 'controller'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}
	
	/**
	 * Skeleton action method
	 */
	public function skeletonAction ()
	{
	}
	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
	}
}