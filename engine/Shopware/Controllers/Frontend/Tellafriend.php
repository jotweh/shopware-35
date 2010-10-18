<?php
class Shopware_Controllers_Frontend_Tellafriend extends Enlight_Controller_Action
{
	var $sSYSTEM;
	
	public function init(){
		$this->sSYSTEM = Shopware()->System();
	}
	
	public function indexAction()
	{
		if (empty($this->Request()->sDetails)){
			$id = $this->Request()->sArticle;
		}else {
			$id = $this->Request()->sDetails;
		}
		
		if (empty($id)){
			return $this->forward("index","index");
		}
		

		// Get Article-Information
		$sArticle = Shopware()->Modules()->Articles()->sGetPromotionById('fix',0,intval($id));
		if (empty($sArticle["articleName"])){
			return $this->forward("index","index");
		}
				
		if ($this->Request()->getPost("sMailTo")){
			$variables["sError"] = false;
			if (!$this->Request()->getPost("sName")) $variables["sError"] = true;
			if (!$this->Request()->getPost("sMail")) $variables["sError"] = true;
			if (!$this->Request()->getPost("sRecipient")) $variables["sError"] = true;	
			
			
			if (preg_match("/;/",$this->Request()->getPost("sRecipient")) || strlen($this->Request()->getPost("sRecipient")>=50)){
				$variables["sError"] = true;
			}
			
			$validator = new Zend_Validate_EmailAddress();
		
			if(!$validator->isValid($this->Request()->getPost("sRecipient"))){
				$variables["sError"] = true;
			}
						
			if (!empty(Shopware()->Config()->CaptchaColor) && !$voteConfirmed) {
				$captcha = str_replace(' ', '', strtolower($this->Request()->sCaptcha));
				$rand = $this->Request()->getPost('sRand');
				
				$random = $rand;
				$random .= Shopware()->Plugins()->Core()->License()->getLicense("community");
				$random .= Shopware()->Plugins()->Core()->License()->getLicense("core");
				$random = md5($random);
				$calculatedValue = substr($random,0,5);
				if (!empty($rand) && $captcha == $calculatedValue){
				} else {
					$variables["sError"]  = true;
				}
			}
			
			if ($variables["sError"]==false){
				// Prepare eMail
				$sArticle["linkDetails"] = $this->Front()->Router()->assemble(array('sViewport'=>'detail','sArticle'=>$sArticle["articleID"]));
			    $template = Shopware()->Config()->Templates->sTELLAFRIEND;
			    
				$template['subject'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$template['subject']);
				$template['subject'] = str_replace("{sArticle}",$sArticle["articleName"],$template['subject']);
				
				// Standard-Content
				$template['content'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$template['content']);
				$template['content'] = str_replace("{sShop}",$this->sSYSTEM->sCONFIG['sSHOPNAME'],$template['content']);
				$template['content'] = str_replace("{sArticle}",$sArticle["articleName"],$template['content']);
				$template['content'] = str_replace("{sLink}",$sArticle["linkDetails"],$template['content']);
				// HTML-Content
				$template['contentHTML'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$template['contentHTML']);
				$template['contentHTML'] = str_replace("{sShop}",$this->sSYSTEM->sCONFIG['sSHOPNAME'],$template['contentHTML']);
				$template['contentHTML'] = str_replace("{sArticle}",$sArticle["articleName"],$template['contentHTML']);
				$template['contentHTML'] = str_replace("{sLink}",$sArticle["linkDetails"],$template['contentHTML']);
				
				
				if ($this->sSYSTEM->_POST["sComment"]){
					$this->sSYSTEM->_POST["sComment"] = strip_tags(addslashes($this->sSYSTEM->_POST["sComment"]));
					$template['content'] = str_replace("{sComment}",$this->sSYSTEM->_POST["sComment"],$template['content']);
					$template['contentHTML'] = str_replace("{sComment}",$this->sSYSTEM->_POST["sComment"],$template['contentHTML']);
				}else {
					$template['content'] = str_replace("{sComment}","",$template['content']);
					$template['contentHTML'] = str_replace("{sComment}","",$template['contentHTML']);
				}
				
				
				if ($this->sSYSTEM->sCONFIG["sVOUCHERTELLFRIEND"]){
					/*$checkIfArktisClient = $this->sSYSTEM->sDB_CONNECTION->fetchRow("
					SELECT id FROM s_user WHERE active=1 AND email=?
					",array($this->sSYSTEM->_POST["sMail"]));
					
					if ($checkIfArktisClient["id"]){
						$checkRecipientState = $this->sSYSTEM->sDB_CONNECTION->fetchRow("
						SELECT id FROM s_user WHERE active=1 AND email=?
						",array($this->sSYSTEM->_POST["sRecipient"]));
						if (!$checkRecipientState["id"]){
							Shopware()->Db()->query("
							INSERT INTO s_emarketing_tellafriend (datum, recipient, sender, confirmed)
							VALUES (now(),?,?,0)
							",array($this->sSYSTEM->_POST["sRecipient"],$checkIfArktisClient["id"]));
						}
					}*/
				}
				
				// Send eMail
				$mail           = $this->sSYSTEM->sMailer;				
			
				$mail->From     = $this->sSYSTEM->_POST["sMail"];
				$mail->FromName = $this->sSYSTEM->_POST["sName"];
				$mail->Subject  = $template['subject'];
				
				if ($template['ishtml']){
					$mail->IsHTML(1);
					$mail->Body     = $template['contentHTML'];
					$mail->AltBody     = $template['content'];
				}else {
					$mail->IsHTML(0);
					$mail->Body     = $template['content'];
				}
				
				$mail->ClearAddresses();
				$mail->AddAddress($this->sSYSTEM->_POST["sRecipient"], "");
				if ($mail->Send()){
					$this->View()->sSuccess = true;
				}
			}else {
				$this->View()->sError = true;
				$this->View()->sName = $this->Request()->getPost("sName");
				$this->View()->sMail = $this->Request()->getPost("sMail");
				$this->View()->sRecipient = $this->Request()->getPost("sRecipient");
				$this->View()->sComment = $this->Request()->getPost("sComment");
			}
			
		}
		$this->View()->rand = md5(uniqid(rand()));
		$this->View()->sArticle = $sArticle;
		
	}
}