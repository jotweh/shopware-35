<?php
if (!defined('sAuthFile')) die();

$sql = "
	SELECT 
		c.countryname AS `Land`,
		COUNT(b.id) AS `Anzahl`
	FROM `s_user_billingaddress` as b, s_core_countries as c
	WHERE 
		b.countryID=c.id
	GROUP BY 
		b.countryID
	ORDER BY `Anzahl` DESC";
$result = mysql_query($sql);

if (!$result)
	die();

while ($entry = mysql_fetch_assoc($result))
{
	if (!empty($_REQUEST["table"])) $entry["Land"] = utf8_encode($entry["Land"]);
	$arrays[] = $entry;
}




if (empty($arrays))
	$arrays[0] = array("Anzahl"=>0,"Land"=>"");

if(!isset($csv) && empty($_REQUEST["table"]))
{
?>
<chart palette='4'>
<?php
foreach ($arrays as $array)
{
?>	<set label='<?php echo html_entity_decode($array['Land'])?>' value='<?php echo$array['Anzahl']?>' />
<?php
}
?>
</chart>
<?php
}
else 
{
	$data = $arrays;
	if (!isset($csv))
	{
		
		 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_user_country'
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
		
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}
?>
