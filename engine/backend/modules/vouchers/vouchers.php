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
if ($_GET["export"]){
	$getVoucher = mysql_query("
	SELECT description FROM s_emarketing_vouchers
	WHERE id = {$_GET["edit"]}
	");
	$description = mysql_result($getVoucher,0,"description");
	include ("csvdump.php");
	$dumpfile = new iam_csvdump();
	
	$sql = "
	SELECT code AS Code,userID AS BenutzerID, cashed AS Eingeloest FROM s_emarketing_voucher_codes
	WHERE voucherID = {$_GET["edit"]}
	ORDER BY cashed ASC, userID ASC
	";
	$dumpfile->dump($sql, "Gutschein-Codes-$description-".date("d.m.Y"), "csv", "", "", "", "" ); 
	exit;
}

if ($_GET["regenerate"]){
	// Erzeuge X neue, eindeutige Codes
	$getVoucher = mysql_query("
	SELECT numberofunits FROM s_emarketing_vouchers
	WHERE id = {$_GET["edit"]}
	");
	$numberofunits = mysql_result($getVoucher,0,"numberofunits");
	for ($i=0;$i<$numberofunits;$i++){
		$uniquePassword = false;
		while ($uniquePassword==false){
			$ticketCode = makepwd();
			
			// Fast check if code is already determinated
			$checkCode = mysql_query("
			SELECT id FROM s_emarketing_voucher_codes WHERE voucherID={$_GET["edit"]} AND
			code='$ticketCode'
			");
			
			if (!mysql_num_rows($checkCode)){
				$uniquePassword = true;
				$insertCode = mysql_query("
				INSERT INTO s_emarketing_voucher_codes (voucherID, code)
				VALUES ({$_GET["edit"]},'$ticketCode')
				");
			}
		}
	}
	// Aktualisiere Anzahl der Codes
	$getVoucher = mysql_query("
	UPDATE  s_emarketing_vouchers SET numberofunits = numberofunits + $numberofunits 
	WHERE id = {$_GET["edit"]}
	");
	

}

print_header();

if ($_POST['up_id'])
{
	$errorMessage = print_info(do_update());
	$ret = get_edit ();
	print_from($ret,$_GET["edit"],$errorMessage);
}
elseif ($_POST['add'])
{
	$errorMessage = print_info(do_save ());
	print_from(0,0,$errorMessage);
}
elseif ($_GET['delete'])
{
	$errorMessage = print_info(do_delete($_GET['delete']));
}
elseif ($_GET['edit'])
{
	$ret = get_edit ();
	print_from($ret, $_GET['edit']);
}
elseif ($_GET['add'])
{
	print_from();
}
print_table();

die($errorMessage);
	
function do_delete ($delete)
{
	$banner_loeschen = mysql_query("DELETE from s_emarketing_vouchers where id='$delete'");
	$codes_loeschen = mysql_query("DELETE from s_emarketing_voucher_codes where voucherID='$delete'");
	if($banner_loeschen == 1) { 
		return "Gutschein wurde gelöscht";	
	}
	else { 
		return "Gutschein konnte nicht gelöscht werden"; 
	}
}

function print_header()
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
<link href="js/calendar.css" rel="stylesheet" type="text/css">

<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteVoucher":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
			break;
		case "addVoucher":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?add=1";
			break;
		case "saveVoucher":
			$('formVoucher').submit();
			//parent.Growl('&Auml;nderung wurde gespeichert');
			break;
	}
}

function deleteVoucher(ev,text){
		parent.sConfirmationObj.show("Soll der Gutschein "+text+" wirklich gel&ouml;scht werden?",window,"deleteVoucher",ev);
	}

</script>
<script language="javascript1.4">
function refreshCode(element){
	//alert(element.checked);
	switch (element.checked){
		case true:
			document.getElementById("vouchercode").disabled = true;
		break;
		case false:
			document.getElementById("vouchercode").disabled = false;
		break;
	}
}
</script>
</head>
<?php


}

function makepwd()
{
   mt_srand ((double) microtime() * 1000000);
   $passwd = "";
   $chars = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
   for ($k = 0; $k < 8; $k += 1)
   {
     $num = mt_rand(0, strlen($chars)-1);
     $passwd .= $chars[$num];
   }
   return $passwd;
}

function do_save ()
{
	
	//if(!$name) {  } ???
	if (!$_POST["description"]) $fehler[] = "Gutschein-Name";
	if (!$_POST["vouchercode"] && !$_POST["modus"]) $fehler[] = "Gutschein-Code";
	if (!$_POST["numberofunits"]) $fehler[] = "Anzahl Gutscheine";
	if (!$_POST["value"]) $fehler[] = "Gutschein-Wert";
	if (($_POST["minimumcharge"]<=$_POST["value"]) && empty($_POST["kind"])) $fehler[] = "Mindestbestellwert muss größer als Gutscheinwert sein";
	if (!$_POST["ordercode"]) $fehler[] = "Bestellnummer";
	if (!$_POST["numorder"]) $_POST["numorder"] = "1";
	
	if (!count($fehler)){
		if ($_POST["modus"]){
			$mode = "1";
		}else {
			$mode = "0";
		}
		
		if ($_POST["kind"]){
			$kind = "1";
		}else {
			$kind = "0";
		}
		
		//$result = mysql_query("INSERT INTO s_emarketing_vouchers(description, vouchercode, numberofunits, value, minimumcharge, shippingfree, bindtosupplier, valid_from, valid_to, ordercode, modus) VALUES('$description', '$vouchercode', '$numberofunits', '$value', '$minimumcharge', '$shippingfree', '$bindtosupplier', '$valid_from', '$valid_to', '$ordercode', '$modus')");
		$sql = "INSERT INTO s_emarketing_vouchers(description, vouchercode, numberofunits, value, minimumcharge, shippingfree, bindtosupplier, valid_from, valid_to, ordercode, modus,percental, numorder,customergroup) VALUES('".$_POST['description']."', '".$_POST['vouchercode']."', '".$_POST['numberofunits']."', '".$_POST['value']."', '".$_POST['minimumcharge']."', '".$_POST['shippingfree']."', '".$_POST['bindtosupplier']."', '".formatdate($_POST['valid_from'])."', '".formatdate($_POST['valid_to'])."', '".$_POST['ordercode']."', '".$_POST['modus']."',$kind,{$_POST["numorder"]},'{$_POST["customergroup"]}')";
		
		$result = mysql_query($sql);
		$idGutschein = mysql_insert_id();
		
		if($idGutschein)
		{ 
			echo "<div class='accept_info'>Gutschein erfolgreich hinzugefügt! <br></div>";
			
			
			if ($mode){
				echo "<div class='showthis'><a class='ico question_shield'></a>Erzeuge ".$_POST['numberofunits']." Gutschein-Codes.<br>Um die Gutscheine als CSV-Liste zu downloaden rufen Sie den Gutschein im Bearbeiten-Modus bitte erneut auf.</div>";
				for ($i=0;$i<$_POST['numberofunits'];$i++){
					$uniquePassword = false;
					while ($uniquePassword==false){
						$ticketCode = makepwd();
						
						// Fast check if code is already determinated
						$checkCode = mysql_query("
						SELECT id FROM s_emarketing_voucher_codes WHERE voucherID=$idGutschein AND
						code='$ticketCode'
						");
						
						if (!mysql_num_rows($checkCode)){
							$uniquePassword = true;
							$insertCode = mysql_query("
							INSERT INTO s_emarketing_voucher_codes (voucherID, code)
							VALUES ($idGutschein,'$ticketCode')
							");
						}
					}
				}
			}
			
			
		} else
		{ 
			return "Beim Abspeichern ist ein Fehler aufgetreten!"; 
		}
	}
	return print_fehler ($fehler);
}

function do_update ()
{
	$sel_arr = mysql_query("SELECT * FROM s_emarketing_vouchers WHERE id='".$_GET["edit"]."'");
	$arr = mysql_fetch_array($sel_arr,MYSQL_BOTH);
	
	if (!$_POST["description"]) $fehler[] = "Gutschein-Name";
	if (!$_POST["vouchercode"] && !$arr["modus"]) $fehler[] = "Gutschein-Code";
	if (!$_POST["numberofunits"]) $fehler[] = "Anzahl Gutscheine";
	if (!$_POST["value"]) $fehler[] = "Gutschein-Wert";
	if ($_POST["minimumcharge"]<=$_POST["value"] && empty($_POST["kind"])) $fehler[] = "Mindestbestellwert muss größer als Gutscheinwert sein";
	if (!$_POST["ordercode"]) $fehler[] = "Bestellnummer";
	if (!$_POST["numorder"]) $_POST["numorder"] = "1";
		
	if ($_POST["kind"]){
		$kind = "1";
	}else {
		$kind = "0";
	}
		
	

	
	if (!count($fehler)){


		$sql = "UPDATE s_emarketing_vouchers SET description='".$_POST['description']."', vouchercode='".$_POST['vouchercode']."', numberofunits='".$_POST['numberofunits']."', value='".$_POST['value']."', minimumcharge='".$_POST['minimumcharge']."', shippingfree='".$_POST['shippingfree']."', bindtosupplier='".$_POST['bindtosupplier']."', valid_from='".formatdate($_POST['valid_from'])."', valid_to='".formatdate($_POST['valid_to'])."', ordercode='".$_POST['ordercode']."', percental='$kind', numorder='{$_POST["numorder"]}', customergroup='{$_POST["customergroup"]}' WHERE id='".$_POST['up_id']."'";
		
		$updt = mysql_query($sql);
	}
	if($updt == 1)
	{ 
		return "Gutschein erfolgreich geändert!"; }
	else
	{ 
		return print_fehler ($fehler);
		return "Beim Speichern ist ein Fehler aufgetreten!"; }
}

function formatdate ($date)
{
	if (!$date) return "";
	//02.12.2005 -> 2005-12-02
	$date = explode(".",$date);
	return $date[2]."-".$date[1]."-".$date[0];
}

function formatdate_ret ($date)
{
	if (!$date) return "";
	//02.12.2005 <- 2005-12-02
	$date = split("-",$date);
	return $date[2].".".$date[1].".".$date[0];
}

function get_edit ()
{
	$sel_arr = mysql_query("SELECT * FROM s_emarketing_vouchers WHERE id='".$_GET["edit"]."'");

	$arr = mysql_fetch_array($sel_arr,MYSQL_BOTH);
	$_POST["description"] = $arr["description"];
	$_POST["vouchercode"] = $arr["vouchercode"];
	$_POST["numberofunits"] = $arr["numberofunits"];
	$_POST["kind"] = $arr["percental"];
	$_POST["minimumcharge"] = $arr["minimumcharge"];
	$_POST["numorder"] = $arr["numorder"];
	$_POST["shippingfree"] = $arr["shippingfree"];
	$_POST["bindtosupplier"] = $arr["bindtosupplier"];
	$_POST["valid_from"] = $arr["valid_from"];
	$_POST["valid_to"] = $arr["valid_to"];
	$_POST["ordercode"] = $arr["ordercode"];
	$_POST["value"] = $arr["value"];
	$_POST["customergroup"] = $arr["customergroup"];
	//$_POST["percental"] = $arr["percental"];
	 
	return $arr;
} // if($erben)

function print_fehler ($fehler = "")
{
	if (count($fehler)){
		$errorMsg = "<strong>Bitte füllen Sie folgende Felder aus</strong><ul>";
		foreach ($fehler as $fehlerRow){
			$errorMsg.= "<li style=\"float:none\">$fehlerRow</li>";
		}
		$errorMsg.= "</ul>";
	}
	return $errorMsg;
}

function print_from ($arr = "", $erben = false,$errorMsg)
{
?>

<body>

		
<fieldset class="col2_cat2" style="margin-top:-20px;">
<legend><a class="ico help"></a>Hinweise für Gutschein-Modus</legend>
<strong>Modus - Allgemein gültig</strong><br />
Es wird ein einheitlicher Gutschein-Code bereitgestellt<br />
<strong>Modus - Individuelle Gutscheincodes</strong><br />
Es werden soviele individuelle Gutschein-Codes erzeugt, wie Sie
unter "Stückzahl" angeben. Jeder Kunde erhält also seinen eigenen,
individuellen Code. Sie können eine Liste aller hinterlegten Codes
im CSV-Format abrufen<br />
</fieldset>
<?php
if (!empty($errorMsg) && preg_match("/Felder/",$errorMsg)){
	?>
	<div class="error_info">
	Folgende Fehler sind aufgetreten:<br/>
	<?php
	echo "$errorMsg";
	?>
	</div>
	<?php
}
?>
<div class="clear"></div>
<?php
if ($arr["modus"]){
	// Informationen über den Gutschein abrufen
	// Anzahl Codes
	$getUsedCodes = mysql_query("
	SELECT id FROM s_emarketing_voucher_codes WHERE userID=0 AND cashed=0 AND voucherID = {$_GET["edit"]}
	");
	$freeCodes = mysql_num_rows($getUsedCodes);
	if (!$freeCodes){
		$noFree = true;	
	}
	if (!$freeCodes) $freeCodes = "0";
		?>
			<p style="font-weight:bold;border: 1px dotted #F00;height:40x;width:100%;padding:5px;background-color:#FFEEAA;position:relative;margin-bottom:30px">
			<strong>Dieser Gutschein wurde mit individuellen Codes angelegt. (<?php echo $freeCodes ?> von <?php echo $arr["numberofunits"] ?> frei)</strong><br />
			<span style="color:#F00">
			<?php if ($noFree){ ?><br />
			Keine freien Codes mehr verfügbar!!!<br />
			Klicken Sie <a href="<?php echo $_SERVER["PHP_SELF"]."?edit={$_GET["edit"]}&regenerate=1"?>">hier</a> um weitere <?php echo $arr["numberofunits"]?> Codes anzulegen<br />
			<br />
			<?php } 
			?>
			</span>
			<a class="ico3 disk" style="cursor:pointer" href="<?php echo $_SERVER["PHP_SELF"]."?edit={$_GET["edit"]}&export=1"?>">Excel-Liste mit erzeugten Codes</a>
			</p>
		<?php
	}
?>
<form id="formVoucher" name="form1" method="POST" action="<?php $_SERVER["PHP_SELF"] ?>">
<fieldset style="margin-bottom:0px">
	<legend>Gutscheine</legend>
	
	<ul>
	<li><label for="name">Bezeichnung:</label>  
  	<input type="text" name="description" value="<?php echo $_POST["description"] ?>" class="w200" /></li>
	<li class="clear" />
<?php
if(!$erben) 
{ 
?>
	<!-- Auswahl Modus -->
	<li><label for="modus">Allgemein gültig:</label>
    <input type="radio" name="modus" id="modus" value="0" checked onclick="$('vouchercodeli').setStyle('display','block');$('voucherPerClient').setStyle('display','block');">
    <label for="modus1">Individuelle Gutscheincodes:</label>
    <input type="radio" name="modus" id="modus1" value="1" onclick="$('vouchercodeli').setStyle('display','none');$('voucherPerClient').setStyle('display','none');">
	</li>
    <li class="clear" />
<?php
} // Nachträgliches bearbeiten des Modus verhindern
else {
	
}
?>
    <?php
	if (!$arr["modus"]){
    ?>
	<li id="vouchercodeli"><label for="vouchercode">Code:</label>
 	<input type="text" id="vouchercode" name="vouchercode" value="<?php echo $_POST["vouchercode"]; ?>" class="w200" /></li>
	<?php
	}
	?>
	
	
	<li><label for="numberofunits">St&uuml;ckzahl:</label>
  	<input type="text" name="numberofunits"  value="<?php echo $_POST["numberofunits"]; ?>" maxlength="7" size="7" class="w50" /></li>
	<li class="clear" />
	<li><label for="modus">Absoluter Abzug:</label>
    <input type="radio" name="kind" id="modus" value="0" <?php if (!$_POST["kind"] || !count($arr)){ echo "checked"; } ?> >
    <label for="modus1">Prozentualer Abzug:</label>
    <input type="radio" name="kind" id="modus1" value="1"  <?php if ($_POST["kind"]){ echo "checked"; } ?>>
	</li>
    <li class="clear" />
	<li><label for="value">Wert:</label>
  	<input type="text" name="value"  value="<?php echo $_POST["value"]; ?>" size="4" maxlength="4" class="w50" /></li>
	<li class="clear" />
	
	<li><label for="minimumcharge">Mindestumsatz:</label>
  	<input type="text" name="minimumcharge"  value="<?php echo $_POST["minimumcharge"]; ?>" maxlength="4" size="4" class="w50" /></li>
	<li class="clear" />
	<?php
	if (empty($arr["modus"])){
    ?>
	<li id="voucherPerClient"><label for="numorder">Einlösbar je Kunde:</label>
  	<input type="text" name="numorder"  value="<?php echo $_POST["numorder"] ? $_POST["numorder"] : "1"; ?>" maxlength="4" size="4" class="w50" /></li>
	<li class="clear" />
	<?php
	}
	?>
	<li><label for="numorder">Beschränkt auf Kundengruppe:</label>
	<?php
		// Query available customergroups
		$getCustomerGroups = mysql_query("
		SELECT id, groupkey, description FROM s_core_customergroups ORDER BY id ASC
		");
		echo "<select name=\"customergroup\">
		<option value=\"0\">Keine Beschränkung</option>
		";
		while ($group = mysql_fetch_assoc($getCustomerGroups)){
			$selected = $group["groupkey"] == $_POST["customergroup"] ? "selected" : "";
			echo "<option value=\"{$group["groupkey"]}\" $selected>{$group["description"]} ({$group["groupkey"]})</option>";
		}
		echo "</select>";
	?></li>
	<li class="clear" />
	
	<li><label for="shippingfree">Versandkostenfrei:</label>
  	<input type="checkbox" name="shippingfree" value="1"<?php if($_POST["shippingfree"]==1) { echo " checked"; } ?>></li> 
	
  	<li><label for="bindtusupplier">Beschr&auml;nkt auf Hersteller:</label>
          <select name="bindtosupplier" size="1">
            <option value=''>kein Hersteller</option>
			 <?php

			$result = @mysql_query("SELECT id,name FROM s_articles_supplier ORDER BY name ASC");
		  	$nums = @mysql_num_rows($result);
			for ($i=0;$i<$nums;$i++){
				$herid = @mysql_result($result,$i,"id");
				$name = @mysql_result($result,$i,"name");
				if ($herid != $_POST["bindtosupplier"])
				echo "<option value='$herid'>$name</option>";
				else
				echo "<option selected='selected' value='$herid'>$name</option>";
			}
		  ?>
          </select></li>
		  <li class="clear" />
   
   <li><label for="valid_from">G&uuml;ltig von:</label>     
   <?php echo "<input id=\"valid_from\" name=\"valid_from\" value=\"".formatdate_ret($_POST["valid_from"])."\" onclick=\"displayDatePicker('valid_from', false, 'dmy', '.');\"><a class=\"ico calendar\"  onclick=\"displayDatePicker('valid_from', false, 'dmy', '.');\"></a>"; ?></li>
   <li class="clear" />
   
   <li><label for="valid_to">G&uuml;ltig bis:</label>
   <?php echo "<input id=\"valid_to\" name=\"valid_to\" value=\"".formatdate_ret($_POST["valid_to"])."\" onclick=\"displayDatePicker('valid_to', false, 'dmy', '.');\"><a class=\"ico calendar\"  onclick=\"displayDatePicker('valid_to', false, 'dmy', '.');\"></a>"; ?></li>
   <li class="clear" />
   
   <li><label for="ordercode">Bestell-Nr WaWi:</label>
   <input type="text" name="ordercode" value="<?php echo $_POST["ordercode"] ?>" maxlength="10" size="10" class="w100" /></li>
   
   </ul>
   <?php 
	if($erben) 
	{ 
		?>
		<div class="clear"></div>
	  <!--<input type="submit" name="update" value="speichern">
	  <input type="submit" name="del" value="l-schen">-->
	  <input type="hidden" name="up_id" value="<?php echo $erben; ?>">
		<div class="buttons" id="div">
	      <ul>
	      	<li id="buttonTemplate" class="buttonTemplate">
	        <button type="submit" value="send" class="button">
	        <div class="buttonLabel">Speichern</div>
	        </button>
	       </li>
	      </ul>
	    </div>	<div class="clear"></div>
	  <?php
	} else {
		?>	<div class="clear"></div>
	  <!--<input type="submit" name="save" value="speichern"> -->
	  <input type="hidden" name="add" value="true">
		<div class="buttons" id="div">
	      <ul>
	      	<li id="buttonTemplate" class="buttonTemplate">
	        <button type="submit" value="send" class="button">
	        <div class="buttonLabel">Speichern</div>
	        </button>
	       </li>
	      </ul>
	    </div>	<div class="clear"></div>
	  <?php
	} ?>
	<br />

</fieldset>


  <br />
</form>
<?php
}

function print_table ()
{ 
	  $voucher = mysql_query("
	  SELECT * FROM s_emarketing_vouchers ORDER BY valid_from ASC
	  ") or die(mysql_error());
	  
 ?>
	<div class="buttons" id="buttons" style="margin-left:5px;">
		<ul>

		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $_SERVER["PHP_SELF"] ?>?add=1" class="bt_icon email_open_image" value="send" style="text-decoration:none;">Neuer Gutschein</a></li>	
		
		</ul>
		</div><br/><br/>
<script type='text/javascript'>

var headers = [
{
"text":"Bezeichnung",
"key":"name","sortable":true,
"fixedWidth":true,"defaultWidth":"120px"},
{
"text":"Code",
"key":"code","sortable":true,
"fixedWidth":true,"defaultWidth":"100px"},
{
"text":"Eingelöst",
"key":"used","sortable":true,
"fixedWidth":true,"defaultWidth":"80px"},
{
"text":"Wert",
"key":"price","sortable":true,
"fixedWidth":true,"defaultWidth":"80px"},
{
"text":"von",
"key":"from","sortable":true,
"fixedWidth":true,"defaultWidth":"70px"},
{
"text":"bis",
"key":"to","sortable":true,
"fixedWidth":true,"defaultWidth":"70px"},
{
"text":"Optionen",
"key":"options","sortable":true,
"fixedWidth":true,"defaultWidth":"80px"}
];

<?php
  $voucher = mysql_query("
	  SELECT * FROM s_emarketing_vouchers ORDER BY valid_from ASC
	  ") or die(mysql_error());
$numbervoucherData = @mysql_num_rows($voucher);
if ($numbervoucherData){
// =================================
	echo "var data = [";	// Header
// =================================
// Ausgabe Gutscheine
// =================================
while ($voucherData=mysql_fetch_array($voucher))
{	
	$voucherData["valid_from"] = explode(" ",$voucherData["valid_from"]);
	$voucherData["valid_from"] = explode("-",$voucherData["valid_from"][0]);
	$voucherData["valid_from"] = $voucherData["valid_from"][2].".".$voucherData["valid_from"][1].".".$voucherData["valid_from"][0];
	
	$voucherData["valid_to"] = explode(" ",$voucherData["valid_to"]);
	$voucherData["valid_to"] = explode("-",$voucherData["valid_to"][0]);
	$voucherData["valid_to"] = $voucherData["valid_to"][2].".".$voucherData["valid_to"][1].".".$voucherData["valid_to"][0];
	
	if (!$voucherData["modus"]){
		$queryAlreadyChecked = mysql_query("SELECT * FROM s_order_details WHERE articleordernumber ='{$voucherData["ordercode"]}' AND ordernumber!=0");
		$voucherData["checkedIn"] = mysql_num_rows($queryAlreadyChecked);
	}else {
		$sql = "SELECT * FROM s_emarketing_voucher_codes WHERE voucherID ='{$voucherData["id"]}' AND (cashed=1 OR userID!=0)";
		$queryAlreadyChecked = mysql_query($sql);
		$voucherData["checkedIn"] = mysql_num_rows($queryAlreadyChecked);
	}
	
	if ($voucherData["percental"]){
		$char = "% ";
	}else {
		$char = "&euro; ";	
	}
	$i++;
	$comma = $i==$numbervoucherData ? "" : ",";
	?>

{"id":0,
"name":"<?php echo $voucherData["description"] ?>",
"code":"<?php echo $voucherData["modus"] != 2 ? $voucherData["vouchercode"] : "CSV generated"; ?>" ,
"used":"<?php echo $voucherData["checkedIn"]."/".$voucherData["numberofunits"]; ?> <?php  ?>",
"price":"<?php echo $char ?> <?php echo $voucherData["value"]; ?>",
"from":"<?php echo $voucherData["valid_from"]; ?>",
"to":"<?php echo $voucherData["valid_to"]; ?>",
"options":"<a class=\"ico delete\" style=\"cursor:pointer\" onclick=\"deleteVoucher(<?php echo $voucherData["id"] ?>,'<?php echo $voucherData["description"] ?>')\"></a><a class=\"ico pencil\" style=\"cursor:pointer\" onclick=\"window.location='?edit=<?php echo $voucherData["id"]?>'\"></a>"}
<?php echo $comma ?>

	<?php
// =================================
} // for voucher
// =================================
	echo "];";				// Footer
// =================================
} // Vouchers found
// =================================
?>



window.addEvent('load',function(){	
	mootable = new MooTable( 'test', {debug: false, height: '270px', headers: headers, sortable: true, useloading: false, resizable: false});
	mootable.loadData( data );
});

</script>
<style>
.th {
	display:none
}
</style>
<fieldset class="white" style="padding:0;margin-top:0px;">
<legend>Angelegte Gutscheine</legend>
<div id='test' style="padding-top:1px;"></div>
</fieldset>
</body>

<?php
}
/*
print info
*/
function print_info ($sInform = "",$sError = "")
{

?>
<script type='text/javascript'>
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
<?php
return $sInform;
}
/*
ende // print info 
*/

?>
</html>
