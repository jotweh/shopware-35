<?php
class Shopware_Plugins_Core_System_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
	 	$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_System',
	 		'onInitResourceSystem'
	 	);
		$this->subscribeEvent($event);
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Modules',
	 		'onInitResourceModules'
	 	);
		$this->subscribeEvent($event);
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Adodb',
	 		'onInitResourceAdodb'
	 	);
		$this->subscribeEvent($event);
		return true;
	}
	
	public static function onInitResourceSystem(Enlight_Event_EventArgs $args)
	{
		$shop = Shopware()->Shop();
				
		require_once(Shopware()->OldPath().'engine/core/class/sSystem.php');

    	$system = new sSystem();
    	    	
    	Shopware()->Bootstrap()->registerResource('System', $system);
    	
    	$system->sMODULES = Shopware()->Modules();
    	$system->sDB_CONNECTION = Shopware()->Adodb();
    	$system->sSMARTY = Shopware()->Template();
    	$system->sCONFIG = Shopware()->Config();
    	$system->sMailer = Shopware()->Mail();
    	
		$request = Shopware()->Front()->Request();
		if($request!==null) {
			$system->_GET = $request->getQuery();
			$system->_POST = $request->getPost();
			$system->_COOKIE = $request->getCookie();
		}
		
		if(Shopware()->Bootstrap()->issetResource('Session')) {
			$system->_SESSION = Shopware()->Session();
			$system->sSESSION_ID = Shopware()->SessionID();
		}
    	
    	$system->sLoadHookPoints();
		
		$shop = Shopware()->Shop();
		$config = Shopware()->Shop()->Config();
		
		$system->sCurrencyData = self::getCurrencyData();
		$system->sLicenseData = self::getLicenceData();
		$system->sSubShops = self::getShopData();
		$system->sLanguageData = $system->sSubShops;
		$system->sBotSession = self::getUserIsBot();
		
		$system->sLanguage = $shop->getId();
		$system->sSubShop = $system->sSubShops[$shop->getId()];
		$system->sCurrency = $shop->Currency()->getShortName();
		$system->sCurrencyData[$system->sCurrency]['flag'] = true;
		$system->sCurrency = $system->sCurrencyData[$system->sCurrency];
		if($request!==null) {
			$system->sPathBase = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
		} else {
			$system->sPathBase = 'http://'.$config->BasePath;
		}
		$system->sPathArticleImg = $system->sPathBase.$config->ArticleImages.'/';
		$system->sPathBanner = $system->sPathBase.$config->Banner.'/';
		$system->sPathSupplierImg = $system->sPathBase.$config->SupplierImages.'/';
		$system->sPathCmsImg = $system->sPathBase.$config->CmsImages.'/';
		$system->sPathStart = $system->sPathBase.$config->BaseFile;
		$system->sPathArticleFiles = $system->sPathBase.$config->ArticleFiles;
		$system->sBasefile = $config->BaseFile;
		
		$config['sPREMIUM'] = $system->sLicenseData['sPREMIUM'];
		$config['sCurrencies'] = $system->sCurrencyData;
		$config['sCURRENCY'] = $system->sCurrency['currency'];
		$config['sCURRENCYHTML'] = $system->sCurrency['templatechar'];
		
		if (Shopware()->Bootstrap()->issetResource('Session')&&!empty(Shopware()->Session()->sUserGroup)) {
			$system->sUSERGROUP = Shopware()->Session()->sUserGroup;
			$system->sUSERGROUPDATA = Shopware()->Session()->sUserGroupData;
		} else {
			$system->sUSERGROUP = $system->sSubShop['defaultcustomergroup'];
			$sql = 'SELECT * FROM s_core_customergroups WHERE groupkey=?';
			$system->sUSERGROUPDATA = Shopware()->Db()->fetchRow($sql, array($system->sUSERGROUP));
		}
		
		if (empty($system->sUSERGROUPDATA['tax']) && !empty($system->sUSERGROUPDATA['id'])) {
			$config['sARTICLESOUTPUTNETTO'] = 1;
		}

		return $system;
	}
	
	public static function getUserIsBot()
	{
		static $result;
		if($result !== null){
			return $result;
		}
		$result = false;
		if(!empty($_SERVER['HTTP_USER_AGENT'])) {
			$useragent = preg_replace('/[^a-z]/', '', strtolower($_SERVER['HTTP_USER_AGENT']));
		} else {
			$useragent = '';
		}
		$bots = preg_replace('/[^a-z;]/', '', strtolower(Shopware()->Config()->BotBlackList));
		$bots = explode(';',$bots);
		if(!empty($useragent) && str_replace($bots, '', $useragent)!=$useragent) {
			$result = true;
		}
		return $result;
	}
	
	public static function getCurrencyData()
	{
		$sql = 'SELECT currency as `key`, cc.* FROM s_core_currencies cc ORDER BY position ASC';
		$data = Shopware()->Db()->fetchAssoc($sql);
		return $data;
	}
	
	public static function getLicenceData()
	{
		$sql = 'SELECT module, hash FROM s_core_licences WHERE inactive=0';
		$data = Shopware()->Db()->fetchPairs($sql);
		$sql = 'SELECT hash FROM s_core_licences WHERE module LIKE ? AND inactive=0 ORDER BY module DESC';
		$data['sLANGUAGEPACK'] = Shopware()->Db()->fetchOne($sql, 'sLANGUAGEPACK%');
		return $data;
	}
	
	public static function getShopData()
	{
		$data = Shopware()->Db()->fetchAssoc('SELECT id as `key`, m.* FROM s_core_multilanguage m');
		return $data;
	}
	
	public static function onInitResourceModules(Enlight_Event_EventArgs $args)
	{
		$modules = new Shopware_Components_Modules();
		Shopware()->Bootstrap()->registerResource('Modules', $modules);
		$modules->setSystem(Shopware()->System());
    	return $modules;
	}
	
	public static function onInitResourceAdodb(Enlight_Event_EventArgs $args)
	{
		$db = new Enlight_Components_Adodb(array(
			'db' => Shopware()->Db(),
			'cache' => empty(Shopware()->Config()->DisableCache) ?  Shopware()->Cache() : null,
			'cacheTags' => array('Shopware_Adodb')
		));
    	return $db;
	}
	
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}