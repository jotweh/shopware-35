<?php

class sViewportCheaper{
	var $sSYSTEM;
	
	function sRender(){
		
		if (!$this->sSYSTEM->_GET["sDetails"]) return;
		$this->sSYSTEM->_GET['sDetails'] = intval($this->sSYSTEM->_GET['sDetails']);
		
		// Get Article-Information
		$sArticle = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById('fix',0,$this->sSYSTEM->_GET['sDetails']);
		if (!$sArticle["articleName"]) return;
		
		
		$reg = "/^(([^<>()[\]\\\\.,;:\s@\"]+(\.[^<>()[\]\\\\.,;:\s@\"]+)*)|(\"([^\"\\\\\r]|(\\\\[\w\W]))*\"))@((\[([0-9]{1,3}\.){3}[0-9]{1,3}\])|(([a-z\-0-9áàäçéèêñóòôöüæøå]+\.)+[a-z]{2,}))$/i";
		if(!preg_match($reg, $this->sSYSTEM->_POST["sMailTo"])){
			$variables["sErrorMessages"][] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorForgotMailUnknown'];
			unset($this->sSYSTEM->_POST["sMailTo"]);
		}
		if ($this->sSYSTEM->_POST["sMailTo"]){
			if (!$this->sSYSTEM->_POST["sName"]) $variables["sError"] = true;
			if (!$this->sSYSTEM->_POST["sMail"]) $variables["sError"] = true;
			if (!$this->sSYSTEM->_POST["sLink"]) $variables["sError"] = true;	
	
			if (!$variables["sError"]){
				// Prepare eMail
			
			
				$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['subject'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['subject']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['subject'] = str_replace("{sArticle}",$sArticle["articleName"],$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['subject']);
				
				$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content']);
				
				$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content'] = str_replace("{sShop}",$this->sSYSTEM->sCONFIG['sSHOPNAME'],$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content']);
				
				$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content'] = str_replace("{sArticle}",$sArticle["articleName"],$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content'] = str_replace("{sLink}",$this->sSYSTEM->_POST["sLink"],$this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content']);
				
				
				
				// Send eMail
				$mail           = $this->sSYSTEM->sMailer;				
				$mail->IsHTML($this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['ishtml']);
				$mail->From     = $this->sSYSTEM->_POST["sMail"];
				$mail->FromName = $this->sSYSTEM->_POST["sName"];
				$mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['subject'];
				$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sCHEAPER']['content'];
				$mail->ClearAddresses();
				$mail->AddAddress($this->sSYSTEM->sCONFIG["sMAIL"], "");
				if (!$mail->Send()){
					$this->sSYSTEM->E_CORE_WARNING ("##01 sCheaper","Could not send eMail");
				}else {
					$variables["sSuccess"] = true;
				}
			}
			
		}
				
		
		$templates = array("sContainer"=>"/articles/article_cheaper.tpl","sContainerRight"=>"");
		
		
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		$variables["sArticle"] = $sArticle;
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>