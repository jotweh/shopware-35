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

$monate = $sLang["statistics"]["basket_relative_month"];
$sql = "	
	SELECT 
		DATE_FORMAT(datum,'%d.%m.%Y') AS `Datum`,
		COUNT(*) AS `Warenkoerbe`
	FROM `s_order_basket`
	WHERE 
		datum <='$jear-$mounth-$day 23:59:59'
	AND 
		datum >= '$jear2-$mounth2-$day2'
	GROUP BY 
		DATE_FORMAT(datum,'%d.%m.%Y')
	ORDER BY `Datum` ASC";
$result = mysql_query($sql);
if (!$result)
	die('FAIL');
if(mysql_num_rows($result)==0)
	die('FAIL');
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Datum']]['Warenkoerbe'] = $entry['Warenkoerbe'];
}
$sql = "
	SELECT 
		DATE_FORMAT(s.datum,'%d.%m.%Y') AS `Datum`,
		SUM(s.uniquevisits) AS `Visits`
	FROM 
		`s_statistics_visitors` as s
	WHERE 
		s.datum <= '$jear-$mounth-$day'
	AND 
		s.datum >= '$jear2-$mounth2-$day2'
	GROUP BY 
		DATE_FORMAT(s.datum,'%d.%m.%Y')
	ORDER BY `Datum` ASC";
$result = mysql_query($sql);
if (!$result)
	die('FAIL');
if(mysql_num_rows($result)==0)
	die('FAIL');
	
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Datum']]['Visits'] = $entry['Visits'];
}

$curdate = $firstday;
while ($curdate <= $lastday)
{
	$date = date("d.m.Y",$curdate);
	list($day3, $mounth3, $jear3) = explode('.',$date);
	$set = $arrays[$date];
	if(empty($set['Datum']))
		$set['Datum'] = $date; 
	if(empty($set['Visits']))
		$set['Visits'] = 0;
	if(empty($set['Warenkoerbe']))
		$set['Warenkoerbe'] = 0;
	
	if(!empty($set['Warenkoerbe'])&&!empty($set['Visits']))
		$set['Rate']	= round($set['Warenkoerbe']/$set['Visits']*100,2);
	else 
		$set['Rate'] = 0;
	$d = intval($day3).'. '.$monate[intval($mounth3)];
		
	$data[$d] = array("Datum2" => $set['Datum'],"Datum" => $d,"Warenkoerbe" => $set['Warenkoerbe'],"Visits" => $set['Visits'],"Rate" => $set['Rate']);
	$curdate = mktime(0, 0, 0, $mounth3, $day3+1, $jear3);
}

if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>
<chart palette="2" decimals='2' caption="Basket/Visit Conversion Rate" subCaption="<?php echo'von '.date('d.m.Y',$firstday).' bis '.date('d.m.Y',$lastday).''?>" showValues="0" numberSuffix="%">
<categories>
<?php foreach($data as $dat) {?>
	<category label='<?php echo $dat['Datum']?>' />
<?php }?>
</categories>
<dataset showValues="0" renderAs="Area">
<?php foreach ($data as $key =>$dat) {?>
	<set label="<?php echo $key?>" value="<?php echo $dat['Rate']?>"/>
<?php }?>
</dataset>
</chart>
<?php }
else {
	if(!isset($csv)){
		$data = array_values($data);
		$headers = $sLang["statistics"]["basket_relative_header"];
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}?>
