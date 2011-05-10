<?php
/**
 * Shopware default widgets - data bridge
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Plugins/Widgets
 */
class Shopware_Controllers_Backend_WidgetDataStore extends Enlight_Controller_Action
{
	/**
	 * Method to retrieve shop amount statistics
	 * @return void
	 */
	public function getAmountAction(){
		if (!$this->checkWidgetPermissions("ShopwareAmountChart")){
			throw new Enlight_Exception("Permission denied");
		}

		$this->View()->setTemplate();

		$id = $this->Request()->id;
		if (empty($id)){
			throw new Enlight_Exception("Empty id");
		}
		$panelModel = new Shopware_Models_Widgets_Panel(md5($_SESSION["Shopware"]["Auth"]->id));
		$config = $panelModel->getWidgetConfiguration($id);

		$subshopID = $config["subshopID"];
		$interval = $config["timeBack"];

		if (empty($interval)) $interval = 14;

		$sqlAdd = "";
		if (!empty($subshopID)){
			$sqlAdd = "	AND subshopID = ".Shopware()->Db()->quote($subshopID);
		}
		
		$sql = "
		SELECT
			SUM(invoice_amount/currencyFactor) AS `amount`,
			COUNT(id) AS `count`,
			DATE_FORMAT(ordertime,'%d.%m.%y') AS `day`
		FROM `s_order`
		WHERE
			ordertime >= DATE_SUB(now(),INTERVAL $interval DAY)
		AND
			status != 4
		AND
			status != -1
		$sqlAdd
		GROUP BY
			DATE(ordertime)
		ORDER BY ordertime ASC";

		$result = Shopware()->Db()->fetchAll($sql);
		
		foreach ($result as &$row){
			$row["amount"] = round($row["amount"],0);
		}
		echo Zend_Json::encode(array("data"=>$result));
	}

	/**
	 * Get a list of last orders to display in widget
	 * @return void
	 */
	public function getOrdersAction(){
		if (!$this->checkWidgetPermissions("ShopwareLastOrders")){
			throw new Enlight_Exception("Permission denied");
		}
		$this->View()->setTemplate();

		$id = $this->Request()->id;
		if (empty($id)){
			throw new Enlight_Exception("Empty id");
		}
		$panelModel = new Shopware_Models_Widgets_Panel(md5($_SESSION["Shopware"]["Auth"]->id));
		$config = $panelModel->getWidgetConfiguration($id);

		$subshopID = $config["subshopID"];
		$restrictPayment = $config["restrictPayment"];

		$addSqlPayment = ""; $addSqlSubshop = "";
		if (!empty($subshopID)){
			$addSqlSubshop = "
			AND s_order.subshopID = ".Shopware()->Db()->quote($subshopID);
		}

		if (!empty($restrictPayment)){
			$addSqlPayment = "
			AND s_order.paymentID = ".Shopware()->Db()->quote($restrictPayment);
		}
		
		$sql = "
		SELECT s_order.id AS id, currency,currencyFactor,firstname,lastname, company, subshopID, paymentID,  ordernumber, transactionID, s_order.userID AS userID, invoice_amount,invoice_shipping, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertimeFormated, status, cleared
		FROM s_order
		LEFT JOIN s_order_billingaddress ON s_order_billingaddress.userID = s_order.userID
		WHERE
			s_order.status != -1
		$addSqlSubshop
		$addSqlPayment
		AND
			ordertime >= DATE_SUB(now(),INTERVAL 14 DAY)
		GROUP BY s_order.id
		ORDER BY ordertime DESC
		LIMIT 20
		";

		$result = Shopware()->Db()->fetchAll($sql);
		foreach ($result as &$order){
			$order["customer"] = htmlentities($order["company"] ? $order["company"] : $order["firstname"]." ".$order["lastname"],ENT_QUOTES);
			$amount = round(($order["invoice_amount"]/$order["currencyFactor"]),2);
			$order["invoice_amount"] = $amount;
			if (strlen($order["customer"])>25){
				$order["customer"] = substr($order["customer"],0,25)."..";
			}
		}
		echo Zend_Json::encode(array("total"=>count($result),"result"=>$result));
	}

	/**
	 * Get a list of last edited articles to display in widget
	 * @return void
	 */
	public function getLastEditsAction(){
		if (!$this->checkWidgetPermissions("ShopwareLastEdits")){
			throw new Enlight_Exception("Permission denied");
		}
		$this->View()->setTemplate();
		$result = Shopware()->Db()->fetchAll("
		SELECT id, name,changetime FROM s_articles WHERE changetime!='0000-00-00' ORDER BY changetime DESC LIMIT 20
		");
		foreach ($result as &$row){
			$row["name"] = utf8_encode($row["name"]);
		}
		echo Zend_Json::encode(array("total"=>count($result),"result"=>$result));
	}

	protected function checkWidgetPermissions($id){
		
		$widgetsApi = new Shopware_Models_Widgets_Widgets(null,Shopware()->DocPath()."/files/config/Widgets.xml");
		$widget = $widgetsApi->get($id);
		
		if (isset($widget["permissions"])){
			$mode = $widget["permissions"]["aclGroup"];
			if ($mode == 1 && !$_SESSION["Shopware"]["Auth"]->admin){
				// Only admins
				return false;
			}

			if ($mode == 2){
				// User based permissions
				$users = $widget["permissions"]["Users"];

				$validUser = false;
				foreach ($users as $checkUserId){
					if ($checkUserId["id"] == $_SESSION["Shopware"]["Auth"]->id){
						$validUser = true;
					}
				}
				if (!$validUser) return false;
			}
		}
		return true;
	}

	/**
	 * Get historical and current conversion rates
	 * @return void
	 */
	public function getConversionAction(){
		$this->View()->setTemplate();
		$id = $this->Request()->id;

		if (!$this->checkWidgetPermissions("ShopwareConversion")){
			throw new Enlight_Exception("Permission denied");
		}

		$panelModel = new Shopware_Models_Widgets_Panel(md5($_SESSION["Shopware"]["Auth"]->id));
		$config = $panelModel->getWidgetConfiguration($id);

		$shop = $config["subshopID"];
		$timeBack = $config["timeBack"];

		if (empty($timeBack)){
			$timeBack = 7;
		}

		// Calculate conversion in configured time-range
		for ($i=0;$i<=1;$i++){
			$operator = $i == 0 ? ">=" : "<";
			$sql = "
			SELECT
				COUNT(id) AS `countOrders`,
				DATE_FORMAT(DATE_SUB(now(),INTERVAL ? DAY),'%d.%m.%Y') AS point,
				((SELECT SUM(uniquevisits) FROM s_statistics_visitors WHERE datum $operator DATE_SUB(now(),INTERVAL ? DAY) GROUP BY DATE_SUB(now(),INTERVAL ? DAY))) AS visitors
			FROM `s_order`
			WHERE
				ordertime $operator DATE_SUB(now(),INTERVAL ? DAY)
			AND
				status != 4
			AND
				status != -1
			GROUP BY
				DATE_SUB(now(),INTERVAL ? DAY)
			";
			$result[$i == 0 ? "current" : "historical"] = Shopware()->Db()->fetchRow($sql,array($timeBack,$timeBack,$timeBack,$timeBack,$timeBack));
		}

		$result["current"]["conversion"] = number_format($result["current"]["countOrders"] / $result["current"]["visitors"] * 100,2) ;
		$result["historical"]["conversion"] = number_format($result["historical"]["countOrders"] / $result["historical"]["visitors"] * 100,2);
		
		$config["absValue"] = $result["current"]["conversion"];
		$config["perValue"] = $config["absValue"] - $result["historical"]["conversion"];

		if ($config["perValue"] > 0){
			$config["upDown"] = "up";
		}else {
			$config["upDown"] = "down";
		}

		$result = array("current"=>$result["current"]["conversion"],"historical"=>$result["historical"]["conversion"],"abs"=>$config["absValue"],"updown"=>$config["upDown"],"percent"=>$config["perValue"],"datePoint"=>$result["current"]["point"]);
		echo Zend_Json::encode($result);
	}

	/**
	 * Load user notes to display in widget
	 * @return void
	 */
	public function loadNotesAction(){
		$this->View()->setTemplate();

		$userID = $_SESSION["Shopware"]["Auth"]->id;
		$notes = Shopware()->Db()->fetchOne("
		SELECT notes FROM s_plugin_widgets_notes WHERE userID = ?
		",array($userID));
		echo Zend_Json::encode(array("success"=>true,"data"=>array("notes"=>$notes)));
	}

	/**
	 * Save user notes to database
	 * @return void
	 */
	public function saveNotesAction(){
		if (!$this->checkWidgetPermissions("ShopwareNotepad")){
			throw new Enlight_Exception("Permission denied");
		}
		$this->View()->setTemplate();
		$userID = $_SESSION["Shopware"]["Auth"]->id;

		$notes = $this->Request()->notes;

		if (Shopware()->Db()->fetchOne("SELECT id FROM s_plugin_widgets_notes WHERE userID = ?",array($userID))){
			// Update
			Shopware()->Db()->query("
			UPDATE s_plugin_widgets_notes SET notes = ? WHERE userID = ?
			",array($notes,$userID));
		}else {
			// Insert
			Shopware()->Db()->query("
			INSERT INTO s_plugin_widgets_notes (userID, notes)
			VALUES (?,?)
			",array($userID,$notes));
		}
		echo Zend_Json::encode(array("success"=>true));
	}

	/**
	 * Get a list of referrers from today, sorted by count
	 * @return void
	 */
	public function getReferrerAction(){
		if (!$this->checkWidgetPermissions("ShopwareReferer")){
			throw new Enlight_Exception("Permission denied");
		}
		$this->View()->setTemplate();
		$result = Shopware()->Db()->fetchAll("
		SELECT referer, COUNT(referer) AS `countRef` FROM s_statistics_referer
		WHERE DATE(datum) = DATE(now())
		GROUP BY referer
		ORDER BY countRef DESC
		LIMIT 200
		");

		$cleanResult = array();
		$tempCount = array();
		foreach ($result as &$row){
			if (strpos($row["referer"],"q=")!==false){
				$q = array();
				preg_match("/(.*)q=(.*)[&|?|\\n]/Us",$row["referer"],$q);
				if (!empty($q[2])){
					$searchTerm = "Search for ".htmlentities(utf8_decode(urldecode($q[2])));
					$tempCount[md5($searchTerm)]++;
					
					$cleanResult[md5($searchTerm)] = array("referrer"=>$searchTerm,"referrerOriginal"=>$row["referer"],"count"=>$tempCount[md5($searchTerm)]);
				}
			}else {
				$searchTerm = md5(htmlentities(utf8_encode(urldecode($row["referer"]))));
				$tempCount[$searchTerm]++;
				$cleanResult[$searchTerm] = array("referrer"=>htmlentities(utf8_encode(urldecode($row["referer"]))),"referrerOriginal"=>$row["referer"],"count"=>$tempCount[$searchTerm]);
			}
		}
		$this->multiArraySort($cleanResult,"count");
		$cleanResult = array_reverse($cleanResult);
		$cleanResult = array_values($cleanResult);

		if (!is_array($cleanResult[0])){
			$cleanResult[0] = array("referrer"=>"No Referrers today","count"=>"0","referrerOriginal"=>"");
		}
		foreach ($cleanResult as &$result){
			if (empty($result["referrer"]) || $result["referrer"]=="&nbsp;") unset($result);
		}
		echo Zend_Json::encode(array("total"=>count($cleanResult),"result"=>$cleanResult));
	}

	/**
	 * Helper function to easily sort multidimensional arrays and preserve keys
	 * @param  $data
	 * @param  $sortby
	 * @return void
	 */
	protected function multiArraySort(&$data, $sortby)
	{
	   static $sort_funcs = array();

	   if (empty($sort_funcs[$sortby])) {
	       $code = "\$c=0;";
	       foreach (explode(',', $sortby) as $key) {
	         $array = array_pop($data);
	         array_push($data, $array);
	         if(is_numeric($array[$key]))
	           $code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) );";
	         else
	           $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
	       }
	       $code .= 'return $c;';
	       $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	   } else {
	       $sort_func = $sort_funcs[$sortby];
	   }

	  $sort_func = $sort_funcs[$sortby];
      uasort($data, $sort_func);
	}

	/**
	 * Get today visitors and some basic statistics to compare
	 * @return void
	 */
	public function getVisitorsAction(){
		if (!$this->checkWidgetPermissions("ShopwareViewStats")){
			throw new Enlight_Exception("Permission denied");
		}
		$this->View()->setTemplate();

		$id = $this->Request()->id;
		$panelModel = new Shopware_Models_Widgets_Panel(md5($_SESSION["Shopware"]["Auth"]->id));
		$config = $panelModel->getWidgetConfiguration($id);

		$shop = $config["subshopID"];
		$timeBack = $config["timeBack"];

		if (empty($timeBack)){
			$timeBack = 7;
		}

		// Calculate conversion in configured time-range
		$sql = "
		SELECT AVG(uniquevisits) AS `avg`,MIN(uniquevisits) AS `min`,MAX(uniquevisits) AS `max`
		FROM s_statistics_visitors
		WHERE datum >= DATE_SUB(now(),INTERVAL ? DAY)
		GROUP BY DATE_SUB(now(),INTERVAL ? DAY)
		";
		$legend = Shopware()->Db()->fetchRow($sql,array($timeBack,$timeBack));

		$today = Shopware()->Db()->fetchOne("
		SELECT uniquevisits FROM s_statistics_visitors WHERE DATE(datum) = DATE(now())
		");

			
		$config["absValue"] = $today;
		$config["perValue"] = -100 + round($today / $legend["avg"] * 100,2);

		if ($config["perValue"] > 0){
			$config["upDown"] = "up";
		}else {
			$config["upDown"] = "down";
		}


		$result = array("avg"=>$legend["avg"],"max"=>$legend["max"],"min"=>$legend["min"],"abs"=>$config["absValue"],"updown"=>$config["upDown"],"percent"=>$config["perValue"]);
		echo Zend_Json::encode($result);
	}
}