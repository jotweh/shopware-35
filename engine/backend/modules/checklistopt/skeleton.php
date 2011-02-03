<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
//==============================================================================================================
// Params
//==============================================================================================================

// id of checklist-element (s_core_checklist.id) ---------------------------------------------------------------
$checklistId = intval($_REQUEST['id']);
if(empty($checklistId)) die("missing param: id (s_core_checklist.id)");

//==============================================================================================================
// Load checklist values
//==============================================================================================================

$getchklstQ = mysql_query("
	SELECT * FROM `s_core_checklist`
	WHERE `id` = '{$checklistId}'
	LIMIT 1
");

if(mysql_num_rows($getchklstQ) == 0) 
	die("no db-result >> s_core_checklist.id = ".$checklistId);
	
$getchklstData = mysql_fetch_array($getchklstQ);

//==============================================================================================================
// Set window configuration
//==============================================================================================================

// edit-url ----------------------------------------------------------------------------------------------------
$windowUrl = $getchklstData['winurl'];

// generate title-----------------------------------------------------------------------------------------------
$windowTitle = $getchklstData['area'];
if(!empty($getchklstData['subarea'])) $windowTitle.= " > ".$getchklstData['subarea'];
if(!empty($getchklstData['option'])) $windowTitle.= " > ".$getchklstData['option'];

switch ($getchklstData['type'])
{
	//Presettings
	case '1':		
		$windowWidth = 	750;	
	break;
	//Snippets
	case '2':		
		$windowWidth = 	600;
		
		if(empty($windowUrl))
		{
			// generate edit-url -------------------------------------------------------------------------------
			if(empty($getchklstData['attr1'])) die("missing param attr1(=snippetname) or winurl");
			$getSnippetIdQ = mysql_query("
				SELECT `id`
				FROM `s_core_config_text`
				WHERE `name` = '{$getchklstData['attr1']}'
				LIMIT 1
			");	
			if(mysql_num_rows($getSnippetIdQ) == 0) 
				die("no db-result >> s_core_config_text.name = ".$getchklstData['attr1']);
				
			$snippetId = mysql_result($getSnippetIdQ, 0, 'id');
			$windowUrl = "../snippets/snippets.php?edit=".$snippetId;
		}
			
	break;
	default:
		$windowWidth = 	1000;
	break;
}
?>
{
	"init": {
		"title": "<?php echo $windowTitle; ?>",
		"minwidth": <?php echo $windowWidth; ?>,
		"minheight": 680,
		"height": 680,
		"width": <?php echo $windowWidth; ?>,
		"content": "",
		"loader": "iframe",
		"url": "<?php echo $windowUrl; ?>"
	}
	
}
