<?php
if (!defined('sAuthFile')) die();

$monate = $sLang["statistics"]["amount_week_month"];
if(empty($_REQUEST['range']))
	$_REQUEST['range'] = 0;
$weeks = $_REQUEST['range'];
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

$range = $_REQUEST['range'];

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
if(empty($_REQUEST['table'])) {
	$sort = "ASC";
}else {
	$sort = "DESC";
}

$sql = "
	SELECT 
		SUM($amount_1/currencyFactor) AS `Umsatz`,
		WEEK(ordertime, 3) AS `Woche`,
		ordertime
	FROM `s_order`
	WHERE 
		TO_DAYS(ordertime) >= TO_DAYS('$year-$month-$day')
	AND 
		TO_DAYS(ordertime) <= TO_DAYS('$year2-$month2-$day2')
	AND 
		status != 4
	AND
		status != -1
	GROUP BY 
		WEEK(ordertime, 3)
	ORDER BY WEEK(ordertime, 3) $sort";


$result = mysql_query($sql);

if (!$result)
	die();

$data = array();
while ($entry = mysql_fetch_assoc($result))
{
	$data[intval(date("W",strtotime($entry["ordertime"])))]["Woche"] = $entry['Woche'];
	$data[intval(date("W",strtotime($entry["ordertime"])))]["Umsatz"] = round($entry["Umsatz"],2);
}

if(empty($_REQUEST['table'])) {
?>

<?php header('Content-type: text/plain');?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>

<chart caption='<?php echo $sLang["statistics"]["amount_week_Turnover"] ?>' palette='2' showValues='1' numberPrefix="" decimals="2" formatNumberScale="0">	
	<categories>
<?php
foreach ($data as $key)
{?>
		<category label='<?php echo$key['Woche']?>' />
<?php }
?>
	</categories>
	<dataset>
<?php foreach ($data as $array) {?>
		<set value='<?php echo round($array['Umsatz'])?>' />
<?php }?>
	</dataset>
<?php //}?>
</chart>
<?php } else {
	if(!isset($csv)) {
		$data = array_values($data);
		 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_week'
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