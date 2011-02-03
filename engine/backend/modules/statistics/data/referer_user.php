<?php
if (!defined('sAuthFile')) die();

if (!isset($csv))
	$limit = 15;
else 
	$limit = 300;
	
list($day, $month, $year, $week) = split('-',date("d-m-Y-W",strtotime($_REQUEST['date'])));
list($day2, $month2, $year2, $week2) = split('-',date("d-m-Y-W", strtotime($_REQUEST['date2'])));

if (!empty($_REQUEST["tax"])){
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
  ROUND((o.$amount_1/currencyFactor),2) AS `Umsatz`,
  o.referer as `Referer`,
  u.id as `UserID`,
  date(u.firstlogin) as `Firstlogin`,
  date(o.ordertime) as `Ordertime`,
  (SELECT ordertime FROM s_order WHERE userID=u.id ORDER BY ordertime DESC LIMIT 1) as `Firstorder`,
  (SELECT ROUND(SUM($amount_1/currencyFactor),2) FROM s_order WHERE userID=u.id AND status != 4 AND status != -1) as `Kunden Umsatz`
 FROM  s_order o, s_user u
 WHERE o.status != 4
 AND o.status != -1
 AND o.userID=u.id
 AND o.ordertime <= '$year2-$month2-$day2 23:59:59'
 AND o.ordertime >= '$year-$month-$day'
 AND o.referer NOT LIKE 'http://www.{$sCore->sCONFIG['sHOST']}%'
 AND o.referer NOT LIKE 'http://{$sCore->sCONFIG['sHOST']}%'
 AND o.referer LIKE 'http%//%' 
 ORDER BY Umsatz
";
$result = mysql_query($sql);
if (!$result)
	die('FAIL');
if(mysql_num_rows($result)==0){
	include("json.php");
		$json = new Services_JSON();
		$result = array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>array(),"totalProperty"=>"totalCount"),"rows"=>array(),"totalCount"=>0);
		
		echo $json->encode($result);
		exit;
}

$kunden = array();
while ($entry = mysql_fetch_assoc($result))
{	
	$ref = parse_url($entry['Referer']);
	$ref = str_replace('www.','',$ref['host']);	
	if(!empty($ref))
	{
		$entry['Firstlogin'] = strtotime($entry['Firstlogin']);
		$entry['Ordertime'] = strtotime($entry['Ordertime']);
		$dif = abs($entry['Firstlogin']-$entry['Ordertime']);
		if($dif<60*60*24)
		{
			if(!in_array($entry['UserID'],$kunden))
				$arrays[$ref]['Neukunden']++;
			$arrays[$ref]['Umsatz Neukunden']+= $entry['Umsatz'];
		}
		else
		{ 
			if(!in_array($entry['UserID'],$kunden))
				$arrays[$ref]['Altkunden']++;
			$arrays[$ref]['Umsatz Altkunden']+= $entry['Umsatz'];
		}
		if(!in_array($entry['UserID'],$kunden)) $arrays[$ref]['Kunden Umsatz'] += $entry['Kunden Umsatz'];
		$kunden[] = $entry['UserID'];
		
		$arrays[$ref]['Host'] = $ref;
		$arrays[$ref]['Bestellungen'] ++;
		$arrays[$ref]['Umsatz'] += $entry['Umsatz'];
		
	}
}
foreach ($arrays as $ref => $array)
{
	if(empty($arrays[$ref]['Altkunden']))
		$arrays[$ref]['Altkunden'] = 0;
	if(empty($arrays[$ref]['Umsatz Altkunden']))
		$arrays[$ref]['Umsatz Altkunden'] = 0;
	if(empty($arrays[$ref]['Neukunden']))
		$arrays[$ref]['Neukunden'] = 0;
	if(empty($arrays[$ref]['Umsatz Neukunden']))
		$arrays[$ref]['Umsatz Neukunden'] = 0;
		
	if(!empty($array['Umsatz'])&&!empty($array['Bestellungen']))
		$arrays[$ref]['Umsatz/Bestellungen'] = round($array['Umsatz']/$array['Bestellungen'],2);
	else 
		$arrays[$ref]['Umsatz/Bestellungen'] = 0;
	if(!empty($array['Umsatz Altkunden'])&&!empty($array['Altkunden']))
		$arrays[$ref]['Umsatz/Altkunden'] = round($array['Umsatz Altkunden']/$array['Altkunden'],2);
	else 
		$arrays[$ref]['Umsatz/Altkunden'] = 0;
	if(!empty($array['Umsatz Neukunden'])&&!empty($array['Neukunden']))
		$arrays[$ref]['Umsatz/Neukunden'] = round($array['Umsatz Neukunden']/$array['Neukunden'],2);
	else
		$arrays[$ref]['Umsatz/Neukunden'] = 0;
	if(!empty($array['Kunden Umsatz'])&&(!empty($array['Neukunden'])||!empty($array['Altkunden'])))
		$arrays[$ref]['Kundenwert'] = round($array['Kunden Umsatz']/($array['Neukunden']+$array['Altkunden']),2);
	else
		$arrays[$ref]['Kundenwert'] = 0;
		
	$ordervalues[$ref] = $arrays[$ref]['Umsatz'];
}
arsort($ordervalues);
foreach (array_keys($ordervalues) as $key)
{
	$data[] = $arrays[$key];
}


if(empty($_REQUEST['table'])&&!isset($csv))
{

}
else 
{
	if (!isset($csv))
	{
 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='referer_user'
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
}
?>