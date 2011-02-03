<?php
include_once("s_login.php");
include_once("s_sale.php");

include_once("s_ticket.php");


class sViewportTicketview{
	
	var $sSYSTEM;
	var $sViewportLogin;
	var $sViewportSale;
	
	function sViewportTicketview(&$sSYSTEM,&$sViewportLogin){
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
		
		
		// Check if user is logged in
		if (!$this->sSYSTEM->sMODULES['sAdmin']->sCheckUser()){
			$this->sSYSTEM->_GET["sViewport"] = "login";
			return $this->sViewportLogin->sRender();
		}
			
		
		$this->sSYSTEM->sMODULES['sTicketSystem']->sDbType = "adodb";
		
		if($this->sSYSTEM->_GET['sAction'] == "detail")
		{
			$ticketID = intval($this->sSYSTEM->_GET['tid']);
			
			$tmpTicketDetails = $this->sSYSTEM->sMODULES['sTicketSystem']->getTicketSupportById($ticketID, intval($this->sSYSTEM->_SESSION['sUserId']));
			//form was sent ++ reload block
			if(isset($this->sSYSTEM->_POST['sSubmit']) && ($tmpTicketDetails['responsible'] == 1 && $tmpTicketDetails['closed'] != 1))
			{
				$answer = trim(stripslashes($this->sSYSTEM->_POST['sAnswer']));
				if(!empty($answer))
				{
					$aInsert = array();
					$aInsert['ticketID'] = $ticketID;
					$aInsert['message'] = nl2br($answer);
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
	
			$variables['ticketDetails'] = $this->sSYSTEM->sMODULES['sTicketSystem']->getTicketSupportById($ticketID, intval($this->sSYSTEM->_SESSION['sUserId']));
			$variables['ticketHistoryDetails'] = $this->sSYSTEM->sMODULES['sTicketSystem']->getSingleTicketHistoryStore($ticketID);
		
			//Check authority
			if($variables['ticketDetails']['userID'] != $this->sSYSTEM->_SESSION['sUserId'])
				unset($variables['ticketDetails']);
				
			$templates = array("sContainer"=>"/ticket/ticketdetail.tpl");
		}
		elseif ($this->sSYSTEM->_GET['sAction'] == "request")
		{
			$this->sSYSTEM->_GET["sFid"] = $this->sSYSTEM->sCONFIG['sTICKETACCOUNTFORMID'];
			
			$sViewportTicket = new sViewportTicket();
			$sViewportTicket->sSYSTEM = &$this->sSYSTEM;
			$renderData = $sViewportTicket->sRender();
			
			$templates = array("sContainer"=>"/ticket/support_template.tpl");
			$variables = $renderData['variables'];
			$variables['sCaptcha'] = intval($this->sSYSTEM->_SESSION['sCaptcha']);
		}else{	
			//Shows the ticketoverveiw	
			$aFilter['filter_status'] = -1;						 
			$sUserId = intval($this->sSYSTEM->_SESSION['sUserId']);                 
			$variables["ticketStore"] = $this->sSYSTEM->sMODULES['sTicketSystem']->getTicketSupportStore("receipt", "DESC", 0, 3000, "", "AND ts.userID = {$sUserId}", $aFilter);
			
			$templates = array("sContainer"=>"/ticket/ticketoverview.tpl");
		}
		
		
		
		
		return array("templates"=>$templates,"variables"=>$variables);
	}
}
?>