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

$_GET["duplicate"] = $_REQUEST["duplicate"];

if ($_GET["duplicate"]){
	//exit;
	// Get Article-Name
	$getArticleName = mysql_query("SELECT name FROM s_articles WHERE id = {$_GET["duplicate"]}");
	$name = mysql_result($getArticleName,0,"name");
	$newPrimaryKey = copyRow($_GET["duplicate"], "s_articles",array("name"=>"$name KOPIE","datum"=>date("Y-m-d"),"active"=>0));
	
	if (!$newPrimaryKey){
		die ("Abort duplicate");
	}else {
		// Duplicate category-relations
		$getCategories = mysql_query("
		SELECT id FROM s_articles_categories WHERE articleID = {$_GET["duplicate"]}");
		while ($category = mysql_fetch_array($getCategories)){
			copyRow($category["id"], "s_articles_categories",array("articleID"=>$newPrimaryKey));
		}
		
		// r302 Duplicate properties
		$getProperties = mysql_query("
		SELECT id FROM s_filter_values WHERE articleID = {$_GET["duplicate"]}");
		while ($property = mysql_fetch_array($getProperties)){
			copyRow($property["id"], "s_filter_values",array("articleID"=>$newPrimaryKey));
		}
		
		// Duplicate Translations
		$getTranslations = mysql_query("
		SELECT id FROM s_core_translations WHERE objectkey = {$_GET["duplicate"]} AND objecttype='article'");
		
		while ($translation = mysql_fetch_array($getTranslations)){
			copyRow($translation["id"], "s_core_translations",array("objectkey"=>$newPrimaryKey));
		}
		
		// Duplicate Images
		$getImages = mysql_query("
		SELECT id,img FROM s_articles_img WHERE articleID = {$_GET["duplicate"]}");
		while ($image = mysql_fetch_array($getImages)){
			// Fullcopy of this picture
			$thumbs = $sCore->sCONFIG["sIMAGESIZES"];
			$queryGetSizes = explode(";",$thumbs);
			$random = md5(uniqid(rand()));
			copy("../../../images/articles/{$image["img"]}.jpg","../../../images/articles/$random.jpg");
				
			foreach ($queryGetSizes as $size){
				$imgSizes = explode(":",$size);
				$width = $imgSizes[0];
				$height = $imgSizes[1];
				$suffix  = $imgSizes[2];
				copy("../../../images/articles/{$image["img"]}_$suffix.jpg","../../../images/articles/$random"."_$suffix.jpg");
				
			}
			
			copyRow($image["id"], "s_articles_img",array("articleID"=>$newPrimaryKey,"img"=>$random));
			
		}
		
		
		$getDetails = mysql_query("
		SELECT id,ordernumber FROM s_articles_details WHERE articleID = {$_GET["duplicate"]}
		");
		while ($details = mysql_fetch_array($getDetails)){
			
			$rand = rand(1000,10000);
			$prefix = $sCore->sCONFIG["sBACKENDAUTOORDERNUMBERPREFIX"] ? $sCore->sCONFIG["sBACKENDAUTOORDERNUMBERPREFIX"] : "SW";
			// Get next ordernumber
			$getNumber = mysql_query("
			SELECT number FROM s_order_number WHERE name='articleordernumber'
			");
			$default = $prefix.@mysql_result($getNumber,0,"number");
			
			$newSecondaryKey = copyRow($details["id"], "s_articles_details",array("articleID"=>$newPrimaryKey,"ordernumber"=>$default,"active"=>1));
			$update = mysql_query("UPDATE s_order_number SET number = number + 1 WHERE name = 'articleordernumber'");
			//echo "$newPrimaryKey -> $newSecondaryKey\n";
			// Duplicate assigned attributes
			$getPrices = mysql_query("
			SELECT id FROM s_articles_prices WHERE articleID = {$_GET["duplicate"]} AND articledetailsID = {$details["id"]} ORDER BY id ASC
			");
			while ($price = mysql_fetch_array($getPrices)){
				copyRow($price["id"], "s_articles_prices",array("articleID"=>$newPrimaryKey,"articledetailsID"=>$newSecondaryKey));
			}
			// ---
			
			// Duplicate assigned attributes
			$getAttributes = mysql_query("
			SELECT id FROM s_articles_attributes WHERE articleID = {$_GET["duplicate"]} AND articledetailsID = {$details["id"]} 
			");
			while ($attribute = mysql_fetch_array($getAttributes)){
				copyRow($attribute["id"], "s_articles_attributes",array("articleID"=>$newPrimaryKey,"articledetailsID"=>$newSecondaryKey));
			}
			// ---
			
			
		}
	}
	die("#".$newPrimaryKey);
}
function copyRow($primaryKey, $tabelle, $resetColumns){
	// Primary-Key, Foreign-Key, Table
	
	$primaryColumn = "id";
	//$tabelle = "emark_mailings";
	$queryFields = mysql_query("SHOW FIELDS FROM $tabelle");
	
	//$resetColumns = ;
	
	//print_r($resetColumns);
	
	while ($queryField = mysql_fetch_array($queryFields)){
		// Primary-Key ignorieren
		if ($queryField["Key"]!="PRI"){
			$columns[] = array("fieldname"=>$queryField["Field"],"fieldtype"=>$queryField["Type"]);
			$selectFields[] = "`".$queryField["Field"]."`";
		}else {
			$primaryColumn = $queryField["Field"];
		}
	}
	
	// Alle bestehenden Daten grabben
	// Building Query-String
	// --
	$selectFields = implode(",",$selectFields);
	$sqlSelect = "
	SELECT $selectFields FROM $tabelle WHERE $primaryColumn=$primaryKey
	";
	//echo $sqlSelect;
	$sqlQuerySourceRow = mysql_fetch_array(mysql_query($sqlSelect),MYSQL_ASSOC);
	
	
	if (!count($sqlQuerySourceRow)) die("Could not copy row");
	
	// Die Bestandsdaten haben wir nun
	$insertIntoString = "INSERT INTO $tabelle ($selectFields) VALUES (%VALUES%)";
	
	$i=0;
	//print_r($sqlQuerySourceRow);
	foreach ($sqlQuerySourceRow as $copyColumn){
		if (array_key_exists($columns[$i]["fieldname"],$resetColumns)){
			$copyColumn = $resetColumns[$columns[$i]["fieldname"]];
		}
		$copyColumn = mysql_real_escape_string($copyColumn);
		if (preg_match("/int/",$columns[$i]["fieldtype"])){
			$data[] = "'$copyColumn'";
		}else {
			$data[] = "'$copyColumn'";
		}
		$i++;
	}
	
	$insertIntoData = implode(",",$data);
	
	$insertIntoString = preg_replace("/%VALUES%/",$insertIntoData,$insertIntoString);
	
	#echo $insertIntoString;
	#return;
	//$insertIntoString = "";
	if (mysql_query($insertIntoString)){
		// Neuen Primary-Key zurückgeben
		//echo "PRIMARY:".mysql_insert_id();
		return mysql_insert_id();
		
	}else {
		echo "Could not copy row $insertIntoString".mysql_error();
		return false;
	}

	
	// --
} // EOF copyrow
?>