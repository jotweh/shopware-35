<?php

define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
include("json.php");

if ($result!="SUCCESS"){
	echo("FAIL");
	die();
}

$typen = array(
	0 => 'Rechnung',
	1 => 'Lieferschein',
	2 => 'Gutschrift',
	3 => 'Stornierung');
$queryCurrency = mysql_query("SELECT s_core_currencies.currency AS currency, templatechar FROM s_order, s_core_currencies WHERE
s_order.currency = s_core_currencies.currency AND s_order.id = {$_REQUEST["id"]}
");
$queryCurrency = mysql_fetch_assoc($queryCurrency);

if (!isset($_REQUEST['action']))
{
	$sql = "SELECT ID, DATE_FORMAT(date,'%d.%m.%Y') AS dateFormated, type, userID, orderID, amount, docID,hash FROM s_order_documents WHERE orderID={$_REQUEST["id"]} ORDER BY type ASC LIMIT 0 , 3000";
	
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
		//$data['ico'] = "<a href=\"../../../files/documents/{$entry['ID']}.pdf\" class=\"ico page_white_acrobat\" style=\"cursor:pointer\" onclick=\"parent.Growl('Beleghandling in Demo gesperrt')\"></a>";
		$data['ico'] = "<a  class=\"ico page_white_acrobat\" style=\"cursor:pointer\" href=\"openPDF.php?pdf={$entry['ID']}\" target=\"_blank\"></a>";
		if (get_magic_quotes_gpc()){
			$data['ico']  = stripslashes($data['ico']);
		}
		$data['datum'] = $entry['dateFormated'];
		$data['beleg'] = $typen[$entry['type']] . " " . sprintf("%08d",$entry['docID']);
		$data['amount'] = number_format($entry['amount'], 2, ",", '')." {$queryCurrency["templatechar"]}";
		$data["hash"] = $entry["hash"];
		$ret[] = $data;
		
		//Ausgabe für ExtJS
		
		$ext['id'] = $entry['ID'];
		$ext['hash'] = $entry['hash'];
		$ext['datum'] = $entry['dateFormated'];
		$ext['beleg'] = $typen[$entry['type']] . " " . sprintf("%08d",$entry['docID']);
		$ext['amount'] = number_format($entry['amount'], 2, ",", '')." {$queryCurrency["templatechar"]}";
		
		//Sonderfall Lieferschein
		if($entry['type'] == 1)
		{
			$ext['amount'] = "-";
		}
		//Sonderfall Stornierung
		if($entry['type'] == 3)
		{
			$ext['beleg'] = $typen[$entry['type']];
		}
		$ret_ext[] = $ext;
	}
	if($_REQUEST['type'] == 'forExt' && $_REQUEST['loading'] == 'true')
	{
		unset($ret_ext);
		$ext['id'] = 0;
		$ext['datum'] = '-';
		$ext['beleg'] = $_REQUEST["loading_text"];
		$ext['amount'] = "0,00 &euro;";
		$ret_ext[] = $ext;
	}
}
elseif ($_REQUEST['action']=='getDetails')
{

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
	$data['id'] = htmlentities($entry['articleordernumber']);
	$entry['name'] = str_replace(array('\n\r','\n','\r','<br>','<br />'),' ',$entry['name']);
	$data['name'] = htmlentities($entry['name']);
	$data['quantity'] = "<input id=\"d{$entry['id']}\" style=\"width: 40px;\" value=\"{$entry['quantity']}\" name=\"quantity_d{$entry['id']}\" onkeyup=\"calculate(this);\">";
	$data['price'] = "<input id=\"d{$entry['id']}\" style=\"width: 60px;\" value=\"".format_price($entry['price'])."\" name=\"price_d{$entry['id']}\" onkeyup=\"calculate(this);\"> &euro;";
	$data['amount'] = ($entry['price'] * $entry['quantity']);
	$data['amount'] = "<input id=\"amount_d{$entry['id']}\" style=\"width: 60px;\" value=\"".format_price($data['amount'])."\" name=\"amount_d{$entry['id']}\" disabled=\"disabled\"> &euro;";
	$ret[] = $data;
	
	//Ausgabe für ExtJS
	$ext['id'] = htmlentities($entry['articleordernumber']);
	$ext['name'] = htmlentities($entry['name']);
	$ext['quantity'] = "<input id=\"d{$entry['id']}\" style=\"width: 40px;\" value=\"{$entry['quantity']}\" name=\"quantity_d{$entry['id']}\" onkeyup=\"calculate(this);\">";
	$ext['price'] = "<input id=\"d{$entry['id']}\" style=\"width: 60px;\" value=\"".format_price($entry['price'])."\" name=\"price_d{$entry['id']}\" onkeyup=\"calculate(this);\"> &euro;";
	$ext['amount'] = ($entry['price'] * $entry['quantity']);
	$ext['amount'] = "<input id=\"amount_d{$entry['id']}\" style=\"width: 60px;\" value=\"".format_price($data['amount'])."\" name=\"amount_d{$entry['id']}\" disabled=\"disabled\"> &euro;";
	$ret_ext[] = $ext;
	//disabled=\"disabled\"
}

}
elseif ($_REQUEST['action']=='createPDF')
{
	/*if(isset($_REQUEST['id'])) if(is_numeric($_REQUEST['id'])) $sBillingID = $_REQUEST['id'];
	//if(isset($_REQUEST['typ'])) if(file_exists($sTemplatePath.'/content_'.$_REQUEST['typ'].'.tpl')) $sTyp = $_REQUEST['typ'];
	$sTyp = 0;
	$sSettings['date'] = date("d.m.Y");
	include("../../backend/php/sCreateDocuments.php");
	*/
	
}
else 
	die();

	function format_price ($i)
	{
		/*
		if ($i < 0.009 && $i != 0)
			return $i;
		else 
			return sprintf("%01.2f", $i);*/
		return $i;
	}
	
$json = new Services_JSON();
if($_POST["type"] == "forExt")
{
	echo $json->encode($ret_ext);
}elseif ($_POST["type"]=="forExt3"){
	
	echo $json->encode(array("count"=>count($ret_ext),"documents"=>$ret_ext));
}
else{
	
	echo $json->encode($ret);
}

?>