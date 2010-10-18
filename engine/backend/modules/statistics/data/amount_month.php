<?php
if (!defined('sAuthFile')) die();

if(!empty($_REQUEST['date']))
{
	$time = date("Y",strtotime($_REQUEST['date']));
}
else
{
	$time = date('Y');
}
$time = date('Y');
$timeEnd = $time - 1;


$monate = $sLang["statistics"]["amount_month_month_Short"];

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
		MONTH(ordertime) AS `Monat`,
		YEAR(ordertime) AS `Jahr`
	FROM `s_order`
	WHERE 
		YEAR(ordertime) <= $time
	AND 
		YEAR(ordertime) >= $timeEnd
	AND 
		status != 4
	AND
		status != -1
	GROUP BY 
		YEAR(ordertime),MONTH(ordertime)
	ORDER BY YEAR(ordertime) DESC, MONTH(ordertime) ASC";

$result = mysql_query($sql);

if (!$result)
	die();

while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Jahr']][$entry['Monat']-1] = round($entry['Umsatz'],2);
}

if(empty($_REQUEST['table'])) {
?>
<chart caption='<?php echo $sLang["statistics"]["amount_month_turnover_month"] ?>' palette='1' showValues='1' yAxisValuesPadding='5'  numberPrefix="" decimals="2" formatNumberScale="0">	
	<categories>
<?php
foreach ($monate as $monat)
{
	echo "\t\t<category label='";
	echo $monat;
	echo "' />\n";
}
?>
	</categories>
<?php foreach ($arrays as $Jahr => $array) {?>
	<dataset seriesName="<?php echo$Jahr?>">
<?php foreach ($monate as $key => $monat) {?>
		<set value='<?php echo$array[$key]?>' />
<?php }?>
	</dataset>
<?php }?>
</chart>
<?php } else {
	foreach ($arrays as $Jahr => $array) {
		foreach ($monate as $key => $monat) {
			$dat['Monat'] = utf8_encode($monat);
			$dat['Jahr'] = $Jahr;
			$dat['Umsatz'] = round($array[$key],2);
			if(empty($dat['Umsatz']))
				$dat['Umsatz'] = 0;
			$data[] = $dat;
		}
	}
	
	if (!isset($csv))
	{
	include("json.php");
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_month'
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
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}?>