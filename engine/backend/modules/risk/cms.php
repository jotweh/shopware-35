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

if ($_GET["delete"]){
	$deletePreviousRules = mysql_query("
	DELETE FROM s_core_rulesets WHERE
	id = {$_GET["delete"]}
	");
	$sInform = "Riskmanagement-Regel wurde gelöscht";
}

?>

<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

</head>

<body >

<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteArticle":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?group=<?php echo $_GET["group"]?>&delete="+sId;
			break;
	}
}

function deleteArticle(ev,text){
		parent.sConfirmationObj.show('<?php echo $sLang["risk"]["cms_should_the_article"] ?> "'+text+'" <?php echo $sLang["risk"]["cms_really_be_deleted"] ?>',window,'deleteArticle',ev);
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

$licenceFailed = false;
if ($_POST["sSaveRules"]){
$deletePreviousRules = mysql_query("
DELETE FROM s_core_rulesets WHERE paymentID = {$_GET["id"]}
");
	foreach ($_POST["ruleSelect"] as $key => $value){
		if ($licenceFailed && $key>0) continue;
		if ($value[0]){
		
			// Bedingung 1 gesetzt
			$insertRule = mysql_query("
			INSERT INTO s_core_rulesets (paymentID, rule1, value1, rule2, value2)
			VALUES (
			{$_GET["id"]},
			'{$value[0]}',
			'{$_POST["ruleValue"][$key][0]}',
			'{$value[1]}',
			'{$_POST["ruleValue"][$key][1]}'
			)
			");
		}
	}
echo "<script>parent.parent.Growl('Riskmanagement Regeln wurden modifiziert');</script>";
}
?>
<fieldset class="col2_cat2" style="width:400px;">
<legend><a class="ico help"></a><?php echo $sLang["risk"]["cms_Risk-Management"] ?></legend>
<?php echo $sLang["risk"]["cms_Here_you_can_define_rules"] ?>
</fieldset>

<form method="GET" id="groupChange" action="<?php echo $_SERVER["PHP_SELF"] ?>">
<fieldset>
<legend><?php echo $sLang["risk"]["cms_select_payment"] ?></legend>
<ul>
<li>
		
		<select name="id" style="width:250px">
		<?php
		$getGroups = mysql_query("
		SELECT * FROM s_core_paymentmeans 
		WHERE active = 1
		ORDER BY id ASC
		");
		while ($group=mysql_fetch_array($getGroups)){
			if ($group["id"]==$_GET["id"]){
				$selected = "selected";
			}else {
				unset($selected);
			}
			// Check if rules are defined
			$queryRules = mysql_query("
			SELECT id FROM s_core_rulesets WHERE paymentID = {$group["id"]}
			");
			if (@mysql_num_rows($queryRules)){
				$style = "style=\"background-color:#F00\"";
			}else {
				unset($style);
			}
		?>
		<option value="<?php echo $group["id"] ?>" <?php echo $selected ?> <?php echo $style ?>><?php echo $group["description"]?> (<?php echo $group["name"]?>)</option>	
		<?php
		}
		?>
		</select>
		
		
		</li>
		
</ul>
<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button id="btnBrowse" type="button" value="send" class="button" onClick="$('groupChange').submit();"><div class="buttonLabel"><?php echo $sLang["risk"]["cms_select"] ?></div></button>
			</li>	
		</ul>
	</div>
</fieldset>
</form>
<?php
if ($_GET["id"]){
	$getPayment = mysql_query("
	SELECT * FROM s_core_paymentmeans 
	WHERE id = {$_GET["id"]}
	");
	
	$payment = mysql_fetch_array($getPayment);

?>

<form method="POST" id="saveRuleSet" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
<input type="hidden" name="sSaveRules" value="1">
<fieldset>
<legend><?php echo $payment["description"] ?> <?php echo $sLang["risk"]["cms_block_IF"] ?></legend>
<?php
$rules = array(
"ORDERVALUEMORE"=>"Bestellwert >=",
"ORDERVALUELESS"=>"Bestellwert <=",
"CUSTOMERGROUPIS"=>"Kundengruppe IST",
"CUSTOMERGROUPISNOT"=>"Kundengruppe IST NICHT",
"NEWCUSTOMER"=>"Neukunde IST WAHR",
"ZONEIS"=>"Zone IST",
"ZONEISNOT"=>"Zone IST NICHT",
"LANDIS"=>"Land IST",
"LANDISNOT"=>"Land IST NICHT",
"ORDERPOSITIONSMORE"=>"Bestellpositionen >=",
"INKASSO"=>"Inkasso IST WAHR",
"LASTORDERLESS"=>"Keine Bestellung vor mind. X Tagen",
"LASTORDERSLESS"=>"Anzahl Bestellungen <=",
"ARTICLESFROM"=>"Artikel aus Kategorie",
"ZIPCODE"=>"Postleitzahl IST",
"PREGSTREET"=>"Straßenname ENTHÄLT X",
"CUSTOMERNR"=>"Kundennummer IST",
"LASTNAME"=>"Nachname ENTHÄLT X",
"SUBSHOP"=>"Subshop IST",
"SUBSHOPNOT"=>"Subshop IST NICHT",
"DIFFER"=>"Lieferadresse != Rechnungsadresse",
"CURRENCIESISOIS"=>"Währungs-Iso-Kürzel IST",
"CURRENCIESISOISNOT"=>"Währungs-Iso-Kürzel IST NICHT",
"ATTRIS"=>"Artikel-Attribut IST (1>5)",
"ATTRISNOT"=>"Artikel-Attribut IST NICHT (1>5)"
);

$syntax = array(
"ORDERVALUEMORE"=>"Wert numerisch",
"ORDERVALUELESS"=>"Wert numerisch",
"CUSTOMERGROUPIS"=>"ID der Kundengruppe",
"CUSTOMERGROUPISNOT"=>"ID der Kundengruppe",
"NEWCUSTOMER"=>"1 oder 0",
"ZONEIS"=>"deutschland,europa,welt",
"ZONEISNOT"=>"deutschland,europa,welt",
"LANDIS"=>"Länder-Isokürzel",
"LANDISNOT"=>"Länder-Isokürzel",
"ORDERPOSITIONSMORE"=>"Wert numerisch",
"INKASSO"=>"1 oder 0",
"LASTORDERLESS"=>"Wert numerisch",
"LASTORDERSLESS"=>"Wert numerisch",
"ARTICLESFROM"=>"ID der Kategorie",
"ZIPCODE"=>"Wert numerisch",
"PREGSTREET"=>"Begriff oder Teilbegriff",
"CUSTOMERNR"=>"Wert numerisch",
"LASTNAME"=>"Wert numerisch",
"SUBSHOP"=>"ID/UniqueKey des Subshops",
"SUBSHOPNOT"=>"ID/UniqueKey des Subshops",
"DIFFER"=>"Lieferadresse != Rechnungsadresse",
"CURRENCIESISOIS"=>"Währungs-Iso-Kürzel",
"CURRENCIESISOISNOT"=>"Währungs-Iso-Kürzel",
"ATTRIS"=>"attr*1-20*|5",
"ATTRISNOT"=>"attr*1-20*|5"
);

$sample = array(
"ORDERVALUEMORE"=>"500.50",
"ORDERVALUELESS"=>"500.50",
"CUSTOMERGROUPIS"=>"EK für Shopkunden, H für Händler",
"CUSTOMERGROUPISNOT"=>"EK für Shopkunden, H für Händler",
"NEWCUSTOMER"=>"1",
"ZONEIS"=>"deutschland,europa,welt",
"ZONEISNOT"=>"deutschland,europa,welt",
"LANDIS"=>"z.B. DE für Deutschland",
"LANDISNOT"=>"z.B. DE für Deutschland",
"ORDERPOSITIONSMORE"=>"5",
"INKASSO"=>"1",
"LASTORDERLESS"=>"30",
"LASTORDERSLESS"=>"30",
"ARTICLESFROM"=>"3",
"ZIPCODE"=>"48624",
"PREGSTREET"=>"z.B. Packstation, sperrt die Zahlungsarten für alle Adressen in den 'Packstation' vorkommt",
"CUSTOMERNR"=>"12345",
"LASTNAME"=>"Mustermann",
"SUBSHOP"=>"s_core_multilanguage.id also z.B. 1 für Hauptshop",
"SUBSHOPNOT"=>"s_core_multilanguage.id also z.B. 1 für Hauptshop",
"DIFFER"=>"Lieferadresse != Rechnungsadresse",
"CURRENCIESISOIS"=>"EUR",
"CURRENCIESISOISNOT"=>"USD",
"ATTRIS"=>"attr5|2 Zahlart sperren wenn Artikel im Warenkorb mit attr5 = 2",
"ATTRISNOT"=>"attr5|2 Zahlart NICHT sperren wenn Artikel im Warenkorb mit attr5 = 2"
);
// Get all previous rules
$selectRules = mysql_query("
SELECT * FROM s_core_rulesets 
WHERE paymentID = {$_GET["id"]}
ORDER BY id ASC
");
$i = "0";
while ($rule = mysql_fetch_array($selectRules)){
?>
	<ul>
		<li>
			<label style="margin-right:120px">
				<select name="ruleSelect[<?php echo $i ?>][0]">
				<option value=0><?php echo $sLang["risk"]["cms_please_select"] ?></option>
					<?php
						foreach ($rules as $ruleSetsKey => $ruleSet){
							if ($rule["rule1"]==$ruleSetsKey){
								$selected = "selected";
							}else {
								$selected = "";
							}
							echo "<option value=\"$ruleSetsKey\" $selected>$ruleSet</option>";
						}
					?>
				</select>
			</label>
				<input type="text" style="width:50px;height:22px" value="<?php echo $rule["value1"] ?>" name="ruleValue[<?php echo $i ?>][0]">
		</li>
		<li><strong><?php echo $sLang["risk"]["cms_AND"] ?></strong></li>
		<li>
			<label style="margin-right:120px">
				<select name="ruleSelect[<?php echo $i ?>][1]">
				<option value=0><?php echo $sLang["risk"]["cms_please_select"] ?></option>
					<?php
						foreach ($rules as $ruleSetsKey => $ruleSet){
							if ($rule["rule2"]==$ruleSetsKey){
								$selected = "selected";
							}else {
								$selected = "";
							}
							echo "<option value=\"$ruleSetsKey\" $selected>$ruleSet</option>";
						}
					?>
				</select>
			</label>
				<input type="text" style="width:50px;height:22px" value="<?php echo $rule["value2"] ?>" name="ruleValue[<?php echo $i ?>][1]">
		</li>
		<li><a class="ico delete" href="<?php echo $_SERVER["PHP_SELF"]."?delete={$rule["id"]}&id={$_GET["id"]}"?>"></a></li>
	</ul>
<?php
$i++;
	echo "<br /><strong>".$sLang["risk"]["cms_OR"]."</strong><br /><br />";
}
?>
<ul>
	<li>
		<label style="margin-right:120px">
			<select name="ruleSelect[<?php echo $i ?>][0]">
			<option value=0><?php echo $sLang["risk"]["cms_please_select"] ?></option>
			<?php
				foreach ($rules as $ruleSetsKey => $ruleSet){
					echo "<option value=\"$ruleSetsKey\">$ruleSet</option>";
				}
			?>
			</select>
		</label>
		<input type="text" style="width:50px;height:22px" name="ruleValue[<?php echo $i ?>][0]">
	</li>
	<li><strong><?php echo $sLang["risk"]["cms_AND"] ?></strong></li>
	<li>
		<label style="margin-right:120px">
			<select name="ruleSelect[<?php echo $i ?>][1]">
			<option value=0><?php echo $sLang["risk"]["cms_please_select"] ?></option>
			<?php
				foreach ($rules as $ruleSetsKey => $ruleSet){
					echo "<option value=\"$ruleSetsKey\">$ruleSet</option>";
				}
			?>
			</select>
		</label>
		<input type="text" style="width:50px;height:22px" name="ruleValue[<?php echo $i ?>][1]">
	</li>
</ul>


	
	<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button id="btnBrowse" type="button" value="send" class="button" onClick="$('saveRuleSet').submit();"><div class="buttonLabel"><?php echo $sLang["risk"]["cms_save"] ?></div></button>
			</li>	
		</ul>
	</div>

</fieldset>
</form>

<fieldset>
<legend>Erklärung</legend>
<style>
td {
font-size:12px;
}		
</style>
<p style="padding:5px 5px 5px 5px;height:300px;overflow-x:hidden;overflow-y:scroll">
<table>
<tr>
<td><strong>Regel</strong></td>
<td><strong>Syntax</strong></td>
<td><strong>Beispiel</strong></td>
</tr>

<?php
foreach ($rules as $k => $value){
	?>
	<tr>
<td><strong><?php echo $value ?></strong></td>
<td><?php echo $syntax[$k]?></td>
<td><?php echo $sample[$k]?></td>
</tr>
	<?php
}
?>
</table>
</p>
</fieldset>

<?php
}
?>
</body>

</html><?php die(); ?>