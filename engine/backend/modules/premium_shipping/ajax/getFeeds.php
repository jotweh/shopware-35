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

if(isset($_REQUEST["delete"]))
{
	$delete = (int)$_REQUEST["delete"];
	$sql = "DELETE FROM s_premium_shippingcosts WHERE dispatchID=$delete";
	mysql_query($sql);
	$sql = "DELETE FROM s_premium_dispatch_paymentmeans WHERE dispatchID=$delete";
	mysql_query($sql);
	$sql = "DELETE FROM s_premium_dispatch_holidays WHERE dispatchID=$delete";
	mysql_query($sql);
	$sql = "DELETE FROM s_premium_dispatch_countries WHERE dispatchID=$delete";
	mysql_query($sql);
	$sql = "DELETE FROM  s_premium_dispatch_categories WHERE dispatchID=$delete";
	mysql_query($sql);
	$sql = "DELETE FROM s_premium_dispatch WHERE id=$delete";
	mysql_query($sql);
}
if(isset($_REQUEST["feedID"]))
{
	$sql_where = "WHERE d.id=".intval($_REQUEST["feedID"]);
}

	$sql = "
		SELECT SQL_CALC_FOUND_ROWS
			d.id as feedID, d.*, m.name as multishop, c.description as customergroup,
			GROUP_CONCAT(DISTINCT holidayID SEPARATOR ',') as holidays
		FROM s_premium_dispatch d
		LEFT JOIN s_core_multilanguage m
		ON m.id=d.multishopID
		LEFT JOIN s_core_customergroups c
		ON c.id=d.customergroupID
		LEFT JOIN s_premium_dispatch_holidays h
		ON d.id=h.dispatchID
		$sql_where
		GROUP BY d.id
		ORDER BY position, name
	";
	
	$rows = array();
	
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
	while($row = mysql_fetch_assoc($result))
	{
		$row['name'] = trim(utf8_encode($row['name']));
		$row['description'] = trim(utf8_encode($row['description']));
		$row['comment'] = trim(utf8_encode($row['comment']));
		$row['status_link'] = trim(utf8_encode($row['status_link']));
		
		$row["name"] = str_replace("&euro;","\xe2\x82\xac",$row["name"]);
		$row["description"] = str_replace("&euro;","\xe2\x82\xac",$row["description"]);
		$row["comment"] = str_replace("&euro;","\xe2\x82\xac",$row["comment"]);
		$row["status_link"] = str_replace("&euro;","\xe2\x82\xac",$row["status_link"]);
		
		$row['bind_sql '] = utf8_encode($row['bind_sql']);
		$row['calculation_sql '] = utf8_encode($row['calculation_sql']);
		$row['multishop'] = trim(utf8_encode($row['multishop']));
		$row['customergroup'] = trim(utf8_encode($row['customergroup']));
		
		$row['position'] = empty($row['position']) ? null : (int) $row['position'];
		$row['multishopID'] = empty($row['multishopID']) ? null : (int) $row['multishopID'];
		$row['customergroupID'] = empty($row['customergroupID']) ? null : (int) $row['customergroupID'];
		$row['bind_instock'] = empty($row['bind_instock']) ? null : (int) $row['bind_instock'];
		
		$row['active'] = !empty($row['active']);
		$row['type'] = (int) $row['type'];
		$row['bind_shippingfree'] = (int) $row['bind_shippingfree'];
		$row['bind_laststock'] = !empty($row['bind_laststock']);
		
		if(!empty($row['bind_time_to']))
			$row['bind_time_to'] = mktime(0,0,(int) $row['bind_time_to']);
		if(!empty($row['bind_time_from']))
			$row['bind_time_from'] = mktime(0,0,(int) $row['bind_time_from']);
		
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