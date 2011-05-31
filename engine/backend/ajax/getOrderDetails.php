<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
include("json.php");
$result = new checkLogin();
$result = $result->checkUser();

require_once("../../backend/ajax/json.php");
$json = new Services_JSON();

if ($result!="SUCCESS"){
	die("FAIL");
}
if(!empty($_REQUEST['id'])) $_REQUEST['orderID'] = $_REQUEST['id'];
if (empty($_REQUEST['orderID']) || !is_numeric($_REQUEST['orderID'])){
	die("FAIL");
}

$sql = "
	SELECT
		od.*,
		IF(g.instock IS NULL,d.instock,g.instock) as instock,
		od.taxID,
		t.tax,
		od.id,
		od.status,
		o.currency,
		o.currencyFactor,
		o.taxfree
	FROM  s_order o,s_order_details od
	LEFT JOIN s_articles_groups_value g
	ON g.ordernumber=od.articleordernumber
	AND od.modus=0
	AND g.articleID=od.articleID
	LEFT JOIN s_articles_details d
	ON d.ordernumber=od.articleordernumber
	AND od.modus=0
	AND d.articleID=od.articleID
	LEFT JOIN s_articles a
	ON a.id=od.articleID
	LEFT JOIN s_core_tax AS t
	ON t.id = od.taxID
	WHERE orderID={$_REQUEST['orderID']}
	AND o.id = od.orderID
	ORDER BY od.modus, od.id";

$result = mysql_query($sql);
if (!$result||!mysql_num_rows($result)){
	die("FAIL");
}

$options = array(
	0 => "Offen",
	1 => "In Bearbeitung",
	2 => "Storniert",
	3 => "Abgeschlossen"
);
$data = array();
while ($entry = mysql_fetch_assoc($result))
{
	
	if(empty($entry['modus']))
		$entry['status_description'] = $options[$entry['status']];
	if(empty($entry['taxID']))
		$entry['taxID'] = 1;
	if(empty($entry['tax'])){
		if ($entry["modus"] == 4 || $entry["modus"] == 3){
			if (!empty($sCore->sCONFIG["sTAXAUTOMODE"])){
				$entry['tax'] = getMaxTax($_REQUEST['orderID']);
			}else {
				$entry['tax'] = $sCore->sCONFIG["sDISCOUNTTAX"];
		}
		}elseif ($entry["modus"] == 2){
			$ticketResult = mysql_query("
			SELECT * FROM s_emarketing_vouchers WHERE ordercode='{$entry["articleordernumber"]}'
			");
			$ticketResult = mysql_fetch_assoc($ticketResult);
			
			if ($ticketResult["taxconfig"] == "default" || empty($ticketResult["taxconfig"])){
				$entry['tax'] = $sCore->sCONFIG['sVOUCHERTAX'];
				// Pre 3.5.4 behaviour
			}elseif ($ticketResult["taxconfig"]=="auto"){
				// Check max. used tax-rate from basket
				$entry['tax'] = getMaxTax($_REQUEST['orderID']);
			}elseif (intval($ticketResult["taxconfig"])){
				// Fix defined tax
				$temporaryTax = $ticketResult["taxconfig"];
				$getTaxRate = mysql_fetch_assoc(mysql_query("
				SELECT tax FROM s_core_tax WHERE id = $temporaryTax
				"));
				$entry['tax'] = $getTaxRate["tax"];
			}else {
				$entry['tax'] = 0;
			}
			
		}else {
			$entry['tax'] = $sCore->sCONFIG['sDISCOUNTTAX'];
		}
	}

	if (!empty($entry["taxfree"])) $entry["tax"] = "0";
	$entry['tax'] = $entry['tax']." %";
	if(!empty($entry['priceCalc1SourcePrice']))
		$entry['priceCalc1SourcePrice'] = number_format($entry['priceCalc1SourcePrice'],2,",","")." &euro;";
	$entry['amount'] = number_format(($entry['price']/$entry["currencyFactor"])*$entry['quantity'],2,",","")."";
	$entry['price'] = number_format(($entry['price']/$entry["currencyFactor"]),2,",","");
	$entry['name'] = utf8_encode(htmlentities(html_entity_decode(str_replace(array('\r\n','\r','<br>','<br />'),"\n",$entry['name']))));
	while(strpos($entry['name'],"\n\n")!==false)
		$entry['name'] = str_replace("\n\n","\n",$entry['name']);
	$entry['articleordernumber'] = utf8_encode(htmlentities($entry['articleordernumber']));
	$entry['instock_save'] = $entry['instock'];
	$entry['total'] = $entry['amount'];
	$entry['options2'] = "<a href=\"#\" onclick=\"deleteOrder()\" style=\"cursor: pointer;\" class=\"ico cross\"></a>";
	$entry['templatechar'] = " ".htmlentities($entry["currency"]);
	$data[] = $entry;
}
function getMaxTax($orderID){
	$sql = "
	SELECT
		od.*,
		IF(g.instock IS NULL,d.instock,g.instock) as instock,
		od.taxID,
		t.tax,
		od.id,
		od.status,
		o.currency,
		o.currencyFactor,
		o.taxfree
	FROM  s_order o,s_order_details od
	LEFT JOIN s_articles_groups_value g
	ON g.ordernumber=od.articleordernumber
	AND od.modus=0
	AND g.articleID=od.articleID
	LEFT JOIN s_articles_details d
	ON d.ordernumber=od.articleordernumber
	AND od.modus=0
	AND d.articleID=od.articleID
	LEFT JOIN s_articles a
	ON a.id=od.articleID
	LEFT JOIN s_core_tax AS t
	ON t.id = od.taxID
	WHERE orderID=$orderID
	AND o.id = od.orderID
	ORDER BY od.modus, od.id";
	
	$result = mysql_query($sql);
	$maxTax = 0;
	while ($check = mysql_fetch_assoc($result)){
		if ($check["tax"] > $maxTax) $maxTax = $check["tax"];
	}
	return $maxTax;
}
echo $json->encode(array("articles"=>$data,"count"=>count($data)));
?>