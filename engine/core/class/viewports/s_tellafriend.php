<?php

class sViewportTellafriend{
	var $sSYSTEM;
	
	function sRender(){
		if (empty($this->sSYSTEM->_GET["sDetails"])){
			$this->sSYSTEM->_GET["sDetails"] = $this->sSYSTEM->_GET["sArticle"];
		}
		if (empty($this->sSYSTEM->_GET["sDetails"])) return;
		$this->sSYSTEM->_GET["sCategory"] = intval($this->sSYSTEM->_GET["sCategory"]);
		$this->sSYSTEM->_GET["c"] = intval($this->sSYSTEM->_GET["c"]);
		// Get Article-Information
		$sArticle = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById('fix',0,intval($this->sSYSTEM->_GET['sDetails']));
		if (!$sArticle["articleName"]) return;
		
		// Get similar articles
		// =================================================.
		$getSimilarArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],"
		SELECT relatedarticle FROM s_articles_similar WHERE articleID={$sArticle["articleID"]}
		");
		if (count($getSimilarArticles)){
			foreach ($getSimilarArticles as $relatedArticleKey => $relatedArticleValue){
					$tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix",0,$relatedArticleValue['relatedarticle']);
					if (count($tmpContainer) && isset($tmpContainer["articleName"])){
						$sArticle["sSimilarArticles"][] = $tmpContainer;
					}
			}
		}else {
			
			$sql = "
			SELECT categoryparentID FROM s_articles_categories
			WHERE articleID={$sArticle['articleID']} ORDER BY id ASC LIMIT 1
			";
		
		
			$queryArticleCategory = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sql);
			if (isset($queryArticleCategory["categoryparentID"])){
				$this->sSYSTEM->_GET['sCategory'] = $queryArticleCategory["categoryparentID"];
			}else {
				$this->sSYSTEM->_GET['sCategory'] = null;
			}
			
			$sqlGetCategory = "
			SELECT DISTINCT s_articles.id AS relatedarticle FROM s_articles_categories, s_articles, s_articles_details 
			WHERE s_articles_categories.categoryID=".$this->sSYSTEM->_GET["sCategory"]."
			AND s_articles.id=s_articles_categories.articleID AND s_articles.id=s_articles_details.articleID
			AND s_articles_details.kind=1 
			AND s_articles.id!={$sArticle["articleID"]}
			AND s_articles.active=1
			ORDER BY s_articles_details.sales DESC LIMIT 3
			";
			
			$getSimilarArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'],$sqlGetCategory);
			
			foreach ($getSimilarArticles as $relatedArticleKey => $relatedArticleValue){
					$tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix",0,$relatedArticleValue['relatedarticle']);
					if (count($tmpContainer) && isset($tmpContainer["articleName"])){
						$sArticle["sSimilarArticles"][] = $tmpContainer;
					}
			}
			
			if (!count($getSimilarArticles)){
				$sArticle["sSimilarArticles"] = array();
			}
		}
				
		if ($this->sSYSTEM->_POST["sMailTo"]){
			if (!$this->sSYSTEM->_POST["sName"]) $variables["sError"] = true;
			if (!$this->sSYSTEM->_POST["sMail"]) $variables["sError"] = true;
			if (!$this->sSYSTEM->_POST["sRecipient"]) $variables["sError"] = true;	
			if (preg_match("/;/",$this->sSYSTEM->_POST["sRecipient"]) || strlen($this->sSYSTEM->_POST["sRecipient"]>=50)){
				$variables["sError"] = true;
			}
			$reg = "/^(([^<>()[\]\\\\.,;:\s@\"]+(\.[^<>()[\]\\\\.,;:\s@\"]+)*)|(\"([^\"\\\\\r]|(\\\\[\w\W]))*\"))@((\[([0-9]{1,3}\.){3}[0-9]{1,3}\])|(([a-z\-0-9áàäçéèêñóòôöüæøå]+\.)+[a-z]{2,}))$/i";
			if(!preg_match($reg, $this->sSYSTEM->_POST["sRecipient"])){
				$variables["sError"] = true;
				unset($this->sSYSTEM->_POST["sRecipient"]);
			}
			
			$captcha = $this->sSYSTEM->sCONFIG["sTEMPLATEPATH"]."/".$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]."/media/img/default/captcha.jpg";
			if (!is_file($captcha)){
				$captcha = "templates/0/de/media/img/default/captcha.jpg";
			}
			if (is_file($captcha)){
				if ($this->sSYSTEM->_POST["sCaptcha"] != str_replace(" ","",$this->sSYSTEM->_SESSION["sCaptcha"])){
					$variables["sError"] = true;
				}else {
					unset($this->sSYSTEM->_SESSION["sCaptcha"]);
				}
			}
			if (!$variables["sError"]){
				// Prepare eMail
				$sArticle["linkDetails"] = $this->sSYSTEM->rewriteLink(array(
				2=>$sArticle["linkDetails"],3=>$sArticle["articleName"]
				),true);
			
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['subject'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['subject']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['subject'] = str_replace("{sArticle}",$sArticle["articleName"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['subject']);
				
				// Standard-Content
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'] = str_replace("{sShop}",$this->sSYSTEM->sCONFIG['sSHOPNAME'],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'] = str_replace("{sArticle}",$sArticle["articleName"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'] = str_replace("{sLink}",$sArticle["linkDetails"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content']);
				// HTML-Content
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML'] = str_replace("{sName}",$this->sSYSTEM->_POST["sName"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML'] = str_replace("{sShop}",$this->sSYSTEM->sCONFIG['sSHOPNAME'],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML'] = str_replace("{sArticle}",$sArticle["articleName"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML']);
				$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML'] = str_replace("{sLink}",$sArticle["linkDetails"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML']);
				
				
				if ($this->sSYSTEM->_POST["sComment"]){
					$this->sSYSTEM->_POST["sComment"] = strip_tags(addslashes($this->sSYSTEM->_POST["sComment"]));
					$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'] = str_replace("{sComment}",$this->sSYSTEM->_POST["sComment"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content']);
					$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML'] = str_replace("{sComment}",$this->sSYSTEM->_POST["sComment"],$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML']);
				}else {
					$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'] = str_replace("{sComment}","",$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content']);
					$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML'] = str_replace("{sComment}","",$this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML']);
				}
				
				
				if ($this->sSYSTEM->sCONFIG["sVOUCHERTELLFRIEND"]){
					// Tell-a-friend and get voucher
					// Prüfen ob Gutschein ausgestellt werden darf

					$checkIfArktisClient = $this->sSYSTEM->sDB_CONNECTION->GetRow("
					SELECT id FROM s_user WHERE active=1 AND email=?
					",array($this->sSYSTEM->_POST["sMail"]));
					
					if ($checkIfArktisClient["id"]){
						$checkRecipientState = $this->sSYSTEM->sDB_CONNECTION->GetRow("
						SELECT id FROM s_user WHERE active=1 AND email=?
						",array($this->sSYSTEM->_POST["sRecipient"]));
						if (!$checkRecipientState["id"]){
							$heute = date("Y-m-d");
							$this->sSYSTEM->sDB_CONNECTION->Execute("
							INSERT INTO s_emarketing_tellafriend (datum, recipient, sender, confirmed)
							VALUES ('$heute','".$this->sSYSTEM->_POST["sRecipient"]."',{$checkIfArktisClient["id"]},0)
							");
						}
					}
				}
				
				// Send eMail
				$mail           = $this->sSYSTEM->sMailer;				
			
				$mail->From     = $this->sSYSTEM->_POST["sMail"];
				$mail->FromName = $this->sSYSTEM->_POST["sName"];
				$mail->Subject  = $this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['subject'];
				
				if ($this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['ishtml']){
					$mail->IsHTML(1);
					$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['contentHTML'];
					$mail->AltBody     = $this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'];
				}else {
					$mail->IsHTML(0);
					$mail->Body     = $this->sSYSTEM->sCONFIG['sTemplates']['sTELLAFRIEND']['content'];
				}
				
				$mail->ClearAddresses();
				$mail->AddAddress($this->sSYSTEM->_POST["sRecipient"], "");
				if (!$mail->Send()){
					$this->sSYSTEM->E_CORE_WARNING ("##01 TellaFriend","Could not send eMail");
				}else {
					$variables["sSuccess"] = true;
				}
			}
			
		}
				
		
		$templates = array("sContainer"=>"/articles/article_tellafriend.tpl","sContainerRight"=>"/articles/article_tellafriend_right.tpl");
		
		
		
		$variables["sBreadcrumb"] = array(0=>array("name"=>$this->sSYSTEM->sCONFIG['sViewports'][$this->sSYSTEM->_GET["sViewport"]]["name"]));
		$variables["sArticle"] = $sArticle;
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>