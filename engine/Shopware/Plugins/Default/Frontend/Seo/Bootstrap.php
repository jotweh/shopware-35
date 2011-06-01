<?php
/**
 * Shopware SEO Plugin
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_Seo_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install SEO-Plugin
	 * @return bool
	 */
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Plugins_ViewRenderer_FilterRender',
	 		'onFilterRender'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch',
	 		'onPostDispatch'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	/**
	 * Optimize Sourcecode / Apply seo rules
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return
	 */
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend'){
			return;
		}
		
		$config = Shopware()->Config();
			
		$view = $args->getSubject()->View();
		
		$viewport_blacklist = preg_replace('#\s#', '', $config['sSEOVIEWPORTBLACKLIST']);
		$viewport_blacklist = explode(',', $viewport_blacklist);
		
		$query_blacklist = preg_replace('#\s#', '', $config['sSEOQUERYBLACKLIST']);
		$query_blacklist = explode(',', $query_blacklist);
		
		if(!empty($config['sSEOMETADESCRIPTION']))
		{
			if(!empty($view->sArticle['description']))
			{
				$meta_description = $view->sArticle['description'];
			}
			elseif(!empty($view->sArticle['description_long']))
			{
				$meta_description = $view->sArticle['description_long'];
			}
			elseif(!$request->getQuery('sCategory')&&!empty($view->sSnippets['sIndexMetaDescriptionStandard']))
			{
				$meta_description = $view->sSnippets['sIndexMetaDescriptionStandard'];
			}
			elseif(!empty($view->sCategoryContent['metadescription']))
			{
				$$meta_description = $view->sCategoryContent['metadescription'];
			}
			elseif(!empty($view->sCategoryContent['cmstext']))
			{
				$meta_description = $view->sCategoryContent['cmstext'];
			}
			if(!empty($meta_description))
			{
				$meta_description = htmlentities(strip_tags(html_entity_decode($meta_description)));
				$meta_description = trim(preg_replace('/\s\s+/', ' ', $meta_description));
			}
		}
		
		$viewport = $request->getControllerName()=='viewport' ? $request->getParam('sViewport') : $request->getControllerName();
				
		if(!empty($viewport_blacklist)&&in_array($viewport, $viewport_blacklist)) {
			$meta_robots = 'noindex,follow';
		} elseif(!empty($query_blacklist)) {
			foreach ($query_blacklist as $query_key) {
				if($request->getQuery($query_key)!==null) {
					$meta_robots = 'noindex,follow';
				}
			}
		}
		
		if(empty($config['sTEMPLATEOLD'])) {
			$view->extendsTemplate('frontend/plugins/seo/index.tpl');
			
			if(!empty($meta_robots)) {
				$view->SeoMetaRobots = $meta_robots;
			}
			if(!empty($meta_description)) {
				$view->SeoMetaDescription = $meta_description;
			}
		} else {
			$snippet = Shopware()->Config()->Snippets();
			
			if(!empty($meta_robots)) {
				$snippet->set('sIndexMetaRobots', $meta_robots, false);
			}
			if(!empty($meta_description)) {
				$snippet->set('sIndexMetaDescriptionStandard', $meta_description, false);
			}
		}
	}

	/**
	 * Remove html-comments / whitespaces
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return mixed|string
	 */
	public static function onFilterRender(Enlight_Event_EventArgs $args)
	{	
		$request = $args->getSubject()->Action()->Request();
		$response = $args->getSubject()->Action()->Response();
		$source = $args->getReturn();
		
		if(strpos($source, '<html')===false) {
			return $source;
		}
		
		$template = Shopware()->Template();
		$config = Shopware()->Config();
				
		// Remove comments
		if(!empty($config['sSEOREMOVECOMMENTS'])&&empty($template->_tpl_vars['debug_output'])) {
			// Ticket 5412
			$source = preg_replace('#(<script[^>]*?>.*?</script>)|(<style[^>]*?>.*?</style>)|(<!--\[.*?\]-->)|<!--.*?-->#msi' ,'$1$2$3', $source);
		}
		
		// Trim whitespace
		if(!empty($config['sSEOREMOVEWHITESPACES'])&&empty($template->_tpl_vars['debug_output'])) {
			require_once(SMARTY_DIR.'plugins/outputfilter.trimwhitespace.php');
			$source = smarty_outputfilter_trimwhitespace($source, $template);
		}
		
		return $source;
	}
}