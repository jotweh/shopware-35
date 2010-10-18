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

//action
if(isset($_GET['add']) || isset($_GET['delete']))
{
	if(isset($_GET['add'])){
		$inactive = 0;
		$id = $_GET['add'];
	}else{
		$inactive = 1;
		$id = $_GET['delete'];
	}
	$sql = sprintf("UPDATE `s_core_licences` SET `inactive` = '%s' WHERE `id`='%s' LIMIT 1", $inactive, $id);
	mysql_query($sql);
}

?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
</head>

<body >

        
<fieldset class="col2_cat2" style="margin-top:-34px;">
<legend>Verfügbare Module</legend>

<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
   		 
   		 	//Modulname mmapping
   		 	$aSL = array();	
			$aSL['sCORE'] 			= "Shopware Core";
			$aSL['sARTICLECONF'] 	= "Artikel Konfigurator";
			$aSL['sANALYTICS'] 		= "Erweiterte Auswertungen";
			$aSL['sESD'] 			= "ESD Integration";
			$aSL['sGROUPS'] 		= "Haendlerbereich Kunden";
			$aSL['sFUZZY'] 			= "Intelligente Suchfunktion";
			$aSL['sPRICESEARCH'] 	= "Produkt Exporte";
			$aSL['sPROPERTIES'] 	= "Produktvergleiche Filter";
			$aSL['sMAILCAMPAIGNS'] 	= "Marketing-Tools";
			$aSL['sMAILCAMPAIGNSADV']= "Marketing Tools Advanced";
			$aSL['sLANGUAGEPACK1'] 	= "Subshop Lizenz 1";
			$aSL['sLANGUAGEPACK2'] 	= "Subshop Lizenz 2";
			$aSL['sLANGUAGEPACK3'] 	= "Subshop Lizenz 3";
			$aSL['sLANGUAGEPACK4'] 	= "Subshop Lizenz 4";
			$aSL['sLANGUAGEPACK5'] 	= "Subshop Lizenz 5";
			$aSL['sLANGUAGEPACK6'] 	= "Subshop Lizenz 6";
			$aSL['sLANGUAGEPACK7'] 	= "Subshop Lizenz 7";
			$aSL['sLANGUAGEPACK8'] 	= "Subshop Lizenz 8";
			$aSL['sLANGUAGEPACK9'] 	= "Subshop Lizenz 9";
			$aSL['sTICKET'] 	= "Ticket-System";
			$aSL['sPREMIUM'] 	= "Wartungsvertrag";
			$aSL['sFORMBUILDER'] 	= "Formular-Modul";
			$aSL['sBUNDLE'] 	= "Bundle-Modul";
			$aSL['sLIVE'] 	= "Liveshopping";
			
			$aSLink = array();	
			$sl_pre = "<a class='softlink' target='_blank' href='";	
			$sl_pst = "'>Informationen zu diesem Modul</a>";	
			$aSLink['sCORE'] 			= $sl_pre."http://www.shopware-ag.de/Shopware-Core_detail_66_189.html".$sl_pst;
			$aSLink['sENTERPRISE'] 	= $sl_pre."http://www.shopware-ag.de/Shopware-Enterprise_detail_19_164.html".$sl_pst;
			$aSLink['sARTICLECONF'] 	= $sl_pre."http://www.shopware-ag.de/Artikel-Konfigurator_detail_64_193.html".$sl_pst;
			$aSLink['sANALYTICS'] 		= $sl_pre."http://www.shopware-ag.de/Erweiterte-Auswertungen_detail_65_194.html".$sl_pst;
			$aSLink['sESD'] 			= $sl_pre."http://www.shopware-ag.de/ESD-Integration_detail_70_199.html".$sl_pst;
			$aSLink['sGROUPS'] 		= $sl_pre."http://www.shopware-ag.de/Haendlerbereich-Kunden._detail_67_196.html".$sl_pst;
			$aSLink['sFUZZY'] 			= $sl_pre."http://www.shopware-ag.de/Intelligente-Suchfunkti._detail_69_198.html".$sl_pst;
			$aSLink['sPRICESEARCH'] 	= $sl_pre."http://www.shopware-ag.de/Produkt-Exporte_detail_63_192.html".$sl_pst;
			$aSLink['sPROPERTIES'] 	= $sl_pre."http://www.shopware-ag.de/Produktvergleiche-Fil._detail_61_190.html".$sl_pst;
			$aSLink['sMAILCAMPAIGNS'] 	= $sl_pre."http://www.shopware-ag.de/Shopware-Marketing-Tools_detail_68_197.html".$sl_pst;
			$aSLink['sMAILCAMPAIGNSADV']= $sl_pre."http://www.shopware-ag.de/Shopware-Marketing-Tools-Advanced_detail_71_197.html".$sl_pst;
			$aSLink['sLANGUAGEPACK1'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK2'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK3'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK4'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK5'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK6'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK7'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK8'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
			$aSLink['sLANGUAGEPACK9'] 	= $sl_pre."http://www.shopware-ag.de/Subshop-Lizenz-weiter._detail_16_195.html".$sl_pst;
   		 
			// Query Related-Articles
			$sql = "
			SELECT id, module as description, inactive FROM s_core_licences WHERE id>=1 ORDER BY module ASC
			";
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
			
			if(!empty($aSL[$article["description"]]))	
			{
				$description = $aSL[$article["description"]];
			}else{
				$description = $article["description"];
			}
				
			if($article["description"] != "sCORE" && $article["description"] != "sCAMPAIGNS") {	
		?>
        
		
     <tr class="rowcolor2">
       <th class="first-child"><img src="../../../backend/img/default/icons4/key.png" style="margin:0 15px 0 0;" /><?php echo $description?></th>
       <?php if($article["inactive"] == 1) { ?>
      			<td class="last-child"><a href="<?php echo $_SERVER["PHP_SELF"]."?add=".$article["id"]?>" style="cursor:pointer" class="ico add"></a>Modul aktivieren</td>
      	<?php }else{ ?>	
  				<td class="last-child"><a href="<?php echo $_SERVER["PHP_SELF"]."?delete=".$article["id"]?>" style="cursor:pointer" class="ico delete"></a>Modul deaktivieren</td>
    	 <?php } ?>
    	 <td class="last-child"><a href="#" style="cursor:default;" class="ico information"></a><?php echo $aSLink[$article["description"]]; ?></td>
  	</tr>
  	<?php }} ?>
   </tbody>
</table>
</fieldset>

</body>

</html>