<?php
if (!$path){
	$path = "../../";
}
include($path."payment.class.php");

class sofortPayment extends sPayment {
	/**
	* Projekt-Daten
	*/
		 
	var $user_id  		  	=  "";
	var $secretKey  		=  "";
	var $projectID  		=  "";
	 
	var $gateway       		= "";

	
	// Benötigte Parameter
	var $neededArguments = array("sCoreId","transaction","hash","amount");
	var $arguments = array("coreID"=>"sCoreId","dispatchID"=>"sDispatchID","transactionID"=>"transaction","comment"=>"sComment","uniqueID"=>"user_variable_1");
	
  
  function __construct($debug,$path,$startSession=true){
		parent::__construct($debug,$path,$startSession);
		
		// Set configuration
		$this->user_id = $this->config["sSOFORTUSERID"];
		$this->secretKey  = $this->config["sSOFORTSECRETKEY"];
		$this->projectID = $this->config["sSOFORTPROJECTID"];
  }
  
  function sViewportSale(){
  	// Doing something after / while submitting order
  	
  }
  
  function initPayment(){
  		parent::initPayment();
  }
}
?>