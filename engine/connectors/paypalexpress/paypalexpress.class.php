<?php
/**********************************************************
PayPal-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

paypalexpress.class.php

**********************************************************/

if (!$path){
	$path = "../../";
}
include($path."payment.class.php");

class paypalexpressPayment extends sPayment {
	/**
	* Projekt-Daten
	*/
		 
	var $paypalUsername =  "";
	var $paypalPassword  =  "";
	var $paypalSignature =  "";	 
	var $paypalSandbox	=  "";	 
	var $paypalXpress =  "";	 
	var $paypalAuthorization =  "";	 
	
	/*Benötigte Parameter*/
	var $neededArguments = array("trans_id","token");
	var $arguments = array("coreID"=>"coreID","dispatchID"=>"dispatchID","transactionID"=>"trans_id","comment"=>"sComment","uniqueID"=>"uniqueID");
  
	function __construct($debug,$path,$startSession=true){
		parent::__construct($debug,$path,$startSession);
		
		/*Setze Konfiguration*/
		$this->paypalUsername = $this->config["sAPI_USERNAME"];
		$this->paypalPassword = $this->config["sAPI_PASSWORD"];
		$this->paypalSignature = $this->config["sAPI_SIGNATURE"];
		$this->paypalSandbox = $this->config["sAPI_SANDBOX"];
		$this->paypalXpress = $this->config["sXPRESS"];
		$this->paypalAuthorization = $this->config["sAUTHORIZATION"];
	}
    
	function sViewportSale(){
		/*Doing something after / while submitting order*/
	}
  
	function sLog($type, $logString, $backend){
		return;
		if ($backend == true) {			
		    $datei = "../../connectors/paypalexpress/log.txt";
		} else {
		    $datei = "log.txt";			
		}
		
		$date = date("Y-m-d H:i:s");
		$timeStamp = time();
	  	$time = "\n\nDateTime: ".$date. " TimeStamp: ".$timeStamp."\n";
	    $type = $type."\n";
	    $text = $time.$type.$logString;
	    $textdatei = fopen ($datei, "a+");
	    fwrite($textdatei, $text);
	    fclose($textdatei);
	 		
		
	}
    
  
	 function initPayment(){
	 	parent::initPayment();
	 }
  
}
?>