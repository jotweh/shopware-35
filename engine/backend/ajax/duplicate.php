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

if ($_GET["duplicate"] && $_GET["tableM"]){
	
	// Get Name for duplication
	$getArticleName = mysql_query("SELECT {$_GET["tableMName"]} FROM {$_GET["tableM"]} WHERE id = {$_GET["duplicate"]}");
	$name = mysql_result($getArticleName,0,$_GET["tableMName"]);
	$newPrimaryKey = copyRow($_GET["duplicate"], $_GET["tableM"],array($_GET["tableMName"]=>$name.$_GET["tableMNameAttach"]." ".date("d.m.Y H:i:s")));
	
	if (!$newPrimaryKey){
		die ("Error while duplicating row");
	}else {
		$subTables = explode("#",$_REQUEST["tableS"]);
		
		foreach ($subTables as $subTable){
			$subTable = explode("|",$subTable);
			if ($subTable[0] && $subTable[1]){
				$getCategories = mysql_query("
				SELECT id FROM {$subTable[0]} WHERE {$subTable[1]} = {$_GET["duplicate"]}");
				while ($category = mysql_fetch_array($getCategories)){
					copyRow($category["id"], $subTable[0],array($subTable[1]=>$newPrimaryKey));
				}
			}
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