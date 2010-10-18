<?php
if (!defined('sAuthFile')) die();

if (!isset($csv))
	$limit = 15;
else 
	$limit = 300;
	
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
	SELECT `value`
	FROM `s_core_config`
	WHERE (
	`s_core_config`.`name` ='sHOST'
	)
";
$result = mysql_query($sql);
$sHOST = mysql_fetch_row($result);
$sHOST = $sHOST[0];
$sql = "
SELECT 
		ROUND((s_order.invoice_amount),2) AS `Umsatz`,
		referer as `Referer`,
		s_user.id as `UserID`,
		date(s_user.firstlogin) as `Firstlogin`,
		date(s_order.ordertime) as `Ordertime`,
		of.ordertime as `Firstorder`
	FROM 
		`s_order`,`s_user`
	LEFT OUTER JOIN 
		(
			SELECT
				date(`ordertime`) as `ordertime`,
				`userID`
			FROM
				s_order
			GROUP BY
				`userID`
			ORDER BY 
				`ordertime` ASC
		) as `of`
	ON
		`of`.`userID`=`s_user`.id
	WHERE 
		s_order.status != 4
	AND
		s_order.status != -1
	AND 
		s_order.userID=s_user.id
	AND 
		s_order.ordertime >= '$jear2-$mounth2-$day2'
	AND 
		s_order.ordertime <= '$jear-$mounth-$day 23:59:59'
	AND 
		s_order.referer NOT LIKE 'http://www.$sHOST%'
	AND 
		s_order.referer NOT LIKE 'http://$sHOST%'
	AND 
		s_order.referer LIKE 'http%//%'	
	ORDER BY Umsatz";
$result = mysql_query($sql);
if (!$result)
	die('FAIL');
if(mysql_num_rows($result)==0)
	die('FAIL');

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
		$script = 
"Table.addEvent( 'afterRow', function(data, row){					
	row.cols[1].element.setStyle('cursor', 'pointer');
	row.cols[2].element.setStyle('cursor', 'pointer');
	row.cols[1].element.addEvent('click',function(){
		parent.parent.loadSkeleton('userdetails',false, row.cols[0].value);
	});
	row.cols[2].element.addEvent('click',function(){
		parent.parent.loadSkeleton('userdetails',false, row.cols[0].value);
	});
});";
		/*
		foreach (array_keys($data) as $key)
		{
			$data[$key]['Optionen'] = "";
		}
		*/
		$headers = $sLang["statistics"]["referer_user_old_header"];
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}
?>