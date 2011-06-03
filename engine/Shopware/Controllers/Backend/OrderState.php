<?php
/**
 * Order state controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Backend_OrderState extends Enlight_Controller_Action
{
	/**
	 * Init controller method
	 */
	public function init()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
	}
	
	/**
	 * Read action method
	 */
	public function readAction()
	{
		$orderId = $this->Request()->id;
		$statusId = $this->Request()->status;
		$template = $this->Request()->mailtype;
		
		$mail = Shopware()->Modules()->Order()->createStatusMail($orderId, $statusId, $template);
		
		if(!empty($mail)) {
			$ret = array(
				"content" => utf8_encode($mail->getPlainBodyText()),
				"subject" => utf8_encode($mail->getSubject()),
				"email" => utf8_encode(implode(', ', $mail->getTo())),
				"frommail" => utf8_encode($mail->getFrom()),
				"fromname" => utf8_encode($mail->getFromName())
			);
			echo Zend_Json::encode($ret);
		} else {
			echo 'FAIL';
		}
	}
	
	/**
	 * Send action method
	 */
	public function sendAction()
	{
		$mail = clone Shopware()->Mail();
		
		$mail->clearRecipients();
		
		$mail->setSubject(utf8_decode($this->Request()->subject));
		$mail->setBodyText(utf8_decode($this->Request()->content));
		$mail->setFrom(utf8_decode($this->Request()->frommail), utf8_decode($this->Request()->fromname));
		$mail->addTo(utf8_decode($this->Request()->email));

		if (!Shopware()->Modules()->Order()->sendStatusMail($mail)){
			echo 'FAIL';
		}
	}
}