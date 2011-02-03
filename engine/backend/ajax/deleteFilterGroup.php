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
	$group = intval($_REQUEST["group"]);
	
	// Check for relations first
	$check = mysql_query("
	SELECT id FROM s_filter_values WHERE groupID = $group LIMIT 1
	");
	if (@mysql_num_rows($check)){
		die(utf8_encode("Gruppe kann nicht gelöscht werden, da dieser bereits Artikeleigenschaften zugeordnet sind"));
	}else {
		$deleteGroup = mysql_query("
		DELETE FROM s_filter WHERE id = $group
		");
		$deleteRelations = mysql_query("
		DELETE FROM s_filter_relations WHERE groupID = $group
		");
	}
}

?>