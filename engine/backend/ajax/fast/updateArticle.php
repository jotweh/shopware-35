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

$_POST["keyID"] = intval($_POST["keyID"]);
$_POST["value"] = utf8_decode(trim($_POST["value"]));

			
if ($_POST["keyID"]){
	$queryArticle = mysql_query("
		SELECT s_core_tax.tax AS tax, s_articles_details.id AS id FROM s_articles,s_articles_details, s_core_tax WHERE
		s_articles.taxID = s_core_tax.id
		AND s_articles_details.articleID = s_articles.id
		AND s_articles_details.kind = 1
		AND s_articles.id = {$_POST["keyID"]}
	");
	if (!@mysql_num_rows($queryArticle)) exit;
			$article = mysql_fetch_assoc($queryArticle);
	switch ($_POST["field"]){
		case "price":
			$queryPriceGroup = mysql_query("
				SELECT taxinput FROM s_core_customergroups WHERE groupkey='EK'
			");
			$priceWithTax = mysql_result($queryPriceGroup,0,"taxinput");
			
			$price = $_POST["value"];
			$price = floatval(str_replace(",",".",$price));
			if (empty($price)) break;
			// How to save price???
			if ($priceWithTax["taxinput"]){
				// Sub the tax
				$price = round($price/(100+$article["tax"])*100,10);			
			}
			$sql = "
				UPDATE s_articles_prices
				SET
					price = $price
				WHERE
					articleID = {$_POST["keyID"]}
				AND
					articledetailsID = {$article["id"]}
				AND `from` = '1'
				";
			//echo $sql;
			$updatePrice = mysql_query($sql);
			break;
		case "ordernumber":
			if (!$_POST["value"]) return;
			$_POST["value"] = mysql_real_escape_string($_POST["value"]);
			$sql = "
			UPDATE s_articles_details SET ordernumber = '{$_POST["value"]}'
			WHERE
					articleID = {$_POST["keyID"]}
				AND
					id = {$article["id"]}
			";
			//echo $sql;
			$updateOrdernumber = mysql_query($sql);
			break;
		case "supplier":
			if (!$_POST["value"]) return;
			$_POST["value"] = intval($_POST["value"]);
			$sql = "
			UPDATE s_articles SET supplierID = {$_POST["value"]}
			WHERE
				id = {$_POST["keyID"]}
			";
			//echo $sql;
			$updateSupplier = mysql_query($sql);
			break;
		case "articleName":
			if (!$_POST["value"]) return;
			
			$_POST["value"] = mysql_real_escape_string($_POST["value"]);
			$_POST["value"] = str_replace("\"","&quot;",$_POST["value"]);
			$sql = "
			UPDATE s_articles SET name = '{$_POST["value"]}'
			WHERE
				id = {$_POST["keyID"]}
			";
			//echo $sql;
			
			$updateName = mysql_query($sql);
			break;
		case "instock":
			if (!$_POST["value"]) $_POST["value"]="0";
			$_POST["value"] = intval($_POST["value"]);
			$sql = "
			UPDATE s_articles_details SET instock = '{$_POST["value"]}'
			WHERE
					articleID = {$_POST["keyID"]}
				AND
					id = {$article["id"]}
			";
			//echo $sql;
			$updateOrdernumber = mysql_query($sql);
			break;
		case "tax":
			if (!$_POST["value"]) return;
			$_POST["value"] = intval($_POST["value"]);
			$sql = "
			UPDATE s_articles SET taxID = {$_POST["value"]}
			WHERE
				id = {$_POST["keyID"]}
			";
			//echo $sql;
			$updateTax = mysql_query($sql);
			break;
		case "active":
			if (!$_POST["value"]) $_POST["value"]="0";
			$_POST["value"] = intval($_POST["value"]);
			$sql = "
			UPDATE s_articles SET active = {$_POST["value"]}
			WHERE
				id = {$_POST["keyID"]}
			";
			//echo $sql;
			$updateTax = mysql_query($sql);
			$sql = "
			UPDATE s_articles_details SET active = {$_POST["value"]}
			WHERE
				articleID = {$_POST["keyID"]}
			";
			//echo $sql;
			$updateTax = mysql_query($sql);
			break;
	}
}else {
	
	// Insert basic article information
	if ($_POST["field"]!="ordernumber") exit;
	// Query supplierID for first entry
	$querySupplier = @mysql_query("SELECT id FROM s_articles_supplier LIMIT 1");
	$supplierID = @mysql_result($querySupplier,0,"id");
	if (!$supplierID) $supplierID = 1;
	
	$insertArticle = mysql_query("
	INSERT INTO s_articles(supplierID,name, active, taxID,changetime)
	VALUES($supplierID,'Neuer Artikel',0,1,now())
	");
	
	$id = mysql_insert_id();
	
	if (!$id)  echo "ERROR1";
	
	
	if ($_POST["value"]){
		$ordernumber = $_POST["value"];
	}else { 
		$ordernumber = "neu".rand(1,100000); 
	}
	// basic article details
	$insertDetails = mysql_query("
	INSERT INTO s_articles_details (articleID,ordernumber,kind)
	VALUES ($id,'$ordernumber',1)
	");
	
	$idDetails = mysql_insert_id();
	if (!$idDetails) echo "ERROR2";
	
	// insert attributes raw
	$insertAttributes = mysql_query("
	INSERT INTO s_articles_attributes (articleID, articledetailsID)
	VALUES ($id,$idDetails)
	");
	
	// insert prices raw
	$insertPrices = mysql_query("
	INSERT INTO s_articles_prices (pricegroup,`from`,`to`,articleID, articledetailsID,price)
	VALUES ('EK','1','beliebig',$id,$idDetails,0)
	");
	if (!$insertPrices) echo "ERROR3";
	
	// Insert category relations
	if ($_POST["category"]){
		$category = intval($_POST["category"]);
			$source = $category;
			do
			{
				$sql = "
				INSERT INTO s_articles_categories
				(id,articleID,categoryID,categoryparentID)
				VALUES
				('',$id,$category,$source)
				";
				//echo $sql;
				$insertCategory = mysql_query($sql);
				$getCategoriesParent = mysql_query("SELECT parent FROM s_categories WHERE id=$category");
				if (@mysql_num_rows($getCategoriesParent)){
					$parent = mysql_result($getCategoriesParent,0,"parent");
					$category = $parent;
				}else {
					$parent=1;	
				}
			} while ($parent!=1 || !$parent);
	}
	
	
	echo $id;
}
?>