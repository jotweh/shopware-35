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
	DELETE FROM s_core_pricegroups WHERE id={$_GET["delete"]}
	");
	
	$sInform = $sLang["presettings"]["pricegroup_pricegroup_deleted"];
}


if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
		

	
	
	if (!$_POST["discount"]) $_POST["discount"] = "0";
	if (!$_POST["description"]) $sError = $sLang["presettings"]["pricegroup_enter_a_name_for_the_customergroup"];
	
	if (!$sError){
		if ($_GET["edit"]){
			
			// Delete all previous discount and re-insert them
			$deleteAllDiscounts = mysql_query("
			DELETE FROM s_core_pricegroups_discounts WHERE groupID={$_GET["edit"]}
			");
			/*
					<td style="width:35px;font-weight:bold"><input type="text" style="width:45px" name="pricegroup[<?php echo $i ?>]">
				</td>
		
				<?php
			foreach ($customergroups as $customergroup){
				?>
					<td style="width:35px;font-weight:bold"><input type="text" style="width:45px"  name="prices[<?php echo $i ?>][<?php echo $customergroup["id"] ?>]"></td>
			*/
			foreach ($_POST["pricegroup"] as $key => $value){
				if ($_POST["pricegroup"][$key]){
					foreach ($_POST["prices"][$key] as $keyGroup => $keyValue){
						if (!$value) continue;
						
						if (!$keyValue) $keyValue = "0";
						$insertRow = mysql_query("
						INSERT INTO s_core_pricegroups_discounts (groupID, customergroupID, discount, discountstart)
						VALUES (
						{$_GET["edit"]},
						$keyGroup,
						$keyValue,
						$value
						)
						");
					}
				}
			}
			
			
			
			$sql = "
			UPDATE s_core_pricegroups
			SET
			description='{$_POST["description"]}'
			WHERE id={$_GET["edit"]}
			";
			
			$insertArticle = mysql_query($sql);
		}else {
			$sql = "
			INSERT INTO s_core_pricegroups
			(description)
			VALUES ('{$_POST["description"]}'
			)
			";
			$insertArticle = mysql_query($sql);

			
			
			
		}
		/*echo $sql;
		echo mysql_error();
		*/
		
	
		
		if ($insertArticle){
			$sInform = $sLang["presettings"]["pricegroup_entry_saved"];
		}else {
			
		}
	}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM s_core_pricegroups WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = $sLang["presettings"]["pricegroup_pricegroup_not_found"];
	}else {		
		$getPriceGroup = mysql_fetch_array($getSite);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">

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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["presettings"]["pricegroup_should_the_pricegroup"] ?> "'+text+'" <?php echo $sLang["presettings"]["pricegroup_really_deleted"] ?>',window,'deleteCustomer',ev);
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

if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["new"] ? $sLang["presettings"]["pricegroup_new_pricegroup"] : $sLang["presettings"]["pricegroup_edit_pricegroup"] ?></legend>
		<ul>
		
	
		<!-- Felder ausgeben -->
		<?php
		$getFields = mysql_query("SHOW COLUMNS FROM s_core_pricegroups");
		
		
		$substitute = $sLang["presettings"]["pricegroup_array"];
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
			
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		  	
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	  
		   	  
				echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getPriceGroup[$row["Field"]]}\" /></li>";
				echo "<li class=\"clear\" />";
		   	
		       
		        
		      
		       
		      

		   	}

	   }

		
		?>	
		<?php
		if ($_GET["edit"]){
			// Render Matrix	
			// Read available customergroups
			$getCustomergroups = mysql_query("
			SELECT * FROM s_core_customergroups
				ORDER BY id ASC
			");
			while($customergroup=mysql_fetch_assoc($getCustomergroups)){
				$customergroups[$customergroup["id"]] = $customergroup;
			}
		?>
		</li>
		<li class="clear"></li>
		
		
		<?php
		//$getPriceGroup
			$getDiscounts = mysql_query("
			SELECT * FROM s_core_pricegroups_discounts WHERE groupID={$getPriceGroup["id"]} 
			ORDER BY discountstart ASC
			");
			while ($discount=mysql_fetch_assoc($getDiscounts)){
				$discounts[$discount["discountstart"]][$customergroups[$discount["customergroupID"]]["groupkey"]] = $discount["discount"];
			}
			
			
			
		?>
		<li>
	
		
	
	
		
		<br/>
		<fieldset class="grey" style="margin:0 0 0 160px;padding:0 0 0 0;">
		<table cellpadding="2" cellspacing="2" width="100%">
		<tr>
		<td class="th_bold"><?php echo $sLang["presettings"]["pricegroup_From_pieces"] ?></td>

		<?php
			foreach ($customergroups as $customergroup){
				?>
					<td class="th_bold"><?php echo $customergroup["groupkey"] ?> (%)</td>
				<?php
			}
		?>
		</tr>
		<!-- Bestehende Datensätze-->
		<?php
		foreach ($discounts as $discount => $discountGroups){
			$i++;
			?>
			<tr style="background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x; height:40px;border-bottom: 1px solid #a0a0a0;">
			<td><input type="text" style="width:45px" name="pricegroup[<?php echo $i ?>]" value="<?php echo $discount ?>">
			</td>
			<?php
				foreach ($customergroups as $customergroup){
					?>
						<td><input type="text" style="width:45px"  name="prices[<?php echo $i ?>][<?php echo $customergroup["id"] ?>]" value="<?php echo $discounts[$discount][$customergroup["groupkey"]] ?>"></td>
					<?php
				}
			?>
			</tr>
			<?php
		}
		$i++;
		?>
		<!-- Neuer Datensatz-->
		<tr>
		<td><input type="text" style="width:45px" name="pricegroup[<?php echo $i ?>]">
		</td>

		<?php
			foreach ($customergroups as $customergroup){
				?>
					<td><input type="text" style="width:45px"  name="prices[<?php echo $i ?>][<?php echo $customergroup["id"] ?>]"></td>
				<?php
			}
		?>
		</tr>
			
		</table>
		</fieldset>
		
		</li>
		
		<?php
			
			
		} 	// Nur im Edit Modus
		
		?>
		<br />
		<li>
		<div class="buttons" id="div">
	      <ul>
	      	<li id="buttonTemplate" class="buttonTemplate">
	        <button type="submit" value="send" class="button">
	        <div class="buttonLabel">Speichern</div>
	        </button>
	       </li>
	      </ul>
		</div>
		</li>
		</ul>
		<div class="clear"></div>
		</fieldset>
		<div class="clear"></div>	
		</form>
		<div class="clear"></div>
		<?php
		}
		?>
		
		
		<br />

        
        

<fieldset class="col2_cat2">
<legend><?php echo $sLang["presettings"]["pricegroup_created_pricegroups"] ?></legend>
<form action="<?php echo $_SERVER["PHP_SELF"] ?>?new=1" method="GET">
<input type="hidden" name="new" value="1">
<div class="buttons" id="div">
	      <ul>
	      	<li id="buttonTemplate" class="buttonTemplate">
	        <button type="submit" value="send" class="button">
	        <div class="buttonLabel">Neue Preisgruppe</div>
	        </button>
	       </li>
	      </ul>
</div>
</form>
<br /><br />
<p><img src="../../../backend/img/default/icons4/information.png" style="margin:0 15px 0 0;" align="absmiddle" /><?php echo $sLang["presettings"]["pricegroup_Deleting_a_price_group"] ?></p>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
			// Query Related-Articles
			$sql = "
			SELECT id, description FROM s_core_pricegroups ORDER BY id ASC
			";
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
				
				// Check if this group is assigned to any customer
				$queryCustomers = mysql_query("
				SELECT id FROM s_articles WHERE pricegroupID='{$article["id"]}'
				");
				
				$delete = "";
				if (!@mysql_num_rows($queryCustomers)){
					$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteCustomerGroup({$article["id"]},'{$article["description"]}')\"></a>";			}else {
				}
		?>
        
     <tr class="rowcolor2">
       <th class="first-child"><img src="../../../backend/img/default/icons4/note05.png" style="margin:0 15px 0 0;" /><?php echo $article["description"]?></th>
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