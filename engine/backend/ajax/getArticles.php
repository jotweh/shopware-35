<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
// *****************
?>
<?php
require_once("json.php");
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
if (!$_POST["id"]) $_POST["id"] = 3;
if (!$_POST["limit"]) $_POST["limit"] = 25;

if ($_POST["sort"]=="supplier") $_POST["sort"] = "supplierName";

if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "a.name";
if (!$_POST["dir"]) $_POST["dir"] = "ASC";
if ($_POST["sort"]=="datum") $_POST["sort"] = "a.changetime"; 



$sql = "SELECT COUNT(DISTINCT a.id) AS count
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


//echo $sql;
$resultCountArticles = mysql_query($sql);
$nodes["totalCount"] = @mysql_result($resultCountArticles,0,"count");


$sql = "SELECT a.id as articleID,a.active AS active, ordernumber,datum,additionaltext,instock, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.id AS supplierID,aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,taxID,
attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20
FROM s_articles_categories AS aCategories,s_articles AS a,
s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
s_articles_attributes AS aAttributes
WHERE 
aCategories.articleID=a.id AND a.taxID=aTax.id
AND aAttributes.articledetailsID=aDetails.id
AND aCategories.categoryID=".$_POST["id"]."
AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1
AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
AND aPrices.to='beliebig'
GROUP BY a.id ORDER BY {$_POST["sort"]} {$_POST["dir"]} LIMIT {$_POST["start"]},{$_POST["limit"]}";

//die($sql);
$getArticles = mysql_query($sql);

if (@mysql_num_rows($getArticles)){
	while ($article=mysql_fetch_assoc($getArticles)){
		$price = $article["price"]*(100+$article["tax"])/100;
		$price = number_format($price, 2, ',', '');
		
		/*if ($article["active"] == 1){
			$article["active"] = "<a class='ico accept'></a>";
		}else {
			$article["active"] = "<a class='ico exclamation'></a>";
		}*/
		
		$nodes["articles"][] = array('articleName'=>htmlentities($article["articleName"]), 'articleID'=>$article["articleID"],"ordernumber"=>htmlentities($article["ordernumber"]),"supplier"=>htmlentities($article["supplierName"]),"price"=>$price,"active"=>$article["active"]);
		
	}
}else {
	$nodes["articles"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>