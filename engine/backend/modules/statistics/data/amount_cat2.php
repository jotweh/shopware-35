<?php
if (!defined('sAuthFile')) die();

$node = intval($_REQUEST['node']);

if(empty($_REQUEST['range']))
	$_REQUEST['range'] = 4;

if ($_REQUEST["tax"]==1){
	$brutto = false;	
}else {
	$brutto = true;
}


if ($brutto){
	$amount_1 = "ROUND(SUM(a.price*a.quantity)/o.currencyFactor,2) AS `Umsatz`,";
}else {
	$amount_1 = "ROUND(SUM((a.price/(100+tax)*100)*a.quantity)/o.currencyFactor,2) AS `Umsatz`,";
}

if(empty($_REQUEST['date']))
{
	list($year,$month,$day) = explode("-",date("Y-m-d"));
}
else 
{
	list($year,$month,$day) = explode("-",date("Y-m-d",strtotime($_REQUEST["date"])));
}


if(empty($_REQUEST['date']))
{
	list($year2,$month2,$day2) = explode("-",date("Y-m-d"));
}
else 
{
	list($year2,$month2,$day2) = explode("-",date("Y-m-d",strtotime($_REQUEST["date2"])));
}


if(empty($node))
	$node="1";
	
$monate = $sLang["statistics"]["amount_cat2_array_monath_short"];

$sql = "
	SELECT
		$amount_1
		c.description AS `Beschreibung`
	FROM 
		`s_order_details` AS a,
		`s_categories` AS c,
		s_core_tax AS taxtab,
		`s_order` AS o
	JOIN 
	(
		SELECT DISTINCT
			articleID, categoryID
		FROM 
			s_articles_categories
	) as `ac`
	WHERE 
	  o.ordertime <= '$year2-$month2-$day2'
	 AND 
	  o.ordertime >= '$year-$month-$day'
	AND 
		a.orderID=o.id
	AND 
		o.status != 4
	AND
		o.status != -1
	AND
		a.taxID = taxtab.id
	AND 
		a.articleID=ac.articleID
	AND
		(c.parent = $node)
	AND
		c.id=ac.categoryID
	GROUP BY 
		(c.id)
	ORDER BY (ordertime) ASC";


$result = mysql_query($sql);
if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	$data[] = $entry;
}
if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>

<chart caption="" palette='2' showBorder='1' showValues='1' numberPrefix="" decimals="2" formatNumberScale="0">

<?php foreach ($data as $value) {?>
	<set label="<?php echo$value['Beschreibung']?>" value="<?php echo$value['Umsatz']?>"/>
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
 SELECT header FROM s_core_statistics WHERE file='amount_cat2'
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
