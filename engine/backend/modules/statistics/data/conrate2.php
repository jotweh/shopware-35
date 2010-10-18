<?php
if (!defined('sAuthFile')) die();

$monate = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");

if(empty($_REQUEST['range']))
	$_REQUEST['range'] = 7;
$weeks = $_REQUEST['range'];

if(!empty($_REQUEST['dateKW']))
{
	$time = strtotime($_REQUEST['dateKW']);
	
}
else
{

	$time = time();
}
list($day, $mounth, $jear, $week) = split('-',date("d-n-Y-W",$time));

$tmp = mktime(0, 0, 0, $mounth, $day-($weeks*7), $jear);
list($day2, $mounth2, $jear2, $week2, $weekday2) = explode('-',date("d-n-Y-W-w",$tmp));
$weekday2 = strtr($weekday2, "0", "7");
$tmp = mktime(0,0,0,$mounth2,$day2-$weekday2,$jear2);
list($day2, $mounth2, $jear2, $week2, $weekday2) = explode('-',date("d-n-Y-W-w",$tmp));

$sql = "
 SELECT
   SUM(v.uniquevisits) AS `Visits`,
   SUM(v.pageimpressions) AS `Hits`,
   SUM(o.`Bestellungen`) AS `Bestellungen`,
   SUM(b.`Abgebrochene Warenkörbe`) AS `Abgebrochene Warenkörbe`, 
    SUM( u.`Neukunden`) AS `Neukunden`,
   ROUND(SUM(o.`Bestellungen`)/SUM(u.`Neukunden`)*100,2) as `Order Abandonment Rate`,
   ROUND(SUM(o.`Bestellungen`)/(SUM(b.`Abgebrochene Warenkörbe`)+SUM(o.`Bestellungen`))*100,2) as `Basket Conversion Rate`,
   ROUND(SUM(o.`Bestellungen`)/SUM(v.uniquevisits)*100,2) as `Order Conversion Rate`,
   ROUND(SUM(b.`Abgebrochene Warenkörbe`)/SUM(v.uniquevisits)*100,2) as `Basket/Visit Conversion Rate`,
   WEEK(v.datum,3) AS `Kalenderwoche`,
   YEAR(v.datum) AS `Jahr`
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
  v.datum <= '$jear-$mounth-$day'
 AND 
  v.datum > '$jear2-$mounth2-$day2'
 GROUP BY WEEK(v.datum,3)
 ORDER BY v.datum ASC
";
$monate = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$result = mysql_query($sql);
if (!$result)
	die('FAIL');
if (!mysql_num_rows($result))
	die('FAIL');
while ($entry = mysql_fetch_assoc($result))
{
 	$data[$entry['Kalenderwoche']] = $entry;
}
if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
?>
<chart palette="2" caption="Conversion-Tracking" subCaption="<?php echo($week2).' bis '.($week).' Kalenderwoche'?>" showValues="0" divLineDecimalPrecision="1" limitsDecimalPrecision="1" DYAxisName="Visits" PYAxisName="Order Conversion Rate / Basket/Visit Conversion Rate" SYAxisName="Order Abandonment Rate/ Basket Conversion Rate" numberPrefix="" decimals="2" formatNumberScale="0">
<categories>
<?php foreach ($data as $value) {?>
	<category toolText="KW <?php echo$value['Kalenderwoche']?>" label="<?php echo$value['Kalenderwoche']?>"/>
<?php }?>
</categories>
<dataset seriesName="Order&nbsp;Abandonment&nbsp;Rate" renderAs="Area" parentYAxis="S" renderAs="COLUMN">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Order Abandonment Rate']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Basket&nbsp;Conversion&nbsp;Rate" showValues="0" parentYAxis="S" renderAs="COLUMN">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Basket Conversion Rate']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Order&nbsp;Conversion&nbsp;Rate" showValues="0" parentYAxis="P" renderAs="LINE">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Order Conversion Rate']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Basket/Visit&nbsp;Conversion&nbsp;Rate" showValues="0" parentYAxis="P" renderAs="LINE">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Basket/Visit Conversion Rate']?>"/>
<?php }?>
</dataset>
</chart>
<?php }
else {
 if(!isset($csv)){
  $data = array_values($data);
 $headers = array(
  array("text"=>"Kalenderwoche","key"=>"Kalenderwoche","fixedWidth"=>true,"defaultWidth"=>"100px","date"=>true),
  array("text"=>"Bestellungen","key"=>"Bestellungen","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true),
  array("text"=>utf8_encode("Warenkörbe"),"key"=>"Abgebrochene Warenkörbe","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true),
  array("text"=>"Neukunden","key"=>"Neukunden","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true),
  array("text"=>"Order Abandonment Rate","key"=>"Order Abandonment Rate","fixedWidth"=>true,"defaultWidth"=>"150px","numeric"=>true),
  array("text"=>"Basket Conversion Rate","key"=>"Basket Conversion Rate","fixedWidth"=>true,"defaultWidth"=>"150px","numeric"=>true),
  array("text"=>"Order Conversion Rate","key"=>"Order Conversion Rate","fixedWidth"=>true,"defaultWidth"=>"150px","numeric"=>true),
  array("text"=>"Basket/Visit Conversion Rate","key"=>"Basket/Visit Conversion Rate","fixedWidth"=>true,"defaultWidth"=>"150px","numeric"=>true)
  );//
 include("json.php");
 $json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
 }
}?>