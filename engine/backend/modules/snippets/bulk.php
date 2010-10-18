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

// 
if ($_FILES["articles_file"]["tmp_name"]){
	$fp = fopen($_FILES['articles_file']['tmp_name'],"r");
	while ($buffer=fread($fp,1024)) $data .= $buffer;
	$data = explode("\r\n",$data);
	echo count($data)." "." Textbausteine gefunden"."<br />";
	if (count($data)){
		
		$columns = $data[0];
		$columns = explode(";",$columns);
		
		$isocode = $columns[2];
		
		if (empty($isocode)){
			// German to German
			foreach ($data as &$row){
				$row = explode(";",$row);
				$row[0] = str_replace("'","",$row[0]);
				$row[0] = str_replace("##","\"",$row[0]);
				
				$row[1] = str_replace("'","",$row[1]);
				$row[1] = str_replace("##","\"",$row[1]);
				
				$sql = "
				UPDATE s_core_config_text SET `value` = '".mysql_real_escape_string($row[1])."'
				WHERE `name` = '".mysql_real_escape_string($row[0])."'
				";
				
				$update = mysql_query($sql);
			}
		}else {
		
			// Load data
			$getTranslation = mysql_query("SELECT objectdata FROM s_core_translations
			WHERE objecttype = 'config_snippets' AND objectlanguage='$isocode'
			");
			$translation = @mysql_result($getTranslation,0,"objectdata");
			if (!empty($translation)){
				$translation = unserialize($translation);
				$insert = false;
			}else {
				$translation = array();
				$insert = true;
			}
			
			unset($data[0]);
			foreach ($data as &$row){
				$row = explode(";",$row);
				$row[0] = str_replace("'","",$row[0]);
				$row[0] = str_replace("##","\"",$row[0]);
				
				$row[2] = str_replace("'","",$row[2]);
				$row[2] = str_replace("##","\"",$row[2]);
				
				$translation[$row[0]]["value"] = $row[2];	
			}
			$translation = serialize($translation);
			
			if ($insert){
				$sql = "
				INSERT INTO s_core_translations (objecttype,objectdata,objectkey,objectlanguage)
				VALUES ('config_snippets','$translation',1,'$isocode') 
				";
				
				$update = mysql_query($sql);
			}else {
				$sql = "
				UPDATE s_core_translations SET objectdata = '$translation'
				WHERE objecttype = 'config_snippets' AND objectlanguage='$isocode'
				";
				
				$update = mysql_query($sql);
			}
		
		}
	}
			
}


if ($_GET["export"]){
	$getSnippets = mysql_query("
	SELECT name, value FROM s_core_config_text
    ORDER BY name ASC
	");
	$getTranslation = mysql_query("SELECT objectdata FROM s_core_translations
	WHERE objecttype = 'config_snippets' AND objectlanguage='{$_GET["export"]}'
	");
	$data = @mysql_result($getTranslation,0,"objectdata");
	if (count($data)){
		$data = unserialize($data);
	}
	if ($_GET["export"]!="de"){
		$csvDump = "Key;Deutsch;{$_GET["export"]}\r\n";
	}else {
		$csvDump = "Key;Deutsch\r\n";
	}
	while ($snippet = mysql_fetch_assoc($getSnippets)){
		$snippet["value"] = html_entity_decode($snippet["value"]);
		$snippet["value"] = "'".$snippet["value"];
		$snippet["value"] = str_replace(";","",$snippet["value"]);
		$snippet["value"] = str_replace("\r","",$snippet["value"]);
		$snippet["value"] = str_replace("\n","<br />",$snippet["value"]);
		$snippet["value"] = str_replace("\"","##",$snippet["value"]);
		
		
		$data[$snippet["name"]]["value"] = html_entity_decode($data[$snippet["name"]]["value"]);
		$data[$snippet["name"]]["value"] = "'".$data[$snippet["name"]]["value"];
		$data[$snippet["name"]]["value"] = str_replace(";","",$data[$snippet["name"]]["value"]);
		$data[$snippet["name"]]["value"] = str_replace("\r","",$data[$snippet["name"]]["value"]);
		$data[$snippet["name"]]["value"] = str_replace("\n","<br />",$data[$snippet["name"]]["value"]);
		$data[$snippet["name"]]["value"] = str_replace("\"","##",$data[$snippet["name"]]["value"]);
		
		$snippet["name2"] = "'".$snippet["name"];
		
		if ($_GET["export"]=="de"){
			$csv[] = array("Key"=>$snippet["name"],"Deutsch"=>$snippet["value"]);
			$csvDump.= "{$snippet["name2"]};{$snippet["value"]}\r\n";
		}else {
			$csv[] = array("Key"=>$snippet["name"],"Deutsch"=>$snippet["value"],"{$_GET["export"]}"=>$data[$snippet["name"]]["value"]);
			$csvDump.= "{$snippet["name2"]};{$snippet["value"]};{$data[$snippet["name"]]["value"]}\r\n";
		}
	}
	$now = gmdate('D, d M Y H:i:s') . ' GMT';
    $USER_BROWSER_AGENT="";

    if (ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
    {
        $USER_BROWSER_AGENT='OPERA';
    }
    else if (ereg('MSIE ([0-9].[0-9]{1,2})',strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
    {
        $USER_BROWSER_AGENT='IE';
    }
    else if (ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
    {
        $USER_BROWSER_AGENT='OMNIWEB';
    }
    else if (ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
    {
        $USER_BROWSER_AGENT='MOZILLA';
    }
    else if (ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version))
    {
        $USER_BROWSER_AGENT='KONQUEROR';
    }
    else
    {
        $USER_BROWSER_AGENT='OTHER';
    }
	$filename = "Export_{$_GET["export"]}_".date("Y-m-d H:i:s");
	$ext = "csv";
    if ($filename!="")
    {
         header('Content-Type: application/octetstream');
         header('Expires: ' . $now);
         if ($USER_BROWSER_AGENT == 'IE')
         {
              header('Content-Disposition: inline; filename="' . $filename . '.' . $ext . '"');
              header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
              header('Pragma: public');
         }
         else
         {
              header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
              header('Pragma: no-cache');
         }

         die($csvDump);
    }
	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Import</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
	<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<style>
	label {
		width: 300px;
	}
	</style>
</head>
<body>

<div class="clear"></div>

<fieldset>
<legend><a class="ico help"></a>Batch-Verarbeitung</legend>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
<?php
// Load all languages
$getLanguages = mysql_query("
SELECT * FROM s_core_multilanguage
");
while ($lang = mysql_fetch_assoc($getLanguages)){
?>
  <tr class="rowcolor2">
       <th class="first-child">Export</th>
	   <td><img src="../../../backend/img/default/icons/page.png" style="margin-right:15px;" /> <?php echo $lang["isocode"] != "de" ? "Deutsch >".$lang["isocode"]: "Deutsch"; ?></td>
	   <td></td>
       <td class="last-child">
		<a style="cursor:pointer" href="bulk.php?export=<?php echo $lang["isocode"] ?>" target="">Exportieren</a>
	  </td>
     </tr>
<?php
}
?>
   </tbody>
</table>
</fieldset>
<fieldset>
<legend><?php echo $sLang["import_xml"]["index_import"] ?></legend>
<form id="myform" name="myform" method="POST" action="bulk.php" enctype="multipart/form-data" target="_self">
<input type="hidden" name="sAPI" value="<?php echo$sCore->sCONFIG['sAPI']?>">
<ul>


 
 <li style="clear: both;">
 	<label for="articles_file">CSV-Datei</label><input type="file" name="articles_file" id="articles_file" />
 </li>
 <li style="clear: both;">
     <a class="ico add" style="cursor:pointer" href="#" onclick="document.myform.submit(); return false;" target=""></a> Import
 </li>
</form>
</fieldset>
</body>
</html>