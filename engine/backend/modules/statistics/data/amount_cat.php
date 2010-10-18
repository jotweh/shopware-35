<?php
if (!defined('sAuthFile')) die();

$node = intval($_REQUEST['node']);
if(!empty($_REQUEST['date']))
	$time = strtotime($_REQUEST['date']);
else 
{
	list($tY,$tm,$td) = split("-",date("Y-m-d"));
	$time = mktime(0,0,0,$tm,1,$tY);
}
if(empty($_REQUEST['range']))
	$_REQUEST['range'] = 4;

//von
$von = date("Y-m-d",$time);
//bis
$tmp = mktime(0, 0, 0, date('m',$time)+1, -1, date('Y',$time));
$bis = date("Y-m-d",$tmp);

$monate = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");

$sql = "SELECT id
FROM `s_categories`
WHERE `parent` =1";
$result = mysql_query($sql);
if (!$result)
	die();
while ($cat = mysql_fetch_assoc($result))
{
	$cats[] = $cat['id'];
}
$sqlc = "AND ( ac.categoryID=";
$sqlc .= implode(" OR ac.categoryID=",$cats);
$sqlc .= ")";
//ac.categoryID
//$sqlc = "AND ac.categoryID={$cats[0]}";

$sql = "
	SELECT
		ROUND(SUM(a.price*a.quantity)/o.currencyFactor,2) AS `Umsatz`,
		c.description AS `Beschreibung`
	FROM `s_order_details` AS a,`s_categories` AS c,`s_order` AS o,s_articles_categories AS ac
	WHERE 
		o.ordertime >= '$von'
	AND
		o.ordertime <= '$bis 23:59:59'
	AND 
		o.status != 4
	AND
		o.status != -1
	AND 
		a.orderID=o.id
	AND 
		a.articleID=ac.articleID
	$sqlc
	AND
		c.id=ac.categoryID
	GROUP BY 
		(c.id)
	ORDER BY (ordertime) ASC";
echo $sql;
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
	$headers = array(
		array("text"=>"Beschreibung","key"=>"Beschreibung","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true),
		array("text"=>"Umsatz","key"=>"Umsatz","fixedWidth"=>true,"defaultWidth"=>"100px","numeric"=>true),	);
	include("json.php");
	$json = new Services_JSON();
	foreach($headers as $head) $keys[] = $head["dataIndex"];
	echo $json->encode(array("metaData"=>array("id"=>0,"root"=>"rows","fields"=>$headers,"columns"=>$keys,"totalProperty"=>"totalCount"),"rows"=>$data,"totalCount"=>count($data)));
	}
}?>