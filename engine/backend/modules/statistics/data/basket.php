<?php
if (!defined('sAuthFile')) die();

$monate = $sLang["statistics"]["basket_month"];
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
		DATE_FORMAT( datum, '%Y-%m-%d' ) AS `deDatum`
	FROM `s_order_basket`
	WHERE 
		TO_DAYS(datum) <= TO_DAYS('$jear-$mounth-$day')
	AND 
		TO_DAYS(datum) >= TO_DAYS('$jear2-$mounth2-$day2')
	GROUP BY 
		`deDatum`
	ORDER BY datum DESC
";

$result = mysql_query($sql);
if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	//		DATE_FORMAT(datum,'%d.%m.%Y') AS `deDatum`
	$sql = "
	SELECT 
		MONTH(datum) AS `Monat`,
		DAY(datum) AS `Tag`
	FROM `s_order_basket`
	WHERE 
		date(datum) = '{$entry['deDatum']}'
	GROUP BY 
		sessionID
	ORDER BY datum DESC";
	$result2 = mysql_query($sql);
	$entry2 = mysql_fetch_assoc($result2);
	$entry2['Basket'] = mysql_num_rows($result2);
	$arrays[$entry2['Tag'].'. '.$monate[$entry2['Monat']]]['Basket']= $entry2['Basket'];
	$arrays[$entry2['Tag'].'. '.$monate[$entry2['Monat']]]['Datum']= $entry['deDatum'];
}
$sql = "
	SELECT 
		MONTH(s.datum) AS `Monat`,
		DAY(s.datum) AS `Tag`,
		SUM(s.pageimpressions) AS `Hits`,
		SUM(s.uniquevisits) AS `Visits`
	FROM 
		`s_statistics_visitors` as s
	WHERE 
		TO_DAYS(s.datum) <= TO_DAYS('$jear-$mounth-$day')
	AND 
		TO_DAYS(s.datum) >= TO_DAYS('$jear2-$mounth2-$day2')
	GROUP BY 
		DAY(s.datum)
	ORDER BY s.datum DESC";

$result = mysql_query($sql);
if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Tag'].'. '.$monate[$entry['Monat']]]['Hits']= $entry['Hits'];
	$arrays[$entry['Tag'].'. '.$monate[$entry['Monat']]]['Visits']= $entry['Visits'];
	//$arrays[$entry['Woche']]['Visits'] = $entry['Visits'];
}

$sql = "
	SELECT 
		COUNT(invoice_amount) AS `Bestellungen`,
		DAY(ordertime) AS `Tag`,
		MONTH(ordertime) AS `Monat`
	FROM `s_order`
	WHERE 
		TO_DAYS(ordertime) <= TO_DAYS('$jear-$mounth-$day')
	AND 
		TO_DAYS(ordertime) >= TO_DAYS('$jear2-$mounth2-$day2')
	AND 
		status != 4
		
	AND
		status != -1
	GROUP BY 
		DAY(ordertime),MONTH(ordertime)
	ORDER BY ordertime DESC";

$result = mysql_query($sql);
if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Tag'].'. '.$monate[$entry['Monat']]]['Bestellungen']= $entry['Bestellungen'];
}
$curdate = $lastday;
while ($curdate >= $firstday)
{
	list($day3, $mounth3, $mounth4, $jear3) = split('-',date("d-n-m-Y",$curdate));
	$day4 = $day3;
	if ($day3<10)
		$day3 = intval($day3);
	$d = $day3.'. '.$monate[$mounth3];
	$set = $arrays[$d];
	if(empty($set['Hits']))
		$set['Hits'] = 0;
	if(empty($set['Visits']))
		$set['Visits'] = 0;
	if(empty($set['Bestellungen']))
		$set['Bestellungen'] = 0;
	if(empty($set['Basket']))
		$set['Basket'] = 0;

	/*
	if(!empty($set['Basket'])&&!empty($set['Visits']))
		$set['Rate']	= round($set['Basket']/$set['Visits']*100,2);
	else 
		$set['Rate'] = 0;
	*/
		
	$data[$d] = array("Datum" => "$day4.$mounth4.$jear3","Woche" => $d,"Basket" => $set['Basket'],"Visits" => $set['Visits'],"Tag" => $set['Tag'],"Hits" => $set['Hits'],"Bestellungen" => $set['Bestellungen'],"Rate" => $set['Rate']);
	$curdate = mktime(0, 0, 0, $mounth3, $day3-1, $jear3);
}
$data = array_reverse($data,true);
/*
for ($i = 14; $i>0;$i--)
{
	list($day2, $mounth2) = split('-',date("d-n", mktime(0, 0, 0, $mounth, $day-$i, $jear)));
	if ($day2<10)
		$day2 = intval($day2);
	$d = $day2.'. '.$monate[$mounth2];
	if ($i == $days)
		$l = $d;
	$set = $arrays[$d];
	$data[$d] = array("Woche" => $d,"Basket" => $set['Basket'],"Visits" => $set['Visits'],"Tag" => $set['Tag'],"Hits" => $set['Hits'],"Bestellungen" => $set['Bestellungen']);
}*/

if(empty($_REQUEST['table']))
{
header('Content-type: text/plain');
//print_r($arrays);
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>


<chart palette="2" decimals='2' caption="Abgebrochene Warenkörbe" PYAxisName="Bestellungen/Warenkörbe" SYAxisName="Hits/Visits" showValues="0" numberSuffix="">
<categories>
<?php foreach(array_keys($data) as $value) {?>
	<category label='<?php echo$value?>' />
<?php }?>
</categories>
<dataset showValues="0" seriesName="Bestellungen" parentYAxis="P">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Bestellungen']?>"/>
<?php }?>
</dataset>
<dataset showValues="0" seriesName="Warenkörbe" parentYAxis="P">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Basket']?>"/>
<?php }?>
</dataset>
<dataset showValues="0" seriesName="Hits" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Hits']?>"/>
<?php }?>
</dataset>
<dataset showValues="0" seriesName="Visits" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Visits']?>"/>
<?php }?>
</dataset>
</chart>
<?php
}
else {
	if (!isset($csv)){

		$data = array_values($data);
		$headers = $sLang["statistics"]["basket_headers"];
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}
?>