<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
header("Content-Type: text/javascript;utf-8");
switch ($_POST['suchmodus']){
	case 1:
	$sql = "
				SELECT a.categoryID AS category,a.id as articleID, a.active AS active,ordernumber,datum,additionaltext, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20, instock
				FROM s_articles AS a,
				s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
				s_articles_attributes AS aAttributes
				WHERE 
				a.taxID=aTax.id
				AND aAttributes.articledetailsID=aDetails.id
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 
				AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
				AND aPrices.to='beliebig'
				AND a.supplierID={$_POST["select_hersteller"]} 
				GROUP BY a.id ORDER BY a.datum DESC LIMIT 250
					";
	break;
	
	case 2:
	$sql = "
				SELECT a.categoryID AS category,a.id as articleID, a.active AS active, ordernumber,datum,additionaltext, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,instock
				FROM s_articles AS a,
				s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
				s_articles_attributes AS aAttributes
				WHERE 
				a.taxID=aTax.id
				AND aAttributes.articledetailsID=aDetails.id
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id
				AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
				AND aPrices.to='beliebig'
				AND aDetails.ordernumber LIKE '%{$_POST["select_bestellnummer"]}%' 
				GROUP BY a.id ORDER BY a.datum DESC LIMIT 250
					";
	break;
	
	case 3:
	$_POST["select_bezeichnung"] = trim($_POST["select_bezeichnung"]);
	$sql = "
				SELECT a.categoryID AS category,a.id as articleID, ordernumber,datum,a.active AS active, additionaltext, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20, instock
				FROM s_articles AS a,
				s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
				s_articles_attributes AS aAttributes
				WHERE 
				a.taxID=aTax.id
				AND aAttributes.articledetailsID=aDetails.id
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 
				AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
				AND aPrices.to='beliebig'
				AND a.name LIKE '%{$_POST["select_bezeichnung"]}%'
				GROUP BY a.id ORDER BY a.datum DESC LIMIT 250";
	break;
	
	case 4:
				$sql = "
				SELECT articleID, articleName, active, instock, price, tax, ordernumber
				FROM
				(
				SELECT s_articles.id AS articleID,taxID, s_articles.name AS articleName, s_articles.active AS active
				FROM s_articles
				LEFT JOIN 
					s_articles_categories ON s_articles_categories.articleID=s_articles.id
				WHERE 
					s_articles_categories.articleID IS NULL 
				ORDER BY s_articles.name
				) 
					AS `main`,
				(
				SELECT articleID AS id2, instock, ordernumber, kind
				FROM s_articles_details
				) 
					AS `details`,
				(
				SELECT articleID AS id3, price, pricegroup
				FROM s_articles_prices
				) 
					AS `prices`,
				(
				SELECT id AS taxid,tax
				FROM s_core_tax
				) 
					AS `taxtable`
				WHERE 
					main.articleID = details.id2
				AND
					main.articleID = prices.id3
				AND
					details.kind  =  1
				AND
					prices.pricegroup = 'EK'
				AND 
					main.taxID = taxtable.taxid
				GROUP BY
					ordernumber
				";
	break;
	case 5:
	$sql = "
				SELECT a.categoryID AS category,a.id as articleID, a.active AS active, ordernumber,datum,additionaltext, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20, s_export_articles.ID
				FROM s_articles AS a,
				s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
				s_articles_attributes AS aAttributes, s_export_articles
				WHERE 
				a.taxID=aTax.id
				AND aAttributes.articledetailsID=aDetails.id
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 
				AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
				AND aPrices.to='beliebig' 
				AND s_export_articles.portalID={$_POST['portal']}
				AND s_export_articles.articleID=a.id";
				foreach ($_POST['id'] as $ID)
	$sql.=			" AND s_articles.id='$ID' ";
	$sql.=		"GROUP BY a.id ORDER BY a.datum DESC LIMIT 100
					";
	break;
	// Search for articles which were not in stock
	case 6:
		$sql = "
				SELECT a.categoryID AS category,a.id as articleID, ordernumber,datum,a.active AS active, additionaltext, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20, instock
				FROM s_articles AS a,
				s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
				s_articles_attributes AS aAttributes
				WHERE 
				a.taxID=aTax.id
				AND aAttributes.articledetailsID=aDetails.id
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 
				AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
				AND aPrices.to='beliebig'
				AND instock <= 0
				GROUP BY a.id ORDER BY a.datum DESC LIMIT 250";
		break;
	case 7:
				$sql = "
				SELECT articleID, articleName, active, instock, price, tax, ordernumber
				FROM
				(
				SELECT s_articles.id AS articleID,taxID, s_articles.name AS articleName, s_articles.active AS active
				FROM s_articles
				LEFT JOIN 
					s_articles_img ON s_articles_img.articleID=s_articles.id
				WHERE 
					s_articles_img.articleID IS NULL 
				ORDER BY s_articles.name
				) 
					AS `main`,
				(
				SELECT articleID AS id2, instock, ordernumber, kind
				FROM s_articles_details
				) 
					AS `details`,
				(
				SELECT articleID AS id3, price, pricegroup
				FROM s_articles_prices
				) 
					AS `prices`,
				(
				SELECT id AS taxid,tax
				FROM s_core_tax
				) 
					AS `taxtable`
				WHERE 
					main.articleID = details.id2
				AND
					main.articleID = prices.id3
				AND
					details.kind  =  1
				AND
					prices.pricegroup = 'EK'
				AND 
					main.taxID = taxtable.taxid
				";
			break;
	default:
		echo "FAIL";
	die();
}

$abfrage = mysql_query($sql);
if (!$abfrage){ 
	echo "FAIL";
}
//echo mysql_error()."<br>".$sql; 

$countArticles = mysql_num_rows($abfrage);

if (!$countArticles){
	echo "FAIL";
}

if ($countArticles){
if(isset($_POST['portal'])){
echo "[";
while ($article = mysql_fetch_assoc($abfrage)){
	if ($article["active"]){
		$article["active"] = "<a class='ico accept'></a>";
	}else {
		$article["active"] = "<a class='ico exclamation'></a>";
	}
	
	//$price = $sCore->sCalculatingPrice($article["price"],$article["tax"]);
	//$price = round($article["price"]*(100+$article["tax"])/100,2);
	$price = $article["price"]*(100+$article["tax"])/100;
	$price = number_format($price, 2, '.', '');
	if ($_POST["suchmodus"]==4){
		
		
		$article["supplierName"] = "-";
	}
	$i++;
	if ($i==$countArticles){
		$comma = "";
	}else {
		$comma = ",";
	}
	
	$result = mysql_query("SELECT ID FROM s_export_articles WHERE portalID='{$_POST['portal']}' AND articleID='{$article['articleID']}'");
	if (mysql_num_rows($result) > 0)
		$options = '<a class=\"ico2 add\" style=\"cursor:pointer;padding:2 2 5 25\" onclick=\"toogleArticle('.$article['articleID'].',\''.$article['articleName'].'\',this)\">Freigeben</a>';
	else
		$options = '<a class=\"ico2 delete\" style=\"cursor:pointer;padding:2 2 5 25\" onclick=\"toogleArticle('.$article['articleID'].',\''.$article['articleName'].'\',this)\">Sperren</a>';
	?>
{"id":<?php echo $article["articleID"] ?>,"ID":"<?php echo $article["ordernumber"] ?>","Supplier":"<?php echo $article["supplierName"]?>","Article":"<?php echo $article["articleName"]?>","Price":"<?php echo $price ?> &euro;","Active":"<?php echo $article["active"]?>","options":"<?php echo$options?>"}<?php echo $comma ?>
	<?php 
	}
echo "]"; 
} else {

	echo "[";
		while ($article = mysql_fetch_array($abfrage)){
			
			$article['articleName'] = utf8_encode($article['articleName']);
			if ($article["active"]){
				$article["active"] = "<a class='ico accept'></a>";
			}else {
				$article["active"] = "<a class='ico exclamation'></a>";
			}
			
			//$price = $sCore->sCalculatingPrice($article["price"],$article["tax"]);
			//$price = round($article["price"]*(100+$article["tax"])/100,2);
			$price = $article["price"]*(100+$article["tax"])/100;
			$price = number_format($price, 2, '.', '');
			if ($_POST["suchmodus"]==4){
			
				$article["supplierName"] = "-";
			}
			
			$i++;
			if ($i==$countArticles){
				$comma = "";
			}else {
				$comma = ",";
			}
			?>
			{"id":<?php echo $article["articleID"] ?>,"ID":"<?php echo $article["ordernumber"] ?>","Supplier":"<?php echo $article["supplierName"]?>","Article":"<?php echo $article["articleName"]?>","Price": "<?php echo $price ?> &euro;","Active":"<?php echo $article["active"]?>","instock":"<?php echo $article["instock"] ?>","options":"<a class=\"ico delete\" style=\"cursor:pointer\" onclick=\"deleteArticle(<?php echo $article['articleID']?>,'<?php echo $article['articleName']?>')\"></a><a class=\"ico pencil\" style=\"cursor:pointer\" onclick=\"parent.parent.loadSkeleton('articles',false, '{article:<?php echo $article["articleID"] ?>}')\"></a>"}<?php echo $comma ?>
			<?php 
		}
		echo "]"; 	
	
}}

?>