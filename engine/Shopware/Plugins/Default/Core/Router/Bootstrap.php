<?php
class Shopware_Plugins_Core_Router_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{			
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
	
	public static function onRouteShutdown(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		$request->setQuery($request->getQuery()+$request->getUserParams());
	}
	
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
		
		if (!empty($params['sDetails'])&&!empty($params['sViewport'])&&$params['sViewport']=='detail')
		{
			$params['sArticle'] = $params['sDetails'];
			unset($params['sDetails']);
		}
		if(empty($params['module']))
		{
			$params['module'] = $request->getModuleName() ? $request->getModuleName() : '';
			if(empty($params['sViewport']))
			{
				$params['sViewport'] = $request->getControllerName() ? $request->getControllerName() : 'index';
				if(empty($params['sAction']))
				{
					$params['sAction'] = $request->getActionName() ? $request->getActionName() : 'index';
				}
			}
		}
		
		unset($params['sUseSSL'], $params['fullPath'], $params['appendSession'], $params['forceSecure']);		
		
		return $params;
	}
	
	public static function onFilterUrl(Enlight_Event_EventArgs $args)
	{
		$params = $args->getParams();
		$userParams = $args->get('userParams');

		if(!empty($params['module'])&&$params['module']!='frontend'&&empty($userParams['fullPath'])) {
			return $args->getReturn();
		}
		
		if(empty(Shopware()->Config()->UseSSL))
		{
			$useSSL = false;
		}
		elseif(!empty($userParams['sUseSSL'])||!empty($userParams['forceSecure']))
		{
			$useSSL = true;
		}
		elseif(!empty($params['sViewport'])&&in_array($params['sViewport'], array('account', 'checkout', 'register', 'ticket', 'note')))
		{
			$useSSL = true;
		}
		else
		{
			$useSSL = false;
		}
		
		$url = '';
	
		if(!isset($userParams['fullPath'])||!empty($userParams['fullPath']))
		{
			$url = $useSSL ? 'https://' : 'http://';
			if(Shopware()->Bootstrap()->hasResource('Shop')) {
				$url .= Shopware()->Shop()->getHost().Shopware()->Front()->Request()->getBasePath();
			} else {
				$url .= Shopware()->Config()->BasePath;
			}
			$url .= '/';
		}
		
		if(empty(Shopware()->Config()->RouterUseModRewrite)&&(!empty($params['sViewport'])||empty(Shopware()->Config()->RedirectBaseFile)))
		{
			$url .= Shopware()->Config()->BaseFile;
			$url .= '/';
		}
		
		$url .= $args->getReturn();
		
		if (/*((empty($params['module'])||$params['module']=='frontend')&&$this->sUserNeedSessionID())||*/!empty($userParams['appendSession']))
		{
			$url .= strpos($url, '?')===false ? '?' : '&';
			$url .= 'sCoreId='.Shopware()->SessionID();
		}
		
		return $url;
	}
	
	public static function onAssemble(Enlight_Event_EventArgs $args)
	{
		$params = $args->getParams();
		unset($params['sCoreId'], $params['sUseSSL'], $params['title']);
		return $args->getSubject()->assembleDefault($params);
	}
	
	public function getCapabilities()
    {
        return array(
    		'install' => true,
    		'update' => true
    	);
    }
}