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
if (!$_GET["id"]) die("TEST");
?>
<chart palette='6' caption='Umsatz nach Monaten' subcaption='<?php echo date("Y") ?>' xAxisName='Monat' yAxisMinValue='15000' yAxisName='Umsatz' numberPrefix='€ ' showValues='2' decimals="2" formatNumberScale="0">
<?php

for ($i=1;$i<=12;$i++){
	$month = $i;
	$year = date("Y");
	// Build query
	$queryMonth = mysql_query("
	SELECT SUM(invoice_amount) AS amount FROM s_order WHERE userID={$_GET["id"]}
	AND status != -1 AND  status != 4
	AND MONTH(ordertime)=$month AND YEAR(ordertime)=$year
	");
	
	
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