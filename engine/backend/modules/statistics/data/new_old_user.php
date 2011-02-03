<?php
if (!defined('sAuthFile')) die();


if(empty($_REQUEST['date']))
{
	$von = time();
}
else 
{
	$von = strtotime($_REQUEST["date"]);
}


if(empty($_REQUEST['date2']))
{
	$bis = time();
}
else 
{
	$bis = strtotime($_REQUEST["date2"]);
}

$sql= "
	SELECT
		o.userID as `userID`,
		DATE(MAX(o.ordertime)) as `last`,
		DATE(MIN(o.ordertime)) as `first`,
		DATE(`u`.`firstlogin`) as `firstlogin`,
		COUNT(*) as `count`
	FROM
		`s_order` as `o`,
		`s_user` as `u`
	WHERE 		
		o.ordertime <='".date("Y-m-d",$bis)." 23:59:59'
	AND 
		o.ordertime >= '".date("Y-m-d",$von)."'
		
	AND 
		`u`.id=`o`.`userID`
	GROUP BY userID
	ORDER BY `o`.ordertime ASC
";

$result = mysql_query($sql);
if (!$result)
	die('FAIL');
if(!mysql_num_rows($result))
	die('FAIL');
	
$tmp = array();	
while($entry = mysql_fetch_assoc($result))
{
	$entry['last'] = strtotime($entry['last']);
	$entry['first'] = strtotime($entry['first']);
	$entry['firstlogin'] = strtotime($entry['firstlogin']);
	$week = date('W',$entry['last']);
	
	/*
	if(!in_array($entry['userID'],$tmp))
	{
		$data[$week]['Kunden']++;
		if($entry['first']+60*60*24*7>$entry['last']&&$entry['firstlogin']+60*60*24*30>$entry['last'])
			$data[$week]['Neukunden']++;
		else 
			$data[$week]['Stammkunden']++;
		$tmp[$week][] = $entry['userID'];
	}
	*/
	if($entry['firstlogin']>=$von)
	{
		$data[$week]['Neukunden']++;
		$data[$week]['Neukunden Bestellungen'] += $entry['count'];
	}
	else
	{
		$data[$week]['Stammkunden']++;
		$data[$week]['Stammkunden Bestellungen'] += $entry['count'];
	}
	$data[$week]['Kunden']++;
	$data[$week]['Bestellungen'] += $entry['count'];
}

$tmp = array();
foreach ($data as $week => $value){
	if(empty($data[$week]['Kunden']))
		$data[$week]['Kunden'] = 0;
	if(empty($data[$week]['Neukunden']))
		$data[$week]['Neukunden'] = 0;
	if(empty($data[$week]['Stammkunden']))
		$data[$week]['Stammkunden'] = 0;
	if(empty($data[$week]['Bestellungen']))
		$data[$week]['Bestellungen'] = 0;
	if(empty($data[$week]['Neukunden Bestellungen']))
		$data[$week]['Neukunden Bestellungen'] = 0;
	if(empty($data[$week]['Stammkunden Bestellungen']))
		$data[$week]['Stammkunden Bestellungen'] = 0;
	if(!empty($data[$week]['Neukunden Bestellungen'])&&!empty($data[$week]['Bestellungen']))
		$data[$week]['Anteil Neukunden'] = round($data[$week]['Neukunden Bestellungen']/$data[$week]['Bestellungen']*100,2);
	else 
		$data[$week]['Anteil Neukunden'] = 0;
	if(!empty($data[$week]['Stammkunden Bestellungen'])&&!empty($data[$week]['Bestellungen']))
		$data[$week]['Anteil Stammkunden'] = round($data[$week]['Stammkunden Bestellungen']/$data[$week]['Bestellungen']*100,2);
	else 
		$data[$week]['Anteil Stammkunden'] = 0;	
	$data[$week]['Woche'] = $week;
	$tmp[$week] = $data[$week];
}
$data = $tmp;

if(empty($_REQUEST['table'])) {
header('Content-type: text/xml');
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>
<chart palette="2" caption="<?php echo $sLang["statistics"]["new_old_user_share_new_customer"] ?>" subCaption="<?php echo($firstweek).' '.$sLang["statistics"]["new_old_user_until"].' '.($lastweek).' '.$sLang["statistics"]["new_old_user_Week"]?>" showValues="0" divLineDecimalPrecision="1" limitsDecimalPrecision="1" DYAxisName="Visits" PYAxisName="" SYAxisName="Anzahl" numberPrefix="" decimals="2" formatNumberScale="0">
<categories>
<?php foreach ($data as $key=>$value) {?>
	<category label="<?php echo$key?> KW"/>
<?php }?>
</categories>

<dataset seriesName="<?php echo $sLang["statistics"]["new_old_user_new_customer"] ?>" showValues="0" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?> KW" value="<?php echo$value['Anteil Neukunden']?>"/>
<?php }?>
</dataset>
<dataset seriesName="<?php echo $sLang["statistics"]["new_old_user_Loyalty"] ?>" showValues="0" parentYAxis="S">
<?php foreach ($data as $key=>$value) {?>
	<set label="<?php echo$key?> KW" value="<?php echo$value['Anteil Stammkunden']?>"/>
<?php }?>
</dataset>
</chart>
<?php } else {
	if (!isset($csv))
	{
	 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='new_old_user'
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
foreach ($data as $key => $value){
	$data2[] = $value;
}
 $headers = $tempHeader;
	include("json.php");
	$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data2,"totalCount"=>count($data2)));
	}
}?>