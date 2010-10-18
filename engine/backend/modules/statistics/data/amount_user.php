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

$sql = "
	SELECT 
		SUM(s_order.invoice_amount) AS `Umsatz`,
		s_user.id,
		s_user.email AS `Mail`,
		b.company,
		b.department,
		b.firstname,
		b.lastname,
		b.customernumber
	FROM `s_order`,`s_user`,`s_user_billingaddress` AS b
	WHERE 
		s_order.ordertime >= '$jear2-$mounth2-$day2'
	AND 
		s_order.ordertime <= '$jear-$mounth-$day 23:59:59'
	AND 
		s_order.status != 4
	AND
		s_order.status != -1
	AND 
		s_order.userID=s_user.id
	AND 
		s_user.id=b.userID
	GROUP BY 
		s_order.userID
	ORDER BY Umsatz DESC LIMIT $limit";
$result = mysql_query($sql);

if (!$result)
	die();

while ($entry = mysql_fetch_assoc($result))
{
	$ret['id'] = $entry['id'];
	$ret['Kundennr.']= $entry["customernumber"];
	if (!empty($entry["company"]))
	{
		$ret['Name']= $entry["company"];
		if (!empty($entry["department"]))
			$ret['Name'] .= " ".$entry["department"];
	}
	else 
	{
		$ret['Name']= $entry["firstname"] ." ". $entry["lastname"];
	}
	if(!isset($csv)&&!empty($_REQUEST['table'])){
		$ret['Name'] = utf8_encode($ret['Name']);
	}
	$ret['Umsatz'] = round($entry['Umsatz'],2);
	$arrays[] = $ret;
}
	
if(empty($_REQUEST['table'])&&!isset($csv))
{
?>
<chart caption="Umsatz nach Wochentagen" xAxisName="Kunden" yAxisName="Umsatz" showValues="0" decimals="2" formatNumberScale="0" chartRightMargin="30">
<?php foreach ($arrays as $array) {?>
	<set link="JavaScript:parent.parent.loadSkeleton('userdetails',false, <?php echo$array['id']?>);" label='<?php echo$array['Name']?>' value='<?php echo$array['Umsatz']?>' />
<?php }?>
</chart>
<?php
}
else 
{
	$data = $arrays;
	if (!isset($csv))
	{
		$script = 
"Table.addEvent( 'afterRow', function(data, row){					
	row.cols[1].element.setStyle('cursor', 'pointer');
	row.cols[2].element.setStyle('cursor', 'pointer');
	row.cols[1].element.addEvent('click',function(){
		parent.parent.loadSkeleton('userdetails',false, row.cols[0].value);
	});
	row.cols[2].element.addEvent('click',function(){
		parent.parent.loadSkeleton('userdetails',false, row.cols[0].value);
	});
});";
		$headers = $sLang["statistics"]["amount_user_header"];
		include("json.php");
		$json = new Services_JSON();
		foreach($headers as $head) $keys[] = $head["dataIndex"];
		echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}
?>