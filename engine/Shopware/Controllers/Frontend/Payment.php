<?php
/**
 * Shopware Payment Controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
abstract class Shopware_Controllers_Frontend_Payment extends Enlight_Controller_Action
{
	/**
	 * Returns payment short name
	 *
	 * @return string
	 */
	public function getPaymentShortName()
	{
		if(($user = $this->getUser()) !== null
		  && !empty($user['additional']['payment']['name'])) {
			return $user['additional']['payment']['name'];
		} else {
			return null;
		}
	}
	
	/**
	 * Returns currency short name
	 *
	 * @return string
	 */
	public function getCurrencyShortName()
	{
		return Shopware()->Currency()->getShortName();
	}
	
	/**
	 * Save and complete order
	 *
	 * @param string $transactionId
	 * @param string $paymentUniqueId
	 * @param int $paymentStatusId
	 * @param bool $sendStatusMail
	 * @return int
	 */
	public function saveOrder($transactionId, $paymentUniqueId, $paymentStatusId = null, $sendStatusMail=false)
	{
		if(empty($transactionId) || empty($paymentUniqueId)) {
			return false;
		}
		
		$sql = '
			SELECT ordernumber FROM s_order
			WHERE transactionID=? AND temporaryID=?
			AND status!=-1 AND userID=?
		';
		$orderNumber = Shopware()->Db()->fetchOne($sql, array(
			$transactionId,
			$paymentUniqueId,
			Shopware()->Session()->sUserId
		));
		
		if(empty($order)) {
			$user = $this->getUser();
			$basket = $this->getBasket();
			
	       	$order = Shopware()->Modules()->Order();
			$order->sUserData = $user;
			$order->sComment = Shopware()->Session()->sComment;
			$order->sBasketData = $basket;
			$order->sAmount = $basket['sAmount'];
			$order->sAmountWithTax = $basket['AmountNumeric'];
			$order->sAmountNet = $basket['AmountNetNumeric'];
			$order->sShippingcosts = $basket['sShippingcosts'];
			$order->sShippingcostsNumeric = $basket['sShippingcostsWithTax'];
			$order->sShippingcostsNumericNet = $basket['sShippingcostsNet'];
			$order->bookingId = $transactionId;
			$order->dispatchId = Shopware()->Session()->sDispatch;
			$order->sNet = empty($user['additional']['charge_vat']);
			$order->uniqueID = $paymentUniqueId;
			$orderNumber = $order->sSaveOrder();
			
			if(!empty(Shopware()->Config()->DeleteCacheAfterOrder)) {
				Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Adodb'));
			}
		}
        
        if (!empty($orderNumber) && !empty($paymentStatusId)) {
        	$this->savePaymentStatus($transactionId, $paymentUniqueId, $paymentStatusId, $sendStatusMail);
		}
		
		return $orderNumber;
	}
	
	/**
	 * Save payment status
	 *
	 * @param string $transactionId
	 * @param string $paymentUniqueId
	 * @param int $paymentStatusId
	 * @param bool $sendStatusMail
	 * @return unknown
	 */
	public function savePaymentStatus($transactionId, $paymentUniqueId, $paymentStatusId, $sendStatusMail=false)
	{
		$sql = '
			SELECT id FROM s_order
			WHERE transactionID=? AND temporaryID=?
			AND status!=-1
		';
		$orderId = Shopware()->Db()->fetchOne($sql, array(
			$transactionId,
			$paymentUniqueId
		));
		$order = Shopware()->Modules()->Order();
        $order->setPaymentStatus($orderId, $paymentStatusId, $sendStatusMail);
	}
	
	/**
	 * Returns basket amount
	 *
	 * @return float
	 */
	public function getAmount()
	{
		$user = $this->getUser();
		$basket = $this->getBasket();
		if (!empty($user['additional']['charge_vat'])){
			return empty($basket['AmountWithTaxNumeric']) ? $basket['AmountNumeric'] : $basket['AmountWithTaxNumeric'];
		} else {
			return $basket['AmountNetNumeric'];
		}
	}
	
	/**
	 * Returns user data
	 *
	 * @return array
	 */
	public function getUser()
	{
		if(!empty(Shopware()->Session()->sOrderVariables->sUserData)) {
			return Shopware()->Session()->sOrderVariables->sUserData;
		} else {
			return null;
		}
	}
	
	/**
	 * Returns user data
	 *
	 * @return array
	 */
	public function getBasket()
	{
		if(!empty(Shopware()->Session()->sOrderVariables->sBasket)) {
			return Shopware()->Session()->sOrderVariables->sBasket;
		} else {
			return null;
		}
	}
}