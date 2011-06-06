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

		$sort = Zend_Json::decode($this->Request()->sort);
		$sort = $sort[0];

		$sortField = $sort["property"];
		$sortBy = $sort["direction"];
		if (empty($sortField)) $sortField = "executionTime";
		if (empty($sortBy)) $sortBy = "DESC";
		
		$search = $this->Request()->search;

		$addSearchSQL = "";

		if (!empty($search)){
			$sql = "
			SELECT SQL_CALC_FOUND_ROWS MAX(datum) AS executionDate, hash, MIN(TIME) AS executionMin,MAX(time) AS executionMax,AVG(time) AS executionAvg, SUM(time) AS executionTime, COUNT(id) AS executionCount,query,parameters,route FROM s_plugin_benchmark_log
			WHERE query LIKE '%$search%' OR parameters LIKE  '%$search%' OR route LIKE '%$search%'
			GROUP BY hash
			ORDER BY $sortField $sortBy
			LIMIT $start,$limit
			";
			$getQueries = Shopware()->Db()->fetchAll($sql);
		}else {
			$sql = "
			SELECT SQL_CALC_FOUND_ROWS MAX(datum) AS executionDate, hash, MIN(TIME) AS executionMin,MAX(time) AS executionMax,AVG(time) AS executionAvg, SUM(time) AS executionTime, COUNT(id) AS executionCount,query,parameters,route FROM s_plugin_benchmark_log
			GROUP BY hash
			ORDER BY $sortField $sortBy
			LIMIT $start,$limit
			";
			$getQueries = Shopware()->Db()->fetchAll($sql);
		}

		
		$limit = Shopware()->Db()->fetchOne("SELECT FOUND_ROWS()");
		foreach ($getQueries as &$query){
			$query["parameters"] = unserialize($query["parameters"]);
			$query["parameters"] = implode(",",$query["parameters"]);
			$query["executionTime"] = number_format($query["executionTime"], 5, '.', '');
			$query["executionMin"] = number_format($query["executionMin"], 5, '.', '');
			$query["executionMax"] = number_format($query["executionMax"], 5, '.', '');
			$query["executionAvg"] = number_format($query["executionAvg"], 5, '.', '');
			$query["executionSessions"] = Shopware()->Db()->fetchOne("
			SELECT COUNT(DISTINCT session) FROM s_plugin_benchmark_log WHERE hash = ?
			",array($query["hash"]));
			$query["executionQueriesPSession"] = intval($query["executionCount"] / $query["executionSessions"]);
		}
		
		echo Zend_Json::encode(array("total"=>$limit,"result"=>$getQueries));
	}
}