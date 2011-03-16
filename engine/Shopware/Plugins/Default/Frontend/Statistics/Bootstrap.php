<?php
/**
 * Shopware Statistics Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_Statistics_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return unknown
	 */
	public function install()
	{
		$event = $this->createEvent(
	 		'Enlight_Controller_Front_DispatchLoopShutdown',
	 		'onDispatchLoopShutdown'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if($response->isException()
		  || $request->getModuleName()!='frontend'
		  || $request->getClientIp(false)===null
		  || !empty(Shopware()->Session()->Bot)) {
			return;	
		}
		if(!empty(Shopware()->Config()->BlockIP) 
		  && strpos(Shopware()->Config()->BlockIP, $request->getClientIp(false))!==false) {
			return;	
		}
				
		$plugin = Shopware()->Plugins()->Frontend()->Statistics();
		
		$plugin->cleanupStatistic();
		$plugin->refreshLog($request);
		$plugin->refreshReferer($request);
		$plugin->refreshCurrentUsers($request);
		$plugin->refreshPartner($request, $response);
	}
	
	/**
	 * Cleanup statistic
	 */
	public function cleanupStatistic()
	{
		if ((rand()%10) == 0) {
			$sql = 'DELETE FROM s_statistics_currentusers WHERE time < DATE_SUB(NOW(), INTERVAL 3 MINUTE)';
			Shopware()->Db()->query($sql);
			$sql = 'DELETE FROM s_statistics_pool WHERE datum!=CURDATE()';
			Shopware()->Db()->query($sql);	
		}
	}
	
	/**
	 * Refresh current users
	 *
	 * @param Enlight_Controller_Request_Request $request
	 */
	public function refreshCurrentUsers(Enlight_Controller_Request_Request $request)
	{
		$sql = 'INSERT INTO s_statistics_currentusers (`remoteaddr`, `page`, `time`, `userID`) VALUES (?, ?, NOW(), ?)';
		Shopware()->Db()->query($sql,array(
			$request->getClientIp(false),
			$request->getRequestUri(),
			empty(Shopware()->Session()->sUserId) ? 0 : (int) Shopware()->Session()->sUserId
		));
	}

	/**
	 * Refresh visitor log
	 *
	 * @param Enlight_Controller_Request_Request $request
	 */
	public function refreshLog(Enlight_Controller_Request_Request $request){

		$ip = $request->getClientIp(false);
		
		$sql = 'SELECT id FROM s_statistics_visitors WHERE datum=CURDATE()';
		$abfrage = Shopware()->Db()->fetchOne($sql);
		if (empty($abfrage)) {
			$sql = 'INSERT INTO s_statistics_visitors (`datum`, `pageimpressions`, `uniquevisits`) VALUES(NOW(), 1, 1)';	
			Shopware()->Db()->query($sql);
		}
		
		$sql = 'SELECT id FROM s_statistics_pool WHERE datum=CURDATE() AND remoteaddr=?';
		$abfrage = Shopware()->Db()->fetchOne($sql, array($ip));
		if (empty($abfrage)) {
			$sql = 'INSERT INTO s_statistics_pool (`remoteaddr`, `datum`) VALUES (?, NOW())';
			Shopware()->Db()->query($sql, array($ip));
			$sql = 'UPDATE s_statistics_visitors SET pageimpressions=pageimpressions+1, uniquevisits=uniquevisits+1 WHERE datum=CURDATE()';
			Shopware()->Db()->query($sql);
		} else {
			$sql = 'UPDATE s_statistics_visitors SET pageimpressions=pageimpressions+1 WHERE datum=CURDATE()';
			Shopware()->Db()->query($sql);
		}
	}

	/**
	 * Refresh referer log
	 *
	 * @param Enlight_Controller_Request_Request $request
	 */
	public function refreshReferer(Enlight_Controller_Request_Request $request)
	{
		$referer = $request->getHeader('REFERER');
		
		if(empty($referer)
		  || strpos($referer, 'http')!==0
		  || strpos($referer, Shopware()->Config()->Host)!==false) {
			return;
		}
		
		Shopware()->Session()->sReferer = $referer;
		
		if ($request->getQuery('sPartner')){
			$referer .= '$'.$request->getQuery('sPartner');
		}
		
		$sql = 'INSERT INTO s_statistics_referer (datum, referer) VALUES (NOW(), ?)';
		Shopware()->Db()->query($sql, array($referer));
	}
	
	/**
	 * Refresh partner log
	 *
	 * @param Enlight_Controller_Request_Request $request
	 */
	public function refreshPartner(Enlight_Controller_Request_Request $request, Enlight_Controller_Response_Response $response)
	{
		if ($request->getQuery('sPartner') !== null) {
			if (strpos($request->getQuery('sPartner'), 'sCampaign')===0) {
				$campaignID = (int) str_replace('sCampaign', '',$request->getQuery('sPartner'));
				if(!empty($campaignID)) {
					Shopware()->Session()->sPartner = 'sCampaign'.$campaignID;
					$sql = '
						UPDATE s_campaigns_mailings
						SET clicked = clicked + 1
						WHERE id = ?
					';
					Shopware()->Db()->query($sql, array($campaignID));
				}
			} else {
				$sql = 'SELECT * FROM s_emarketing_partner WHERE active=1 AND idcode=?';
				$partner = Shopware()->Db()->fetchRow($sql, array($request->getQuery('sPartner')));
				if(!empty($partner)) {
					if ($partner['cookielifetime']){
						$valid = time() + $partner['cookielifetime'];
					} else {
						$valid = 0;
					}
					$response->setCookie('sPartner', $partner['idcode'], $valid, '/');
				}
				Shopware()->Session()->sPartner = $request->getQuery('sPartner');
			}
		} elseif ($request->getCookie('sPartner') !== null) {
			$sql = 'SELECT idcode FROM s_emarketing_partner WHERE active=1 AND idcode=?';
			$partner = Shopware()->Db()->fetchOne($sql, array($request->getCookie('sPartner')));
			if(empty($partner)) {
				unset(Shopware()->Session()->sPartner);
			} else {
				Shopware()->Session()->sPartner = $partner;
			}
		}
	}
}