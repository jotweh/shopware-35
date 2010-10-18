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
?>
<chart palette='6' caption='<?php echo $sLang["articles"]["amountmonth_Sales_by_months"] ?>' subcaption='<?php echo date("Y") ?>' xAxisName='Monat' yAxisMinValue='15000' yAxisName='Umsatz' numberPrefix='€ ' decimals="2" formatNumberScale="0" showValues='0'>
<?php

for ($i=1;$i<=12;$i++){
	$month = $i;
	$year = date("Y");
	// Build query
	$sql = "
	SELECT SUM(price*quantity) AS amount FROM s_order_details, s_order WHERE articleID={$_GET["id"]}
	AND MONTH(ordertime)=$month AND YEAR(ordertime)=$year AND s_order.id=s_order_details.orderID
	"; 
	$queryMonth = mysql_query($sql);
	
	
		$amount = @mysql_result($queryMonth,0,"amount") ? @mysql_result($queryMonth,0,"amount") : 0;
	
	$monthText = date("M",mktime(0,0,0,$month,1,$year));
	echo "<set label='$monthText' value='$amount' />\n";
	
}
?>
	<styles>
		<definition>
			<style name='Anim1' type='animation' param='_xscale' start='0' duration='1' />
			<style name='Anim2' type='animation' param='_alpha' start='0' duration='1' />
			<style name='DataShadow' type='Shadow' alpha='20'/>
		</definition>
		<application>
			<apply toObject='DIVLINES' styles='Anim1' />
			<apply toObject='HGRID' styles='Anim2' />
			<apply toObject='DATALABELS' styles='DataShadow,Anim2' />
	</application>	
	</styles>
</chart>