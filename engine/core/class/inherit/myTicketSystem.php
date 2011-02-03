<?php
include ("$path/sTicketSystem.php");

class myTicketSystem extends sTicketSystem
{	
	/**
	 * send notification by new or answered tickets
	 *
	 * @param int $ticketID
	 * @param bool $newticket true=new ticket; false=ticket answer
	 */
	function sendNotifyEmail($ticketID, $newticket=true)
	{
		if($newticket)
		{
			$sTICKETNOTIFYMAIL_ID = $this->getTicketMailItemIdByName("sTICKETNOTIFYMAILNEW");
		}else{
			$sTICKETNOTIFYMAIL_ID = $this->getTicketMailItemIdByName("sTICKETNOTIFYMAILANS");
		}
		
						
		if(!empty($this->sSYSTEM->sCONFIG["sTICKETNOTIFYEMAIL"]) && !empty($sTICKETNOTIFYMAIL_ID))
		{
			$notifyTpl = $this->getTicketMailItem($sTICKETNOTIFYMAIL_ID, $ticketID);
			
			$mail = new PHPMailer;

			if (!$mail) die("PHPMAILER failure");
			$mail->IsHTML(1);
			
							
			$mail->From     = $notifyTpl["frommail"];
			$mail->FromName = $notifyTpl["fromname"];
			
			
			$mail->Subject  = $notifyTpl["subject"];
			
			if(empty($notifyTpl["ishtml"]))
			{
				$mail->Body = utf8_decode(nl2br($notifyTpl["content"]));
			}else{
				$mail->Body = $notifyTpl["contentHTML"];
			}
			
			
			$mail->ClearAddresses();
			
			$explMails = explode(";",  $this->sSYSTEM->sCONFIG["sTICKETNOTIFYEMAIL"]);
			foreach ($explMails as $explMail)
			{
				$mail->AddAddress($explMail, "");
			}
			
			$mail->Send();
		}

		//notify costumer
		if(!empty($this->sSYSTEM->sCONFIG["sTICKETNOTIFYMAILCOSTUMER"]) && $newticket == true)
		{
			//Fetch Ticket Details
			$ticketData = $this->getTicketSupportById($ticketID);
			
			//Fetch mail template
			$sTICKETNOTIFYMAIL_ID = $this->getTicketMailItemIdByName("sTICKETNOTIFYMAILCOSTUMER");
			
			$notifyTpl = $this->getTicketMailItem($sTICKETNOTIFYMAIL_ID, $ticketID);
			
			$mail = new PHPMailer;

			if (!$mail) die("PHPMAILER failure");
			$mail->IsHTML(1);
			
							
			$mail->From     = $notifyTpl["frommail"];
			$mail->FromName = $notifyTpl["fromname"];
			
			
			$mail->Subject  = $notifyTpl["subject"];
			
			if(empty($notifyTpl["ishtml"]))
			{
				$mail->Body = utf8_decode(nl2br($notifyTpl["content"]));
			}else{
				$mail->Body = $notifyTpl["contentHTML"];
			}
			
			
			$mail->ClearAddresses();			
			$mail->AddAddress($ticketData['ticket_email'], "");
			
			$mail->Send();
		}	
	}
	
	function getTicketMailItemIdByName($name)
	{
		$fetch = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT id FROM `s_ticket_support_mails` WHERE `name` LIKE '{$name}' LIMIT 1");
		return $fetch[0]["id"];
	}
}
?>