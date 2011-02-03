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
if (!function_exists("htmlspecialchars_decode")) {
    function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT) {
        return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
    }
}
if(isset($_REQUEST['doc']))
{
foreach ($_REQUEST['doc'] as $key => $value)
{
	//$sql = "SELECT typ SET `value`='".htmlspecialchars_decode(mysql_real_escape_string($value))."' WHERE id='{$key}'  LIMIT 1";
	
	//$price = floatval(str_replace(',', '.',$_REQUEST['price'][$key]));
	$sql = "UPDATE s_billing_template SET `value`='".htmlspecialchars_decode(mysql_real_escape_string($value))."' WHERE id='{$key}'  LIMIT 1";
	//typ
	mysql_query($sql);
	if(isset($_REQUEST['show'][$key]))
	{
	if($_REQUEST['show'][$key] == 0)
	{
		$sql="UPDATE s_billing_template SET `show`='0' WHERE id='$key'";
		mysql_query($sql);
	}
	else 
	{
		$sql="UPDATE s_billing_template SET `show`='1' WHERE id='$key'";
		mysql_query($sql);
	}
	}
}
}
$sql = "SELECT `ID` , `name` , `value` , `typ` , `group` , `show` ,`desc`  FROM s_billing_template ORDER BY position ASC ";
$result = mysql_query($sql);

if(!$result)
{
	die();
}
while($row = mysql_fetch_assoc($result))
{
	$entrys[$row['group']][$row['ID']]['desc'] = $row['desc'];
	$entrys[$row['group']][$row['ID']]['typ'] = $row['typ'];
	$entrys[$row['group']][$row['ID']]['name'] = $row['name'];
	$entrys[$row['group']][$row['ID']]['ID'] = $row['ID'];
	$entrys[$row['group']][$row['ID']]['show'] = $row['show'];
	if($row['typ'] == 1)
	{
		$tinyMCE[] = $row["name"].$row["ID"];
	}
	$entrys[$row['group']][$row['ID']]['value'] = $row['value'];
}

?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
  	tinyMCE.init({
		// General options
		mode: "exact",
		elements : "<?php echo implode(',',$tinyMCE)?>",
		theme : "advanced",
		<?php echo $sCore->sCONFIG['sTINYMCEOPTIONS']?>, 
		extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage|tinybrowser]",
		//cleanup : false, skin : "o2k7", relative_urls : false,theme_advanced_resizing : true, theme_advanced_toolbar_location : "top", theme_advanced_toolbar_align : "left",	theme_advanced_path_location : "bottom",
		plugins : "safari,pagebreak,style,layer,table,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		height:"350px"
	});
	function tinyBrowser (field_name, url, type, win) {
		
       /* If you work with sessions in PHP and your client doesn't accept cookies you might need to carry
          the session name and session ID in the request string (can look like this: "?PHPSESSID=88p0n70s9dsknra96qhuk6etm5").
          These lines of code extract the necessary parameters and add them back to the filebrowser URL again. */
	   type = "image";
       var cmsURL = "../../../vendor/tinymce/backend/plugins/tinybrowser/tinybrowser.php";    // script URL - use an absolute path!
       if (cmsURL.indexOf("?") < 0) {
           //add the type as the only query parameter
           cmsURL = cmsURL + "?type=" + type;
       }
       else {
           //add the type as an additional query parameter
           // (PHP session ID is now included if there is one at all)
           cmsURL = cmsURL + "&type=" + type;
       }

       tinyMCE.activeEditor.windowManager.open({
           file : cmsURL,
           title : 'Tiny Browser',
           width : 650, 
           height : 440,
           resizable : "yes",
           scrollbars : "yes",
           inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
           close_previous : "no"
       }, {
           window : win,
           input : field_name
       });
       return false;
     }
</script>
</head>
<body>

<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="">

<?php
$sCore->sInitTranslations(1,"config_documents");

//print_r($entrys);

$h = $sLang["presettings"]["documents_array"];

//foreach ($entrys as $name => $entry) {

foreach ($h as $id => $hn) {
	echo "<fieldset><legend>{$hn}</legend>\n<ul>\n";
	foreach ($entrys[$id] as $key => $value) {
		
		switch ($id)
		{
			case "margin":
				$trans_key = 1;
			break;
			case "header":
				$trans_key = 2;
			break;
			case "footer":
				$trans_key = 3;
			break;
			case "headline":
				$trans_key = 4;
			break;
			case "sender":
				$trans_key = 5;
			break;
			case "content_middle":
				$trans_key = 6;
			break;
			default:
				$trans_key = 10;
			break;
		}
		
		
		echo "<li style=\"clear:both;\">\n";	
		echo "<label for=\"doc_{$value["ID"]}\">{$value["desc"]}:</label>\n";
		switch ($value["typ"]) 
		{
		case 1:
			echo "<input type=\"radio\" name=\"show[{$value["ID"]}]\" value=\"0\" style=\"float:none;\" ";
			if ($value["show"] != 1)
				echo "checked ";
			echo ">nicht zeigen";
			echo "<input type=\"radio\" name=\"show[{$value["ID"]}]\" value=\"1\" style=\"float:none;\" ";
			if ($value["show"] == 1)
				echo "checked ";
			echo ">zeigen";
			//echo "<label for=\"doc_{$value["ID"]}\">{$value["desc"]}:</label>\n";
			echo "<br /><textarea mce_editable=\"mce_editable\" cols=\"50\" rows=\"6\" id=\"".$value["name"].$value["ID"]."\" name=\"doc[{$value["ID"]}]\">{$value["value"]}</textarea>\n";
			echo $sCore->sBuildTranslation($value["name"].$value["ID"], $value["name"].$value["ID"],$trans_key,"config_documents");
			break;
		case 2:
			echo "<input class=\"w200\" style=\"height:25px\" value=\"{$value["value"]}\" id=\"".$value["name"].$value["ID"]."\" name=\"doc[{$value["ID"]}]\">\n";
			echo $sCore->sBuildTranslation($value["name"].$value["ID"], $value["name"].$value["ID"],$trans_key,"config_documents");
			break;
		case 3:
			echo "<input class=\"w200\" style=\"height:25px\" value=\"{$value["value"]}\" id=\"".$value["name"].$value["ID"]."\" name=\"doc[{$value["ID"]}]\">\n";
			echo $sCore->sBuildTranslation($value["name"].$value["ID"],$value["name"].$value["ID"], $trans_key,"config_documents");
			break;
		}
		echo "</li>\n";
	}
	echo "</ul></fieldset>\n\n";
}
?>


	
	
	
	<div class="buttons" id="buttons">
	<ul>

	<li style="display: block;" class="buttonTemplate">
	<button id="save" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["customergroups_save"] ?></div></button>
	</li>
	
	<li id="buttonTemplate" class="buttonTemplate"><a class="bt_icon page_white_acrobat" onclick="parent.parent.loadSkeleton('pdfpreview'); return false;"  value="send">Vorschau zeigen</a></li>	

</ul>
</div>



<div class="clear"></div>
</form>

<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>

</body>
</html>