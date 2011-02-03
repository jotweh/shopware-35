<?php
if (!defined('sAuthFile')) die();

//SELECT `pageimpressions`, `uniquevisits` FROM `s_statistics_visitors` WHERE `datum` = NOW()
$monate = $sLang["statistics"]["order_user_month"];
$sql = "
	SELECT 
		SUM(o.invoice_amount) AS `Umsatz`,
		WEEK(o.ordertime) AS `Woche`
	FROM 
		`s_order` AS o
	WHERE 
		WEEK(o.ordertime) <= WEEK(now())
	AND 
		WEEK(o.ordertime) >= WEEK(now())-8
	AND 
		o.status != 4
	AND
		o.status != -1
	GROUP BY 
		WEEK(o.ordertime)
	ORDER BY o.ordertime ASC";
$result = mysql_query($sql);
if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Woche']]['Umsatz'] = $entry['Umsatz'];
}
$sql = "
	SELECT 
		u.email AS `Kunde`,
		SUM(s.invoice_amount) AS `Umsatz`
	FROM 
		`s_order` as s,`s_user_billingaddress` as u
	WHERE
		s.userID=u.userID
	AND 
		s.status != 4
	AND
		s.status != -1
	GROUP BY 
		s.userID
	ORDER BY Umsatz ASC";

$result = mysql_query($sql);
if (!$result)
	die();
while ($entry = mysql_fetch_assoc($result))
{
	$arrays[$entry['Woche']]['Hits'] = $entry['Hits'];
	$arrays[$entry['Woche']]['Visits'] = $entry['Visits'];
}
$w = date("W");
header('Content-type: text/xml');
//print_r($arrays);
?>
<?php echo"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"?>

<?php//<chart caption="Umsatz / Besucher" palette="1" showValues="0" yAxisValuesPadding="10">?>
<chart palette="2" caption="Umsatz / Besucher" subCaption="von <?php echo($w-7).' bis '.($w).' Kalenderwoche'?>" showValues="0" divLineDecimalPrecision="1" limitsDecimalPrecision="1" DYAxisName="Visits" PYAxisName="Umsatz" SYAxisName="Anzahl" numberPrefix="" formatNumberScale="0">
<categories>
<?php for($i = $w-7; $i<=$w; $i++) {?>
	<category label='<?php echo$i?>' />
<?php }?>
</categories>
<dataset seriesName="Umsatz" renderAs="Area" parentYAxis="P">
<?php foreach ($arrays as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Umsatz']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Hits" showValues="0" parentYAxis="P">
<?php foreach ($arrays as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Hits']?>"/>
<?php }?>
</dataset>
<dataset seriesName="Visits" showValues="0" parentYAxis="S">
<?php foreach ($arrays as $key=>$value) {?>
	<set label="<?php echo$key?>" value="<?php echo$value['Visits']?>"/>
<?php }?>
</dataset>
</chart>
