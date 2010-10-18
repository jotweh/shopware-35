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
if ($_REQUEST["group"]){
	$group = explode("_",$_REQUEST["group"]);
	$group[0] = intval($group[0]);
	$group[1] = intval($group[1]);
	
	if (!empty($group[0]) && !empty($group[1])){
		// Check for relations first
		$check = mysql_query("
		SELECT id FROM s_filter_values WHERE groupID = {$group[0]} AND optionID = {$group[1]} LIMIT 1
		");
		if (@mysql_num_rows($check)){
			die(utf8_encode("Verknüpfung kann nicht gelöscht werden, da dieser bereits Artikeleigenschaften zugeordnet sind"));
		}else {
			$deleteRelations = mysql_query("
			DELETE FROM s_filter_relations WHERE groupID = {$group[0]} AND optionID = {$group[1]}
			");
		}
	}
	
}

?>