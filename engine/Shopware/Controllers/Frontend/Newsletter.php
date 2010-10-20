<?php
class Shopware_Controllers_Frontend_Newsletter extends Enlight_Controller_Action
{	
	public function confirmAction()
	{
		if (!empty($this->request()->sConfirmation)){
			
			$getVote = Shopware()->Db()->fetchRow("
			SELECT * FROM s_core_optin WHERE hash = ?
			",array($this->request()->sConfirmation));
			if (!empty($getVote["data"])){
				Shopware()->System()->_POST = unserialize($getVote["data"]);
				$this->View()->voteConfirmed = true;
				Shopware()->Db()->query("
				DELETE FROM s_core_optin WHERE hash = ?
				",array($this->request()->sConfirmation));
			}else {
				$this->View()->voteConfirmed = false;
			}
		}else {
			$this->View()->voteConfirmed = false;
		}
		return $this->forward("index");
	}
	
	protected function sendMail($recipient, $template, $optin=false)
	{
		$mail = clone Shopware()->Mail();
		
		$mail->IsHTML($template['ishtml']);
		$mail->From     = $template['frommail'];
		$mail->FromName = $template['fromname'];
		$mail->Subject  = $template['subject'];
		if ($template['ishtml']){
	    	$mail->IsHTML(1);	
	    	$mail->Body     = $template['contentHTML'];
	    	$mail->AltBody = $template['content'];
	    }else {
	    	$mail->IsHTML(0);
	    	$mail->Body     = $template['content'];
	    }
	    $mail->Body = str_replace("{sMAIL}",$email,$mail->Body);
	     
	    if (!empty($optin)){
	    	 $mail->Body = str_replace("{\$sConfirmLink}",$optin,$mail->Body);
	    }
	    
	    foreach (Shopware()->System()->_POST as $key => $value)
	    {
	    	$mail->Subject = str_replace('{$sUser.'.$key.'}', $value, $mail->Subject);
	    	$mail->Body = str_replace('{$sUser.'.$key.'}', $value, $mail->Body);
	    	$mail->AltBody = str_replace('{$sUser.'.$key.'}', $value, $mail->AltBody);
	    }
	    
	    $mail->ClearAddresses();
	    $mail->AddAddress($recipient, "");
	    $mail->Send();
	}
	
	public function indexAction()
	{
		$variables = array();
		
		if (isset($this->request()->sUnsubscribe)){
			$this->View()->sUnsubscribe = true;
		}else {
			$this->View()->sUnsubscribe = false;
		}
		$this->View()->_POST = Shopware()->System()->_POST;
		
		if(isset(Shopware()->System()->_POST["newsletter"]))
		{
			if(Shopware()->System()->_POST["subscribeToNewsletter"]==1)
			{
				if (empty(Shopware()->Config()->sOPTINNEWSLETTER) || $this->View()->voteConfirmed==true)
				{
					
					$this->View()->sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST["newsletter"],false);
					if($this->View()->sStatus['code']==3)
					{
						$template = Shopware()->Config()->Templates->sNEWSLETTERCONFIRMATION;
						// Send mail to subscriber
						$this->sendMail(Shopware()->System()->_POST["newsletter"],$template);
						
					}
				}
				else
				{
					
					$this->View()->sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST["newsletter"],false);
					if($this->View()->sStatus["code"]==3)
					{
						
						Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST["newsletter"], true);
						$template = Shopware()->Config()->Templates->sOPTINNEWSLETTER;
					    $hash = md5(uniqid(rand()));
					    $data = serialize(Shopware()->System()->_POST);
						
					    $link = $this->Front()->Router()->assemble(array('sViewport'=>'newsletter','action'=>'confirm','sConfirmation'=>$hash));
						
					    $this->sendMail(Shopware()->System()->_POST["newsletter"],$template,$link);
						
						// Setting status-code
						$this->View()->sStatus = array("code"=>3,"message"=>Shopware()->Snippets()->getSnippet()->get('sMailConfirmation'));
						
						
					    Shopware()->Db()->query("
					    INSERT INTO s_core_optin (datum,hash,data)
					    VALUES (
					    now(),?,?
					    )
					    ",array($hash,$data));
					}
				}
			}
			else
			{
				// Unsubscribe user
				$this->View()->sStatus = Shopware()->Modules()->Admin()->sNewsletterSubscription(Shopware()->System()->_POST["newsletter"],true);
				
			}
		}
	}
	
	protected function getCustomerGroups()
	{
		$customergroups = array('EK');
		if(!empty(Shopware()->System()->sSubShop['defaultcustomergroup']))
		{
			$customergroups[] = Shopware()->System()->sSubShop['defaultcustomergroup'];
		}
		if(!empty(Shopware()->System()->sUSERGROUPDATA['groupkey']))
		{
			$customergroups[] = Shopware()->System()->sUSERGROUPDATA['groupkey'];
		}
		$customergroups = array_unique($customergroups);
		return $customergroups;
	}
	
	public function listingAction()
	{
		$customergroups = $this->getCustomerGroups();
		$customergroups = Shopware()->Db()->quote($customergroups);
		
		$page = isset($this->request()->sPage) ? (int) $this->request()->sPage : 1;
		$perpage = isset(Shopware()->Config()->sCONTENTPERPAGE) ? (int) Shopware()->Config()->sCONTENTPERPAGE : 0;
		
		$sql = "
			SELECT SQL_CALC_FOUND_ROWS id, IF(datum='00-00-0000','',datum) as `date`, subject as description, sendermail, sendername
			FROM `s_campaigns_mailings`
			WHERE `status`!=0
			AND plaintext=0
			AND publish!=0
			AND languageID=?
			AND customergroup IN ($customergroups)
			ORDER BY id DESC
		";
		
		$sql = Shopware()->Db()->limit($sql, $perpage, $perpage*($page-1));
		$result = Shopware()->Db()->query($sql, array(Shopware()->System()->sLanguage));
		
		$content = array();
		while ($row = $result->fetch())
		{
			$row['link'] = $this->Front()->Router()->assemble(array('action'=>'detail','sID'=>$row['id']));
			$content[] = $row;
		}
		
		$sql = 'SELECT FOUND_ROWS() as count_'.md5($sql);
		$count = Shopware()->Db()->fetchOne($sql);
		
		$count = ceil($count/$perpage);
		
		$pages = array();
		for ($i=1; $i<=$count; $i++)
		{
			if ($i==$page) {
				$pages['numbers'][$i]['markup'] = true;
			} else {
				$pages['numbers'][$i]['markup'] = false;
			}
			$pages['numbers'][$i]['value'] = $i;
			$pages['numbers'][$i]['link'] = $this->Front()->Router()->assemble(array('sViewport'=>'newsletter','action'=>'listing','sPage'=>$i)); 
		}
		
		$this->View()->sPage = $page;
		$this->View()->sNumberPages = $count;
		$this->View()->sPages = $pages;
		$this->View()->sContent = $content;
	}
	
	public function detailAction()
	{
		$customergroups = $this->getCustomerGroups();
		$customergroups = Shopware()->Db()->quote($customergroups);
		
		$sql = "
			SELECT id, IF(datum='00-00-0000','',datum) as `date`, subject as description, sendermail, sendername
			FROM `s_campaigns_mailings`
			WHERE `status`!=0
			AND plaintext=0
			AND publish!=0
			AND languageID=?
			AND id=?
			AND customergroup IN ($customergroups)
		";
		$content = Shopware()->Db()->fetchRow($sql, array(Shopware()->System()->sLanguage, $this->request()->sID));
		if(!empty($content))
		{
			$content['hash'] = array($content['id'], Shopware()->System()->sLicenseData['sCORE']);
			$content['hash'] = md5(implode('|', $content['hash']));
			$content['link'] = 'engine/core/php/campaigns.php?id='.$content['id'].'&hash='.$content['hash'];
		}
		
		$this->View()->sContentItem = $content;
		$this->View()->sBackLink = $this->Front()->Router()->assemble(array('action'=>'listing')); 
	}
}