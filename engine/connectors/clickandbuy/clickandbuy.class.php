<?php
/*
ClickandBuy-Schnittstelle
Version 1.0
(c)2008, PayIntelligent 
*/

if (!$path){
	$path = "../../";
}
include($path."payment.class.php");

class clickandbuyPayment extends sPayment {
	/**
	* Projekt-Daten
	*/
		 
	var $cabLink  	  	=  "";
	var $sellerID  		=  "";
	var $tmiPwd  		=  "";	 
	//var $gateway       	= "";

	
	/*Benötigte Parameter*/
	var $neededArguments = array("trans_id","externalBDRID");
	var $arguments = array("coreID"=>"coreID","dispatchID"=>"dispatchID","transactionID"=>"trans_id","comment"=>"sComment","uniqueID"=>"uniqueID");
  
  function __construct($debug,$path,$startSession=true){
		parent::__construct($debug,$path,$startSession);
		
		/*Setze Konfiguration*/
		$this->cabLink = $this->config["sCABLINK"];
		$this->sellerID = $this->config["sCABSELLERID"];
		$this->tmiPwd = $this->config["sCABTMIPWD"];
		$this->customerData = $this->config["sCUSTOMERDATA"];
		$this->secondConfirmationStatus = $this->config["sSECONDCONFIRMATIONSTATUS"];
  }
  
  function sViewportSale(){
  	/*Doing something after / while submitting order*/
  }
  
  function initPayment(){
  		parent::initPayment();
  }  
  
  function secondConfirmation($debug, $sellerID,$tmPassword,$externalBDRID, $cabLink){	

	/*Importiere NuSoap Lib*/ 
	include("lib/nusoap.php");  	
	
	$isCommitted = 0;

	/*Systemcode Ermittlung*/
	$systemCode = "eu";
	if (preg_match (".uk.", $cabLink)) $systemCode = "uk";
	if (preg_match (".us.", $cabLink)) $systemCode = "us";
	if (preg_match (".ch.", $cabLink)) $systemCode = "ch";
	if (preg_match (".se.", $cabLink)) $systemCode = "se";
	if (preg_match (".dk.", $cabLink)) $systemCode = "dk";
	if (preg_match (".no.", $cabLink)) $systemCode = "no";

	$wsdl_URL = "http://wsdl.".$systemCode.".clickandbuy.com/TMI/1.4/TransactionManagerbinding.wsdl";

	/*Client Object*/
	$client = new nusoapclient($wsdl_URL,true); 

	$secondconfirmation = array(
		'sellerID' => $sellerID,
		'tmPassword' => $tmPassword,
		'slaveMerchantID' => '0',
		'externalBDRID' => $externalBDRID
	);
 
	/*Starte Soap Request*/
	$cb_result = $client->call('isExternalBDRIDCommitted',$secondconfirmation,'https://clickandbuy.com/TransactionManager/','https://clickandbuy.com/TransactionManager/');

	$isCommitted = $cb_result['isCommitted'];
	
	if (($isCommitted == 1) || ($isCommitted == true)) {
		$isCommitted = 1;	
	} else {
		
		/*Fehler $cb_result*/
		$err = $client->getError();
	}
	
	return $isCommitted;
  }
  




}
?>