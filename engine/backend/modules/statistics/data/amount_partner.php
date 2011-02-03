<?php
if (!defined('sAuthFile')) die();

if (!isset($csv))
	$limit = 15;
else 
	$limit = 300;
	
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
$lastdate = date("d.m.Y",$lastday);
list($day, $mounth, $jear) = explode ('.',$lastdate);
if(empty($_REQUEST['date']))
{
	$firstday = mktime(0,0,0,$mounth,$day-$range,$jear);
}
else 
{
	list($td, $tm, $tj) = explode ('.',$_REQUEST['date']);
	$firstday = mktime(0,0,0,$tm,$td,$tj);
}
$firstdate = date("d.m.Y",$firstday);
list($day2, $mounth2, $jear2) = explode ('.',$firstdate);


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
		ROUND(SUM((o.invoice_amount_net-o.invoice_shipping_net)/currencyFactor),2) AS `Umsatz`,
		p.company  as `Partner`,
		o.partnerID as `Tracking Code`,
		p.id as `PartnerID`
	FROM 
		`s_order` as o
	LEFT JOIN s_emarketing_partner as p
	ON o.partnerID=p.idcode
	WHERE 
		TO_DAYS(o.ordertime) >= TO_DAYS('$jear2-$mounth2-$day2')
	AND 
		TO_DAYS(o.ordertime) <= TO_DAYS('$jear-$mounth-$day')
	AND 
		o.status != 4
	AND
		o.status != -1
	AND 
		o.partnerID != ''
	GROUP BY 
		o.partnerID
	ORDER BY Umsatz DESC LIMIT $limit";

$result = mysql_query($sql);

if ((!$result || !@mysql_num_rows($result)) && !$_REQUEST["table"] && !$_REQUEST["csv"]){
	die('FAIL');
}/*elseif ((!$result || !@mysql_num_rows($result))){
	include("json.php");
	$json = new Services_JSON();
	$headers = $sLang["statistics"]["amount_partner_header"];
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>array(),"totalCount"=>0));
	exit;
}*/


$data = array();
while ($entry = mysql_fetch_assoc($result))
{
	if(!isset($csv)&&!empty($_REQUEST['table'])){
		$entry['Partner'] = utf8_encode($entry['Partner']);
	}
	if(empty($entry['Partner']))
		$entry['Partner'] = $entry['Tracking Code'];
	if(empty($entry['PartnerID']))
		$entry['PartnerID'] = 0;
	$data[] = $entry;
}
	
if(empty($_REQUEST['table'])&&!isset($csv))
{
?>
<chart caption="<?php echo $sLang["statistics"]["amount_partner_header"] ?>" xAxisName="<?php echo $sLang["statistics"]["amount_partner_partner_turnover"] ?>" yAxisName="<?php echo $sLang["statistics"]["amount_partner_turnover"] ?>" showValues="0" decimals="2" formatNumberScale="0" chartRightMargin="30">
<?php foreach ($data as $array) {?>
	<set link="JavaScript:parent.parent.loadSkeleton('partner',false, <?php echo$array['PartnerID']?>);" label='<?php echo$array['Partner']?>' value='<?php echo$array['Umsatz']?>' />
<?php }?>
</chart>
<?php
}
else 
{
	if (!isset($csv))
	{
		
		 $getHeader = mysql_query("
 SELECT header FROM s_core_statistics WHERE file='amount_partner'
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