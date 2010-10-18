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
	SELECT ROUND((`sales`/`impressions`) * 100,2) as `Scoring`, `sales`, `impressions`,`aid`,`aname`,`ordernumber`
	FROM
		(
			SELECT 
				sum(odd.quantity) AS `sales`, 
				odd.articleID AS `SalesA`,
				odd.articleordernumber AS `ordernumber`,
				a.`id` as `aid`,
				odd.`name` as `aname`
			FROM 
				s_order_details as odd,
				s_articles as a,
				s_order as o
			WHERE
				a.id=odd.articleID
			AND
				o.id=odd.orderID
			AND
				o.ordertime <='$jear-$mounth-$day 23:59:59'
			AND 
				o.ordertime >= '$jear2-$mounth2-$day2'
			AND 
				o.status != 4
			AND 
				o.status != -1
			GROUP BY odd.articleordernumber
		) AS `od`,
		(
			SELECT 
				count(articleID) AS `impressions`,
				articleID AS `ViewsA`
			FROM 
				s_emarketing_lastarticles 
			WHERE
				`time` <='$jear-$mounth-$day 23:59:59'
			AND 
				`time` >= '$jear2-$mounth2-$day2'
			GROUP BY 
				articleID
		) AS `el`,
		s_articles as A
	WHERE 
		(`impressions` IS NULL  AND A.id = `SalesA`)
	OR 
		(A.id = `ViewsA` AND `sales` IS NULL)
	OR 
		(A.id = `ViewsA` AND A.id = `SalesA`)
	ORDER BY `Scoring` DESC, `sales` DESC
	LIMIT 0 , 50";
/*$sql2 = "
	SELECT 
		a.id,
		d.impressions, 
		d.sales,
		d.ordernumber,
		a.`name`,
		ROUND(d.sales / d.impressions * 100,2) AS `Scoring`
	FROM 
		`s_articles_details` as d, `s_articles` as a
	WHERE
		d.articleID=a.id
	ORDER BY `Scoring` DESC, d.impressions DESC";*/
$result = mysql_query($sql);

if (!$result)
{
	echo "FAIL"; die();
}

while ($entry = mysql_fetch_assoc($result))
{
	if(empty($entry['Scoring']))
		$entry['Scoring'] = 0;
	$entry['id'] = $entry['aid'];
	$entry['name'] = $entry['aname'];
	if(!isset($csv)){
		$entry['Scoring'] .= " %";
		$entry['name'] = utf8_encode($entry['name']);
	}
	$data[] = $entry;
}

if(!isset($csv))
{
	$headers = $sLang["statistics"]["article.views.sales_header"];
	$script = 
"Table.addEvent( 'afterRow', function(data, row){					
	row.cols[1].element.setStyle('cursor', 'pointer');
	row.cols[1].element.addEvent('click',function(){ 
		parent.parent.loadSkeleton('articles',false, '{article:'+row.data.id+'}');
	});
	row.cols[0].element.setStyle('cursor', 'pointer');
	row.cols[0].element.addEvent('click',function(){ 
		parent.parent.loadSkeleton('articles',false, '{article:'+row.data.id+'}');
	});
});";


	$data = array_values($data);
	include("json.php");
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));

}
?>