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


$total = "
	SELECT 
		COUNT(DISTINCT a.`id`) AS `total`,
		a.`name`,
		a.`id`
	FROM 
		s_emarketing_lastarticles as od,
		s_articles as a
	WHERE a.id=od.articleID
	AND
		time <='$jear-$mounth-$day 23:59:59'
	AND 
		time >= '$jear2-$mounth2-$day2'
";
$total = mysql_query($total);
$total = @mysql_result($total,0,0);

$sql = "
	SELECT 
		count(od.articleID) AS `impressions`,
		a.`name`,
		a.`id`
	FROM 
		s_emarketing_lastarticles as od,
		s_articles as a
	WHERE a.id=od.articleID
	AND
		time <='$jear-$mounth-$day 23:59:59'
	AND 
		time >= '$jear2-$mounth2-$day2'
	GROUP BY 
		od.articleID
	ORDER BY impressions DESC
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
	$headers = $sLang["statistics"]["article.views_header"];
	$data = $arrays;
	include("json.php");
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>array(),"totalCount"=>$total));
}
while ($entry = mysql_fetch_assoc($result))
{
	$sql = "
	SELECT 
		sum(od.quantity) AS `sales`
	FROM 
		s_order_details as od,
		s_order as o
	WHERE '{$entry['id']}'=od.articleID
	AND
		o.id=od.orderID
	AND
		o.status!=4
	AND
		o.status!=-1
	AND
		o.ordertime <='$jear-$mounth-$day 23:59:59'
	AND 
		o.ordertime >= '$jear2-$mounth2-$day2'
	GROUP BY articleID";

	$result2 = mysql_query($sql);
	if($result2)
		$entry2 = mysql_fetch_assoc($result2);
	if(empty( $entry2['sales']))
		$entry['sales'] = 0;
	else 
		$entry['sales'] = $entry2['sales'];
		
	if(!isset($csv)){
		$entry['name'] = utf8_encode($entry['name']);
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
 SELECT header FROM s_core_statistics WHERE file='article.views'
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
	if(empty($data))
		$data = array();
	else
		$data = array_values($data);
	include("json.php");
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>$total));

}
?>