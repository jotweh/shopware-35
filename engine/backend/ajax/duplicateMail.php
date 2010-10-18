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



$_GET["duplicate"] = intval($_REQUEST["duplicate"]);	// Primary-Key
$_GET["tableM"] = $_REQUEST["tableM"];					// Main-Table
$_GET["tableMName"] = $_REQUEST["tableMName"];			// Name Main-Table 
$_GET["tableMNameAttach"] = $_REQUEST["tableMNameAttach"];	// Name Main-Table  Attach
$_GET["tableS"] = $_REQUEST["tableS"];

if ($_GET["duplicate"]){
	
	// Get Name for duplication
	$getArticleName = mysql_query("SELECT subject FROM s_campaigns_mailings WHERE id = {$_GET["duplicate"]}");
	$name = mysql_result($getArticleName,0,"subject");
	
	$newPrimaryKey = copyRow($_GET["duplicate"], "s_campaigns_mailings",array("subject"=>$name." Kopie".date("d.m.Y H:i:s"),"status"=>"0","locked"=>"","read"=>"0","clicked"=>"0","datum"=>date("Y-m-d")));
	
	$getContainers = mysql_query("
	SELECT * FROM s_campaigns_containers WHERE promotionID = {$_GET["duplicate"]}
	");
	
	while ($container = mysql_fetch_assoc($getContainers)){
		switch ($container["type"]){
			case "ctText":
			case "ctVoucher":
				$secKey = copyRow($container["id"], "s_campaigns_containers",array("promotionID"=>$newPrimaryKey));
				$getAll = mysql_query("
				SELECT * FROM s_campaigns_html WHERE parentID = {$container["id"]}
				");
				while ($sube = mysql_fetch_assoc($getAll)){
					copyRow($sube["id"], "s_campaigns_html",array("parentID"=>$secKey));
				}
				break;
			case "ctBanner":
				$secKey = copyRow($container["id"], "s_campaigns_containers",array("promotionID"=>$newPrimaryKey));
				$getAll = mysql_query("
				SELECT * FROM s_campaigns_banner WHERE parentID = {$container["id"]}
				");
				while ($sube = mysql_fetch_assoc($getAll)){
					copyRow($sube["id"], "s_campaigns_banner",array("parentID"=>$secKey));
				}
				break;
			case "ctArticles":
				$secKey = copyRow($container["id"], "s_campaigns_containers",array("promotionID"=>$newPrimaryKey));
				$getAll = mysql_query("
				SELECT * FROM  s_campaigns_articles WHERE parentID = {$container["id"]}
				");
				while ($sube = mysql_fetch_assoc($getAll)){
					copyRow($sube["id"], " s_campaigns_articles",array("parentID"=>$secKey));
				}
				break;
			case "ctSuggest":
				$secKey = copyRow($container["id"], "s_campaigns_containers",array("promotionID"=>$newPrimaryKey));
				break;
			case "ctLinks":
				$secKey = copyRow($container["id"], "s_campaigns_containers",array("promotionID"=>$newPrimaryKey));
				$getAll = mysql_query("
				SELECT * FROM  s_campaigns_links WHERE parentID = {$container["id"]}
				");
				while ($sube = mysql_fetch_assoc($getAll)){
					copyRow($sube["id"], "s_campaigns_links",array("parentID"=>$secKey));
				}
				break;
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