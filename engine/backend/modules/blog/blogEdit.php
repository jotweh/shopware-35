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
if(!isset($HTTP_RAW_POST_DATA) ){
  $HTTP_RAW_POST_DATA = file_get_contents("php://input");
}
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
include ("../articles/class_articles.inc.php");
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
function sArticleCategory  ($articleID, $categoryID)
{
	$inserts = array();
	$categoryID = intval($categoryID);
	$articleID = intval($articleID);
	if(empty($categoryID)||empty($articleID))
		return false;
	$categoryparentID = $categoryID;
	$parentID = $categoryID;
	while ($categoryID!=1 && !empty($categoryID))
	{
		$sql = "
			REPLACE INTO s_articles_categories
				(articleID,categoryID,categoryparentID)
			VALUES
				($articleID, $categoryID, $categoryparentID)
		";
		mysql_query($sql);
		$inserts[] = mysql_insert_id();
		
		$sql = "SELECT parent FROM s_categories WHERE id=$categoryID";
		$tmp = mysql_result(mysql_query($sql),0,"parent");
		$parentID = $categoryID;
		if (!empty($tmp)){
			$categoryID = $tmp;
		} else {
			$categoryID = 1;
		}
	}
	return $inserts;
}
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
	$checkDependencies = $form->validate($_POST,$clsShopware);
	
	
	if (($_REQUEST["article"] || $_REQUEST["variante"]) && $_REQUEST["variante"]!=-1){
			$mode = "edit";	
	} else {
			$mode = "insert";
	}
	
	// Check if ordernumber is not already set
	if ($_POST["txtbestellnr"]){
		
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
		if ($mode=="insert"){
				// Get next ordernumber
				$getNumber = mysql_query("
				UPDATE s_order_number SET number=number+1 WHERE name='blogordernumber'
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
						$_POST["changetime"] = ( !isset($_POST["changetime"]) || empty($_POST["changetime"]) ) ? 'now()' : "'{$_POST["changetime"]}'";
	    				$insertArticleMainSQL = "
			    		INSERT INTO s_articles (supplierID,name,description, description_long,datum,shippingtime
			    		,active,shippingfree,notification,releasedate,variantID, taxID, pseudosales,topseller, free, keywords, minpurchase, purchasesteps, maxpurchase, 
			    		purchaseunit, referenceunit, packunit, unitID, changetime,pricegroupID,pricegroupActive,filtergroupID, laststock,template,mode)
			    		VALUES (
			    		".$supplierID.",
			    		\"".$_POST["txtArtikel"]."\",
			    		\"".$_POST["txtshortdescription"]."\",
			    		\"".$_POST["txtlangbeschreibung"]."\",
			    		now(),
			    		\"".$_POST["txtlieferzeit"]."\",
			    		".$_POST["txtaktiv"].",
			    		".$_POST["txtversandkostenfrei"].",
			    		".$_POST["notification"].",
			    		now(),
		    			".$_POST["txtvariantenkriterium"]["ALT"].",
		    			0,
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
		    			{$_POST["changetime"]},
		    			'{$_POST["selectPricegroup"]}',
		    			'{$_POST["checkPricegroup"]}',
		    			'{$_POST["selectFilterGroup"]}',
		    			{$_POST["laststock"]},
		    			'{$_POST["selectTemplate"]}',
		    			1
			    		)
			    		";
	    				eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post6"));	
	    				
		    			$insertArticleMain = mysql_query($insertArticleMainSQL);
		    			$insertid = mysql_insert_id();
		    			if (!empty($insertid) && !empty($_REQUEST["category"])){
		    				sArticleCategory  ($insertid, $_REQUEST["category"]);
		    			}
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
	    				changetime = '{$_POST["changetime"]}',
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
		
		
		
	} // End of result
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
		eval($sCore->sCallHookPoint("articles_artikeln1.inc.php_Post13"));
		if ($_SHOPWARE["EDIT"]["mode"]!=1){
			die("Reguläre Shop-Artikel können nicht im Blog-Modus bearbeitet werden!");
		}
		$_SHOPWARE["EDIT"]["name"] = htmlspecialchars($_SHOPWARE["EDIT"]["name"]);
		$_SHOPWARE["EDIT"]["name"] = str_replace("&quot;","\"",$_SHOPWARE["EDIT"]["name"]);
		$_SHOPWARE["EDIT"]["name"] = str_replace("&amp;","&",$_SHOPWARE["EDIT"]["name"]);
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

?>
<!-- HTML OUTPUT -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="../articles/js/autocompleter.js"></script>
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>

<link href="../articles/js/autocompleter.css" rel="stylesheet" type="text/css">
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
var activePanel;

		window.addEvent('domready', function(){

			
		
	
			<?php
			if (!$_REQUEST["variante"]){
			?>
			var el = $('txtHersteller');
			
			<?php
			// Query all suppliers
			$queryAllSuppliers = mysql_query("
			SELECT name FROM s_articles_supplier
			ORDER BY name ASC
			");
			echo "var tokens = [";
			while ($supplier = mysql_fetch_array($queryAllSuppliers)){
				if(empty($supplier["name"])) continue;
				$suppliers[] =  "['".addslashes(htmlspecialchars_decode($supplier["name"]))."', '']";
			}
			echo implode(",",$suppliers);
			echo "];";
			?>

			new Autocompleter.Local(el, tokens, {
				'filter': function() {
					// filter only items beginning with the query
					var regex = new RegExp('^' + this.queryValue.escapeRegExp(), 'i');
					// we filter name and country code
					return this.tokens.filter(function(token){
						return (regex.test(token[0]) || regex.test(token[1]));
					});
				},
				'injectChoice': function(choice) {
					// element with text and inside, the code
					var el = new Element('li', {'style':'float:none;margin:0','html': this.markQueryValue(choice[0])}).grab(
						new Element('span', {'class': 'example-info', 'html': this.markQueryValue(choice[1])})
					);
					el.inputValue = choice[0];
					// addChoiceEvents is a helper to add the mouse events to the choice items
					this.addChoiceEvents(el).inject(this.choices);
				},
				'selectMode': 'pick',
				'overflow': true
			});
			
			<?php
			}
			?>
		});

</script>


</head>

<body>
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
	if ($insertid){
		?>
		parent.myExt.unlockTabs('<?php echo $insertid ?>');
		parent.parent.Growl('<?php echo $sLang["articles"]["artikeln1_data_saved"] ?>');
		<?php
	}
}
?>
</script>
<div id="accordion">

	
	
	
	
<?php


$abfrage = mysql_query("
SELECT * FROM s_core_engine_groups WHERE `group` NOT IN ('Grundpreisberechnung') ORDER BY position ASC
");

// AUSGABE DER EINZELNEN EINGABE-BLÖCKE

if (!isset($edit)) $edit = 0;
if (!isset($artikel)) $artikel = 0;
if (!isset($maincat)) $maincat = 0;

$form->header("submitArticle","POST",$_SERVER['PHP_SELF']."?article=".$_GET["article"]."&edit=".$_REQUEST["edit"]."&variante=".$_REQUEST["variante"]);
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
	
	$elemente = array("'txtHersteller'","'selectFilterGroup'","'selectTemplate'","'changetime'","'txtArtikel'","'txtaktiv'","'txtbestellnr'","'txtlangbeschreibung'","'toparticle'","'txtshortdescription'","'txtkeywords'");
	for ($i = 1;$i<=20;$i++) $elemente[] = "'attr[$i]'";
	if ($curID == 1){
	$sql = "
	SELECT * FROM s_core_engine_elements WHERE (`group`=$curID OR (`group` = 0 AND domname='changetime')) AND (version=".sVersion." OR !version)  
	AND domname IN (".implode(",",$elemente).")
	ORDER BY position
	";
	}else {
		$sql = "
	SELECT * FROM s_core_engine_elements WHERE `group`=$curID  AND (version=".sVersion." OR !version)  
	AND domname IN (".implode(",",$elemente).")
	ORDER BY position
	";
	}
	$abfrage_elemente = mysql_query($sql);	
	
	
?>
<!-- Beginn of Group  -->

<?php

$sCore->sCONFIG["sCLASSICMODE"] = true;
//$sCore->sCONFIG["sCLASSICMODE"] = true;
if ($area["group"]!="Preise"){
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
	<fieldset style="margin-top: -30px;">
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
	
<?php
}
}else {
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
			}else if (!isset($_SHOPWARE["EDIT"]) && $element["domname"]=="txtbestellnr"){
				// Auto generate ordernumber
				// Ordernumber Prefix
				$prefix = "BLOG";
				// Get next ordernumber
				$getNumber = mysql_query("
				SELECT number FROM s_order_number WHERE name='blogordernumber'
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
				
					if ($default == $name){
						$set = 1;
							
					} else{
						$set = 0;
					}
					if ($name != "../blog/details.tpl"){
						$result_data[] = array("option"=>$id,"value"=>$name,"set"=>$set);
					}
				}
			}
			
			if (!isset($result_data) && isset($default)) { $result_data = $default;}
			
			if($element["domname"] !="notification" || $element["domname"] =="notification" && $sCore->sCheckLicense("","",$sCore->sLicenseData["sPREMIUM"])) {
				$disabled = $element["domname"] == "txtbestellnr" ? true : false;
				$form->addElement($element["domtype"],$element["domname"],$element["domdescription"],1,$result_data,$element["required"],$element["domclass"],$element["help"],$element["multilanguage"],$disabled);
			}
			
			if ($element["required"]){
				$form->addRule($element["domname"],''.$element["domdescription"].'','required',$element["domtype"]);
			}
	
	} // Für jedes Element
	?>
<!-- End of Block-Controls -->
<?php
if ($area["group"]!="Preise"){
	
	if(preg_match("/Stammdaten/",$area["group"]) && $_GET["edit"]){
		
	?>
	
	
	
	
	<?php
	}
	?>
	
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
<input type="hidden" name="category" value="<?php echo $_REQUEST["category"] ?>">
<?php
}
?>
<?php
$form->footer();

?>

</div>



<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>

</body>
</html>
<!-- END OF HTML-OUTPUT -->