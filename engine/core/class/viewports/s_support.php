<?php

class sViewportSupport{
	var $sSYSTEM;
	
	function sRender()
	{
		//$this->sSYSTEM->_GET['sCid']
		if (!$this->sSYSTEM->sMODULES['sCmsSupport']->sConstruct())
		{
			//$templates = array("sContainer"=>"/support/support_404.tpl");
			$templates = array("sContainer"=>"/support/support_details.tpl");
			return array("templates"=>$templates,"variables"=>"");
		}
		
		$sELEMENTS = $this->sSYSTEM->sMODULES['sCmsSupport']->sELEMENTS;
		

		
		if (isset($this->sSYSTEM->_POST['sAction'])) if ($this->sSYSTEM->_POST['sAction']=="saveFrom")
		{
			
			if (!isset($sERRRORS))
			{
				$sERRRORS = $this->sSYSTEM->sMODULES['sCmsSupport']->validate_input($this->sSYSTEM->_POST,$this->sSYSTEM->sMODULES['sCmsSupport']->sELEMENTS);
			}
			$sPOSTS = $this->sSYSTEM->sMODULES['sCmsSupport']->sPOSTS;
						
			$captcha = $this->sSYSTEM->sCONFIG["sTEMPLATEPATH"]."/".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."/media/img/default/captcha.jpg";
			if (!is_file($captcha)){
				$captcha = "templates/0/de/media/img/default/captcha.jpg";
			}
			if (is_file($captcha)){
				
				if ($this->sSYSTEM->_POST["sCaptcha"] != str_replace(" ","",$this->sSYSTEM->_SESSION["sCaptcha"])){
					$sELEMENTS["sCaptcha"]['class'] = " instyle_error";
					$sERRRORS["e"]["sCaptcha"] = true;// = array("e"=>array("sCaptcha"=>true));
					
				}else {
					unset($this->sSYSTEM->_SESSION["sCaptcha"]);
				}
				
			}
			if (!empty($sERRRORS)) {
				foreach ($sERRRORS['e'] as $key => $value)
				{
					if(isset($sERRRORS['e'][$key]))
					{
						if($sELEMENTS[$key]['typ']=="text2")
						{
							$class = explode(";",$sELEMENTS[$key]['class']);
							$sELEMENTS[$key]['class'] = implode(" instyle_error;",$class)." instyle_error";
						}
						else 
							$sELEMENTS[$key]['class'].= " instyle_error";
					}
				}
			}
		}
		
		if(!isset($this->sSYSTEM->_POST['sAction']))
		{
			//Function to manipulate the Values
			$sPOSTS = array();
			$sPOSTS = $this->sModifyValues($sPOSTS);
		}
		
		
		$variables["sSupport"] = $this->sSYSTEM->sMODULES['sCmsSupport']->sSUPPORT;
		
		if (!empty($sERRRORS)||!isset($this->sSYSTEM->_POST['sAction'])) {
				
			
			foreach ($sELEMENTS as $sELEMENT)
			{
				if ($sELEMENT["name"]=="inquiry" && $this->sSYSTEM->_GET["sInquiry"]){
					switch ( $this->sSYSTEM->_GET["sInquiry"]){
						case "basket":
							$text =  $this->sSYSTEM->sCONFIG["sSnippets"]["sINQUIRYTEXTBASKET"];
							$getBasket = $this->sSYSTEM->sMODULES["sBasket"]->sGetBasket();
							foreach ($getBasket["content"] as $basketRow){
								if (empty($basketRow["modus"])){
									$text.= "\n{$basketRow["quantity"]} x {$basketRow["articlename"]} ({$basketRow["ordernumber"]}) - {$basketRow["price"]} ".$this->sSYSTEM->sCurrency["currency"];
								}
							}
							if (!empty($text)) $sELEMENT["value"] = $text;
							break;
						case "detail":
							if (!empty($this->sSYSTEM->_GET["sOrdernumber"])){
								$getName = $this->sSYSTEM->sMODULES["sArticles"]->sGetArticleNameByOrderNumber($this->sSYSTEM->_GET["sOrdernumber"]);
								$text =  $this->sSYSTEM->sCONFIG["sSnippets"]["sINQUIRYTEXTARTICLE"];
								$text .= " ".$getName;
								$sELEMENT["value"] = $text;
							}
							break;
					}
				}
				$sFIELDS[$sELEMENT['id']] =  $this->sSYSTEM->sMODULES['sCmsSupport']->create_input_element($sELEMENT,$sPOSTS[$sELEMENT['id']]);
				$sLABELS[$sELEMENT['id']] =  $this->sSYSTEM->sMODULES['sCmsSupport']->create_label_element($sELEMENT);
			}
			
			$variables["sSupport"]["sErrors"] = $sERRRORS;
			$variables["sSupport"]["sElements"] = $sELEMENTS;
			$variables["sSupport"]["sFields"] = $sFIELDS;
			$variables["sSupport"]["sLabels"] = $sLABELS;
		}
		else 
		{
			$SPAM = false;
			foreach ($sPOSTS as $key=>$value)
			{
				$sBADWORDS = array(
					" sex ",
					" porn ",
					" viagra ",
					"url=",
					"src=",
					"link=",
				);
				foreach ($sBADWORDS as $sBADWORD)
				if(strpos($value,$sBADWORD)!==false)
				{
					$SPAM = true;
				}
			}
			if(!empty($this->sSYSTEM->_POST['sCaptchaTest']))
			{
				$SPAM = true;
			}
			if(!$SPAM)
			{
				$this->sManageData($variables,$sPOSTS,$sELEMENTS);
			}
		}
		
		$templates = array("sContainer"=>"/support/support_details.tpl");
		return array("templates"=>$templates,"variables"=>$variables);
	}
	
	function sManageData($variables,$sPOSTS,$sELEMENTS){
		
		$mail = $this->sSYSTEM->sMailer;									
		$mail->IsHTML($this->sSYSTEM->sCONFIG['sTemplates']['sSUPPORT']['ishtml']);
		
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//+++++++++++++++++++++++++SET MAIL FROM ATTR+++++++++++++++++++++++++++++++++++		
		
		//eMail field available check
		foreach ($sELEMENTS as $element) {
			if($element['typ'] == "email")
			{
				$post_email = $sPOSTS[$element['id']];
				$post_email = trim($post_email);
			}
		}
		if(!empty($post_email)){
			$mail->From     = $post_email;
		}else{
			$mail->From     = $this->sSYSTEM->sCONFIG['sMAIL'];
		}
		
		
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$mail->FromName = $variables["sSupport"]["email_subject"];
		$mail->Subject  = $variables["sSupport"]["email_subject"];
		$mail->Body     = $variables["sSupport"]["email_template"];
		foreach ($sPOSTS as $key => $value)
		{
			if($sELEMENTS[$key]['typ']=="text2")
			{
				$names = explode(";",$sELEMENTS[$key]['name']);
				
				$mail->Body = str_replace("{sVars.".$names[0]."}",$value[0],$mail->Body);
				$mail->Body = str_replace("{sVars.".$names[1]."}",$value[1],$mail->Body);
			}
			else 
			{
				$mail->Body = str_replace("{sVars.".$sELEMENTS[$key]['name']."}",$value,$mail->Body);
			}
		}
		$mail->Body = str_replace("{sIP}",$_SERVER['REMOTE_ADDR'],$mail->Body);
		$mail->Body = str_replace("{sDateTime}",date("d.m.Y h:i:s"),$mail->Body);
		$mail->Body = html_entity_decode($mail->Body);
		$mail->Body = htmlspecialchars_decode($mail->Body);
		if (empty($mail->Body)) $mail->Body = " ";
		
		$mail->ClearAddresses();
		
		$mail->AddAddress($variables["sSupport"]["email"], "");
		
		
		if (!$mail->Send()){
			
		}
	}
	
	/**
	 * This function allow to modify the start values of the form
	 */
	function sModifyValues($sPOSTS)
	{
		//to use in a child class
		return $sPOSTS;
	}
	
}
?>