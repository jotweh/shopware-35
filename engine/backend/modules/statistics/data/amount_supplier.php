<?php
if (!defined('sAuthFile')) die();

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

if ($_REQUEST["tax"]==1){
	$brutto = false;	
}else {
	$brutto = true;
}


if ($brutto){
	$amount_1 = "SUM((s_order_details.price*s_order_details.quantity)/currencyFactor) AS `Umsatz`";
}else {
	$amount_1 = "SUM(((s_order_details.price/(100+tax)*100)*s_order_details.quantity)/currencyFactor) AS `Umsatz`";
}


$sql = "
	SELECT 
		$amount_1,
		s_articles_supplier.name AS `Hersteller`
	FROM `s_order`
	LEFT JOIN s_order_details ON s_order_details.orderID = s_order.id AND modus=0
	LEFT JOIN s_articles ON s_articles.id = s_order_details.articleID
	LEFT JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID,
	s_core_tax
	WHERE 
		TO_DAYS(ordertime) >= TO_DAYS('$year-$month-$day')
	AND 
		TO_DAYS(ordertime) <= TO_DAYS('$year2-$month2-$day2')
	AND 
		s_order.status != 4
	AND
		s_order.status != -1
	AND s_order_details.taxID = s_core_tax.id
	GROUP BY 
		 s_articles_supplier.id
	ORDER BY Umsatz DESC";
   




$result = mysql_query($sql);


while ($entry = mysql_fetch_assoc($result))
{
	$totalAmount += $entry["Umsatz"];	
}


if (!$result)
	die();
	
$arrays = array();

mysql_data_seek($result,0);
while ($entry = mysql_fetch_assoc($result))
{
	$csvEntry["Hersteller"] = $entry["Hersteller"];
	$entry["Hersteller"] = utf8_encode($entry["Hersteller"]);
	
	$csvEntry["Prozent"] = round($entry["Umsatz"]/$totalAmount*100,3);
	$entry["Prozent"] = round($entry["Umsatz"]/$totalAmount*100,2);
	
	$entry["Umsatz"] = round($entry["Umsatz"],2);
	$csvEntry["Umsatz"] = $entry["Umsatz"];
	
	$arrays[] = $entry;
	$data[] = $csvEntry;
}


if(empty($_REQUEST['table'])) {
?>
<chart caption='<?php echo $sLang["statistics"]["amount_supplier"] ?>' palette='1' showValues='1' yAxisValuesPadding='5'  numberPrefix="" decimals="2" formatNumberScale="0">	
	<categories>
<?php
foreach ($arrays as $array)
{
	echo "\t\t<category label='";
	echo $array["Hersteller"];
	echo "' />\n";
}
?>
	</categories>
	<dataset>
<?php foreach ($arrays as $array) {?>
		<set value='<?php echo$array["Umsatz"]?>' />
<?php }?>
	</dataset>

</chart>
<?php } else {
	
	
	if (!isset($csv))
	{
	include("json.php");
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_supplier'
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
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$arrays,"totalCount"=>count($data)));
	}
}?>