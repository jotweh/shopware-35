<?php
class sViewportPassword{
	var $sSYSTEM;
	
	function sRender(){
		
		if ($this->sSYSTEM->_POST["sAction"]=="renewPassword"){
			
			$email = $this->sSYSTEM->_POST["email"];
			$email = strtolower(stripslashes(htmlspecialchars($email)));
			$reg = "/^(([^<>()[\]\\\\.,;:\s@\"]+(\.[^<>()[\]\\\\.,;:\s@\"]+)*)|(\"([^\"\\\\\r]|(\\\\[\w\W]))*\"))@((\[([0-9]{1,3}\.){3}[0-9]{1,3}\])|(([a-z\-0-9áàäçéèêñóòôöüæøå]+\.)+[a-z]{2,}))$/i";
			if(!preg_match($reg, $email)){
				$variables["sErrorMessages"][] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorForgotMailUnknown'];
				unset($email);
			}
			
			if (empty($email)){
				$variables["sErrorMessages"][] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorForgotMail'];
				
			}else {
				
				// Check if user exists
				$userId = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserByMail($email);
				if (empty($userId)){
					$variables["sErrorMessages"][] = $this->sSYSTEM->sCONFIG['sErrors']['sErrorForgotMailUnknown'];
				}else {
					$newPassword = substr(md5(uniqid(rand())),0,6);
					$newPasswordDatabase = md5($newPassword);
					
					// Update user account
					$updateUser = $this->sSYSTEM->sDB_CONNECTION->Execute("UPDATE s_user SET password='$newPasswordDatabase'
					WHERE id=$userId
					");
					
					// Composing info-email
					$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content'] = str_replace("{sMail}",$email, $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content']);
					$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content'] = str_replace("{sPassword}",$newPassword, $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content']);
					$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['subject'] = str_replace("{sShop}",$this->sSYSTEM->sCONFIG['sSHOPNAME'],$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['subject']);					
					$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content'] = str_replace("{sShopURL}","http://".$this->sSYSTEM->sCONFIG['sBASEPATH'], $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content']);
					
					$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['contentHTML'] = str_replace("{sMail}",$email, $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['contentHTML']);
					$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['contentHTML'] = str_replace("{sPassword}",$newPassword, $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['contentHTML']);
					$this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['contentHTML'] = str_replace("{sShopURL}","http://".$this->sSYSTEM->sCONFIG['sBASEPATH'], $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['contentHTML']);
										
					$mail           = is_object($this->sSYSTEM->sMailer) ? $this->sSYSTEM->sMailer : new PHPMailer;				
					$mail->From     = $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['frommail'];
					$mail->FromName = $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['fromname'];
					
					$mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['subject'];
					if ($this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['ishtml']){
						$mail->IsHTML(0);
						$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['contentHTML'];
						$mail->AltBody     = $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content'];
					}else {
						$mail->IsHTML(0);
						$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sPASSWORD']['content'];
					}
					
					$mail->ClearAddresses();
				
					$mail->AddAddress($email, "");
					
					if (!$mail->Send()){
						$this->sSYSTEM->E_CORE_WARNING ("##01 ForgotPassword","Could not send eMail");
					}else {
						$variables["sSuccess"] = true;
					}
					
				}
			}
			
		}

		$templates = array("sContainer"=>"/login/login_newpassword.tpl","sContainerRight"=>"/articles/article_tellafriend_right.tpl");
		
		
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
	
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
	
	
}
?>