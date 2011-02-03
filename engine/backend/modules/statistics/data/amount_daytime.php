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


if(!empty($_REQUEST['date']))
{
	$time = strtotime($_REQUEST['date']);
}
else
{
	$time = time();
}

list($day, $month, $year) = explode('-',date("d-m-Y",$time));

if ($_REQUEST["date2"]){
	list($day2, $month2, $year2) = explode('-',date("d-m-Y",strtotime($_REQUEST["date2"])));
}

$monate = $sLang["statistics"]["amount_daytime_month"];
$wochentage = $sLang["statistics"]["amount_daytime_day"];


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
			ROUND(SUM($amount_1/currencyFactor)/IF(DATEDIFF('$year2-$month2-$day2','$year-$month-$day'),DATEDIFF('$year2-$month2-$day2','$year-$month-$day'),1),2) AS `Umsatz`,
			HOUR(ordertime) as `Stunde`
		FROM 
			`s_order` AS o
		WHERE 
			TO_DAYS(o.ordertime) <= TO_DAYS('$year2-$month2-$day2')
		AND 
			TO_DAYS(o.ordertime) >= TO_DAYS('$year-$month-$day')
		AND 
			o.status != 4
		AND
			o.status != -1
		GROUP BY 
			`Stunde`
		ORDER BY `Stunde`
";

$result = mysql_query($sql);

if ((!$result || !mysql_num_rows($result)) && !$_REQUEST["table"] && !$_REQUEST["csv"])
	die('FAIL');
	
$data = array();
$entrys = array();
while ($entry = mysql_fetch_assoc($result))
{
	$entrys[$entry['Stunde']] = $entry;
}
for ($i=0;$i<24;$i++)
{
	$entrys[$i]['Stunde'] = $i.":00";
	
	if(empty($entrys[$i]['Umsatz']))
		$entrys[$i]['Umsatz'] = 0;
	$data[$i] = $entrys[$i];
}
if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>
<chart palette="2" decimals='2' xAxisName="<?php echo $sLang["statistics"]["amount_daytime_time"] ?>" yAxisName="<?php echo $sLang["statistics"]["amount_daytime_turnover"] ?>" caption="<?php echo $sLang["statistics"]["amount_daytime_turnover_time"] ?>" subCaption="<?php echo$caption?>" showValues="0" numberSuffix="">
<categories>
<?php foreach ($data as $key=>$dat) {?>
	<category label="<?php echo$dat['Stunde']?>"/>
<?php }?>
</categories>
<dataset>
<?php foreach ($data as $key =>$dat) {?>
	<set label="<?php echo$dat['Stunde']?>" value="<?php echo$dat['Umsatz']?>"/>
<?php }?>
</dataset>
</chart>
<?php } else {
	if (!isset($csv))
	{
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_daytime'
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
