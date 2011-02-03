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

function countLicences(){
	$getLicences = mysql_query("
	
	");
}

$valueName = "Subshop
";
$valueDelete = true;
$valueTable = "s_core_multilanguage";
$valueAdd = true;
$valueDescription = "name";

$substitute = array(
"id"=>"hide",
"isocode"=>"Unique - Code (z.B. en)",
"parentID"=>"ID der Stammkategorie",
"flagstorefront"=>"hide",
"flagbackend"=>"Grafik für Darstellung in Backend",
"skipbackend"=>"Übersetzungsmöglichkeit im Backend ausblenden?",
"name"=>"Bezeichnung",
"defaultcustomergroup"=>"Standard-Kundengruppe",
"template"=>"Template Verz. (unterhalb /templates/)",
"doc_template"=>"Template-Pfad Belege",
"separate_numbers"=>"Eigene Nummernkreise für Belege",
"domainaliase"=>"Liste der gültigen Domains",
"defaultcurrency"=>"Standardwährung",
"default"=>"hide",
"switchCurrencies"=>"Auswählbare Währungen",
"switchLanguages"=>"Auswählbare Sprachen","fallback"=>"Texte von Subshop erben","navigation"=>"Navigations-Mapping",
"inheritstyles"=>"CSS-Layout von Hauptshop (0/de) erben"
);
if (!empty($_POST["switchCurrencies"])){
	$_POST["switchCurrencies"] = implode("|",$_POST["switchCurrencies"]);
}
if (!empty($_POST["switchLanguages"])){
	$_POST["switchLanguages"] = implode("|",$_POST["switchLanguages"]);
}
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$tmp_field_value = mysql_real_escape_string($_POST[$row["Field"]]);
			if($row["Field"]=='domainaliase') {
				$tmp_field_value = trim(preg_replace('#\s+#m', "\n", $tmp_field_value));
			}
			$updateSQL[] = "{$row["Field"]} = '{$tmp_field_value}'";
		}
}
$updateSQL = implode(",",$updateSQL);
//echo $updateSQL;
// Building update query
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$insertHead[] = "{$row["Field"]}";
			$tmp_field_value = mysql_real_escape_string($_POST[$row["Field"]]);
			$valueHead[] = "'{$tmp_field_value}'";
		}
}

$insertHead = implode(",",$insertHead);
$valueHead = implode(",",$valueHead);

		
if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM $valueTable WHERE id={$_GET["delete"]}
	");
	
	$sInform = "$valueName wurde gelöscht";
}


if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
	

	if (!$_POST["tax"]) $_POST["tax"] = "0";
	// $_POST["isocode"]
	if (!$sError){
		
		// Check if unique code is already set
		$tmpKey = $_POST["isocode"];
		if (empty($tmpKey)&&!empty($_GET["edit"])){
			echo "<br />Fehler: Bitte geben Sie einen eindeutigen Schlüssel für diesen Subshop ein!<br /><br />";
		}else {
			if ($_GET["edit"]){
				$check = mysql_query("
				SELECT id FROM s_core_multilanguage WHERE isocode = '$tmpKey' AND id != {$_GET["edit"]}
				");
				if (@mysql_num_rows($check)){
					echo "<br />Fehler: Bitte geben Sie einen eindeutigen Schlüssel für diesen Subshop ein!<br /><br />";
				}else {
				$sql = "
				UPDATE $valueTable SET 
				$updateSQL
				WHERE id={$_GET["edit"]}
				";
				
				
				$insertArticle = mysql_query($sql);
				}
			}else {
				// Check if this uniquekey is already in use
				$check = mysql_query("
				SELECT id FROM s_core_multilanguage WHERE isocode = '$tmpKey'
				");
				if (@mysql_num_rows($check)){
					echo "<br />Fehler: Bitte geben Sie einen eindeutigen Schlüssel für diesen Subshop ein!<br /><br />";
				}else {
					$sql = "
					INSERT INTO $valueTable ($insertHead)
					VALUES ($valueHead)
					";
					$insertArticle = mysql_query($sql);
				}
				$id = mysql_insert_id();
				
				$sql = "UPDATE $valueTable SET isocode=id WHERE id=$id";
				mysql_query($sql);				
			}
		}
		/*echo $sql;
		echo mysql_error();
		*/
		
		//Seperate numbers options	
		$q = mysql_query("SELECT id FROM `s_core_multilanguage`
				WHERE `id` = '{$_GET["edit"]}' AND `default` = '1'");	
		//if subshop != default
		if(mysql_num_rows($q) == 0)
		{					
			if(!empty($_POST['separate_numbers']))
			{
				
				$desc = array();
				$desc[0] = "Rechnung";
				$desc[1] = "Lieferscheine";
				$desc[2] = "Gutschriften";
				
				//check for existent ordernumbers
				for($i=0; $i<=2; $i++)
				{
					$name = sprintf("doc_%s_%s", $i, $_GET['edit']);
					//check
					$chkQ = mysql_query("
						SELECT *
						FROM `s_order_number`
						WHERE `name` LIKE '{$name}'
					");
					if(!mysql_num_rows($chkQ))
					{
						$desc_str = sprintf("%s - Subshop %d", $desc[$i], $_GET['edit']);
						mysql_query("INSERT INTO `s_order_number` (
							`number` ,`name` ,`desc`)	VALUES (
							'10000', '{$name}', '{$desc_str}')");
					}
				}
			}					
		}else{
			if(!empty($_POST['separate_numbers']))
			{
				//default - reset seperate number
				mysql_query("UPDATE `s_core_multilanguage` 
						SET `separate_numbers` = '0' 
						WHERE `id` = '{$_GET["edit"]}' LIMIT 1");
			}
		}
		
	
		
		if ($insertArticle){
			$sInform = "Subshop wurde gespeichert";
		}else {
			
		}
	}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM $valueTable WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = "$valueName ".$sLang["presettings"]["multilanguage_cant_be_found"];
	}else {		
		$getCustomerGroup = mysql_fetch_array($getSite);
		if (!empty($getCustomerGroup["switchCurrencies"])){
			//die($getCustomerGroup["switchCurrencies"]);
			$getCustomerGroup["switchCurrencies"] = array_flip(explode("|",$getCustomerGroup["switchCurrencies"]));
		}
		if (!empty($getCustomerGroup["switchLanguages"])){
			//die($getCustomerGroup["switchCurrencies"]);
			$getCustomerGroup["switchLanguages"] = array_flip(explode("|",$getCustomerGroup["switchLanguages"]));
		}
	}
}
?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
</head>

<body >

<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteCustomer":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
			break;
	}
}

function deleteShop(ev,text){
		parent.parent.sConfirmationObj.show('Soll Subshop "'+text+'" wirklich gelöscht werden?',window,'deleteCustomer',ev);
}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
};
</script>
<?php
// TEMPLATE FÜR NICHT LIZENZIERTE MODULE //

foreach ($sCore->sLicenseData as $key => $value){
	if (preg_match("/sLANGUAGEPACK/",$key) && $sCore->sCheckLicense("","",$value)){
		$count = str_replace("sLANGUAGEPACK","",$key);
	}
}
if (empty($count)) $count = "0";
$countSub = mysql_query("
			SELECT COUNT(id) AS countSubshops FROM s_core_multilanguage 
			");
$countSub = mysql_result($countSub,0,"countSubshops")-1;
if ($countSub>=$count) $licenceFailed = true;
if ($count>0){
	$title = "Sie nutzen derzeit $countSub von $count lizenzierten Subshops.";
}else {
	$title = "Sie haben noch keine Subshop-Lizenzen erworben.";
}
echo $sCore->sDumpLicenceInfo("../../../","Modul Subshop","Mit Shopware können Sie beliebig viele Subshops erstellen um beispielsweise unterschiedliche Zielgruppen mit einem unterschiedlichen Sortiment oder einem ganz anderen Design anzusprechen. Jeder Subshop kann über eine eigene URL erreichbar sein, und sich in Sachen Artikel, Preise, Währungen, Sprachen und Design komplett vom Hauptshop unterscheiden. Dabei wird die gesamte Verwaltung und die Bestellabwicklung zentral über ein Backend abgewickelt. Trotz beliebig vieler Subshops brauchen Sie alle Daten nur einmal pflegen. So können Sie ohne viel Aufwand unterschiedliche Zielgruppen ganz individuell ansprechen um Ihre Umsätze noch weiter zu steigern.","http://www.shopware-ag.de/Subshop-Lizenz-weiter.-_detail_16_195.html","sLANGUAGEPACK1",$title);
?>
<?php
if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["new"] ? "$valueName ".$sLang["presettings"]["multilanguage_Creating"] : "$valueName ".$sLang["presettings"]["multilanguage_edit"] ?></legend>
		<ul>
		
	
		<!-- Felder ausgeben -->
		<?php
		
		
		$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
		
		$old_template = (bool) preg_match('#templates/[0-9]+#', $getCustomerGroup['template']);
						
		if(!$old_template) {
			$substitute['isocode'] = 'hide';
			$substitute['inheritstyles'] = 'hide';
			$substitute['locale'] = 'Sprache';
			if(empty($getCustomerGroup["isocode"])) {
				echo '<input name="isocode" type="hidden" value="'.(int) $_GET['edit'].'" />';
			} else {
				echo '<input name="isocode" type="hidden" value="'.$getCustomerGroup["isocode"].'" />';
			}
		}
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
			$intFields = array("skipbackend","separate_numbers","inheritstyles");
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		  	
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	  if ($fieldName=="flagbackend"){
		   	   	
				   
					echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
		   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
		   	   		";
					$path = '../../../backend/img/default/icons/flags/*';
					$files = glob($path);
		   	    	natsort($files);
				    /* Das ist der korrekte Weg, ein Verzeichnis zu durchlaufen. */
				   foreach ($files as $file){
				   		$file = basename($file);
				        if (preg_match("/\.png/",$file)){
				        	$selected = $getCustomerGroup["flagbackend"]==$file ? "selected" : "";
				        	echo "<option value=\"$file\" $selected>$file</option>";
				        }
				    }
				    echo "</select></li>";
				   
		   	    }
		   	    elseif ($fieldName=="doc_template"){
		   	    
		   	    	$path = '../../../../templates/*';
		   	    	$dirs = glob($path, GLOB_ONLYDIR);
		   	    	natsort($dirs);
		   	   		if (!empty($dirs)) {
						echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
			   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
			   	   		";
					    foreach ($dirs as $dir)
					    {
					    	$file = basename($dir);
					    	if(in_array($file, array('.svn'))) continue;
					    	
					    	if(preg_match('#^[0-9]+$#', $file)) {
					    		$sdirs = glob($dir.'/*', GLOB_ONLYDIR);
								if(!empty($sdirs))
					    		foreach ($sdirs as $sdir) {
					    			$sfile = basename($sdir);
					    			if(in_array($sfile, array('.svn'))||!file_exists($sdir.'/forms')) continue;
					    			$selected = $getCustomerGroup["doc_template"]=="$file/$sfile/forms" ? "selected" : "";
					    			echo "<option value=\"$file/$sfile/forms\" $selected>$file/$sfile</option>";
					    		}
					    	} else {
					    		//if(!file_exists($dir.'/documents')) continue;
					    		if(in_array($file, array('.svn'))||strpos($file, '_')===0) continue;
					    		$selected = $getCustomerGroup["doc_template"]=="templates/".$file ? "selected" : "";
					       		echo "<option value=\"templates/$file\" $selected>$file</option>";
					    	}
					    }
					    echo "</select></li>";
			   	    }
		   	    }
		   	    elseif ($fieldName=="template"){
		   	    
		   	    	$path = '../../../../templates/*';
		   	    	$dirs = glob($path, GLOB_ONLYDIR);
		   	    	ksort($dirs);
		   	   		if (!empty($dirs)) {
						echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
			   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
			   	   		";
					    foreach ($dirs as $dir)
					    {
					    	$file = basename($dir);
					    	if(in_array($file, array('.svn'))||strpos($file, '_')===0) continue;
					        $selected = $getCustomerGroup["template"]=="templates/".$file ? "selected" : "";
					        echo "<option value=\"templates/$file\" $selected>$file</option>";
					    }
					    echo "</select></li>";
			   	    }
		   	    }
		   	   	elseif ($fieldName=="domainaliase"){
   	   		  	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:<br />(Nur wenn Domain von der des Hauptshops abweicht, Format www.domain.de, je Domain eine Zeile (ohne http://)) </label><textarea name=\"$fieldName\" style=\"height:225px;width:250px\">{$getCustomerGroup[$row["Field"]]}</textarea></li>";
		   	   	}
		   	   	elseif ($fieldName=="locale")
		   	   	{
		   	   		// Query available customergroups
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
		   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
		   	   		";
	   	   			$result = mysql_query("
		   	   			SELECT * FROM `s_core_locales` ORDER BY `id` ASC
		   	   		");
	   	   			$getCustomerGroup["locale"] = (int) $getCustomerGroup["locale"];
	   	   			if(!$getCustomerGroup["locale"]) $getCustomerGroup["locale"] = 1;
	   	   			while ($group = mysql_fetch_assoc($result)) {
		   	   			$selected = $group["id"] == $getCustomerGroup["locale"] ? "selected" : "";
		   	   			echo "<option value=\"{$group["id"]}\" $selected>{$group["language"]} / {$group["territory"]}</option>";
		   	   		}
		   	   		echo "</select></li>";
		   	   	}
		   	   	elseif ($fieldName=="fallback")
		   	   	{
		   	   		// Query available customergroups
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
		   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
		   	   		";
		   	   		if(!$getCustomerGroup["default"]) {
		   	   			$result = mysql_query("
			   	   			SELECT isocode, name, `default` FROM s_core_multilanguage WHERE id!='{$_GET["edit"]}' AND (skipbackend=0 OR `default`=1) ORDER BY `default` DESC, id ASC
			   	   		");
		   	   			while ($group = mysql_fetch_assoc($result)) {
		   	   				//if(mysql_num_rows($result)>1&&$group['default']) continue;
		   	   				if($group['default']) $group['isocode'] = '';
			   	   			$selected = $group["isocode"] == $getCustomerGroup["fallback"] ? "selected" : "";
			   	   			echo "<option value=\"{$group["isocode"]}\" $selected>{$group["name"]}</option>";
			   	   		}
		   	   		}
		   	   		echo "</select></li>";
		   	   	}
		   	   	elseif ($fieldName=="defaultcustomergroup")
		   	   	{
		   	   		// Query available customergroups
		   	   		$getCustomerGroups = mysql_query("
		   	   		SELECT id, groupkey, description FROM s_core_customergroups ORDER BY id ASC
		   	   		");
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
		   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
		   	   		";
		   	   		while ($group = mysql_fetch_assoc($getCustomerGroups)){
		   	   			$selected = $group["groupkey"] == $getCustomerGroup[$row["Field"]] ? "selected" : "";
		   	   			echo "<option value=\"{$group["groupkey"]}\" $selected>{$group["description"]} ({$group["groupkey"]})</option>";
		   	   		}
		   	   		echo "</select></li>";
		   	   	}
		   	   	elseif ($fieldName=="defaultcurrency")
		   	   	{
		   	   		// Query available customergroups
		   	   		$getCustomerGroups = mysql_query("
		   	   		SELECT id, currency, name AS description FROM s_core_currencies ORDER BY id ASC
		   	   		");
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
		   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
		   	   		";
		   	   		while ($group = mysql_fetch_assoc($getCustomerGroups)){
		   	   			$selected = $group["id"] == $getCustomerGroup[$row["Field"]] ? "selected" : "";
		   	   			echo "<option value=\"{$group["id"]}\" $selected>{$group["description"]} ({$group["currency"]})</option>";
		   	   		}
		   	   		echo "</select></li>";
		   	   	}
		   	   	elseif ($fieldName=="switchCurrencies")
		   	   	{
		   	   		// Query available customergroups
		   	   		$getCustomerGroups = mysql_query("
		   	   		SELECT id, currency, name AS description FROM s_core_currencies ORDER BY id ASC
		   	   		");
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label></li><li class=\"clear\"/>
		   	   		
		   	   		";
		   	   		while ($group = mysql_fetch_assoc($getCustomerGroups)){
		   	   			$selected = isset($getCustomerGroup["switchCurrencies"][$group["id"]]) ? "checked" : "";
		   	   			echo "<li><input type=\"checkbox\" name=\"switchCurrencies[]\" value=\"{$group["id"]}\" $selected>{$group["description"]} ({$group["currency"]})<li class=\"clear\"/>";
		   	   		}
		   	   		
		   	   	}
		   	   	elseif ($fieldName=="switchLanguages")
		   	   	{
		   	   		// Query available customergroups
		   	   		$getCustomerGroups = mysql_query("
		   	   		SELECT * FROM s_core_multilanguage
		   	   		");
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label></li><li class=\"clear\"/>
		   	   		
		   	   		";
		   	   		while ($group = mysql_fetch_assoc($getCustomerGroups)){
		   	   			$selected = isset($getCustomerGroup["switchLanguages"][$group["id"]]) ? "checked" : "";
		   	   			echo "<li><input type=\"checkbox\" name=\"switchLanguages[]\" value=\"{$group["id"]}\" $selected>{$group["name"]} ({$group["isocode"]})<li class=\"clear\"/>";
		   	   		}
		   	   		
		   	   	}elseif (in_array($fieldName,$intFields)){
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>
		   	   		<select name=\"$fieldName\" style=\"height:25px;line-height:25px;width:250px;\">
		   	   		";
		   	   		
	   	   			$value = $getCustomerGroup[$row["Field"]];
	   	   			if ($value=="0"){
	   	   				$select2 = "selected";
	   	   				$select1 = "";
	   	   			}else {
	   	   				$select1 = "selected";
	   	   				$select2 = "";
	   	   			}
	   	   			echo "<option value=\"0\" $select2>Nein</option>";
	   	   			echo "<option value=\"1\" $select1>Ja</option>";
			   	   	echo "</select></li>";
		   	   	}
		   	   	else {
		   	   		
			   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" /></li>";
		   	   	}
			   	   
			   	   echo "<li class=\"clear\"/>";
		   	}

	   }

		
		?>	
		<!-- // Felder ausgeben -->
		<li class="clear"></li>
			<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["multilanguage_save"] ?></div></button></li>	
		
					
		</ul>
		</div>
		<li class="clear"></li>
		
		
		</ul>
		</fieldset>
		</form>
<?php
}
?>
		


        
        
        
<fieldset class="col2_cat2">
<legend>Angelegte Subshops</legend>

<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
   		 if (($count)!=0){
			// Query Related-Articles
			$count+=1;
			$sql = "
			SELECT id, name as description,`default` FROM $valueTable ORDER BY id ASC LIMIT $count
			";
   		 }else {
   		 	$sql = "
			SELECT id, name as description,`default` FROM $valueTable WHERE `default`=1 ORDER BY id ASC
			";
   		 }
			//echo $sql;
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
			if (!$article["default"]){
				$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteShop({$article["id"]},'{$article["description"]}')\"></a>";			
				$icon = "flag_orange.png";
				$main = false;
			}else {
				$article["description"] = $sCore->sCONFIG["sHOST"];
				$icon = "flag_blue.png";
				$main = true;
			}
			
		?>
        
     <tr class="rowcolor2">
       <th class="first-child" <?php echo $main ? "style=\"font-weight:bold\"" : ""?>><img src="../../../backend/img/default/icons4/<?php echo $icon ?>" style="margin:0 15px 0 0;" /><?php echo $article["description"]?> (ID: <?php echo $article["id"] ?>) </th>
       <td class="last-child"><?php echo $delete ?><a href="<?php echo $_SERVER["PHP_SELF"]."?edit=".$article["id"]?>" style="cursor:pointer" class="ico pencil"></a></td>
     </tr>
	 	<?php
			}
		?>
   </tbody>
</table>
</fieldset>

<?php
if ($valueAdd && !$licenceFailed){
?>
<div class="buttons" id="buttons" style="margin-left:5px;">
		<ul>		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $PHP_SELF."?new=1" ?>" class="bt_icon flag_orange" style="text-decoration:none;"><?php echo $valueName ?> <?php echo $sLang["presettings"]["multilanguage_Creating"] ?></a></li>	
		
		</ul>
		</div>
		<br/><div class="fixfloat"></div><br/>


<?php
}
?>

</body>

</html>