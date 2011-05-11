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
	 * @param unknown_type $transactionID
	 * @param unknown_type $uniqueID
	 * @param unknown_type $paymentStatus
	 * @param unknown_type $sendStatusMail
	 * @return unknown
	 */
	public function saveOrder($transactionID, $uniqueID, $paymentStatus=null, $sendStatusMail=false)
	{
		if(empty($transactionID) || empty($uniqueID)) {
			return false;
		}
		
		$sql = '
			SELECT ordernumber FROM s_order
			WHERE transactionID=? AND status!=-1 AND userID=?
		';
		$orderNumber = Shopware()->Db()->fetchOne($sql, array(
			$transactionID,
			$uniqueID,
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
			$order->bookingId = $transactionID;
			$order->dispatchId = Shopware()->Session()->sDispatch;
			$order->sNet = empty($user['additional']['charge_vat']);
			$order->uniqueID = $uniqueID;
			$orderNumber = $order->sSaveOrder();
			
			if(!empty(Shopware()->Config()->DeleteCacheAfterOrder)) {
				Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Adodb'));
			}
		}
        
        if (!empty($orderNumber) && !empty($paymentStatus)) {
        	$sql = 'SELECT id FROM s_order WHERE ordernumber=?';
			$orderId = Shopware()->Db()->fetchOne($sql, array(
				$orderNumber
			));
        	
        	$order = Shopware()->Modules()->Order();
        	$order->setPaymentStatus($orderId, $paymentStatus, $sendStatusMail);
		}
		
		return $orderNumber;
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