<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}


$from = (empty($_REQUEST["from"])||!is_numeric($_REQUEST["from"])) ? 1 : (int) $_REQUEST["from"];
$price = (empty($_REQUEST["price"])||!is_numeric($_REQUEST["price"])) ? "0" : (float) $_REQUEST["price"];
$pseudoprice = (empty($_REQUEST["pseudoprice"])||!is_numeric($_REQUEST["pseudoprice"])) ? "0" : (float) $_REQUEST["pseudoprice"];
$baseprice = (empty($_REQUEST["baseprice"])||!is_numeric($_REQUEST["baseprice"])) ? "0" : (float) $_REQUEST["baseprice"];
$percent = (empty($_REQUEST["percent"])||!is_numeric($_REQUEST["percent"])) ? "0" : (float) $_REQUEST["percent"];
$pricegroup =  "PG" . (int) $_REQUEST["pricegroupID"];
$tax = empty($_REQUEST['tax']) ? 0 : (float) $_REQUEST["tax"];
if(!empty($tax))
	$price = $price/(100+$tax)*100;
if(!empty($tax)&&!empty($pseudoprice))
	$pseudoprice = $pseudoprice/(100+$tax)*100;

$sql = "
	SELECT articleID, valueID
	FROM s_articles_groups_value
	WHERE ordernumber='".mysql_real_escape_string($_REQUEST["ordernumber"])."'
";
$result = mysql_query($sql);
if(!$result)
	exit();
if(mysql_num_rows($result))
{
	list($articleID, $valueID) = mysql_fetch_row($result);
	if($from!=1)
	{
		exit();
	}
	$sql = "
		DELETE FROM s_articles_groups_prices WHERE groupkey=$pricegroup AND valueID=$valueID
	";
	mysql_query($sql);
	if(!empty($price))
	{
		$sql = "
			INSERT INTO s_articles_groups_prices
				(articleID, valueID, groupkey, price)
			VALUES
				($articleID, $valueID, '$pricegroup', $price);
		";
		mysql_query($sql);
	}
	exit();
}

$sql = "
	SELECT articleID, id as articledetailsID
	FROM s_articles_details
	WHERE ordernumber='".mysql_real_escape_string($_REQUEST["ordernumber"])."'
";
$result = mysql_query($sql);
if(!$result)
	exit();
if(mysql_num_rows($result))
{
	list($articleID, $articledetailsID) = mysql_fetch_row($result);

	$sql = "
		DELETE FROM s_articles_prices WHERE pricegroup='$pricegroup' AND articledetailsID=$articledetailsID AND `from`>=$from
	";
	mysql_query($sql);
	
	if($from!=1)
	{
		$sql = "
			UPDATE `s_articles_prices` 
			SET `to` = $from-1
			WHERE pricegroup = '$pricegroup'
			AND articledetailsID = $articledetailsID
			ORDER BY `from` DESC
			LIMIT 1
		";
		mysql_query($sql);
	}
	if(!empty($price))
	{
		$sql = "
			
			INSERT INTO `s_articles_prices`
				(`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`)
			VALUES
				('$pricegroup', $from, 'beliebig', $articleID, $articledetailsID, $price, $pseudoprice, $baseprice, $percent);
		";
		mysql_query($sql);
	}
	exit();
}
?>