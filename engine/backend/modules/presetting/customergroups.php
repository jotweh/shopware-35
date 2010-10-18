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
// *****************
?>
<?php


if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM s_core_customergroups WHERE id={$_GET["delete"]}
	");
	
	$sInform = $sLang["presettings"]["customergroups_customergroup_deleted"];
}


if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
		
	if (!$_POST["tax"]) $_POST["tax"] = "0";
	if (!$_POST["taxinput"]) $_POST["taxinput"] = "0";
	if (!$_POST["mode"]){
		$_POST["mode"] = "0";
		$_POST["discount"] = "0";
	}
	
	//if ($_POST["minimumorder"]&&!$_POST["minimumordersurcharge"]) $sError = $sLang["presettings"]["customergroups_Please_define_a"];
	if (!$_POST["minimumorder"]) $_POST["minimumorder"] = "0";
	if (!$_POST["minimumordersurcharge"]) $_POST["minimumordersurcharge"] = "0";
	
	
	if (!$_POST["discount"]) $_POST["discount"] = "0";
	if (!$_POST["description"]) $sError = $sLang["presettings"]["customergroups_enter_a_name_for_customergroup"];
	
	if (!$sError){
		if ($_GET["edit"]){
			
			// Delete all previous discount and re-insert them
			$deleteAllDiscounts = mysql_query("
			DELETE FROM s_core_customergroups_discounts WHERE groupID={$_GET["edit"]}
			");
			foreach ($_POST["basketdiscountstart"] as $key => $value){
				if ($_POST["basketdiscountstart"][$key] && $_POST["basketdiscount"][$key]){
					
					$insertDiscount = mysql_query("
					INSERT INTO s_core_customergroups_discounts (groupID, basketdiscount, basketdiscountstart)
					VALUES ({$_GET["edit"]},{$_POST["basketdiscount"][$key]},{$_POST["basketdiscountstart"][$key]})
					");
					if (!$insertDiscount){
						echo $sLang["presettings"]["customergroups_Cart_rebate_could_not_be_inserted"]." {$_POST["basketdiscountstart"][$key]} ".$sLang["presettings"]["customergroups_rebate"]." {$_POST["basketdiscount"][$key]}<br />";
						echo mysql_error()."<br />";
					}
				}
			}
			
			
			
			$sql = "
			UPDATE s_core_customergroups
			SET
			description='{$_POST["description"]}',
			tax={$_POST["tax"]},
			taxinput={$_POST["taxinput"]},
			mode={$_POST["mode"]},
			discount={$_POST["discount"]},
			minimumorder={$_POST["minimumorder"]},
			minimumordersurcharge={$_POST["minimumordersurcharge"]}
			WHERE id={$_GET["edit"]}
			";
			
			$insertArticle = mysql_query($sql);
		}else {
			$sql = "
			INSERT INTO s_core_customergroups
			(description, tax, taxinput, mode, discount, minimumorder, minimumordersurcharge)
			VALUES ('{$_POST["description"]}',
			{$_POST["tax"]}, {$_POST["taxinput"]},
			{$_POST["mode"]}, {$_POST["discount"]}, '{$_POST["minimumorder"]}','{$_POST["minimumordersurcharge"]}'
			)
			";
			$insertArticle = mysql_query($sql);
			
			$insertID = mysql_insert_id();
			
			$sql = "
			UPDATE s_core_customergroups
			SET groupkey='$insertID' WHERE
			id=$insertID 
			";
			
			$updateArticle = mysql_query($sql);
			
			
		}
		/*echo $sql;
		echo mysql_error();
		*/
		
	
		
		if ($insertArticle){
			$sInform = $sLang["presettings"]["customergroups_entry_saved"];
		}else {
			
		}
	}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM s_core_customergroups WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = $sLang["presettings"]["customergroups_customergroup_not_found"];
	}else {		
		$getCustomerGroup = mysql_fetch_array($getSite);
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
<style>
td {
font-size:10px
}
</style>
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

function deleteCustomerGroup(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["presettings"]["customergroups_should_the_customergroup"] ?> "'+text+'" <?php echo $sLang["presettings"]["customergroups_really_be_deleted"] ?>',window,'deleteCustomer',ev);
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
if (!$sCore->sCheckLicense("","",$sCore->sLicenseData["sGROUPS"])){
echo $sCore->sDumpLicenceInfo("../../../","Modul Kundengruppen","Wenn Sie mehr als eine Käuferschicht bedienen wollen, ist dieses Modul genau das Richtige! Definieren Sie beliebig viele Kundengruppen und statten diese mit eigenen Preisen, Staffeln oder Rabatten aus. Ihre Shopware ist somit für B2B und B2C optimal aufgestellt.","http://www.shopware-ag.de/Haendlerbereich-Kunden.-_detail_67_196.html","sGROUPS");
$licenceFailed = true;
}
if (!$licenceFailed){ 
?>
	<div class="buttons" id="buttons" style="margin-left:5px;">
		<ul>		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $_SERVER["PHP_SELF"]."?new=1" ?>" class="bt_icon group" style="text-decoration:none;"><?php echo $sLang["presettings"]["customergroups_new_customergroup"] ?></a></li>	
		
		</ul>
		</div>
		<br/><div class="fixfloat"></div><br/><br/><br/><br/>
		

<?php
}
?>
<?php
if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["new"] ? $sLang["presettings"]["customergroups_new_customergroup"] : $sLang["presettings"]["customergroups_edit_customergroup"] ?></legend>
		<ul>
		
	
		<!-- Felder ausgeben -->
		<?php
		$getFields = mysql_query("SHOW COLUMNS FROM s_core_customergroups");
		
		
		$substitute = $sLang["presettings"]["customergroups_array"];
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
			
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		  	
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	  
		   	   if ($row["Type"]=="int(1)"){
		   	   	echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>";
		   	   	if ($getCustomerGroup[$row["Field"]]){
		   	   		$selYes = "selected";
		   	   		$selNo = "";
		   	   	}else {
		   	   		$selYes = "";
		   	   		$selNo = "selected";
		   	   	}
		   	   	echo "<select name=\"{$fieldName}\" id=\"\" style=\"height:25px;width:250px\" class=\"w200\">";
		   	   	echo "<option value=\"1\" $selYes>".$sLang["presettings"]["customergroups_yes"]."</option>";
		   	   	echo "<option value=\"0\" $selNo>".$sLang["presettings"]["customergroups_no"]."</option>";
		   	   	echo "</select>";
		   	   	echo "</li>";
		   	    echo "<li class=\"clear\"/>";
		   	   }
		   	   elseif ($fieldName=="mode"){
		   	   		echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>";
		   	   		if ($getCustomerGroup[$row["Field"]]){
		   	   			$selYes = "";
		   	   			$selNo = "selected";
			   	   	}else {
			   	   		$selYes = "selected";
			   	   		$selNo = "";
			   	   	}
			   	   	echo "<select onchange=\"if (this.value=='0') $('discount').setStyle('display','none'); else $('discount').setStyle('display','block'); \" name=\"{$fieldName}\" id=\"\" style=\"height:25px;width:250px\" class=\"w200\">";
			   	   	echo "<option value=\"0\" $selYes>".$sLang["presettings"]["customergroups_own_prices_per_Article"]."</option>";
			   	   	if ($getCustomerGroup["groupkey"]!="EK"){
			   	   	echo "<option value=\"1\" $selNo>".$sLang["presettings"]["customergroups_Global_Discount"]."</option>";
			   	   	}
			   	   	echo "</select>";
			   	   	echo "</li>";
			   	    echo "<li class=\"clear\"/>";
		   	   }elseif ($fieldName=="discount"){
			   	   	if (!$getCustomerGroup["mode"]){
			   	   		$style = "style=\"display:none\"";
			   	   	}
			   	   	 echo "<li $style id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" /></li>";
			   	    echo "<li class=\"clear\"/>";
		   	   }
		   	   else {
		   	   
			   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" /></li>";
			   	    echo "<li class=\"clear\"/>";
		   	   }
		       
		        
		      
		       
		      

		   	}

	   }

		
		?>	
		<?php
		if ($_GET["edit"]){
		?>
		<!-- // Felder ausgeben -->
		<li>
		<table border=0 cellpadding="2" cellspacing="2" width="100%">
		<tr>
		<td><strong><?php echo $sLang["presettings"]["customergroups_From_shopping_cart_value"] ?></strong></td>
		<td><strong><?php echo $sLang["presettings"]["customergroups_shopping_cart_discount"] ?></strong></td>
		</tr>
		
		<?php
			// Read all basket discounts
			$getAllDiscounts = mysql_query("
			SELECT basketdiscount, basketdiscountstart FROM s_core_customergroups_discounts 
			WHERE groupID = {$_GET["edit"]}
			ORDER BY basketdiscountstart ASC
			");
			$i = 0;
			while ($discount = mysql_fetch_array($getAllDiscounts)){
		?>	
		<!-- Start -->
		<tr>
		<td><input name="basketdiscountstart[<?php echo $i ?>]" type="text"  style="height:25px;width:250px" class="w200" value="<?php echo $discount["basketdiscountstart"] ?>" /></td>
		<td><input name="basketdiscount[<?php echo $i ?>]" type="text"  style="height:25px;width:250px" class="w200" value="<?php echo $discount["basketdiscount"] ?>" /></td>
		</tr>
		<!-- Ende -->
		
		<?php
			$i++;
			}
		?>
		<!-- Start -->
		<tr>
		<td><input name="basketdiscountstart[<?php echo $i ?>]" type="text"  style="height:25px;width:250px" class="w200" value="0" /></td>
		<td><input name="basketdiscount[<?php echo $i ?>]" type="text"  style="height:25px;width:250px" class="w200" value="0" /></td>
		</tr>
		<!-- Ende -->
		
		</table>
		
		<?php
		} // Nur im Edit Modus
		?>
						<li class="clear"></li>
	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["customergroups_save"] ?></div></button></li>	
		
					
		</ul>
		</div>
		<li class="clear"></li>
		
		</li><li class="clear"/>
		
		</ul>
		</fieldset>
		</form>
<?php
}
?>
		
		
	

        
        
        
<fieldset class="col2_cat2">
<legend><?php echo $sLang["presettings"]["customergroups_created_customergroups"] ?></legend>
<p><img src="../../../backend/img/default/icons/information.png" style="margin:0 15px 0 0;" /><?php echo $sLang["presettings"]["customergroups_Deleting_a_group_of_customers"] ?></p>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
   		 
// TEMPLATE FÜR NICHT LIZENZIERTE MODULE //
			if (!$sCore->sCheckLicense("","",$sCore->sLicenseData["sGROUPS"])){
			// Query Related-Articles
			$sql = "
			SELECT id, description, groupkey FROM s_core_customergroups WHERE `groupkey`='EK' ORDER BY id ASC
			";
			
			}else {
			$sql = "
			SELECT id, description, groupkey FROM s_core_customergroups ORDER BY id ASC
			";	
			}
			
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
				
				// Check if this group is assigned to any customer
				$queryCustomers = mysql_query("
				SELECT id FROM s_user WHERE customergroup='{$article["groupkey"]}'
				");
				$article["description"] = str_replace("\"","",$article["description"]);
				if ($article["groupkey"]!="EK" && !@mysql_num_rows($queryCustomers)){
					$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteCustomerGroup({$article["id"]},'{$article["description"]}')\"></a>";			}else {
						$delete = "";
					}
		?>
        
     <tr class="rowcolor2">
       <th class="first-child"><img src="../../../backend/img/default/icons/group.png" style="margin:0 15px 0 0;" /><?php echo $article["description"]?> (ID: <?php echo $article["groupkey"] ?>)</th>
       <td class="last-child"><?php echo $delete ?><a href="<?php echo $_SERVER["PHP_SELF"]."?edit=".$article["id"]?>" style="cursor:pointer" class="ico pencil"></a></td>
     </tr>
	 	<?php
			}
		?>
   </tbody>
</table>
</fieldset>

</body>

</html>