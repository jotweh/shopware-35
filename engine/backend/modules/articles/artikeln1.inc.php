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

$HTTP_RAW_POST_DATA = file_get_contents("php://input");

if(!empty($HTTP_RAW_POST_DATA)){
	parse_str($HTTP_RAW_POST_DATA,$_POST);
}
// *****************
eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Start"));
?>
<?php
error_reporting(E_ERROR);
if ($_GET["article"]) $_GET["edit"] = $_GET["article"];
 
// =================================================================
// BENÖTIGTE KLASSEN IMPORTIEREN
// =================================================================
include ("class_articles.inc.php");
// =================================================================

// INITIATION
$form = new shopware_forms();
$sw_articles = new shopware_articles();
$clsShopware = new shopware_admin();
// ==========================================================
$form->errorhandlerCLS = $clsShopware;
// ===========================================================
// STANDARD-TEMPLATE LINKS|RECHTS
// ===========================================================
$form->element_template = "
<ul>
<!--<li class=\"clear\"/>-->
<li><label for=\"name\" {FORM_HELP}>{FORM_DESCRIPTION}:</label>
{FORM_ELEMENT}
</li>
<!--<li class=\"clear\"/>-->
</ul>
";
?>
<?php


// ======================================================================================================
// FALLS DATEN ÜBERMITTELT WURDEN, VALIDIEREN WIR DIESE ...
// =======================================================================================================
if (isset($_POST["validate"])){
	
	eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post1"));
	if (!$_REQUEST["variante"]){
	
		// Clearing article description
		$_POST["txtlangbeschreibung"] = stripslashes($_POST["txtlangbeschreibung"]);
		$_POST["txtlangbeschreibung"] = preg_replace('/\r\n|\r|\n/', ' ', $_POST["txtlangbeschreibung"]);
		$_POST["txtlangbeschreibung"] = mysql_real_escape_string($_POST["txtlangbeschreibung"]);
		// Clearout entry/closup ie-additionals
		$_POST["txtlangbeschreibung"] = preg_replace("/<\/?body>/","",$_POST["txtlangbeschreibung"]);
		$_POST["txtlangbeschreibung"] = preg_replace("/<\/?html>/","",$_POST["txtlangbeschreibung"]);
		$_POST["txtlangbeschreibung"] = preg_replace("/<\/?head>/","",$_POST["txtlangbeschreibung"]);
	
		$_POST["txtshortdescription"] = mysql_real_escape_string($_POST["txtshortdescription"]);
	}
		
	// Wurden alle benötigten Felder ausgefüllt?
	
	if ($_POST["txtHersteller"]=="Bitte wählen...") $_POST["txtHersteller"] = "";
	
	$checkDependencies = $form->validate($_POST,$clsShopware);
	$priceCheck = floatval($_POST["priceregulary"]["EK"]["0"]);
	if (empty($priceCheck)){
		$clsShopware->error_box("",$sLang["articles"]["artikeln1_please_enter_price"]);
		$_POST["priceregulary"]["EK"]["0"] = "0";
		unset($checkDependencies);
	}
	
	if (($_REQUEST["article"] || $_REQUEST["variante"]) && $_REQUEST["variante"]!=-1){
			$mode = "edit";	
	} else {
			$mode = "insert";
	}
	// Check if ordernumber is not already set
	if (!empty($_POST["txtbestellnr"])){
		
		if (!$_GET["article"]){
			$checkIfOrdernumberIsUnique = mysql_query("
			SELECT id FROM s_articles_details
			WHERE ordernumber='{$_POST["txtbestellnr"]}'
			");
		} elseif ($_GET["article"] && !$_GET["variante"]) {
			$checkIfOrdernumberIsUnique = mysql_query("
			SELECT id FROM s_articles_details
			WHERE ordernumber='{$_POST["txtbestellnr"]}'
			AND articleID!={$_GET["article"]}
			");
			
		}elseif ($_GET["article"] && $_GET["variante"]) {
			$sql = "
			SELECT id FROM s_articles_details
			WHERE ordernumber='{$_POST["txtbestellnr"]}'
			AND id != '{$_GET["variante"]}'
			";
			$checkIfOrdernumberIsUnique = mysql_query($sql);
		}
		// sth 08.09.2009
		$sCore->sDeletePartialCache("article",$_GET["article"]);
	
		//Konfigurator Artikel//
		$checkIfOrdernumberIsUnique2 = mysql_query("
			SELECT id FROM s_articles_groups_value
			WHERE ordernumber='{$_POST["txtbestellnr"]}'
			");
		//END//
		if (@mysql_num_rows($checkIfOrdernumberIsUnique)||@mysql_num_rows($checkIfOrdernumberIsUnique2)){
				
			$checkDependencies["SUCCESS"] = false;
			$checkDependencies["FAILURE"] = true;
			 $clsShopware->error_box($sLang["articles"]["artikeln1_the_ordernumber"]." \"{$_POST["txtbestellnr"]}\" ".$sLang["articles"]["artikeln1_already_taken"],"");
			
		}
		// Check auf Sonderzeichen
		eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post2"));
		if (preg_match("/^[a-zA-Z0-9-_.ÄäÜüÖöß°# ]+$/", $_POST["txtbestellnr"])==0){
			$checkDependencies["SUCCESS"] = false;
			$checkDependencies["FAILURE"] = true;
			$clsShopware->error_box($sLang["articles"]["artikeln1_the_ordernumber"]." \"{$_POST["txtbestellnr"]}\" "." enthält ungültige Zeichen (erlaubt 0-9 A-Z _ . #)","");
		}
		eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post3"));
		// Falls Auto-Bestellnummern aktiviert sind, Nummernkreis aktualisieren
		if ($sCore->sCONFIG["sBACKENDAUTOORDERNUMBER"] && $mode=="insert"){
				// Get next ordernumber
				$getNumber = mysql_query("
				UPDATE s_order_number SET number=number+1 WHERE name='articleordernumber'
				");
				
		}
	}
	if ($checkDependencies["SUCCESS"]){
				
		// 1.) Hauptdaten eintragen
		// ------------------------
		
		
		// ------------------------
		$insertdate = date("Y-m-d");	// Wann wurde der Artikel hinzugefügt, aktualisiert
		$changetime = date("Y-m-d H:i:s");
		// ----------------------------------------------------------------------------------
		
		// some default values
		if (!$_POST["txtlieferzeit"]) $_POST["txtlieferzeit"] = "0";
		if (!$_POST["txtaktiv"]) $_POST["txtaktiv"] = "0";
		if (!$_POST["txtversandkostenfrei"]) $_POST["txtversandkostenfrei"] = "0";
		if (!$_POST["notification"]) $_POST["notification"] = "0";
		if (!$_POST["txtvariantenkriterium"]["ALT"]) $_POST["txtvariantenkriterium"] = "0";
		
		// Additional fields
		if (!$_POST["txtweight"] || !is_numeric($_POST["txtweight"])) $_POST["txtweight"] = "0";
		$_POST["txtweight"] = str_replace(",",".",$_POST["txtweight"]);
		if (!$_POST["txtlager"] || !is_numeric($_POST["txtlager"])) $_POST["txtlager"] = "0";
		if (!$_POST["txtmindestbestand"] || !is_numeric($_POST["txtmindestbestand"])) $_POST["txtmindestbestand"] = "0";
		if (!$_POST["txtesd"] || !is_numeric($_POST["txtesd"])) $_POST["txtesd"] = "0";
		if (!$_POST["txtfreearticle"] || !is_numeric($_POST["txtfreearticle"])) $_POST["txtfreearticle"] = "0";
		if (!$_POST["toparticle"]) $_POST["toparticle"] = "0";
		if (!$_POST["txtminpurchase"]) $_POST["txtminpurchase"] = "0";
		if (!$_POST["txtmaxpurchase"]) $_POST["txtmaxpurchase"] = "0";
		if (!$_POST["txtpurchasesteps"]) $_POST["txtpurchasesteps"] = "0";
		if (!$_POST["txtpurchaseunit"]) $_POST["txtpurchaseunit"] = "0";
		if (empty($_POST["laststock"])) $_POST["laststock"] = "0";
		$_POST["txtpurchaseunit"] = str_replace(",",".",$_POST["txtpurchaseunit"]);
		if (!$_POST["txtreferenceunit"]) $_POST["txtreferenceunit"] = "0";
		if (!$_POST["txtpackunit"]) $_POST["txtpackunit"] = "";
		$_POST["txtreferenceunit"] = str_replace(",",".",$_POST["txtreferenceunit"]);
		if (!$_POST["txtunit"]) $_POST["txtunit"] = "0";
		$_POST["txtunit"] = str_replace(",",".",$_POST["txtunit"]);
		
		eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post4"));
		// ----------------------------------------------------------------------------------
		
		// Reformarting Date
		$temp_datum = explode(".",$_POST["DV"]);
		$_POST["DV"] = $temp_datum[2]."-".$temp_datum[1]."-".$temp_datum[0];
		#echo "#".$POST["datum"];
		$_POST["datum"] = explode(".",$_POST["datum"]);
		$_POST["datum"] = $_POST["datum"][2]."-".$_POST["datum"][1]."-".$_POST["datum"][0];
		
		#echo "#".$POST["datum"];
		// Close up Article operations
		$_POST["txtArtikel"] = str_replace("\"","&quot;",$_POST["txtArtikel"]);
		$_POST["txtArtikel"] = str_replace(" & "," &amp; ",$_POST["txtArtikel"]);
		
		$_POST["txtArtikel"] = mysql_real_escape_string($_POST["txtArtikel"]);
		
		$_POST["txtkeywords"] = str_replace("\"","&quot;",$_POST["txtkeywords"]);
		$_POST["txtkeywords"] = mysql_real_escape_string($_POST["txtkeywords"]);
		
		$_POST["txtzusatztxt"] = str_replace("\"","&quot;",$_POST["txtzusatztxt"]);
		$_POST["txtzusatztxt"] = str_replace(" & ","&amp;",$_POST["txtzusatztxt"]);
		$_POST["txtzusatztxt"] = mysql_real_escape_string($_POST["txtzusatztxt"]);
		// -----------------------------------------------------------------------------------
		$_POST["txtArtikel"] = strip_tags($_POST["txtArtikel"]);
		$_POST["pseudosales"] = intval($_POST["pseudosales"]);
		eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post5"));
		if (!$_REQUEST["variante"]){
		// Check for supplier
		if ($_POST["txtHersteller"]){
			$_POST["txtHersteller"] = htmlspecialchars($_POST["txtHersteller"]);
			$_POST["txtHersteller"] = mysql_real_escape_string($_POST["txtHersteller"]);
			$querySupplier = mysql_query("
			SELECT id FROM s_articles_supplier
			WHERE name = '{$_POST["txtHersteller"]}'
			");
			if (@mysql_num_rows($querySupplier)){
				$supplierID = mysql_result($querySupplier,0,"id");
			}else {
				$insertSupplier = mysql_query("
				INSERT INTO s_articles_supplier
				(name)
				VALUES ('{$_POST["txtHersteller"]}')
				");
				$supplierID = mysql_insert_id();
			}
		}
		
			$_POST["txtpackunit"] = trim($_POST["txtpackunit"]);
		
			switch ($mode){
    			case "insert":
	    				$insertArticleMainSQL = "
			    		INSERT INTO s_articles (supplierID,name,description, description_long,datum,shippingtime
			    		,active,shippingfree,notification,releasedate,variantID, taxID, pseudosales,topseller, free, keywords, minpurchase, purchasesteps, maxpurchase, 
			    		purchaseunit, referenceunit, packunit, unitID, changetime,pricegroupID,pricegroupActive,filtergroupID, laststock,template)
			    		VALUES (
			    		".$supplierID.",
			    		\"".$_POST["txtArtikel"]."\",
			    		\"".$_POST["txtshortdescription"]."\",
			    		\"".$_POST["txtlangbeschreibung"]."\",
			    		\"{$_POST["datum"]}\",
			    		\"".$_POST["txtlieferzeit"]."\",
			    		".$_POST["txtaktiv"].",
			    		".$_POST["txtversandkostenfrei"].",
			    		".$_POST["notification"].",
			    		\"".$_POST["DV"]."\",
		    			".$_POST["txtvariantenkriterium"]["ALT"].",
		    			{$_POST["txtmwst"]},
		    			'{$_POST["pseudosales"]}',
	    				{$_POST["toparticle"]},
		    			{$_POST["txtfreearticle"]},
		    			\"{$_POST["txtkeywords"]}\",
		    			{$_POST["txtminpurchase"]},
		    			{$_POST["txtpurchasesteps"]},
		    			{$_POST["txtmaxpurchase"]},
		    			{$_POST["txtpurchaseunit"]},
		    			{$_POST["txtreferenceunit"]},
		    			\"{$_POST["txtpackunit"]}\",
		    			{$_POST["txtunit"]},
		    			'$changetime',
		    			'{$_POST["selectPricegroup"]}',
		    			'{$_POST["checkPricegroup"]}',
		    			'{$_POST["selectFilterGroup"]}',
		    			{$_POST["laststock"]},
		    			'{$_POST["selectTemplate"]}'
			    		)
			    		";
	    				eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post6"));	
		    			$insertArticleMain = mysql_query($insertArticleMainSQL);
		    			$insertid = mysql_insert_id();
		    			
    				break;
    			case "edit":
	    				$sql = "
	    				UPDATE s_articles SET supplierID=\"".$supplierID."\",
	    				name=\"".$_POST["txtArtikel"]."\",
	    				description=\"".$_POST["txtshortdescription"]."\",
	    				description_long=\"".$_POST["txtlangbeschreibung"]."\",
	    				shippingtime=\"".$_POST["txtlieferzeit"]."\",
	    				shippingfree=".$_POST["txtversandkostenfrei"].",
	    				notification=".$_POST["notification"].",
						active=".$_POST["txtaktiv"].",
	    				releasedate=\"".$_POST["DV"]."\",
	    				variantID=".$_POST["txtvariantenkriterium"]["ALT"].",
	    				taxID={$_POST["txtmwst"]},
	    				pseudosales='{$_POST["pseudosales"]}',
	    				datum='{$_POST["datum"]}',
	    				topseller={$_POST["toparticle"]},
	    				free={$_POST["txtfreearticle"]},
	    				keywords=\"{$_POST["txtkeywords"]}\",
	    				minpurchase={$_POST["txtminpurchase"]},
	    				purchasesteps={$_POST["txtpurchasesteps"]},
	    				maxpurchase={$_POST["txtmaxpurchase"]},
	    				purchaseunit={$_POST["txtpurchaseunit"]},
	    				referenceunit={$_POST["txtreferenceunit"]},
	    				packunit=\"{$_POST["txtpackunit"]}\",
	    				unitID={$_POST["txtunit"]},
	    				changetime='$changetime',
	    				pricegroupID='{$_POST["selectPricegroup"]}',
	    				pricegroupActive='{$_POST["checkPricegroup"]}',
	    				filtergroupID = '{$_POST["selectFilterGroup"]}',
	    				laststock = '{$_POST["laststock"]}',
	    				template = '{$_POST["selectTemplate"]}'
	    				WHERE id={$_GET["edit"]}
	    				";
	    				eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post7"));
	    				$insertArticleMain = mysql_query($sql);
	    				$insertid = $_GET["edit"];
    				break;	
    		} // End of Switch/Mode
    		
    		if (empty($_POST["enableGroups"]) && !empty($_POST["enableGroupsHidden"]) && !empty($insertid)){
    			$_POST["enableGroups"][] = 0;
    		}
    		if (!empty($_POST["enableGroups"]) && !empty($insertid)){
    			/*foreach ($_POST["enableGroups"] as $scID){
    				
    			}*/
    			$sqlAvoid = "SELECT id FROM s_core_customergroups WHERE id NOT IN (".implode(",",$_POST["enableGroups"]).")";
    			
    			$querySC = mysql_query($sqlAvoid);
    			$deletePreviousSC = mysql_query("
    			DELETE FROM s_articles_avoid_customergroups WHERE articleID = $insertid
    			");
    			while ($insertSC = mysql_fetch_assoc($querySC)){
    				$sqlInsert = "
    				INSERT INTO s_articles_avoid_customergroups (articleID,customergroupID)
    				VALUES (
    				$insertid,
    				{$insertSC["id"]}
    				)
    				";
    				
    				$insertSCRow = mysql_query($sqlInsert);
    			}
    		}
    		
    		if(!empty($_REQUEST['sCategories']))
    		{
    			function sGetDeepCategories($categoryIDs)
				{
					$categories = array();
					foreach ($categoryIDs as $categoryID)
					{
						$categoryID = (int) $categoryID;
						$parentID = $categoryID;
						while ($categoryID!=1 && !empty($categoryID))
						{
							$categories[] = $categoryID;
							$sql = 'SELECT parent FROM s_categories WHERE id='.$categoryID;
							$result = mysql_query($sql);
							if($result && mysql_num_rows($result))
								$tmp = mysql_result($result, 0, 0);
							else
								break;
							$parentID = $categoryID;
							$categoryID = (int) $tmp;
						}
					}
					$categories = array_unique($categories);
					return $categories;
				}
				
				function sSetArticleCategories($articleID, $categories)
				{
					$articleID = (int) $articleID;
					$categories = sGetDeepCategories($categories);
					$sql = "
						INSERT INTO s_articles_categories (articleID, categoryID, categoryparentID)
						
						SELECT $articleID as articleID, c.id as categoryID,
							IF((SELECT 1 FROM `s_categories` WHERE parent=c.id LIMIT 1),c.parent, c.id) as categoryparentID
						FROM `s_categories` c
						WHERE c.id IN (".implode(',', $categories).")
						
						ON DUPLICATE KEY UPDATE categoryparentID=VALUES(categoryparentID)
					";
					$result = mysql_query($sql);
					$sql = "
						DELETE FROM s_articles_categories
						WHERE articleID=$articleID 
						AND categoryID NOT IN (".implode(',', $categories).")
					";
					$result = mysql_query($sql);
				}
				sSetArticleCategories($insertid, $_REQUEST['sCategories']);
    		}
    		
		}else { 
			 // Nur bei Nicht - Varianten
			$insertid = $_REQUEST["article"];
			$insertArticleMain = true;
			
		}
    		// DO OCCUR PROBLEMS WHILE INSERTING OR UPDATING THE DATABASE?
			if (!$insertArticleMain || !$insertid){
				$hinweis = "<b>".$sLang["articles"]["artikeln1_error_cant_save_article"]."</b><br>".$sql;
				$hinweis .= mysql_error();	
				$checkDependencies["FAILURE"] = $hinweis;
				$clsShopware->error_box($hinweis,"MySQL-Error");
			}else {
					switch ($mode){
						case "insert":	
		    				
						if (!$_REQUEST["variante"]){
							$kind = 1;
						}else {
							$kind = 2;
						}
							$sql1 = "
		    				INSERT INTO s_articles_details (id,articleID,ordernumber,suppliernumber,kind,
		    				additionaltext,impressions,sales,active, instock, stockmin, esd, weight
		    				) VALUES
		    				(
		    				'',
		    				$insertid,
		    				'".$_POST["txtbestellnr"]."',
		    				'".$_POST["txtherstellernr"]."',
		    				$kind,
		    				'".$_POST["txtzusatztxt"]."',
		    				0,
		    				0,
		    				{$_POST["txtaktiv"]},
		    				{$_POST["txtlager"]},
		    				{$_POST["txtmindestbestand"]},
		    				{$_POST["txtesd"]},
		    				{$_POST["txtweight"]}
		    				)
		    				";
							eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post8"));
    					break;
    					case "edit":
    						if (!$select_main_aktiv) $select_main_aktiv=1;
	    					
    						if (!$_REQUEST["variante"]){
	    						$sql1 = "
		    					UPDATE s_articles_details
		    					SET
		    					ordernumber='".$_POST["txtbestellnr"]."',
		    					suppliernumber='".$_POST["txtherstellernr"]."',
		    					kind=1,
		    					additionaltext='".$_POST["txtzusatztxt"]."',
		    					active={$_POST["txtaktiv"]},
		    					instock={$_POST["txtlager"]},
		    					stockmin={$_POST["txtmindestbestand"]},
		    					esd={$_POST["txtesd"]},
		    					weight={$_POST["txtweight"]}
		    					WHERE articleID={$_GET["article"]} AND kind=1
	    						";
	    						eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post9"));
    						}else {
    							$sql1 = "
		    					UPDATE s_articles_details
		    					SET
		    					ordernumber='".$_POST["txtbestellnr"]."',
		    					suppliernumber='".$_POST["txtherstellernr"]."',
		    					kind=2,
		    					additionaltext='".$_POST["txtzusatztxt"]."',
		    					active={$_POST["txtaktiv"]},
		    					instock={$_POST["txtlager"]},
		    					stockmin={$_POST["txtmindestbestand"]},
		    					esd={$_POST["txtesd"]},
		    					weight={$_POST["txtweight"]}
		    					WHERE id = {$_REQUEST["variante"]}
	    						";
    							eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post10"));
    						}
    					break;
					}
		    			
		    				
    				$insertArticleSub = mysql_query($sql1);
    				
    				// Konnte der Subartikel erfolgreich angelegt werden???
    				if (!$insertArticleSub){
    					$hinweis = "<b>".$sLang["articles"]["artikeln1_error_cant_save_subarticle"]."</b><br>".$sql1;
						$hinweis .= mysql_error();
						$checkDependencies["FAILURE"] = $hinweis;
						$clsShopware->error_box($hinweis,"MySQL-Fehler");	
    				}else {
    					// HINZUFÜGEN VON PREISEN ZUM HAUPTARTIKEL
    					// -----------------------------------------------------
	    					if ($mode!="edit"){
								$insertid_sub = mysql_insert_id(); // contains the id-referer to subarticle
								if ($_REQUEST["variante"]==-1){
									$_REQUEST["variante"] = $insertid_sub;
								}
	    					}else {
	    						
	    						if ($_REQUEST["variante"]){
	    							$insertid_sub = $_REQUEST["variante"];
	    						}else {
		    						$abfrage_insertID = mysql_query("
		    						SELECT id FROM s_articles_details WHERE articleID={$_GET["edit"]} AND kind=1
		    						");	
		    						$insertid_sub = mysql_result($abfrage_insertID,0,"id");
	    						}
	    						
	    					}
	    					
	    					// LÖSCHEN DER BESTEHENDEN INFORMATIONEN ZUM ARTIKEL ....
							$deleteArticlePrices = mysql_query("
							DELETE FROM s_articles_prices WHERE articledetailsID=$insertid_sub AND pricegroup NOT LIKE 'PG%'
							");
							
							
							// Gruppen durchgehen
							if (!$_REQUEST["variante"]){
								$mwstChoosen = $_POST["txtmwst"];
							}else {
								$sql = "
								SELECT s_core_tax.id AS tax FROM s_articles, s_core_tax WHERE
								s_articles.taxID=s_core_tax.id AND s_articles.id={$_GET["article"]}
								";
								//echo $sql;
								$queryTax = mysql_query($sql);
								//echo mysql_error();
								$mwstChoosen = mysql_result($queryTax,0,"tax");
								//echo $mwstChoosen;
							}
							
							
							foreach ($_POST["priceregulary"] as $ckey => $cvalue){
								foreach ($cvalue as $priceKey => $price){
									if (empty($price) && $priceKey != 0){
										// Found empty price 
										$_POST["bis"][$ckey][$priceKey-1] = "beliebig";
										unset($_POST["priceregulary"][$ckey][$priceKey]);
										
									}
								}
								if (empty($cvalue)){
									die($ckey);
									
								}
							}
							
							$priceregulary = $_POST["priceregulary"];
							
							
							foreach ($priceregulary as $group => $array){
								// group => considers current group-key
								// array => row / price
								
								// Rows durchlaufen
								foreach ($array as $row => $nVK){
									//nVK enthält regulären Verkaufspreis
									
									// nsVK enthält Pseudo-Verkaufspreis
									$nsVK = $_POST["pricepseudo"][$group][$row];
									$nEK = $_POST["priceEK"][$group][$row];
									
									$nVK = str_replace(",",".",$nVK);
									$nsVK = str_replace(",",".",$nsVK);
									$nEK = str_replace(",",".",$nEK);
									
									$percent = $_POST["percent"][$group][$row];
									$percent = str_replace(",",".",$percent);;
									
									// Wenn der Brutto-Preis übergeben wurde,
									// berechnen wir dynamisch den Netto-Preis
									//print_r($_POST);
									if ($_POST["tax$group"]=="brutto"){
										// Ust.Steuer abziehen
										$nVK = round($nVK/(100+return_tax($mwstChoosen))*100,10);
										$nsVK = round($nsVK/(100+return_tax($mwstChoosen))*100,10);
									}
									// Staffel Start
									$von = $_POST["von"][$group][$row];
									// Staffel Ende
									$bis = $_POST["bis"][$group][$row];
									
									// Building query
								if ($nVK){	// Ignore 0 prices
									$pricequery = "
									INSERT INTO s_articles_prices
									(pricegroup, `from`, `to`, articleID, articledetailsID, price, pseudoprice, baseprice, percent)
									VALUES ('$group', '$von', '$bis', $insertid, $insertid_sub, '$nVK', '$nsVK','$nEK','$percent')
									";
									eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post11"));
									//echo $pricequery."<br>";
									$priceExecute = mysql_query($pricequery);
									if (!$priceExecute){
										$hinweis = $sLang["articles"]["artikeln1_error_cant_add_article_price"];
										$hinweis .= mysql_error();
										$hinweis .= $pricequery;	
										$checkDependencies["FAILURE"] = $hinweis;
										$clsShopware->error_box($hinweis,"MySQL-Fehler");
										// Set default row -
										$pricequery = "
										INSERT INTO s_articles_prices
										(pricegroup, `from`, `to`, articleID, articledetailsID, price, pseudoprice, baseprice, percent)
										VALUES ('EK', '1', 'beliebig', $insertid, $insertid_sub, '500', '0','0','0')
										";
										eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post12"));
										//echo $pricequery."<br>";
										$priceExecute = mysql_query($pricequery);
										$priceError = 1;
									
									} // !PriceExecute
								}
								} // Für jede Staffel
								
							} // Für jede Gruppe
							
							
							if (!$priceError){
							// HIER ATTRIBUTE EINFÜGEN
							// Setting Attributes ...
							// =================================================================
							$sqlHeader = "
							INSERT INTO s_articles_attributes(articleID,articledetailsID%attributes%)
							";
							$sqlData = "
							VALUES ($insertid,$insertid_sub%data%)
							";
							//print_r($_POST);
							foreach ($_POST["attr"] as $attribut => $v){
								if ($v){ // is set
									$v = mysql_real_escape_string($v);			
									//$v = htmlentities($v);			
									// Eigentlich bräuchten wir Metainformationen über
									// die Struktur der Spalten
									$getFieldConfiguration = mysql_query("
									SHOW FULL COLUMNS FROM s_articles_attributes
									");
									
									$FieldConfiguration = mysql_result($getFieldConfiguration,$attribut+2,"Type");
									if (preg_match("/int/",$FieldConfiguration)){
										$type = "int";	
										$data[] = $v;
									}else {
										$type = "char";
										
										$data[] = "'$v'";
									}
									$attributes[] = "attr".$attribut;
									
									
									
									
								} // there is data
							} // for every attribute
							
								$attributes = @implode($attributes,",");
								$data = @implode($data,",");
								if ($attributes){
									$attributes = ",".$attributes;	
								}
								if ($data){
									$data = ",".$data;	
								}
								$sqlHeader = preg_replace("/%attributes%/",$attributes,$sqlHeader);
								$sqlData = preg_replace("/%data%/",$data,$sqlData);
								$sqlAttributes = $sqlHeader.$sqlData;
								// Die Attribute in die Datenbank packen ...
								$deleteAttributes = mysql_query("
								DELETE FROM s_articles_attributes WHERE articledetailsID=$insertid_sub
								");
								
								$executeAttributes = mysql_query($sqlAttributes);
								if (!$executeAttributes){
									$hinweis = $sLang["articles"]["artikeln1_error_cant_add_Attributes"];
									$hinweis .= mysql_error();
									$hinweis .= $sqlAttributes;
									$checkDependencies["FAILURE"] = $hinweis;	
								}else {
									unset ($_POST);
									$edit = $insertid;
								}
							
							// =================================================================
		
		    				// Switch zum zweiten Artikelschritt
		    				// Artikel wurde erzeugt... Weiter zur Kategorie-Auswahl...
		    					if ($executeAttributes){
			    					switch ($mode){
			    					case "insert":
			    						//echo "INSERT OKAY";
			    						$_GET["edit"] = $insertid;
			    						$_GET["article"] = $insertid;
			    						break;
			    					} // Switch Mode
		    					} // If//Abfrage
		    				// -----------------------------------------------------
							} // Wenn kein Fehler bei der Preiseingabe passiert ist
				// .......................................................
    				} // Subdaten, Bedingung
			// ----------------------------------------------------------------
			} // Hauptdaten, Bedingung
		
		
		
	}else {
		$_POST["txtlangbeschreibung"] = stripslashes($_POST["txtlangbeschreibung"]);
	}
} // Form-Submit

// =======================================================================================================
// --> A R T I K E L  E D I T I E R E N <--
// =======================================================================================================
// Pass between edit/erben
if (isset($_GET["edit"])) $edit = $_GET["edit"]!=0 ? $_GET["edit"] : 0;
if (isset($_POST["edit"])) $edit = $_POST["edit"]!=0 ? $_POST["edit"] : 0;

if ($edit && $_REQUEST["variante"]!=-1){

	if (!$_REQUEST["variante"]){
		$queryArticleData = mysql_query("
		SELECT * FROM s_articles, s_articles_details WHERE s_articles.id=$edit AND s_articles_details.articleID=s_articles.id
		AND s_articles_details.kind=1
		");
	}else {
		$queryArticleData = mysql_query("
		SELECT * FROM s_articles, s_articles_details WHERE s_articles.id=$edit AND s_articles_details.articleID=s_articles.id
		AND s_articles_details.id={$_REQUEST["variante"]}
		");
	}
	
	if (!@mysql_num_rows($queryArticleData)){
		// Fehler-Handling
	  	#$hinweis = "<b>Fehler: Artikel konnte nicht angelegt werden!</b><br>";
	  	$hinweis .= mysql_error();	
	  	$clsShopware->error_box($hinweis,$sLang["articles"]["artikeln1_error_cant_find_article"]);
	  	exit;
	}else {
		$_SHOPWARE["EDIT"] = mysql_fetch_array($queryArticleData);
		if ($_SHOPWARE["EDIT"]["mode"]!=0){
			die("Blog-Artikel können nicht im Artikel-Modus bearbeitet werden!");
		}
		eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post13"));
		$_SHOPWARE["EDIT"]["description_long"] = nl2br($_SHOPWARE["EDIT"]["description_long"]);
		
		// Query Supplier
		$getSupplier = mysql_query("SELECT name FROM s_articles_supplier
		WHERE id = {$_SHOPWARE["EDIT"]["supplierID"]}
		");
		$_SHOPWARE["EDIT"]["supplierID"] = @mysql_result($getSupplier,0,"name");
		
		if ($_SHOPWARE["EDIT"]["releasedate"]){
			$datum = $_SHOPWARE["EDIT"]["releasedate"];
			$datum = explode("-",$datum);
			$datum = $datum[2].".".$datum[1].".".$datum[0];
			$_SHOPWARE["EDIT"]["releasedate"] = $datum;
		}else {
			$_SHOPWARE["EDIT"]["releasedate"] = "";
		}
		
		if ($_SHOPWARE["EDIT"]["datum"]){
			$datum = $_SHOPWARE["EDIT"]["datum"];
			$datum = explode("-",$datum);
			$datum = $datum[2].".".$datum[1].".".$datum[0];
			$_SHOPWARE["EDIT"]["datum"] = $datum;
		}else {
			$_SHOPWARE["EDIT"]["datum"] = "";
		}
	}
	
	// Preise aus Datenbank auslesen ...
	
	// Daten des Hauptartikels auslesen ...
	
	
	if (!$_REQUEST["variante"]){
		$abfrage_insertID = mysql_query("
	    SELECT id FROM s_articles_details WHERE articleID=$edit AND kind=1
	    ");	
    	$insertid_sub = mysql_result($abfrage_insertID,0,"id");
	}else {
		$insertid_sub = $_REQUEST["variante"];
	}
    // Standardwerte
	
	

	// Shopware Professional
	$queryPriceGroups = mysql_query("
	SELECT * FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC
	");

	while ($pricegroup = mysql_fetch_array($queryPriceGroups)){
		$sql = "
		SELECT * FROM s_articles_prices WHERE articledetailsID=$insertid_sub AND pricegroup='{$pricegroup["groupkey"]}' ORDER BY `from` ASC
		";
		$abfrage = mysql_query($sql);
		if (!@mysql_num_rows($abfrage)){
				
		  		$hinweis .= mysql_error($conn);	
		  		$clsShopware->warning_box("Hinweis<br />","Kein Preis für {$pricegroup["description"]} hinterlegt. Für diese Kundengruppe wird der Standardpreis angezeigt");
		  		// Set default-values
		  		$_POST["von"][$pricegroup["groupkey"]][0] = "1";
				$_POST["bis"][$pricegroup["groupkey"]][0] = "beliebig";
				$priceregulary[$pricegroup["groupkey"]][0] = 0;
				$_POST["pricepseudo"][$pricegroup["groupkey"]][0] = 0;
				$_POST["priceEK"][$pricegroup["groupkey"]][0] = 0;
				$_POST["percent"][$pricegroup["groupkey"]][0] = 0;
				
		}else {
			// Alles in Ordnung
			$i=0;
			while ($priceArray = mysql_fetch_array($abfrage)){
				$Tkey = $priceArray["pricegroup"];
				$TnVK = $priceArray["price"];
				$TnsVK = $priceArray["pseudoprice"];
				$TnEK = $priceArray["baseprice"];
	
				
				$_POST["von"][$Tkey][$i] = $priceArray["from"];
				$_POST["bis"][$Tkey][$i] = $priceArray["to"];
				
				// Preise Brutto oder Netto anzeigen?
				if (($sCore->sCONFIG['sARTICLESINPUTNETTO'] && mysql_num_rows($queryPriceGroups)==1) || $pricegroup["taxinput"]==0){
					$priceregulary[$Tkey][$i] = $priceArray["price"];
					$_POST["pricepseudo"][$Tkey][$i] = $priceArray["pseudoprice"];
				}else {
					$priceregulary[$Tkey][$i] = round($priceArray["price"]*(100+return_tax($_SHOPWARE["EDIT"]["taxID"]))/100,2);
					$_POST["pricepseudo"][$Tkey][$i] = round($priceArray["pseudoprice"]*(100+return_tax($_SHOPWARE["EDIT"]["taxID"]))/100,2);
				}
				
				$_POST["priceEK"][$Tkey][$i] = $priceArray["baseprice"];
				$_POST["percent"][$Tkey][$i] = $priceArray["percent"];
						
				unset($TnVK);
				unset($TnsVK);
				unset($TnEK);
				unset($Tkey);
				$i++;
			} // While - Schleife für Preise
			
			if ($_POST["von"]["EK"][0]!="1"){
				unset($_POST["von"]["EK"]);
				unset($_POST["bis"]["EK"]);
				unset($_POST["pricepseudo"]["EK"]);
				unset($_POST["priceEK"]["EK"]);
				unset($_POST["percent"]["EK"]);
				
				$_POST["von"]["EK"][0] = "1";
				$_POST["bis"]["EK"][0] = "beliebig";
				$priceregulary["EK"][0] = 0;
				$_POST["pricepseudo"]["EK"][0] = 0;
				$_POST["priceEK"]["EK"][0] = 0;
				$_POST["percent"]["EK"][0] = 0;
			}
		} // Alles in Ordnung, If Statement
	
} // Für jede Preisgruppe
	// =================================================
	// Attribute importieren
	// =================================================
	$includeAttributes = mysql_query("
	SELECT * FROM s_articles_attributes WHERE articledetailsID=$insertid_sub
	");
	while ($getAttribute = mysql_fetch_array($includeAttributes)){
		foreach ($getAttribute as $data => $v){
			#echo $data."->".$v."<br>";	
			#data holds fieldname, v holds data
			if (preg_match("/attr/",$data)){
				// Wenn Attr im Feldnamen vorkommt, passthrough Result Array
				#echo "Found<br>";	
				$attrNr =preg_replace("!attr(.*)!","\\1",$data);
				#echo $attrNr;
				$v = htmlspecialchars_decode($v);
				if ($v=="0") unset($v);
				$_SHOPWARE["EDIT"]["attr$attrNr"] = $v;
				
			}
		}	
	}
	// =================================================
	
} else {
	$priceregulary = array();
}
// ===========================================================

// Returns current tax
function return_tax($taxid){
	global $conn;

	$abfrageMwSt = mysql_query("
		SELECT tax FROM s_core_tax WHERE id=$taxid
	");
	
	return mysql_result($abfrageMwSt,0,"tax");
	
}
?>
<!-- HTML OUTPUT -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link href="../../../vendor/ext/resources/css/ext-all.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="js/SuperBoxSelect/SuperBoxSelect.js"></script>
<link href="js/SuperBoxSelect/superboxselect.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="js/pricebox.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>

<link href="js/calendar.css" rel="stylesheet" type="text/css">
<!--
<script type="text/javascript" src="js/autocompleter.js"></script>
<link href="js/autocompleter.css" rel="stylesheet" type="text/css">
-->
<script language="javascript" type="text/javascript">

<?php
$queryAttributes = mysql_query("
SELECT * FROM s_core_engine_elements
WHERE domname LIKE '%attr%' AND domtype = 'wysiwyg'
");
while ($queryAttribute = mysql_fetch_array($queryAttributes)){
	$data = str_replace("[","",$queryAttribute["domname"]);
	$data = str_replace("]","",$data);
	$queriedAttributes[] = $data;
}
if (@count($queriedAttributes)){
	$addWYSIWYG = ",". implode(",",$queriedAttributes);
}

if (!$_REQUEST["variante"]){
?>
	tinyMCE.init({
		// General options
		mode: "exact",
		elements : "txtlangbeschreibung,sTranslationsValue<?php echo  $addWYSIWYG ?>",
		theme : "advanced",
		name : "testtiny",
		<?php echo$sCore->sCONFIG['sTINYMCEOPTIONS'].","?>
		
		//cleanup : false, skin : "o2k7", relative_urls : false,theme_advanced_resizing : true, theme_advanced_toolbar_location : "top", theme_advanced_toolbar_align : "left",	theme_advanced_path_location : "bottom",
		plugins : "safari,pagebreak,style,layer,table,inlinepopups,preview,media,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,imagemanager",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor|,insertimage"	
	});
<?php
}
?>
<?php
$sql = 'SELECT name FROM s_articles_supplier ORDER BY name ASC';
$result = mysql_query($sql);
if(!empty($result)&&mysql_num_rows($result))
while ($supplier = mysql_fetch_assoc($result))
{
	if(empty($supplier['name'])) continue;
	$suppliers[] =  "['".addslashes(htmlspecialchars_decode($supplier['name']))."']";
}

$categories = array();
$sql = 'SELECT categoryID FROM s_articles_categories WHERE articleID='.(int) $_GET['edit'].' AND categoryID=categoryparentID';
$result = mysql_query($sql);
if(!empty($result)&&mysql_num_rows($result))
while ($category = mysql_fetch_assoc($result))
{
	$categories[] =  $category['categoryID'];
}
?>
window.addEvent('domready', function(){
	new Ext.form.ComboBox({
	    store: new Ext.data.ArrayStore({
		    fields: ['name'],
		    data : [<?php echo implode(', ', $suppliers);?>]
		}),
	    displayField:'name',
	    typeAhead: true,
	    mode: 'local',
	    triggerAction: 'all',
	    emptyText:'Bitte wählen...',
	    selectOnFocus:true,
	    applyTo: 'txtHersteller'
	});
	new Ext.ux.form.SuperBoxSelect({
	 	applyTo: 'sCategories',
	 	mode: 'remote',
		store:  new Ext.data.JsonStore({
		 	id:'id',
			root:'rows',
		 	fields:[
			 	{name:'id', type:'int'},
				{name:'name', type:'string'}
		 	],
			url: 'ajax/getCategories.php'
		}),
		value: '<?php echo implode(',', $categories);?>',
        resizable: true,
        name: 'sCategories[]',
        displayField: 'name',
        valueField: 'id',
        minChars: 2
     });	
});
</script>
<style type="text/css">
.x-form-field-wrap {float:left;}
label {text-align: left}
</style>
<?php 
/* LOAD _POST VALUES || EDIT VALUES FOR PREFILL PRICE SELECTION */
// Gruppen durchgehen
if ($_POST["priceregulary"]) $priceregulary = $_POST["priceregulary"];
foreach ($priceregulary as $group => $array){
// group => considers current group-key
// array => row / price

// Rows durchlaufen
	foreach ($array as $row => $nVK){
	//nVK enthält regulären Verkaufspreis
										
	// nsVK enthält Pseudo-Verkaufspreis
	$nsVK = $_POST["pricepseudo"][$group][$row];
	// nEK enthält Netto-Einkaufspreis
	$nEK = $_POST["priceEK"][$group][$row];
	// Staffel Start
	$von = $_POST["von"][$group][$row];
	// Staffel Ende
	$bis = $_POST["bis"][$group][$row];
	
	// Passthrough array
	$preset[$group][$row]["pricevk"] = $nVK;
	$preset[$group][$row]["pricepseudo"] = $nsVK;
	$preset[$group][$row]["priceek"] = $nEK;
	$preset[$group][$row]["percent"] = $_POST["percent"][$group][$row];
	$preset[$group][$row]["von"] = $von;
	$preset[$group][$row]["bis"] = $bis;
	
											
	} // Für jede Staffel
										
} // Für jede Gruppe

#print_r($preset);
if (!isset($preset)){
	// Standardwerte
	$queryPriceGroups = mysql_query("
	SELECT * FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC
	");
	
	while ($pricegroup = mysql_fetch_array($queryPriceGroups)){
		$preset[$pricegroup["groupkey"]][0]["pricevk"] = 0;
		$preset[$pricegroup["groupkey"]][0]["pricepseudo"] = 0;
		$preset[$pricegroup["groupkey"]][0]["priceek"] = 0;
		$preset[$pricegroup["groupkey"]][0]["percent"] = 0;
		$preset[$pricegroup["groupkey"]][0]["von"] = 1;
		$preset[$pricegroup["groupkey"]][0]["bis"] = "beliebig";
	}
	
}
// ------------------------------------------------

/* DEFINE TEMPLATE FOR PRICE SELECTION */
$form->element_price_template = "
	<div class=\"groupboxchild\">
		<dl class=\"accordion example2\">
		
		";
		
		// Shopware Professional
		$queryPriceGroups = mysql_query("
		SELECT * FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC
		");
		
		
		// Put pricegroups to javascript
		$jsgroups = "<script language=\"javascript\">
		pricegroups=new Array();";
		$defaultmwst = "<script language=\"javascript\">
		defaultmwst=new Array();";
		
		$i=0;
		// initiatie staffeln
		$staffeln = "
		<script language=\"javascript\">
		var staffel = new Array();";
		
		while ($pricegroup = mysql_fetch_array($queryPriceGroups)){
			if (($sCore->sCONFIG['sARTICLESINPUTNETTO'] && mysql_num_rows($queryPriceGroups)==1) || $pricegroup["taxinput"]==0){
				$priceInputMode = "netto";
				$priceInputModeText = "Netto";
			}else {
				$priceInputMode = "brutto";
				$priceInputModeText = "Brutto";
			}
		
		$form->element_price_template .= "<fieldset>
			<legend>{$pricegroup["description"]} - Eingabe $priceInputModeText-Preise
			<input type=\"hidden\" name=\"tax{$pricegroup["groupkey"]}\" value=\"$priceInputMode\">
			</legend>
		  		<div id=\"pricetemplate{$pricegroup["groupkey"]}\">
		  		</div>
			</fieldset>";
			// Build array with different pricegroups
			$jsgroups .= "pricegroups[$i]=\"{$pricegroup["groupkey"]}\";\n";
			$defaultmwst .= "defaultmwst[$i]=\"{$pricegroup["tax"]}\";\n";
			
			// Preset first row for each pricegroup
			$staffeln .= "		
    		staffel[\"{$pricegroup["groupkey"]}\"] = new Array();";
			
			foreach ($preset[$pricegroup["groupkey"]] as $key =>  $staffel){
			#echo "$key,,$staffel";
			//print_r($staffel);
			$staffeln .= "
    		staffel[\"{$pricegroup["groupkey"]}\"][$key] = new Array();
    		staffel[\"{$pricegroup["groupkey"]}\"][$key][\"von\"] = {$staffel["von"]};
    		staffel[\"{$pricegroup["groupkey"]}\"][$key][\"bis\"] = \"{$staffel["bis"]}\";
    		staffel[\"{$pricegroup["groupkey"]}\"][$key][\"pricevk\"] = \"{$staffel["pricevk"]}\";
    		staffel[\"{$pricegroup["groupkey"]}\"][$key][\"percent\"] = \"{$staffel["percent"]}\";
    		staffel[\"{$pricegroup["groupkey"]}\"][$key][\"pricepseudo\"] = \"{$staffel["pricepseudo"]}\";
    		staffel[\"{$pricegroup["groupkey"]}\"][$key][\"priceek\"] = \"{$staffel["priceek"]}\";
    		";
			}
			// ===
		$i++;
		}
		// Terminate pricegroups
		$jsgroups .= "</script>";
		$staffeln .= "</script>";
		$defaultmwst .= "</script>";
		
$form->element_price_template .= "			
		</dl>
	</div>
";
// Parse dynamic javascript to document
echo $jsgroups;
echo $staffeln;
echo $defaultmwst; 
?>

</head>

<body onLoad="generateInnerHtml();">
<?php
if ($clsShopware->errors){
	?>
	<div class="error_info">
	<?php
	foreach ($clsShopware->errors as $key => $message){
		echo "<strong>$key</strong>";
		echo $message;
	}
	?>
	</div>
	<?php
}
?>
<script language="javascript">
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "saveArticle":
		document.getElementById('submitArticle').submit();
		//parent.Growl('Artikel wurde gespeichert');
		break;
		case "deleteArticle":
		var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteArticle.php",{method: 'post', onComplete: function(json){
			parent.Growl('Artikel wurde gelöscht');
			parent.parent.sWindows.focus.close();
		}}).request('delete='+sId);
		break;
		case "duplicateArticle":
			var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/copyArticle.php",{method: 'post', onComplete: function(res){
				
				parent.parent.Growl('Artikel wurde dupliziert');
				res = res.replace(/#/,"");
				parent.parent.loadSkeleton('articles',false, {'article':res})
			}}).request('duplicate='+sId);
			break;
	}
	
}

<?php
if ($checkDependencies["FAILURE"]){
?>

parent.parent.sWindows.focus.shake(15);
parent.parent.Growl('<?php echo $sLang["articles"]["artikeln1_error_cant_save_article_1"] ?>');
// Mal testweise die anderen tabs freigeben


<?php
}else{
	if ($insertid && empty($_GET["ext"])){
		?>
		parent.sWindows.focus.unlockTabs('\\?article=(.*)','?article=<?php echo $insertid ?>');
		parent.Growl('<?php echo $sLang["articles"]["artikeln1_data_saved"] ?>');
		<?php
	}
}
?>
</script>
<?php
$form->header("submitArticle","POST",$_SERVER['PHP_SELF']."?article=".$_GET["article"]."&edit=".$_REQUEST["edit"]."&variante=".$_REQUEST["variante"]);
?>
<div id="accordion">
<?php

if ($_REQUEST["variante"]){
	$abfrage = mysql_query("
	SELECT * FROM s_core_engine_groups WHERE availablebyvariants=1 ORDER BY position ASC
	");	
}else {
	$abfrage = mysql_query("
	SELECT * FROM s_core_engine_groups ORDER BY position ASC
	");
}
// AUSGABE DER EINZELNEN EINGABE-BLÖCKE

if (!isset($edit)) $edit = 0;
if (!isset($artikel)) $artikel = 0;
if (!isset($maincat)) $maincat = 0;

	if ($_REQUEST["variante"]){
?><div style="clear:both"></div>
<div class="buttons" id="div" style="margin-bottom:20px">
  <ul>
    <li id="buttonTemplate" class="buttonTemplate">
      <button type="submit" value="send" class="button">
        <div class="buttonLabel"><?php echo $sLang["articles"]["artikeln1_save_Variant"] ?></div>
      </button>
    </li>
  </ul>
</div><br/><br /><br /><div style="clear:both"><br /></div>
<?php
}
if (($_REQUEST["edit"] || $_REQUEST["article"]) && $_REQUEST["variante"]!=-1){
	// Load Languages
	$queryLanguages = mysql_query("
	SELECT * FROM s_core_multilanguage 
	WHERE 
	skipbackend != 1
	ORDER BY id ASC		
	");
}
	
while ($language = mysql_fetch_array($queryLanguages)){
	$form->languages[] = $language;
	
	if ($_REQUEST["variante"]){
		$queryTranslation = mysql_query("
		SELECT * FROM s_core_translations
		WHERE
			objecttype = 'variant'
		AND
			objectkey = '{$_REQUEST["variante"]}'
		AND
			objectlanguage = '{$language["isocode"]}'
		");
	}else {
		$queryTranslation = mysql_query("
		SELECT * FROM s_core_translations
		WHERE
			objecttype = 'article'
		AND
			objectkey = '{$_GET["article"]}'
		AND
			objectlanguage = '{$language["isocode"]}'
		");
	}
	
	$form->translations[$language["isocode"]] = unserialize(@mysql_result($queryTranslation,0,"objectdata"));
}

// Load Language Data


while ($area=mysql_fetch_array($abfrage)){
	// render area
	$curID = $area["id"];
	if (!$_REQUEST["variante"]){
		$sql = "
		SELECT * FROM s_core_engine_elements WHERE `group`=$curID AND (version=".sVersion." OR !version)  ORDER BY position
		";
	}else {
		$sql = "
		SELECT * FROM s_core_engine_elements WHERE `group`=$curID AND (version=".sVersion." OR !version)  AND availablebyvariants=1 ORDER BY position
		";
	}

	$abfrage_elemente = mysql_query($sql);	
	
	
?>
<!-- Beginn of Group  -->

<?php

$sCore->sCONFIG["sCLASSICMODE"] = true;

if ($area["group"]!="Preise" && $area["group"]!="Kundengruppen"){
	if (!$sCore->sCONFIG["sCLASSICMODE"]){
	?>
		<h3 class="toggler atStart">
			<?php echo $area["group"]; ?>
		</h3>
		<div class="element atStart" style="height:100%" id="main" style="background: url(../../../backend/img/default/window/bg_toggle_gradient.gif) repeat-x;">
		
		<fieldset>
	<legend style="display:none"><?php echo $area["group"]; ?></legend>
	<?php
	} else {
	?>
		<fieldset style="margin-top: -30px;position:relative;top:0;left:0">
		<?php
		if($_SHOPWARE["EDIT"]["changetime"] && $area["group"]=="Stammdaten"){
			$changetime = $_SHOPWARE["EDIT"]["changetime"];
			$changetime = explode(" ",$changetime);
			$changetimeDate = explode("-",$changetime[0]);
			$changetimeTime = explode(":",$changetime[1]);
			$area["group"].= " Zuletzt bearbeitet: ".date("d.m.Y H:i:s",mktime($changetimeTime[0],$changetimeTime[1],$changetimeTime[2],$changetimeDate[1],$changetimeDate[2],$changetimeDate[0]));
		}
		?>
		<legend><?php echo $area["group"]; ?></legend>
		<div style="float:left">
	<?php
	}
}
elseif ($area["group"]=="Kundengruppen"){?>
	<fieldset style="margin-top: -30px;position:relative;top:0;left:0">
	<legend>Aktiv für Kundengruppen:</legend>
	<ul>
	<?php
	if (empty($_GET["article"])) $_GET["article"] = "0";
	
	$queryGroups = mysql_query("SELECT sc.id,sc.description, IF(s_articles_avoid_customergroups.articleID,1,0) AS active FROM s_core_customergroups sc
	LEFT JOIN s_articles_avoid_customergroups ON s_articles_avoid_customergroups.articleID = {$_GET["article"]} AND customergroupID = sc.id
	ORDER BY sc.id ASC
	");
	while ($sc = mysql_fetch_assoc($queryGroups)){
	?>
		<li>
			<input id="chkGrp<?php echo $sc["id"]?>" type="checkbox" name="enableGroups[]" value="<?php echo $sc["id"]?>" <?php if (empty($sc["active"])) echo "checked"; ?> >
			<label for="chkGrp<?php echo $sc["id"]?>" style="margin-left:5px;width:70px"><?php echo $sc["description"] ?></label>
		</li>
	<?php
	}
	?>
	<input type="hidden" name="enableGroupsHidden" value="1">
	</ul>
	</fieldset>
<?php
}
else {
	if (!$sCore->sCONFIG["sCLASSICMODE"]){
		?>
		<h3 class="toggler atStart">
			<?php echo $area["group"]; ?>
		</h3>
		<div class="element atStart" style="height:100%" id="mainPrices" style="background: url(../../../backend/img/default/window/bg_toggle_gradient.gif) repeat-x;">
		<?php
	}else {
		?>
		<div class="element atStart" style="height:100%" id="mainPrices">
		<?php
	}
}
?>
<!-- Block Controls-->
<?php
// Render Items
	while ($element = mysql_fetch_array($abfrage_elemente)){
			// Sind bereits Daten gesetzt?
			unset ($default);
			unset ($result_data);
		
			
			// Attribute müssen umgewandelt werden
			// Damit sie im _POST Array gefunden werden ....
			$attribute =  preg_replace("!attr\[(.*)\]!","\\1",$element["domname"]);
			
			
			
			if (is_numeric($attribute)){
				if (isset($_POST["attr"][$attribute])) $_POST[$attribute] = $_POST["attr"][$attribute];
	
			}
			if (isset($_POST[$attribute]) || isset($_POST[$element["domname"]]["ALT"]) ){
				// Datenübergabe aus 2-Feld Objekt ?
				if ($element["domtype"]!="select_with_new_option"){
						$default = $_POST[$attribute];	
				}else {
					$default = $_POST[$element["domname"]]["ALT"];
				}
				
					
			}
			else if (isset($_SHOPWARE["EDIT"][$element["databasefield"]])){
					
					$default = $_SHOPWARE["EDIT"][$element["databasefield"]]; //$EDIT[$element["item_name"]];	
						
					if (!$default && $element["domvalue"] && $element["domtype"]!="boolean"){
						$default = $element["domvalue"];
					}
				
			}else if ($element["domvalue"]){
					$default = $element["domvalue"];
			}else if ($sCore->sCONFIG["sBACKENDAUTOORDERNUMBER"] && !isset($_SHOPWARE["EDIT"]) && $element["domname"]=="txtbestellnr"){
				// Auto generate ordernumber
				// Ordernumber Prefix
				$prefix = $sCore->sCONFIG["sBACKENDAUTOORDERNUMBERPREFIX"] ? $sCore->sCONFIG["sBACKENDAUTOORDERNUMBERPREFIX"] : "SW";
				// Get next ordernumber
				$getNumber = mysql_query("
				SELECT number FROM s_order_number WHERE name='articleordernumber'
				");
				$default = $prefix.@mysql_result($getNumber,0,"number");
			}
			
			//echo $element["domname"]."->".$default;
			
			// Müssen wir an dieser Stelle Daten übergeben?
			$row=0;
			$abfrage_queries = mysql_query("
			SELECT * FROM s_core_engine_queries WHERE domelement='".$element["domname"]."' LIMIT 1
			");
			if (!isset($default)) $default = "";
			// Hier werden dynamisch die notwendigen Abfragen generier
			if (@mysql_num_rows($abfrage_queries)){
				$queryData = mysql_fetch_array($abfrage_queries);
				$do_query = mysql_query($queryData["query"]);
				if ($do_query && mysql_num_rows($do_query)){
					
					// !!IMPORTANT!!
					// Not finaly checked
					if ($element["domvalue"]){
						$result_data[0]["option"] = $element["domvalue"];
						$result_data[0]["value"] = 0;
						$row = 1;	
					}
					// !!IMPORTANT!!
					
					while ($result_of_query = mysql_fetch_array($do_query)){
						if ($default==$result_of_query[$queryData["value"]]){
							$result_data[$row]["set"] = 1;
						}else {
							$result_data[$row]["set"] = 0;
						}
						
						$result_data[$row]["option"] = $result_of_query[$queryData["option"]];
						$result_data[$row]["value"] = $result_of_query[$queryData["value"]];
						$row++;
					
					}	
				}
			}elseif ($element["domname"]=="selectTemplate"){
				$getTemplate = mysql_query("
				SELECT value FROM s_core_config WHERE name = 'sDETAILTEMPLATES'
				");
				$getTemplate = mysql_result($getTemplate,0,"value");
				$getTemplate = explode(";",$getTemplate);
				foreach ($getTemplate as $template){
					$pair = explode(":",$template);
					$id = $pair[1];
					$name = $pair[0];
					if ($id=="Blog") continue;
					
					if ($default == $name){
						$set = 1;
							
					} else{
						$set = 0;
					}
					$result_data[] = array("option"=>$id,"value"=>$name,"set"=>$set);
					
				}
			}
			
			if (!isset($result_data) && isset($default)) { $result_data = $default;}
			
			if($element["domname"] !="notification" || $element["domname"] =="notification" && $sCore->sCheckLicense("","",$sCore->sLicenseData["sPREMIUM"])) {
				$form->addElement($element["domtype"],$element["domname"],$element["domdescription"],1,$result_data,$element["required"],$element["domclass"],$element["help"],$element["multilanguage"]);
			}
			
			if ($element["required"]){
				$form->addRule($element["domname"],''.$element["domdescription"].'','required',$element["domtype"]);
			}
	
	} // Für jedes Element
	?>
<!-- End of Block-Controls -->
<?php
if ($area["group"]!="Preise"){
	
	if(preg_match("/Stammdaten/",$area["group"]) && $_GET["edit"] && !isset($_GET["variante"])){
		
	?>
	<script>
		function deleteArticle(ev,text){
				parent.parent.sConfirmationObj.show('Soll der Artikel "'+text+'" wirklich gel&ouml;scht werden?',window,'deleteArticle',ev);
		}
		
		function sDuplicate(ev,text){
				parent.parent.sConfirmationObj.show('Soll der Artikel "'+text+'" wirklich dupliziert werden?',window,'duplicateArticle',ev);
		}
		
		function preview (){
			var domain = $('selectPreview').value;
			if (!domain){
				alert ("Domain is empty");
				return;
			}
			
			
			var Target = "http://<?php echo $sCore->sCONFIG["sBASEPATH"] ?>/backend/UserLogin/previewDetail/?article=<?php echo $_GET["article"] ?>&id="+domain;
			window.open(Target);
			
		}
		function sNote(ev,text){
			var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/saveIm.php",{method: 'post', onComplete: function(json){
			parent.parent.Growl('Artikel wurde auf die Merkliste gesetzt');
			}}).request('subject='+"Artikel "+text+"&msg={article:<?php echo $_SHOPWARE["EDIT"]["ordernumber"] ?>}&user=<?php echo $_SESSION["sID"] ?>");
		}
	</script>
	</div>
	<div style="float:right;margin-top:-20px">
	<div style="float:left;margin-right:10px;">
	<fieldset style="width:300px;height:55px;margin:0;">
		<legend>Optionen zu Artikel ID:<?php echo $_REQUEST["article"] ?></legend>
		<ul>
		<li class="clear"> </li>
		<li>
		<a onclick="sDuplicate(<?php echo $_REQUEST["article"] ?>,'<?php echo str_replace("'","",$_SHOPWARE["EDIT"]["name"]) ?>');" style="cursor: pointer;float:left;margin-right:15px;border:0px" class="ico3 folder_plus" >Duplizieren</a>
		<a onclick="deleteArticle(<?php echo $_REQUEST["article"] ?>,'<?php echo str_replace("'","",$_SHOPWARE["EDIT"]["name"]) ?>');" style="cursor: pointer;float:left;margin-right:15px;border:0px" class="ico3 delete" >Löschen</a>
		<a onclick="sNote(<?php echo $_REQUEST["article"] ?>,'<?php echo str_replace("'","",$_SHOPWARE["EDIT"]["name"]) ?>');" style="cursor: pointer;float:left;margin-right:15px;border:0px" class="ico3 folder_plus" >Merken</a>
		</li>
		<li class="clear"> </li>
		</ul>
		
	</fieldset>
	</div>
	<div style="float:left;margin-right:10px;">
	<fieldset style="width:300px;height:55px;margin:0;">
		<legend>Artikel Preview</legend>
		<ul>
		<li class="clear"> </li>
		<li>
		<label style="width:25px;font-weight:bold">Shop:</label>
		<?php
		$shops = array();
	 	$getShops = mysql_query("SELECT id,name,domainaliase FROM s_core_multilanguage WHERE domainaliase != ''");
	 	while ($shop = mysql_fetch_Assoc($getShops)){
			$domain = explode("\n",$shop["domainaliase"]);
			if (!is_array($domain)) $domain[0] = $shop["domainaliase"];
			if (empty($domain[0])) continue;
			$temp = str_replace($sCore->sCONFIG["sHOST"],$domain[0],$sCore->sCONFIG["sBASEPATH"]);
			
			$domain = "http://".$temp."/".$sCore->sCONFIG["sBASEFILE"];
			$domain = str_replace(array("\n","\r"),"",$domain);			 		
	 		$shops[$shop["name"]] = array("id"=>$shop["id"],"domain"=>$domain,"name"=>$shop["name"]);
	 		
	 		//"?sViewport=detail&sArticle=1234";
	 	}
		?>
		<select id="selectPreview" name="shop" style="float:left">
		<?php
			foreach ($shops as $shop){
				echo '<option value="'.$shop["id"].'">'.$shop["name"].'</option>';
			}
		?>
			
		</select>
		<a class="ico3 world" style="margin-left:5px;cursor:pointer;float:left;height:16px;padding: 1px 0 1px 16px" onclick="preview()"></a>
		</li>
		<li class="clear"> </li>
		</ul>
		
	</fieldset>
	</div>
	<div style="float:left;margin-right:10px;">
	<fieldset style="width:300px;margin:0;">
		<legend>Schnellkategorisierung</legend>
		<ul>
		<li class="clear"> </li>
		<li style="margin:0;">
		<input type="text" style="width:292px;" id="sCategories"></div>
		</li>
		<li class="clear"> </li>
		</ul>
	</fieldset>
	</div>
	</div>
	<?php
	}
	?>
	</div>
	</fieldset>
	<?php
	if (!$sCore->sCONFIG["sCLASSICMODE"]){
	?>
	</div>
	
	<?php
	}
}else {
	
	
	?>
	<!-- End of Group -->
	
	</div>
	<?php

}
} // ENDE AUSGABE DER EINZELNEN BLÖCKE
?>
<?php
if ($_REQUEST["variante"]){
?>
<div class="buttons" id="div" style="margin-top:10px;">
  <ul>
    <li id="buttonTemplate" class="buttonTemplate">
      <button type="submit" value="send" class="button">
        <div class="buttonLabel"><?php echo $sLang["articles"]["artikeln1_save_Variant"] ?></div>
      </button>
    </li>
  </ul>
</div><br/>
<?php
}elseif ($_REQUEST["ext"]){
?>
<div class="buttons" id="div" style="margin-top:10px;">
  <ul>
    <li id="buttonTemplate" class="buttonTemplate">
      <button type="submit" value="send" class="button">
        <div class="buttonLabel"><?php echo $sLang["articles"]["artikeln1_save_data"] ?></div>
      </button>
    </li>
  </ul>
</div><br/>
<input type="hidden" name="ext" value="1">
<?php
}
?>
<?php
$form->footer();

?>

</div>

<div id="priceHeader" style="display:none">
	<div class="price_line_head">
		<div class="fauxpricecol2">
			<div class="phead_col1"><?php echo $sLang["articles"]["artikeln1_Season"] ?></div>
			<div class="phead_col2"><?php echo $sLang["articles"]["artikeln1_Selling_price"] ?></div>
			<div class="phead_col3"><?php echo $sLang["articles"]["artikeln1_Percent_discount"] ?></div>
			<div class="phead_col4"><?php echo $sLang["articles"]["artikeln1_pseudo_price"] ?></div>
			<div class="phead_col5"><?php echo $sLang["articles"]["artikeln1_purchase_price"] ?></div>
			<div class="fixfloat"></div>
		</div>
	</div>	
	<div class="fixfloat"></div>
</div>

<script language="javascript">
window.addEvent('domready',function(){
	var myTips = new Tips($$('.toolTip'));
});
</script>
<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>

</body>
</html>
<!-- END OF HTML-OUTPUT -->