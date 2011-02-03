<?php
/**********************************************************
Saferpay-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

saferpay.class.php

**********************************************************/

if (!$path){
	$path = "../../";
}
include($path."payment.class.php");

class saferpayPayment extends sPayment {
	/**
	* Projekt-Daten
	*/
		 
	var $saferpayAccountID =  "";
	var $saferpayTestsystem   =  "";
	var $saferpayPassword =  "";	 
	var $saferpayAuthorization =  "";	 
	
	var $saferpayCVC =  "";	 
	var $saferpayCardholder =  "";	 
	var $saferpayMenucolor =  "";	 
	var $saferpayMenufontcolor =  "";	 
	var $saferpayBodyfontcolor =  "";	 
	var $saferpayBodycolor =  "";	 
	var $saferpayHeadfontcolor =  "";	 
	var $saferpayHeadcolor =  "";	 
	var $saferpayHeadlinecolor =  "";	 
	var $saferpayLinkcolor =  "";	 	
	
	/*Benötigte Parameter*/
	var $neededArguments = array("trans_id","token");
	var $arguments = array("coreID"=>"coreID","dispatchID"=>"dispatchID","transactionID"=>"trans_id","comment"=>"sComment","uniqueID"=>"uniqueID");
  
	function __construct($debug,$path,$startSession=true){
		parent::__construct($debug,$path,$startSession);
		
		/*Setze Konfiguration*/
		$this->saferpayAccountID = $this->config["sSAFERPAY_ACCOUNTID"];
		$this->saferpayTestsystem = $this->config["sSAFERPAY_TESTSYSTEM"];
		$this->saferpayPassword = $this->config["sSAFERPAY_PASSWORD"];
		$this->saferpayAuthorization = $this->config["sSAFERPAY_AUTHORIZATION"];

		$this->saferpayCVC = $this->config["sSAFERPAY_CVC"];
		$this->saferpayCardholder = $this->config["sSAFERPAY_CARDHOLDER"];
		$this->saferpayMenucolor = $this->config["sSAFERPAY_MENUCOLOR"];
		$this->saferpayMenufontcolor = $this->config["sSAFERPAY_MENUFONTCOLOR"];
		$this->saferpayBodyfontcolor = $this->config["sSAFERPAY_BODYFONTCOLOR"];
		$this->saferpayBodycolor = $this->config["sSAFERPAY_BODYCOLOR"];
		$this->saferpayHeadfontcolor = $this->config["sSAFERPAY_HEADFONTCOLOR"];
		$this->saferpayHeadcolor = $this->config["sSAFERPAY_HEADCOLOR"];
		$this->saferpayHeadlinecolor = $this->config["sSAFERPAY_HEADLINECOLOR"];
		$this->saferpayLinkcolor = $this->config["sSAFERPAY_LINKCOLOR"];

	}
    
	function sViewportSale(){
		/*Doing something after / while submitting order*/
	}
  
	function sLog($type, $logString, $backend){
		
		if ($backend == true) {			
		    $datei = "../../connectors/saferpay/log.txt";
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