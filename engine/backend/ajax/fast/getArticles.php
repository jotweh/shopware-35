<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
// *****************
?>
<?php
require_once("../json.php");
$json = new Services_JSON();
?>
<?php

// Fetch-Data from categories
$_REQUEST["node"] = intval($_REQUEST["node"]);

if (!$_REQUEST["node"]){
	$_REQUEST["node"] = 1;
}

$nodes = array();
$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);
$_POST["id"] = intval($_POST["id"]);


if ($_POST["pagingID"]) $_POST["id"] = $_POST["pagingID"];



if (!$_POST["id"] && !isset($_REQUEST['filter'])){
	
	$nodes["articles"] = array();
	$nodes["totalCount"] = 0;
	echo $json->encode($nodes);
	exit;
}

if (!$_POST["limit"]) $_POST["limit"] = 25;

if ($_POST["sort"]=="supplier") $_POST["sort"] = "supplierName";

if (!$_POST["sort"] || $_POST["sort"]=="lastpost" || $_POST["sort"]=="info") $_POST["sort"] = "a.name";
if (!$_POST["dir"]) $_POST["dir"] = "ASC";






if(empty($_REQUEST['filter'])){
	$sql = "SELECT COUNT(DISTINCT a.id) as count
	FROM s_articles_categories AS aCategories,s_articles AS a,
	s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
	s_articles_attributes AS aAttributes
	WHERE 
	aCategories.articleID=a.id AND a.taxID=aTax.id
	AND aAttributes.articledetailsID=aDetails.id
	AND aCategories.categoryID=".$_POST["id"]."
	AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1
	AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
	AND aPrices.to='beliebig'";
	
	
	$resultCountArticles = mysql_query($sql);
	$nodes["totalCount"] = @mysql_result($resultCountArticles,0,'count');
	
	$sql = "SELECT DISTINCT a.id as articleID,a.active AS active, ordernumber,datum,additionaltext,instock, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.id AS supplierID,aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,taxID,
	attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
	attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = aDetails.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments
	FROM s_articles_categories AS aCategories,s_articles AS a,
	s_articles_supplier AS aSupplier, s_articles_details AS aDetails
	, s_articles_prices AS aPrices, s_core_tax AS aTax,
	s_articles_attributes AS aAttributes
	WHERE 
	aCategories.articleID=a.id AND a.taxID=aTax.id
	AND aAttributes.articledetailsID=aDetails.id
	AND aCategories.categoryID=".$_POST["id"]."
	AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1
	AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
	AND aPrices.to='beliebig'
	ORDER BY {$_POST["sort"]} {$_POST["dir"]} LIMIT {$_POST["start"]},{$_POST["limit"]}";


	$getArticles = mysql_query($sql);
	
	if (@mysql_num_rows($getArticles)){
		while ($article=mysql_fetch_array($getArticles)){
			
			// Check if we have to show tax for this customer-group
			$queryPriceGroup = mysql_query("
			SELECT taxinput FROM s_core_customergroups WHERE groupkey='EK'
			");
			$priceWithTax = mysql_result($queryPriceGroup,0,"taxinput");
			
			// Add tax if brutto mode
			if ($priceWithTax["taxinput"]){
				$price = $article["price"]*(100+$article["tax"])/100;
			}else {
				$price = $article["price"];
			}
			
			$price = number_format($price, 2, ',', '');
						
			// Check for variants
			$queryVariants = mysql_query("
			SELECT id FROM s_articles_details WHERE articleID = {$article["articleID"]}
			AND kind = 2
			");
			if (@mysql_num_rows($queryVariants)){
				$article["info"] = "<a class='ico documents_stack' title='Varianten-Artikel'></a>";
			}
			
			// Check for configurator
			$queryConfig = mysql_query("
			SELECT articleID FROM s_articles_groups
			WHERE articleID = {$article["articleID"]}
			");
			if (@mysql_num_rows($queryConfig)){
				$article["info"] = "<a class='ico tables_stacks' title='Konfigurator-Artikel'></a>";
			}
						
			// Check for bundles
			$queryBundles = mysql_query("
			SELECT id FROM s_articles_bundles WHERE articleID = {$article["articleID"]}
			");
			if (@mysql_num_rows($queryBundles)){
				$article["info"] .= "<a class='ico bricks' title='Bundles hinterlegt'></a>";
			}
						
			// Check for bundles
			$queryBundles = mysql_query("
			SELECT id FROM s_articles_live WHERE articleID = {$article["articleID"]}
			");
			if (@mysql_num_rows($queryBundles)){
				$article["info"] .= "<a class='ico clock_red' title='Liveshopping hinterlegt'></a>";
			}
			
			// Grep one picture to show in list
			$queryPicture = mysql_query("
			SELECT img FROM s_articles_img WHERE articleID={$article["articleID"]} AND main=1
			");
			
			if (@mysql_num_rows($queryPicture)){
				$article["image"] = $_SERVER["SERVER_PORT"] == "80" ? "http" : "https";
				$article["image"] .= "://".$sCore->sCONFIG['sBASEPATH']."/images/articles/".mysql_result($queryPicture,0,"img")."_2.jpg";
			}else {
				$article["image"] = "";
				$article["info"] .= "<a class='ico picture_exclamation' title='Kein Bild hinterlegt'></a>";
			}			
			// Check categorysettings
			$queryCat = mysql_query("
			SELECT id FROM s_articles_categories WHERE articleID={$article["articleID"]}
			");
			
			if (@mysql_num_rows($queryCat)){
			}else {
				$article["info"] .= "<a class='ico chain_exclamation' title='Keiner Kategorie zugeordnet'></a>";
			}
			
			if (!$article["info"]) $article["info"] = "";
			if (empty($article["payments"])) $article["payments"] = "0";
			
			/*if ($article["active"] == 1){
				$article["active"] = "<a class='ico accept'></a>";
			}else {
				$article["active"] = "<a class='ico exclamation'></a>";
			}*/
			//$article["info"] = "TEST";
			$nodes["articles"][] = array('articleName'=>utf8_encode(str_replace("'","",$article["articleName"])), 'articleID'=>$article["articleID"],"ordernumber"=>utf8_encode($article["ordernumber"]),"supplier"=>($article["supplierID"]),"image"=>$article["image"],"price"=>$price,"instock"=>$article["instock"],"tax"=>$article["taxID"],"active"=>$article["active"],"info"=>$article["info"],"payments"=>$article["payments"]);
			
		}
	}else {
		$nodes["articles"] = array();
		$nodes["totalCount"] = 0;
	}
}elseif (isset($_REQUEST['filter']))
{
	//ORDER BY SETTINGS
	//Default
	$sort = "a.name";
	$dir = "ASC";
	
	if(!empty($_REQUEST['dir'])) $dir = $_REQUEST['dir'];
	switch ($_REQUEST['sort'])
	{
		case "ordernumber":
			$sort = "ad.ordernumber";
		break;
		case "supplier":
			$sort = "a.supplierID";
		break;
		case "articleName":
			$sort = "a.name";
		break;
		case "price":
			$sort = "pr.price";
		break;
		case "tax":
			$sort = "a.taxID";
		break;
		case "instock":
			$sort = "ad.instock";
		break;
		case "active":
			$sort = "a.active";
		break;
	}
	
	switch ($_REQUEST['filter'])
	{
		case "filter_supplier":
			$gettotal = "
			SELECT 
			COUNT(a.id) AS `total`
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			WHERE a.`supplierID` = {$_REQUEST['filter_id']}
			AND ad.kind=1
			";
			$total_query = mysql_query($gettotal);
			$total = mysql_result($total_query, 0, 'total');
			
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			WHERE a.`supplierID` = {$_REQUEST['filter_id']} 
			AND ad.kind=1 GROUP BY a.id
			ORDER BY {$sort} {$dir}
			LIMIT {$_POST["start"]},{$_POST["limit"]}";
		break;
		case "f_ordernumber":
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			
			WHERE ad.ordernumber = '{$_REQUEST['filter_id']}'
			AND ad.kind=1 GROUP BY a.id";
		break;
		case "f_articlename":
			$gettotal = "
			SELECT 
			COUNT(a.id) AS `total`
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			WHERE a.name LIKE CONVERT( _utf8 '%".$_REQUEST['filter_id']."%' USING latin1 )
			AND ad.kind=1
			";
			$total_query = mysql_query($gettotal);
			$total = mysql_result($total_query, 0, 'total');
			
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			WHERE a.name LIKE CONVERT( _utf8 '%".$_REQUEST['filter_id']."%' USING latin1 )
			AND ad.kind=1 GROUP BY a.id
			ORDER BY {$sort} {$dir}
			LIMIT {$_POST["start"]},{$_POST["limit"]}";
		break;
		case "f_instock":
			$gettotal = "
			SELECT 
			COUNT(a.id) AS `total`
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			WHERE ad.instock <= 0
			AND ad.kind=1
			";
			$total_query = mysql_query($gettotal);
			$total = mysql_result($total_query, 0, 'total');
			
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			WHERE ad.instock <= 0
			AND ad.kind=1 GROUP BY a.id
			ORDER BY {$sort} {$dir}
			LIMIT {$_POST["start"]},{$_POST["limit"]}
			";
		break;
		case "f_nocat":
			$get = "SELECT id FROM `s_articles` WHERE `id` NOT IN 
					(
					SELECT articleID
					FROM `s_articles_categories`
					)";
			$get_query = mysql_query($get);
			while ($getids = mysql_fetch_array($get_query)) {
				$ids[] = $getids['id'];
			}
			$id_str = implode(",", $ids);
			
			$gettotal = "
			SELECT 
			COUNT(a.id) AS `total`
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1
			";
			$total_query = mysql_query($gettotal);
			$total = mysql_result($total_query, 0, 'total');
			
			
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1 GROUP BY a.id
			ORDER BY {$sort} {$dir}
			LIMIT {$_POST["start"]},{$_POST["limit"]}
			";
		break;
		case "f_noimg":
			$get = "SELECT id FROM `s_articles` WHERE `id` NOT IN 
					(
					SELECT articleID FROM `s_articles_img`
					)";
			$get_query = mysql_query($get);
			while ($getids = mysql_fetch_array($get_query)) {
				$ids[] = $getids['id'];
			}
			$id_str = implode(",", $ids);
			
			$gettotal = "
			SELECT 
			COUNT(a.id) AS `total`
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1
			";
			$total_query = mysql_query($gettotal);
			$total = mysql_result($total_query, 0, 'total');
			
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1 GROUP BY a.id
			ORDER BY {$sort} {$dir}
			LIMIT {$_POST["start"]},{$_POST["limit"]}
			";
		break;
		case "f_bundles":
			$get = "SELECT DISTINCT articleID FROM `s_articles_bundles`";
			$get_query = mysql_query($get);
			while ($getids = mysql_fetch_array($get_query)) {
				$ids[] = $getids['articleID'];
			}
			$id_str = implode(",", $ids);
			
			$gettotal = "
			SELECT 
			COUNT(a.id) AS `total`
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1
			";
			$total_query = mysql_query($gettotal);
			$total = mysql_result($total_query, 0, 'total');
			
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1 GROUP BY a.id
			ORDER BY {$sort} {$dir}
			LIMIT {$_POST["start"]},{$_POST["limit"]}
			";
		break;
		case "f_live":
			$get = "SELECT DISTINCT articleID FROM `s_articles_live`";
			$get_query = mysql_query($get);
			while ($getids = mysql_fetch_array($get_query)) {
				$ids[] = $getids['articleID'];
			}
			$id_str = implode(",", $ids);
			
			$gettotal = "
			SELECT 
			COUNT(a.id) AS `total`
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1
			";
			$total_query = mysql_query($gettotal);
			$total = mysql_result($total_query, 0, 'total');
			
			$sql = "
			SELECT 
			a.name AS name, 
			a.id AS articleID, 
			ad.ordernumber AS ordernumber,
			sr.name AS supplier,
			a.supplierID AS supplierID,
			ad.instock AS instock,
			a.active AS active,
			a.taxID AS taxID,
			pr.price AS price,
			
	(SELECT SUM(quantity) FROM s_order_details, s_order WHERE s_order_details.articleordernumber = ad.ordernumber AND s_order.id = s_order_details.orderID 
	AND s_order.status != -1 AND s_order.status != 4 GROUP BY articleordernumber) AS payments 
			 
			FROM `s_articles` AS a
			LEFT JOIN `s_articles_details` AS ad ON(ad.`articleID` = a.id)
			LEFT JOIN `s_articles_supplier` AS sr ON(sr.`id` = a.supplierID)
			LEFT JOIN `s_articles_prices` AS pr ON(pr.`pricegroup` = 'EK' AND pr.articledetailsID = ad.id AND pr.articleID = a.id)
			WHERE a.id IN ({$id_str})
			AND ad.kind=1 GROUP BY a.id
			ORDER BY {$sort} {$dir}
			LIMIT {$_POST["start"]},{$_POST["limit"]}
			";
		break;
	}
	
		
	
	$query = mysql_query($sql);
	if(mysql_num_rows($query) != 0)
	{
		while ($data = mysql_fetch_array($query)) {
			//Get Tax
			$tax_sql = "SELECT tax FROM `s_core_tax` WHERE `id` =".$data['taxID'];
			$tax_que = mysql_query($tax_sql);
			$tax = mysql_result($tax_que, 0, 'tax');
			
			// Check if we have to show tax for this customer-group
			$price = $data['price'];
			$queryPriceGroup = mysql_query("
			SELECT taxinput FROM s_core_customergroups WHERE groupkey='EK'
			");
			$priceWithTax = mysql_result($queryPriceGroup,0,"taxinput");
			//$tax = 19;
			// Add tax if brutto mode
			if ($priceWithTax["taxinput"]){
				$price = $price*(100+$tax)/100;
			}
			
			// Check for variants
			$queryVariants = mysql_query("
			SELECT id FROM s_articles_details WHERE articleID = {$data["articleID"]}
			AND kind = 2
			");
			if (@mysql_num_rows($queryVariants)){
				$data["info"] = "<a class='ico documents_stack' title='Varianten-Artikel'></a>";
			}
			
			// Check for configurator
			$queryConfig = mysql_query("
			SELECT articleID FROM s_articles_groups
			WHERE articleID = {$data["articleID"]}
			");
			if (@mysql_num_rows($queryConfig)){
				$data["info"] = "<a class='ico tables_stacks' title='Konfigurator-Artikel'></a>";
			}
			
			// Check for bundles
			$queryBundles = mysql_query("
			SELECT id FROM s_articles_bundles WHERE articleID = {$data["articleID"]}
			");
			if (@mysql_num_rows($queryBundles)){
				$data["info"] .= "<a class='ico bricks' title='Bundles hinterlegt'></a>";
			}
			
			// Check for bundles
			$queryBundles = mysql_query("
			SELECT id FROM s_articles_live WHERE articleID = {$data["articleID"]}
			");
			if (@mysql_num_rows($queryBundles)){
				$data["info"] .= "<a class='ico clock_red' title='Liveshopping hinterlegt'></a>";
			}
			
			// Grep one picture to show in list
			$queryPicture = mysql_query("
			SELECT img FROM s_articles_img WHERE articleID={$data["articleID"]} AND main=1
			");
			
			if (@mysql_num_rows($queryPicture)){
				$data["image"] = $_SERVER["SERVER_PORT"] == "80" ? "http" : "https";
				$data["image"] .= "://".$sCore->sCONFIG['sBASEPATH']."/images/articles/".mysql_result($queryPicture,0,"img")."_2.jpg";
			}else {
				$data["image"] = "";
				//No Pic
				$data["info"] .= "<a class='ico picture_exclamation' title='Kein Bild hinterlegt'></a>";
			}
			
			// Check categorysettings
			$queryCat = mysql_query("
			SELECT id FROM s_articles_categories WHERE articleID={$data["articleID"]}
			");
			
			if (@mysql_num_rows($queryCat)){
			}else {
				$data["info"] .= "<a class='ico chain_exclamation' title='Keiner Kategorie zugeordnet'></a>";
			}
			
			if (!$data["info"]) $data["info"] = "";
			
			$price = number_format($price, 2, ',', '');
			if (empty($data["payments"])) $data["payments"] = "0";
			$nodes["articles"][] = array('articleName'=>utf8_encode(str_replace("'","",$data['name'])), 'articleID'=>$data['articleID'],"ordernumber"=>$data['ordernumber'],"supplier"=>$data['supplierID'],"image"=>$data['image'],"price"=>$price,"instock"=>$data['instock'],"tax"=>$data['taxID'],"active"=>$data['active'],"info"=>$data["info"],"payments"=>$data["payments"]);
			$nodes["totalCount"] = $total;
	}
		
	}else{
		$nodes["articles"] = array();
		$nodes["totalCount"] = 0;
	}
	
}
echo $json->encode($nodes);
//echo $sql;

?>