<?php
class Shopware_Controllers_Backend_OrderState extends Enlight_Controller_Action
{
	public function smarty_modifier_fill ($str, $width=10, $break="...", $fill=" ")
	{
		if(!is_scalar($break))
			$break = "...";
		if(empty($fill)||!is_scalar($fill))
			$fill = " ";
		if(empty($width)||!is_numeric($width))
			$width = 10;
		else 
			$width = (int)$width;
		if(!is_scalar($str))
			return str_repeat($fill,$width);
		if(strlen($str)>$width)
			$str = substr($str,0,$width-strlen($break)).$break;
		if($width>strlen($str))
			return $str.str_repeat($fill,$width-strlen($str));
		else 
			return $str;
	}
		
	public function smarty_modifier_padding ($str, $width=10, $break="...", $fill=" ")
	{
		if(!is_scalar($break))
			$break = "...";
		if(empty($fill)||!is_scalar($fill))
			$fill = " ";
		if(empty($width)||!is_numeric($width))
			$width = 10;
		else 
			$width = (int)$width;
		if(!is_scalar($str))
			return str_repeat($fill,$width);
		if(strlen($str)>$width)
			$str = substr($str,0,$width-strlen($break)).$break;
		if($width>strlen($str))
			return str_repeat($fill,$width-strlen($str)).$str;
		else 
			return $str;
	}
	
	public function readAction()
	{
		
		$id = $this->Request()->id;
		$status = $this->Request()->status;
		
		$mailtype = $this->Request()->mailtype;
		if (empty($mailtype)){
			$mailname = "sORDERSTATEMAIL".$status;
		}else {
			$mailname = $mailtype;
		}
		if (!empty(Shopware()->Config()->Templates[$mailname]["content"]) && !empty($id) && !empty($status)){
			
			// Read subshop
			$getShop = Shopware()->Db()->fetchOne("SELECT subshopID FROM s_order WHERE id = ?",array($id));
			
			
			$shop = new Shopware_Models_Shop($getShop);
			$shop->setCache();
			$shop->registerResources(Shopware()->Bootstrap());
			
			$template = clone Shopware()->Config()->Templates[$mailname];
			
			$templateEngine = Shopware()->Template();
			$templateEngine->register_modifier("fill",array(&$this,"smarty_modifier_fill"));
			$templateEngine->register_modifier("padding",array(&$this,"smarty_modifier_padding"));
			$templateData = $templateEngine->createData();
			$templateData->assign('sConfig', Shopware()->Config());
			
			$sOrder = Shopware()->Api()->Export()->sGetOrders(array("orderID"=>$id));
			$sOrder = current($sOrder);
			
			
			
			if (empty($sOrder["orderID"])){
				throw new Enlight_Exception("Empty order");
			}
			if (!empty($sOrder["dispatchID"])){
				$dispatch = Shopware()->Db()->fetchRow("
				SELECT name, description FROM s_shippingcosts_dispatch
				WHERE id=?",array($sOrder["dispatchID"]));
				$templateData->assign('sDispatch', $dispatch);
			}
			$sOrderDetails = Shopware()->Api()->Export()->sOrderDetails(array("orderID"=>$id));
			$sOrderDetails = array_values($sOrderDetails);
			
			$sUser = current(Shopware()->Api()->Export()->sOrderCustomers(array("orderID"=>$id)));
			
			$templateData->assign('sOrder', $sOrder);
			$templateData->assign('sOrderDetails', $sOrderDetails);
			$templateData->assign('sUser', $sUser);
			
			$result = Enlight()->Events()->notify('Shopware_Controllers_Backend_OrderState_Notify', array('subject'=>$this,"mailtype"=>$mailtype,"id"=>$id,"status"=>$status,"mailname"=>$mailname,"template"=>$template));
			if (is_object($result)){
				$templateData->assign('EventResult',$result->getValues());
			}
			
			$ret = array(
				"content" => utf8_encode($templateEngine->fetch('string:'.$template->content, $templateData)), 
				"subject" => utf8_encode(trim($templateEngine->fetch('string:'.$template->subject, $templateData))),
				"email" => utf8_encode(trim($sUser['email'])),
				"frommail" => utf8_encode(trim($templateEngine->fetch('string:'.$template->frommail, $templateData))),
				"fromname" => utf8_encode(trim($templateEngine->fetch('string:'.$template->fromname, $templateData)))
			);
			$ret = Enlight()->Events()->filter('Shopware_Controllers_Backend_OrderState_Filter',$ret, array('subject'=>$this,"mailtype"=>$mailtype,"id"=>$id,"status"=>$status,"mailname"=>$mailname,"template"=>$template,"engine"=>$templateEngine));

			echo json_encode($ret);;
		}else {
			echo "FAIL";
		}
		$this->View()->setTemplate();
	}
	
	public function sendAction()
	{
		$mail = Shopware()->System()->sMailer;
		
		$mail->IsHTML(0);
		
		$mail->From     = utf8_decode($this->Request()->frommail);
		$mail->FromName = utf8_decode($this->Request()->fromname);
		
		$mail->Subject  = utf8_decode($this->Request()->subject);
		$mail->Body     = utf8_decode($this->Request()->content);
		$mail->ClearAddresses();
		$mail->AddAddress(utf8_decode($this->Request()->email), "");
		$ackeMail = trim(Shopware()->Config()->sORDERSTATEMAILACK);
		
		Enlight()->Events()->notify('Shopware_Controllers_Backend_OrderState_Send_BeforeSend', array('subject'=>$this,"mail"=>$mail));
		
		if(!empty($ackeMail)){
			$mail->AddAddress($ackeMail, "");	
		}

		if (!$mail->Send()){
			echo "FAIL";
		}
		$this->View()->setTemplate();
	}
}