<?php

class sViewportPaypalexpressAPIError{
	var $sSYSTEM;

	function sRender(){

		$resArray=	$this->sSYSTEM->_SESSION['reshash']; 
		$payPalURL = $this->sSYSTEM->_SESSION['payPalURL'];
		 
		if(isset($this->sSYSTEM->_SESSION['curl_error_no'])) { 
	
				// URL Error, something goes wrong
				$variables["errorCode"] = $this->sSYSTEM->_SESSION['curl_error_no'] ;
				$variables["errorMessage"] = $this->sSYSTEM->_SESSION['curl_error_msg'] ;
				$variables["urlError"] = true;
				session_unset();	
	
		} else {

			$variables["check"] = "OK";
			
			$variables["ACK"] = $resArray['ACK'];
			$variables["CORRELATIONID"] = $resArray['CORRELATIONID'];
			$variables["VERSION"] = $resArray['VERSION'];

			$count=0;
			while (isset($resArray["L_SHORTMESSAGE".$count])) {		
				  $paypalAPIError[$count]["errorCode"] = $resArray["L_ERRORCODE".$count];
				  $paypalAPIError[$count]["shortMessage"] = $resArray["L_SHORTMESSAGE".$count];
				  $paypalAPIError[$count]["longMessage"]  = $resArray["L_LONGMESSAGE".$count]; 
				  $count=$count+1; 
			}			
			$variables["paypalAPIError"] = $paypalAPIError;
			
		}

		// Redirect to paypal.com here
		$token = urlencode( $this->sSYSTEM->_SESSION['token']);
		$variables["payPalURL"] = $payPalURL;
	
		// Display error 
		$templates = array("sContainer"=>"/error/paypalexpress_api_error.tpl","sContainerRight"=>"");	
		return array("templates"=>$templates,"variables"=>$variables);

		

		

	}
}
?>