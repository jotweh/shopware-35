<?php
include_once("s_login.php");
include_once("s_sale.php");

include_once("s_ticket.php");


class sViewportTicketdirect{
	
	var $sSYSTEM;
	var $sViewportLogin;
	var $sViewportSale;
	
	function sViewportTicketdirect(&$sSYSTEM,&$sViewportLogin){
		if (!is_object($sViewportLogin)){
			$this->sViewportLogin = new sViewportLogin($sSYSTEM,$this);
			$this->sViewportLogin->sSYSTEM = $sSYSTEM;
		}else {
			$this->sViewportLogin = $sViewportLogin;
		}
		
		
		$this->sViewportSale = new sViewportSale($sSYSTEM,$sViewportLogin);
		$this->sViewportSale->sSYSTEM = $sSYSTEM;
		
	}
	
	function sRender(){
		
		//Set Adobo as standard
		$this->sSYSTEM->sMODULES['sTicketSystem']->sDbType = "adodb";
		
		// AnswerID availible?
		if(!empty($this->sSYSTEM->_GET['sAID']))
		{
			//Get TicketID by UniqueID			
			$sAID = mysql_real_escape_string(stripslashes($this->sSYSTEM->_GET['sAID']));
			$ticketID = $this->sSYSTEM->sMODULES['sTicketSystem']->getTicketIdByUniqueID($sAID);
		}else{
			$this->sSYSTEM->_GET["sViewport"] = "login";
			return $this->sViewportLogin->sRender();
		}
			
				
		$tmpTicketDetails = $this->sSYSTEM->sMODULES['sTicketSystem']->getTicketSupportById($ticketID);
		//form was sent ++ reload block
		if(isset($this->sSYSTEM->_POST['sSubmit']) && ($tmpTicketDetails['responsible'] == 1 && $tmpTicketDetails['closed'] != 1))
		{
			$answer = trim(mysql_real_escape_string(stripslashes($this->sSYSTEM->_POST['sAnswer'])));
			if(!empty($answer))
			{
				$aInsert = array();
				$aInsert['ticketID'] = $ticketID;
				$aInsert['message'] = mysql_real_escape_string(nl2br($answer));
				$aInsert['support_type'] = "Supporttype s_ticketview.php";
				$aInsert['subject'] = "Antwort";
				$aInsert['direction'] = "IN";
				$this->sSYSTEM->sMODULES['sTicketSystem']->insertTicketHistoryEntry($aInsert);
				
				$aUpdates = array();
				$aUpdates['statusID'] = 1;
				$this->sSYSTEM->sMODULES['sTicketSystem']->updateTicketDataById($ticketID, $aUpdates);
				
				$variables['accept'] = $this->sSYSTEM->sCONFIG["sSnippets"]["sTicketSysReplySentSuccessful"];
				
				//ds 23.04.09 ticketsystem notify
				$this->sSYSTEM->sMODULES['sTicketSystem']->sendNotifyEmail($ticketID, false);					
				//ds 23.04.09 ticketsystem notify
					
			}else{
				$variables['error'] = $this->sSYSTEM->sCONFIG["sSnippets"]["sTicketSysFillRequiredFields"];
			}				
		}

		$variables['sAIDticketID'] = $ticketID;	
		$variables['ticketDetails'] = $this->sSYSTEM->sMODULES['sTicketSystem']->getTicketSupportById($ticketID);
				
		$variables['ticketHistoryDetails'] = $this->sSYSTEM->sMODULES['sTicketSystem']->getSingleTicketHistoryStore($ticketID);
					
		$templates = array("sContainer"=>"/ticket/ticketdetail.tpl");
				
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>