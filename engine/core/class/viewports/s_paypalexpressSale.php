<?php
class sViewportPaypalexpressSale{
	
	var $sSYSTEM;
	
		
	function sRender(){

		$serverName = $_SERVER['SERVER_NAME'];
		$url= "http://".$serverName."/";

		$basketData = $this->sSYSTEM->sMODULES['sBasket']->sGetBasket();	
		$variables["sBasket"] = $basketData;
		
		if (!count($basketData)){
				// This is possible a fatal error
				// No articles in basket
				$templates = array(
				"sContainer"=>"/error/error.tpl",
				"sContainerRight"=>""
				);
		} else {
			$templates = array("sContainer"=>"/payment/paypalexpress_order_confirm_middle.tpl","sContainerRight"=>"/orderprocess/order_confirm_right.tpl");					
		}
						
		// Fix - display order-information -
		$variables = $this->sSYSTEM->_SESSION["sOrderVariables"];
		$variables["sContainerRight"] = "";
		$variables["sRedirectURL"] = $url."/engine/connectors/paypalexpress/doPaymentGuest.php";
		$variables["sAGBError"] = $this->sSYSTEM->_SESSION['sAGB2'];

		return array("templates"=>$templates,"variables"=>$variables);

	}
	
}
?>