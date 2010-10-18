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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<fieldset class="col2_cat2" style="margin-top:-15px;">
		<legend><a href="#" class="ico folder_add"></a> <?php echo $sLang["articles"]["categoryrelations_Category"] ?></legend>
<script>
function refreshList(){
	var id;
	id = document.getElementById('categoryDataId').innerHTML;
	if (id){
	window.location='categoryrelations.php?article=<?php echo $_GET["article"]?>&addCategory='+id;
	}else {
		parent.parent.parent.Growl('<?php echo $sLang["articles"]["categoryrelations_no_Category_selected"] ?>');
	}
}
</script>


<?php echo $sLang["articles"]["categoryrelations_please_select_category"] ?> 	  	<?php
				  	$abfrage = mysql_query("
					SELECT name FROM s_articles WHERE id={$_GET["article"]}
					");
					if (@mysql_num_rows($abfrage)){
						echo "<strong>".strip_tags(mysql_result($abfrage,0,"name"))."</strong>";
					}else {
						die($sLang["articles"]["categoryrelations_article_not_found"]);
					}
		?> <?php echo $sLang["articles"]["categoryrelations_to_be_set"] ?><br />

<?php
$checkIfAlreadyAssigned = mysql_query("
SELECT id FROM s_articles_categories WHERE articleID = {$_GET["article"]} AND categoryparentID = {$_GET["catid"]}
");
if ($_GET["catid"] && !@mysql_num_rows($checkIfAlreadyAssigned)){
?>
<?php echo $sLang["articles"]["categoryrelations_selected_category"] ?>&nbsp; <strong><?php echo $_GET["text"]?></strong>
<span id="categoryData"></span>
	<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="refreshList();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["articles"]["categoryrelations_Assigning_to_category"] ?></div></button>
			</li>	
		</ul>
	</div>
<span id="categoryDataId" style="display:none"><?php echo $_GET["catid"]?></span>
<!-- Grafik -->
<?php
}elseif (@mysql_num_rows($checkIfAlreadyAssigned)){
	?>
	<p style="font-weight:bold">Diese Kategorie wurde bereits verknüpft</p>
	<?php
}
?>
<div class="clear"></div>
</fieldset>
<?php
unset($jsBumb);
if ($_GET["delete"])
{
	$categoryID = intval($_GET["delete"]);
	$articleID = intval($_GET["article"]);
	while ($categoryID)
	{
		$sCore->sDeletePartialCache("category",$categoryID);
		$sql = "
			SELECT 1
			FROM s_categories c, s_articles_categories ac
			WHERE c.parent=$categoryID
			AND ac.categoryID=c.id
			AND ac.articleID=$articleID
			LIMIT 1
		";
		$result = mysql_query($sql);
		if(!$result)
			break;
		if(mysql_num_rows($result))
			break;
		$sql = "
			DELETE FROM s_articles_categories
			WHERE categoryID=$categoryID
			AND articleID=$articleID
		";
		$result = mysql_query($sql);
		if(!$result)
			break;
		$sql = "
			SELECT parent
			FROM s_categories
			WHERE parent!=1
			AND parent!=id
			AND id=$categoryID
		";
		$result = mysql_query($sql);
		if(!$result||!mysql_num_rows($result))
			break;
		$categoryID = (int) mysql_result($result,0,0);
		
	}
	if ($result){
		$jsBumb = $sLang["articles"]["categoryrelations_Assigning_to_category_delete"];
	}else {
		$jsBumb = $sLang["articles"]["categoryrelations_Assigning_to_category_cant_delete"];
	}
}
if ($_GET["addCategory"]){
	$_GET["addCategory"] = intval($_GET["addCategory"]);
	$source = $_GET["addCategory"];
	do
	{
		$sql = "
			REPLACE INTO s_articles_categories
			(articleID,categoryID,categoryparentID)
			VALUES
			({$_GET["article"]},{$_GET["addCategory"]},$source)
		";
		$insertCategory = mysql_query($sql);
		$sCore->sDeletePartialCache("category",$_GET["addCategory"]);
		$getCategoriesParent = mysql_query("SELECT parent FROM s_categories WHERE id={$_GET["addCategory"]}");
		if (@mysql_num_rows($getCategoriesParent)){
			$parent = mysql_result($getCategoriesParent,0,"parent");
			
			$_GET["addCategory"] = $parent;
		}else {
			$parent=1;
		}
	} while ($parent!=1 || !$parent);

	$jsBumb = $sLang["articles"]["categoryrelations_Category_assignment_has_been_added"];
}


				  	
// Anzeige der bereits zugeordneten Kategorien
$abfrage = mysql_query("
SELECT * FROM s_articles_categories WHERE articleID={$_GET["article"]} AND categoryID=categoryparentID GROUP BY categoryparentID
");
					
if (@mysql_num_rows($abfrage)){
	
	$showCategories = 1;
}


				
if ($showCategories==1){

					$bgcolor="bgcolor='#F5F5F5'";
					while ($kategorie=mysql_fetch_array($abfrage)){
							
							$id = $kategorie["categoryparentID"];
							$tmpid = $id; 
							
							// Verlauf abfragen
							
							unset($verlauf);
							do
							{
								$sql = "
								SELECT description, parent FROM s_categories WHERE id=$tmpid
								";
								
								$abfrage2 = mysql_query($sql);
								
								
								$arg = mysql_fetch_assoc($abfrage2);
								
								$text = $arg["description"];
								
								$verlauf[] = $text;
								
								if (mysql_num_rows($abfrage2)){
									$parent = mysql_result($abfrage2,0,"parent");
									$tmpid =  $parent;
								}else {
									echo $sLang["articles"]["categoryrelations_nothing_found"]."\n";
									$parent=1;	
								}
							}	while ($parent!=1 OR !$parent);
							
							
							
							
							$verlauf = array_reverse($verlauf);
							$tempCategory = array("text"=>implode(" / ",$verlauf),"parent"=>$id);
							$categories[] = $tempCategory;
							//print_r($categories);
							
							//echo $verlauf."\n\n";
						
							
	
					} // For each category
	
				  } // If categories found

if (count($categories)){

$numberCategories = count($categories);
?>

<fieldset class="col2_cat2">
<legend>Bereits zugeordnete Kategorien</legend>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   <?php
   foreach ($categories as $category){
   ?>
     <tr class="rowcolor2">
       <th><a class="ico folder"></a><?php echo $category["text"] ?></th>
       <td class="last-child">
		   <a href="javascript::" class="ico delete" style="cursor:pointer" onclick="deleteCategory(<?php echo $category["parent"] ?>,'<?php echo preg_replace("/[^A-Za-z äöüÄÜÖ]/", "", $category["text"]);?>');return false;"></a>
	  </td>
     </tr>
     <?php
   }
     ?>
     
 </table>
</fieldset>
<?php
}
?>



<script>
	function deleteCategory(ev,text){
		try {
			parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["categoryrelations_assignment"] ?> "'+text+'" <?php echo $sLang["articles"]["categoryrelations_assignment_delete"] ?>',window,'deleteRelationship',ev);
		}catch (e){
			parent.parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["categoryrelations_assignment"] ?> "'+text+'" <?php echo $sLang["articles"]["categoryrelations_assignment_delete"] ?>',window,'deleteRelationship',ev);
		}
	}
	function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteRelationship":
			location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId+"&article=<?php echo $_GET["article"]?>";
			break;
	}
}
</script>

<!-- // Tabelle -->
</body>
</html>