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

$monate = $sLang["statistics"]["referer_google_month"];

$sql = "
	SELECT `value`
	FROM `s_core_config`
	WHERE (
	`s_core_config`.`name` ='sHOST'
)";
$result = mysql_query($sql);
$sHOST = mysql_fetch_row($result);
$sHOST = $sHOST[0];
$sql = "
	SELECT 
		COUNT(referer) AS `Count`,
		referer
	FROM `s_statistics_referer`
	WHERE 
		datum >= '$jear2-$mounth2-$day2'
	AND 
		datum <= '$jear-$mounth-$day'
	AND
		referer NOT LIKE '%$sHOST%'
	AND
		referer NOT LIKE '%uos-test.com%'
	AND
	(
			referer LIKE 'http%//www.google%'
		OR
			referer LIKE 'http%//google%'
	)
	GROUP BY 
		referer
	ORDER BY `Count` ASC";
$result = mysql_query($sql);
if (!$result)
	die();

while ($entry = mysql_fetch_assoc($result))
{
	preg_match("#[?&]([qp]|query|encquery|url|as_q)=([^&]+)&#",$entry['referer']."&",$match);
	if(!$match)
	{
		$entrys[$entry['referer']] = $match;
	}
	//$match[3] = $entry['referer'];
	$match[2] = urldecode(strtolower($match[2]));
	$match[2] = str_replace("+"," ",$match[2]);
	$match[2] = trim(preg_replace('/\s\s+/', ' ', $match[2]));
	//$match[1] = trim(str_replace(array('+++', '++', '+'),' ',));
	//$matchs[] = $match[2];
	if(!empty($match[2]))
		$arrays[$match[2]]++;	
}
if(isset($_REQUEST['test']))
{
	print_r($entrys);
	exit();
}
if (empty($arrays))
	$arrays["Keine Vorhanden"] = 0;
else
	arsort($arrays);

if(empty($_REQUEST['table']))
{
	$arrays = array_slice($arrays, 0, 10);
	header('Content-type: text/plain');
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>
<chart caption="Referer" showValues="0" decimals="0" formatNumberScale="0" chartRightMargin="30">
<?php
foreach ($arrays as $ref => $count)
{
?>	<set label='<?php echo$ref?>' value='<?php echo$count?>' link=''/>
<?php
}
?>
</chart>
<?php
}
else 
{
	$arrays = array_slice($arrays, 0, 300);
	foreach ($arrays as $ref => $count)
	{
		$data[] = array("request"=>$ref, "count"=>$count);
	}
	if(!isset($csv))
	{
		$headers = $sLang["statistics"]["referer_google_header"];
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}
?>