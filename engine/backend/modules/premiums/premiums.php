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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />


<title><?php echo $sLang["premius"]["premiums_premiums"] ?></title>
<?php

if( $_GET["delete"] )
{
 	mysql_query("DELETE FROM s_addon_premiums WHERE id={$_GET["delete"]}");
	echo mysql_error();
	$sInform = $sLang["premius"]["premiums_premium_deleted"];
}


if($_POST["sSave"])
{
     if(!$_POST["minAmount"] )
	  {
	  		$error.=$sLang["premius"]["premiums_Please_specify_a_minimum_order_value"]."<br />";
	  }else {
	  	$_POST["minAmount"] = str_replace(",",".",$_POST["minAmount"]);
	  }
	 if(!$_POST["orderNumberIntern"] )
	  {
	  		$error.=$sLang["premius"]["premiums_Please_specify_a_article_number"]."<br />";
	  }
	 if(!$_POST["orderNumberShop"] )
	  {
	  		$error.=$sLang["premius"]["premiums_Please_specify_a_article_number_Shop"]."<br />";
	  }
	  
	  
	  if( empty($error) && !$_GET["edit"] ) 
	  {
	  	$sql = "INSERT INTO s_addon_premiums (startprice,ordernumber,articleID,subshopID) VALUES ('{$_POST["minAmount"]}','{$_POST["orderNumberIntern"]}','{$_POST["orderNumberShop"]}','{$_POST["subshopID"]}') ";
	 		mysql_query($sql);
			echo mysql_error();
			$sInform = $sLang["premius"]["premiums_premium_added"];
	  }
	  
	  if(empty($error) && $_GET["edit"])
	  {
	  		mysql_query("UPDATE s_addon_premiums SET startprice={$_POST["minAmount"]},ordernumber='{$_POST["orderNumberIntern"]}',articleID='{$_POST["orderNumberShop"]}',subshopID = '{$_POST["subshopID"]}'  WHERE id={$_GET["edit"]}");
			echo mysql_error();
			
			$sInform = $sLang["premius"]["premiums_change_saved"];
	  }
}

if ($_GET["edit"]){	
	$sql_datensatz="SELECT * FROM s_addon_premiums WHERE id=".$_GET["edit"];
	$getPremium =mysql_query($sql_datensatz);
	$getPremium = mysql_fetch_array($getPremium);
	$_POST["minAmount"]	= $getPremium["startprice"];
	$_POST["orderNumberIntern"]	= $getPremium["ordernumber"];
	$_POST["orderNumberShop"]	= $getPremium["articleID"];
	$_POST["subshopID"] = $getPremium["subshopID"];
}
?>
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deletePremium":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
			break;
	}
}

function deletePremium(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["premius"]["premiums_should_the_premium"] ?> "'+text+'" <?php echo $sLang["premius"]["premiums_really_be_deleted"] ?>',window,'deletePremium',ev);
	}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.sWindows.focus.shake(50);";
		}
	?>
	
};
</script>
</head>

<body>

<?php



?>

<?php

  if($error!="")
	  {
	  		echo"<div class=\"error\" id=\"error_box\">$error</div><br />";
	  }  
?>


  <form name="formular" id="premiums" method="POST" action="<?php echo $_SERVER["PHP_SELF"]."?edit=".$_GET["edit"]?>">
  <input type="hidden" name="sSave" value="1">

    <fieldset>
	<legend><?php echo $sLang["premius"]["premiums_premiumarticle"] ?></legend>
	<ul>
	
	<li><label for="ordernumber"><?php echo $sLang["premius"]["premiums_Minimum_Turnover"] ?></label></li>
	<li class="w200">
	
	<input name="minAmount" type="text" size="8" value="<?php echo $_POST["minAmount"] ?>" class="w200" /></li>
	<li class="clear" />
	
	<li><label for="ordernumber"><?php echo $sLang["premius"]["premiums_articlenumber"] ?></label></li>
	<li class="w200">
	
	<input name="orderNumberIntern" type="text" size="8" value="<?php echo $_POST["orderNumberIntern"] ?>" class="w200" /></li>
	<li class="clear" />
	
	<li><label for="ordernumber2"><?php echo $sLang["premius"]["premiums_articlenumber_shop"] ?></label></li>
	<li class="w200">
	<input name="orderNumberShop" type="text" size="8"  value="<?php echo $_POST["orderNumberShop"] ?>" class="w200" /></li>
	<li class="clear"></li>
	<li><label for="ordernumber2">Gültig für Subshop:</label></li>
	<li class="w200">
	<select name="subshopID" class="w200" />
	<option value="0">Bitte wählen</option>
	<?php
		$getShops = mysql_query("
		SELECT id,name FROM s_core_multilanguage ORDER BY id ASC
		");
		while ($shop = mysql_fetch_assoc($getShops)){
		$selected = $_POST["subshopID"] == $shop["id"] ? "selected" : "";
		?>
		<option value="<?php echo $shop["id"] ?>" <?php echo $selected?>><?php echo $shop["name"]?></option>
		<?php
		}
	?>
	</select>
	</li>
	<li class="clear"></li>
	
		<div class="buttons" id="buttons">
			<ul>
			<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
			<div class="button">
			<a onclick="$('premiums').submit();"><div class="buttonLabel"><?php echo $sLang["premius"]["premiums_save_premium"] ?></div></a>
			</div>
			</li>
			<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
			<div class="button">
			<a href="<?php echo $_SERVER["PHP_SELF"] ?>"><div class="buttonLabel"><?php echo $sLang["premius"]["premiums_new_premium"] ?></div></a>
			</div>
			</li>	
			</ul>
		</div>	
	
	</ul>
	
	
</fieldset>
	

   </form>



<?php
 $ergebnis_datensatz = mysql_query("
 SELECT * FROM s_addon_premiums ORDER BY startprice DESC
 ");
 ?>

<script type='text/javascript'>

var headers = [
{
"text":"<?php echo $sLang["premius"]["premiums_Order_value"] ?>",
"key":"price","sortable":true,
"fixedWidth":true,"defaultWidth":"150px"},
{
"text":"<?php echo $sLang["premius"]["premiums_articlenumber"] ?>",
"key":"ordernumber","sortable":true,
"fixedWidth":true,"defaultWidth":"200px"},
{
"text":"<?php echo $sLang["premius"]["premiums_pseudo_articlenumber_shop"] ?>",
"key":"articleID","sortable":true,
"fixedWidth":true,"defaultWidth":"200px"},
{
"text":"<?php echo $sLang["premius"]["premiums_options"] ?>",
"key":"options","sortable":true,
"fixedWidth":true,"defaultWidth":"80px"}
];

<?php
$numberPremiums = @mysql_num_rows($ergebnis_datensatz);
if ($numberPremiums){
// =================================
	echo "var data = [";	// Header
// =================================
// Ausgabe Banner
// =================================
while($row_datensatz=mysql_fetch_array($ergebnis_datensatz))
{
	$i++;
	$comma = $i==$numberPremiums ? "" : ",";
	?> 

 
{
"price":"&euro; <?php echo $row_datensatz["startprice"]; ?>", 

"ordernumber":"<?php echo $row_datensatz["ordernumber"]; ?>",

"articleID":"<?php echo $row_datensatz["articleID"]; ?>",

"options":"<a class=\"ico delete\" style=\"cursor:pointer\" onclick=\"deletePremium(<?php echo $row_datensatz["id"] ?>,'<?php echo $row_datensatz["startprice"] ?>')\"></a><a class=\"ico pencil\" style=\"cursor:pointer\" onclick=\"window.location='?edit=<?php echo $row_datensatz["id"]?>'\"></a>"}
<?php echo $comma ?>


<?php
// =================================
} // for every premium
// =================================
	echo "];";				// Footer
// =================================
} // Premiums found
// =================================
?>



window.addEvent('load',function(){

	
				function exampleClick(ev){
					//parent.parent.Growl( 'You picked row ' + (this.data.id) );
					parent.parent.loadSkeleton('articles',false, "{article:"+this.data.id+"}");
				}
				mootable = new MooTable( 'test', {debug: false, height: '270px', headers: headers, sortable: true, useloading: false, resizable: false});
				mootable.addEvent( 'afterRow', function(data, row){
					
					//debug.log( row );
					//row.cols[0].element.innerHTML = ( data.ID + 1);

				});
				mootable.loadData( data );
				});
		</script>
		

  

<body>
<fieldset class="white" style="padding:0;margin-top:-25px;">
<legend>Angelegte Prämien</legend>
<div id='test' style="padding-top:1px;"></div>
</fieldset>
</body>
</html>

