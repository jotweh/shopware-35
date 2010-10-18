<?php
class Shopware_Controllers_Frontend_Forms extends Enlight_Controller_Action
{	
	public $FormElements;
	public $Post;
	public $Errors;
	
	public function init(){
	
		$id = intval($this->Request()->sFid ? $this->Request()->sFid : $this->Request()->id);
		if (!empty($id)){
			if (!Shopware()->Modules()->CmsSupport()->sConstruct($id))
			{
				throw new Enlight_Exception("Could not construct form class");
			}
			$this->View()->id = $id;
			if (empty($this->FormElements)){
				
				$this->FormElements = Shopware()->Modules()->CmsSupport()->sELEMENTS;
			}
		}
	}
		
	private function checkFields(){

			$this->Errors = Shopware()->Modules()->CmsSupport()->validate_input(Shopware()->System()->_POST,Shopware()->Modules()->CmsSupport()->sELEMENTS);
			
			$this->Post = Shopware()->Modules()->CmsSupport()->sPOSTS;
						
			if (!empty(Shopware()->Config()->CaptchaColor) && !$voteConfirmed) {
				/*$captcha = str_replace(' ', '', strtoupper($this->Request()->sCaptcha));
				if ($key = array_search($captcha, Shopware()->Session()->sCaptchaCodes)){
					Shopware()->Session()->sCaptcha = "";
					unset(Shopware()->Session()->sCaptchaCodes[$key]);
				} else {
					$this->FormElements["sCaptcha"]['class'] = " instyle_error";
					$this->Errors["e"]["sCaptcha"] = true;
				}*/
				$captcha = str_replace(' ', '', strtolower($this->Request()->sCaptcha));
				$rand = $this->Request()->getPost('sRand');
				
				$random = $rand;
				$random .= Shopware()->Plugins()->Core()->License()->getLicense("community");
				$random .= Shopware()->Plugins()->Core()->License()->getLicense("core");
				$random = md5($random);
				$calculatedValue = substr($random,0,5);
				if (!empty($rand) && $captcha == $calculatedValue){
				} else {
					$this->FormElements["sCaptcha"]['class'] = " instyle_error";
					$this->Errors["e"]["sCaptcha"] = true;
				}
			}
			
			if (!empty($this->Errors )) {
				foreach ($this->Errors['e'] as $key => $value)
				{
					if(isset($this->Errors['e'][$key]))
					{
						if($this->FormElements[$key]['typ']=="text2")
						{
							$class = explode(";",$this->FormElements[$key]['class']);
							$this->FormElements[$key]['class'] = implode(" instyle_error;",$class)." instyle_error";
						}
						else 
							$this->FormElements[$key]['class'].= " instyle_error";
					}
				}
			}
			
			$SPAM = false;
			foreach ($this->Post as $key => $value)
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
			
			if($SPAM == true)
			{
				sleep(3);
				$this->Errors[] = array("1");
			}
			
	}

	public function indexAction()
	{
		if (!empty($this->Request()->Submit)){
			$this->checkFields();
		}
		
		if (empty($this->Request()->Submit) || count($this->Errors)){
			foreach ($this->FormElements as $element)
			{
				if ($element["name"]=="inquiry" && !empty($this->Request()->sInquiry)){
					switch ( $this->Request()->sInquiry){
						case "basket":
							$text =  Shopware()->System()->sCONFIG["sSnippets"]["sINQUIRYTEXTBASKET"];
							$getBasket = Shopware()->Modules()->Basket()->sGetBasket();
							$text = '';
							foreach ($getBasket["content"] as $basketRow){
								if (empty($basketRow["modus"])){
									$text.= "\n{$basketRow["quantity"]} x {$basketRow["articlename"]} ({$basketRow["ordernumber"]}) - {$basketRow["price"]} ".Shopware()->System()->sCurrency["currency"];
								}
							}
							if (!empty($text)) $element["value"] = $text;
							break;
						case "detail":
							if (!empty($this->Request()->sOrdernumber)){
								$getName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($this->Request()->sOrdernumber);
								$text =  Shopware()->System()->sCONFIG["sSnippets"]["sINQUIRYTEXTARTICLE"];
								$text .= " ".$getName;
								$element["value"] = $text;
							}
							break;
					}
				}
				$Fields[$element['id']] =  Shopware()->Modules()->CmsSupport()->create_input_element($element,$this->Post[$element['id']]);
				$Labels[$element['id']] =  Shopware()->Modules()->CmsSupport()->create_label_element($element);
			}
		}
		
		$this->View()->sSupport = Shopware()->Modules()->CmsSupport()->sSUPPORT;
		$this->View()->sSupport = array_merge($this->View()->sSupport,array("sErrors"=>$this->Errors,"sElements"=>$this->FormElements,"sFields"=>$Fields,"sLabels"=>$Labels));
		$this->View()->rand = md5(uniqid(rand()));
		if (!count($this->Errors) && !empty($this->Request()->Submit)){
			$this->commitForm();
			$this->View()->sSupport = array_merge($this->View()->sSupport,array("sElements"=>""));
		}
	
	}
	
	public function commitForm(){
		
		$mail = Shopware()->System()->sMailer;			
		$template = Shopware()->Config()->Templates->sSUPPORT;						
		$mail->IsHTML($template['ishtml']);
		
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//+++++++++++++++++++++++++SET MAIL FROM ATTR+++++++++++++++++++++++++++++++++++		
		
		//eMail field available check
		foreach ($this->FormElements as $element) {
			if($element['typ'] == "email")
			{
				$post_email = $this->Post[$element['id']];
				$post_email = trim($post_email);
			}
		}
		
		if(!empty($post_email)){
			$mail->From     = $post_email;
		}else{
			$mail->From     = Shopware()->Config()->Mail;
		}
		
		$content = $this->View()->sSupport;
		
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		
		$mail->FromName = $mail->From;
		
		$mail->Subject  = $content["email_subject"];
		$mail->Body     = $content["email_template"];
		
		foreach ($this->Post as $key => $value)
		{
			if($this->FormElements[$key]['typ']=="text2")
			{
				$names = explode(";",$this->FormElements[$key]['name']);
				
				$mail->Body = str_replace("{sVars.".$names[0]."}",$value[0],$mail->Body);
				$mail->Body = str_replace("{sVars.".$names[1]."}",$value[1],$mail->Body);
			}
			else 
			{
				$mail->Body = str_replace("{sVars.".$this->FormElements[$key]['name']."}",$value,$mail->Body);
			}
		}

		$mail->Body = str_replace("{sIP}",$_SERVER['REMOTE_ADDR'],$mail->Body);
		$mail->Body = str_replace("{sDateTime}",date("d.m.Y h:i:s"),$mail->Body);
		$mail->Body = strip_tags($mail->Body);
		
		$mail->Body = htmlentities($mail->Body);

		$mail->ClearAddresses();
		
		$mail->AddAddress($content["email"], "");
		
		if (!$mail->Send()){
			throw new Enlight_Exception("Could not send mail");
		}
	}
}