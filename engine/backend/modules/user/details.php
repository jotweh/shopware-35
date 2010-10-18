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
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Reorder TreePanel</title>
<!-- Common Styles for the examples -->
</head>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
<?php

?>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<script type='text/javascript'>

<?php

	$sql = "SELECT a.categoryID AS category,a.id as articleID, ordernumber,datum,additionaltext, shippingfree, description_long, aSupplier.name AS supplierName, aSupplier.img AS supplierImg, a.name AS articleName, price, sales, pseudoprice, tax,
				attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
				attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20
				FROM s_articles_categories AS aCategories,s_articles AS a,
				s_articles_supplier AS aSupplier, s_articles_details AS aDetails, s_articles_prices AS aPrices, s_core_tax AS aTax,
				s_articles_attributes AS aAttributes
				WHERE 
				aCategories.articleID=a.id AND a.taxID=aTax.id
				AND aAttributes.articledetailsID=aDetails.id
				AND aCategories.categoryID=".$_GET["id"]."
				AND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 AND a.active=1
				AND aPrices.pricegroup='EK' AND aPrices.articleDetailsID=aDetails.id
				AND aPrices.to='beliebig'
				GROUP BY a.id ORDER BY a.datum DESC LIMIT 100";
	$getArticles = mysql_query($sql);
	$countArticles = @mysql_num_rows($getArticles);
	if ($countArticles){
	?>
var headers = [
{
"text":"<?php echo $sLang["user"]["details_number"] ?>",
"key":"ID","sortable":false,
"fixedWidth":true,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["user"]["details_manufacturer"] ?>","fixedWidth":true,"defaultWidth":"100px","key":"Supplier"},{"text":"<?php echo $sLang["user"]["details_article"] ?>","key":"Article","fixedWidth":true,"defaultWidth":"200px"},{"text":"<?php echo $sLang["user"]["details_price"] ?>","key":"Price"},{"text":"<?php echo $sLang["user"]["details_active"] ?>","key":"Active","fixedWidth":true,"defaultWidth":"40px"}];
	<?php
		echo "var data = [";
		while ($article = mysql_fetch_array($getArticles)){
			$price = $sCore->sCalculatingPrice($article["price"],$article["tax"]);
			$i++;
			if ($i==$countArticles){
				$comma = "";
			}else {
				$comma = ",";
			}
			echo "{\"id\":{$article["articleID"]},\"ID\":{$article["ordernumber"]},\"Supplier\":\"{$article["supplierName"]}\",\"Article\":\"{$article["articleName"]}\",\"Price\":\"$price €\",\"Publisher\":\"Merriam\",\"Active\":\"1\"}$comma\n";
		}
		echo "];"; 
	}else {
		?>
var headers = [
{
"text":"<?php echo $sLang["user"]["details_date"] ?>",
"key":"date","sortable":true,
"fixedWidth":true,"defaultWidth":"150px"},
{
"text":"<?php echo $sLang["user"]["details_ordernumber"] ?>",
"key":"id","sortable":true,
"fixedWidth":true,"defaultWidth":"150px"},
{
"text":"<?php echo $sLang["user"]["details_orderworth"] ?>",
"key":"ordernumber","sortable":true,
"fixedWidth":true,"defaultWidth":"150px"},
{
"text":"<?php echo $sLang["user"]["details_status"] ?>",
"key":"state","sortable":true,
"fixedWidth":true,"defaultWidth":"150px"}
];

var data = [{"date":"26.04.2007","id":"<?php echo $sLang["user"]["details_no_Data"] ?>","ordernumber":"000000","state":"<?php echo $sLang["user"]["details_in_constuction"] ?>"}];
		<?php
	}

?>


Window.onDomReady( function(){
	
				function exampleClick(ev){
					//parent.parent.Growl( 'You picked row ' + (this.data.id) );
					parent.parent.loadSkeleton('articles',false, "{article:"+this.data.id+"}");
				}
				mootable = new MooTable( 'test', {debug: false, height: '270px', headers: headers, sortable: true, useloading: false, resizable: false});
				mootable.addEvent( 'afterRow', function(data, row){
					
					//debug.log( row );
					//row.cols[0].element.innerHTML = ( data.ID + 1);
					<?php
					if ($countArticles){
					?>
					row.cols[2].element.setStyle('cursor', 'pointer');
					row.cols[2].element.addEvent( 'click', exampleClick.bind(row) );
					<?php
					}
					?>
				});
				mootable.loadData( data );
				});
		</script>
<?php
// If category is choosen

?>
		<style>
/*
 * Ext JS Library 1.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */

body {
	font-family:verdana,tahoma,helvetica;
    font-size:11px;
    margin: 0px;
    padding:0px;
	background-color:#fff !important;
}

</style>
<body>
<div id='test'>&nbsp;</div>
</body>
</html>
