<?php
if (!defined('sAuthFile')) die();

if(empty($_REQUEST['range']))
	$range = 14;
else 
	$range = $_REQUEST['range'];

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
$lastdate = date("d.m.Y",$lastday);
list($day, $mounth, $jear) = explode ('.',$lastdate);
if(empty($_REQUEST['date']))
{
	$firstday = mktime(0,0,0,$mounth,$day-$range,$jear);
}
else 
{
	list($td, $tm, $tj) = explode ('.',$_REQUEST['date']);
	$firstday = mktime(0,0,0,$tm,$td,$tj);
}
$firstdate = date("d.m.Y",$firstday);
list($day2, $mounth2, $jear2) = explode ('.',$firstdate);

$monate = $sLang["statistics"]["amount_weekday_month"];
$wochentage = $sLang["statistics"]["amount_weekday_days"];
//DAYOFWEEK(ordertime) as `Wochentag`
$anzwochentage = array();
$curdate = $firstday;
while ($curdate <= $lastday)
{
	$date = date("d.m.Y.w",$curdate);
	list($day3, $mounth3, $jear3, $week3) = explode('.',$date);
	$anzwochentage[$week3+1]++;
	$curdate = mktime(0, 0, 0, $mounth3, $day3+1, $jear3);
}

if ($_REQUEST["tax"]==1){
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
		DAYOFWEEK(`Order`.Tag) as `Wochentag`,
		SUM(`Order`.Umsatz) AS `Umsatz`,
		COUNT(*) as `Tage`
	FROM
	(
		SELECT
			SUM($amount_1/currencyFactor) AS `Umsatz`,
			DATE(ordertime) as `Tag`
		FROM 
			`s_order` AS o
		WHERE 
			TO_DAYS(o.ordertime) >= TO_DAYS('$jear2-$mounth2-$day2')
		AND 
			TO_DAYS(o.ordertime) <= TO_DAYS('$jear-$mounth-$day')
		AND 
			o.status != 4
		AND
			o.status != -1
		GROUP BY 
			TO_DAYS(ordertime)
	) AS `Order`
	GROUP BY
		DAYOFWEEK(`Order`.Tag)
	ORDER BY `Wochentag`
";

$result = mysql_query($sql);
if ((!$result || !mysql_num_rows($result)) && !$_REQUEST["table"] && !$_REQUEST["csv"])
	die('FAIL');

$data = array();
while ($entry = mysql_fetch_assoc($result))
{
	if(!empty($anzwochentage[$entry['Wochentag']]))
		$entry['Count'] = $anzwochentage[$entry['Wochentag']];
	else 
		$entry['Count'] = 0;
	if(!empty($entry['Umsatz'])&&!empty($entry['Count']))
		$entry['Umsatz'] = round($entry['Umsatz']/$entry['Count'],2);
	else 
		$entry['Umsatz'] = 0;
	$entry['Wochentag'] = $wochentage[intval($entry['Wochentag'])];
	$data[] = $entry;
}
if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>

<chart caption="<?php echo $sLang["statistics"]["amount_weekday_turnover_days"] ?>" xAxisName="" yAxisName="<?php echo $sLang["statistics"]["amount_weekday_turnover"] ?>" showValues="0" decimals="2" formatNumberScale="0" chartRightMargin="30">
<?php foreach ($data as $value) {?>
	<set label="<?php echo$value['Wochentag']?>" value="<?php echo$value['Umsatz']?>"/>
<?php }?>
</chart>
<?php } else {
	foreach ($data as $key => $dat)
	{
		$data[$key]['Beschreibung'] =  utf8_encode($dat['Beschreibung']); 
	}
	if (!isset($csv))
	{
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_weekday'
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
}?>
