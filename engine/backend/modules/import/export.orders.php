<?php
/**
 * Shopware API Beispiel: Bestellungen in XML exportieren
 *
 * @author      Heiner Lohaus <hl@shopware2.de>
 * @package     Shopware 2.08.01
 * @subpackage  API
 */
/*
	Klassen laden, Aliase erstellen und Einstellungen vornehmen
*/
require_once('../../../connectors/api/api.php');
$api = new sAPI();

$sql = 'SELECT id FROM s_core_auth WHERE sessionID=? AND lastlogin>=DATE_SUB(NOW(),INTERVAL 60*90 SECOND)';
$id = isset($_REQUEST[session_name()]) ? $_REQUEST[session_name()] : $_COOKIE[session_name()];
$result = $api->sDB->GetOne($sql,array($id));
if (empty($result))
	exit;
if ($_REQUEST["sAPI"]!=$api->sSystem->sCONFIG["sAPI"])
	exit;

$export =& $api->export->shopware;
$mapping =& $api->convert->mapping;
$xml =& $api->convert->xml;
$xml->sSettings['encoding'] = "ISO-8859-1";

$where = array();
if(!empty($_REQUEST["ordernumber"])&&is_numeric($_REQUEST["ordernumber"]))
{
	$where[] = "ordernumber>=".(int)$_REQUEST["ordernumber"];
}
if(!empty($_REQUEST["orderstateID"])&&is_numeric($_REQUEST["orderstateID"]))
{
	$where[] = "status=".($_REQUEST["orderstateID"]-1);
}
if(!empty($_REQUEST["paymentstateID"])&&is_numeric($_REQUEST["paymentstateID"]))
{
	$where[] = "cleared=".($_REQUEST["paymentstateID"]-1);
}
if(!empty($_REQUEST["fromDate"])&&$_REQUEST["fromDate"]!="Bitte auswählen")
{
	$_REQUEST["fromDate"] = explode(".",$_REQUEST["fromDate"]);
	$_REQUEST["fromDate"] = $api->sDB->qstr($_REQUEST["fromDate"][2]."-".$_REQUEST["fromDate"][1]."-".$_REQUEST["fromDate"][0]." 00:00:00");
	$where[] = "ordertime>=".$_REQUEST["fromDate"];
}
if(!empty($_REQUEST["toDate"])&&$_REQUEST["fromDate"]!="Bitte auswählen")
{
	$_REQUEST["toDate"] = explode(".",$_REQUEST["toDate"]);
	$_REQUEST["toDate"] = $api->sDB->qstr($_REQUEST["toDate"][2]."-".$_REQUEST["toDate"][1]."-".$_REQUEST["toDate"][0]." 23:59:59");
	$where[] = "ordertime<=".$_REQUEST["toDate"];
}
if(!empty($_REQUEST["formatID"])&&$_REQUEST["formatID"]==1)
{
	header('Content-type: text/x-comma-separated-values');
	header('Content-Disposition: attachment; filename="export.orders.'.date("Y.m.d").'.csv"');
}
else 
{
	header('Content-type: text/xml');
	header('Content-Disposition: attachment; filename="export.orders.'.date("Y.m.d").'.xml"');
}

/*
	Bestellungen mit dem Status 0 holen
*/
if(empty($where))
	$orders = $export->sGetOrders();
else 
	$orders = $export->sGetOrders (array("where"=>implode(" AND ",$where)));
	
$orderIDs = array_keys($orders);
if(!$orders)
	exit();
	
/*
	Die dazu passenden Kunden holen
*/
$customers = $export->sOrderCustomers(array("orderIDs"=> $orderIDs));
if(!$customers)
	exit();
	
/*
	Und die dazu passenden Bestellpositionen holen
*/
$positions = $export->sOrderDetails(array("orderIDs"=> $orderIDs));
if(!$positions)
	exit();	
	
/*
	Bestellung maskieren
*/
$ordermask = array (
	"orderID",
	"ordernumber",
	"ordertime",
	"customerID",
	"paymentID",
	"transactionID",
	"partnerID",
	"clearedID",
	"statusID",
	"paymentID",
	"dispatchID",
	"subshopID",
	"invoice_amount",
	"invoice_amount_net",
	"invoice_shipping",
	"invoice_shipping_net",
	"invoice_amount_net",
	"invoice_amount_net",
	"netto",
	"cleared_description",
	"status_description",
	"payment_description",
	"dispatch_description",
	"currency_description",
	"referer", 
	"cleareddate",
	"trackingcode",
	"language",
	"currency",
	"currencyFactor",
);	
$orders = $mapping->convert_array ($ordermask, $orders);

/*
	Kunden maskieren
*/
$salutationmap = array(
	"mr" => "Herr",
	"ms" => "Frau",
	"_default" => ""
);
$customermask = array (
	"customernumber",
	"billing_company",
	"billing_department",
	"billing_salutation" => array (
		"convert" => array(
			"map" =>  $salutationmap
		)
	),
	"billing_firstname",
	"billing_lastname",
	"billing_street",
	"billing_streetnumber",
	"billing_street",
	"billing_zipcode",
	"billing_city",
	"billing_country",
	"billing_countryen",
	"billing_countryiso",
	"shipping_company",
	"shipping_department",
	"shipping_salutation" => array (
		"convert" => array(
			"map" =>  $salutationmap
		)
	),
	"shipping_firstname",
	"shipping_lastname",
	"shipping_street",
	"shipping_streetnumber",
	"shipping_street",
	"shipping_zipcode",
	"shipping_city",
	"shipping_country",
	"shipping_countryen",
	"shipping_countryiso",
	"ustid" => array (
		"convert" => "trim"
	),
	"phone",
	"fax",
	"email",
	"customergroup",
	"paymentID",
	"newsletter",
	"affiliate",
	"language",
);	
$customers = $mapping->convert_array ($customermask, $customers);

/*
	Bestellpositionen maskieren
*/
$positionmask = array (
	"orderdetailsID",
	"articleID",
	"articleordernumber",
	"name",
	"price",
	"quantity",
	"invoice",
	"releasedate",
	"tax",
	"esd",
	"modus",
);	
foreach ($positions as $orderID =>$position)
{
	$tmp[$orderID] = $mapping->convert_array ($positionmask, $position);
}
$positions = $tmp;




if(!empty($_REQUEST["formatID"])&&$_REQUEST["formatID"]==1)
{
	$open_orders = array();
	/*
		Alle Daten zusammenfassen
	*/
	foreach ($orderIDs as $orderID)
	{
		if(isset($orders[$orderID])&&isset($customers[$orderID])&&isset($positions[$orderID]))
		{
			$orders[$orderID]["count_positions"] = count($positions[$orderID]);
			foreach ($positions[$orderID] as $key=>&$position)
			{
				$open_orders[$position["orderdetailsID"]] = array_merge($orders[$orderID],$position,$customers[$orderID]);
			}
		}
		unset($orders[$orderID],$customers[$orderID],$positions[$orderID]);
	}
	/*
		Daten in CSV konvertieren
	*/
	$open_orders = $api->convert->csv->encode($open_orders/*,$csv->get_all_keys($open_orders)*/);
	
	/*
		CSV-Daten ausgeben
	*/

	echo $open_orders;
}
else 
{
	$open_orders = array();
	/*
		Alle Daten zusammenfassen
	*/
	foreach ($orderIDs as $orderID)
	{
		if(isset($orders[$orderID])&&isset($customers[$orderID])&&isset($positions[$orderID]))
		{
			$open_orders[$orderID] = $orders[$orderID];
			$open_orders[$orderID]["count_positions"] = count($positions[$orderID]);
			$open_orders[$orderID]["customer"] = $customers[$orderID];
			$open_orders[$orderID]["positions"]["position"] = $positions[$orderID];
		}
		unset($orders[$orderID],$customers[$orderID],$positions[$orderID]);
	}
	$open_orders = array("shopware"=>array("orders"=>array("order"=>$open_orders)));
	$open_orders = $xml->encode($open_orders);
	echo $open_orders;
}
if(!empty($_REQUEST["updatestateID"])&&is_numeric($_REQUEST["updatestateID"]))
{
	$export->sUpdateOrderStatus(array("status"=>$_REQUEST["updatestateID"]-1,"orderIDs"=>$orderIDs));
}
//sUpdateOrderStatus(array("status"=>,"orderIDs"=>$orderIDs));
?>