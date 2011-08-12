<?php
/**
 * Shopware ControllerBase Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Core_ControllerBase_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
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

	/**
	 * Event listener method
	 * 
	 * Read base controller data
	 * 
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{				
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
			return;	
		}
		
		$view = $args->getSubject()->View();
		$plugin = Shopware()->Plugins()->Core()->ControllerBase();
		
		$view->setNoCache(true);
		
		$view->Controller = $args->getSubject()->Request()->getControllerName();
		$view->sBasketQuantity = Shopware()->Modules()->Basket()->sCountBasket();
		$view->sBasketAmount = $plugin->getBasketAmount();
		$view->sNotesQuantity = Shopware()->Modules()->Basket()->sCountNotes();
		$view->sUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
		$view->sUniqueRand = md5(uniqid(mt_rand(), true));
		$view->Shopware = Shopware();

		$view->setNoCache(false);
		
		if(!$view->isCached()) {
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
	
	/**
	 * Returns basket amount
	 *
	 * @return float
	 */
	public function getBasketAmount()
	{
		$amount = Shopware()->Modules()->Basket()->sGetAmount();
		return empty($amount) ? 0 : array_shift($amount);
	}
	
	/**
	 * Returns current category id
	 *
	 * @return int
	 */
	public function getCategoryCurrent()
	{
		if(!empty(Shopware()->System()->_GET['sCategory'])) {
			return (int) Shopware()->System()->_GET['sCategory'];
		} elseif(Shopware()->Front()->Request()->getQuery('sCategory')) {
			return (int) Shopware()->Front()->Request()->getQuery('sCategory');	
		} else {
			return (int) Shopware()->Shop()->get('parentID');
		}
	}
	
	/**
	 * Return current categories
	 *
	 * @return array
	 */
	public function getCategories()
	{
		return Shopware()->Modules()->Categories()->sGetCategories($this->getCategoryCurrent());
	}
	
	/**
	 * Returns start category id
	 *
	 * @return int
	 */
	public function getCategoryStart()
	{
		return Shopware()->Shop()->get('parentID');
	}
	
	/**
	 * Returns main categories
	 *
	 * @return array
	 */
	public function getMainCategories()
	{
		return Shopware()->Modules()->Categories()->sGetMainCategories();
	}
	
	/**
	 * Return shop languages
	 *
	 * @return array
	 */
	public function getLanguages()
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
	
	/**
	 * Return shop currencies
	 *
	 * @return array
	 */
	public function getCurrencies()
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
	
	/**
	 * Return cms menu items
	 *
	 * @return array
	 */
	public function getMenu()
	{
		$menu = array();
		$sql = '
			SELECT * FROM s_cms_static WHERE grouping!=? ORDER BY position ASC
		';
		$links = Shopware()->Db()->fetchAll($sql, array(''));
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
		
		if (Shopware()->Shop()->get('navigation')) {
			foreach (explode(';', Shopware()->Shop()->get('navigation')) as $group) {
				$group = explode(':', $group);
				if(empty($group[0])||empty($group[1])||empty($menu[$group[1]])) {
					continue;
				}
				$menu[$group[0]] = $menu[$group[1]];
			}
		}
		
		return $menu;
	}
	
	/**
	 * Return box campaigns items
	 *
	 * @return array
	 */
	public function getCampaigns()
	{
		$campaigns = array('leftTop'=>array(), 'leftMiddle'=>array(), 'leftBottom'=>array(), 'rightMiddle'=>array(),);
							
		if(!empty(Shopware()->Config()->CampaignsPositions)) {
			$campaignPositions = explode(';', Shopware()->Config()->CampaignsPositions);
			
			foreach ($campaignPositions as $campaign_position) {
				$group = explode(':', $campaign_position);
				if(empty($group[1])) {
					continue;
				}
			
				$campaigns[$group[1]] = Shopware()->Modules()->Marketing()->sCampaignsGetList(
					$this->getCategoryCurrent(), $group[1]
				);
				if (empty($campaigns[$group[1]])){
					$campaigns[$group[1]] = array();
				}
			}
		}
		
		return $campaigns;
	}
	
	/**
	 * Returns capabilities
	 *
	 * @return array
	 */
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}