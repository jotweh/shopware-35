<?php

class sViewportPaypalexpressTXNPending{
	var $sSYSTEM;

	function sRender(){

		// Display error 
		$templates = array("sContainer"=>"/payment/paypalexpress_txn_pending.tpl","sContainerRight"=>"");	
		return array("templates"=>$templates,"variables"=>$variables);

	}
}
?>