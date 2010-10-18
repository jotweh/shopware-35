<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
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

$possiblePayments = array(
  'WLT' => 'Moneybookers Wallet',
  'ACC' => 'all credit/debit cards',
  'VSA' => 'VISA',
  'MSC' => 'MASTERCARD',
  'VSD' => 'DELTA / VISA DEBIT',
  'VSE' => 'VISA ELECTRON',
  'AMX' => 'AMERICAN EXPRESS',
  'DIN' => 'DINERS',
  'JCB' => 'JCB',
  'MAE' => 'MAESTRO',
  'LSR' => 'LASER',
  'SLO' => 'SOLO',
  'GCB' => 'Carte Bleue',
  'SFT' => 'Sofortueberweisung',
  'DID' => 'direct debit',
  'GIR' => 'Giropay',
  'ENT' => 'Enets',
  'EBT' => 'Solo sweden',
  'SO2' => 'Solo finland',
  'NPY' => 'eps (NetPay)',
  'PLI' => 'POLi',
  'DNK' => 'Dankort',
  'CSI' => 'CartaSi',
  'PSP' => 'Postepay',
  'EPY' => 'ePay Bulgaria',
  'BWI' => 'BWI',
  'PWY6' => 'PKO BP (PKO Inteligo)',
  'PWY7' => 'Multibank (Multitransfer)',
  'PWY14' => 'Lukas Bank',
  'PWY15' => 'Bank BPH',
  'PWY37' => 'Kredyt Bank',
  'PWY17' => 'InvestBank',
  'PWY18' => 'PeKaO S.A.',
  'PWY19' => 'Citibank handlowy',
  'PWY20' => 'Bank Zachodni WBK (Przelew24)',
  'PWY21' => 'BGZ',
  'PWY22' => 'Millenium',
  'PWY26' => 'Place z Inteligo',
  'PWY25' => 'mBank (mTransfer)',
  'PWY28' => 'Bank Ochrony Srodowiska',
  'PWY32' => 'Nordea',
  'PWY33' => 'Fortis Bank',
  'PWY36' => 'Deutsche Bank PBC S.A,.',
);


#echo '<pre>'.print_r($_POST, 1).'</pre>';

if ($_GET["sInstall"]){
  $installed = '';
  $installedPaymethods = array();
	foreach ($_POST['pm'] as $key => $value){
    $value = mysql_real_escape_string($value);
    $installed.= $value.',';
    $installedPaymethods[] = $value;

    $sql = 'SELECT * FROM `s_core_paymentmeans` WHERE `name` = "moneybookers_'.$value.'"';
    $res = mysql_query($sql);
    if (!@mysql_num_rows($res)){
      $sql = 'INSERT INTO `s_core_paymentmeans` SET
            `id` = NULL,
            `name` = "moneybookers_'.$value.'",
            `description` = "Moneybookers '.$possiblePayments[$value].'",
            `template` = "moneybookers.tpl",
            `class` = "moneybookers.class.php",
            `table` = "",
            `hide` = "0",
            `additionaldescription` = "",
            `debit_percent` = "0",
            `surcharge` = "0",
            `surchargestring` = "",
            `position` = "0",
            `active` = "0",
            `esdactive` = "1",
            `embediframe` = "../../../../../../../engine/connectors/moneybookers/form.php",
            `hideprospect` = "0"
            ';
      mysql_query($sql);
    }
    
  }
  $installed = substr($installed, 0, -1);
  $sql = 'UPDATE `s_core_config` SET `value` = "'.$installed.'" WHERE `name` = "sMONEYBOOKERS_INSTALLED_PAYMETHODS"';
  $update = mysql_query($sql);

  // Nicht installierte Paymethods aufräumen
  $unusedPaymentIds = array();
  foreach ($possiblePayments as $key => $value){
    if (!in_array($key, $installedPaymethods)){
      // PaymentID der nicht installierten Paymethods ermitteln
      $sql = 'SELECT * FROM `s_core_paymentmeans` WHERE `name` = "moneybookers_'.$key.'"';
      $res = mysql_query($sql);
      $row = mysql_fetch_assoc($res);
      $unusedPaymentIds[] = $row['id'];
      // Nicht installierte Paymethods löschen
      $sql = 'DELETE FROM `s_core_paymentmeans` WHERE `name` = "moneybookers_'.$key.'"';
      mysql_query($sql);
    }
  }
  // Kunden die eine nicht installierte Paymethod nutzen auf Vorkasse setzen
  $sql = 'UPDATE `s_user` SET `paymentID` = "5" WHERE `paymentID` IN ("'.implode('","', $unusedPaymentIds).'")';
  mysql_query($sql);
}

if ($_GET["sSave"]){
	foreach ($_POST as $key => $value){
		$value = mysql_real_escape_string($value);
		$update = mysql_query("
		UPDATE s_core_config SET value='$value' WHERE name='$key'
		");
	}
}

$sCore->sInitTranslations(1,"config");
?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../backend/js/mootools.js"></script>
<link href="../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

<style>
.clear { /*  - fixfloat */
	clear: both;
	padding: 0;
	margin:0;
	width: 0px;
	height: 0px;
	line-height: 0px;
	font-size: 0px;
}
td {
	font-size: 10px;
}
.w250{width: 250px;}
</style>
</head>
<body style="padding: 10 10 10 10; margin: 0 0 0 0; ">
<?php
$grpID = mysql_query("
SELECT id FROM s_core_config_groups WHERE name = 'Moneybookers'
");
$_GET["id"] = mysql_result($grpID,0,"id");
$sCore->sInitTranslations("1","config");
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?sSave=1&id=".$_GET["id"] ?>">
<?php
$sql = 'SELECT * FROM s_core_config_groups WHERE id="'.addslashes($_GET["id"]).'"';
$queryGroups = mysql_query($sql);
if (!@mysql_num_rows($queryGroups)) exit();
$group = mysql_fetch_array($queryGroups);

$sql = 'SELECT * FROM `s_core_config` WHERE `group`="'.addslashes($group["id"]).'" ORDER BY `id` ASC';
$queryOptions = mysql_query($sql);
if (!@mysql_num_rows($queryOptions)) exit();

$sql = 'SELECT * FROM `s_core_states` WHERE `group` = "payment" OR `id` = 0 ORDER BY `description` ASC';
$res = mysql_query($sql);
$states = array();
while ($row = mysql_fetch_assoc($res)){
  $states[$row['id']] = $row;
}
#echo '<pre>'.print_r($states, 1).'</pre>';
?>

<fieldset style="margin-top:-29px"><legend><?php echo $group["name"]?></legend>
<fieldset class="col2_cat2" style="margin-top:-15px">
	<legend><a class="ico exclamation"></a><span style=\"color:#F00\">Hinweis:</span></legend>
  Moneybookers ist einer der größten Online-Zahlungsdienstleister Europas und in über 200 Ländern mit lokalen Bezahloptionen vertreten<br>
  <br>
  Die Vorteile von Moneybookers<br>
  <ol>
  <li>Einfacher Zugang zum größten Bezahlnetzwerk in Europa</li>
  <li>Mehr Kunden dank mehr Bezahlvarianten</li>
  <li>Kosten- und Zeitersparnis durch Lösung aus einer Hand</li>
  <li>Weniger Rücklastschriften dank Risiko-Check</li>
  <li>Hohe Konversionsrate durch weniger Bestellabbrüche</li>
  <li>Erhöhte Kundenbindung durch Einsatz des eWallets</li>
  <li>Zuverlässig und sicher: Alle Daten werden verschlüsselt übertragen</li>
  </ol>
  Melden Sie sich <a href="http://www.moneybookers.com/partners/shopware/" target="_blank">hier</a> mit an um Moneybookers als Händler zu benutzen.<br>
  </fieldset>

<table>
<?php
while ($field=mysql_fetch_array($queryOptions)){
  if ($field["name"] == 'sMONEYBOOKERS_INSTALLED_PAYMETHODS'){
    define('sMONEYBOOKERS_INSTALLED_PAYMETHODS', $field["value"]);
    continue;
  }
?>
<tr><td><label class="w250"><?php echo$field["description"]?>:</label></td><td>
<?php
if (strpos($field["name"], 'STATUS_') !== false){
  ?><select class="w250" style="height:25px" name="<?php echo$field["name"]?>" id="<?php echo$field["name"]?>">
    <?php foreach($states AS $k => $v){?>
    <option value="<?php echo$k?>" <?php if ($field["value"] == $k) echo 'selected';?>><?php echo$v['description']?></option>
    <?php }?>
    </select><?php
} else if (strpos($field["name"], 'LIST_') !== false){
  ?><select class="w250" name="<?php echo $field["name"]?>" id="<?php echo $field["name"]?>" multiple size="10">
    <?php foreach($states AS $k => $v){?>
    <option value="<?php echo $k?>" <?php if ($field["value"] == $k) echo 'selected';?>><?php echo $v['description']?></option>
    <?php }?>
    </select><?php
} else if (strpos($field["name"], 'SWITCH_') !== false){
  ?>
  <table><tr>
  <td><input type="radio" value="1" name="<?php echo$field["name"]?>" id="<?php echo $field["name"]?>" title="<?php echo $field["name"]?>" <?php if ($field["value"] == 1) echo 'checked';?>></td>
  <td>Ja</td>
  <td><input type="radio" value="0" name="<?php echo$field["name"]?>" id="<?php echo $field["name"]?>" title="<?php echo $field["name"]?>" <?php if ($field["value"] == 0) echo 'checked';?>></td>
  <td>Nein</td>
  </tr></table>
  <?php
} else {
  ?><input class="w250" style="height:25px" value="<?php echo htmlentities($field["value"]);?>" name="<?php echo $field["name"]?>" id="<?php echo$field["name"]?>" title="<?php echo $field["name"]?>"><?php
}
?>
<?php if ($field["multilanguage"]) echo $sCore->sBuildTranslation($field["name"],$field["name"],1,"config");?>
</td></tr>
<?php }?>
</table>

<ul>
<li class="clear"></li>
<li style="clear:both;">
	<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="save" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo$sLang["presettings"]["pricegroup_save"] ?></div></button>
	</li></ul></div>
</li>
</ul>

</fieldset>
<div class="clear"></div>
</form>

<?php
$installed = explode(',', sMONEYBOOKERS_INSTALLED_PAYMETHODS);
?>
Moneybookers Paymethod Install:<br>
<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?sInstall=1&id=".$_GET["id"] ?>">
<select name="pm[]" id="pm" size="15" multiple="true">
<?php foreach($possiblePayments AS $k => $v){?>
<option value="<?php echo $k?>" <?php if (in_array($k, $installed)) echo 'selected';?>><?php echo $v?></option>
<?php }?>
</select><br>
<button id="save" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["pricegroup_save"] ?></div></button>
</form>

<?php include("../../backend/elements/window/translations.htm");?>

<script type="text/javascript" src="../../backend/js/translations.php"></script>
</body>

</html>
