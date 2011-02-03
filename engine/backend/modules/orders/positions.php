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
SELECT id, userID,currency,currencyFactor, invoice_shipping, ordernumber, referer,language,comment,customercomment, invoice_amount,paymentID, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertime,DATE_FORMAT(cleareddate,'%d.%m.%Y') AS cleareddate, status, cleared, trackingcode, dispatchID FROM s_order WHERE id={$_GET["id"]}
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
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<link href="js/calendar.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php
$sql = sprintf("SELECT currencyFactor FROM `s_order` WHERE `id` = '%s'", $_GET["id"]);
$query = mysql_query($sql);
$currencyFactor = mysql_result($query, 0, 'currencyFactor');
?>
<script type='text/javascript'>	
//Grid Position neu-------------------------------------------------
function deleteConfirm(rowI, s_order_details_id)
{
	var store = Ext.getCmp('ext_order_grid').store;
	var rec = store.getAt(rowI);
	Ext.MessageBox.confirm('Bestätigung', 'Soll die Position '+rec.get("name")+' wirklich gelöscht werden?', function(btn){
		if(btn == "yes")
		{
			Ext.Ajax.request({
			   url: '../../../backend/ajax/orderes.php',
			   params: 	{id:'<?php echo $_GET["id"]; ?>',
						s_order_details_id: s_order_details_id,
			   			action:'deletePositionForExt'},
			   success: function(){
			   		var store = Ext.getCmp('ext_order_grid').store;
					store.load();
					parent.parent.parent.Growl('Löschvorgang erfolgreich!');
			   }
			});
		}else{
			parent.parent.parent.Growl('Löschvorgang abgebrochen!');
		}
	});

}


Ext.onReady(function(){
	var currencyFactor = '<?php echo $currencyFactor ?>';
	currencyFactor = Number(currencyFactor);
	currencyFactor != 1 ? cFhidden = false : cFhidden = true;
	
	if(Ext.getCmp('ext_order_grid') == null)
	{
		var taxStore = new Ext.data.Store({
		    url: '../../../backend/ajax/getTaxStore.php',		
		    reader: new Ext.data.JsonReader({
		           root: '',
		           fields : ['id', 'value']
		        })
		});
		taxStore.load();
		var ordersStore = new Ext.data.Store({
		    url: '../../../backend/ajax/orderes.php',
		     baseParams:{action: 'getDetailsForExt',
		     			 id:'<?php echo $_GET["id"]; ?>'} ,
		
		    reader: new Ext.data.JsonReader({
		           root: '',
		           fields : ['id','articleordernumber','name','quantity','price','total','status','tax', 'instock', 'instock_save', 'articleID', 'templatechar', 'euro_amount']
		        }),
		    listeners: {'update': function(store, rec, oper){
		    	if(oper == "commit")
		    	{
		    		var json = Ext.encode(rec.data);
		    		
		    		Ext.Ajax.request({
					   url: '../../../backend/ajax/orderes.php',
					   params: {rec: Ext.encode(rec.data),
								id: 0,
					   			action:'saveOrderForExt'},
					   success: function(){
					   		store.load();
					   }
					});
		    	}
		    }}
		    });
		ordersStore.load();
		
		var statusStore = new Ext.data.SimpleStore({
			fields: ['id', 'value'],
			data: [	[0, 'offen'],
					[1, 'In Bearbeitung'],
					[2, 'Storniert'],
					[3, 'Abgeschlossen']]
		});
		
		function renderEuro(value, meta, rec, rowI, colI, store)
		{	
			var templatechar = store.getAt(rowI).get('templatechar');		
			var val = String(value);
			val = val.replace(/,/, ".");
			val = Number(val);
			val = val.toFixed(2);
			
			val = String(val);
			val = val.replace(/\./, ",");
			val = val+" "+templatechar;
			
			val.substr(0,1) == "-" ? color='red' : color='inherit';
			
			return "<div style='text-align:right;'><font color='"+color+"'>"+val+"</font></div>";
		}
		
		function renderEuroCF(value, meta, rec, rowI, colI, store)
		{	
			var cF = Number(currencyFactor);
			if(cF != 1)
			{		
			//Andere Währung / Umrechnung in Euro			
			var rec = store.getAt(rowI);
			var quant = rec.get('quantity');
			var price = rec.get('price');
			
			var priceW = price.replace(/,/, ".");
			var quantW = quant.replace(/,/, ".");
		
			var cFVal = priceW*quantW/cF;
			cFVal = cFVal.toFixed(2);
		
			
			cFVal = String(cFVal);
			cFVal.substr(0,1) == "-" ? color='red' : color='inherit';
			cFVal = cFVal.replace(/\./, ",");
			cFVal = cFVal + ' €'
			return "<div style='text-align:right;'><font color='"+color+"'>"+cFVal+"</font></div>";
			}else{
				return "";
			}
		}
		
		function renderStatus(value, meta, rec, rowI, colI, store)
		{
			var val = statusStore.getAt(value).get('value');
			
			return val;
		}
		
		function renderOptions(value, meta, rec, rowI, colI, store)
		{
			var rec = store.getAt(rowI);
			var s_order_details_id = rec.get('id');
			var articleID = rec.get('articleID');
			var val = "";
			
			if(rec.get('id') == "new") return val;
			
			val = "<a class='ico cards_minus' style='cursor:pointer; float:left;' onclick='deleteConfirm("+rowI+","+s_order_details_id+");'></a>";
			if(articleID != 0)
			{
				val += "<a class='ico package_green' style='cursor:pointer; float:left;' onclick='parent.parent.loadSkeleton(\"articles\", false, {article: "+articleID+"});'></a>";
			}
			return val;
		}
		
		
		function renderTax(value, meta, rec, rowI, colI, store)
		{
			if(value != null)
			{
//				var val = taxStore.getById(value).get('value');
//				return val;

				for(var i=0; i<taxStore.getCount(); i++)
				{
					var rec = taxStore.getAt(i);
					if(rec.get('id') == value)
					{
						return rec.get('value');
//					}else{
//						return taxStore.getAt(0).get('value');
					}
				}
				return "";
			}else{
				return "";
			}
			
		}
		
		var cm = [
				{header: 'Art-Nr.', dataIndex: 'articleordernumber', width:80, editor: new Ext.form.TextField({})},
				{header: 'Bezeichnung', dataIndex: 'name', width: 200, editor: new Ext.form.TextField({})},
				{header: 'Anzahl', dataIndex: 'quantity', width: 80,
				editor: new Ext.form.TextField({})},
				{header: 'Preis', dataIndex: 'price', renderer: renderEuro, width: 80,
				editor: new Ext.form.TextField({})},
				{header: 'Gesamt', dataIndex: 'total', renderer: renderEuro, width: 80},
				{header: 'EUR Gesamt', dataIndex: 'euro_amount', renderer: renderEuroCF, width: 80, hidden: cFhidden},
				{header: 'Status', dataIndex: 'status', renderer:renderStatus, width: 80,
				editor: new Ext.form.ComboBox({
					store: statusStore,
				    displayField:'value',
				    valueField:'id',
				    forceSelection: true,
				    typeAhead: true,
				    mode: 'local',
				    triggerAction: 'all'
				})},
				{header: 'MwSt', dataIndex: 'tax', width: 80, renderer:renderTax,
				editor: new Ext.form.ComboBox({
					store: taxStore,
				    displayField:'value',
				    forceSelection: true,
				    valueField:'id',
				    typeAhead: true,
				    mode: 'local',
				    triggerAction: 'all'
				})},
				{header: 'Lagerbestand', dataIndex: 'instock', width: 60},
				{header: 'Optionen', dataIndex: 'options', width: 65, renderer:renderOptions}
				];
		
		var addEntry = Ext.data.Record.create([
			{name: 'id'},	
			{name: 'articleordernumber'},	
			{name: 'name'},	
			{name: 'quantity'},	
			{name: 'price'},	
			{name: 'total'},	
			{name: 'status'},	
			{name: 'instock'},
			{name: 'orderID'},
			{name: 'tax'},
			{name: 'options'}
		]);
				
		var grid = new Ext.grid.EditorGridPanel({
			id: 'ext_order_grid',
			renderTo: 'grid',
			height:200,
			columns: cm,
			clicksToEdit:1,
			store: ordersStore,
			listeners: {'afteredit': function(grid){
				//var str = grid+"\n"+grid.field+"\n"+grid.value+"\n"+grid.originalValue +"\n"+grid.row+"\n"+grid.column;
				var rec = ordersStore.getAt(grid.row);
				
				if(grid.field == "quantity" || grid.field == "price")
				{
					var quant = rec.get('quantity');
					var price = rec.get('price');
					
					var priceW = price.replace(/,/, ".");
					var quantW = quant.replace(/,/, ".");
					
					var calc = (quantW*priceW);
					calc = calc.toFixed(2);
					rec.set('total', calc);
					
					priceW = Number(priceW);
					//priceW = priceW.toFixed(2);
					rec.set('price', priceW);
				}
				if(grid.field == "quantity" && rec.get('id') != "new" && rec.get('instock') != null && rec.get('instock') != "")
				{
					var def = (grid.originalValue-grid.value);
					var old = rec.get('instock');
					var calc = parseInt(old)+parseInt(def);
					rec.set('instock', calc);
				}
				//Kommastellen
//				if(grid.field == "price" || grid.field == "total")
//				{
//					alert(grid.value);
//				}
					
			}, 'beforeedit': function(grid){
				var rec = ordersStore.getAt(grid.row);
				//Skonto!
				if(grid.field == "tax" && rec.get('tax') == 0)
				{
					grid.cancel = true;
				}
			}, 'beforeedit': function(grid){
				var rec = ordersStore.getAt(grid.row);
				//Skonto!
				if(grid.field == "tax" && rec.get('tax') == 0)
				{
					grid.cancel = true;
				}
			}},
			tbar: [{
				text: '<a class="ico cards_plus"></a>Bestellposition hinzufügen',
				listeners:{'click': function(){
//					var p = new addEntry({
//						id: 'new',
//						articleordernumber: '',
//						name: '',
//						quantity: '0',
//						price: '0',
//						total: '0',
//						status: 0,
//						instock: '-',
//						options: 0,
//						tax: 1,
//						orderID: '<?php echo $_GET["id"]; ?>'
//					});
//					
//					ordersStore.insert(0, p);
//					grid.startEditing(0, 0);
					Ext.Ajax.request({
					   url: '../../../backend/ajax/orderes.php',
					   params: {id: <?php echo $_GET["id"]; ?>,
					   			action:'newEntryForExt'},
					   success: function(){
					   		grid.store.load();
					   }
					});
				}}
			}]
		});
		
	}	
});
function save(){
	Ext.getCmp('ext_order_grid').store.commitChanges();
}
// /Grid Position neu-----------------------------------------------
</script>
	<fieldset style="margin-top:-20px; margin-left:0px; margin-right:10px;">
		<legend><?php echo $sLang["orders"]["please_remember"] ?></legend>
		 	<?php echo $sLang["orders"]["please_remember_txt"] ?>
		</fieldset>

	<div id="grid" style="margin-top:-10px; margin-left:0px; margin-right:10px; clear:both;"></div>
	
	<div onclick="save();" class="buttons" id="buttons" style="position:relative; top:10px; left:-10px;">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
		<button type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["orders"]["main_save"] ?></div></button>
		</li>	
		</ul>
	</div>

</body>
</html>