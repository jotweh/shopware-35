<?php
/**
 * Shopware Benchmark SQL - Monitor
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_SqlMonitor extends Enlight_Controller_Action
{
	/**
	 * Load initial extjs template
	 * @return void
	 */
	public function indexAction(){
		$this->View()->loadTemplate("backend/plugins/benchmark/index.tpl");
	}

	/**
	 * Load json skeleton with window properties
	 * @return void
	 */
	public function skeletonAction(){
		$this->View()->loadTemplate("backend/plugins/benchmark/skeleton.tpl");
	}
}