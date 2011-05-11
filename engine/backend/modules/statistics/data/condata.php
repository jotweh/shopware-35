<?php
if (!defined('sAuthFile')) die();


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

if (!$_REQUEST["start"]){
	$start = 0;
}else {
	$start = $_REQUEST["start"];
}

if (!$_REQUEST["limit"]){
	$end = 100;
}else {
	$end = $_REQUEST["limit"];
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


if ($_REQUEST["group"]==1){
	$kw = true;
	foreach ($sLang["statistics"]["condata_array"] as $key => $value){
		if ($value["header"]=="Datum") $sLang["statistics"]["condata_array"][$key]["header"] = "KW";
	}
}

if ($kw){
	$group = "WEEK(v.datum,3)";
}else {
	$group = "TO_DAYS(v.datum)";
}
if(empty($_REQUEST['table'])) {
	$sort = "ASC";
}else {
	$sort = "DESC";
}
$sql = "
SELECT
   v.uniquevisits AS `Visits`,
   v.pageimpressions AS `Hits`,
   o.`Bestellungen` AS `Bestellungen`,
   b.`Abgebrochene Warenkoerbe` AS `Abgebrochene Warenkoerbe`,
   u.`Neukunden` AS `Neukunden`,
   ou.`Umsatz` AS `Umsatz`,
   ROUND(SUM(o.`Bestellungen`)/SUM(u.`Neukunden`)*100,2) as `Order Abandonment Rate`,
   ROUND(SUM(o.`Bestellungen`)/(SUM(b.`Abgebrochene Warenkoerbe`)+SUM(o.`Bestellungen`))*100,2) as `Basket Conversion Rate`,
   ROUND(SUM(o.`Bestellungen`)/SUM(v.uniquevisits)*100,2) as `Order Conversion Rate`,
   ROUND(SUM(b.`Abgebrochene Warenkoerbe`)/SUM(v.uniquevisits)*100,2) as `Basket/Visit Conversion Rate`,
   WEEK(v.datum,3) AS `Kalenderwoche`,
   YEAR(v.datum) AS `Jahr`,
   DATE_FORMAT(v.datum,'%d.%m.%Y') AS `Datum`,
   MONTH(v.datum) AS `Monat`,
   DAY(v.datum) AS `Tag`
 FROM
  `s_statistics_visitors` as v
 LEFT OUTER JOIN 
  (
   SELECT
    COUNT(DISTINCT id) AS `Bestellungen`, 
    DATE (ordertime) as `date`
   FROM
    `s_order`
   WHERE
    status != 4
   AND
    status != -1
   GROUP BY 
    DATE (ordertime) 
  ) as o
 ON
  `o`.`date`=v.datum
 LEFT OUTER JOIN 
  (
   SELECT
    SUM($amount_1/currencyFactor) AS `Umsatz`, 
    DATE (ordertime) as `date`
   FROM
    `s_order`
   WHERE
    status != 4
   AND
    status != -1
   GROUP BY 
    DATE (ordertime) 
  ) as ou
 ON
  `ou`.`date`=v.datum
 LEFT OUTER JOIN 
  (
   SELECT
    COUNT(DISTINCT  sessionID) AS `Abgebrochene Warenkoerbe`, 
    DATE (datum) as `date`
   FROM
    `s_order_basket`
   GROUP BY 
    DATE (datum) 
  ) as b
 ON
  `b`.`date`=v.datum
 LEFT OUTER JOIN 
  (
   SELECT
    COUNT(DISTINCT  id) AS `Neukunden`, 
    firstlogin as `date`
   FROM
    `s_user`
   GROUP BY 
    firstlogin
  ) as u
 ON
  `u`.`date`=v.datum
 WHERE 
  v.datum <= '$year2-$month2-$day2'
 AND 
  v.datum >= '$year-$month-$day'
 GROUP BY $group
 ORDER BY v.datum $sort
";

$totalCount = mysql_num_rows(mysql_query($sql));
if(!isset($csv)){
	$sql .="LIMIT $start, $end";
}

//die($sql);


$monate = $sLang["statistics"]["condata_month"];
$result = mysql_query($sql);

if (!$result)
	die('FAIL');
if (!mysql_num_rows($result))
	die('FAIL');
while ($entry = mysql_fetch_assoc($result))
{
	foreach ($entry as $key => $field) if (!$entry[$key]) $entry[$key] = "0";
	$entry["Umsatz"] = round($entry["Umsatz"],2);
	if ($kw){
		$entry["Datum"] = $entry["Kalenderwoche"];
		$data[$entry["Kalenderwoche"]] = $entry;
	}else {
		$entry['Datum2'] = $entry['Tag'].". ".$monate[intval($entry['Monat'])];
 		$data[$entry['Datum2']] = $entry;
	}
}
if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"
?>
<chart palette="2" decimals='2' caption="<?php echo $sLang["statistics"]["condata_conversion_data"] ?>" PYAxisName="<?php echo $sLang["statistics"]["condata_order_basket_newcustomer"] ?>" SYAxisName="<?php echo $sLang["statistics"]["condata_hits_visits"] ?>" showValues="0" numberSuffix="">
<categories>
<?php foreach(array_keys($data) as $value) {?>
	<category label='<?php echo$value?>' />
<?php }?>
</categories>
<dataset showValues="0" seriesName="<?php echo $sLang["statistics"]["condata_order"] ?>" parentYAxis="P">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Bestellungen']?>"/>
<?php }?>
</dataset>
<dataset showValues="0" seriesName="<?php echo $sLang["statistics"]["condata_basket"] ?>" parentYAxis="P">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Abgebrochene Warenkörbe']?>"/>
<?php }?>
</dataset>
<dataset showValues="0" seriesName="<?php echo $sLang["statistics"]["condata_new_customer"] ?>" parentYAxis="P">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Neukunden']?>"/>
<?php }?>
</dataset>
<dataset showValues="0" seriesName="<?php echo $sLang["statistics"]["condata_new_hit"] ?>" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Hits']?>"/>
<?php }?>
</dataset>
<dataset showValues="0" seriesName="<?php echo $sLang["statistics"]["condata_visits"] ?>" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Visits']?>"/>
<?php }?>
</dataset>
</chart>
<?php }
else {
 if(!isset($csv)){
  $data = array_values($data);
 // Get Header
 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='condata'
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
 
  //$headers = $sLang["statistics"]["condata_array"];
 
 
 include("json.php");
 $json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>$totalCount));
 }
}?>