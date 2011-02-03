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
	parent.parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<title></title>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../../vendor/flashgraph/JSClass/FusionCharts.js"></script>


<style type="text/css">
a {color: #3e677d;}
a:hover {color: #3e677d !important;}
.box_left {
	width:49%; border-right: 1px solid #ccc; float:left;height:230px;
}
.box_right {
	width:49%; float:left; padding-left:10px;height:230px;
}
.box_white {
	min-width: 700px; padding: 10px; border: 1px solid #a9a9a9; border-top: none; background-color: #fff;  margin:0 5px;
}

strong, h2 {font-size:11px; font-weight: bold; color: #888;}
a:hover {background-color:#cee5f1 !important; color:#777 !important;}
table tr.inline:hover {background-color:#cee5f1;color:#3e677d !important;}
table tr.inline:hover a {color:#3e677d !important;}

div.table_zebra a:hover {color:#3e677d !important;}
</style>

</head>
<body>
<?php
$uhrzeit = date('H');
    if ($uhrzeit < 12) {
       $gruss = "Guten Morgen";
       }
elseif ($uhrzeit >= 12 && $uhrzeit < 18){
       $gruss = "Guten Tag";
       }
  else {
       $gruss = "Guten Abend";
       }
       
   $tage = array(0=>"Sonntag",
                 1=>"Montag",
                 2=>"Dienstag",
                 3=>"Mittwoch",
                 4=>"Donnerstag",
                 5=>"Freitag",
                 6=>"Samstag");

   $monate = array(1=>"Januar",
                   2=>"Februar",
                   3=>"März",
                   4=>"April",
                   5=>"Mai",
                   6=>"Juni",
                   7=>"Juli",
                   8=>"August",
                   9=>"September",
                   10=>"Oktober",
                   11=>"November",
                   12=>"Dezember");

   $monat = $monate[date("n")];
   $name  = $tage[date("w")];
   $tag   = date("d");
   $jahr  = date("Y");
   
   if(!empty($_GET['margintop']))
   {
   		$margin_top = "top:5px;";
   }else{
   		$margin_top = "top:0px;";
   }
?> 


<fieldset class="white" style="margin-bottom: 0px; min-width: 700px;position:relative;<?php echo $margin_top; ?>left:0px; padding:0;">
<legend><?php echo $gruss ?> <?php echo $_SESSION["sName"] ?>. Willkommen in Ihrer Shopware, <?php echo $name.", der ".$tag.". ".$monat." ".$jahr ?></legend>
<!--
<div style="float:left; top: -8px; right: 26px; height: 72px; min-width: 600px;  background: #81aecb; margin-bottom: 5px;"></div>
-->
<div style="height: 72px; width: 100%; background: #81aecb url(../../../backend/img/default/welcome.jpg) no-repeat; margin-bottom: 5px;"></div>

<div class="clear" style="height:10px;"></div>



<div class="box_left" style="border:none;padding-left:3px;">
<h2 style="font-size:11px; margin:0px 0 0 5px;">Zuletzt bearbeitete Artikel:</h2>
<fieldset class="grey" style="margin: 10px 5px 0 5px;padding:0;height:180px;">
	<!--
	<div class="table_zebra" style="margin:15px 10px 0 0;">
	<?php /*
	$selectChangedArticles = mysql_query("
	SELECT id, name,changetime FROM s_articles WHERE changetime!='0000-00-00' ORDER BY changetime DESC LIMIT 7 
	");
	while ($article=mysql_fetch_assoc($selectChangedArticles)){
		$i++;
	?>
		<div class="<?php echo $i % 2 ? "nocolor" : "color" ?>"><a class="ico2 package_green" style="cursor:pointer" onclick="parent.parent.loadSkeleton('articles',false, {'article':<?php echo $article["id"] ?>})" target="_blank"><?php echo $article["name"] ?> (<?php echo date("d.m.Y. H:i:s",strtotime($article["changetime"]))?>)</a></div>
	<?php
	}
	*/?>
	</div>
	-->
	
		<table width="100%"  border="0" cellpadding="2" cellspacing="1">
			<tr style="height:22px;">
				<td nowrap="nowrap" class="th_bold" style="color: #777">Artikel</td>
				<td nowrap="nowrap" class="th_bold" style="color: #777">Bearbeitet am</td>
			</tr>
		<?php
		$selectChangedArticles = mysql_query("
		SELECT id, name,changetime FROM s_articles WHERE changetime!='0000-00-00' ORDER BY changetime DESC LIMIT 5 
		");
		while ($article=mysql_fetch_assoc($selectChangedArticles)){
			$i++;
			if (strlen($article["name"])>20){
				$article["name"] = substr($article["name"],0,20)."..";
			}
		?>
		<tr style="padding:10px 0 10px 0;" class="inline">
				<td class="td_padd" style="padding:4px 0 4px 5px; border-bottom:1px solid #ddd; height:20px;"><a class="ico2 package_green" style="cursor:pointer" onclick="parent.parent.loadSkeleton('articles',false, {'article':<?php echo $article["id"] ?>})" target="_blank"><?php echo $article["name"] ?></a></td>
				<td class="td_padd" style="padding:4px 0 4px 5px;  border-bottom:1px solid #ddd;">(<?php echo date("d.m.Y. H:i:s",strtotime($article["changetime"]))?>)</td>
		</tr>	
		<?php
		}
		?>
				
			
		</table>
	
</fieldset>
	
</div>


<div class="box_right">
	<h2 style="font-size:11px; margin:0px 0 0 5px;">Die letzten 5 Bestellungen:</h2>
	<fieldset class="grey" style="margin: 10px 5px 0 5px;padding:0;height:180px;">
		<table width="100%"  border="0" cellpadding="2" cellspacing="1">
			<tr style="height:22px;">
				<td nowrap="nowrap" class="th_bold" style="color: #777;"></td>
				<td nowrap="nowrap" class="th_bold" style="color: #777">Datum</td>
				<td nowrap="nowrap" class="th_bold" style="color: #777">Umsatz</td>
				<td nowrap="nowrap" class="th_bold" style="color: #777">Kunde</td>
			</tr>
			<?php
			$sql = "
SELECT s_order.id AS id, currency,currencyFactor,firstname,lastname, company, subshopID, paymentID,  ordernumber, transactionID, s_order.userID AS userID, invoice_amount,invoice_shipping, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertimeFormated, status, cleared 
FROM s_order
LEFT JOIN s_user_billingaddress ON s_user_billingaddress.userID = s_order.userID
WHERE 
	s_order.status != -1
ORDER BY ordertime DESC
LIMIT 5
";
$getOrders = mysql_query($sql);



	while ($order=mysql_fetch_assoc($getOrders)){
		if (!$order["currencyFactor"]) $order["currencyFactor"] = 1;
		$order["customer"] = htmlentities($order["company"] ? $order["company"] : $order["firstname"]." ".$order["lastname"],ENT_QUOTES);
		$amount = round(($order["invoice_amount"]/$order["currencyFactor"]),2);
		$amount = $sCore->sFormatPrice($amount);
		$order["invoice_amount"] = $amount;
		if (strlen($order["customer"])>25){
			$order["customer"] = substr($order["customer"],0,25)."..";
		}
		?>
		<tr style="padding:10px 0 10px 0;" class="inline">
				<td class="td_padd" style="width: 20px;border-bottom:1px solid #ddd;"><a class="ico2 sticky_note_pin" onclick="parent.parent.loadSkeleton('orders',false,{'id':<?php echo $order["id"] ?>})" style="cursor:pointer;height:20px;" target="_blank">&nbsp;</a></td>
				<td class="td_padd" style="padding:4px 0 4px 5px; border-bottom:1px solid #ddd;"><?php echo $order["ordertimeFormated"] ?></td>
				<td class="td_padd" style="padding:4px 10px 4px 5px;text-align:left;border-bottom:1px solid #ddd;"><?php echo $order["invoice_amount"] ?> €</td>
				<td class="td_padd" style="padding:4px 0 4px 5px; border-bottom:1px solid #ddd;"><?php echo utf8_decode($order["customer"]) ?></td>
		</tr>	
		<?php
		}
		?>
				
			
		</table>
		<div class="clear" style="height:40px;"></div>
	</fieldset>
</div>

</fieldset>

<!-- START WHITE AREA -->
<div class="box_white">
	<div class="box_left" style="height:100px;">
	<h2 style="font-size:11px; margin:0px 0 0 5px;">Erste Schritte Shopware:</h2>
	<div class="table_zebra" style="margin:15px 10px 0 0;">
		<div class="nocolor"><a class="ico2 question" href="http://www.shopware-ag.de/dev/wiki/Tutorial:_Erste_Schritte_Shopware" target="_blank">Erste Schritte zur Bedienung</a></div>
		<div class="color"><a class="ico2 information" href="http://www.shopware-ag.de/dev/wiki/Hilfe:Shopware_Account" target="_blank">Informationen zu den Shopware Account-Services</a></div>
	</div>
	
	</div>
	<div class="box_right" style="background: url(../../../backend/img/default/swl_logo.jpg) no-repeat 10px 10px; padding-left: 70px; width: 285px;height:100px;">
		<h2 style="font-size:11px; margin:0px 0 0 5px;">Ihr Shopware Account Konto:</h2>
		<?php
		if ($sCore->sCONFIG["sACCOUNTID"]){
			
		?>
		
	
	
		<div class="table_zebra" style="margin:0px 10px 0 0;">
			<div class="nocolor"><a class="ico2 shopware" style="cursor:pointer" onclick="parent.parent.parent.openAccount();">Account-Übersicht anzeigen</a></div>
			<div class="color"><a class="ico2 information" href="http://www.shopware-ag.de/dev/wiki/Hilfe:Shopware_Account" target="_blank">Informationen zu den Shopware Account-Services</a></div>
		</div>
		
		<?php
		}else {
		?>
		<p style="font-weight:bold">
		<span style="color:#F00">Sie haben noch keinen Shopware - Account!</span><br /><br />
		<a class="ico3 world_link" target="_blank" href="http://www.shopware-ag.de/">
		Jetzt Shopware-Konto beantragen
		</a>
		<a class="ico2 world_link" href="http://www.shopware-ag.de/dev/wiki/Hilfe:Shopware_Account" target="_blank">Informationen zu den Shopware Account-Services</a> 
		</p>
		<?php
		}
		?>
	</div>
<div class="clear" style="height:15px;"></div>
</div>

<!-- END WHITE AREA -->

</body>
</html>
