<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

$upset = array();

$upset[] = "active=".((!isset($_REQUEST["active"])||$_REQUEST["active"]!="on") ? 0 : 1);
$upset[] = "bind_laststock=".((!isset($_REQUEST["bind_laststock"])||$_REQUEST["bind_laststock"]!="on") ? 0 : 1);

if(isset($_REQUEST["customergroupID"]))
	$upset[] = "customergroupID=".((empty($_REQUEST["customergroupID"])||!is_numeric($_REQUEST["customergroupID"])) ? "NULL" : (int) $_REQUEST["customergroupID"]);
if(isset($_REQUEST["multishopID"]))
	$upset[] = "multishopID=".((empty($_REQUEST["multishopID"])||!is_numeric($_REQUEST["multishopID"])) ? "NULL" : (int) $_REQUEST["multishopID"]);

if(!empty($_REQUEST["bind_time_from"]))
{
	$_REQUEST["bind_time_from"] = explode(":",$_REQUEST["bind_time_from"]);
	$_REQUEST["bind_time_from"] = (int)(($_REQUEST["bind_time_from"][0]*60)+$_REQUEST["bind_time_from"][1])*60;
}
if(!empty($_REQUEST["bind_time_to"]))
{
	$_REQUEST["bind_time_to"] = explode(":",$_REQUEST["bind_time_to"]);
	$_REQUEST["bind_time_to"] = (int)(($_REQUEST["bind_time_to"][0]*60)+$_REQUEST["bind_time_to"][1])*60;
}

if(isset($_REQUEST["bind_time_from"]))
	$upset[] = "bind_time_from=".((empty($_REQUEST["bind_time_from"])||!is_numeric($_REQUEST["bind_time_from"])) ? "NULL" : (int) $_REQUEST["bind_time_from"]);
if(isset($_REQUEST["bind_time_to"]))
	$upset[] = "bind_time_to=".((empty($_REQUEST["bind_time_to"])||!is_numeric($_REQUEST["bind_time_to"])) ? "NULL" : (int) $_REQUEST["bind_time_to"]);
if(isset($_REQUEST["bind_weekday_from"]))
	$upset[] = "bind_weekday_from=".((empty($_REQUEST["bind_weekday_from"])||!is_numeric($_REQUEST["bind_weekday_from"])) ? "NULL" : (int) $_REQUEST["bind_weekday_from"]);
if(isset($_REQUEST["bind_weekday_to"]))
	$upset[] = "bind_weekday_to=".((empty($_REQUEST["bind_weekday_to"])||!is_numeric($_REQUEST["bind_weekday_to"])) ? "NULL" : (int) $_REQUEST["bind_weekday_to"]);
if(isset($_REQUEST["bind_instock"]))
	$upset[] = "bind_instock=".((empty($_REQUEST["bind_instock"])||!is_numeric($_REQUEST["bind_instock"])) ? "NULL" : (int) $_REQUEST["bind_instock"]);

if(!empty($_REQUEST["bind_weight_from"]))
	$_REQUEST["bind_weight_from"] = (float) str_replace(",",".",$_REQUEST["bind_weight_from"]);
if(!empty($_REQUEST["bind_weight_to"]))
	$_REQUEST["bind_weight_to"] = (float) str_replace(",",".",$_REQUEST["bind_weight_to"]);
	
if(isset($_REQUEST["bind_weight_from"]))
	$upset[] = "bind_weight_from=".((empty($_REQUEST["bind_weight_from"])||!is_numeric($_REQUEST["bind_weight_from"])) ? "NULL" : $_REQUEST["bind_weight_from"]);
if(isset($_REQUEST["bind_weight_to"]))
	$upset[] = "bind_weight_to=".((empty($_REQUEST["bind_weight_to"])||!is_numeric($_REQUEST["bind_weight_to"])) ? "NULL" : $_REQUEST["bind_weight_to"]);

if(!empty($_REQUEST["bind_price_from"]))
	$_REQUEST["bind_price_from"] = (float) str_replace(",",".",$_REQUEST["bind_price_from"]);
if(!empty($_REQUEST["bind_price_to"]))
	$_REQUEST["bind_price_to"] = (float) str_replace(",",".",$_REQUEST["bind_price_to"]);
if(!empty($_REQUEST["shippingfree"]))
	$_REQUEST["shippingfree"] = (float) str_replace(",",".",$_REQUEST["shippingfree"]);
	
if(isset($_REQUEST["bind_price_from"]))
	$upset[] = "bind_price_from=".((empty($_REQUEST["bind_price_from"])||!is_numeric($_REQUEST["bind_price_from"])) ? "NULL" : $_REQUEST["bind_price_from"]);
if(isset($_REQUEST["bind_price_to"]))
	$upset[] = "bind_price_to=".((empty($_REQUEST["bind_price_to"])||!is_numeric($_REQUEST["bind_price_to"])) ? "NULL" : $_REQUEST["bind_price_to"]);
if(isset($_REQUEST["shippingfree"]))
	$upset[] = "shippingfree=".((empty($_REQUEST["shippingfree"])||!is_numeric($_REQUEST["shippingfree"])) ? "NULL" : $_REQUEST["shippingfree"]);

if(isset($_REQUEST["calculation"]))
	$upset[] = "calculation=".(int) $_REQUEST["calculation"];
if(isset($_REQUEST["position"]))
	$upset[] = "position=".(int) $_REQUEST["position"];
if(isset($_REQUEST["surcharge_calculation"]))
	$upset[] = "surcharge_calculation=".(int) $_REQUEST["surcharge_calculation"];
if(isset($_REQUEST["bind_shippingfree"]))
	$upset[] = "bind_shippingfree=".(int) $_REQUEST["bind_shippingfree"];
if(isset($_REQUEST["tax_calculation"]))
	$upset[] = "tax_calculation=".(int) $_REQUEST["tax_calculation"];
if(isset($_REQUEST["type"]))
	$upset[] = "type=".(int) $_REQUEST["type"];
	
if(isset($_REQUEST["comment"]))
	$_REQUEST["comment"] = str_replace("\xe2\x82\xac","&euro;",$_REQUEST["comment"]);
if(isset($_REQUEST["name"]))
	$_REQUEST["name"] = str_replace("\xe2\x82\xac","&euro;",$_REQUEST["name"]);
if(isset($_REQUEST["description"]))
	$_REQUEST["description"] = str_replace("\xe2\x82\xac","&euro;",$_REQUEST["description"]);
if(isset($_REQUEST["status_link"]))
	$_REQUEST["status_link"] = str_replace("\xe2\x82\xac","&euro;",$_REQUEST["status_link"]);

if(isset($_REQUEST["comment"]))
	$upset[] = "comment=".((empty($_REQUEST["comment"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["comment"])))."'");
if(isset($_REQUEST["name"]))
	$upset[] = "name=".((empty($_REQUEST["name"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["name"])))."'");
if(isset($_REQUEST["description"]))
	$upset[] = "description=".((empty($_REQUEST["description"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["description"])))."'");
if(isset($_REQUEST["status_link"]))
	$upset[] = "status_link=".((empty($_REQUEST["status_link"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["status_link"])))."'");
if(isset($_REQUEST["bind_sql"]))
	$upset[] = "bind_sql=".((empty($_REQUEST["bind_sql"])) ? "NULL" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["bind_sql"])))."'");
if(isset($_REQUEST["calculation_sql"]))
	$upset[] = "calculation_sql=".((empty($_REQUEST["calculation_sql"])) ? "NULL" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["calculation_sql"])))."'");
	
if(!empty($_REQUEST["feedID"])&&is_numeric($_REQUEST["feedID"]))
{
	$feedID = (int)$_REQUEST["feedID"];
}



if(!empty($upset)&&!empty($feedID))
{
	$upset = implode(",",$upset);
	$sql = "UPDATE s_premium_dispatch SET $upset WHERE id=$feedID";
	mysql_query($sql);
}
elseif (!empty($upset))
{
	$upset = implode(",",$upset);
	$sql = "REPLACE INTO s_premium_dispatch SET $upset";
	mysql_query($sql);
	$tempFeedId = mysql_insert_id();
	/**
	 * @ticket 4904
	 * Duplicate translations
	 */
	if (empty($feedID) && !empty($_REQUEST["duplicateFeed"])){
		$getTranslations = mysql_query("
		SELECT * FROM s_core_translations WHERE `objecttype` = 'config_dispatch'
		");
		while ($result = mysql_fetch_assoc($getTranslations)){
			
			$serializedTranslation = unserialize($result["objectdata"]);
			if (!empty($serializedTranslation[$_REQUEST["duplicateFeed"]])){
				$serializedTranslation[$tempFeedId] = $serializedTranslation[$_REQUEST["duplicateFeed"]];
				mysql_query("
				UPDATE s_core_translations SET `objectdata` = '".serialize($serializedTranslation)."' WHERE id = ".$result["id"]);
			}
		}
	}
	$feedID = $tempFeedId;

}



if(isset($_REQUEST["holidays"]))
{
	$sql = "DELETE FROM s_premium_dispatch_holidays WHERE dispatchID=$feedID";
	mysql_query($sql);
	$_REQUEST["holidays"] = explode(",",$_REQUEST["holidays"]);
	array_map(create_function('$e', 'return (int) $e;'),$_REQUEST["holidays"]);
	if(!empty($_REQUEST["holidays"]))
	{
		$sql = "($feedID, ".implode("), ($feedID,",$_REQUEST["holidays"]).")";
		$sql = "INSERT INTO s_premium_dispatch_holidays (dispatchID, holidayID) VALUES $sql";
		mysql_query($sql);
	}
}

if(isset($_REQUEST["categories"])&&!empty($feedID))
{
	$sql = "DELETE FROM s_premium_dispatch_categories WHERE dispatchID=$feedID";
	mysql_query($sql);
	$_REQUEST["categories"] = explode(",",$_REQUEST["categories"]);
	array_map(create_function('$e', 'return (int) $e;'),$_REQUEST["categories"]);
	if(!empty($_REQUEST["categories"]))
	{
		$sql = "($feedID, ".implode("), ($feedID,",$_REQUEST["categories"]).")";
		$sql = "INSERT INTO s_premium_dispatch_categories (dispatchID, categoryID) VALUES $sql";
		mysql_query($sql);
	}
}
if(isset($_REQUEST["paymentmeans"])&&!empty($feedID))
{
	$sql = "DELETE FROM s_premium_dispatch_paymentmeans WHERE dispatchID=$feedID";
	mysql_query($sql);
	$_REQUEST["paymentmeans"] = explode(",",$_REQUEST["paymentmeans"]);
	array_map(create_function('$e', 'return (int) $e;'),$_REQUEST["articles"]);
	if(!empty($_REQUEST["paymentmeans"]))
	{
		$sql = "($feedID, ".implode("), ($feedID,",$_REQUEST["paymentmeans"]).")";
		$sql = "INSERT INTO s_premium_dispatch_paymentmeans (dispatchID, paymentID) VALUES $sql";
		mysql_query($sql);
	}
}
if(isset($_REQUEST["countries"])&&!empty($feedID))
{
	$sql = "DELETE FROM s_premium_dispatch_countries WHERE dispatchID=$feedID";
	mysql_query($sql);
	$_REQUEST["countries"] = explode(",",$_REQUEST["countries"]);
	array_map(create_function('$e', 'return (int) $e;'),$_REQUEST["countries"]);
	if(!empty($_REQUEST["countries"]))
	{
		$sql = "($feedID, ".implode("), ($feedID,",$_REQUEST["countries"]).")";
		$sql = "INSERT INTO s_premium_dispatch_countries (dispatchID, countryID) VALUES $sql";
		mysql_query($sql);
	}
}
require_once("json.php");
$json = new Services_JSON();
$data = array(
	'feedID' => $feedID,
	'success'=>true
);
echo $json->encode($data);
?>