<?php
/**
 * Router rewrite plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_RouterRewrite_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Init plugin method
	 *
	 * @return bool
	 */
	public function init()
	{		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Front_SendResponse',
	 		array($this, 'onAfterSendResponse')
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Bootstrap_InitResource_SessionID',
	 		array($this, 'onInitResourceSessionID')
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Router_Route',
	 		array($this, 'onRoute')
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Router_Assemble',
	 		array($this, 'onAssemble')
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Router_Assemble',
	 		array($this, 'onAssemble')
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Front_PreDispatch',
	 		array($this, 'onPreDispatch')
	 	);
		Shopware()->Events()->registerListener($event);
	}
	
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{		
		$event = $this->createEvent(
			'Enlight_Controller_Front_StartDispatch',
			'onStartDispatch'
		);
		$this->subscribeEvent($event);
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onStartDispatch(Enlight_Event_EventArgs $args)
    {
    	Shopware()->Plugins()->Frontend()->RouterRewrite();
    }
    
    /**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
    public function onPreDispatch(Enlight_Event_EventArgs $args)
	{		
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if($response->isException() 
		  || $request->isPost()
		  || $request->isXmlHttpRequest()
		  || ($request->getModuleName() && $request->getModuleName()!='frontend')
		  || (!$request->getParam('RewriteAlias') && !$request->getParam('RewriteOld'))) {
			return;	
		}
		
		$router = $args->getSubject()->Router();
		
		$currentLocation = $request->getScheme().'://'.$request->getHttpHost().$request->getRequestUri();
		$query = $request->getQuery();
		unset($query['RewriteOld'], $query['RewriteAlias'], $query['RewriteUrl']);
		$location = $router->assemble($query);
				
		if($currentLocation!=$location) {
			$response->setRedirect($location, 301);
		}
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onAfterSendResponse(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		
		if($request->getModuleName()!='frontend'){
			return;
		}
		
		if(!Shopware()->Bootstrap()->issetResource('Db')
			|| !Shopware()->Bootstrap()->issetResource('Shop')) {
    		return;
    	}
		
		$sql = 'SELECT value FROM s_core_config WHERE name=?';
		$last_update = Shopware()->Db()->fetchOne($sql, array('sROUTERLASTUPDATE'));
		if(!empty($last_update))
			$last_update = unserialize($last_update);
		if(empty($last_update)||!is_array($last_update))
			$last_update = array();
		
		$shopId = Shopware()->Shop()->getId();
		$cache = (empty(Shopware()->Config()->RouterCache)||Shopware()->Config()->RouterCache<360) ? 86400 : (int) Shopware()->Config()->RouterCache;
		$current_time = Shopware()->Db()->fetchOne('SELECT NOW()');
		$cached_time = empty($last_update[$shopId]) ? false : $last_update[$shopId];
				
		if(empty($cached_time)
		  || strtotime($cached_time)<strtotime($current_time)-$cache) {
			Shopware()->Modules()->RewriteTable()->sCreateRewriteTable();
	    	
	    	$data = $last_update;
			$data[$shopId] = $current_time;
			$data = serialize($data);
			
	    	$sql = 'UPDATE `s_core_config` SET `value`=? WHERE `name`=?';
	    	Shopware()->Db()->query($sql, array($data,'sROUTERLASTUPDATE'));
		}
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onInitResourceSessionID(Enlight_Event_EventArgs $args)
	{
		$alias = $this->sGetQueryAlias('sCoreId');
		if(!empty($alias)&&!empty($_GET[$alias]))
		{
			return $_GET[$alias];
		}
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onRoute(Enlight_Event_EventArgs $args)
	{
		$request = $args->getRequest();
		$url = $args->getRequest()->getPathInfo();
		$url = ltrim($url, '/');
				
		$sql = 'SELECT subshopID, org_path, main FROM s_core_rewrite_urls WHERE path LIKE ?';
		$result = Shopware()->Db()->fetchAssoc($sql, array($url));
		if(!empty($result) && !empty($result[Shopware()->Shop()->getId()])) {
			$result = $result[Shopware()->Shop()->getId()];
			$alias_list = $this->sGetQueryAliasList();
			foreach ($alias_list as $key => $alias) {
				$value = $request->getQuery($alias);
				if($value!==null) {
					$request->setQuery($key, $value);
					$request->setQuery($alias, null);
				}
			}
			parse_str($result['org_path'], $query);
			if(empty($result['main'])) {
				$request->setParam('RewriteAlias', true);
			} else {
				$request->setParam('RewriteUrl', true);
			}
			return $query;
		}
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onAssemble(Enlight_Event_EventArgs $args)
	{
		$params = $args->getParams();
		
		if(!empty($params['module'])&&$params['module']!='frontend') {
			return;
		}
		
		if(!Shopware()->Bootstrap()->issetResource('Db')
			|| !Shopware()->Bootstrap()->issetResource('Shop')) {
    		return;
    	}
    	
		unset($params['sCoreId'], $params['sUseSSL'], $params['title'], $params['module']);
		if(!empty($params['sAction'])&&$params['sAction']=='index') {
			unset($params['sAction']);
		}
		
		/*
		if(!empty(Shopware()->Config()->RouterUrlCache)) {
			$id = 'Shopware_RouterRewrite_'.Shopware()->Shop()->getId().'_'.md5(serialize($params));
			$cache = Shopware()->Cache();
			if(!$cache->test($id)) {
				$url = $this->assemble($params);
				$cache->save($url, $id, array('Shopware_RouterRewrite'), Shopware()->Config()->RouterUrlCache);
			} else {
				$url = $cache->load($id);
			}
			return $url;
		} else {
		*/
			return $this->assemble($params);
		/*
		}
		*/
	}
	
	/**
	 * Assemble query method
	 *
	 * @param array $query
	 * @return string
	 */
	public function assemble($query)
	{
		$org_query = array ();
		if(!empty($query['sViewport'])) {
			$org_query = array ('sViewport' => $query['sViewport']);
			switch ($query['sViewport']) {
				case 'detail':
					$org_query['sArticle'] = $query['sArticle'];
					break;
				case 'cat':
					$org_query ['sCategory'] = $query['sCategory'];
					break;
				case 'campaign':
					$org_query ['sCampaign'] = $query['sCampaign'];
					break;
				case 'support':
				case 'ticket':
					if(!empty($query['sFid'])) {
						$org_query['sFid'] = $query['sFid'];
						if($query['sFid']==Shopware()->Config()->InquiryID) {
							$org_query['sInquiry'] = $query['sInquiry'];
						}
					}
					break;
				case 'custom':
					$org_query['sCustom'] = $query['sCustom'];
					break;
				case 'content':
					$org_query['sContent'] = $query['sContent'];
					break;
				default:
				case 'sale':
				case 'admin':
					if(isset($query['sAction'])) {
						$org_query['sAction'] = $query['sAction'];
					}
					break;
			}
			$org_path = http_build_query($org_query, '', '&');
			
			if(!empty(Shopware()->Config()->RouterUrlCache)) {
				$id = 'Shopware_RouterRewrite_'.Shopware()->Shop()->getId().'_'.md5($org_path);
				$cache = Shopware()->Cache();
				if(!$cache->test($id)) {
					$sql = 'SELECT path FROM s_core_rewrite_urls WHERE org_path=? AND subshopID=? AND main=1 ORDER BY id DESC';
					$path = Shopware()->Db()->fetchOne($sql, array($org_path, Shopware()->Shop()->getId()));
					$cache->save($path, $id, array('Shopware_RouterRewrite'), Shopware()->Config()->RouterUrlCache);
				} else {
					$path = $cache->load($id);
				}
			} else {
				$sql = 'SELECT path FROM s_core_rewrite_urls WHERE org_path=? AND subshopID=? AND main=1 ORDER BY id DESC';
				$path = Shopware()->Db()->fetchOne($sql, array($org_path, Shopware()->Shop()->getId()));
			}
			
		} else {
			$path = '';
		}
		if(!empty($path)&&!empty(Shopware()->Config()->RouterToLower)) {
			$path = strtolower($path);
		}
		if (!empty($path)) {
			$query = array_diff_key($query, $org_query);
			if (!empty($query)) {
				$path .= '?'.$this->sRewriteQuery($query);
			}
			return $path;
		}
		return null;
	}
	
	protected $sQueryAliasList;
	
	/**
	 * Returns query alias list
	 *
	 * @return array
	 */
	public function sGetQueryAliasList()
	{
		if(!isset($this->sQueryAliasList))
		{
			$this->sQueryAliasList = array();
			if(!empty(Shopware()->Config()->SeoQueryAlias))
			foreach (explode(',',Shopware()->Config()->SeoQueryAlias) as $alias)
			{
				list($key, $value) = explode('=', trim($alias));
				$this->sQueryAliasList[$key] = $value;
			}
		}
		return $this->sQueryAliasList;
	}
	
	/**
	 * Returns query alias by key
	 *
	 * @param string $key
	 * @return string
	 */
	public function sGetQueryAlias($key)
	{
		if(!isset($this->sQueryAliasList)) $this->sGetQueryAliasList();
		return isset($this->sQueryAliasList[$key]) ? $this->sQueryAliasList[$key] : null;
	}
	
	/**
	 * Rewrite query method
	 *
	 * @param array $query
	 * @return string
	 */
	public function sRewriteQuery($query)
	{
		if(!empty($query))
		{
			$tmp = array();
			foreach ($query as $key => $value)
			{
				if($alias = $this->sGetQueryAlias($key))
					$tmp[$alias] = $value;
				else
					$tmp[$key] = $value;
			}
			$query = $tmp; unset($tmp);
		}
		return http_build_query($query, '', '&' );
	}
}