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

	/**
	 * Get a list of queries to display in grid
	 * @return void
	 */
	public function getQueriesAction(){
		$this->View()->setTemplate();
		$start = $this->Request()->start;
		$limit = $this->Request()->limit;
		$getQueries = Shopware()->Db()->fetchAll("
		SELECT SQL_CALC_FOUND_ROWS MAX(datum) AS executionDate, MIN(TIME) AS executionMin,MAX(time) AS executionMax,AVG(time) AS executionAvg, SUM(time) AS executionTime, COUNT(id) AS executionCount,query,parameters,route FROM s_plugin_benchmark_log
		GROUP BY hash
		ORDER BY executionTime DESC
		LIMIT $start,$limit
		");
		$limit = Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()");
		foreach ($getQueries as &$query){
			$query["parameters"] = unserialize($query["parameters"]);
			$query["parameters"] = implode(",",$query["parameters"]);
			$query["executionTime"] = number_format($query["executionTime"], 5, '.', '');
			$query["executionMin"] = number_format($query["executionMin"], 5, '.', '');
			$query["executionMax"] = number_format($query["executionMax"], 5, '.', '');
			$query["executionAvg"] = number_format($query["executionAvg"], 5, '.', '');
												
		}
		echo Zend_Json::encode(array("total"=>$limit,"result"=>$getQueries));
	}
}