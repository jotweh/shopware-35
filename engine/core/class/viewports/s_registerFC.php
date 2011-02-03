<?php
include("s_register3.php");
include("s_register2shipping.php");

class sViewportRegisterFC{
	var $sSYSTEM;
	var $sViewportRegister3;
	var $sViewportRegister2Shipping;
	
	function sViewportRegisterFC(&$sSYSTEM){
		$this->sViewportRegister3 = new sViewportRegister3;
		$this->sViewportRegister3->sSYSTEM = $sSYSTEM;
		
		$this->sViewportRegister2Shipping = new sViewportRegister2shipping($this->sSYSTEM,$this->sViewportRegister3);
		$this->sViewportRegister2Shipping->sSYSTEM = $sSYSTEM;
	}
	function sRender(){
		$this->sSYSTEM->_SESSION["sRegisterFinished"] = false;

		if ($this->sSYSTEM->_POST['sAction']=="register1"){
		
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep1());
			// Checking tax id 
			if (!empty($this->sSYSTEM->_POST["ustid"])){
				$result = $this->sSYSTEM->sMODULES['sAdmin']->sCheckTaxID($this->sSYSTEM->_POST["ustid"],$this->sSYSTEM->_POST["country"]);
				$checkData = array_merge($checkData,$result);
			}
			if (!count($checkData["sErrorMessages"])){
				$accountError = false;
				
			}else {
			
				// Uups, something goes wrong - pass error through template
				$variables["sErrorFlag"] = $checkData["sErrorFlag"];
				$variables["sErrorMessages"] = $checkData["sErrorMessages"];
				$accountError = true;

			}
			
			// Data for Step-2
			
			// Define field-rules
			$rules = array(
			"salutation"=>array("required"=>1),
			"company"=>array("addicted"=>array("salutation"=>"company"),"required"=>1),
			"firstname"=>array("required"=>1),
			"lastname"=>array("required"=>1),
			"street"=>array("required"=>1),
			"streetnumber"=>array("required"=>1),
			"zipcode"=>array("required"=>1),
			"city"=>array("required"=>1),
			"phone"=>array("required"=>1),
			"country"=>array("required"=>1),
			"department"=>array("required"=>0),
			"fax"=>array("required"=>0),
			"shippingAddress"=>array("required"=>0),
			"ustid"=>array("required"=>0),
			"text1"=>array("required"=>0),
			"text2"=>array("required"=>0),
			"text3"=>array("required"=>0),
			"text4"=>array("required"=>0),
			"text5"=>array("required"=>0),
			"text6"=>array("required"=>0),
			"sValidation"=>array("required"=>0),
			"birthyear"=>array("required"=>0),
			"birthmonth"=>array("required"=>0),
			"birthday"=>array("required"=>0)			
			);
			
			if (!empty($this->sSYSTEM->sCONFIG["sACTDPRCHECK"])){
				$rules["dpacheckbox"]["required"] = 1;
			}
			
            if($this->sSYSTEM->_POST['birthday'] == "--") 
               $this->sSYSTEM->_POST['birthday'] = ""; 
                     
        	if($this->sSYSTEM->_POST['birthmonth'] == "-") 
                $this->sSYSTEM->_POST['birthmonth'] = ""; 
                     
            if($this->sSYSTEM->_POST['birthyear'] == "----") 
                $this->sSYSTEM->_POST['birthyear'] = ""; 
			
			
		
			// Check data 
			$checkData = ($this->sSYSTEM->sMODULES['sAdmin']->sValidateStep2($rules));
			
			if (!count($checkData["sErrorMessages"])  && !$accountError){
				// Send eMail notification
				if (!empty($this->sSYSTEM->_POST["receiveNewsletter"])){
					if (!empty($this->sSYSTEM->sCONFIG["sOPTINNEWSLETTER"])){
						unset($this->sSYSTEM->_SESSION["sRegister"]["auth"]["receiveNewsletter"]);
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
					    $data = $this->sSYSTEM->sDB_CONNECTION->qstr(serialize(array("newsletter"=>$this->sSYSTEM->_POST["email"],"chkmail"=>1,"sCustomer"=>1)));
					    $link = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/".$this->sSYSTEM->sCONFIG["sBASEFILE"]."?sViewport=newsletter";
						$link .= "&sConfirmation=$hash";
					    $mail->Body = str_replace("{\$sConfirmLink}",$link,$mail->Body);
					    $mail->AltBody = str_replace("","",$mail->AltBody);
					    $this->sSYSTEM->sDB_CONNECTION->Execute("
					    INSERT INTO s_core_optin (datum,hash,data)
					    VALUES (
					    now(),'$hash',$data
					    )
					    ");
						
						$mail->ClearAddresses();
						$mail->AddAddress($this->sSYSTEM->_POST["email"], "");
						$mail->Send();
					}
					
				}
				// Next step 
				if ($this->sSYSTEM->_POST["shippingAddress"]){
					$this->sSYSTEM->_GET["sViewport"] = "register2shipping";
					return $this->sViewportRegister2Shipping->sRender();					
				}else {
					$this->sSYSTEM->_GET["sViewport"] = "register3";
					return $this->sViewportRegister3->sRender();
				}
				
			}else {
				// Uups, something goes wrong - pass error through template
				if (!count($variables["sErrorFlag"])) $variables["sErrorFlag"] = array();
				if (!count($variables["sErrorMessages"])) $variables["sErrorMessages"] = array();
				$checkData["sErrorFlag"]["password"] = true;
				$checkData["sErrorFlag"]["passwordConfirmation"] = true;
				
				if (count($checkData["sErrorFlag"])){
					$variables["sErrorFlag"] = array_merge($variables["sErrorFlag"],$checkData["sErrorFlag"]);
				}
				if (count($checkData["sErrorMessages"])){
					$variables["sErrorMessages"] = array_merge($variables["sErrorMessages"],$checkData["sErrorMessages"]);
				}
			}
			
		}
		
	
		if ($this->sSYSTEM->_POST['sValidation']) $this->sSYSTEM->_GET['sValidation'] = $this->sSYSTEM->_POST['sValidation'];
		
		$variables["sEsd"] =  $this->sSYSTEM->sMODULES['sBasket']->sCheckForESD();
		
		if(isset($this->sSYSTEM->_SESSION['sCountry'])&&!isset($this->sSYSTEM->_POST['country'])){
			
			$this->sSYSTEM->_POST['country'] = (int) $this->sSYSTEM->_SESSION['sCountry'];
			
		}
		elseif (isset($this->sSYSTEM->_POST['country'])){
			
			$this->sSYSTEM->_SESSION['sCountry'] = (int) $this->sSYSTEM->_POST['country'];
			
		}
		
		$variables["sCountryList"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetCountryList();
		foreach ($variables["sCountryList"] as $key => $value){
			if ($value["id"] == $this->sSYSTEM->_SESSION["sRegister"]["billing"]["country"]){
				$variables["sCountryList"][$key]["flag"] = true;
			}
		}
	
		// Show default-values if user uses back-button
		if ($this->sSYSTEM->_SESSION["sRegister"]["auth"]["email"]){
			$this->sSYSTEM->_POST["email"] = $this->sSYSTEM->_SESSION["sRegister"]["auth"]["email"];
		}
		if ($this->sSYSTEM->_SESSION["sRegister"]["auth"]["accountmode"]==1){
			$this->sSYSTEM->_POST["skipLogin"] = true;
		}
		if (count($this->sSYSTEM->_SESSION["sRegister"]["billing"])){
			foreach ($this->sSYSTEM->_SESSION["sRegister"]["billing"] as $key => $value){
				$this->sSYSTEM->_POST[$key] = $value;
			}
		}
		
		
		$templates = array("sContainer"=>"/register/register_step_1.tpl","sContainerRight"=>"/register/register_right.tpl");
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>