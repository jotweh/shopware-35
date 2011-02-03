<?php
if (!defined('sAuthFile')) die();

if(empty($_REQUEST['range']))
	$range = 14;
else 
	$range = $_REQUEST['range'];

if(empty($_REQUEST['date2']))
{
	$lastday = time();
}
else 
{
	list($td, $tm, $tj) = explode('.',$_REQUEST['date2']);
	$lastday = mktime(0,0,0,$tm,$td,$tj);
}
list($day, $mounth, $jear) = explode ('-',date("d-m-Y",$lastday));
if(empty($_REQUEST['date']))
{
	$firstday = mktime(0,0,0,$mounth,$day-$range,$jear);
}
else 
{
	list($td, $tm, $tj) = explode ('.',$_REQUEST['date']);
	$firstday = mktime(0,0,0,$tm,$td,$tj);
}
list($day2, $mounth2, $jear2) = explode ('-',date("d-m-Y",$firstday));


$monate = $sLang["statistics"]["search_month"];
if (!$_REQUEST["start"]){
	$start = 0;
}else {
	$start = $_REQUEST["start"];
}

if (!$_REQUEST["limit"]){
	$ende = 100;
}else {
	$ende = $_REQUEST["limit"];
}

$total = 
"
	SELECT
		COUNT(DISTINCT searchterm) AS anzahl
	FROM `s_statistics_search`
	WHERE 
		datum <= '$jear-$mounth-$day 23:59:59'
	AND 
		datum >= '$jear2-$mounth2-$day2'
	AND
		searchterm != ''
";

$total = mysql_query($total);
$total = mysql_result($total,0,"anzahl");
//die($total."#");
$sql = "
	SELECT
		COUNT(searchterm) AS anzahl,
		searchterm,
		MAX(results) AS results
	FROM `s_statistics_search`
	WHERE 
		datum <= '$jear-$mounth-$day 23:59:59'
	AND 
		datum >= '$jear2-$mounth2-$day2'
	AND
		searchterm != ''
	GROUP BY searchterm
	ORDER BY anzahl DESC, results ASC
";

if(!isset($csv)){
	$sql .="LIMIT $start, $ende";
}


$result = mysql_query($sql);

if (!$result)
	die();
if (!@mysql_num_rows($result) && $_REQUEST["table"]){
	$headers = $sLang["statistics"]["search_header"];
	$data = $arrays;
	include("json.php");
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>array(),"totalCount"=>$total));
}


$count = array();
while ($entry = mysql_fetch_assoc($result))
{
	$count[] = $entry;	
}
arsort($count);
if(empty($_REQUEST['table'])||isset($csv))
{
	foreach ($count as $key=>$value)
		$arrays[] = array("searchterm"=>strip_tags($value["searchterm"]),"count"=>$value["anzahl"],"results"=>$value["results"]);
}
else
{
	foreach ($count as $key=>$value)
		$arrays[] = array("searchterm"=>utf8_encode(strip_tags($value["searchterm"])),"count"=>$value["anzahl"],"results"=>$value["results"]);
}
	
if(empty($_REQUEST['table']))
{
	$arrays = array_slice($arrays, 0, 10);
	header('Content-type: text/plain');
	?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>
<chart palette="2" caption="<?php echo $sLang["statistics"]["search_search"] ?>" yaxisname="" showValues="0" showLabels="0" hovercapbg="FFFFFF" toolTipBorder="889E6D" divLineColor="999999" divLineAlpha="80" showShadow="0" canvasBgColor="FEFEFE" canvasBaseColor="FEFEFE" canvasBaseAlpha="50" divLineIsDashed="1" divLineDashLen="1" divLineDashGap="2" chartRightMargin="30" useRoundEdges="1" legendBorderAlpha="0">

<categories>
<?php
foreach ($arrays as $array)
{
?>	<category showLabel="1" label="<?php echo$array['searchterm']?>" />
<?php
}
?>
</categories>

<dataset seriesname="<?php echo $sLang["statistics"]["search_count"] ?>" color="8EAC41">
<?php
foreach ($arrays as $array)
{
?>	<set showValue="1" value="<?php echo$array['count']?>" />
<?php
}
?>
</dataset>

<dataset seriesname="<?php echo $sLang["statistics"]["search_Search_Results"] ?>" color="607142">
<?php
foreach ($arrays as $array)
{
?>	<set showValue="1" value="<?php echo$array['results']?>" />
<?php
}
?>
</dataset>
</chart>
<?php
} 
else
{
$getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='search'
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
	$data = $arrays;
	if (!isset($csv))
	{
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		foreach($data as &$row) $row["searchterm"] = "<a href=\"http://{$sCore->sCONFIG['sBASEPATH']}/{$sCore->sCONFIG['sBASEFILE']}?sViewport=searchFuzzy&sSearch=".urlencode($row["searchterm"])."\" target=\"_blank\">".htmlspecialchars($row["searchterm"])."<a/>";
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>$total));
	}
}
?>