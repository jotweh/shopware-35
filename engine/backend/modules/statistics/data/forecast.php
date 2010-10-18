<?php
if (!defined('sAuthFile')) die();


//Tages-Umsatz dieser Woche
/*
		WEEK(ordertime,3) = WEEK(NOW(),3)
	AND 
		YEAR(ordertime) = YEAR(NOW())
	AND 
		DATE(ordertime) != DATE(NOW())
*/

if ($_REQUEST["tax"]==1){
	$brutto = false;	
}else {
	$brutto = true;
}


if ($brutto){
	$amount_1 = "invoice_amount";
}else {
	$amount_1 = "invoice_amount_net";
}


$sql = "
	SELECT
		ROUND(SUM($amount_1/currencyFactor),2) AS `Umsatz`,
		WEEK(ordertime,3) AS `Woche`,
		COUNT(*) as `Bestellungen`,
		ROUND(SUM($amount_1/currencyFactor)/(REPLACE(DAYOFWEEK(NOW())-1,0,7)),2) AS `Tagesumsatz`,
		REPLACE(DAYOFWEEK(NOW()),0,7)-1 as `Tage`,
		ROUND(COUNT(*)/(REPLACE(DAYOFWEEK(NOW())-1,0,7))) as `Bestellungen pro Tag`
	FROM `s_order`
	WHERE 
		YEARWEEK(ordertime, 3) = YEARWEEK(NOW(), 3)
	AND 
		status != 4
	AND
		status != -1
	GROUP BY Woche
";
$result = mysql_query($sql);
if (!$result)
	die('FAIL1');
if(!mysql_num_rows($result)){
	$data['this_week'] = array();
	$data['this_week']['Beschreibung'] = $sLang["statistics"]["forecast_this_week"];
}else {
	$data['this_week'] = mysql_fetch_assoc($result);
	$data['this_week']['Beschreibung'] = $sLang["statistics"]["forecast_this_week"];
}
//Tages-Umsatz letzter Woche
/*
		WEEK(ordertime,3) = WEEK(DATE_SUB(NOW(), INTERVAL 7 DAY),3)
	AND 
		YEAR(ordertime) = YEAR(DATE_SUB(NOW(), INTERVAL 7 DAY))
*/
$sql = "
	SELECT
		ROUND(SUM($amount_1/currencyFactor),2) AS `Umsatz`,
		WEEK(ordertime,3) AS `Woche`,
		COUNT(*) as `Bestellungen`,
		ROUND(SUM($amount_1/currencyFactor)/7,2) AS `Tagesumsatz`,
		7 as `Tage`,
		ROUND(COUNT(*)/7) as `Bestellungen pro Tag`
	FROM `s_order`
	WHERE 		
		YEARWEEK(ordertime, 3) = YEARWEEK(DATE_SUB(NOW(), INTERVAL 7 DAY), 3)
	AND 
		status != 4
	AND
		status != -1
	GROUP BY Woche
";
$result = mysql_query($sql);
if (!$result)
	die('FAIL3');
	
if(!mysql_num_rows($result)){
	$data['last_week'] = array();
	$data['last_week']['Beschreibung'] = $sLang["statistics"]["forecast_last_week"];
}else {
	$data['last_week'] = mysql_fetch_assoc($result);
	$data['last_week']['Beschreibung'] = $sLang["statistics"]["forecast_last_week"];
}
//Tages-Umsatz diesen Monat
$sql = "
	SELECT
		ROUND(SUM($amount_1/currencyFactor),2) AS `Umsatz`,
		ROUND(SUM($amount_1/currencyFactor)/(DAYOFMONTH(NOW())),2) AS `Tagesumsatz`,
		MONTH(ordertime) AS `Monat`,
		COUNT(*) as `Bestellungen`,
		DAYOFMONTH(NOW()) as `Tage`,
		ROUND(COUNT(*)/(DAYOFMONTH(NOW())-1)) as `Bestellungen pro Tag`
	FROM `s_order`
	WHERE 
		MONTH(ordertime) = MONTH(NOW())
	AND 
		YEAR(ordertime) = YEAR(NOW())
	AND 
		status != 4
	AND
		status != -1
	GROUP BY YEAR(ordertime)
";
$result = mysql_query($sql);
if (!$result)
	die('FAIL5');
if(!mysql_num_rows($result)){
	$data['this_month'] = array();
	$data['this_month']['Beschreibung'] = $sLang["statistics"]["forecast_this_month"];
}else {
	//die('FAIL6');
	$data['this_month'] = mysql_fetch_assoc($result);
	$data['this_month']['Beschreibung'] = $sLang["statistics"]["forecast_this_month"];
}
//Tages-Umsatz letzten Monat
$sql = "
	SELECT
		ROUND(SUM($amount_1/currencyFactor),2) AS `Umsatz`,
		ROUND(SUM($amount_1/currencyFactor)/DAY(DATE_SUB(NOW(), INTERVAL DAY(NOW()) DAY)),2) AS `Tagesumsatz`,
		MONTH(ordertime) AS `Monat`,
		COUNT(*) as `Bestellungen`,
		DAY(DATE_SUB(NOW(), INTERVAL DAY(NOW()) DAY)) as `Tage`,
		ROUND(COUNT(*)/DAY(DATE_SUB(NOW(), INTERVAL DAY(NOW()) DAY))) as `Bestellungen pro Tag`
	FROM `s_order`
	WHERE 
		MONTH(ordertime) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
	AND 
		YEAR(ordertime) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
	AND 
		status != 4
	AND
		status != -1
	GROUP BY YEAR(ordertime)
";
$result = mysql_query($sql);
if (!$result)
	die('FAIL7');
	
if(!mysql_num_rows($result)){
	$data['last_month'] = array();
	$data['last_month']['Beschreibung'] = $sLang["statistics"]["forecast_last_month"];
}else {
	$data['last_month'] = mysql_fetch_assoc($result);
	$data['last_month']['Beschreibung'] = $sLang["statistics"]["forecast_last_month"];
}
//Tages-Umsatz im letzen Jahr selbens Monats
$sql = "
	SELECT
		ROUND(SUM($amount_1/currencyFactor),2) AS `Umsatz`,
		ROUND(SUM($amount_1/currencyFactor)/DAY(DATE_SUB(DATE_FORMAT(DATE_ADD(ordertime,INTERVAL 1 MONTH),'%Y-%m-01'),INTERVAL 1 DAY)),2) AS `Tagesumsatz`,
		MONTH(ordertime) AS `Monat`,
		COUNT(*) as `Bestellungen`,
		DAY(DATE_SUB(DATE_FORMAT(DATE_ADD(ordertime, INTERVAL 1 MONTH),'%Y-%m-01'), INTERVAL 1 DAY)) as `Tage`,
		ROUND(COUNT(*)/DAY(DATE_SUB(DATE_FORMAT(DATE_ADD(ordertime, INTERVAL 1 MONTH),'%Y-%m-01'), INTERVAL 1 DAY))) as `Bestellungen pro Tag`
	FROM `s_order`
	WHERE 
		MONTH(ordertime) = MONTH(NOW())
	AND 
		YEAR(ordertime) = YEAR(NOW())-1
	AND 
		status != 4
	AND
		status != -1
	GROUP BY YEAR(ordertime)
";

$result = mysql_query($sql);
if (!$result)
	die('FAIL9');
if(mysql_num_rows($result))
{
	$data['last_year'] = mysql_fetch_assoc($result);
	$data['last_year']['Beschreibung'] = $sLang["statistics"]["forecast_last_year_same_month"];
}else {
	$data['last_year'] = array();
	$data['last_year']['Beschreibung'] = $sLang["statistics"]["forecast_last_year_same_month"];
}

if(!empty($data['last_month']))
{
	$data['forecast_month']['Beschreibung'] = $sLang["statistics"]["forecast_Forecast_month"];
	
	
	if (!empty($data['this_month']))
	{
		$forecastAmount = $data['last_month']["Tagesumsatz"];
		$forecastOrders = $data['last_month']["Bestellungen pro Tag"];
	}
	if (!empty($forecastAmount)){
		$forecastAmount = $data['this_month']['Tagesumsatz'];
		//$forecastAmount /= 2;
		$forecastOrders = $data['this_month']["Bestellungen pro Tag"];
		//$forecastOrders /= 2;
	}else {
		$forecastAmount = $data['this_month']['Tagesumsatz'];
	}
	$forecastOrders = (int)$forecastOrders;
	
	$forecastAmount *= date("t");
	
	$data['forecast_month']['Tage'] = date('t');
	
	
	$data['forecast_month']['Umsatz'] = $forecastAmount;
	$data['forecast_month']['Bestellungen pro Tag'] = $forecastOrders;
	$data['forecast_month']['Bestellungen'] = $data['forecast_month']['Bestellungen pro Tag']*date('t');
	
	$data['forecast_month']['Tagesumsatz'] = $forecastAmount/date("t");
	
	$data['forecast_month']['Tage'] = date('t');
	
}
if(!empty($data['last_week'])&&!empty($data['this_week']))
{
	$data['forecast_week']['Tage'] = 7;
	$data['forecast_week']['Beschreibung'] = $sLang["statistics"]["forecast_Forecast_week"];
	
	$data['forecast_week']['Bestellungen pro Tag'] = intval(($data['this_week']['Bestellungen pro Tag']));
	
	$data['forecast_week']['Tagesumsatz'] = round(($data['this_week']['Tagesumsatz']),2);
	
	$data['forecast_week']['Bestellungen'] = $data['forecast_week']['Bestellungen pro Tag']*7;
	$data['forecast_week']['Umsatz'] = $data['forecast_week']['Tagesumsatz']*7;
	
}


if(!isset($csv))
{
	foreach ($data as $key=>$dat)
	{
		$data[$key]['Beschreibung'] = utf8_encode($dat['Beschreibung']);
	}
	
	foreach ($data as $key => $dat){
		$data2[] = $dat;
	}
	$data = $data2;
	
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='forecast'
 ");
 $getHeader = mysql_result($getHeader,0,"header");
 $getHeader = explode("#",$getHeader);
 $i=0;
 foreach ($getHeader as $header){
 	$columns = explode(";",$header);
 	unset($tempColumns);
 	foreach ($columns as $column){
 		$column = explode(":",$column);
 		if (intval($column[1])){
 			$tempColumns[$column[0]] = intval($column[1]);
 		}else {
 			$tempColumns[$column[0]] = $column[1];
 		}
 	}
 	$tempHeader[$i] = $tempColumns;
 	$i++;
 }
 
 $headers = $tempHeader;
	include("json.php");
	$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
}
?>