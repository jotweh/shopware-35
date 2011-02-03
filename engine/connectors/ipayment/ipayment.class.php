<?php
if (!$path){
	$path = "../../";
}
include($path."payment.class.php");

class ipaymentPayment extends sPayment {

	
	// Benötigte Parameter
	var $neededArguments = array("ret_status");
	var $arguments = array("coreID"=>"coreID","dispatchID"=>"dispatchID","transactionID"=>"ret_trx_number","comment"=>"sComment","uniqueID"=>"uniqueID");
	
  
  function __construct($debug,$path,$startSession=true){
		parent::__construct($debug,$path,$startSession);
  }
  
  function sViewportSale(){
  	// Doing something after / while submitting order
  	
  }
  
  function initPayment(){
  		parent::initPayment();
  }
}
?>