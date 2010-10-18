<?php
if (!defined('sAuthFile')) die();

if(empty($_REQUEST['range']))
	$range = 14;
else 
	$range = $_REQUEST['range'];

if(empty($_REQUEST['date2']))
{
	$lastday = time();
}
else 
{
	list($td, $tm, $tj) = explode('.',$_REQUEST['date2']);
	$lastday = mktime(0,0,0,$tm,$td,$tj);
}
list($day, $mounth, $jear) = explode ('-',date("d-m-Y",$lastday));
if(empty($_REQUEST['date']))
{
	$firstday = mktime(0,0,0,$mounth,$day-$range,$jear);
}
else 
{
	list($td, $tm, $tj) = explode ('.',$_REQUEST['date']);
	$firstday = mktime(0,0,0,$tm,$td,$tj);
}
list($day2, $mounth2, $jear2) = explode ('-',date("d-m-Y",$firstday));


if (!$_REQUEST["start"]){
	$start = 0;
}else {
	$start = $_REQUEST["start"];
}

if (!$_REQUEST["limit"]){
	$ende = 100;
}else {
	$ende = $_REQUEST["limit"];
}

$total = mysql_query("
SELECT 
		sum(od.quantity) AS `sales`, 
		a.`name`,
		od.articleordernumber,
		a.`id`
	FROM 
		s_order_details as od,
		s_articles as a,
		s_order as o
	WHERE 
		a.id=od.articleID
	AND
		o.id=od.orderID
	AND
		o.ordertime <='$jear-$mounth-$day 23:59:59'
	AND 
		o.ordertime >= '$jear2-$mounth2-$day2'
	AND 
		o.status != 4
	AND
		o.status != -1
	GROUP BY a.`id`
	ORDER BY Sales DESC
");
$total = @mysql_num_rows($total);


$sql = "
	SELECT 
		sum(od.quantity) AS `sales`, 
		a.`name`,
		od.articleordernumber,
		a.`id`
	FROM 
		s_order_details as od,
		s_articles as a,
		s_order as o
	WHERE 
		a.id=od.articleID
	AND
		o.id=od.orderID
	AND
		o.ordertime <='$jear-$mounth-$day 23:59:59'
	AND 
		o.ordertime >= '$jear2-$mounth2-$day2'
	AND 
		o.status != 4
	AND
		o.status != -1
	GROUP BY a.`id`
	ORDER BY Sales DESC
	";
if(!isset($csv)){
	$sql .="LIMIT $start, $ende";
}
$result = mysql_query($sql);

if (!$result)
{
	echo "FAIL"; die();
}
if (!@mysql_num_rows($result) && $_REQUEST["table"]){
	include("json.php");
	$json = new Services_JSON();
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>array(),"columns"=>array(),"totalProperty"=>"totalCount"),"rows"=>array(),"totalCount"=>0));
}
$data = array();
while ($entry = mysql_fetch_assoc($result))
{
	$sql = "
	SELECT 
		count(articleID) AS `Views`
	FROM 
		s_emarketing_lastarticles
	WHERE 
		articleID = {$entry['id']}
	AND
		time <='$jear-$mounth-$day 23:59:59'
	AND 
		time >= '$jear2-$mounth2-$day2'
	GROUP BY 
		articleID
	";
	$result2 = mysql_query($sql);
	$Views = mysql_fetch_row($result2);
	if(empty( $Views[0]))
	{
		$entry['impressions'] = 0;
	}
	else 
	{
		$entry['impressions'] = $Views[0];
	}
	if(!isset($csv)){
		$entry['name'] = utf8_encode($entry['name']);
		$entry['articleordernumber'] = utf8_encode($entry['articleordernumber']);
	}
	$data[] = $entry;
}
/*
arsort($rate);
foreach ($rate as $key => $value) {
	$data[] = $ret3[$key];
}*/

if(!isset($csv))
{
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='article.sales'
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
	

	$data = array_values($data);
	include("json.php");
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>$total));

}
?>