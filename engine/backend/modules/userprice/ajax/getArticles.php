<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once("json.php");
$json = new Services_JSON();

if(isset($_REQUEST['pricegroupID']))
{
	$sql_pricegroup = "'PG".intval($_REQUEST["pricegroupID"])."'";
}

if(!empty($_REQUEST["delete"]))
{
	$delete = "'".mysql_real_escape_string(trim(utf8_decode($_REQUEST["delete"])))."'";
	//$sql = "DELETE FROM s_articles_prices WHERE `s_articles_prices`.`id` = 267 LIMIT 1;"
	$sql = "
		DELETE gp FROM s_articles_groups_value gv, s_articles_groups_prices gp
		WHERE gv.ordernumber=$delete AND gp.groupkey=$sql_pricegroup AND gp.valueID=gv.valueID
	";
	mysql_query($sql);
	$sql = "
		DELETE p FROM s_articles_details d, s_articles_prices p
		WHERE d.ordernumber=$delete	AND p.pricegroup=$sql_pricegroup AND p.articledetailsID=d.id
	";
	mysql_query($sql);
}

if(!empty($_REQUEST["search"]))
{
	$search = "'".mysql_real_escape_string(trim(utf8_decode($_REQUEST["search"])))."%'";
	$sql_where = "WHERE d.ordernumber LIKE  $search ";
	$sql_where .= "OR gv.ordernumber LIKE $search ";
	$sql_where .= "OR a.name LIKE $search";
}
$limit = empty($_REQUEST['limit']) ? 25 : (int)$_REQUEST['limit'];
$start = empty($_REQUEST['start']) ? 0 : (int)$_REQUEST['start'];
$dir = (empty($_REQUEST['dir'])||$_REQUEST['dir']=='ASC') ? 'ASC' : 'DESC';
$sort = (empty($_REQUEST['sort'])||is_array($_REQUEST['sort'])) ? 'ordernumber' : preg_replace('#[^\w]#','',$_REQUEST['sort']);

	$sql = "
		SELECT SQL_CALC_FOUND_ROWS
			a.id as articleID,
			IFNULL(gv.ordernumber,d.ordernumber) as ordernumber,
			TRIM( CONCAT( a.name, ' ', d.additionaltext ) ) AS name,
			IF(gv.valueID,gp.groupkey,p.pricegroup) AS pricegroup,
			IF(gv.valueID,gp.price,p.price) as price,
			IF(gv.valueID,gp2.price,p2.price) as defaultprice,
			IF(gv.valueID,1,0) as config,
			t.tax
		FROM s_articles a
		INNER JOIN s_articles_details d
		ON d.articleID=a.id
		INNER JOIN s_core_tax t
		ON t.id=a.taxID
		
		LEFT JOIN s_articles_prices p
		ON p.articledetailsID = d.id
		AND p.`to` = 'beliebig'
		AND p.pricegroup = $sql_pricegroup
	
		LEFT JOIN s_articles_prices p2
		ON p2.articledetailsID = d.id
		AND p2.`to` = 'beliebig'
		AND p2.pricegroup = 'EK'
		
		LEFT JOIN s_articles_groups_value gv
		ON gv.articleID=a.id
		
		LEFT JOIN s_articles_groups_prices gp
		ON gp.valueID=gv.valueID
		AND gp.groupkey = $sql_pricegroup
	
		LEFT JOIN s_articles_groups_prices gp2
		ON gp2.valueID=gv.valueID
		AND gp2.groupkey = 'EK'
	
		$sql_where
	
		ORDER BY $sort $dir
				
		LIMIT $start, $limit
	";
	$rows = array();
	
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
	while($row = mysql_fetch_assoc($result))
	{
		$row['name'] = trim(utf8_encode($row['name']));
		$row['ordernumber'] = trim(utf8_encode($row['ordernumber']));
		$row['pricegroup'] = trim(utf8_encode($row['pricegroup']));
		$row['config'] = empty($row['config']) ? 0 : 1;
		if(!empty($row['tax'])&&empty($_REQUEST['netto']))
		{
			$row['price'] = $row['price']*(100+$row['tax'])/100;
			$row['defaultprice'] = $row['defaultprice']*(100+$row['tax'])/100;
		}
		if(!empty($row['price']))
		{
			$row['price'] = number_format($row['price'],2,',','');
		}
		else
		{
			$row['price'] = '';
		}
		if(!empty($row['defaultprice']))
		{
			$row['defaultprice'] = number_format($row['defaultprice'],2,',','');
		}
		$rows[] = $row;
	}
	$sql = "
		SELECT FOUND_ROWS() as count
	";
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
		$count = (int) mysql_result($result,0,"count");
	else 
		$count = 0;
	echo  $json->encode(array("articles"=>$rows,"count"=>$count));
?>