<?php
/**
 * Eos payment controller
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Controllers
 */
class Shopware_Controllers_Frontend_PaymentEos extends Shopware_Controllers_Frontend_Payment
{	
	/**
	 * Index action method
	 */
	public function indexAction()
	{
		switch ($this->getPaymentShortName()) {
			case 'eos_credit':
			case 'eos_elv':
				return $this->forward('gateway');
			case 'eos_giropay':
				return $this->forward('giropay');
			case 'eos_ideal':
				return $this->forward('direct');
			default:
				return $this->forward('index', 'checkout');
		}
	}
	
	/**
	 * Pre dispatch action method
	 */
	public function preDispatch()
	{
		if(in_array($this->Request()->getActionName(), array('notify', 'book', 'cancel', 'refresh', 'memo'))) {
			Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		}
	}
	
	/**
	 * Style action method
	 */
	public function styleAction()
	{
		$this->Response()->setHeader('Content-Type', 'text/css');
	}
	
	/**
	 * Credit action method
	 */
	public function gatewayAction()
	{
		if(!in_array($this->getPaymentShortName(), array('eos_credit', 'eos_elv'))) {
			return $this->forward('index');
		}
				
		$user = $this->getUser();
		$router = $this->Front()->Router();
		$request = $this->Request();
		$secret = $this->createPaymentUniqueId();
		
		$params = array();
		$params['haendlerid'] = $this->Config()->merchantId;
		$params['haendlercode'] = $this->Config()->merchantCode;
		$params['referenz'] = $this->getEosReferenz($user);
		$params['bruttobetrag'] = $this->formatEosNumber($this->getAmount());
		$params['waehrung'] = Shopware()->Currency()->getShortName();
		$params['_language'] = Shopware()->Locale()->getLanguage();
		if($this->getPaymentShortName() == 'eos_credit') {
			$params['_buchen'] = (int) $this->Config()->creditDirectBook;
		} else {
			$params['_buchen'] = (int) $this->Config()->elvDirectBook;
		}
		$params['_stylesheet'] = $router->assemble(array(
			'action' => 'style'
		));
		
		//$params['_text'] = Shopware()->Config()->get('sCLICKPAYTEXT');
		//if($sClickPay->sGetSnippet('sClickPayButtonCancel'))
		//	$params['_ButtonTextCancel'] = $sClickPay->sGetSnippet('sClickPayButtonCancel');
		//if($sClickPay->sGetSnippet('sClickPayButtonOK'))
		//	$params['_ButtonTextOK'] = $sClickPay->sGetSnippet('sClickPayButtonOK');
			
		$params['karteninhaber'] = $user['billingaddress']['firstname'].' '.$user['billingaddress']['lastname'];
				
		$params['NotifyURL'] = $router->assemble(array(
			'action' => 'notify',
			'secret' => $secret
		));
		$params['NotifyURL'] .= strpos($params['NotifyURL'], '?') === false ? '?' : '&';
		$params['NotifyURL'] .= 'transactionId=<<KontaktID>>' . '&';
		$params['NotifyURL'] .= 'status=<<statuscode>>';
		
		$params['SuccessURL'] = $router->assemble(array(
			'action' => 'notify', 'status' => 'success',
			'secret' => $secret, 'appendSession' => true, 
		));
		$params['SuccessURL'] .= strpos($params['SuccessURL'], '?') === false ? '?' : '&';
		$params['SuccessURL'] .= 'transactionId=<<KontaktID>>' . '&';
		$params['SuccessURL'] .= 'accountNumber=<<Zahlungsdatensatz.Kreditkartennummer_maskiert.>>';
		
		$params['BackURL'] = $router->assemble(array(
			'action' => 'notify',  'status' => 'back',
			'secret' => $secret, 'appendSession' => true,
		));
		$params['BackURL'] .= strpos($params['BackURL'], '?') === false ? '?' : '&';
		$params['BackURL'] .= 'transactionId=<<KontaktID>>';
		
		$params['FailURL'] = $router->assemble(array(
			'action' => 'notify', 'status' => 'fail',
			'secret' => $secret, 'appendSession' => true
		));
		$params['FailURL'] .= strpos($params['FailURL'], '?') === false ? '?' : '&';
		$params['FailURL'] .= 'transactionId=<<KontaktID>>' . '&';
		$params['FailURL'] .= 'failMessage=<<SPAY_Reservierungen.AUTHRESULT.>>';
		
		$params['ErrorURL'] = $router->assemble(array(
			'action' => 'notify', 'status' => 'error',
			'secret' => $secret, 'appendSession' => true
		));
		$params['ErrorURL'] .= strpos($params['ErrorURL'], '?') === false ? '?' : '&';
		$params['ErrorURL'] .= 'transactionId=<<KontaktID>>';
		
		$params['EndURL'] = $router->assemble(array(
			'action' => 'end'
		));
		
		foreach ($params as $key => &$param) {
			if($key == '_stylesheet' || $key == 'EndURL') {
				continue;
			}
			$param = str_replace('hl.shopvm.de/trunk/shopware.php', 'sh.shopvm.de/test.php', $param);
		}
		
		if($this->getPaymentShortName() == 'eos_credit') {
			$requestUrl = 'https://www.eos-payment.de/PaymentGatewayMini_CC.acgi';
		} else {
			$requestUrl = 'https://www.eos-payment.de/PaymentGateway_ELV.acgi';
		}
		
		$respone = $this->doEosRequest($requestUrl, $params);
		
		if(!empty($respone['kontaktid'])) {
			$this->saveEosResponse($respone, $secret);
		}
		
		if(!empty($respone['URL'])) {
			$this->View()->PaymentShortName = $this->getPaymentShortName();
			$this->View()->PaymentUrl = $respone['URL'];
		} else {
			$errorMessages = $this->checkEosResponse($respone);
			$errorMessages = implode("\n", $errorMessages);
			
			$sql = '
				UPDATE s_plugin_payment_eos
				SET status=?, `changed`=NOW(),
					fail_message=IFNULL(?, fail_message)
				WHERE transactionID=? AND secret=?
			';
			Shopware()->Db()->query($sql, array(
				'error',
				$errorMessages,
				$respone['kontaktid'],
				$secret
			));
			
			return $this->forward('end');
		}
	}
	
	/**
	 * Direct payment action method
	 */
	public function directAction()
	{
		if(!in_array($this->getPaymentShortName(), array('eos_giropay', 'eos_ideal'))) {
			return $this->forward('index');
		}
				
		$user = $this->getUser();
		$router = $this->Front()->Router();
		$request = $this->Request();
		$secret = $this->createPaymentUniqueId();
		
		$params = array();
		$params['haendlerid'] = $this->Config()->merchantId;
		$params['haendlercode'] = $this->Config()->merchantCode;
		$params['referenz'] = $this->getEosReferenz($user);
		$params['bruttobetrag'] = $this->formatEosNumber($this->getAmount());
		$params['waehrung'] = $this->getCurrencyShortName();
		$params['kontonummer'] = $this->Request()->getParam('account_number');
		$params['blz'] = $this->Request()->getParam('account_bank');
		$params['kontoinhaber'] = $this->Request()->getParam('account_holder');
		if(empty($params['kontoinhaber'])) {
			$params['kontoinhaber'] = $user['billingaddress']['firstname'].' '.$user['billingaddress']['lastname'];
		}
		if($this->getPaymentShortName() == 'eos_ideal') {
			$params['provider'] = 'iDEAL';
		} else {
			$params['provider'] = $this->Config()->giropayProvider;
		}
		
		$params['NotifyURL'] = $router->assemble(array(
			'action' => 'notify',
			'secret' => $secret
		));
		$params['NotifyURL'] .= strpos($params['NotifyURL'], '?') === false ? '?' : '&';
		$params['NotifyURL'] .= 'transactionId=<<KontaktID>>' . '&';
		$params['NotifyURL'] .= 'status=<<statuscode>>';
		
		$params['SuccessURL'] = $router->assemble(array(
			'action' => 'end', 'status' => 'success',
			'validate' => md5($secret.'success')
		));
		$params['BackURL'] = $router->assemble(array(
			'action' => 'end', 'status' => 'back',
			'validate' => md5($secret.'back')
		));		
		$params['FailURL'] = $router->assemble(array(
			'action' => 'end', 'status' => 'fail',
			'validate' => md5($secret.'fail')
		));
		$params['ErrorURL'] = $router->assemble(array(
			'action' => 'end', 'status' => 'error',
			'validate' => md5($secret.'error')
		));
		
		$requestUrl = 'https://www.eos-payment.de/onlineueberweisung.acgi';
		
		$params['NotifyURL'] = str_replace('hl.shopvm.de/trunk/shopware.php', 'sh.shopvm.de/test.php', $params['NotifyURL']);

		$respone = $this->doEosRequest($requestUrl, $params);
		
		if(!empty($respone['kontaktid'])) {
			$this->saveEosResponse($respone, $secret);
		}
		
		if(!empty($respone['URL'])) {
			$this->redirect($respone['URL']);
		} else {
			$errorMessages = $this->checkEosResponse($respone);
			$errorMessages = implode("\n", $errorMessages);
			
			$sql = '
				UPDATE s_plugin_payment_eos
				SET status=?, `changed`=NOW(),
					fail_message=IFNULL(?, fail_message)
				WHERE transactionID=? AND secret=?
			';
			Shopware()->Db()->query($sql, array(
				'error',
				$errorMessages,
				$respone['kontaktid'],
				$secret
			));
			
			if($this->getPaymentShortName() == 'eos_giropay') {
				$this->View()->PaymentParams = $params;
				$this->View()->PaymentErrorMessages = $errorMessages;
				return $this->forward('giropay');
			} else {
				return $this->forward('end');
			}
		}
	}
	
	/**
	 * Giropay action method
	 */
	public function giropayAction()
	{
		if(!in_array($this->getPaymentShortName(), array('eos_giropay'))) {
			return $this->forward('index');
		}
		
		$user = $this->getUser();
		
		$this->View()->PaymentParams = array(
			'kontoinhaber' => $user['billingaddress']['firstname'].' '.$user['billingaddress']['lastname']
		);
	}
		
	/**
	 * Notify action method
	 */
	public function notifyAction()
	{		
//		$mail = clone Shopware()->Mail();
//		
//		$mail->setSubject('test eos');
//		$mail->setBodyText(var_export($_GET, true).var_export($_SERVER, true));
//		$mail->addTo('hl@shopware.de');
//		
//		$mail->send();
		
		$status = $this->Request()->getParam('status');
		$transactionId = $this->Request()->getParam('transactionId');
		$secret = $this->Request()->getParam('secret');
				
		$this->saveEosStatus($status, $transactionId, $secret);
	}
	
	/**
	 * End action method
	 */
	public function endAction()
	{		
		$status = $this->Request()->getParam('status');
		$validate = $this->Request()->getParam('validate');
		
		if(!empty($status) && !empty($validate)) {
			$sql = '
				SELECT *
				FROM s_plugin_payment_eos
				WHERE userID=?
				AND `added` > DATE_SUB(NOW(), INTERVAL 6 HOUR)
				AND MD5(CONCAT(`secret`, ?)) = ?
				ORDER BY `added` DESC
			';
			$payment = Shopware()->Db()->fetchRow($sql, array(
				Shopware()->Session()->sUserId,
				$status,
				$validate
			));
			if(!empty($payment)) {
				$payment['status'] = $status;
				$this->saveEosStatus($payment['status'], $payment['transactionID'], $payment['secret']);
			}
		} else {
			for ($i=0; $i<20; $i++) {
				$sql = '
					SELECT status, fail_message, added, secret
					FROM s_plugin_payment_eos
					WHERE userID=?
					AND `added` > DATE_SUB(NOW(), INTERVAL 6 HOUR)
					ORDER BY `added` DESC
				';
				$payment = Shopware()->Db()->fetchRow($sql, array(Shopware()->Session()->sUserId));
				if(empty($payment) || !empty($payment['status'])) {
					break;
				}
				sleep(1);
			}
		}
		$this->View()->Payment = $payment;
	}
	
	/**
	 * Book action method
	 */
	public function bookAction()
	{		
		$book_date = new Zend_Date();
		if(!empty($this->Request()->book_date)) {
			$book_date->set($this->Request()->book_date);
		}
		$book_delay = clone $book_date;
		$now_date = new Zend_Date();
		$now_date->setHour(0)->setMinute(0)->setSecond(0);
		$book_delay->sub($now_date);
		$book_delay = round($book_delay / 60 / 60 / 24);
		$book_delay = $book_delay > 15 ? 0 : $book_delay;
	
		if(!empty($this->Request()->book_amount)) {
			$book_amount = str_replace(',', '.', $this->Request()->book_amount);
		} else {
			$book_amount = str_replace(',', '.', $this->Request()->amount);
		}
		
		$secret = $this->Request()->secret;
		$transactionId = $this->Request()->transactionID;
				
		$params = array();
		$params['kontaktid'] = $transactionId;
		$params['werbecode'] = $this->Request()->werbecode;
		$params['spb_referenz'] = $this->Request()->reference;
		$params['spb_betrag'] = $this->formatEosNumber($book_amount);
		$params['spb_warten'] = 25;
		$params['BuchungDelayTage'] = $book_delay;
		
		$requestUrl = 'https://www.eos-payment.de/kartebuch.acgi';
				
		$respone = $this->doEosRequest($requestUrl, $params);
		
		$errorMessages = $this->checkEosResponse($respone);
		$errorMessages = implode("\n", $errorMessages);
				
		$sql = '
			UPDATE s_plugin_payment_eos
			SET
				`book_amount`=?,
				`book_date`=?,
				`changed`=NOW(),
				`fail_message`=IFNULL(?, `fail_message`)
			WHERE transactionID=? AND secret=?';
		$result = Shopware()->Db()->query($sql, array(
			$book_amount,
			$book_date,
			$errorMessages,
			$transactionId,
			$secret
		));
		
		sleep(3);
		
		//$errorMessages = print_r($params, true);
		
		echo Zend_Json::encode(array('success'=>empty($errorMessages), 'message'=>!empty($errorMessages) ? utf8_encode($errorMessages) : ''));
	}
	
	/**
	 * Cancel action method
	 */
	public function cancelAction()
	{
		$params = array();
		$params['kontaktid'] = $this->Request()->transactionID;
		$params['werbecode'] = $this->Request()->werbecode;
		$params['spr_warten'] = 25;
		$params['spr_referenz'] = $this->Request()->reference;
		
		$requestUrl = 'https://www.eos-payment.de/KarteStorno.acgi';
		
		$respone = $this->doEosRequest($requestUrl, $params);
				
		$errorMessages = $this->checkEosResponse($respone);
		$errorMessages = implode("\n", $errorMessages);
		
		//$errorMessages = print_r($respone, true);
		
		sleep(3);
		
		echo Zend_Json::encode(array('success'=>empty($errorMessages), 'message'=>!empty($errorMessages) ? utf8_encode($errorMessages) : ''));
	}
	
	/**
	 * Refresh action method
	 */
	public function refreshAction()
	{
		$params = array();
		$params['spr_haendlerid'] = $this->Config()->merchantId;
		$params['spr_haendlercode'] = $this->Config()->merchantCode;
		$params['kontaktid'] = $this->Request()->transactionID;
		$params['werbecode'] = $this->Request()->werbecode;
		$params['referenz'] = $this->Request()->reference;
		
		$requestUrl = 'https://www.eos-payment.de/GetZahlungsdaten.acgi';
		
		$respone = $this->doEosRequest($requestUrl, $params);
				
		$errorMessages = $this->checkEosResponse($respone);
		$errorMessages = implode("\n", $errorMessages);
				
		$sql = '
			UPDATE s_plugin_payment_eos
			SET
				`clear_status`=IFNULL(?, `clear_status`),
				`bank_account`=IFNULL(?, `bank_account`),
				`account_expiry`=IFNULL(?, `account_expiry`),
				`changed`=NOW(),
				`fail_message`=IFNULL(?, `fail_message`)
			WHERE transactionID=? AND secret=?
		';
		$result = Shopware()->Db()->query($sql, array(
			!empty($respone['spr_statuscode']) ? $respone['spr_statuscode'] : null,
			$this->getEosBankAccount($respone),
			$this->getEosAccountExpiry($respone),
			$errorMessages,
			$this->Request()->transactionID,
			$this->Request()->secret
		));
		
		echo Zend_Json::encode(array(
			'success' => empty($errorMessages),
			'message' => !empty($errorMessages) ? utf8_encode($errorMessages) : ''
		));
	}

	/**
	 * Memo action method
	 */
	public function memoAction()
	{
		$secret = $this->createPaymentUniqueId();
		
		$params = array();
		$params['haendlerid'] = $this->Config()->merchantId;
		$params['haendlercode'] = $this->Config()->merchantCode;
		$params['refkontaktid'] = $this->Request()->transactionID;
		$params['refwerbecode'] = $this->Request()->werbecode;
		$params['referenz'] = $this->Request()->reference.'_'.$params['refkontaktid'];
		
		$params['waehrung'] = $this->Request()->currency;
		$params['bruttobetrag'] = $this->formatEosNumber($this->Request()->memo_amount);
		$params['warten'] = 25;
		
		$params['NotifyURL'] = $this->Front()->Router()->assemble(array(
			'action' => 'notify',
			'secret' => $secret
		));
		$params['NotifyURL'] .= strpos($params['NotifyURL'], '?') === false ? '?' : '&';
		$params['NotifyURL'] .= 'transactionId=<<KontaktID>>' . '&';
		$params['NotifyURL'] .= 'status=<<statuscode>>';
		
		$params['NotifyURL'] = str_replace('hl.shopvm.de/trunk/shopware.php', 'sh.shopvm.de/test.php', $params['NotifyURL']);
		
		if($this->Request()->payment_key == 'eos_credit') {
			$requestUrl = 'https://www.eos-payment.de/gutschrift_cc_Folge.acgi';
		} else {
			$requestUrl = 'https://www.eos-payment.de/gutschrift_elv_Folge.acgi';
		}
		
		$respone = $this->doEosRequest($requestUrl, $params);
				
		$errorMessages = $this->checkEosResponse($respone);
		$errorMessages = implode("\n", $errorMessages);

		$sql = '
			INSERT INTO `s_plugin_payment_eos` (
				`userID`, `secret`, `werbecode`, `transactionID`,
				`reference`, `amount`, `currency`, `payment_key`,
				`added`, `changed`, `book_amount`, `book_date`
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, NOW());
		';
		Shopware()->Db()->query($sql, array(
			$this->Request()->userID,
			$secret,
			$respone['werbecode'],
			$respone['kontaktid'],
			$respone['referenz'],
			str_replace(',', '.', $params['bruttobetrag']),
			$params['waehrung'],
			$this->Request()->payment_key . '_memo',
			str_replace(',', '.', $params['bruttobetrag'])
		));
		
		sleep(3);
				
		echo Zend_Json::encode(array(
			'success' => empty($errorMessages),
			'message' => !empty($errorMessages) ? utf8_encode($errorMessages) : ''
		));
	}
	
	/**
	 * Format eos number
	 *
	 * @param string $value
	 * @return string
	 */
	public function formatEosNumber($value)
	{
		$value = str_replace(',', '.', $value);
		return number_format($value, 2, ',', '');
	}
	
	/**
	 * Returns eos account expiry
	 *
	 * @param array $respone
	 * @return Zend_Date|null
	 */
	public function getEosAccountExpiry($respone)
	{
		if(empty($respone['spr_gueltigbismonat'])
		  || empty($respone['spr_gueltigbisjahr'])) {
		  	return null;
		}
		return new Zend_Date($respone['spr_gueltigbisjahr'] . '-' . $respone['spr_gueltigbismonat'] . '-01');
	}
	
	/**
	 * Returns eos bank account
	 *
	 * @param array $respone
	 * @return string
	 */
	protected function getEosBankAccount($respone)
	{
		if(!empty($respone['spr_kontonummer'])) {
			$l = strlen($respone['spr_kontonummer']);
			$s = $l > 8 ? 4 : 2;
			$n = str_repeat('*', $l - $s) . substr($respone['spr_kontonummer'], -$s);
			$bank_account = array(
				'Konto:',
				$n,
				'BLZ:',
				$respone['spr_blz']
			);
		} elseif(!empty($respone['spr_kartennummer'])) {
			$bank_account = array(
				$respone['spr_kartentyp'],
				$respone['spr_kartennummer']
			);
		} else {
			$bank_account = array();
		}
		return implode(' ', $bank_account);
	}
		
	/**
	 * Do request method
	 *
	 * @param string $url
	 * @param array $params
	 * @return array
	 */
	public function doEosRequest($url, $params=array())
	{
		$client = new Zend_Http_Client($url);
		$client->setParameterGet($params);
		if (extension_loaded('curl')) {
			$adapter = new Zend_Http_Client_Adapter_Curl();
			$adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
			$adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
			$client->setAdapter($adapter);
		}
		$respone = $client->request();
		$respone = $respone->getBody();
		
		//$respone = file_get_contents($url . '?' . http_build_query($params, '', '&'));
		
		$result = null;
		$respone = str_replace('&#37;2B' , ' ', $respone);
		parse_str($respone, $result);
		return $result;
	}
	
	/**
	 * Save eos status method
	 *
	 * @param string $status
	 * @param string $transactionId
	 * @param string $secret
	 */
	public function saveEosStatus($status, $transactionId, $secret)
	{
		switch ($status) {
			case 'fail':
			case 'back':
			case 'success':
			case 'error':
				$sql = '
					UPDATE s_plugin_payment_eos
					SET status=?, `changed`=NOW(),
						bank_account=IFNULL(?, bank_account),
						fail_message=IFNULL(?, fail_message)
					WHERE transactionID=? AND secret=?
				';
				Shopware()->Db()->query($sql, array(
					$status,
					$this->Request()->getParam('accountNumber'),
					$this->Request()->getParam('failMessage'),
					$transactionId,
					$secret
				));
				break;
			default:
				break;
		}
		
		if($status == 'success') {
			$sql = '
				SELECT `id`, `clear_status`
				FROM s_plugin_payment_eos
				WHERE `added` > DATE_SUB(NOW(), INTERVAL 6 HOUR)
				AND `userID`=?
				AND `transactionId`=?
				AND `secret`=?
				AND `amount`=?
				AND `currency`=?
			';
			$payment = Shopware()->Db()->fetchRow($sql, array(
				Shopware()->Session()->sUserId,
				$transactionId,
				$secret,
				$this->getAmount(),
				$this->getCurrencyShortName()
			));
			if(!empty($payment)) {
				$this->saveOrder($transactionId, $secret);
			}
			if(!empty($payment['clear_status'])) {
				$this->saveEosStatus($payment['clear_status'], $transactionId, $secret);
			}
		}		
		
		if(!empty($status) && is_numeric($status)) {
			$sql = '
				UPDATE s_plugin_payment_eos
				SET clear_status=?, `changed`=NOW()
				WHERE transactionID=? AND secret=?
			';
			$result = Shopware()->Db()->query($sql, array(
				$status,
				$transactionId,
				$secret
			));
		}
		
		if($status == 2) {
			$sql = '
				UPDATE s_plugin_payment_eos
				SET
					`book_amount`=IFNULL(`book_amount`, `amount`),
					`book_date`=IFNULL(`book_date`, NOW())
				WHERE transactionID=? AND secret=?
			';
			$result = Shopware()->Db()->query($sql, array(
				$transactionId,
				$secret
			));
		}
			
		switch ($status) {
			case 1:
				$paymentStatus = 18;
				break;
			case 2:
				$paymentStatus = 12;
				break;
			case 6:
				$paymentStatus = 20;
				break;
			case 4:
			case 5:
				$paymentStatus = 17;
				break;
			case 10:
			case 11:
				$paymentStatus = 21;
				break;
			default:
				break;
		}
		
		if(!empty($paymentStatus)) {
			$this->savePaymentStatus(
				$transactionId, $secret,
				$paymentStatus,
				$this->Config()->paymentStatusMail
			);
		}
	}
	
	/**
	 *  Save eos response method
	 *
	 * @param unknown_type $respone
	 * @param unknown_type $secret
	 */
	public function saveEosResponse($respone, $secret)
	{
		$sql = '
			INSERT INTO `s_plugin_payment_eos` (
				`userID`, `secret`, `werbecode`, `transactionID`,
				`reference`, `amount`, `currency`, `payment_key`,
				`added`, `changed`
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW());
		';
		Shopware()->Db()->query($sql, array(
			Shopware()->Session()->sUserId,
			$secret,
			$respone['werbecode'],
			$respone['kontaktid'],
			$respone['referenz'],
			$this->getAmount(),
			$this->getCurrencyShortName(),
			$this->getPaymentShortName()
		));
		$sql = '
			UPDATE s_order SET transactionID=?
			WHERE userID=? AND temporaryID=?
			AND status=-1
		';
		Shopware()->Db()->query($sql, array(
			$respone['kontaktid'],
			Shopware()->Session()->sUserId,
			Shopware()->SessionID()
		));
	}
	
	/**
	 * Check eos response method
	 *
	 * @param unknown_type $respone
	 * @return unknown
	 */
	public function checkEosResponse($respone)
	{
		$errorMessages = array();
		if(!empty($respone['status']) && $respone['status']=='ERROR') {
			$fields = array(
				'BLZ', 'blz', 'kontonummer', 'kontoinhaber', 'kartennummer', 
				'karteninhaber','haendlercode','haendlerid',
				'vorname', 'nachname', 'errorcode', 'waehrung', 'bruttobetrag'
			);
			foreach ($fields as $field) {
				if(isset($respone[$field])) {
					$errorCode = $respone[$field];
				} elseif(isset($respone['spr_'.$field])) {
					$errorCode = $respone['spr_'.$field];
				} else {
					continue;
				}
				$errorMessage = $this->getEosErrorMessage($errorCode, $field);
				if(!empty($errorMessage)) {
					$errorMessages[] = $errorMessage;
				}
			}
			if(empty($errorMessages)) {
				$errorMessages[] = 'Ein unbekannter Fehler ist aufgetreten.';
			}
		} elseif(!empty($respone['status']) && $respone['status']=='WAIT') {
			$errorMessages[] = 'Vorgang konnte nicht in innerhalb von 25 Sekunden abgeschlossen werden.';
		}
		return $errorMessages;
	}
	
	/**
	 * Returns eos error message
	 *
	 * @param int $errorCode
	 * @param string $field
	 * @return unknown
	 */
	public function getEosErrorMessage($errorCode, $field)
	{
		$field = end(explode('_', $field));
		$fieldList = array(
			'blz' => 'Bankleitzahl',
			'plz' => 'Postleitzahl',
			'vname' => 'Vorname',
			'nname' => 'Nachname',
			'strasse' => 'Straße',
			'gebdatum' => 'Geburtstag',
		);
		if(isset($fieldList[$field])) {
			$field = $fieldList[$field];
		}
		$field = ucfirst($field);
		
		if($field=="errorcode")
		{
			$errorList = array(
				1910 => 'Bank ist offline',
				1920 => 'Bankleitzahl wegen Fusion nicht mehr gültig',
				1930 => 'Bankkonto ist nicht für Giropay zugrelassen',
				1940 => 'Fehlerhafte Kontodaten',
				1900 => 'Wartungsmodus',
				2400 => 'Kontonummer nicht gültig für Online Banking',
				4900 => 'Transaktion nicht autorisiert',
				4500 => 'Status der Transaktion ist unbekannt'
			);
			if(isset($errorList[(int) $errorCode]))
				return $errorList[(int) $errorCode];
			else 
				return false;
		}
		
		$errorList = array(
			30001 => 'Feld "'.$field.'" wurde nicht übergeben.',
			30002 => 'Feld "'.$field.'" ist leer.',
			30011 => 'Eintrag im Feld "'.$field.'" in Negativliste gefunden (Wert nicht erlaubt).',
			30012 => 'Unzulässige Zeichenwiederholung im Feld "'.$field.'".',
			30013 => 'Inhalt vom Feld "'.$field.'" darf nicht mit einer Ziffer beginnen.',
			30014 => 'Ziffern in der Eingabe vom im Feld "'.$field.'" nicht erlaubt.',
			30031 => 'Wert vom Feld "'.$field.'" zu klein.',
			30032 => 'Wert vom Feld "'.$field.'" zu groß.',
			30072 => 'Keine gültige HändlerID.',
			30073 => 'Händler hat keinen Kreditkartenaccount.',
			30074 => 'Falscher URL Sicherheitscode des Händlers.',
			30075 => 'Händler ist noch nicht freigeschaltet.',
			30076 => 'Falsches Passwort des Händlers.',
			30201 => $field.' ist ungültig.',
			30202 => $field.' ist ungültig.',
			30320 => 'Buchung bereits erfolgreich durchgeführt.',
			30401 => 'Kein PDF Job gefunden.',
			30402 => 'PDF noch nicht bearbeitet.',
			30403 => 'PDF noch nicht auf Server.',
			30411 => 'Ungültiger Werbecode.',
		);
		if(isset($errorList[(int) $errorCode])) {
			return $errorList[(int) $errorCode];
		}
		
		$errorCodeSub = substr($errorCode,0,2);
		$errorValue = (int) substr($errorCode,2);

		$errorSubList = array(
			31 => 'Es müssen im Feld "'.$field.'" mindestens xxx Zeichen eingegeben werden.',
			34 => 'Es dürfen im Feld "'.$field.'" höchstens xxx Zeichen eingegeben werden.',
		);
		if (isset($errorSubList[(int) $errorCodeSub])) {
			return str_replace('xxx', $errorValue, $errorSubList[(int) $errorCodeSub]);
		}
	}

	/**
	 * Returns eos payment referenz
	 *
	 * @param array $user
	 * @return string
	 */
	public function getEosReferenz($user)
	{
		$referenz = $user['billingaddress']['customernumber']
			. '_' . $user['billingaddress']['firstname']
			. '_' . $user['billingaddress']['lastname'];
		$referenz = str_replace(
			array('Ä', 'Ü', 'Ö', 'ä', 'ü', 'ö', 'ß', ' '),
			array('Ae', 'Ue', 'Oe', 'ae', 'ue', 'oe', 'ss', '_'),
			$referenz
		);
		$referenz = preg_replace('#[^A-Za-z0-9_]#', '', $referenz);
		return $referenz;
	}
	
	/**
	 * Returns payment plugin config
	 *
	 * @return unknown
	 */
	public function Config()
	{
		return Shopware()->Plugins()->Frontend()->PaymentEos()->Config();
	}
}