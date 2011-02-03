<?php
/*
Article-Details
*/
class sViewportDetail{
	var $sSYSTEM;
	
	function sRender(){
	
		if (!$this->sSYSTEM->_GET['sArticle'] || !is_numeric($this->sSYSTEM->_GET['sArticle'])) return false;
		
		
		$sArticle = ($this->sSYSTEM->sMODULES['sArticles']->sGetArticleById());
		
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
		if (!empty($this->sSYSTEM->_SESSION["sUserId"]) && empty($this->sSYSTEM->_POST["sVoteName"])){
			$userData = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserData();
			$this->sSYSTEM->_POST["sVoteName"] = $userData["billingaddress"]["firstname"]." ".$userData["billingaddress"]["lastname"];
		}
		
		if ($this->sSYSTEM->_POST["sAction"]=="saveComment"){
			if (!$this->sSYSTEM->_POST["sVoteName"]) $sErrorFlag["sVoteName"] = true;
			if (!$this->sSYSTEM->_POST["sVoteSummary"]) $sErrorFlag["sVoteSummary"] = true;
			$captcha = $this->sSYSTEM->sCONFIG["sTEMPLATEPATH"]."/".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."/media/img/default/captcha.jpg";
			if (!is_file($captcha)){
				$captcha = "templates/0/de/media/img/default/captcha.jpg";
			}
			if (is_file($captcha) && empty($voteConfirmed)){
				if ($this->sSYSTEM->_POST["sCaptcha"] != str_replace(" ","",$this->sSYSTEM->_SESSION["sCaptcha"])){
					$sErrorFlag["sCaptcha"] = true;
				}else {
					unset($this->sSYSTEM->_SESSION["sCaptcha"]);
				}
			}
			
			if (!empty($this->sSYSTEM->sCONFIG["sOPTINVOTE"]) && (empty($this->sSYSTEM->_POST["sVoteMail"]) || !preg_match("/^(([^<>()[\]\\\\.,;:\s@\"]+(\.[^<>()[\]\\\\.,;:\s@\"]+)*)|(\"([^\"\\\\\r]|(\\\\[\w\W]))*\"))@((\[([0-9]{1,3}\.){3}[0-9]{1,3}\])|(([a-z\-0-9áàäçéèêñóòôöüæøå]+\.)+[a-z]{2,}))$/i", $this->sSYSTEM->_POST["sVoteMail"]))){
				$sErrorFlag["sVoteMail"] = true;
			}
						
			if (!count($sErrorFlag)){
				// Opt-In for Voting
				
				if (!empty($this->sSYSTEM->sCONFIG["sOPTINVOTE"]) && empty($voteConfirmed) && empty($this->sSYSTEM->_SESSION["sUserId"])){
					
					// Send eMail-confirmation first
					$mail  = $this->sSYSTEM->sMailer;
					
					$mail->IsHTML($this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['ishtml']);
					$mail->From     = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['frommail'];
					$mail->FromName = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['fromname'];
					$mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['subject'];
					if ($this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['ishtml']){
				    	$mail->IsHTML(1);	
				    	$mail->Body = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['contentHTML'];
				    	$mail->AltBody = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['content'];
				    }else {
				    	$mail->IsHTML(0);
				    	$mail->Body = $this->sSYSTEM->sCONFIG['sTemplates']['sOPTINVOTE']['content'];
				    }
				  	
				    // Generate confirmation link
				    $hash = md5(uniqid(rand()));
				    $data = $this->sSYSTEM->sDB_CONNECTION->qstr(serialize($this->sSYSTEM->_POST));
				    $link = $this->sSYSTEM->rewriteLink(array(
					2=>$sArticle["linkDetails"],3=>$sArticle["articleName"],4=>"Test"
					),true);
					$link .= "?sConfirmation=$hash";
		
				    $mail->Body = str_replace("{\$sConfirmLink}",$link,$mail->Body);
				    $mail->Body = str_replace("{\$sArticle.articleName}",$sArticle["articleName"],$mail->Body);
				    
				    $mail->AltBody = str_replace("","",$mail->AltBody);
				    
				    $this->sSYSTEM->sDB_CONNECTION->Execute("
				    INSERT INTO s_core_optin (datum,hash,data)
				    VALUES (
				    now(),'$hash',$data
				    )
				    ");
					
					$mail->ClearAddresses();
					$mail->AddAddress($this->sSYSTEM->_POST["sVoteMail"], "");
					
					$mail->Send();
				}else {
					unset($this->sSYSTEM->sCONFIG["sOPTINVOTE"]);
					$this->sSYSTEM->sMODULES['sArticles']->sSaveComment($this->sSYSTEM->_GET['sArticle']);
				}
				$this->sSYSTEM->_POST['sAction'] = "saveComment";
				// Opt-In for Voting
			}
			
		}
		
		if ($this->sSYSTEM->_POST["sAction"]=="sendArticleNotification") {
            $sNotificationEmail = $this->sSYSTEM->_POST["sNotificationEmail"];
            $sShowWrongEmailMessage = false;
            $pattern="/^[_a-zA-Z0-9-](.{0,1}[_a-zA-Z0-9-])*@([a-zA-Z0-9-]{2,}.){0,}[a-zA-Z0-9-]{3,}(.[a-zA-Z]{2,4}){1,2}$/";
            if(!$sNotificationEmail || !preg_match($pattern, $sNotificationEmail)) {
                $sErrorFlag["sNotificationEmail"] = true;
                $sShowWrongEmailMessage = true;
            } 
             else if(isset($this->sSYSTEM->_POST["sArticle"])) {    
                 //Put into an array
                 if(is_array($this->sSYSTEM->_SESSION["sNotificatedArticles"])) {
                     if(in_array($this->sSYSTEM->_POST["sArticle"], $this->sSYSTEM->_SESSION["sNotificatedArticles"])){
                          $sErrorFlag["sNotificatedArticles"] = true;
                          $sShowNotificationFunction = false;
                     }
                     else{
                         $this->sSYSTEM->_SESSION["sNotificatedArticles"][] = $this->sSYSTEM->_POST["sArticle"];
                     }
                 }
                 else{
                     $this->sSYSTEM->_SESSION["sNotificatedArticles"] = array($this->sSYSTEM->_POST["sArticle"]);
                 }
             }
             else {
                  $sErrorFlag["sNotificatedArticles"] = true;
             }
            if (!count($sErrorFlag)) {
                $isAlreadyAvailable = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                    SELECT *  FROM `s_articles_notification` 
                    WHERE `ordernumber`=? 
                    AND `mail` = ?
                    AND send = 0
                ", array($this->sSYSTEM->_POST["sArticle"],$sNotificationEmail));
                $_POST["sLanguage"] = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"];
                $_POST["sShopPath"] = "http://".$this->sSYSTEM->sCONFIG["sBASEPATH"]."/".$this->sSYSTEM->sCONFIG["sBASEFILE"];
                if(empty($isAlreadyAvailable)) {
                    $sAlreadyForArticleRegistered = false;
                    $mail = $this->sSYSTEM->sMailer;
                    $mail->IsHTML($this->sSYSTEM->sCONFIG['sTemplates']['sACCEPTNOTIFICATION']['ishtml']);
                    $mail->From     = $this->sSYSTEM->sCONFIG['sTemplates']['sACCEPTNOTIFICATION']['frommail'];
                    $mail->FromName = $this->sSYSTEM->sCONFIG['sTemplates']['sACCEPTNOTIFICATION']['fromname'];
                    $mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sACCEPTNOTIFICATION']['subject'];
                    if ($this->sSYSTEM->sCONFIG['sTemplates']['sACCEPTNOTIFICATION']['ishtml']){
                        $mail->IsHTML(1);    
                        $mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sACCEPTNOTIFICATION']['contentHTML'];
                    }else {
                        $mail->IsHTML(0);
                        $mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sACCEPTNOTIFICATION']['content'];
                    }
                   
                    // Generate confirmation link
                    $hash = md5(uniqid(rand()));
                    $data = $this->sSYSTEM->sDB_CONNECTION->qstr(serialize($_POST));
                    $sCONFIG['sUSESSL'] == 1 ? $http = "https://" : $http = "http://";
                    /*$link = $http.$this->sSYSTEM->sCONFIG['sBASEPATH']."/".$this->sSYSTEM->sCONFIG["sBASEFILE"]."_detail_".$sArticle["articleID"];
                    $link .= ".html?sNotificationConfirmation=$hash"."&sNotify=1";
                    */
                    $link = $this->sSYSTEM->rewriteLink(array(
                    2=>$sArticle["linkDetails"],3=>$sArticle["articleName"],4=>""
                    ),true);
                    $link .= "?sNotificationConfirmation=$hash&sNotify=1";
                    $mail->Body = str_replace("{\$sConfirmLink}",$link,$mail->Body);
                    $mail->Body = str_replace("{\$sArticleName}",$sArticle["articleName"],$mail->Body);
                    $this->sSYSTEM->sDB_CONNECTION->Execute("
                    INSERT INTO s_core_optin (datum,hash,data)
                    VALUES (
                    now(),'$hash',$data
                    )
                    ");
         
                    $mail->ClearAddresses();
                    $mail->AddAddress($sNotificationEmail, "");
                    $mail->Send();
                }
                else {
                    $sAlreadyForArticleRegistered = true;
                }
            }
        }

		
		
		
		if ($this->sSYSTEM->_GET["sNotificationConfirmation"] && $this->sSYSTEM->_GET["sNotify"]) {
			$hash = $this->sSYSTEM->sDB_CONNECTION->qstr($this->sSYSTEM->_GET["sNotificationConfirmation"]);
			$getConfirmation = $this->sSYSTEM->sDB_CONNECTION->GetRow("
			SELECT * FROM s_core_optin WHERE hash = $hash
			");
			if (!empty($getConfirmation["hash"])){
				$notificationConfirmed = true;
				$json_data = unserialize($getConfirmation["data"]);
				$date = date("Y-m-d h:i:s");
				$this->sSYSTEM->sDB_CONNECTION->Execute("
				DELETE FROM s_core_optin WHERE hash = $hash
				");
			}else {
				$notificationConfirmed = false;
			}
	
			if ($notificationConfirmed){
				$this->sSYSTEM->sDB_CONNECTION->Execute("INSERT INTO `s_articles_notification` (
					`ordernumber` ,
					`date` ,
					`mail` ,
					`language` ,
					`shopLink` ,
					`send`
					)
					VALUES (
						?,?,?,?,?,'0'
					)", array($json_data["sArticle"],$date,$json_data["sNotificationEmail"],$json_data["sLanguage"],$json_data["sShopPath"]));
				$notifyMessage["sConfirmed"] = 1;
				$notifyMessage["sMessage"] =  $this->sSYSTEM->sCONFIG['sSnippets']["sRegisterForNotificationValid"];
			}
			else {
				// Send eMail-confirmation first
				$notifyMessage["sConfirmed"] = 0;
				$notifyMessage["sMessage"] =  $this->sSYSTEM->sCONFIG['sSnippets']["sRegisterNotificationInValid"];
			}
		}
		
		if(is_array($this->sSYSTEM->_SESSION["sNotificatedArticles"])) {
			foreach ($sArticle["sVariants"] as $variantArray) {
				if(in_array($variantArray["ordernumber"],$this->sSYSTEM->_SESSION["sNotificatedArticles"])) {
					$notificationVariants[] = $variantArray["ordernumber"];
				}
			}
			if(in_array($sArticle["ordernumber"],$this->sSYSTEM->_SESSION["sNotificatedArticles"])) {
		 		$sShowNotificationFunction = false;
		 	}
		 	else{
		 		$sShowNotificationFunction = true;
		 	}
		}
		else{
			$sShowNotificationFunction = true;
		}
		
		
		
		$sArticle = $this->sSYSTEM->sMODULES['sArticles']->sGetConfiguratorImage($sArticle);
		
		if (!empty($sArticle["images"])){
			foreach ($sArticle["images"] as $key => $image){ 
			    if ($image["relations"]=="&{}" || $image["relations"]=="||{}"){ 
			            $sArticle["images"][$key]["relations"] = ""; 
			    } 
			} 
		}
		
		//Load Bundlearticles 
		$sArticle['sBundles'] = $this->sSYSTEM->sMODULES['sArticles']->sGetArticleBundlesByArticleID($sArticle['articleID']);
	
		
		if (!isset($sArticle["articleName"])){
			
			$charts = $this->sSYSTEM->sMODULES['sMarketing']->sGetSimilarArticles();
			// No articles in basket
			$templates = array(
			"sContainer"=>"/error/error.tpl",
			"sContainerRight"=>""
			);
			
			$variables = array("sError"=>"sDetails - Article {$this->sSYSTEM->_GET['sArticle']} not found","sCross"=>$charts);
			return array("templates"=>$templates,"variables"=>$variables);
		}
		
		if (isset($this->sSYSTEM->_GET['sCategory'])){
			
			$categoryBreadcrumb = array_reverse(($this->sSYSTEM->sMODULES['sCategories']->sGetCategoriesByParent($this->sSYSTEM->_GET['sCategory'])));
		}else {
			$categoryBreadcrumb = array("");
		}
		
		$categoryBreadcrumb[] = array(
			'link' => $sArticle['linkDetails'],
			'name' => $sArticle['articleName']
		);
		
		if (count($categoryBreadcrumb)>1){
			$catindex = count($categoryBreadcrumb)-2;
		}else {
			$catindex = 0;
		}
		
		
		$sql = "
	    SELECT `id`,`active` FROM `s_core_paymentmeans`
	    WHERE `name` = 'hanseatic'
	    ";
	    $res = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
	    
	    if (!empty($res["active"])){
	    	 // Hanseatic Module Integration Start
		    $path = "engine/connectors/";
		    require("engine/connectors/hanseatic/hanseatic.class.php");								
		    $hbObj = new hanseaticPayment("/dev/null",$path);			
		    $isActiveHanseatic = $hbObj->isActive();
				
		    $amount = $sArticle['price'];
		    $zinssatz = 1 + (sHANSEATIC_PERCENT / 100);
		    // Eingestellte Zinsen aufrechnen
		    $amount = $amount * $zinssatz;
		    $lowestRate = $hbObj->getLowestRate($amount);
		    $rateMonth = key($lowestRate);
		    $rateAmount = $lowestRate[$rateMonth];
		    // Wenn Raten möglich
		    if ($isActiveHanseatic && !empty($rateAmount)){
		      $sArticle['sFinance'] = array(
		        'calculator' => $hbObj->getCalculatorCode($amount),
		        'rateMonth' => $rateMonth,
		        'rateAmount' => $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($rateAmount / 100),
		      );
		    }
	    }
	
		$sArticle["sShowNotificationFunction"] = $sShowNotificationFunction;
		$sArticle["sShowWrongEmailMessage"] = $sShowWrongEmailMessage;
		$sArticle["sNotificationVariants"] = $notificationVariants;
		$sArticle["sAlreadyForArticleRegistered"] = $sAlreadyForArticleRegistered;
		$sArticle["notifyMessage"] = $notifyMessage;
		$sArticle["notifyLicence"] = $this->sSYSTEM->sCheckLicense("","",$this->sSYSTEM->sLicenseData["sPREMIUM"]);
		$sArticle["showBasketOnNotification"] = $this->sSYSTEM->sCONFIG['sDEACTIVATEBASKETONNOTIFICATION'];
	   
	    // Hanseatic Module Integration Ende 
		// Get Article details
		$variables = array(
		"sBreadcrumb"=>$categoryBreadcrumb,
		"sCategoryInfo"=>$categoryBreadcrumb[$catindex],
		"sArticle"=>$sArticle,
		"sErrorFlag"=>isset($sErrorFlag) ? $sErrorFlag : ""
		);
		
		// Show inquiry-formular
		if (!empty($this->sSYSTEM->sCONFIG["sINQUIRYVALUE"])){
			// Show Link to inquiry formular
			$variables["sInquiry"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=support&sFid=".$this->sSYSTEM->sCONFIG["sINQUIRYID"]."&sInquiry=detail&sOrdernumber={$sArticle["ordernumber"]}";
		}
		
		if (!empty($sArticle["template"])){
		
			$templates = array(
			"sContainer"=>"/articles/".$sArticle["template"],
			"sContainerRight"=>"/articles/article_details_right.tpl"
			);
		}elseif (!empty($sArticle["mode"])){
			
			$templates = array(
			"sContainer"=>"/blog/details.tpl",
			"sContainerRight"=>"/articles/article_details_right.tpl"
			);
		}else {
			
			$templates = array(
			"sContainer"=>"/articles/article_details_middle.tpl",
			"sContainerRight"=>"/articles/article_details_right.tpl"
			);
		}
	
		
		eval($this->sSYSTEM->sCallHookPoint("s_detail.php_sRender_BeforeEnd"));
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>