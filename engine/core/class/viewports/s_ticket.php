<?php
include("s_support.php");

class sViewportTicket extends sViewportSupport {
	
	private $sFallbackSupport;
	
	function sRender()
	{
		//no ts-licence >>> fallback to viewport support
		$getLicenseKey = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow(86400,"
		SELECT hash FROM s_core_licences WHERE module LIKE '%sTICKET%' AND `inactive` != 1
		");
		if (!$this->sSYSTEM->sCheckLicense("","",$getLicenseKey["hash"])){
			$this->sFallbackSupport = true;
		}else{
			$this->sFallbackSupport = false;
		}		
		return parent::sRender();
	}
	
	function sManageData($variables,$sPOSTS,$sELEMENTS){
		
		if($this->sFallbackSupport){
			
			return parent::sManageData($variables,$sPOSTS,$sELEMENTS);
		}
		$sFid = intval($this->sSYSTEM->_GET['sFid']); 
		$formFields = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT * FROM `s_cms_support_fields` WHERE `supportID` = '{$sFid}' ORDER BY `position`"); 
		$formData = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT * FROM `s_cms_support` WHERE `id` = '{$sFid}'");
			
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
						$message = $sPOSTS[$field['id']];
					break;
					case "email":
						$email = $sPOSTS[$field['id']];
						
						//catch userID
						if(empty($this->sSYSTEM->_SESSION['sUserId']) && !empty($this->sSYSTEM->sCONFIG["sTICKETEMAILMATCH"]))
						{
							$eMailCheck = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT id FROM `s_user` WHERE `email` = ?", array($email));
							
							if(!empty($eMailCheck[0]['id'])){
								$catchedUserID = $eMailCheck[0]['id'];
							}
						}
					break;
					case "subject":
						$subject = $sPOSTS[$field['id']];
					break;					
				}
			}else{
				
				$aAdditionalEntry = array();
				$aAdditionalEntry['name'] = $field['name'];
				$aAdditionalEntry['label'] = $field['label'];
				$aAdditionalEntry['typ'] = $field['typ'];
				$aAdditionalEntry['value'] = nl2br(stripcslashes($sPOSTS[$field['id']]));
				
				$aAdditional[] = $aAdditionalEntry;
			}
			
			//Catch userID
			if(!empty($this->sSYSTEM->_SESSION['sUserId'])){
				$userID=$this->sSYSTEM->_SESSION['sUserId']; 
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
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($userID, $subject, $message, $email, $additional, $ticket_typeID, $ticket_iso));
		
		//Create an uniqueID
		$insert_id = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
		$uniqueID = md5($insert_id.time());
		$sql_update = "UPDATE `s_ticket_support` SET `uniqueID` = ? WHERE `id` = ? LIMIT 1";
		
		$this->sSYSTEM->sDB_CONNECTION->Execute($sql_update, array($uniqueID, $insert_id));
				
		$this->sSYSTEM->sMODULES['sTicketSystem']->sendNotifyEmail($insert_id, true);
	}
	
	function sModifyValues($sPOSTS)
	{	
		if($this->sFallbackSupport)
			return parent::sModifyValues($sPOSTS);
		
		$sFid = intval($this->sSYSTEM->_GET['sFid']);
		$formFields = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT * FROM `s_cms_support_fields` WHERE `supportID` = '{$sFid}'");
		foreach ($formFields as $field)
		{
			//check if it is a special field
			if(!empty($field['ticket_task']))
			{	
				switch ($field['ticket_task'])
				{
					case "email":
						if(!empty($this->sSYSTEM->_SESSION['sUserMail']))						
							$sPOSTS[$field['id']] = stripcslashes($this->sSYSTEM->_SESSION['sUserMail']);
					break;
					case "name":
						if(!empty($this->sSYSTEM->_SESSION['sUserId']))		
							$sUserName = $this->sSYSTEM->sMODULES['sAdmin']->sGetUserNameById($this->sSYSTEM->_SESSION['sUserId']);	
							$sPOSTS[$field['id']] = $sUserName['firstname']." ".$sUserName['lastname'];
					break;
				}
			}
		}
		return $sPOSTS;
	}
}

?>