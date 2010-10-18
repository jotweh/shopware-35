<?php
if (!defined('sAuthFile')) die();


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
	foreach ($sLang["statistics"]["conrate_header"] as $key => $value){
		if ($value["header"]=="Datum") $sLang["statistics"]["conrate_header"][$key]["header"] = "KW";
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
   SUM(v.uniquevisits) AS `Visits`,
   SUM(v.pageimpressions) AS `Hits`,
   o.`Bestellungen` AS `Bestellungen`,
   SUM(b.`Abgebrochene Warenkörbe`) AS `Abgebrochene Warenkörbe`, 
   SUM(u.`Neukunden`) AS `Neukunden`,
   ROUND(o.`Bestellungen`/SUM(u.`Neukunden`)*100,2) as `oar`,
   ROUND(o.`Bestellungen`/(SUM(b.`Abgebrochene Warenkörbe`)+SUM(o.`Bestellungen`))*100,2) as `bcr`,
   ROUND(o.`Bestellungen`/SUM(v.uniquevisits)*100,2) as `ocr`,
   ROUND(SUM(b.`Abgebrochene Warenkörbe`)/SUM(v.uniquevisits)*100,2) as `bvcr`,
   DATE_FORMAT(v.datum,'%d.%m.%Y') AS `Datum`,
   MONTH(v.datum) AS `Monat`,
   DAY(v.datum) AS `Tag`,
   WEEK(v.datum,3) AS `Kalenderwoche`
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
    COUNT(DISTINCT  sessionID) AS `Abgebrochene Warenkörbe`, 
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
 GROUP BY
 	$group
 ORDER BY v.datum $sort
";
$totalCount = mysql_num_rows(mysql_query($sql));
if(!isset($csv)){
	$sql .="LIMIT $start, $end";
}
$monate = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$result = mysql_query($sql);
if (!$result)
	die('FAIL');
if (!mysql_num_rows($result))
	die('FAIL');
while ($entry = mysql_fetch_assoc($result))
{
	if ($kw){
		$entry["Datum"] = $entry["Kalenderwoche"];
		$entry["Datum2"] = $entry["Kalenderwoche"];
	}else {
		$entry['Datum2'] = $entry['Tag'].". ".$monate[intval($entry['Monat'])];
	}
	if (!$entry["oar"]) $entry["oar"] = "0";
	if (!$entry["bcr"]) $entry["bcr"] = "0";
	if (!$entry["ocr"]) $entry["ocr"] = "0";
	if (!$entry["bvcr"]) $entry["bvcr"] = "0";
	
	
	unset($entry["Abgebrochene Warenkörbe"]);
 	$data[$entry['Datum2']] = $entry;
}
if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
?>
<chart palette="2" caption="Conversion-Tracking" subCaption="<?php echo'von '.$firstdate.' bis '.$lastdate?>" showValues="0" divLineDecimalPrecision="1" limitsDecimalPrecision="1" PYAxisName="Order Conversion Rate / Basket/Visit Conversion Rate" SYAxisName="Order Abandonment Rate/ Basket Conversion Rate" numberPrefix="" decimals="2" formatNumberScale="0">
<categories>
<?php foreach ($data as $value) {?>
	<category toolText="<?php echo$value['Datum2']?>" label="<?php echo$value['Datum2']?>"/>
<?php }?>
</categories>
<dataset seriesName="Order&nbsp;Abandonment&nbsp;Rate" renderAs="Area" parentYAxis="S" renderAs="COLUMN">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['oar']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Basket&nbsp;Conversion&nbsp;Rate" showValues="0" parentYAxis="S" renderAs="COLUMN">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['bcr']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Order&nbsp;Conversion&nbsp;Rate" showValues="0" parentYAxis="P" renderAs="LINE">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['ocr']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Basket/Visit&nbsp;Conversion&nbsp;Rate" showValues="0" parentYAxis="P" renderAs="LINE">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['bvcr']?>"/>
<?php }?>
</dataset>
</chart>
<?php }
else {
 if(!isset($csv)){
 $data = array_values($data);
  $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='conrate'
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
		$date = array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>$totalCount);
		
		echo $json->encode($date);
 }
}?>