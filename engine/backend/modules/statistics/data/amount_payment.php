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
	$amount_1 = "invoice_amount";
}else {
	$amount_1 = "invoice_amount_net";
}


$sql = "
	SELECT 
		COUNT(s_order.id) as `Bestellungen`,
		SUM($amount_1/currencyFactor) AS `Umsatz`,
		s_core_paymentmeans.description AS `Zahlungsart`
	FROM `s_order`
	LEFT JOIN s_core_paymentmeans ON s_order.paymentID = s_core_paymentmeans.id
	WHERE 
		TO_DAYS(ordertime) >= TO_DAYS('$year-$month-$day')
	AND 
		TO_DAYS(ordertime) <= TO_DAYS('$year2-$month2-$day2')
	AND 
		s_order.status != 4
	AND
		s_order.status != -1
	GROUP BY 
		 s_order.paymentID
	ORDER BY Umsatz DESC";


$result = mysql_query($sql);


while ($entry = mysql_fetch_assoc($result))
{
	$totalAmount += $entry["Umsatz"];	
}


if (!$result)
	die();
	
	
mysql_data_seek($result,0);
while ($entry = mysql_fetch_assoc($result))
{
	$entry["Zahlungsart"] = utf8_encode($entry["Zahlungsart"]);
	$entry["Prozent"] = round($entry["Umsatz"]/$totalAmount*100,2);
	$entry["Umsatz"] = round($entry["Umsatz"],2);
	//For CSV
	$data[] = $entry;
	$arrays[] = $entry;
	
}


if (empty($arrays))
	$arrays[0] = array("Anzahl"=>0,"Land"=>"");

if(empty($_REQUEST['table'])) 
{
?>
<chart palette='4'>
<?php
foreach ($arrays as $array)
{
?>	<set label='<?php echo html_entity_decode(utf8_decode($array['Zahlungsart']))?>' value='<?php echo$array['Umsatz']?>' />
<?php
}
?>
</chart>
<?php
}else {
	if (!isset($csv))
	{
		$data = $arrays;
		include("json.php");
		 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_payment'
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
