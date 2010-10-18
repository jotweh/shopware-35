<?php
if (!defined('sAuthFile')) die();

if(!empty($_REQUEST['date']))
	$time = strtotime($_REQUEST['date']);
else 
	$time = time();
if(empty($_REQUEST['range']))
	$_REQUEST['range'] = 4;
$weeks = $_REQUEST['range'];
//von

list($day, $mounth, $jear, $week) = split('-',date("d-n-Y-W",$time));
//bis
$tmp = mktime(0, 0, 0, $mounth, $day-($weeks*7), $jear);
list($day2, $mounth2, $jear2, $week2) = split('-',date("d-n-Y-W",$tmp));


$monate = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sql = "
	SELECT 
		ROUND(SUM(o.invoice_amount / o.currencyFactor),2) AS `Umsatz`,
		WEEK(o.ordertime, 1) AS `Woche`
	FROM 
		`s_order` AS o
	WHERE 
		o.ordertime <= '$jear-$mounth-$day 23:59:59'
	AND 
		o.ordertime >= '$jear2-$mounth2-$day2'
	AND 
		o.status != 4
	AND
		o.status != -1
	GROUP BY 
		WEEK(o.ordertime, 1)
	ORDER BY o.ordertime ASC";

$result = mysql_query($sql);

if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Woche']]['Umsatz'] = $entry['Umsatz'];
}
$sql = "
	SELECT 
		WEEK(s.datum, 3) AS `Woche`,
		SUM(s.pageimpressions) AS `Hits`,
		SUM(s.uniquevisits) AS `Visits`
	FROM 
		`s_statistics_visitors` as s
	WHERE 
		s.datum <= '$jear-$mounth-$day 23:59:59'
	AND 
		s.datum >= '$jear2-$mounth2-$day2'
	GROUP BY 
		WEEK(s.datum, 3)
	ORDER BY s.datum ASC";
$result = mysql_query($sql);
if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Woche']]['Hits'] = $entry['Hits'];
	$arrays[$entry['Woche']]['Visits'] = $entry['Visits'];
}
for($i = $weeks; $i>=0; $i--) {
	$w7 = date("W",mktime(0, 0, 0, $mounth, $day-($i*7), $jear));
	if($w7<10)
		$w7 = "0".$w7;
	$data[$w7]['Hits'] = $arrays[$w7]['Hits'];
	$data[$w7]['Visits'] = $arrays[$w7]['Visits'];
	$data[$w7]['Woche'] = $w7;
	$data[$w7]['Umsatz'] = $arrays[$w7]['Umsatz'];
}

if(empty($_REQUEST['table']))
{
header('Content-type: text/xml');
?>


<chart palette="2" caption="Umsatz / Besucher" subCaption="<?php echo($week2).' '.$sLang["statistics"]["amount_until"].' '.($week).' '.$sLang["statistics"]["amount_Calendar"]?>" showValues="0" divLineDecimalPrecision="1" limitsDecimalPrecision="1" DYAxisName="Visits" PYAxisName="Umsatz" SYAxisName="Anzahl" numberPrefix="" decimals="2" formatNumberScale="0">
<categories>
<?php foreach ($data as $key=>$value) {?>
	<category label="<?php echo$value['Woche']?>"/>
<?php }?>
</categories>
<dataset seriesName="<?php echo $sLang["statistics"]["amount_turnover"] ?>" renderAs="Area" parentYAxis="P">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Umsatz']?>"/>
<?php }?>
</dataset>
<dataset seriesName="<?php echo $sLang["statistics"]["amount_hits"] ?>" showValues="0" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Hits']?>"/>
<?php }?>
</dataset>
<dataset seriesName="<?php echo $sLang["statistics"]["amount_visits"] ?>" showValues="0" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Visits']?>"/>
<?php }?>
</dataset>
</chart>

<?php
}
else
{
	if (!isset($csv))
	{
		$data = array_values($data);
		$headers = array(
			array("text"=>"Woche","key"=>"Woche","fixedWidth"=>true,"defaultWidth"=>"50px","numeric"=>true),
			array("text"=>"Umsatz","key"=>"Umsatz","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true),
			array("text"=>"Hits","key"=>"Hits","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true),
			array("text"=>"Visits","key"=>"Visits","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true)
		);
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}
?>