<?php
if (!defined('sAuthFile')) die();

if(empty($_REQUEST['date']))
	$von = time();
else 
	$von = strtotime($_REQUEST["date"]);

if(empty($_REQUEST['date']))
	$bis = time();
else 
	$bis = strtotime($_REQUEST["date2"]);


if ($_REQUEST["tax"]!=1){
	$sql_amount = "invoice_amount";
} else {
	$sql_amount = "invoice_amount_net";
}

if (!empty($sCore->sCONFIG['sPREMIUMSHIPPIUNG']))
{
	$dispatch_table = 's_premium_dispatch';
}
else
{
	$dispatch_table = 's_shippingcosts_dispatch';
}

$sql = "
	SELECT 
		COUNT(s_order.id) as `Bestellungen`,
		SUM($sql_amount/currencyFactor) AS `Umsatz`,
		$dispatch_table.name AS `Versandart`
	FROM `s_order`
	LEFT JOIN $dispatch_table ON s_order.dispatchID  = $dispatch_table.id
	WHERE 
		ordertime <='".date("Y-m-d",$bis)." 23:59:59'
	AND 
		ordertime >= '".date("Y-m-d",$von)."'
	AND 
		s_order.status != 4
	AND
		s_order.status != -1
	GROUP BY 
		 s_order.dispatchID
	ORDER BY Umsatz DESC
";


$result = mysql_query($sql);


while ($entry = mysql_fetch_assoc($result))
{
	$totalAmount += $entry["Umsatz"];	
}


if (!$result)
	die();
	
	
mysql_data_seek($result,0);
$arrays = array();
while ($entry = mysql_fetch_assoc($result))
{
	$entry["Versandart"] = utf8_encode($entry["Versandart"]);
	$entry["Prozent"] = round($entry["Umsatz"]/$totalAmount*100,2);
	$entry["Umsatz"] = round($entry["Umsatz"],2);
	$arrays[] = $entry;
}

if(empty($_REQUEST['table'])) 
{
?>
<chart palette='4'>
<?php
foreach ($arrays as $array)
{
?>	<set label='<?php echo html_entity_decode(utf8_decode($array['Versandart']))?>' value='<?php echo$array['Umsatz']?>' />
<?php
}
?>
</chart>
<?php
}else {
	$data = $arrays;
	if (!isset($csv))
	{
		include("json.php");
		$getHeader = mysql_query("
			SELECT header FROM s_core_statistics WHERE file='amount_dispatch'
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
