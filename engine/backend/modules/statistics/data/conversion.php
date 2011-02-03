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


if(empty($_REQUEST['range']))
	$_REQUEST['range'] = 4;
$weeks = $_REQUEST['range'];
if(!empty($_REQUEST['date']))
{
	$time = strtotime($_REQUEST['date']);
}
else
{
	$time = time();
}
list($day, $month, $year, $week) = split('-',date("d-n-Y-W",$time));
list($day2, $month2, $year2, $week2) = split('-',date("d-n-Y-W", strtotime($_REQUEST['date2'])));

if ($_REQUEST["group"]!=1){
	$kw = false;
	foreach ($sLang["statistics"]["conversion_header"] as $key => $value){
		if ($value["header"]=="Woche") $sLang["statistics"]["conrate_header"][$key]["header"] = "Datum";
	}
}else {
	$kw = true;
}
if(empty($_REQUEST['table'])) {
	$sort = "ASC";
}else {
	$sort = "DESC";
}
if ($kw){
	$group = "WEEK(s.datum,3)";
	$orderBy = "WEEK(s.datum,3) DESC";
}else {
	$group = "TO_DAYS(s.datum)";
	$orderBy = "s.datum $sort";
}

$monate = $sLang["statistics"]["conversion_array"];

$sql = "
	SELECT 
		WEEK(s.datum, 3) AS `Woche`,
		o.`Bestellungen` AS `Bestellungen`,
		SUM(s.uniquevisits) AS `Visits`,
		DATE_FORMAT(s.datum,'%d.%m.%Y') AS `Datum`
	FROM 
		`s_statistics_visitors` AS s
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
  	`o`.`date`=s.datum
  	 WHERE 
	  s.datum <= '$year2-$month2-$day2'
	 AND 
	  s.datum >= '$year-$month-$day'
	GROUP BY 
		$group
	ORDER BY
	$orderBy
	";
$totalCount = mysql_num_rows(mysql_query($sql));
if(!isset($csv)){
	$sql .="LIMIT $start, $end";
}

$result = mysql_query($sql);
if (!$result)
	die();
	
	
while ($entry = mysql_fetch_assoc($result))
{
	if (!$kw){
		$entry["Woche"] = $entry["Datum"];
	}
	if (empty($entry["Bestellungen"])) $entry["Bestellungen"] = "0";
	if (empty($entry["Visits"])) $entry["Visits"] = "0";
	
	$data[$entry['Woche']]["Woche"] = $entry["Woche"];
	$data[$entry['Woche']]["Bestellungen"] = $entry["Bestellungen"];
	$data[$entry['Woche']]["Visits"] = $entry["Visits"];
	if ($entry["Bestellungen"]){
		$data[$entry['Woche']]["Conversion-Rate"] = round($entry["Bestellungen"]/$entry["Visits"]*100,2);
	}else {
		$data[$entry['Woche']]["Conversion-Rate"] = "0";	
	}
}
if(empty($_REQUEST['table'])) {
header('Content-type: text/plain');
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>
<chart palette="2" decimals='2' caption="<?php echo $sLang["statistics"]["conversion_order_conversion_rate"] ?>" subCaption="<?php echo$week2.' '.$sLang["statistics"]["conversion_until"].' '.$week.' '.$sLang["statistics"]["conversion_week"]?>" showValues="0" numberSuffix="%">
<categories>
<?php foreach($data as $dat) {?>
	<category label='KW <?php echo$dat['Woche']?>' />
<?php }?>
</categories>
<dataset showValues="0" renderAs="Area">
<?php foreach ($data as $key =>$dat) {?>
	<set label="KW <?php echo$key?>" value="<?php echo$dat['Conversion-Rate']?>"/>
<?php }?>
</dataset>
</chart>
<?php }
else {
	if(!isset($csv)){
		$data = array_values($data);
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='conversion'
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
		/*print_r($date);
		exit;*/
		echo $json->encode($date);
	}
}?>
