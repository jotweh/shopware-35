<?php
class sViewportNewsletter
{
	var $sSYSTEM;
	function sRender()
	{
		$variables = array();
		if ($this->sSYSTEM->_GET["sConfirmation"]){
			$hash = $this->sSYSTEM->sDB_CONNECTION->qstr($this->sSYSTEM->_GET["sConfirmation"]);
			$getVote = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT * FROM s_core_optin WHERE hash = $hash
			");
			if (!empty($getVote["data"])){
				$this->sSYSTEM->_POST = unserialize($getVote["data"]);
				$voteConfirmed = true;
				$this->sSYSTEM->sDB_CONNECTION->Execute("
				DELETE FROM s_core_optin WHERE hash = $hash
				");
			}else {
				$voteConfirmed = false;
			}
		}else {
			$voteConfirmed = false;
		}

		if(isset($this->sSYSTEM->_POST["newsletter"]))
		{
			if($this->sSYSTEM->_POST["chkmail"]==1)
			{
				$unsubscribe = false;
				
				if (empty($this->sSYSTEM->sCONFIG["sOPTINNEWSLETTER"]) || $voteConfirmed)
				{
					if (empty($this->sSYSTEM->_POST["sCustomer"])){
						$variables["sStatus"] = $this->sSYSTEM->sMODULES['sAdmin']->sNewsletterSubscription($this->sSYSTEM->_POST["newsletter"],$unsubscribe);
					} else {
						$this->sSYSTEM->sMODULES['sAdmin']->sSaveRegisterNewsletter(array("auth"=>array("email"=>$this->sSYSTEM->_POST["newsletter"])));
						$variables["sStatus"] = array("code"=>3,"message"=>$this->sSYSTEM->sCONFIG['sSnippets']["sInfoEmailRegiested"]);
					}
					if($variables['sStatus']['code']==3)
					{
						// Send mail to subscriber
						$mail           = $this->sSYSTEM->sMailer;
						$mail->IsHTML($this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['ishtml']);
						$mail->From     = $this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['frommail'];
						$mail->FromName = $this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['fromname'];
						$mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['subject'];
						if ($this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['ishtml']){
					    	$mail->IsHTML(1);	
					    	$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['contentHTML'];
					    	$mail->AltBody = $this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['content'];
					    }else {
					    	$mail->IsHTML(0);
					    	$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sNEWSLETTERCONFIRMATION']['content'];
					    }
					    $mail->Body = str_replace("{sMAIL}",$email,$mail->Body);
					    foreach ($this->sSYSTEM->_POST as $key => $value)
					    {
					    	$mail->Subject = str_replace('{$sUser.'.$key.'}', $value, $mail->Subject);
					    	$mail->Body = str_replace('{$sUser.'.$key.'}', $value, $mail->Body);
					    	$mail->AltBody = str_replace('{$sUser.'.$key.'}', $value, $mail->AltBody);
					    }
					    $mail->ClearAddresses();
					    $mail->AddAddress($this->sSYSTEM->_POST["newsletter"], "");
					    $mail->Send();
					}
				}
				else
				{
					$variables["sStatus"] = $this->sSYSTEM->sMODULES['sAdmin']->sNewsletterSubscription($this->sSYSTEM->_POST["newsletter"],false);
					if($variables["sStatus"]["code"]==3)
					{
						$this->sSYSTEM->sMODULES['sAdmin']->sNewsletterSubscription($this->sSYSTEM->_POST["newsletter"], true);
	
						$mail           = $this->sSYSTEM->sMailer;
						$mail->IsHTML($this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['ishtml']);
						$mail->From     = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['frommail'];
						$mail->FromName = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['fromname'];
						$mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['subject'];
						if ($this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['ishtml']){
					    	$mail->IsHTML(1);	
					    	$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['contentHTML'];
					    	$mail->AltBody = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['content'];
					    }else {
					    	$mail->IsHTML(0);
					    	$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINNEWSLETTER']['content'];
					    }
					    // Generate confirmation link
					    $hash = md5(uniqid(rand()));
					    $data = $this->sSYSTEM->sDB_CONNECTION->qstr(serialize($this->sSYSTEM->_POST));
					    $link = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/".$this->sSYSTEM->sCONFIG["sBASEFILE"]."?sViewport=newsletter";
						$link .= "&sConfirmation=$hash";
						// Setting status-code
						$variables["sStatus"]["code"] = 3;
						$variables["sStatus"]["message"] = $this->sSYSTEM->sCONFIG['sSnippets']["sMailConfirmation"];
						
					    $mail->Body = str_replace("{\$sConfirmLink}",$link,$mail->Body);
					    foreach ($this->sSYSTEM->_POST as $key => $value)
					    {
					    	$mail->Subject = str_replace('{$sUser.'.$key.'}', $value, $mail->Subject);
					    	$mail->Body = str_replace('{$sUser.'.$key.'}', $value, $mail->Body);
					    	$mail->AltBody = str_replace('{$sUser.'.$key.'}', $value, $mail->AltBody);
					    }
					    
					    $mail->AltBody = str_replace("","",$mail->AltBody);
					    
					    $this->sSYSTEM->sDB_CONNECTION->Execute("
					    INSERT INTO s_core_optin (datum,hash,data)
					    VALUES (
					    now(),'$hash',$data
					    )
					    ");
						
						$mail->ClearAddresses();
						$mail->AddAddress($this->sSYSTEM->_POST["newsletter"], "");
						$mail->Send();
					}
				}
			}
			else
			{
				$unsubscribe = true;
				$variables["sStatus"] = $this->sSYSTEM->sMODULES['sAdmin']->sNewsletterSubscription($this->sSYSTEM->_POST["newsletter"],$unsubscribe);
			}
		}
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		$templates = array("sContainer"=>"/newsletter/newsletter.tpl");
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>