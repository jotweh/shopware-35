<?php
/*
ClickandBuy-Schnittstelle
Version 1.2
(c)2009, PayIntelligent 
*/
 
$referer = $_SERVER['HTTP_HOST'];; 

$url= "https://eu.clickandbuy.com/cgi-bin/register.pl?mode=anbieter&portalmerchant=SHOPWARE&skriptname=Ihre%20Bestellung&linkType=transaction&test=False&readOnly=False&cb_regversion=1.1&ConfigurationURL=https://eu.clickandbuy.com/cgi-bin/special/MReg_LP.pl&activateTMI=True&AccountCurrency=EUR&SellerIDMaster=18631481&EnableDynamicCurrencyHandover=true&prn_link=True";

if (!empty($referer)) {
	
	$host = "http://".$referer;
	$domainurl = "&domainurl=".$host; 
	$skripturl = "&skripturl=".$host."/engine/connectors/clickandbuy/cab_trans.php";	
	$emsurl = "&ems_push=".$host."/engine/connectors/clickandbuy/ems_listener.php";	
 
} else {
	$domainurl = "";
	$skripturl = "";
	$emsurl = "";
} 
 
$url = $url.$domainurl.$skripturl.$emsurl;

/*Berechne fgkey*/
$fgkey = md5("SHT3Nni5FR83r".$url);

/*Setze Redirect URL*/
$redirect_url = $url."&fgkey=".$fgkey;

/*Weiterleitung zur ClickandBuy Haendlerregistrierung*/
header("Location: ".$redirect_url);

?>