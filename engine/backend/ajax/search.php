<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

header("Content-Type: text/javascript;utf-8");

if (empty($_GET["search"])||!trim($_GET["search"])) die('FAIL');

$search = stripslashes($_GET["search"]);
$search = strtolower($search);
$search = trim($search);



$search = preg_replace("/[^a-z0-9הצüß]/", " ", $search);
$search = trim(preg_replace('/\s\s+/', ' ', $search),"%");
$search = str_replace(" ","%",$search);
$search = mysql_real_escape_string($search);

$_GET["search"] = mysql_real_escape_string(htmlspecialchars($_GET["search"]));



		$sql = "
			SELECT DISTINCT
				a.id,
				a.name
			FROM 
				s_articles as a
			INNER JOIN s_articles_details as d
				ON a.id = d.articleID
			LEFT JOIN s_articles_translations AS t
				ON a.id=t.articleID
			LEFT JOIN s_articles_groups_value AS v
				ON a.id = v.articleID AND v.ordernumber LIKE '%$search%'	
			WHERE 
				(
						a.name LIKE '%$search%' 
					OR
						d.ordernumber LIKE '%$search%'
					OR
						t.name LIKE '%$search%' 
					OR
						v.ordernumber != ''
				)
			LIMIT 5
		";
		//ORDER BY a.changetime DESC

$queryArticles = mysql_query($sql);

if (@mysql_num_rows($queryArticles)){
	while ($article=mysql_fetch_array($queryArticles)){
		
		
		$article["name"] = html_entity_decode($article["name"]);
		$article["name"] = strip_tags($article["name"]);
		//$article["name"] = stripslashes($article["name"]);
		$article["name"] = substr($article["name"],0,45);
		//$article["name"] = utf8_encode($article["name"]);
		//$article["name"] = htmlspecialchars($article["name"]);
		
		$articles .= "<li onclick=\"loadSkeleton('articles',false, {'article':{$article["id"]}});\"><a href=\"#\" style=\"font-size:9px\">{$article["name"]}</a></li>";
	}
}else {
	#$articles = "<li ><a href=\"#\">Keine Treffer</a></li>";
}
//lastname LIKE '%$search%' OR company LIKE '%$search%'	OR 

$sql = "OR customernumber LIKE '$search%'";

$sql = "
SELECT DISTINCT userID, firstname, lastname, company 
FROM s_user_billingaddress, s_user 
WHERE (
	email LIKE '%$search%'
	$sql
	OR TRIM(CONCAT(company,' ',department)) LIKE '%$search%'
	OR TRIM(CONCAT(firstname,' ',lastname)) LIKE '%$search%'
)
AND s_user.id=s_user_billingaddress.userID
GROUP BY userID
ORDER BY lastname, company ASC
LIMIT 5";

//die($sql);

$queryCustomer = mysql_query($sql);



if (@mysql_num_rows($queryCustomer)){
	while ($customer=mysql_fetch_array($queryCustomer)){
		if ($customer["company"]){
			$customer["name"] = $customer["company"];
		}else {
			$customer["name"] = $customer["firstname"]." ".$customer["lastname"];
		}
		$userids[] = $customer["userID"];
	
		//$customer["name"] = str_replace("צ","&ouml;",$customer["name"]);
		//$customer["name"] = htmlentities($customer["name"]);
		$customer["name"] = strip_tags($customer["name"]);
		$customer["name"] = substr($customer["name"],0,30);
		//$customer["name"] = utf8_encode($customer["name"]);
		//$customer["name"] = htmlspecialchars($customer["name"]);
		
		
		//$customer["name"] = "";
		$customers .= "<li onclick=\"loadSkeleton('userdetails',false, {'id':{$customer["userID"]}})\"><a href=\"#\" style=\"font-size:9px\">{$customer["name"]}</a></li>";
	}
}else {
	#$customers = "<li ><a href=\"#\">Keine Treffer</a></li>";
}
if(count($userids)&&strlen($_GET["search"])>3)
{

	$sqlu = "OR s_order.userID = '";
	$sqlu .=  implode("'\n\tOR s_order.userID = '",$userids);
	$sqlu .= "'";
}

$sqld =  "s_order.ordernumber LIKE '$search%' OR s_order.transactionID LIKE '$search%' OR docID LIKE '$search%'";

if(!empty($sqld)||!empty($sqlu))
{
	$sql = 
		"SELECT 
			s_order.id,
			s_order.ordernumber,
			s_order.userID,
			s_order.invoice_amount,
			s_order.transactionID,
			`status`,
			`cleared`,
			`type`,
			docID 
		FROM 
			s_order
		LEFT JOIN s_order_documents
		ON s_order_documents.orderID=s_order.id AND docID != '0'
		WHERE
		(
			$sqld
			$sqlu
		) AND  s_order.ordernumber != '0'
		
		GROUP BY s_order.id
		ORDER BY s_order.ordertime DESC 
		LIMIT 3
	";
		//die($sql);
	$queryOrders = mysql_query($sql);
	if ($queryOrders&&mysql_num_rows($queryOrders)){
		while ($order=mysql_fetch_array($queryOrders)){
			$orders .= "<li onclick=\"loadSkeleton('orders',false,{'id':{$order["id"]}})\"><a href=\"#\" style=\"font-size:9px\">Bestellung: {$order["ordernumber"]}</a></li>";
		}
	}
}
$sql = 
"SELECT 
	s_order.id,
	s_order.ordernumber,
	s_order.userID,
	s_order.invoice_amount,
	`status`,
	`cleared`,
	`type`,
	docID 
FROM 
	s_order, s_order_documents
WHERE
s_order_documents.orderID=s_order.id AND docID != '0' AND
(
	docID LIKE '$search%'
	OR s_order.ordernumber LIKE '$search%'$sqlu
)
ORDER BY ordertime DESC LIMIT 3";

$typen = array(
	0 => 'Rechnung',
	1 => 'Lieferschein',
	2 => 'Gutschrift',
	3 => 'Stornierung');

$queryOrders = mysql_query($sql);
if ($queryOrders&&mysql_num_rows($queryOrders)){
	while ($order=mysql_fetch_array($queryOrders)){
		$orders .= "<li onclick=\"loadSkeleton('orders',false, {'id':{$order["id"]}})\"><a href=\"#\" style=\"font-size:9px\">{$typen[$order["type"]]}: {$order["docID"]}</a></li>";
	}
}
#if(empty($orders))
#	$orders = "<li ><a href=\"#\">Keine Treffer</a></li>";
?>
<?php if(!empty($articles)) {?>
<span>Artikel:</span>
<div class="line"></div>
<ul>
<?php echo utf8_encode($articles) ?>
</ul>
<?php } if(!empty($customers)) {?>
<span>Kunden:</span>
<div class="line"></div>
<ul>
<?php  echo utf8_encode($customers) ?>
</ul>
<?php } if(!empty($orders)) {?>
<span>Bestellungen:</span>
<div class="line"></div>
<ul>
<?php  echo $orders?>
</ul>
<?php } if(empty($articles)&&empty($customers)&&empty($orders)) {?>
<span>Keine Treffer</span>
<div class="line"></div>
<?php }?>