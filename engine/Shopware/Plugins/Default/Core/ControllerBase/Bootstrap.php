<?php
class Shopware_Plugins_Core_ControllerBase_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch',
	 		'onPostDispatch',
	 		100
	 	);
		$this->subscribeEvent($event);
		return true;
	}
			
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{				
		if(!$args->getSubject()->Request()->isDispatched()){
			return;
		}
		if($args->getSubject()->Request()->getModuleName()!='frontend'){
			return;
		}
		
		$view = $args->getSubject()->View();
		$plugin = Shopware()->Plugins()->Core()->ControllerBase();
		
		$view->setNoCache(true);
		
		$view->sBasketQuantity = $plugin->getBasketQuantity();
		$view->sBasketAmount = $plugin->getBasketAmount();
		$view->sNotesQuantity = $plugin->getNotesQuantity();
		$view->sUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
		$view->sUniqueRand = md5(uniqid(mt_rand(), true));
		$view->Shopware = Shopware();

		$view->setNoCache(false);
		
		if(!$view->isCached())
		{
			$view->Shop = Shopware()->Shop();
			$view->Locale = Shopware()->Locale();
			$view->sCategories = $plugin->getCategories();
			$view->sMainCategories = $plugin->getMainCategories();
			$view->sCategoryStart = $plugin->getCategoryStart();
			$view->sCategoryCurrent = $plugin->getCategoryCurrent();
			
			$view->sMenu = $plugin->getMenu();
			$view->sCampaigns = $plugin->getCampaigns();
			$view->sLanguages = $plugin->getLanguages();
			$view->sCurrencies = $plugin->getCurrencies();
			
			$view->sShopname = Shopware()->Config()->Shopname;
		}
	}
	
	function getBasketAmount()
	{
		$amount = Shopware()->Modules()->Basket()->sGetAmount();
		return empty($amount) ? 0 : array_shift ($amount);
	}
	
	function getBasketQuantity()
	{
		return Shopware()->Modules()->Basket()->sCountBasket();
	}
	
	function getNotesQuantity()
	{
		return count(Shopware()->Modules()->Basket()->sGetNotes());
	}
	
	function getCategoryCurrent()
	{
		if(!empty(Shopware()->System()->_GET['sCategory'])) {
			return (int) Shopware()->System()->_GET['sCategory'];
		} elseif(!Shopware()->Front()->Request()->getQuery('sCategory')) {
			return (int) Shopware()->System()->sLanguageData[Shopware()->System()->sLanguage]['parentID'];
		} else {
			return (int) Shopware()->Front()->Request()->getQuery('sCategory');	
		}
	}
	
	function getCategories()
	{
		return Shopware()->Modules()->Categories()->sGetCategories($this->getCategoryCurrent());
	}
	
	function getCategoryStart()
	{
		if(empty(Shopware()->System()->sLanguageData[Shopware()->System()->sLanguage]['parentID'])) {
			return Shopware()->Front()->Request()->getQuery('sCategory');
		} else {
			return Shopware()->System()->sLanguageData[Shopware()->System()->sLanguage]['parentID'];
		}
	}
	
	function getMainCategories()
	{
		return Shopware()->Modules()->Categories()->sGetMainCategories();
	}
	
	function getLanguages()
	{
		if (empty(Shopware()->System()->sSubShop['switchLanguages'])
			|| !Shopware()->License()->checkLicense('sLANGUAGEPACK'))
		{
			return false;
		}
		$sql = '
			SELECT c.*, IF(id=?,1,0) as flag FROM s_core_multilanguage c
			WHERE id IN ('.str_replace('|', ',', Shopware()->System()->sSubShop['switchLanguages']).')
		';
		return Shopware()->Db()->fetchAll($sql, array(Shopware()->System()->sLanguage));
	}
	
	function getCurrencies()
	{
		if (empty(Shopware()->System()->sSubShop['switchCurrencies'])
			|| !Shopware()->License()->checkLicense('sLANGUAGEPACK'))
		{
			return false;
		}
		$sql = '
			SELECT c.*, IF(id=?,1,0) as flag FROM s_core_currencies c
			WHERE id IN ('.str_replace('|', ',', Shopware()->System()->sSubShop['switchCurrencies']).')
			ORDER BY position ASC
		';
		return Shopware()->Db()->fetchAll($sql, array(Shopware()->System()->sCurrency['id']));
	}
		
	function getMenu()
	{
		$menu = array();
		$sql = '
			SELECT * FROM s_cms_static WHERE grouping!=\'\' ORDER BY position ASC
		';
		$links = Shopware()->Db()->fetchAll($sql);
		foreach ($links as $link)
		{
			$groups = explode('|', $link['grouping']);
			foreach ($groups as $group)
			{
				$group = trim($group);
				if(empty($group)) continue;
				$menu[$group][] = $link;
			}
		}
		
		if (!empty(Shopware()->System()->sSubShop['navigation']))
		{
			foreach (explode(';', Shopware()->System()->sSubShop['navigation']) as $group)
			{
				$group = explode(':', $group);
				if(empty($group[0])||empty($group[1])||empty($menu[$group[1]])) continue;
				$menu[$group[0]] = $menu[$group[1]];
			}
		}
		
		return $menu;
	}
	
	function getCampaigns()
	{
		$campaigns = array('leftTop'=>array(), 'leftMiddle'=>array(), 'leftBottom'=>array(), 'rightMiddle'=>array(),);
							
		if(!empty(Shopware()->Config()->CampaignsPositions))
		{
			$campaignPositions = explode(';', Shopware()->Config()->CampaignsPositions);
			
			foreach ($campaignPositions as $campaign_position)
			{
				$group = explode(':', $campaign_position);
				if(empty($group[1])) continue;
			
				$campaigns[$group[1]] = Shopware()->Modules()->Marketing()->sCampaignsGetList($this->getCategoryCurrent(),$group[1]);
				if (empty($campaigns[$group[1]])){
					$campaigns[$group[1]] = array();
				}
			}
		}
		
		return $campaigns;
	}
	
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}