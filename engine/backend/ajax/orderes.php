<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
include("json.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

function format_price ($i)
{
	/*
	if ($i < 0.009 && $i != 0)
		return $i;
	else 
		return sprintf("%01.2f", $i);*/
	return $i;
}

function recalcInvoiceAmount()
{
	//Net
	$sql0 = "SELECT `net` FROM `s_order`
			WHERE `id` = {$_REQUEST['id']}
			LIMIT 1";
	$query0 = mysql_query($sql0);
	$res0 = mysql_fetch_array($query0);
	$net = $res0['net'];
	if($net == 0)
	{
		//Berechnung von Brutto Gesamt
		$sql = "SELECT 
				SUM(det.price*det.`quantity`)+ord.invoice_shipping AS new_invoice_amount  
				
				FROM `s_order_details` AS det
				LEFT JOIN `s_order` AS ord ON(ord.`ordernumber` = det.`ordernumber`)
				WHERE det.`orderID` = {$_REQUEST['id']}";
		$query = mysql_query($sql);
		$invoice_amount = mysql_fetch_array($query);
		$db_amount = $invoice_amount['new_invoice_amount'];
		
		//Berechnung von Netto Gesamt
		$invoice_amount_net = 0;
		$sql2 = "SELECT (details.price * details.quantity) AS total, 
		tax.tax, 
		`order`.invoice_shipping_net
		FROM `s_order_details` AS details
		LEFT JOIN `s_core_tax` AS tax ON ( tax.id = details.taxID )
		LEFT JOIN `s_order` AS `order` ON ( `order`.ordernumber = details.ordernumber )
		WHERE details.`orderID` = {$_REQUEST['id']}";
		$query2 = mysql_query($sql2);
		while($data = mysql_fetch_array($query2))
		{
			$invoice_shipping_net = $data['invoice_shipping_net'];
			
			$data['tax'] == null ? $tax = 19 : $tax = $data['tax'];
			$plus = $data['total']/(100+$tax)*100;
			$invoice_amount_net = $invoice_amount_net+$plus;
		}
		$invoice_amount_net = $invoice_amount_net+$invoice_shipping_net;
		$db_amount_net = $invoice_amount_net;
	}else{
		//Berechnung von Brutto Brutto
		$sql = "SELECT 
				SUM(det.price*det.`quantity`)+ord.invoice_shipping_net AS new_invoice_amount_net  
				
				FROM `s_order_details` AS det
				LEFT JOIN `s_order` AS ord ON(ord.`ordernumber` = det.`ordernumber`)
				WHERE det.`orderID` = {$_REQUEST['id']}";
		$query = mysql_query($sql);
		$invoice_amount_net = mysql_fetch_array($query);
		$db_amount_net = $invoice_amount_net['new_invoice_amount_net'];
		
		//Berechnung von Netto Gesamt
		$invoice_amount_net = 0;
		$sql2 = "SELECT (details.price * details.quantity) AS total, 
		tax.tax, 
		`order`.invoice_shipping
		FROM `s_order_details` AS details
		LEFT JOIN `s_core_tax` AS tax ON ( tax.id = details.taxID )
		LEFT JOIN `s_order` AS `order` ON ( `order`.ordernumber = details.ordernumber )
		WHERE details.`orderID` = {$_REQUEST['id']}";
		$query2 = mysql_query($sql2);
		while($data = mysql_fetch_array($query2))
		{
			$invoice_shipping = $data['invoice_shipping'];
			
			$data['tax'] == null ? $tax = 19 : $tax = $data['tax'];
			$plus = $data['total']/100*(100+$tax);
			$invoice_amount_net = $invoice_amount_net+$plus;
		}
		$invoice_amount_net = $invoice_amount_net+$invoice_shipping;
		$db_amount = $invoice_amount_net;
	}
	$sql_update = sprintf("UPDATE `s_order` SET 
						`invoice_amount` = '%s',
						`invoice_amount_net` = '%s' 
						WHERE `id` = '%s' LIMIT 1",
						$db_amount,
						$db_amount_net,
						$_REQUEST['id']);
	mysql_query($sql_update);
		
}

if (!isset($_REQUEST['action']) || !is_numeric($_REQUEST['id']))
{
	echo "FAIL[{$_REQUEST['id']}][{$_REQUEST['action']}]";
	die();
}
if ($_REQUEST['action'] == 'saveDetails')
{
	foreach ($_REQUEST['quantity'] as $key => $value)
	{
	$price = floatval(str_replace(',', '.',$_REQUEST['price'][$key]));
	$quantity = intval($_REQUEST['quantity'][$key]);
	$status = intval($_REQUEST['status'][$key]);
	if($status==2)
		$quantity=0;
	if ($price >= 0){
		$sql = "
		UPDATE 
			s_articles_details,
			s_order_details
		SET 
			s_articles_details.instock=
			s_articles_details.instock-({$quantity}-s_order_details.quantity)
		WHERE 
			s_articles_details.ordernumber=s_order_details.articleordernumber 
		AND 
			s_order_details.id={$key}
		AND 
			s_order_details.esdarticle!=1";
		
		mysql_query($sql);
	}
	$sql = "
		UPDATE `s_order_details` SET 
			`quantity` = '$quantity',
			`price` = '$price',
			`status` = '$status'
		WHERE id={$key} LIMIT 1";
	$result = mysql_query($sql);
	$newAmount += $quantity*$price;
	
	}
	$invoice_shipping = mysql_query("
		SELECT invoice_shipping FROM s_order WHERE id={$_REQUEST["id"]}
	");
	$invoice_shipping = mysql_fetch_row($invoice_shipping);
	$newAmount += $invoice_shipping[0];
	
	$updateOrder = mysql_query("
		UPDATE s_order SET invoice_amount=$newAmount WHERE id={$_REQUEST["id"]}
	");
}

if (!empty($_REQUEST['name']) && !empty($_REQUEST['artnumber']))
{
	
}

if ($_REQUEST['action'] == 'getDetails' || $_REQUEST['action'] == 'saveDetails')
{
// Fetch currency
$sql = "
SELECT s_core_currencies.currency AS currency, templatechar FROM s_core_currencies, s_order WHERE s_order.id={$_REQUEST["id"]}
AND s_order.currency = s_core_currencies.currency
";
//echo $sql;
$getCurrency = mysql_query($sql);
$getCurrency = @mysql_result($getCurrency,0,"templatechar");

$sql = "SELECT * FROM  s_order_details WHERE orderID={$_REQUEST['id']} ORDER BY modus ASC";
$result = mysql_query($sql);

if (!$result){
	echo("FAIL");
	die();
}
if (mysql_num_rows($result)<=0){
	echo("FAIL");
	die();
}
$options = array(
	0 => "Offen",
	1 => "In Bearbeitung",
	2 => "Storniert",
	3 => "Abgeschlossen"
);
while ($entry = mysql_fetch_assoc($result))
{
	$data['id'] = htmlentities($entry['articleordernumber']);
	$entry['name'] = htmlspecialchars(str_replace(array('\n','\r','<br>','<br />'),'',$entry['name']));
	$data['name'] = "<a onclick=\"parent.loadSkeleton('articles',false, '{article:".$entry['articleID']."}')\" style=\"cursor: pointer;\">".htmlentities($entry['name'])."</a>";
	$data['quantity'] = "<input style=\"height:20px\" id=\"{$entry['id']}\" value=\"{$entry['quantity']}\" name=\"quantity[{$entry['id']}]\" onkeyup=\"calculate(this);\">";
	$data['price'] = "<input style=\"height:20px\" id=\"{$entry['id']}\" value=\"".format_price($entry['price'])."\" name=\"price[{$entry['id']}]\" onkeyup=\"calculate(this);\"> $getCurrency";
	$data['amount'] = ($entry['price'] * $entry['quantity']);
	$data['amount'] = "<input style=\"height:20px\" id=\"amount_{$entry['id']}\" value=\"".format_price($data['amount'])."\" name=\"amount[{$entry['id']}]\" disabled=\"disabled\"> $getCurrency";
	$data['status'] = "<select style=\"height:20px\" name=\"status[{$entry['id']}]\">";
	foreach ($options as $key => $value)
	{
		if ($entry['status'] == $key) 
			$data['status'] .= "<option selected=\"selected\" value=\"$key\">$value</option>";
		else
			$data['status'] .= "<option value=\"$key\">$value</option>";
	}
	$data['status'] .= "</select>";
	$data['options2'] = "<a href=\"#\" onclick=\"deleteOrder()\" style=\"cursor: pointer;\" class=\"ico cross\"></a>";
	$ret[] = $data;
	//disabled=\"disabled\"
}
}
elseif ($_REQUEST['action'] == 'deletePositionForExt')
{
	$sql = "DELETE FROM `s_order_details` WHERE id = '{$_REQUEST["s_order_details_id"]} LIMIT 1'";
	mysql_query($sql);
	//Gesamt netto/brutto aktualisieren
	recalcInvoiceAmount();
}
elseif ($_REQUEST['action'] == 'getDetailsForExt')
{
	// Fetch currency
	$sql = "
	SELECT s_core_currencies.currency AS currency, templatechar FROM s_core_currencies, s_order WHERE s_order.id={$_REQUEST["id"]}
	AND s_order.currency = s_core_currencies.currency
	";
	//echo $sql;
	$getCurrency = mysql_query($sql);
	$getCurrency = mysql_fetch_array($getCurrency);
	
	$sql = "SELECT * FROM  s_order_details WHERE orderID={$_REQUEST['id']} ORDER BY modus ASC";
	$result = mysql_query($sql);
	
	if (!$result){
		echo("FAIL");
		die();
	}
	if (mysql_num_rows($result)<=0){
		echo("FAIL");
		die();
	}
	
	while ($entry = mysql_fetch_assoc($result))
	{
	
		$sql_article = "SELECT instock FROM `s_articles_details` WHERE 
						`ordernumber` ='{$entry['articleordernumber']}'";
		$query_article = mysql_query($sql_article);
		$result_article = mysql_fetch_assoc($query_article);
		
		$instock = "";
		$sql = sprintf("SELECT instock FROM `s_articles_details` WHERE `ordernumber` = '%s'", $entry['articleordernumber']);
		$query = mysql_query($sql);
		if(mysql_num_rows($query) != 0)
		{
			$data = mysql_fetch_array($query);
			$instock = $data['instock'];
		}
		$sql = sprintf("SELECT instock FROM `s_articles_groups_value` WHERE `ordernumber` = '%s'", $entry['articleordernumber']);
		$query = mysql_query($sql);
		if(mysql_num_rows($query) != 0)
		{
			$data = mysql_fetch_array($query);
			$instock = $data['instock'];
		}

		$entry['taxID'] == 0 ? $taxID = 1 : $taxID = $entry['taxID'];
		//Skonto
		if($entry['modus'] == 4) $taxID = "";
		
		$data['id'] 		= $entry['id'];
		$data['articleID'] 	= $entry['articleID'];
		$data['articleordernumber'] = utf8_encode(htmlentities($entry['articleordernumber']));
		$data['name'] = utf8_encode(htmlspecialchars(str_replace(array('\n','\r','<br>','<br />'),'',$entry['name'])));
		$data['quantity'] = $entry['quantity'];
		$data['price'] = ereg_replace("\.",",", $entry['price']);
		$data['total'] = ($entry['price'] * $entry['quantity']);
		$data['total'] = ereg_replace("\.",",", $data['total']);
		$data['status'] = $entry['status'];
		$data['tax'] = $entry['taxID'];
		$data['instock'] = $instock;
		$data['instock_save'] = $instock;
		$data['options2'] = "<a href=\"#\" onclick=\"deleteOrder()\" style=\"cursor: pointer;\" class=\"ico cross\"></a>";
		$data['templatechar'] = $getCurrency['templatechar'];
		$ret[] = $data;
	}
}
elseif ($_REQUEST['action'] == 'newEntryForExt')
{	
	$sql2 = sprintf("SELECT `ordernumber` FROM `s_order` WHERE `id` = '%s' LIMIT 1",
			$_REQUEST['id']);
	$query = mysql_query($sql2);
	$result = mysql_fetch_array($query);
	
	$sql = sprintf("INSERT INTO `s_order_details` 
			(`orderID`, `ordernumber`, `taxID`) VALUES 
			('%s', '%s', 1)", 
			$_REQUEST['id'],
			$result['ordernumber']);
	mysql_query($sql);
	recalcInvoiceAmount();
}
elseif ($_REQUEST['action'] == 'saveOrderForExt')
{		
	$json = json_decode($_POST[rec]);
		
	if($json->id == "new")
	{
		//auslesen der ordernummer für den Abgleich mit s_order
//		$sql2 = sprintf("SELECT `ordernumber` FROM `s_order_details` WHERE `orderID` = '%s' LIMIT 1",
//				$json->orderID);
//		$query = mysql_query($sql2);
//		$result = mysql_fetch_array($query);
//		
//		$sql = sprintf("INSERT INTO `s_order_details` 
//				(`orderID`, `articleordernumber`, `ordernumber`, `name`, `quantity`, `price`, `status`, `taxID`) VALUES 
//				('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
//				$json->orderID,
//				$json->articleordernumber,
//				$result["ordernumber"],
//				$json->name,
//				$json->quantity,
//				ereg_replace(",",".", $json->price),
//				$json->price,
//				$json->status,
//				$json->tax);
//		mysql_query($sql);
	}else{
		//Skonto
		$json->tax == "" ? $taxID = 0 : $taxID = $json->tax;
		
		$sql = sprintf("
			UPDATE `s_order_details` SET 
				`articleordernumber` = '%s',
				`price` = '%s',
				`quantity` = '%s',
				`name` = '%s',
				`status` = '%s',
				`taxID` = '%s'
			WHERE id='%s' LIMIT 1",
			utf8_decode($json->articleordernumber),
			utf8_decode(ereg_replace(",",".", $json->price)),
			utf8_decode($json->quantity),
			utf8_decode($json->name),
			utf8_decode($json->status),
			utf8_decode($taxID),
			$json->id);
		$result = mysql_query($sql);
			
		//Update Instock
		$instock = null;
		$sql = sprintf("SELECT instock FROM `s_articles_details` WHERE `ordernumber` = '%s'", $json->articleordernumber);
		$query = mysql_query($sql);
		if(mysql_num_rows($query) != 0)
		{
			$data = mysql_fetch_array($query);
			$table = 's_articles_details';
			$instock = $data['instock'];
		}
		$sql = sprintf("SELECT instock FROM `s_articles_groups_value` WHERE `ordernumber` = '%s'", $json->articleordernumber);
		$query = mysql_query($sql);
		if(mysql_num_rows($query) != 0)
		{
			$data = mysql_fetch_array($query);
			$table = 's_articles_groups_value';
			$instock = $data['instock'];
		}
		
		if($instock != null)
		{
			//Lagerbestanddifferenz berechnen
			$vorher = $json->instock_save;
			$nachher = $json->instock;
			$diff = $nachher-$vorher;
			$aktuell = $instock;
			
			$update = $aktuell+$diff;
			
			$sql = "UPDATE $table SET `instock` = '{$update}' 
				WHERE `ordernumber` = '{$json->articleordernumber}' LIMIT 1";
				mysql_query($sql);
		}
		
		
		
		//Netto/Brutto aktualisieren
		$sql = "SELECT orderID FROM `s_order_details` WHERE id = {$json->id}";
		$query = mysql_query($sql);
		$result = mysql_fetch_array($query);
		
		$_REQUEST['id'] = $result['orderID'];
		recalcInvoiceAmount($json->id);
	}
		
}
elseif ($_REQUEST['action'] == 'test')
{
	$vorher = 0;
	$nachher = -150;
	
	$diff = $nachher-$vorher;
	echo $diff;
	
	$aktuell = 100;
	echo "<br><br>";
	echo $aktuell+$diff;
	echo "<br><br>";
}
else
{
	echo "FAIL";
}
$json = new Services_JSON();
echo $json->encode($ret);
?>