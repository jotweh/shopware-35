<?php
class Shopware_Plugins_Frontend_Statistics_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
	 		'Enlight_Controller_Front_DispatchLoopShutdown',
	 		'onDispatchLoopShutdown'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
		
	public static function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if($response->isException()||$request->getModuleName()!='frontend'){
			return;	
		}
		
		if($request->getClientIp(false)===null) {
			return;
		}
		
		if (strpos(Shopware()->Config()->BlockIP, $request->getClientIp(false))!==false){
			return;
		}
		
		$plugin = Shopware()->Plugins()->Frontend()->Statistics();
		
		$plugin->sCleanUp();
		$plugin->sRefreshLog();
		$plugin->sRefreshReferer($request);
		$plugin->sRefreshCurrentUsers($request);
		$plugin->sRefreshPartner($request);
	}
	
	public function sCleanUp()
	{
		if ((rand()%10) == 0) {
			$sql = 'DELETE FROM s_statistics_currentusers WHERE time < DATE_SUB(NOW(), INTERVAL 3 MINUTE)';
			Shopware()->Db()->query($sql);
			$sql = 'DELETE FROM s_statistics_pool WHERE datum!=CURDATE()';
			Shopware()->Db()->query($sql);	
		}
	}
	
	public function sRefreshCurrentUsers($request)
	{
		$sql = 'INSERT INTO s_statistics_currentusers VALUES (NULL,?,?,NOW(),?)';
		Shopware()->Db()->query($sql,array(
			$request->getClientIp(false),
			$_SERVER['PHP_SELF'],
			empty(Shopware()->Session()->sUserId) ? 0 : (int) Shopware()->Session()->sUserId
		));
	}

	public function sRefreshLog($request){

		$ip = $_SERVER['REMOTE_ADDR'];
		
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

	public function sRefreshReferer($request)
	{
		if(empty($_SERVER['HTTP_REFERER'])) return;
		if(strpos($_SERVER['HTTP_REFERER'], 'http')!==0) return;
		if(strpos($_SERVER['HTTP_REFERER'], Shopware()->Config()->Host)!==false) return;
		
		$referer = $_SERVER['HTTP_REFERER'];
		
		if (!empty($_GET['sPartner'])){
			$referer = $referer.'$'.$_GET['sPartner'];
		}
		
		Shopware()->Session()->sReferer = $referer;
		$sql = 'INSERT INTO s_statistics_referer (datum, referer) VALUES (NOW(),?)';
		$sql = Shopware()->Db()->query($sql, array($referer));
	}
	
	public function sRefreshPartner($request)
	{
		if (isset($_GET['sPartner'])) {
			if (strpos($_GET['sPartner'], 'sCampaign')===0) {
				$campaignID = (int) str_replace('sCampaign', '', $_GET['sPartner']);
				Shopware()->Session()->sPartner = 'sCampaign'.$campaignID;
				$sql = '
					UPDATE s_campaigns_mailings
					SET clicked = clicked + 1
					WHERE id = ?
				';
				Shopware()->Db()->query($sql, array($campaignID));
			} else {
				$sql = 'SELECT * FROM s_emarketing_partner WHERE active=1 AND idcode=?';
				$partner = Shopware()->Db()->fetchRow($sql, array($_GET['sPartner']));
				if(!empty($partner))
				{
					if ($partner['cookielifetime']){
						$valid = time() + $partner['cookielifetime'];
					} else {
						$valid = '0';
					}
					setcookie('sPartner', $_GET['sPartner'], $valid);
				}
				Shopware()->Session()->sPartner = $_GET['sPartner'];
			}
		} elseif (isset($_COOKIE['sPartner'])) {
			$sql = 'SELECT idcode FROM s_emarketing_partner WHERE active=1 AND idcode=?';
			$partner = Shopware()->Db()->fetchOne($sql, array($_COOKIE['sPartner']));
			if(empty($partner)) {
				unset(Shopware()->Session()->sPartner);
			} else {
				Shopware()->Session()->sPartner = $partner;
			}
		}
	}
}