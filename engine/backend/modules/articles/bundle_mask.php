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

if (!$sCore->sCheckLicense("","",$sCore->sLicenseData["sBUNDLE"])) exit;

class sBundle
{
	//new||edit
	public $sModus;
	//s_articles.id
	public $sArticleID;
	//s_articles_bundles.id
	public $sBundleID;
	//Verfügbare Kundengruppen
	public $sCustomerGrps;
	public $sCustomerGrpsById;

	//Array an Values (Stammdaten)
	public $sValues;
	//Array an Values (Preise)
	public $sValuesPrices;
	//Array an Feldern mit einem Fehler
	public $sErrorFields;

	//Alle verfügbaren Bestellnummern
	public $sArticleOrdernumbers;

	//BundleDaten
	public $sBundleData;

	public function __construct()
	{
		$this->sCheckHandleParams();
		$this->sFetchCustomergroups();
		$this->sFetchArticleOrdernumbers();

		//Speichern/Laden der Preisdetails
		if(isset($_POST['sActionCGRP']))
		{
			$this->sSaveCGRP();
		}
		$this->sLoadCGRP();

		//Speichern/Laden der Stammdaten
		if(isset($_POST['sAction']))
		{
			$this->sSaveAction();
		}else{
			$this->sLoadBundle();
		}
	}

	public function sFetchArticleOrdernumbers()
	{
		//Alle verfügbaren Artikelnummern ermitteln
		//$articleOrdernumbers
		$this->sArticleOrdernumbers = array();

		//Normale und Variantenartikel
		$sqlAD = sprintf("
				SELECT ordernumber
				FROM `s_articles_details`
				WHERE `articleID` = %s
				ORDER BY `ordernumber`
			", $this->sArticleID);
		$sqlADQuery = mysql_query($sqlAD);
		if(mysql_num_rows($sqlADQuery) != 0)
		{
			while($fetchAD = mysql_fetch_assoc($sqlADQuery))
			{
				$this->sArticleOrdernumbers[] = strtoupper($fetchAD['ordernumber']);
			}
		}
		//Konfiguratorartikel
		$sqlC = sprintf("
				SELECT ordernumber
				FROM `s_articles_groups_value`
				WHERE `articleID` = %s
			", $this->sArticleID);
		$sqlCQuery = mysql_query($sqlC);
		if(mysql_num_rows($sqlCQuery) != 0)
		{
			while($fetchC = mysql_fetch_assoc($sqlCQuery))
			{
				//save case-insensitive
				$this->sArticleOrdernumbers[] = strtoupper($fetchC['ordernumber']);
			}
		}
	}

	public function sCheckHandleParams()
	{
		//ArtikelID
		$this->sArticleID = intval($_GET["articleID"]);
		if(empty($this->sArticleID)) die("missing param <b>articleID</b>");

		//Modus
		if(!empty($_GET['new']))
		{
			$this->sModus = "new";
		}elseif(!empty($_GET['bundleID']))
		{
			$this->sModus = "edit";
			$this->sBundleID = intval($_GET['bundleID']);
		}else{
			die("missing parameter <b>new</b> or <b>bundleID</b>");
		}
	}

	/**
	* Liest alle Kundengruppen aus
	**/
	public function sFetchCustomergroups()
	{
		$this->sCustomerGrps = array();
		$getCustomerGrpsQ = mysql_query("SELECT `id`, `groupkey`, `taxinput`, `description` FROM `s_core_customergroups`");
		while($customerGrpData = mysql_fetch_array($getCustomerGrpsQ))
		{
			$this->sCustomerGrps[] = $customerGrpData;
			$this->sCustomerGrpsById[$customerGrpData['groupkey']] = $customerGrpData;
		}
	}

	/**
	* Speichert die Daten, wenn das Formular abgeschickt worden ist
	**/
	public function sSaveAction()
	{
		//Fehlerüberprüfung
		if(trim($_POST['sBundleName']) == "")
			$this->sAddErrorField("sBundleName");

		//Values übernehmen
		foreach ($_POST as $key=>$post)
		{
			$key = mysql_escape_string($key);
			$post = mysql_escape_string($post);
			$this->sValues[$key] = $post;
		}

		//Bei einem Fehler
		if(!empty($this->sErrorFields))
		{
			return false;
		}

		//Datenermittlung
		$sSaveValues = array();
		$sBundleActive = "on" == $this->sValues['sBundleActive'] ? 1 : 0;
		$max_quantity_enable = "on" == $this->sValues['sExtMaxQuantityCheck'] ? 1 : 0;
		$valid_from = $this->sValues['sExtValidFrom'];
		$valid_to = $this->sValues['sExtValidTo'];

		$rabordernumber = trim($this->sValues['sExtRabOrdernumber']);
		if(empty($rabordernumber))
		{
			$rabordernumber = $this->sGetRabOrdernumber();
		}

		$sBundleCustomerGrps = array();
		foreach($this->sValues as $key=>$value)
		{
			if("groupkey" == substr($key, 0, 8)) $sBundleCustomerGrps[] = substr($key, 9);
		}
		if(!empty($sBundleCustomerGrps)) $sBundleCustomerGrps = implode(",", $sBundleCustomerGrps);

		//SPEICHERPROZESS
		if("new" == $this->sModus)
		{
			//Speichern der Stammdaten
			$sInsertSQL = sprintf("INSERT INTO `s_articles_bundles` ( `articleID` , `name` , `active` ,`datum` ,`customergroups`, `rab_type`, `taxID`,
							`ordernumber`, `max_quantity_enable`, `max_quantity`, `valid_from`, `valid_to`)
							VALUES ('%s', '%s', '%s', NOW(), '%s', '%s', '%s', '%s', '%s', '%s'", $this->sArticleID, $this->sValues['sBundleName'], $sBundleActive, $sBundleCustomerGrps,
							$this->sValues['sRabTypeValue'], $this->sValues['sTaxValue'], $rabordernumber, $max_quantity_enable,
							$this->sValues['sMaxQuantityValue']);
			$sInsertSQL.= ", STR_TO_DATE('{$valid_from}', '%d.%m.%Y'), STR_TO_DATE('{$valid_to}', '%d.%m.%Y'))";

			if(!mysql_query($sInsertSQL)) die("SQL-Statement fehlerhaft!<br><i>".$sInsertSQL."</i>");
			$this->sBundleID = mysql_insert_id();
			if(empty($this->sBundleID)) die("Error: mysql_insert_id is null.<br>SQL-Insert-Statement: ".$sInsertSQL);
		}else{

			$sUpdateSQLValid = ",`valid_from` = STR_TO_DATE('{$valid_from}', '%d.%m.%Y')
								,`valid_to` = STR_TO_DATE('{$valid_to}', '%d.%m.%Y')";
			$sUpdateSQL = sprintf("UPDATE `s_articles_bundles`
									SET `name` = '%s'
									,`datum` = NOW()
									,`active` = '%s'
									,`customergroups` = '%s'
									,`rab_type` = '%s'
									,`taxID` = '%s'

									,`ordernumber` = '%s'
									,`max_quantity_enable` = '%s'
									,`max_quantity` = '%s'
									%s
									WHERE `id`='%s' LIMIT 1 ;",
									$this->sValues['sBundleName'], $sBundleActive, $sBundleCustomerGrps,
									$this->sValues['sRabTypeValue'], $this->sValues['sTaxValue'],
									$rabordernumber, $max_quantity_enable,
									$this->sValues['sMaxQuantityValue'],
									$sUpdateSQLValid, $this->sBundleID
									);
			if(!mysql_query($sUpdateSQL)) die("SQL-Statement fehlerhaft!<br><i>".$sUpdateSQL."</i>");
		}

		//Artikelbeschränkung
		$sBundleStint = trim($this->sValues['sBundleStint']);

		//Alle bestehenden Einträge löschen
		$delBS = sprintf("
					DELETE FROM `s_articles_bundles_stint` WHERE `bundleID` = %s
				", $this->sBundleID);
		mysql_query($delBS);

		if(!empty($sBundleStint))
		{
			//Artikelnummern splitten
			$sBundleStintItems = explode(";", $sBundleStint);

			if(!empty($sBundleStintItems))
			{
				foreach($sBundleStintItems as $stintItem)
				{
					$stintItem = trim($stintItem);
					//case-insensitive
					$stintItem = strtoupper($stintItem);

					//Überprüfung, ob die Bestellnummer im Artikel
					//verfügbar ist
					if(in_array($stintItem, $this->sArticleOrdernumbers))
					{
						$verfiedStintItems[] = $stintItem;
						$insBS = sprintf("
							INSERT INTO `s_articles_bundles_stint` (
							`bundleID` ,
							`ordernumber`)
							VALUES
							(%s, '%s'
							);",
						$this->sBundleID, $stintItem);
						mysql_query($insBS);
					}
				}
			}
		}

		//Neuladen der Seite
		$sParams = array();
		$sParams['bundleID'] = $this->sBundleID;
		$sParams['articleID'] = $this->sArticleID;
		$sParams['sReloadTree'] = "1";
		$url = sprintf("%s?%s", $_SERVER['PHP_SELF'], http_build_query($sParams));
		header("Location: {$url}");
		exit;
	}

	public function sLoadBundle()
	{
		//Bearbeitungsmodus
		if("edit" == $this->sModus)
		{
			$getBundleDataQ = mysql_query("
				SELECT *,
				DATE_FORMAT(valid_from,'%d.%m.%Y') AS valid_from_f,
				DATE_FORMAT(valid_to,'%d.%m.%Y') AS valid_to_f
				FROM `s_articles_bundles`
				WHERE `id`='{$this->sBundleID}' LIMIT 1");
			$getBundleData = mysql_fetch_assoc($getBundleDataQ);
			$this->sBundleData = $getBundleData;

			$this->sValues['sBundleName'] = $getBundleData['name'];
			$this->sValues['sBundleActive'] = $getBundleData['active'];
			$this->sValues['sTaxValue'] = $getBundleData['taxID'];
			$this->sValues['sRabTypeValue'] = $getBundleData['rab_type'];
			$this->sValues['sValidFrom'] = $getBundleData['valid_from_f'];
			$this->sValues['sValidTo'] = $getBundleData['valid_to_f'];
			$this->sValues['sRabOrdernumber'] = $getBundleData['ordernumber'];
			$this->sValues['sMaxQuantityCheck'] = 1==$getBundleData['max_quantity_enable'] ? true : false;
			$this->sValues['sMaxQuantity'] = !empty($getBundleData['max_quantity']) ? $getBundleData['max_quantity'] : 0;
			if(!empty($getBundleData['customergroups']))
			{
				$customergroupsArr = explode(",", $getBundleData['customergroups']);
				foreach($customergroupsArr as $cGrp)
				{
					$key = sprintf("groupkey_%s", $cGrp);
					$this->sValues[$key] = "on";
				}
			}

			//Artikelbeschränkung laden
			$sqlBS = sprintf("
				SELECT
				GROUP_CONCAT(ordernumber SEPARATOR ';') AS ordernumbers
				FROM `s_articles_bundles_stint`
				WHERE `bundleID` = %s
			", $this->sBundleID);
			$sqlBSQuery = mysql_query($sqlBS);
			if(mysql_num_rows($sqlBSQuery) != 0)
			{
				$this->sValues['sBundleStint'] = mysql_result($sqlBSQuery, 0, 'ordernumbers');
			}
		}
	}

	private function sAddErrorField($field)
	{
		if(empty($this->sErrorFields)) $this->sErrorFields = array();
		$this->sErrorFields[] = $field;
	}

	public function sReloadTreeRef()
	{
		if(isset($_GET['sReloadTree']))
		{
			echo "<script type='text/javascript'>";
				echo "try{";
					echo "parent.myExt.reload();";
				echo "}catch(e){}";
			echo "</script>";
		}
	}

	public function sLoadCGRP()
	{
		$sql = sprintf("
			SELECT * FROM `s_articles_bundles_prices` WHERE `bundleID` = %s",
		$this->sBundleID);
		$getPricesQ = mysql_query($sql);
		if(mysql_num_rows($getPricesQ) != 0)
		{
			$this->sValuesPrices = array();
			while($fetch = mysql_fetch_assoc($getPricesQ))
			{
				$this->sValuesPrices[$fetch['customergroup']] = number_format($fetch['price'], 2, ".", "");
			}
		}
	}

	public function sSaveCGRP()
	{
		foreach($_POST as $key=>$post)
		{
			if("cGrp" == substr($key, 0, 4))
			{
				$groupkey = substr($key, 5);
				$price = mysql_real_escape_string($_POST[$key]);

				//Nettowert berechnen bei Brutto-Kundengruppen
				/* 2do
				if(1 == $this->sCustomerGrpsById[$groupkey]['taxinput'])
				{
					$price =
				}
				*/

				//Insert
				$sql = sprintf("
					REPLACE INTO `s_articles_bundles_prices` (
					`bundleID` ,
					`customergroup` ,
					`price`
					)
					VALUES (
					%s, '%s', '%s'
					);
				", $this->sBundleID, $groupkey, $price);
				if(!mysql_query($sql))
				{
					echo "SQL-Statement konnte nicht ausgeführt werden:<br>";
					die("<b>".$sql."</b>");
				}
			}
		}
	}

	/**
	* Generiert eine Bestellnummer für den Rabattartikel
	*/
	public function sGetRabOrdernumber()
	{
		$created=false;
		while(!$created)
		{
			srand ((double) microtime( )*1000000);
			$random_number = rand( );
			$ordernumber = sprintf("BUNDLE_%s", $random_number);


			//Überprüfung, ob die Bestellnummer noch nicht benutzt wird
			$checkDetails = sprintf("
				SELECT *
				FROM `s_articles_details`
				WHERE `ordernumber` LIKE '%s'
			", $ordernumber);
			$checkDetailsQ = mysql_query($checkDetails);


					$checkGrpVal = sprintf("
				SELECT *
				FROM `s_articles_groups_value`
				WHERE `ordernumber` LIKE '%s'
			", $ordernumber);
			$checkGrpValQ = mysql_query($checkGrpVal);


			$checkBundle = sprintf("
				SELECT *
				FROM `s_articles_bundles`
				WHERE `ordernumber` LIKE '%s'
			", $ordernumber);
			$checkBundleQ = mysql_query($checkBundle);

			if(mysql_num_rows($checkDetailsQ) == 0 && mysql_num_rows($checkGrpValQ) == 0 && mysql_num_rows($checkBundleQ) == 0)
				$created=true;
		}

		return $ordernumber;
	}

	/**
	* Überprüft, ob Konflikte vorhanden sind
	* die eine Ausgabe des Bundles auf Storefrontseite
	* verhindert
	**/
	public function sDetectConflicts()
	{
		$conflicts = array();

		/*
		echo "<pre>";
		print_r($this->sBundleData);
		print_r($this->sValues);
		print_r($this->sValuesPrices);
		echo "</pre>";
		*/

		//Überprüfung, ob der Bundleartikel aktiv ist
		if($this->sBundleData['active'] != 1)
			$conflicts[] = sprintf("Der Bundleartikel ist inaktiv.");

		//Überprüfung, ob der Bundleartikel im Gültigkeitsbereich liegt
		$today = date("Y-m-d", time());
		if($this->sBundleData['valid_from'] != "0000-00-00")
		{
			if($today < $this->sBundleData['valid_from'])
				$conflicts[] = sprintf("Der Bundleartikel befindet sich noch nicht im Gütigkeitszeitraum.");
		}
		if($this->sBundleData['valid_to'] != "0000-00-00")
		{
			if($today > $this->sBundleData['valid_to'])
				$conflicts[] = sprintf("Der Gütigkeitszeitraum für den Bundleartikel ist abgelaufen.");
		}

		//Überprüfung, ob für die aktiven Preisgruppen
		//ein Preis hinterlegt wurde
		//++
		//Überprüfung, ob ein Prozentwert über 99 angegeben wurde
		$ActiveCustomergroups = explode(",", $this->sBundleData['customergroups']);
		if(!empty($ActiveCustomergroups)){
			foreach($ActiveCustomergroups as $cgrp)
			{
				$tmpPrice = str_replace(".", "", $this->sValuesPrices[$cgrp]);
				$tmpPrice = intval($tmpPrice);
				if(empty($tmpPrice))
					$conflicts[] = sprintf("Für die Kundengruppe <b>%s</b> wurde noch kein Bundlepreis bzw. -rabatt definiert.", $cgrp);

				if("pro" == $this->sBundleData['rab_type'] && $this->sValuesPrices[$cgrp]>=100)
					$conflicts[] = sprintf("Für die Kundengruppe <b>%s</b> wurde ein ungültiger Prozentwert hinterlegt.", $cgrp);
			}
		}

		//Abverkaufbundle
		if(1 == $this->sBundleData['max_quantity_enable'] && 1 == $this->sBundleData['max_quantity'] <= 0)
		{
			$conflicts[] = "Der Bundleartikel ist ausverkauft. (<i>siehe: Bundlestückzahl begrenzen</i>).";
		}

		//Überprüfung, ob Bundleartikel ausverkauft sind
		//Eindimensionelle und Variantenartikel
		$soldoutArticle = array();
		$checkDetails = sprintf("
			SELECT
			ba.ordernumber, ad.instock, a.active
			FROM `s_articles_bundles_articles` AS ba

			LEFT JOIN `s_articles_details` AS ad
			ON(ad.ordernumber = ba.ordernumber)

			LEFT JOIN `s_articles` AS a
			ON(a.id = ad.articleID)

			WHERE ba.`bundleID` = %d
			AND
			(
				(
					a.laststock = 1
					AND ad.instock <= 0
				)OR(
					a.active=0
				)
			)
		", $this->sBundleID);
		$checkDetailsQ = mysql_query($checkDetails);
		if(mysql_num_rows($checkDetailsQ) != 0)
		{
			while($fetchD = mysql_fetch_assoc($checkDetailsQ))
			{
				$tmpSoldoutArticle = array();
				$tmpSoldoutArticle[ordernumber] = $fetchD['ordernumber'];
				$tmpSoldoutArticle['instock'] = $fetchD['instock'];
				$tmpSoldoutArticle['active'] = $fetchD['active'];
				$soldoutArticle[] = $tmpSoldoutArticle;
			}
		}

		//Konfiguratorartikel
		$checkConf = sprintf("
			SELECT
			ags.instock as laststock,
			agv.instock,
			ba.ordernumber,
			a.active
			FROM `s_articles_bundles_articles` AS ba

			LEFT JOIN `s_articles_groups_value` AS agv
			ON(agv.ordernumber = ba.ordernumber)

			LEFT JOIN `s_articles_groups_settings` AS ags
			ON(ags.articleID = agv.articleID)

			LEFT JOIN `s_articles` AS a
			ON(a.id = agv.articleID)

			WHERE ba.`bundleID` = %d
			AND
			(
				(
					ags.instock = 1
					AND agv.instock <= 0
				)OR(
					a.active=0
				)
			)
		", $this->sBundleID);
		$checkConfQ = mysql_query($checkConf);
		if(mysql_num_rows($checkConfQ) != 0)
		{
			while($fetchC = mysql_fetch_assoc($checkConfQ))
			{
				$tmpSoldoutArticle = array();
				$tmpSoldoutArticle[ordernumber] = $fetchC['ordernumber'];
				$tmpSoldoutArticle['instock'] = $fetchC['instock'];
				$tmpSoldoutArticle['active'] = $fetchC['active'];
				$soldoutArticle[] = $tmpSoldoutArticle;
			}
		}

		if(!empty($soldoutArticle))
		{
			$tmpConflict = "Folgende Bundleartikel sind ausverkauft oder inaktiv:";
			$tmpConflict.= "<ul class='ul2'>";
			foreach($soldoutArticle as $soldout)
			{
				if(0 == $soldout['active'])
					$info = "Artikel inaktiv";
				else
					$info = sprintf("Lagerbestand: %d", $soldout['instock']);

				$tmpConflict.= sprintf("<li>- %s (<i>%s</i> )</li>", $soldout['ordernumber'], $info);
			}
			$tmpConflict.= "</ul>";
			$conflicts[] = $tmpConflict;
		}


		return $conflicts;
	}
}
$sBundle = new sBundle();

if("edit" == $sBundle->sModus)
{
	//Translation
	$sCore->sInitTranslations($sBundle->sBundleID,"bundlename");
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="<?php echo $sLang["supplier"]["hersteller_hamann-media"] ?>"/>
<meta name="copyright" content="<?php echo $sLang["supplier"]["hersteller_2007_hamann"] ?>" />
<meta name="company" content="<?php echo $sLang["supplier"]["hersteller_eBusiness"] ?>" />
<meta name="reply-to" content="<?php echo $sLang["supplier"]["hersteller_eMail"] ?>" />
<meta name="rating" content="general" />
<meta http-equiv="content-language" content="de" />

<title>Supplier</title>

</head>
<body>

<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../backend/js/translations.php"></script>
<script type="text/javascript" src="bundle/number_format.js"></script>
<script type="text/javascript" src="bundlestintcombo.js.php"></script>
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons2.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />


<?php
if("edit" == $sBundle->sModus)
{
	//Translation
	include("../../../backend/elements/window/translations.htm");
}
?>


<!--StintCombo - START -->
<script type="text/javascript">
Ext.onReady(function(){
	new de.shopware.BundleCombo({
		renderTo: 'bundleStintDiv'
		,width:300
		,checkboxgroupItems: [
			<?php
			//Setze Checkboxes
			$i=0;
			foreach ($sBundle->sArticleOrdernumbers as $ordernumber){
				if($i!=0) echo ",";$i++;
				echo "{boxLabel: '{$ordernumber}', name: '{$ordernumber}', checked: false, id:'chkb_{$ordernumber}'}";
			}
			?>
		]
	});

	//MwSt. Combobox
	new Ext.form.ComboBox({
		id: 'sExtTax'
		,renderTo: 'sTax'
		,mode: 'local'
		,displayField: 'name'
		,valueField: 'id'
		,triggerAction: 'all'
		,value:'<?php echo !empty($sBundle->sValues['sTaxValue']) ? $sBundle->sValues['sTaxValue'] : 1; ?>'
		,editable: false
		,store: new Ext.data.SimpleStore({fields:[['id'],['name']]})
		,listeners: {'render': function(combobox){
			<?php
			$sqlTax = "SELECT id, description FROM `s_core_tax` ORDER BY `id`";
			$sqlTaxQ = mysql_query($sqlTax);
			while($fetchTax = mysql_fetch_assoc($sqlTaxQ))
			{
				echo sprintf("combobox.store.add(new Ext.data.Record({id:%s, name:'%s'}));",
					$fetchTax['id'],
					$fetchTax['description']);
			}
			?>
			$('sTaxValue').value = combobox.getValue();
		},'select': function(combobox){
			$('sTaxValue').value = combobox.getValue();
		}}
	});

	//Rabatttyp Combobox
	new Ext.form.ComboBox({
		id: 'sExtRabTypeChb'
		,renderTo: 'sRabTypeChb'
		,mode: 'local'
		,displayField: 'name'
		,valueField: 'id'
		,triggerAction: 'all'
		,value:'<?php echo !empty($sBundle->sValues['sRabTypeValue']) ? $sBundle->sValues['sRabTypeValue'] : 'abs'; ?>'
		,editable: false
		,store: new Ext.data.SimpleStore({fields:[['id'],['name']]})
		,listeners: {'render': function(combobox){
			if(combobox.value != 'abs')
			{
				Ext.getCmp('sExtTax').setDisabled(true);
			}
			combobox.store.add(new Ext.data.Record({id:'abs', name:"Absolute"}));
			combobox.store.add(new Ext.data.Record({id:'pro', name:"Prozentual"}));

			$('sRabTypeValue').value = combobox.getValue();
		},'select': function(combobox){
			var value = combobox.getValue();
			if(value != 'pro')
				Ext.getCmp('sExtTax').setDisabled(false);
			else
				Ext.getCmp('sExtTax').setDisabled(true);

			$('sRabTypeValue').value = combobox.getValue();
		}}
	});


	//Bundle-Artikelbezeichnung
	new Ext.form.TextField({
		id: 'sExtBundleName'
		,renderTo: 'sBundleNameRender'
		,allowBlank: false
		,width: 300
		<?php
		if(!empty($sBundle->sValues['sBundleName']))
		{
			echo ",value: '".htmlentities($sBundle->sValues['sBundleName'])."'";
		}
		?>
		,listeners: {'afterrender': function(textfield){
			$('sBundleName').value = textfield.getValue();
		},'change': function(textfield){
			$('sBundleName').value = textfield.getValue();
		}}
	});


	//Rabatt-Artikelnummer
	sExt.sRabOrdernumberValid=true;
	new Ext.form.TextField({
		id: 'sExtRabOrdernumber'
		,renderTo: 'sRabOrdernumber'
		,maskRe: /^[0-9a-zA-Z_]{0,}$/
		,width: 300
		,value: '<?php echo $sBundle->sValues['sRabOrdernumber']; ?>'
		,listeners:{'change': function(textfield){
			if("" != textfield.getValue())
			{
				sExt.sChangeRabOrderCheckState('prozess');
				Ext.Ajax.request({
					url: '../../../backend/ajax/checkOrdernumberUnique.php'
					,success: function(r, op){
						var res = r.responseText;
						if(res == "SUCCESS")
						{
							sExt.sChangeRabOrderCheckState('accept');
						}else{
							sExt.sChangeRabOrderCheckState('cross');
						}
					}
					,failure: function(){
						sExt.sChangeRabOrderCheckState('cross');
						Ext.MessageBox.alert("Ajax Fehler", "Die Datei checkOrdernumberUnique.php kann nicht geladen werden!");
					}
					,params: {ordernumber: textfield.getValue(), own: '<?php echo $sBundle->sValues['sRabOrdernumber']; ?>'}
				});
			}else{
				sExt.sChangeRabOrderCheckState('null');
			}

		}}
	});

	//Max verfügbare Bundles NumberField
	new Ext.form.NumberField({
		id: 'sExtMaxQuantity'
		,renderTo: 'sMaxQuantity'
		,width: 148
		,allowNegative: true
		,allowDecimals: false
		,value: '<?php echo $sBundle->sValues['sMaxQuantity']; ?>'
		,listeners: {'afterrender': function(field){
			$('sMaxQuantityValue').value = field.getValue();
		},'change': function(field){
			$('sMaxQuantityValue').value = field.getValue();
		}}
	});

	//Max verfügbare Bundles Checkbox
	new Ext.form.Checkbox({
		id: 'sExtMaxQuantityCheck'
		,renderTo: 'sMaxQuantityCheck'
		,checked: <?php echo true==$sBundle->sValues['sMaxQuantityCheck'] ? "true" : "false"; ?>
		,listeners: {'render': function(checkbox){
			if(true == checkbox.getValue())
				Ext.getCmp('sExtMaxQuantity').setDisabled(false);
			else
				Ext.getCmp('sExtMaxQuantity').setDisabled(true);
		},'check': function(checkbox){
			if(true == checkbox.getValue())
				Ext.getCmp('sExtMaxQuantity').setDisabled(false);
			else
				Ext.getCmp('sExtMaxQuantity').setDisabled(true);
		}}
	});

	//Gültig von DateField
	new Ext.form.DateField({
		id: 'sExtValidFrom'
		,renderTo: 'sValidFrom'
		,width: 166
		,format: 'd.m.Y'
		<?php
		if($sBundle->sValues['sValidFrom'] != "00.00.0000")
		{
			echo ",value: '".$sBundle->sValues['sValidFrom']."'";
		}
		?>
	});

	//Gültig bis DateField
	new Ext.form.DateField({
		id: 'sExtValidTo'
		,renderTo: 'sValidTo'
		,width: 166
		,format: 'd.m.Y'
		<?php
		if($sBundle->sValues['sValidTo'] != "00.00.0000")
		{
			echo ",value: '".$sBundle->sValues['sValidTo']."'";
		}
		?>
	});
});

var sExt = function(){

	var sFormValid;
	var sRabOrdernumberValid;

	return{
		sChangeRabOrderCheckState: function(state)
		{
			switch(state)
			{
				case 'cross':
					$('chkRabOrd_cross').setStyle('display', 'block');
					$('chkRabOrd_accept').setStyle('display', 'none');
					$('chkRabOrd_prozess').setStyle('display', 'none');
					Ext.getCmp('sExtRabOrdernumber').markInvalid();
					this.sRabOrdernumberValid=false;
				break;
				case 'accept':
					$('chkRabOrd_cross').setStyle('display', 'none');
					$('chkRabOrd_accept').setStyle('display', 'block');
					$('chkRabOrd_prozess').setStyle('display', 'none');
					this.sRabOrdernumberValid=true;
				break;
				case 'prozess':
					$('chkRabOrd_cross').setStyle('display', 'none');
					$('chkRabOrd_accept').setStyle('display', 'none');
					$('chkRabOrd_prozess').setStyle('display', 'block');
					this.sRabOrdernumberValid=false;
				break;
				case 'null':
					$('chkRabOrd_cross').setStyle('display', 'none');
					$('chkRabOrd_accept').setStyle('display', 'none');
					$('chkRabOrd_prozess').setStyle('display', 'none');
					this.sRabOrdernumberValid=true;
				break;
			}
		},
		sValidateSubmit: function(){
			this.sFormValid = true

			if(false == this.sRabOrdernumberValid)
			{
				this.sFormValid = false;
			}

			//Überprüfung, ob eine Bezeichnung gesetzt wurde
			if(!Ext.getCmp('sExtBundleName').isValid())
			{
				this.sFormValid = false;
			}

			//Überprüfung, ob mindestens eine Kundengruppe gesetzt wurde
			var oneGroupkeyChecked = false;
			$(document.body).getElements('input[groupkey=1]').each(function(item, index, allItems){
				if(true == item.checked) oneGroupkeyChecked=true;
			});
			if(false == oneGroupkeyChecked)
			{
				this.sFormValid=false;
				$('groupkeyDiv').addClass('groupkeyInvalid');
			}else{
				$('groupkeyDiv').removeClass('groupkeyInvalid');
			}

			return this.sFormValid;
		}
	}
}();


</script>
<!--StintCombo - END -->

<?php
if("edit" == $sBundle->sModus)
{
	echo "<script type='text/javascript' src='bundlegrid.js.php'></script>";
	echo "<script type='text/javascript'>";

	echo "var tmpCGRPS = new Object();";
	//Aktive Kundengruppen fürs Grid ermitteln
	$sBundleCustomerGrps = array();
	$i=0;
	foreach($sBundle->sValues as $key=>$value)
	{
		if("groupkey" == substr($key, 0, 8))
		{
			$taxinput = !empty($sBundle->sCustomerGrpsById[$customerGrpID]['taxinput']) ? "N" : "B";
			$customerGrpID = substr($key, 9);
			$customerGrpName = sprintf("%s (%s)", $sBundle->sCustomerGrpsById[$customerGrpID]['description'], $taxinput);
			echo "tmpCGRPS[{$i}] = new Object();";
			echo "tmpCGRPS[{$i}]['groupkey'] = '{$customerGrpID}';";
			echo "tmpCGRPS[{$i}]['description'] = '{$customerGrpName}';";
			$i++;
		}
	}

	echo "Ext.onReady(function(){";
		echo sprintf("myExt.init({bundleID:%s, customerGrps:%s});", $sBundle->sBundleID, tmpCGRPS);
	echo "});";
	echo "</script>";
}

//Neuladen des TreePanels
$sBundle->sReloadTreeRef();
?>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />


<style type="text/css">
.CGrpCheckbox
{
	text-align:left;
}
input[type=checkbox]
{
	position:relative;
	top:0px;
	left:0px;
	margin-right:5px;
}
/* Fehlerhafte input-Felder */
.invalid
{
	border: 1px solid red;
}
.groupkeyInvalid
{
	border: 1px dotted red;
}
.w400
{
	width:400px;
}
.w300
{
	width:300px;
}
.ul2
{
	margin-left:20px;
}
/* Translation */
#sTranslations
{
	z-index:999999;
}
</style>


<?php
//Ausgabe der Konflikte
if("edit" == $sBundle->sModus)
{
	$conflicts = $sBundle->sDetectConflicts();
	if(!empty($conflicts))
	{
		echo "<div class='error_info' style='margin:0px 5px 30px 5px;'>";
		echo "<strong>Aus folgenden Gründen wird das Bundle nicht bzw. nur teilweise angezeigt:</strong><br>";
		echo "<ul>";
		foreach($conflicts as $conflict)
		{
			echo sprintf("<li>- %s</li>", $conflict);
		}
		echo "</ul>";
		echo "</div>";
	}
}
?>


<?php
if ("new" == $sBundle->sModus || "edit" == $sBundle->sModus){
?>
<fieldset>
	<legend><?php if ("new" == $sBundle->sModus) { echo "Neues Bundle";  } else {  echo "Bundle bearbeiten";  } ?></legend>
	<form name="save" enctype="multipart/form-data" method="post" action="<?php echo sprintf("%s?%s", $_SERVER["PHP_SELF"], http_build_query($_GET)); ?>">
	<input type="hidden" name="sAction" value="1">



	<ul>
	<li>
		<label for="name">Bundle-Artikelbezeichnung:</label>
	</li>
	<li>
		<div style='float:left;'>
			<div id="sBundleNameRender"></div>
			<input type='hidden' name='sBundleName' id='sBundleName'>
			<div id='sBundleName_parent'></div>
		</div>
		<?php
		if("edit" == $sBundle->sModus)
		{
			//Translation
			echo "<div style='float:left;'>";
			echo $sCore->sBuildTranslation("sBundleName","sBundleName",$sBundle->sBundleID,"bundlename");
			echo "</div>";
		}
		?>
	</li>
	<li class="clear"/>
	</ul>




	<ul>
	<li>
		<label for="name">Rabatt-Artikelnummer:</label>
	</li>
	<li>
		<div style='float:left;'>
			<div id="sRabOrdernumber"></div>
			<input type='hidden' name='sRabOrdernumberValue' id='sRabOrdernumberValue'>
		</div>
		<div style='float:left;display:none' id='chkRabOrd_cross'>
			<a class="ico cross"></a>
		</div>
		<div style='float:left;display:none' id='chkRabOrd_accept'>
			<a class="ico accept"></a>
		</div>
		<div style='float:left;display:none' id='chkRabOrd_prozess'>
			<img id="prozess" style="float: left;" src="../../../vendor/ext/resources/images/default/tree/loading.gif"/>
		</div>

	</li>
	<li class="clear"/>
	</ul>




	<ul>
	<li>
		<label for="name">Artikelbeschränkung:</label>
	</li>
	<li>
		<div id="bundleStintDiv"/>
		<input name="sBundleStint" id="sBundleStint" type="hidden" value="<?php echo htmlentities($sBundle->sValues['sBundleStint']); ?>"/>
	</li>
	<li class="clear"/>
	</ul>




	<ul>
	<li>
		<label for="name">Abzug:</label>
	</li>
	<li>
		<div id="sRabTypeChb"></div>
		<input type='hidden' name='sRabTypeValue' id='sRabTypeValue'>
	</li>
	<li class="clear"/>
	</ul>




	<ul>
	<li>
		<label for="name">Bundle MwSt.-Satz:</label>
	</li>
	<li>
		<div id="sTax"></div>
		<input type='hidden' name='sTaxValue' id='sTaxValue'>
	</li>
	<li class="clear"/>
	</ul>




	<ul>
	<li>
		<label for="name">Bundlestückzahl begrenzen:</label>
	</li>
	<li>
		<div style='float:left;'>
			<div id="sMaxQuantityCheck"></div>
			<input type='hidden' name='sMaxQuantityCheckValue' id='sMaxQuantityCheckValue'>
		</div>
		<div style='float:left;'>
			<div id="sMaxQuantity"></div>
			<input type='hidden' name='sMaxQuantityValue' id='sMaxQuantityValue'>
		</div>
		<li class="clear"/>
	</li>
	<li class="clear"/>
	</ul>




	<ul>
	<li>
		<label for="name">Gültig von:</label>
	</li>
	<li>
		<div id="sValidFrom"></div>
		<input type='hidden' name='sValidFromValue' id='sValidFromValue'>
	</li>
	<li class="clear"/>
	</ul>




	<ul>
	<li>
		<label for="name">Gültig bis:</label>
	</li>
	<li>
		<div id="sValidTo"></div>
		<input type='hidden' name='sValidToValue' id='sValidToValue'>
	</li>
	<li class="clear"/>
	</ul>





	<ul>
	<li>
		<label for="name">Aktiv:</label>
	</li>
	<li>
		<input name="sBundleActive" type="checkbox" <?php if(!empty($sBundle->sValues['sBundleActive'])) echo "checked"; ?>
	</li>
	<li class="clear"/>
	</ul>




	<ul>
		<li>
			<label for="name">Verfügbar für die Kundengruppen:</label>
		</li>
		<li>
			<div id="groupkeyDiv" style="width:225px;">
	<?php
	foreach($sBundle->sCustomerGrps as $sCGRP)
	{
		$groupkey = sprintf("groupkey_%s", $sCGRP['groupkey']);
	?>
		<input groupkey=1 name="groupkey_<?php echo $sCGRP['groupkey']; ?>" type="checkbox" <?php if("on" == $sBundle->sValues[$groupkey]) echo "checked"; ?>>
		<label class="CGrpCheckbox"><?php echo sprintf("%s (%s)", $sCGRP['description'], $sCGRP['groupkey']); ?></label>
		<br />
	<?php
	} //End foreach $sCustomerGrp
	?>
			</div>
		</li>
		<li class="clear"/>
	</ul>



	<br /><br />

	<div class="buttons" id="div" style="float: left; width:110px">
      <ul>
      	<li id="buttonTemplate" class="buttonTemplate">
        <button type="submit" value="send" class="button" onclick='return sExt.sValidateSubmit()'>
        <div class="buttonLabel">Speichern</div>
        </button>
       </li>
      </ul>
    </div>



     </li>
	</ul>
</form>
</fieldset>


<?php
if("edit" == $sBundle->sModus)
{
?>
<form name="save" enctype="multipart/form-data" method="post" action="<?php echo sprintf("%s?%s", $_SERVER["PHP_SELF"], http_build_query($_GET)); ?>">
<fieldset style="position:relative;top:12px;">
	<legend>Bundle Preise</legend>
	<div id='bundlePricesDiv' style='display:none;'>

		<table>

			<tr>
			<td>
				<div'><b>Kundengruppe</b></b></div>
			</td>
			<td width='25px'></td>
			<td>
				<div style='text-align:right;'><b>Gesamtwert</b></b></div>
			</td>
			<td width='25px'></td>
			<td>
				<div style='text-align:right;'><b><?php echo "abs"==$sBundle->sValues['sRabTypeValue'] ? "Bundlepreis": "Bundlerabatt (%)"; ?></b></div>
			</td>
			</tr>

			<?php
			foreach($sBundle->sCustomerGrps as $sCGRP)
			{
				$groupkey = sprintf("groupkey_%s", $sCGRP['groupkey']);
				if(!isset($sBundle->sValues[$groupkey])) continue;

				$taxModus = $sCGRP['taxinput'] == 1 ? "Brutto" : "Netto";
			?>
			<tr>
			<td>
				<div><?php echo sprintf("%s (%s):", $sCGRP['description'], $taxModus); ?></div>
			</td>
			<td width='25px'></td>
			<td>
				<div style='text-align:right;' id='cGrp_<?php echo $sCGRP['groupkey']; ?>'>0.00</div>
			</td>
			<td width='40px'></td>
			<td>
				<?php
				$fieldName = sprintf("cGrp_%s", $sCGRP['groupkey']);
				$fieldRenderDiv = $fieldName."_Div";
				?>
				<div id='<?php echo $fieldRenderDiv; ?>'></div>
				<script type='text/javascript'>
				new Ext.form.NumberField({
					id: '<?php echo $fieldName; ?>'
					,renderTo: '<?php echo $fieldRenderDiv; ?>'
					,value: '<?php echo $sBundle->sValuesPrices[$sCGRP['groupkey']] ?>'
					,width:70
					,allowDecimals: true
					<?php
					//Prozentdarstellung
					if("pro"==$sBundle->sValues['sRabTypeValue'])
					{
						echo ",maxValue: 99.99";
					}
					?>
				});
				</script>

				<!--<input name='cGrp_<?php echo $sCGRP['groupkey']; ?>' style='width:50px;' type='text' value='<?php echo $sBundle->sValuesPrices[$sCGRP['groupkey']] ?>' />-->
			</td>
			</tr>
			<?php
			} //End foreach $sCustomerGrp
			?>
		</table>

		<input type="hidden" name="sActionCGRP" />

		<div class="buttons" id="div" style="float: left; width:110px">
	      <ul>
	      	<li id="buttonTemplate" class="buttonTemplate">
	        <button type="submit" value="send" class="button">
	        <div class="buttonLabel">Speichern</div>
	        </button>
	       </li>
	      </ul>
	    </div>
	</div>
<div id='bundlePricesInfoDiv' style='display:none;'>
Fügen Sie dem Bundle mindestens einen Artikel hinzu, um Preise hinterlegen zukönnen .
</div>
</fieldset>
</form>
<?php
}
?>


<!--Bundle Artikel Grid-->
<div id='bundleGridDiv'></div>

<?php
}
?>

</body>
</html>