<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
// *****************
?>
<?php
require_once("json.php");
$json = new Services_JSON();
?>
<?php

$nodes = array();




$_POST["id"] = intval($_POST["id"]);



if ($_POST["startDate"] && $_POST["endDate"]){
	$start = $_POST["startDate"];
	$end = $_POST["endDate"];
}else {
	$start = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
	$end = date("Y-m-d",mktime(0,0,0,date("m"),31,date("Y")));
}


if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "sv.datum";
if ($_POST["sort"]=="datumFormated") $_POST["sort"] = "datum";

/*
$resultCountArticles = mysql_query($sql);
$nodes["totalCount"] = @mysql_result($resultCountArticles,0,"count");
$nodes["totalAmount"] = @mysql_result($resultCountArticles,0,"amount");
*/





$sql = "
 SELECT
   SUM(v.uniquevisits) AS `visits`,
   SUM(v.uniquevisits)/SUM(o.`Bestellungen`) AS `averageUsers`,
   SUM(v.pageimpressions) AS `hits`,
   o.`Bestellungen` AS `countOrders`,
   SUM(u.`Neukunden`) AS `countCustomers`,
   ou.`Umsatz` AS `amount`,
   DATE_FORMAT(v.datum,'%d.%m.%Y') AS `Datum`,
   DATE_FORMAT(v.datum,'%d.%m.%Y') AS `datumFormated`
 FROM
  `s_statistics_visitors` as v
 LEFT OUTER JOIN 
  (
   SELECT
    COUNT(DISTINCT id) AS `Bestellungen`, 
    DATE (ordertime) as `date`
   FROM
    `s_order`
   WHERE
    status != 4
   AND
    status != -1
   GROUP BY 
    DATE (ordertime) 
  ) as o
 ON
  `o`.`date`=v.datum
 LEFT OUTER JOIN 
  (
   SELECT
    SUM(invoice_amount/currencyFactor) AS `Umsatz`, 
    DATE (ordertime) as `date`
   FROM
    `s_order`
   WHERE
    status != 4
   AND
    status != -1
   GROUP BY 
    DATE (ordertime) 
  ) as ou
 ON
  `ou`.`date`=v.datum
 LEFT OUTER JOIN 
  (
   SELECT
    COUNT(DISTINCT  id) AS `Neukunden`, 
    firstlogin as `date`
   FROM
    `s_user`
   GROUP BY 
    firstlogin
  ) as u
 ON
  `u`.`date`=v.datum
 WHERE 
  v.datum <= '$end'
 AND 
  v.datum >= '$start'
 GROUP BY TO_DAYS(v.datum)
 ORDER BY v.datum DESC
";


$getOrders = mysql_query($sql);




if (@mysql_num_rows($getOrders)){
	while ($order=mysql_fetch_assoc($getOrders)){
		foreach ($order as $key => $value) if (empty($value)) $order[$key] = "0";
		if (!empty($order["countOrders"])){
			$order["averageOrders"] = $order["amount"] / $order["countOrders"];
		}else {
			$order["averageOrders"] = "0";
		}
		$order["amount"] = $sCore->sFormatPrice(round($order["amount"],2));
		
		$order["averageOrders"] = $sCore->sFormatPrice(round($order["averageOrders"],2));
		$nodes["order"][] = $order;
	}
	$nodes["totalCount"] = mysql_num_rows($getOrders);
}else {
	$nodes["order"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>