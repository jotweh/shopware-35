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
	parent.parent.parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
// *****************
?>
<?php

if (!$_GET["id"]) die ($sLang["orders"]["main_no_order_given"]);

if(isset($_POST["invoice_shipping"]))
	$_POST["invoice_shipping"] = floatval(str_replace(',', '.',$_POST["invoice_shipping"]));

if ($_GET["id"] && $_POST["saveMain"]){
	$oldAmount = mysql_query("
		SELECT invoice_shipping, invoice_amount FROM s_order WHERE id={$_GET["id"]}
	");
	$oldAmount = mysql_fetch_array($oldAmount);
	$newAmount = ($oldAmount['invoice_amount']-$oldAmount['invoice_shipping'])+$_POST["invoice_shipping"];
	
	if ($_POST["cleareddate"]){
		$cleareddate = explode(".",$_POST["cleareddate"]);
		$cleareddate = $cleareddate[2]."-".$cleareddate[1]."-".$cleareddate[0];
	}
	$updateOrder = mysql_query("
	UPDATE s_order SET comment='{$_POST["comment"]}', invoice_amount=$newAmount, status='{$_POST["statusMain"]}',cleared='{$_POST["statusPayment"]}', invoice_shipping='{$_POST["invoice_shipping"]}', cleareddate='$cleareddate', trackingcode='{$_POST["trackingcode"]}' WHERE id={$_GET["id"]}
	");
	
}
$queryOrder = mysql_query("
SELECT id, userID,currency,currencyFactor, subshopID,taxfree, invoice_shipping, ordernumber, referer,language,comment,customercomment, invoice_amount,paymentID, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertime,DATE_FORMAT(cleareddate,'%d.%m.%Y') AS cleareddate, status, cleared, trackingcode, dispatchID FROM s_order WHERE id={$_GET["id"]}
");


if (!@mysql_num_rows($queryOrder)) die($sLang["orders"]["main_order_not"]."{$_GET["id"]} ".$sLang["orders"]["main_order_not_found"]);

$orderMain = mysql_fetch_array($queryOrder);



$userMain = mysql_query("
SELECT * FROM s_user WHERE id={$orderMain["userID"]}
");

if (!@mysql_num_rows($userMain)) echo $sLang["orders"]["main_Attention_assigned_user_was_deleted"]."<br />";
$userMain = mysql_fetch_array($userMain);

// Fetch User-Details
// Billingadress and Shippingadress

$userGetBilling = mysql_query("
SELECT * FROM s_order_billingaddress WHERE userID={$orderMain["userID"]} AND orderID={$_GET["id"]}
");

if (!@mysql_num_rows($userGetBilling)) echo $sLang["orders"]["main_Attention_assigned_user_was_deleted"]."<br />";

$userGetShipping = mysql_query("
SELECT * FROM s_order_shippingaddress WHERE userID={$orderMain["userID"]} AND orderID={$_GET["id"]}
");

$userGetBilling = mysql_fetch_array($userGetBilling);

if (!@mysql_num_rows($userGetShipping)) $userGetShipping = $userGetBilling; else $userGetShipping = mysql_fetch_array($userGetShipping);




if ($_POST["saveBilling"]){
	
	
	if ($updateUser){
		$sSuccess = $sLang["orders"]["main_changes_saved"];
	}else {
		$sError = $sLang["orders"]["main_changes_save_failed"];
	}
	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $sLang["orders"]["main_search"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="js/calendar.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

</head>


<body>

<script language="javascript">
<?php if ($sError){?>
	parent.parent.parent.Growl('<?php echo $sError; ?>');
	parent.parent.sWindows.focus.shake(15);
<?php }?>
<?php if ($sSuccess){?>
	parent.parent.parent.Growl('<?php echo $sSuccess ?>');
<?php }?>	
</script>

	
<script type='text/javascript'>	
	window.addEvent('domready',function(){
		$('form').addEvent('submit', function(e){
			new Event(e).stop();
			createPdf ($('form').toQueryString());
			parent.parent.parent.Growl('PDF wird erstellt!');
		});
		
		$('preview').addEvent('click',function(e){
			new Event(e).stop();
			var typ = $('typ').getValue();
			var ust = $('ust_free').getValue();
			$('previewForm').setProperty('action',"../../../../backend/document/?preview=1&ust_free="+ust+"&typ="+typ+"&id=<?php echo $_REQUEST['id']?>");
			$('previewForm').submit();
		});
		
		$('reset').addEvent('click', function(e){
			parent.parent.parent.Growl('Einstellungen wurden resetet!');
		});
		
		
	});

	function createPdf (vars) 
	{
		$('grid_loading').setStyle('display', 'block');
		$('ext_vouchers_grid').setStyle('opacity', 0.3);
		var myAjax = new Ajax("../../../../backend/document/",{method: 'post', onComplete: function(json){
			$('grid_loading').setStyle('display', 'none');
			$('ext_vouchers_grid').setStyle('opacity', 1.0);
			Ext.getCmp('ext_vouchers_grid').store.load();
		}}).request('id=<?php echo $_REQUEST['id']?>&'+vars);
	}
</script>
<?php
	$today = date("d.m.Y", time());
?>

<script type="text/javascript">
//ExtJS Datefields
Ext.onReady(function(){
	if(Ext.getCmp('date') == null)
	{
		new Ext.form.DateField({
			id:'date',
			width:103,
			format: 'd.m.Y',
			renderTo: 'select_date',
			value:'<?php echo $today ?>'
		});
	}
	
	if(Ext.getCmp('delivery_date') == null)
	{
		new Ext.form.DateField({
			id:'delivery_date',
			width:103,
			format: 'd.m.Y',
			renderTo: 'date_of_delivery'
		});
	}
			

	if(Ext.getCmp('ext_vouchers_grid') == null)
	{
		var documentsStore = new Ext.data.Store({
		    url: '../../../backend/ajax/documents.php',
		     baseParams:{id:'<?php echo $_GET["id"]; ?>',
		     			 type: 'forExt'} ,
		
		    reader: new Ext.data.JsonReader({
		           root: '',
		           fields : ['id', 'datum', 'beleg', 'amount','hash']
		        })
		});
		documentsStore.load();
		
		//PDF Renderer
		function pdfLinkRenderer(value, meta, rec, rowI, colI, store)
		{
			if (rec.data.hash){
				value = rec.data.hash;
			}
			var ret = "<a  class=\"ico page_white_acrobat\" style=\"cursor:pointer\" href=\"openPDF.php?pdf="+value+"\" target=\"_blank\"></a>";
			return ret;
		}
		
		
		//Belegegrid ExtJS
		var cm = [
			{header: '', width:50, dataIndex: 'id', renderer: pdfLinkRenderer},
			{header: 'Datum', width:80, dataIndex: 'datum'},
			{header: 'Beleg', width:200, dataIndex: 'beleg'},
			{header: 'Betrag', width:80, dataIndex: 'amount'}
			];
	
		var grid = new Ext.grid.GridPanel({
			id: 'ext_vouchers_grid',
			renderTo: 'ext_grid',
			height:138,
			columns: cm,
			store: documentsStore
		});
	}
});
</script>

<div style="display:block">	
		<fieldset style="margin: 0px 10px 15px 10px;padding:0; display:none;">
			<legend style="margin-left:10px;"><?php echo $sLang["orders"]["main_Existing_documents"] ?></legend>
			<div id='table3'></div>
		</fieldset>
			
		<div id='ext_grid' style="margin: 0px 10px 15px 10px;position:relative;"></div>
		
		<div id='grid_loading' style="display:none; position:absolute; top:60px; left:40%; width:150px;">
			<img id="prozess" src="../../../vendor/ext/resources/images/default/tree/loading.gif" style="float:left;" />
			<p>&nbsp;<?php echo $sLang["orders"]["main_Document_is_created"] ?></p>
		</div>
		
		<form id="form">
			
			<fieldset style="margin: 0px 10px 15px 10px;padding:10px;">
				<legend style="margin-left:10px;"><?php echo $sLang["orders"]["main_create_documents"] ?></legend>
				<ul>
				 <li>
				  <label for="date"><?php echo $sLang["orders"]["main_Selected_Date"] ?></label>
				  <div id="select_date" style="float:left;"></div>
		
				 </li>
				 <?php
				 $getBid = mysql_query("
				 SELECT docID FROM s_order_documents WHERE orderID = {$_GET["id"]} AND type=0
				 ");
				 $bid = @mysql_result($getBid,0,"docID");
				 ?>
				 <li>
				  <label for="bid"><?php echo $sLang["orders"]["main_invoice_number"] ?></label>
				  <input style="text-align: left;" id="bid" name="bid" value="<?php echo $bid ?>">
				 </li>
				 <li style="clear: both;">
				  <label for="delivery_date"><?php echo $sLang["orders"]["main_date_of_delivery"] ?></label>
				  <div id="date_of_delivery" style="float:left;"></div>
				  
				 </li>
				 <li>
				  <label for="typ"><?php echo $sLang["orders"]["main_choice"] ?></label>
				  <select name="typ" id="typ">
				 	<?php
				  	$getDocumentTypes = mysql_query("
				  	SELECT * FROM s_core_documents ORDER BY id ASC
				  	");
				  	while ($document = mysql_fetch_assoc($getDocumentTypes)){
				    ?>
				    <option value="<?php echo ($document["id"] - 1) ?>"><?php echo $document["name"] ?></option>
				    <?php
				  	 }
				    ?>
				  </select>
				 </li>
		 <?php
		 // Alle verfügbaren Gutscheine abfragen
		 $queryVouchers = mysql_query("
		 SELECT DISTINCT s_emarketing_vouchers.id AS id, description, value, percental FROM s_emarketing_vouchers, s_emarketing_voucher_codes
		 WHERE  modus = 1 AND (valid_to >= now() OR valid_to=0)
		 AND s_emarketing_voucher_codes.voucherID = s_emarketing_vouchers.id
		 AND s_emarketing_voucher_codes.userID = 0
		 AND s_emarketing_voucher_codes.cashed = 0
		 ");
		 ?>
		 <!-- Gutschein - Auswahl -->
				 <li>
				  <label for="typ"><?php echo $sLang["orders"]["main_voucher"] ?></label>
				  <select name="voucher" id="voucher">
				   <option value="0"><?php echo $sLang["orders"]["main_no_voucher"] ?></option>
				   <?php
					while ($voucher = mysql_fetch_array($queryVouchers)){
						if ($voucher["percental"]){
							$value = "%";	
						}else {
							$value = $orderMain["currency"];
							if ($orderMain["currencyFactor"]!=1){
								$voucher["value"] *= $orderMain["currencyFactor"];
								$voucher["value"] = round($voucher["value"],2);
							}
						}
						
						echo "<option value=\"{$voucher["id"]}\">{$voucher["description"]} (".$sCore->sFormatPrice($voucher["value"])." $value)</option>";
					}
				   ?>
				  </select>
				 </li>
		<!-- /////////////////////// --> 
				 <li style="clear: both;">
				  <label for="typ"><?php echo $sLang["orders"]["main_Customer_VAT_number"] ?></label>
				  <?php $userGetBilling["ustid"] = trim($userGetBilling["ustid"]);
				  if(empty($userGetBilling["ustid"])) {$userGetBilling["ustid"] = "keine hinterlegt!";}?>
				  <input style="text-align: left;" id="ust_id" name="ust_id" value="<?php echo$userGetBilling["ustid"]?>" disabled>
				 </li>
				 <li>
				  <label for="docComment">Belegkommentar (Änderungen werden nicht dauerhaft gespeichert)</label>
				  <textarea rows="6" cols="25" id="docComment" name="docComment"><?php echo htmlentities($orderMain["customercomment"])?></textarea>
				 </li>
				 <li>
				  <label for="typ"><?php echo $sLang["orders"]["main_Sales_Tax_Exempt"] ?></label>
				  <input id="ust_free" name="ust_free" value="1" type="checkbox" style="margin: 0;" <?php echo $orderMain["taxfree"] ? "checked" : ""?>>
				 </li>
			  </ul>
		 
			  <!--Beleg Buttons-->
		 		<div class="buttons" id="buttons"> 
		 			<div class="clear" style="height:20px;"></div>
					<ul>
					 <li style="display: block;" class="buttonTemplate" id="save"><button class="button" value="send" type="submit" name="#NAME#"><div class="buttonLabel"><?php echo $sLang["orders"]["main_create_document"] ?></div></button></li>
			         
			         <li style="display: block;" class="buttonTemplate" id="reset"><button class="button" type="button" name="#NAME#"><div class="buttonLabel"><?php echo $sLang["orders"]["main_reset"] ?></div></button></li>
			        
			         <!--<li style="display: block;" class="buttonTemplate" id="add"><button class="button" id="addArticle" name="" type="button" value="" class="button"><div class="buttonLabel"><?php echo $sLang["orders"]["main_add_Article_subsequently"] ?></div></button></li>-->
			         
			         <li style="display: block;" class="buttonTemplate" id="preview"><button class="button" id="preview" name="" type="button" value="" class="button"><div class="buttonLabel"><?php echo $sLang["orders"]["main_preview"] ?></div></button></li>
					</ul>
				</div>	
			</fieldset>
		
		</form>
</div>
	<!--</div>-->

<!--</div>-->
	

<!--</div>-->
<form id="previewForm" name="previewForm" action="" target="_blank" method="post">
<input type="hidden" name="temp" value="1">
</form>



</body>
</html>