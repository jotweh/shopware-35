<?php
if (!defined('sAuthFile')) die();

if(empty($_REQUEST['date']))
	$von = time();
else 
	$von = strtotime($_REQUEST["date"]);

if(empty($_REQUEST['date']))
	$bis = time();
else 
	$bis = strtotime($_REQUEST["date2"]);


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

if(empty($_REQUEST['table'])) {
	$sort = "ASC";
}else {
	$sort = "DESC";
}

$sql = "
	SELECT 
		s_core_multilanguage.name AS `Shop`,
		SUM($amount_1/currencyFactor) AS `Umsatz`,
		COUNT(s_order.id) AS `Bestellungen`
	FROM `s_order`
	LEFT JOIN s_core_multilanguage ON s_core_multilanguage.id = s_order.subshopID
	WHERE 
		ordertime <='".date("Y-m-d",$bis)." 23:59:59'
	AND 
		ordertime >= '".date("Y-m-d",$von)."'
	AND 
		status != 4
	AND
		status != -1
	GROUP BY 
		s_order.subshopID
	ORDER BY `Umsatz` DESC";

$result = mysql_query($sql);

if (!$result)
	die();

$data = array();
while ($entry = mysql_fetch_assoc($result))
{
	$dat['Shop'] = $entry["Shop"];
	$dat['Umsatz'] = round($entry["Umsatz"],2);
	if(empty($dat['Umsatz']))
		$dat['Umsatz'] = 0;
	$dat["Bestellungen"] = $entry["Bestellungen"];
	$data[] = $dat;
}


	
	
if (!isset($csv))
{
include("json.php");
 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_subshop'
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
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
?>