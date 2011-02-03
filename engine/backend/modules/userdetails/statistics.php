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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $sLang["userdetails"]["statistics_search"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="../../../vendor/flashgraph/JSClass/FusionCharts.js"></script>

</head>
<body>
<div id="chartdiv" align="center"> 
</div>
<script type="text/javascript">
		   var chart = new FusionCharts("../../../vendor/flashgraph/Charts/Bar2D.swf", "ChartId", "600", "275", "0", "0");
		   chart.addParam('WMODE','transparent');
		   chart.setDataURL("amountmonth.php?id=<?php echo $_GET["id"] ?>&rand=<?php echo rand(0,255) ?>");		   
		   chart.render("chartdiv");
</script>
<br />
<?php
$queryTotalAmount = mysql_query("
	SELECT SUM(invoice_amount) AS amount FROM s_order WHERE userID={$_GET["id"]} AND status != -1 AND status != 4
	");

if (@mysql_num_rows($queryTotalAmount)){
	$totalAmount = number_format(mysql_result($queryTotalAmount,0,"amount"),2,",","");
}else {
	$totalAmount = "0,00";
}

?>
<strong><?php echo $sLang["userdetails"]["statistics_Total_sales_since_registration"] ?></strong><?php echo $totalAmount ?> €

</body>
</html>