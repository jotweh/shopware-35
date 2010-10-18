<?php
class Shopware_Controllers_Frontend_Ticket extends Shopware_Controllers_Frontend_Forms
{	
	public function preDispatch()
	{
		if (!Shopware()->System()->sCheckLicense('','',Shopware()->System()->sLicenseData['sTICKET']))
		{
			return $this->forward($this->request()->getActionName(),'forms');
		}

		if(!in_array($this->Request()->getActionName(), array('index','direct'))
			&& !Shopware()->Modules()->Admin()->sCheckUser())
		{
			return $this->forward('login','account');
		}
		Shopware()->Modules()->TicketSystem()->sDbType = "adodb";
		$this->View()->sTicketLicensed = true;
	}
	
	public function indexAction(){
		parent::indexAction();
		
		$this->View()->loadTemplate('frontend/forms/index.tpl');
	}
	
	public function listingAction(){
		Shopware()->Modules()->TicketSystem()->sDbType = "adodb";
		$userID = intval(Shopware()->Session()->sUserId);                 
		$this->View()->ticketStore = Shopware()->Modules()->TicketSystem()->getTicketSupportStore("receipt", "DESC", 0, 3000, "", "AND ts.userID = $userID", array("filter_status"=>-1));
	}
	
	public function requestAction(){
		$this->request()->setParam("sFid",Shopware()->Config()->sTICKETACCOUNTFORMID);
		parent::init();
		parent::indexAction();
		$this->View()->loadTemplate('frontend/ticket/request.tpl');
	}
	
	public function detailAction(){
		
		
		$ticketID = intval($this->Request()->tid);
		$userID = intval(Shopware()->Session()->sUserId);               
		
		if (empty($ticketID) || empty($userID)){
			return $this->forward("index");
		}
			
		$tmpTicketDetails =  Shopware()->Modules()->TicketSystem()->getTicketSupportById($ticketID, $userID);
		//form was sent ++ reload block
		if(isset($this->Request()->sSubmit) && ($tmpTicketDetails['responsible'] == 1 && $tmpTicketDetails['closed'] != 1))
		{
			$answer = trim(stripslashes($this->request()->sAnswer));
			if(!empty($answer))
			{
				$aInsert = array();
				$aInsert['ticketID'] = $ticketID;
				$aInsert['message'] = nl2br($answer);
				$aInsert['support_type'] = "Supporttype s_ticketview.php";
				$aInsert['subject'] = "Antwort";
				$aInsert['direction'] = "IN";
				Shopware()->Modules()->TicketSystem()->insertTicketHistoryEntry($aInsert);
				
				$aUpdates = array();
				$aUpdates['statusID'] = 1;
				Shopware()->Modules()->TicketSystem()->updateTicketDataById($ticketID, $aUpdates);
				
				$this->View()->accept = Shopware()->System()->sCONFIG["sSnippets"]["sTicketSysReplySentSuccessful"];
				
				//ds 23.04.09 ticketsystem notify
				Shopware()->Modules()->TicketSystem()->sendNotifyEmail($ticketID, false);					
				//ds 23.04.09 ticketsystem notify	
			}else{
				$this->View()->error = Shopware()->System()->sCONFIG["sSnippets"]["sTicketSysFillRequiredFields"];
			}				
		}
		
		$details = Shopware()->Modules()->TicketSystem()->getTicketSupportById($ticketID, $userID);
		
		if($details['userID'] == $userID){
			$this->View()->ticketDetails = $details;
			$this->View()->ticketHistoryDetails = Shopware()->Modules()->TicketSystem()->getSingleTicketHistoryStore($ticketID);
		}else {
			
		}

	}
	
	public function directAction(){
		
		$id = $this->Request()->sAID;
		
		if(!empty($id))
		{
			//Get TicketID by UniqueID			
			$ticketID = Shopware()->Modules()->TicketSystem()->getTicketIdByUniqueID($id);
			
		}else{
			return $this->forward("login","account");
		}
			
				
		$tmpTicketDetails = Shopware()->Modules()->TicketSystem()->getTicketSupportById($ticketID);
		
		//form was sent ++ reload block
		if(isset($this->Request()->sSubmit) && ($tmpTicketDetails['responsible'] == 1 && $tmpTicketDetails['closed'] != 1))
		{
			$answer = trim(stripslashes(nl2br($this->Request()->sAnswer)));
			if(!empty($answer))
			{
				$aInsert = array();
				$aInsert['ticketID'] = $ticketID;
				$aInsert['message'] = $answer;
				$aInsert['support_type'] = "Supporttype s_ticketview.php";
				$aInsert['subject'] = "Antwort";
				$aInsert['direction'] = "IN";
				Shopware()->Modules()->TicketSystem()->insertTicketHistoryEntry($aInsert);
				Shopware()->Modules()->TicketSystem()->updateTicketDataById($ticketID, array("statusID"=>1));
				$this->View()->accept = Shopware()->System()->sCONFIG["sSnippets"]["sTicketSysReplySentSuccessful"];
				Shopware()->Modules()->TicketSystem()->sendNotifyEmail($ticketID, false);									
			}else{
				$this->View()->error = Shopware()->System()->sCONFIG["sSnippets"]["sTicketSysFillRequiredFields"];
			}				
		}
		
		$this->View()->sAIDticketID = $ticketID;	
		
		$this->View()->ticketDetails = $tmpTicketDetails;
		$this->View()->ticketHistoryDetails = Shopware()->Modules()->TicketSystem()->getSingleTicketHistoryStore($ticketID);
					
		
		$this->View()->loadTemplate('frontend/ticket/detail.tpl');
	}
	
	public function commitForm(){
		
		$id = intval($this->request()->sFid ? $this->request()->sFid : $this->request()->id);
		$formFields = Shopware()->Db()->fetchAll("SELECT * FROM `s_cms_support_fields` WHERE `supportID` = ? ORDER BY `position`",array($id)); 
		$formData = Shopware()->Db()->fetchAll("SELECT * FROM `s_cms_support` WHERE `id` = ?",array($id));
			
		//fields to save
		$message = "";
		$aAdditional = array();
		
		foreach ($formFields as $field)
		{
			//check if it is a special field
			if(!empty($field['ticket_task']))
			{
				switch ($field['ticket_task'])
				{
					case "message":
						$message = $this->Post[$field['id']];
					break;
					case "email":
						if(!empty(Shopware()->Session()->sUserMail)){						
							$this->Post[$field['id']] = stripcslashes(Shopware()->Session()->sUserMail);
						}
						$email = $this->Post[$field['id']];
						
						//catch userID
						if(empty(Shopware()->Session()->sUserId) && !empty(Shopware()->Config()->sTICKETEMAILMATCH))
						{
							$eMailCheck = Shopware()->Db()->fetchAll("SELECT id FROM `s_user` WHERE `email` = ?", array($email));
							
							if(!empty($eMailCheck[0]['id'])){
								$catchedUserID = $eMailCheck[0]['id'];
							}
						}
					break;
					case "name":
						if(!empty(Shopware()->Session()->sUserId)){
							$name = Shopware()->Modules()->Admin()->sGetUserNameById(Shopware()->Session()->sUserId);	
							$this->Post[$field['id']] = $name['firstname']." ".$name['lastname'];
						}
						break;
					case "subject":
						$subject = $this->Post[$field['id']];
					break;					
				}
			}else{
				
				$aAdditionalEntry = array();
				$aAdditionalEntry['name'] = $field['name'];
				$aAdditionalEntry['label'] = $field['label'];
				$aAdditionalEntry['typ'] = $field['typ'];
				$aAdditionalEntry['value'] = nl2br(stripcslashes($this->Post[$field['id']]));
				
				$aAdditional[] = $aAdditionalEntry;
			}
			
			//Catch userID
			if(!empty(Shopware()->Session()->sUserId)){
				$userID=Shopware()->Session()->sUserId; 
			}else{
				if(!empty($catchedUserID)){
					$userID = $catchedUserID;
				}else{
					$userID=0;
				}
			}
		}
		
		$userID = empty($userID) ? 0 : $userID;
		$email = empty($email) ? '' : $email;
		$subject = empty($subject) ? '' : $subject;
		$ticket_typeID = empty($ticket_typeID) ? 0 : $ticket_typeID;
		$message = nl2br($message);
		$additional = serialize($aAdditional);
		$ticket_iso = !empty($formData[0]["isocode"]) ? $formData[0]["isocode"] : "de";
			
		$sql = "INSERT INTO `s_ticket_support` (`userID`, `subject`, `message`, `email`, `additional`, `receipt`, `last_contact`, `ticket_typeID`, `isocode`) 
										VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)";
		
		Shopware()->Db()->query($sql, array($userID, $subject, $message, $email, $additional, $ticket_typeID, $ticket_iso));
		
		//Create an uniqueID
		$insert_id = Shopware()->Db()->lastInsertId();
		$uniqueID = md5($insert_id.time());
		$sql_update = "UPDATE `s_ticket_support` SET `uniqueID` = ? WHERE `id` = ? LIMIT 1";
		
		Shopware()->Db()->query($sql_update, array($uniqueID, $insert_id));
				
		Shopware()->Modules()->TicketSystem()->sendNotifyEmail($insert_id, true);
	}
}