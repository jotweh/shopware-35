<?php
/**********************************************************
Saferpay-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

transactions.php

**********************************************************/
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

include("../../../connectors/saferpay/language_de.php");

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo $sLang["saferpayreserveorder"]["transactions_reorder"] ?></title>
<!-- Common Styles for the examples -->
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="js/calendar.css" rel="stylesheet" type="text/css">
</head>


<?php

$von = $_POST["von"] ? $_POST["von"] : date("01.m.Y");
$bis = $_POST["bis"] ? $_POST["bis"] : date("d.m.Y");

$vonEnglish = explode(".",$von);
$vonEnglish = $vonEnglish[2]."-".$vonEnglish[1]."-".$vonEnglish[0];

$bisEnglish = explode(".",$bis);
$bisEnglish = $bisEnglish[2]."-".$bisEnglish[1]."-".$bisEnglish[0];


$addPaymentFilter = "";
$addStatusFilter = "";
$addReserveFilter = "";
// Realy simple search by Char
if ($_POST["filterPayment"]){
	if ($_POST["filterPayment"] == "-1"){
		$filter = "0";
	} else if ($_POST["filterPayment"] == "17"){
		$addPaymentFilter = "
		AND ( s_order.cleared = 0 OR s_order.cleared = 17 )
		";
	}else {
		$filter = $_POST["filterPayment"];
	}
	
	if (!$addPaymentFilter){
		$addPaymentFilter = "
		AND s_order.cleared = $filter
		";
	}
}
if ($_POST["filterStatus"]){
	if ($_POST["filterStatus"] == "-1") $filter = "0"; else $filter = $_POST["filterStatus"];
	$addStatusFilter = "
	AND s_order.status = $filter
	";
}
if ($_POST["filterReserved"]){
	if ($_POST["filterReserved"] == "1") $booked = "0"; elseif ($_POST["filterReserved"] == "2") $booked = "1";
  if ($booked=='0') {
  	$addReserveFilter = "
  	AND saferpay_orders.saferpay_complete = '0'
  	";
  }  elseif ($booked=='1') {
  	$addReserveFilter = "
  	AND saferpay_orders.saferpay_complete = '1'
  	";
  }
}

if ($_POST["sTransaction"]){
	$sql = "
	SELECT  s_order.id AS id,
          s_order.ordernumber AS ordernumber,
          s_order.transactionID AS transactionID,
          s_order.userID,
          s_order.invoice_amount,
          DATE_FORMAT(s_order.ordertime,'%d.%m.%Y %H:%i') AS ordertimeFormated,
          s_order.status,
          s_order.cleared,
		  s_core_states.description,
          saferpay_orders.saferpay_account_id AS saferpayTransactionID,
          saferpay_orders.saferpay_complete
  FROM s_order, saferpay_orders, s_core_states
	WHERE (
		s_order.ordernumber LIKE '%{$_POST["sTransaction"]}%'
	OR
		s_order.transactionID LIKE '%{$_POST["sTransaction"]}%'
	)
	AND s_core_states.id = s_order.status
  AND s_order.status != -1
	$addPaymentFilter
	$addStatusFilter
  $addReserveFilter
	AND s_order.transactionID != ''
	AND s_order.transactionID = saferpay_orders.orders_id
	ORDER BY s_order.ordertime DESC
	";
}else {
	$sql = "
  SELECT  s_order.id AS id,
          s_order.ordernumber AS ordernumber,
          s_order.transactionID AS transactionID,
          s_order.userID,
          s_order.invoice_amount,
          DATE_FORMAT(s_order.ordertime,'%d.%m.%Y %H:%i') AS ordertimeFormated,
          s_order.status,
          s_order.cleared,
          s_core_states.description,
          saferpay_orders.saferpay_account_id AS saferpayTransactionID,
          saferpay_orders.saferpay_complete
  FROM s_order, saferpay_orders, s_core_states
	WHERE TO_DAYS(s_order.ordertime)>=TO_DAYS('$vonEnglish') AND TO_DAYS(s_order.ordertime)<=TO_DAYS('$bisEnglish')
	AND s_core_states.id = s_order.status
  AND s_order.status != -1
	$addPaymentFilter
	$addStatusFilter
  $addReserveFilter
	AND s_order.transactionID != ''
  AND s_order.transactionID = saferpay_orders.orders_id
	ORDER BY s_order.ordertime DESC
	";
}

//echo $sql;
$queryOrders = mysql_query($sql);

?>


<script type='text/javascript'>	


<?php
if (!mysql_num_rows($queryOrders)){
	// No search-results
	
?>
var headers = [
{
"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_status"] ?>",
"key":"kdnr","sortable":true,
"fixedWidth":true,"defaultWidth":"500px"}
];
var data = [{"kdnr":"<?php echo $sLang["saferpayreserveorder"]["transactions_no_orders_found"] ?>"}
];

<?php
} else {
?>
var headers = [
{
"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_date"] ?>",
"key":"regdate","sortable":true,
"fixedWidth":false,"defaultWidth":"115px","date2":true},
{"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_ordernumber"] ?>",
"key":"ordernumber","sortable":true,
"fixedWidth":false,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_Action"] ?>",
"key":"transaction","sortable":true,
"fixedWidth":false,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_order_status"] ?>",
"key":"state","sortable":true,
"fixedWidth":false,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_payment_status"] ?>",
"key":"state2","sortable":true,
"fixedWidth":false,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_total"] ?>",
"key":"amount","sortable":true,
"fixedWidth":false,"defaultWidth":"90px", "numeric":true},
{"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_customer"] ?>",
"key":"customer","sortable":true,
"fixedWidth":false,"defaultWidth":"150px"},
{"text":"<?php echo $sLang["saferpayreserveorder"]["transactions_options"] ?>",
"key":"order3","sortable":true,
"fixedWidth":false,"defaultWidth":"135px"}

];
<?php

	// Display search-results
	echo "var data = [";
	$countOrders = mysql_num_rows($queryOrders);
	$countOrdersTemp = $countOrders;
	$i = 0;
	while ($order=mysql_fetch_array($queryOrders)){
		// count positions

		$queryCustomer = mysql_query("
		SELECT firstname, lastname, company FROM s_user_billingaddress WHERE userID={$order["userID"]}
		");

		if (@mysql_num_rows($queryCustomer)){
			$userdata = mysql_fetch_array($queryCustomer);
			$customer = $userdata["company"] ? $userdata["company"] : $userdata["firstname"]." ".$userdata["lastname"];
		}else {
			$customer = "ERROR";
		}
		
		// Query previous orders
		$queryAdditionalOrders = mysql_query("
		SELECT id FROM s_order WHERE userID= {$order["userID"]}
		AND status != -1
		");
		
		$countAdditionalOrders = mysql_num_rows($queryAdditionalOrders);
		
		// Query previous canceld orders
		$queryCanceledOrders = mysql_query("
		SELECT id FROM s_order WHERE userID= {$order["userID"]}
		AND status = 8
		");
		
		$countCanceledOrders = mysql_num_rows($queryCanceledOrders);
		
		// Days left
		$daysLeft = explode(" ",$order["ordertimeFormated"]);
		$daysLeft = explode(".",$daysLeft[0]);
		
		$daysLeft = mktime(0,0,0,$daysLeft[1],$daysLeft[0],$daysLeft[2]);
		$now = mktime(0,0,0,date("m"),date("d"),date("Y"));
		$difference = $now - $daysLeft;
		$daysLeft = intval($difference/86400);
		$daysLeft = 7 - $daysLeft;			
				
		if ($order["status"]!=4){
			$invoiceTotal += $order["invoice_amount"];
		}else {
			$countOrdersTemp--;
 		}
		$i++;
		//$order["ordernumber"] = sprintf("%06d", $order["ordernumber"]);
		$comma = $i == $countOrders ? "" : ",";
		$status = $order["description"];
		//$status  = wordwrap($status,12," ");
		// Query Payment - State
		$getPaymentInfo = mysql_query("SELECT description FROM s_core_states WHERE id = {$order["cleared"]}");
		$getPaymentInfo = mysql_fetch_array($getPaymentInfo);
		$payment = $getPaymentInfo["description"];
		if (!$order["cleared"]) $order["cleared"] = 17;
			$customer = htmlspecialchars($customer);

		if ($daysLeft>0 && $order["saferpay_complete"]=='0'){
    		  $cancel = '<a href=\"\" onclick=\"parent.parent.loadSkeleton(\'saferpayreserveorder_action\', false, {\'ordernr\':'.$order['ordernumber'].'}); return false;\" style=\"cursor:pointer\">'.$sLang["saferpayreserveorder"]["transactions_PP_free"].'</a> ('.$daysLeft.' Tage)';
		} elseif ($daysLeft <= 0 && $order["saferpay_complete"]=='0') {
			$cancel = $sLang["saferpayreserveorder"]["transactions_Period_end"];
		}else {
			$cancel = '';
		}
?>

	{"orderID":"<?php echo $order["id"]?>","transaction":"<?php echo $order["transactionID"] ?>","stateNumeric":"<?php echo $order["status"] ?>","paymentNumeric":"<?php echo $order["cleared"] ?>","regdate":"<?php echo $order["ordertimeFormated"]?>","ordernumber":"<a onclick=\"parent.parent.loadSkeleton('orders',false, {'id':<?php echo $order["id"] ?>})\" class=\"ico information\" style=\"cursor:pointer\"></a><?php echo $order["ordernumber"]?>","state":"<?php echo $status ?>","state2":"<?php echo $payment ?>","amount":"<?php echo number_format($order["invoice_amount"], 2, '.', '')?> ","customer":"<a class=\"ico user\" style=\"cursor:pointer\" onclick=\"parent.parent.loadSkeleton('userdetails',false, {'user':<?php echo $order["userID"] ?>})\"></a><?php echo $customer ?>","order3":"<?php echo $cancel?>"}<?php echo $comma ?>
<?php
	} // for each user
	echo "];";
} // In case of any result

?>
</script>
<?php
// If category is choosen

?>
<style>
td {
	font-size:10px;
}
input {
	height:20px;
}
</style>
<body>
<form id="reloadSearch" method="POST" action="">

<table cellpadding="2" cellspacing="2">
<tr>
<td><?php echo $sLang["saferpayreserveorder"]["transactions_Evaluation_of"] ?></td>
<td><?php echo "<input id=\"von\" name=\"von\" value=\"".$von."\" onclick=\"displayDatePicker('von', false, 'dmy', '.');\"><a class=\"ico calendar\"  onclick=\"displayDatePicker('von', false, 'dmy', '.');\"></a>"; ?></td>
<td><?php echo $sLang["saferpayreserveorder"]["transactions_Evaluation_until"] ?></td>
<td><?php echo "<input id=\"bis\" name=\"bis\" value=\"$bis\"  onclick=\"displayDatePicker('bis', false, 'dmy', '.');\"><a class=\"ico calendar\"  onclick=\"displayDatePicker('bis', false, 'dmy', '.');\"></a>"; ?></td>
</tr>
<tr>
<td><?php echo $sLang["saferpayreserveorder"]["transactions_Booking_Status"] ?></td>
<td>
<select name="filterReserved">
<option value="0"><?php echo $sLang["saferpayreserveorder"]["transactions_show_all"] ?></option>
<option value="1" <?php if ($_POST['filterReserved']==1) echo 'selected'?>><?php echo $sLang["saferpayreserveorder"]["transactions_open_bookings"] ?></option>
<option value="2" <?php if ($_POST['filterReserved']==2) echo 'selected'?>><?php echo $sLang["saferpayreserveorder"]["transactions_Completed_bookings"] ?></option>
</select>

</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>


<tr>
<td><?php echo $sLang["saferpayreserveorder"]["transactions_status_1"] ?></td>
<td>
<select name="filterStatus">
<option value="0"><?php echo $sLang["saferpayreserveorder"]["transactions_show_all"] ?></option>
<?php
$getAllStates = mysql_query("
SELECT id, description FROM s_core_states WHERE
`group` = 'state'
AND id >= 0
ORDER BY position ASC
");
$i=0;
while ($state = mysql_fetch_assoc($getAllStates)){
	$translationState[$state["id"]] = $i;
	$i++;
	if (!$state["id"]) $state["id"] = "-1";
	if ($state["id"]==$_POST["filterStatus"]){
		$selected = "selected";
	}else {
		$selected = "";
	}
	echo "<option value=\"{$state["id"]}\" $selected>{$state["description"]}</option>";
}
?>
</select>
</td>
<td><?php echo $sLang["saferpayreserveorder"]["transactions_status_payment"] ?></td>
<td>
<select name="filterPayment">
<option value="0"><?php echo $sLang["saferpayreserveorder"]["transactions_show_all"] ?></option>
<?php
$getAllStates = mysql_query("
SELECT id, description FROM s_core_states WHERE
`group` = 'payment'
ORDER BY position ASC
");
$i=0;
while ($state = mysql_fetch_assoc($getAllStates)){
	$translationPayment[$state["id"]] = $i;
	$i++;
	if (!$state["id"]) $state["id"] = -1;
	if ($state["id"]==$_POST["filterPayment"]){
		$selected = "selected";
	}else {
		$selected = "";
	}
	echo "<option value=\"{$state["id"]}\" $selected>{$state["description"]}</option>";
}
?>
</select>

</td>
</tr>


<tr>
<td><?php echo $sLang["saferpayreserveorder"]["transactions_search"] ?></td>
<td><input type="text" name="sTransaction" value="<?php echo $_POST["sTransaction"]?>"></td>
</tr>
<tr>
<td colspan="4">
 	<div class="buttons" id="buttons" style="width:150px">
		<ul>
		  <li style="display: block;" class="buttonTemplate" id="add"><button onclick="$('reloadSearch').submit();" class="button" id="addArticle" name="" type="button" value="" class="button"><div class="buttonLabel"><?php echo $sLang["saferpayreserveorder"]["transactions_refresh_view"] ?></div></button></li>
		</ul>
	</div>
</td>
</tr>
</table>
</form>
<strong><?php echo $sLang["saferpayreserveorder"]["transactions_attention"] ?></strong>
<br /><br />
<div id='test'></div>
<?php echo $sLang["saferpayreserveorder"]["transactions_total_in_period"] ?> <?php echo $sCore->sFormatPrice(round($invoiceTotal,2)) ?> €<br />
<?php echo $sLang["saferpayreserveorder"]["transactions_count_of_orders"] ?> <?php echo$countOrdersTemp; ?>


<div id="ajaxChangeState" style="opacity:0.8; width:100px;height:30px; position:absolute;display:none;z-index:100;">
	<select id="ajaxChangeStateSelect" onblur="$('ajaxChangeState').setStyle('display','none');">
	<?php
	$getAllStates = mysql_query("
	SELECT id, description FROM s_core_states WHERE
	`group` = 'state' AND id >= 0
	ORDER BY position ASC
	");
	while ($state = mysql_fetch_assoc($getAllStates)){
		echo "<option value=\"{$state["id"]}\">{$state["description"]}</option>";
	}
	?>
	</select>
</div>

<div id="ajaxChangePayment" style="opacity:0.8; width:100px;height:30px; position:absolute;display:none;z-index:100;">
	<select id="ajaxChangePaymentSelect" onblur="$('ajaxChangePayment').setStyle('display','none');">
	<?php
	$getAllStates = mysql_query("
	SELECT id, description FROM s_core_states WHERE
	`group` = 'payment'
	ORDER BY position ASC
	");
	while ($state = mysql_fetch_assoc($getAllStates)){
		echo "<option value=\"{$state["id"]}\">{$state["description"]}</option>";
	}
	?>
	</select>
</div>

<script>
				function manageStateChange(ev){
					// Display state-change-select
					$('ajaxChangeState').setStyles({top: ev.client.y+'px', left: ev.client.x+'px'});
					$('ajaxChangeState').setStyle('display','block');
					//console.log(this);
					//console.log(ev);
					
					// Get current - value
					var orderState =this.data.stateNumeric;
					if (orderState==18) orderState = 9;
					//console.log(orderState);

					// Select default
					$('ajaxChangeStateSelect').options[orderState].selected = true;

					// Add hook to this cell
					$('ajaxChangeStateSelect').removeEvents('change');
					$('ajaxChangeStateSelect').addEvent('change',refreshStateChange.bind(this));
				}
				
				function managePaymentChange(ev){
					// Display state-change-select
					$('ajaxChangePayment').setStyles({top: ev.client.y+'px', left: ev.client.x+'px'});
					$('ajaxChangePayment').setStyle('display','block');
					//console.log(this);
					//console.log(ev);
					var  xLookupPayment = new Array();
					<?php
					foreach ($translationPayment as $key => $value){
						?>
						xLookupPayment[<?php echo $key ?>] = <?php echo $value ?>;
						<?php
					}
					?>
					// Get current - value
					var paymentState = this.data.paymentNumeric;
					//console.log(paymentState+ "TEST");
					// Select default 
					$('ajaxChangePaymentSelect').options[xLookupPayment[paymentState]].selected = true;
					
					// Add hook to this cell
					$('ajaxChangePaymentSelect').removeEvents('change');
					$('ajaxChangePaymentSelect').addEvent('change',refreshPaymentChange.bind(this));
				}
				
				function refreshStateChange (ev){
					// Building lookup-table for states
					var  xLookupStates = new Array();
					<?php
					foreach ($translationState as $key => $value){
						?>
						xLookupStates[<?php echo $key ?>] = <?php echo $value ?>;
						<?php
					}
					?>

					// Get values
					var newState = ev.target.value;
					var newStateDescription = 	$('ajaxChangeStateSelect').options[xLookupStates[newState]].innerHTML;
					if (!newStateDescription){
						alert ("<?php echo $sLang["saferpayreserveorder"]["transactions_cant_load_Description"] ?>");
						return false;
					}
					this.cols[3].element.setHTML(newStateDescription);
					this.data.stateNumeric = newState;
					// Get Order - ID
					var orderID = this.data.orderID;
					var orderNumber = this.data.ordernumber;
					if (!orderID){
						alert ("<?php echo $sLang["saferpayreserveorder"]["transactions_cant_load_orderID"] ?>");
						return false;
					}
					
					// Do AJAX - Call
					new Ajax('http://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/changeOrderState.php?id='+orderID+'&state='+newState+'&group=state', {method: 'get',onComplete: function(el){
					switch (el){
						case "ERROR":
							parent.Growl("<?php echo $sLang["saferpayreserveorder"]["transactions_cant_refresh_status"] ?>");
						break;
						default:
							parent.Growl("<?php echo $sLang["saferpayreserveorder"]["transactions_status_order"] ?> "+orderNumber+" <?php echo $sLang["saferpayreserveorder"]["transactions_has_left"] ?> "+newStateDescription+" <?php echo $sLang["saferpayreserveorder"]["transactions_changed"] ?>");
					}
					
					},}).request();
					// Give response to user
					
					// Dispatch event, hide layer	
				
				}
				function refreshPaymentChange (ev){
					// Building lookup-table for states
					var  xLookupPayment = new Array();
					<?php
					foreach ($translationPayment as $key => $value){
						?>
						xLookupPayment[<?php echo $key ?>] = <?php echo $value ?>;
						<?php
					}
					?>
					// Get values
					var newState = ev.target.value;
					
					var newStateDescription = 	$('ajaxChangePaymentSelect').options[xLookupPayment[newState]].innerHTML;
					if (!newStateDescription){
						alert ("<?php echo $sLang["saferpayreserveorder"]["transactions_cant_load_Description"] ?>");
						return false;
					}
					this.cols[4].element.setHTML(newStateDescription);
					this.data.paymentNumeric = newState;
					// Get Order - ID
					//console.log(this);
					var orderID = this.data.orderID;
					var orderNumber = this.data.ordernumber;
					if (!orderID){
						alert ("<?php echo $sLang["saferpayreserveorder"]["transactions_cant_load_orderID"] ?>");
						return false;
					}

					
					// Do AJAX - Call
					new Ajax('http://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/changeOrderState.php?id='+orderID+'&state='+newState+'&group=payment', {method: 'get',onComplete: function(el){
					switch (el){
						case "ERROR":
							parent.Growl("<?php echo $sLang["saferpayreserveorder"]["transactions_cant_refresh_status"] ?>");
						break;
						default:
							parent.Growl("<?php echo $sLang["saferpayreserveorder"]["transactions_status_order"] ?> "+orderNumber+" <?php echo $sLang["saferpayreserveorder"]["transactions_has_left"] ?> "+newStateDescription+" <?php echo $sLang["saferpayreserveorder"]["transactions_changed"] ?>");
					}
					
					},}).request();
					// Give response to user
					
					// Dispatch event, hide layer	
				}

window.addEvent('load',function(){

				mootable = new MooTable( 'test', {debug: false, height: '360px', headers: headers, sortable: true, useloading: false, resizable: false});
				mootable.addEvent( 'afterRow', function(data, row){					
					row.cols[3].element.setStyle('cursor', 'pointer');
					row.cols[4].element.setStyle('cursor', 'pointer');
					// Remove formating from status-value
					row.cols[3].element.setHTML(row.cols[3].value);
					row.cols[4].element.setHTML(row.cols[4].value);
					// Markup row if status is not eqal finished
					switch (row.data.stateNumeric){
						case "2":
							row.cols[3].element.setStyle('color','#009933');
							break;
						case "4":
							row.cols[3].element.setStyle('color','#FF0000');
							break;
						case "8":
							row.cols[3].element.setStyle('color','#FF0000');
							break;
						default:
							row.cols[3].element.setStyle('color','#009933');
							break;
					}
					
					// Markup - payment row 
					switch (row.data.paymentNumeric){
						case "16":
							row.cols[4].element.setStyle('color','#FF0000');
							break;
						case "25":
							row.cols[4].element.setStyle('color','#FF0000');
							break;
						default:
							row.cols[4].element.setStyle('color','#009933');
							break;
					}
					
					
					// Bind management-function to row
					row.cols[3].element.addEvent( 'dblclick', manageStateChange.bind(row) );
					row.cols[3].element.addEvent( 'click', function (el){$('ajaxChangeState').setStyle('display','none');}.bind(row) );

					// Same for change payment - state
					row.cols[4].element.addEvent( 'click', function (el){$('ajaxChangePayment').setStyle('display','none');}.bind(row) );
					row.cols[4].element.addEvent( 'dblclick', managePaymentChange.bind(row) );
				});

				mootable.loadData( data );

				});
		</script>
</body>
</html>