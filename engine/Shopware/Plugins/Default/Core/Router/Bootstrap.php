<?php
/**
 * Shopware Router Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Core_Router_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Init plugin method
	 *
	 * @return bool
	 */
	public function install()
	{	
		$event = $this->createEvent(
	 		'Enlight_Controller_Front_RouteStartup',
	 		'onRouteStartup'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Front_RouteShutdown',
	 		'onRouteShutdown'
	 	);
		$this->subscribeEvent($event);
			
		$event = $this->createEvent(
	 		'Enlight_Controller_Router_FilterAssembleParams',
	 		'onFilterAssemble'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Router_FilterUrl',
	 		'onFilterUrl'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Router_Assemble',
	 		'onAssemble',
	 		100
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onRouteStartup(Enlight_Event_EventArgs $args)
	{
		$front = $args->getSubject();
		$request = $front->Request();
		$router = $front->Router();
		
		$request->setControllerKey('sViewport');
		$request->setActionKey('sAction');
				
		$aliases = array(
			'controller' => 'sViewport',
			'action' => 'sAction',
		);
		foreach ($aliases as $key=>$aliase)	{
			if (($value = $request->getParam($key))!==null) {
				$request->setParam($aliase, $value);
			}
		}
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onRouteShutdown(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		$request->setQuery($request->getQuery()+$request->getUserParams());
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onFilterAssemble(Enlight_Event_EventArgs $args)
	{			
		$params = $args->getReturn();
		$request = $args->getRequest();
		
		$aliases = array(
			'sDetails' => 'sArticle',
			'cCUSTOM' => 'sCustom',
			'controller' => 'sViewport',
			'action' => 'sAction',
		);
		foreach ($aliases as $key=>$aliase)
		{
			if (isset($params[$key])) {
				$params[$aliase] = $params[$key];
				unset ($params[$key]);
			}
		}
		
		if (!empty($params['sDetails'])&&!empty($params['sViewport'])&&$params['sViewport']=='detail'){
			$params['sArticle'] = $params['sDetails'];
			unset($params['sDetails']);
		}
		
		if(empty($params['module'])) {
			$params['module'] = $request->getModuleName() ? $request->getModuleName() : '';
			if(empty($params['sViewport'])) {
				$params['sViewport'] = $request->getControllerName() ? $request->getControllerName() : 'index';
				if(empty($params['sAction'])) {
					$params['sAction'] = $request->getActionName() ? $request->getActionName() : 'index';
				}
			}
		}

		if(isset($params['sAction'])) {
			$params = array_merge(array('sAction'=>null), $params);
		}
		if(isset($params['sViewport'])) {
			$params = array_merge(array('sViewport'=>null), $params);
		}
		
		unset($params['sUseSSL'], $params['fullPath'], $params['appendSession'], $params['forceSecure'], $params['sCoreId']);
		
		if(!empty($params['sViewport']) && $params['sViewport']=='detail'
		  && !empty(Shopware()->Config()->RouterRemoveCategory)) {
			unset($params['sCategory']);
		}
				
		return $params;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onFilterUrl(Enlight_Event_EventArgs $args)
	{
		$params = $args->getParams();
		$userParams = $args->get('userParams');

		if(!empty($params['module']) && $params['module']!='frontend' && empty($userParams['fullPath'])) {
			return $args->getReturn();
		}
		
		if(empty(Shopware()->Config()->UseSSL)) {
			$useSSL = false;
		} elseif(!empty($userParams['sUseSSL'])||!empty($userParams['forceSecure'])) {
			$useSSL = true;
		} elseif(!empty($params['sViewport']) &&
		  in_array($params['sViewport'], array('account', 'checkout', 'register', 'ticket', 'note'))) {
			$useSSL = true;
		} else {
			$useSSL = false;
		}
		
		$url = '';
	
		if(!isset($userParams['fullPath']) || !empty($userParams['fullPath'])) {
			$url = $useSSL ? 'https://' : 'http://';
			if(Shopware()->Bootstrap()->hasResource('Shop')
			  && Shopware()->Bootstrap()->hasResource('Front')) {
				$url .= Shopware()->Shop()->getHost().Shopware()->Front()->Request()->getBasePath();
			} else {
				$url .= Shopware()->Config()->BasePath;
			}
			$url .= '/';
		}
		
		if(empty(Shopware()->Config()->RouterUseModRewrite)
		  && (!empty($params['sViewport']) || empty(Shopware()->Config()->RedirectBaseFile))) {
			$url .= Shopware()->Config()->BaseFile;
			$url .= '/';
		}
		
		$url .= $args->getReturn();
		
		if (!empty($userParams['appendSession'])) {
			$url .= strpos($url, '?')===false ? '?' : '&';
			$url .= 'sCoreId='.Shopware()->SessionID();
		}
		
		return $url;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onAssemble(Enlight_Event_EventArgs $args)
	{
		$params = $args->getParams();
		unset($params['title']);
		return $args->getSubject()->assembleDefault($params);
	}
	
	/**
	 * Returns capabilities
	 */
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}