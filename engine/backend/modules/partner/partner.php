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
	
function makeProperDate($date){
	$date = explode(".",$date);
	return $date[2]."-".$date[1]."-".$date[0];
}

if ($_GET["delete"]){
	$deletePartner = mysql_query("
	DELETE FROM s_emarketing_partner WHERE id = {$_GET["delete"]}
	");
	$sInform = $sLang["partner"]["partner_partner_was_deleted"];
}
if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
	$fieldDepencies = array("idcode","company","percent");
	
	$_POST["idcode"] = preg_replace('#[^a-zA-Z0-9_\-.]#','',$_POST["idcode"]);
	$_POST["company"] = mysql_real_escape_string($_POST["company"]);
	$_POST["contact"] = mysql_real_escape_string($_POST["contact"]);
	$_POST["street"] = mysql_real_escape_string($_POST["street"]);
	$_POST["streetnumber"] = mysql_real_escape_string($_POST["streetnumber"]);
	$_POST["zipcode"] = mysql_real_escape_string($_POST["zipcode"]);
	$_POST["city"] = mysql_real_escape_string($_POST["city"]);
	$_POST["phone"] = mysql_real_escape_string($_POST["phone"]);
	$_POST["fax"] = mysql_real_escape_string($_POST["fax"]);
	$_POST["country"] = mysql_real_escape_string($_POST["country"]);
	$_POST["email"] = mysql_real_escape_string($_POST["email"]);
	$_POST["web"] = mysql_real_escape_string($_POST["web"]);
	$_POST["profil"] = mysql_real_escape_string($_POST["profil"]);
	$_POST["percent"] = intval($_POST["percent"]);
	$_POST["cookielifetime"] = intval($_POST["cookielifetime"]);
	$_POST["active"] = empty($_POST["active"]) ? 0 : 1;
	
	if (!$_POST["idcode"] || !$_POST["company"] || !$_POST["percent"]) $sError = $sLang["partner"]["partner_please_fill_in_all_fields_marked_in_bold"];
	
	
	
	$_POST["datum"] = makeProperDate($_POST["datum"]);
	
	
	//print_r($_POST);
	$getPartnerWithSameCode = mysql_query("
	SELECT id FROM s_emarketing_partner
	WHERE
	idcode = '{$_POST["idcode"]}'
	");
	if (@mysql_num_rows($getPartnerWithSameCode) && !$_GET["edit"]) $sError = $sLang["partner"]["partner_the_tracking_code"]." {$_POST["idcode"]} ".$sLang["partner"]["partner_is_already_taken"];
	if (!$sError){
		

		
		if ($_GET["edit"]){
			$insertArticle = mysql_query("
			UPDATE s_emarketing_partner SET
			idcode = '{$_POST["idcode"]}',
			company = '{$_POST["company"]}',
			contact = '{$_POST["contact"]}',
			street = '{$_POST["street"]}',
			streetnumber = '{$_POST["streetnumber"]}',
			zipcode = '{$_POST["zipcode"]}',
			city = '{$_POST["city"]}',
			phone = '{$_POST["phone"]}',
			fax = '{$_POST["fax"]}',
			country = '{$_POST["country"]}',
			email = '{$_POST["email"]}',
			web = '{$_POST["web"]}',
			profil = '{$_POST["profil"]}',
			percent = '{$_POST["percent"]}',
			cookielifetime = '{$_POST["cookielifetime"]}',
			active = '{$_POST["active"]}'
			WHERE id={$_GET["edit"]}
			");
		}else {
			$datum = date("Y-m-d");
			$sql = "
			INSERT INTO s_emarketing_partner
			(idcode, datum, company, contact, street, streetnumber, zipcode, city,
			phone, fax, country, email, web, profil, percent, cookielifetime, active)
			VALUES (
			'{$_POST["idcode"]}',
			'$datum',
			'{$_POST["company"]}',
			'{$_POST["contact"]}',
			'{$_POST["street"]}',
			'{$_POST["streetnumber"]}',
			'{$_POST["zipcode"]}',
			'{$_POST["city"]}',
			'{$_POST["phone"]}',
			'{$_POST["fax"]}',
			'{$_POST["country"]}',
			'{$_POST["email"]}',
			'{$_POST["web"]}',
			'{$_POST["profil"]}',
			'{$_POST["percent"]}',
			'{$_POST["cookielifetime"]}',
			'{$_POST["active"]}'
			)
			";
			//echo $sql;
			$insertArticle = mysql_query($sql);
		}
		
		if ($insertArticle){
			$sInform = $sLang["partner"]["partner_entry_saved"];
		}
	}
}

if ($_GET["edit"] || $_GET["showReport"]){
	if ($_GET["showReport"]) $_GET["edit"] = $_GET["showReport"];
	$partner = mysql_query("
	SELECT * FROM s_emarketing_partner WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($partner)){
		$sInform = $sLang["partner"]["partner_partner_not_found"];
	}else {
		$partner = mysql_fetch_array($partner);
	}
	if ($_GET["showReport"]) unset($_GET["edit"]);
}

if ($_POST["sExportPartner"] && $_POST["sExportData"]){
	$data = explode(".",$_POST["sExportData"]);
	
	$sql = "SELECT ordertime AS `Datum`,
		REPLACE(SUM((s_order.invoice_amount_net-s_order.invoice_shipping_net)/currencyFactor),'.',',') AS `Netto Gesamt`,
		REPLACE(SUM(((s_order.invoice_amount_net-s_order.invoice_shipping_net)/currencyFactor)/100*{$partner["percent"]}),'.',',') AS `Provision`,
		email, company AS `Firma` , firstname AS `Vorname`, lastname AS `Nachname`, 
		customernumber AS `Kundennummer`,ordernumber AS `Bestellnummer`, s_core_states.description AS `Bestellstatus`
		FROM `s_order`,`s_user`,`s_user_billingaddress`,`s_core_states`
		WHERE 
			s_order.status != 4 AND s_order.status != -1
		AND
			s_user.id = s_order.userID
		AND
			s_user.id = s_user_billingaddress.userID
		AND
			partnerID = '{$partner["idcode"]}'
		AND
			s_core_states.id = s_order.status
		AND 
			MONTH(s_order.ordertime) = {$data[0]}
		AND
			YEAR(s_order.ordertime) = {$data[1]}
		GROUP BY 
		ordernumber
		ORDER BY ordertime DESC";

	include ("csvdump.php");
	$dumpfile = new iam_csvdump();
	$dumpfile->dump($sql, "Auswertung {$partner["company"]} / {$partner["lastname"]} - ".$_POST["sExportData"], "csv", "", "", "", "" ); 
	die();
}

?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="js/calendar.css" rel="stylesheet" type="text/css">
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
</head>

<body >
<?php
$licenceFailed = false;
?>
<fieldset class="col2_cat2">
<legend><a class="ico help"></a><?php echo $sLang["partner"]["partner_partner_Module"] ?></legend>
<?php echo $sLang["partner"]["partner_here_you_can_administrate_partner"] ?>
</fieldset>
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deletePartner":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
			break;
	}
}

function deleteArticle(ev,text){
		parent.sConfirmationObj.show('<?php echo $sLang["partner"]["partner_should_the_partner"]?> "'+text+'" <?php echo $sLang["partner"]["partner_really_deleted"] ?>',window,'deletePartner',ev);
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
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>&new=<?php echo $_GET["new"] ?>>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $sLang["partner"]["partner_Partnerdetails"] ?></legend>
		<ul>
		<?php
		$valueName = $sLang["partner"]["partner_Number_Range"];
		$valueDelete = false;
		$valueTable = "s_emarketing_partner";
		$valueAdd = false;
		$valueDescription = "name";
		$substitute = $sLang["partner"]["partner_array"];
		
		$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		  	
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	  
		   	   if ($row["Type"]=="int(1)"){
		   	   if ($column=="Aktiv" && !$partner[$row["Field"]] && $_GET["new"]){ $partner[$row["Field"]] = true; }
		   	   	echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>";
		   	   	if ($partner[$row["Field"]]){
		   	   		$selYes = "selected";
		   	   		$selNo = "";
		   	   	}else {
		   	   		$selYes = "";
		   	   		$selNo = "selected";
		   	   	}
		   	   	echo "<select name=\"{$fieldName}\" id=\"\" style=\"height:25px;width:250px\" class=\"w200\">";
		   	   	echo "<option value=\"1\" $selYes>".$sLang["partner"]["partner_yes"]."</option>";
		   	   	echo "<option value=\"0\" $selNo>".$sLang["partner"]["partner_no"]."</option>";
		   	   	echo "</select>";
		   	   	echo "</li>";
		   	    echo "<li class=\"clear\"/>";
		   	   }else if ($row["Type"]=="text"){
		   	   	echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><textarea name=\"{$fieldName}\" style=\"height:125px;width:250px\" class=\"w200\">".htmlspecialchars($partner[$row["Field"]])."</textarea></li>";
			   	    	echo "<li class=\"clear\"/>";
		   	   }
		   	   
		   	  
		   	
		   	   else {
		   	   		if ($column=="Tracking-Code" || $column=="Firma" || $column=="Provision in %") $column = "<strong>$column</strong>";
			   	    if ($column=="Eingetragen seit"){ } else {
		   	   			echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"".htmlspecialchars($partner[$row["Field"]])."\" /></li>";
			   	    	echo "<li class=\"clear\"/>";
			   	    }
		   	   }
		       
		        
		      
		       
		      

		   	}

	   }
	   ?>
		
		<li class="clear"/>
		
		</ul>
			<div class="buttons" id="buttons">
				<ul>
					<li id="buttonTemplate" class="buttonTemplate">
					<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["partner"]["partner_save"] ?></div></button>
					</li>	
				</ul>
			</div>
		</fieldset>
		</form>
<?php
}
?>

<?php
if ($_GET["showReport"]){
?>
<fieldset class="col2_cat2" style="margin-bottom:0px;padding-top:0">
<legend><?php echo $sLang["partner"]["partner_Evaluation"] ?> <?php echo $partner["company"] ?>:</legend><br />
<form id="exportForm" method="POST" action="">
<input type="hidden" name="sExportPartner" value="1">
<?php
// Get all months with amount-data inside
$sql = "SELECT 
		ROUND(SUM((s_order.invoice_amount_net-s_order.invoice_shipping_net)/currencyFactor),2) AS `amount`,
		MONTH(ordertime) AS `Monat`,
		YEAR(ordertime) AS `Jahr`
		FROM `s_order`
		WHERE 
			status != 4
		AND
			status != -1
		AND
			partnerID = '{$partner["idcode"]}'
		GROUP BY 
		YEAR(ordertime),MONTH(ordertime)
		ORDER BY YEAR(ordertime) DESC, MONTH(ordertime) DESC";

$partnerData = mysql_query($sql);
?>
<strong><?php echo $sLang["partner"]["partner_partner_link"] ?></strong> http://<?php echo $sCore->sCONFIG["sBASEPATH"] ?>/<?php echo $sCore->sCONFIG["sBASEFILE"] ?>?sPartner=<?php echo $partner["idcode"]?> <br /><br />
<?php
if (@mysql_num_rows($partnerData)){
?>
<table border="0" width="100%">
<tr>
<td style="border:1px grey solid; font-size:11px"><strong><?php echo $sLang["partner"]["partner_month_year"] ?></strong></td>
<td style="border:1px grey solid; font-size:11px"><strong><?php echo $sLang["partner"]["partner_Turnover"] ?></strong></td>
<td style="border:1px grey solid; font-size:11px"><strong><?php echo $sLang["partner"]["partner_Commission"] ?></strong></td>
</tr>
<?php
while ($monthAmount = mysql_fetch_array($partnerData)){
	$i++;
?>
<tr style="background-color:<?php echo $i % 2 ? "#CCC" : "#FFF" ?>">
<td style="border:1px grey solid; font-size:11px"><?php echo $monthAmount["Monat"] ?>.<?php echo $monthAmount["Jahr"] ?></td>
<td style="border:1px grey solid; font-size:11px"><?php echo $sCore->sFormatPrice($monthAmount["amount"]) ?> €</td>
<td style="border:1px grey solid; font-size:11px"><?php echo $sCore->sFormatPrice(round($monthAmount["amount"] / 100 * $partner["percent"],2)); ?> €</td>
</tr>
<?php
} @mysql_data_seek($partnerData,0);
?>
</table><br />

<table border="0" width="100%">
<tr>
<td style="font-size:11px"><select name="sExportData">
<?php
while ($monthAmount = mysql_fetch_array($partnerData)){
?>
	<option value="<?php echo $monthAmount["Monat"] ?>.<?php echo $monthAmount["Jahr"] ?>"><?php echo $monthAmount["Monat"] ?>.<?php echo $monthAmount["Jahr"] ?></option>
<?php
}
?>
</select></td>
<td style="font-size:11px">
<div class="buttons" id="buttons">
	<ul>
		<li id="buttonTemplate" class="buttonTemplate">
		<button onClick="$('exportForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["partner"]["partner_csv_export"] ?></div></button>
		</li>	
	</ul>
</div>
</td>
</tr>
</table>
<?php
}else {
?>
<?php echo $sLang["partner"]["partner_No_transactions_available"] ?>
<?php
}
?>
</form>

</fieldset>
<?php
}
?>


<?php
if (!$licenceFailed){
?>

			<div class="buttons" id="buttons" style="margin-left: 5px;">
				<ul>
					<li id="buttonTemplate" class="buttonTemplate">
					<a href="<?php echo $_SERVER["PHP_SELF"]."?new=1"?>" style="text-decoration:none;">
					<button type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["partner"]["partner_add_new_partner"] ?></div></button>
					</a>
					</li>	
				</ul>
			</div>
			<div class="clear" style="height:40px;"></div>
<?php
}
?>
<div style="clear:both"></div>
<fieldset class="white" style="padding:0;margin-top:-35px;">
<legend>Angelegte Partner</legend>


<script type='text/javascript'>	
<?php
	$sql = "
	SELECT id, idcode,active, company, DATE_FORMAT(datum,'%d.%m.%Y') AS datum, web, email FROM s_emarketing_partner ORDER BY company ASC
	";
	$getPartner = mysql_query($sql);
	
	if (!mysql_num_rows($getPartner)){
		?>
			var headers = [
			{
			"text":"<?php echo $sLang["partner"]["partner_status"] ?>",
			"key":"kdnr","sortable":true,
			"fixedWidth":true,"defaultWidth":"500px"}
			];
			var data = [{"kdnr":"<?php echo $sLang["partner"]["partner_no_partner_registered"] ?>"}
			];
		<?php
	}else {
		?>
		var headers = [
		{
		"text":"<?php echo $sLang["partner"]["partner_Company"] ?>",
		"key":"company","sortable":true,
		"fixedWidth":true,"defaultWidth":"125px"},
		{
		"text":"<?php echo $sLang["partner"]["partner_Entered"] ?>",
		"key":"datum","sortable":true,
		"fixedWidth":true,"defaultWidth":"75px"},
		{
		"text":"<?php echo $sLang["partner"]["partner_activ"] ?>",
		"key":"state","sortable":true,
		"fixedWidth":true,"defaultWidth":"40px"},
		{
		"text":"<?php echo $sLang["partner"]["partner_Annual_sales"] ?>",
		"key":"amount12","sortable":true,
		"fixedWidth":true,"defaultWidth":"125px"},
		{
		"text":"<?php echo $sLang["partner"]["partner_Monthly_Sales"] ?>",
		"key":"amountmonth","sortable":true,
		"fixedWidth":true,"defaultWidth":"125px"}
		,
		{
		"text":"<?php echo $sLang["partner"]["partner_options"] ?>",
		"key":"options","sortable":true,
		"fixedWidth":true,"defaultWidth":"100px"}
		];
		<?php
		echo "var data = [";
		$countPartner = mysql_num_rows($getPartner);
		$i = 0;
		while ($partner = mysql_fetch_array($getPartner)){
			// Calculate yearly amount
			$sql = "
			SELECT 
			SUM(invoice_amount_net - invoice_shipping_net) AS `amount`
			FROM `s_order`
			WHERE 
				YEAR(ordertime) = YEAR(now())
			AND 
				status != 4
			AND
				status != -1
			AND
				partnerID = '{$partner["idcode"]}'
			GROUP BY 
			YEAR(ordertime)
			ORDER BY YEAR(ordertime) ASC
			";
			
			$getYearlyAmount = mysql_query($sql);
			$getYearlyAmount = @mysql_result($getYearlyAmount,0,"amount") ? $sCore->sFormatPrice(mysql_result($getYearlyAmount,0,"amount")) : "0,00";
			
			$sql = "
			SELECT 
			SUM(invoice_amount_net - invoice_shipping_net) AS `amount`
			FROM `s_order`
			WHERE 
				MONTH(ordertime) = MONTH(now())
			AND
				YEAR(ordertime) = YEAR(now())
			AND 
				status != 4
			AND
				status != -1
			AND
				partnerID = '{$partner["idcode"]}'
			GROUP BY 
			YEAR(ordertime)
			ORDER BY YEAR(ordertime) ASC
			";
			
			$getMonthlyAmount = mysql_query($sql);
			$getMonthlyAmount = @mysql_result($getMonthlyAmount,0,"amount") ? $sCore->sFormatPrice(mysql_result($getMonthlyAmount,0,"amount")) : "0,00";
	
			
			// Calculate monthly amount
			
			
			$i++;
			$comma = $i == $countOrders ? "" : ",";
			
			?>
			{"company":"<?php echo htmlspecialchars($partner["company"])?>","datum":"<?php echo $partner["datum"]?>","state":"<?php if($partner["active"]==1){ ?><a class='ico accept'></a><?php } else { ?><a class='ico delete'></a><?php } ?>","amount12":"<?php echo $getYearlyAmount ?> €","amountmonth":"<?php echo $getMonthlyAmount ?> €","options":"<a style=\"cursor:pointer\" class=\"ico delete\" onClick=\"deleteArticle(<?php echo $partner["id"] ?>,'test')\"></a><a href=\"<?php echo $_SERVER["PHP_SELF"]?>?edit=<?php echo $partner["id"] ?>\" style=\"cursor:pointer\" class=\"ico pencil\"></a><a href=\"<?php echo $_SERVER["PHP_SELF"] ?>?showReport=<?php echo $partner["id"] ?>\" class=\"ico chart_bar02\" style=\"cursor:pointer\"></a>"}<?php echo $comma ?>
			<?php
		}
		echo "];";
	}
?>
window.addEvent('load',function(){
	mootable = new MooTable( 'test', {debug: false, height: '370px', headers: headers, sortable: true, useloading: false, resizable: false});
	mootable.loadData( data );
});
</script>



<div id='test' style="padding-top:1px;"></div>
</fieldset>

</body>

</html><?php die(); ?>