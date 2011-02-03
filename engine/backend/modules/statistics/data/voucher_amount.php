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
	LIMIT 0 , 100
	";
$sql = "
SELECT 
	od.`price` as `Gutscheinwert`,
	o.invoice_amount as `Umsatz`,
	DATE_FORMAT(ordertime,'%d.%m.%Y') AS `Datum`,
	v.vouchercode
FROM 
	`s_order_details` as od,
	s_order as o,
	s_emarketing_vouchers as v
WHERE
	od.`orderID` = o.id
AND
	od.`modus` =2
AND 
	o.status != 4
AND
	o.status != -1
AND
	od.articleordernumber=v.ordercode
AND
	ordertime <='$jear-$mounth-$day 23:59:59'
AND 
	ordertime >='$jear2-$mounth2-$day2'
ORDER BY ordertime DESC";

$result = mysql_query($sql);

if (!$result)
{
	echo "FAIL"; die();
}

while ($entry = mysql_fetch_assoc($result))
{

	if(empty($entry['vouchercode']))
	{
		$entry['vouchercode'] = "Unbekannt";
	}
		
	$data[$entry['Datum']."-".$entry['vouchercode']]['Umsatz'] += round($entry['Umsatz'],2);
	$data[$entry['Datum']."-".$entry['vouchercode']]['Gutscheinwert'] -= $entry['Gutscheinwert'];
	$data[$entry['Datum']."-".$entry['vouchercode']]['Gutscheincode'] = $entry['vouchercode'];
	$data[$entry['Datum']."-".$entry['vouchercode']]['Datum'] = $entry['Datum'];
	$data[$entry['Datum']."-".$entry['vouchercode']]['Einkäufe']++;
}
/*
arsort($rate);
foreach ($rate as $key => $value) {
	$data[] = $ret3[$key];
}*/

if(!isset($csv))
{
	$headers = 
	$script = 
"Table.addEvent( 'afterRow', function(data, row){					
	row.cols[1].element.setStyle('cursor', 'pointer');
	row.cols[1].element.addEvent('click',function(){
		parent.parent.loadSkeleton('articles',false, '{article:'+row.cols[0].value+'}');
	});
});";
	if(!isset($csv))
		$data = array_values($data);
	include("json.php");
	$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));

}
?>