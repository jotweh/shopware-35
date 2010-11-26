<?php
class Shopware_Plugins_Frontend_Seo_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
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
		
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend'){
			return;
		}
		
		$config = Shopware()->Config();
		if(!empty($config['sTEMPLATEOLD'])) {
			$snippet = Shopware()->Config()->Snippets();
		}
			
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
				if($snippet!==null) {
					$snippet->set('sIndexMetaDescriptionStandard', $meta_description, false);
				} else {
					$view->SEOMetaDescription = $meta_description;
				}
			}
		}
		
		$viewport = $request->getControllerName()=='viewport' ? $request->getParam('sViewport') : $request->getControllerName();
				
		if(!empty($viewport_blacklist)&&in_array($viewport, $viewport_blacklist))
		{
			$meta_robots = 'noindex,follow';
		}
		elseif(!empty($query_blacklist))
		{
			foreach ($query_blacklist as $query_key)
			{
				if($request->getQuery($query_key)!==null)
				{
					$meta_robots = 'noindex,follow';
				}
			}
		}
		if(!empty($meta_robots))
		{
			if($snippet!==null) {
				$snippet->set('sIndexMetaRobots', $meta_robots, false);
			}
		}
				
		if(empty($config['sTEMPLATEOLD'])) {
			$view->addTemplateDir(dirname(__FILE__).'/templates/');
			$view->extendsTemplate('frontend/plugins/seo/index.tpl');
			
			if(!empty($meta_robots)) {
				$view->SeoMetaRobots = $meta_robots;
			}
			if(!empty($meta_robots)) {
				$view->SeoMetaDescription = $meta_description;
			}
		}
	}
	
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
			$source = preg_replace('#(<script[^>]*?>.*?</script>)|(<style[^>]*?>.*?</style>)|<!--[^\[].*?-->#msi' ,'$1$2', $source);
		}
		
		// Trim whitespace
		if(!empty($config['sSEOREMOVEWHITESPACES'])&&empty($template->_tpl_vars['debug_output'])) {
			require_once(SMARTY_DIR.'plugins/outputfilter.trimwhitespace.php');
			$source = smarty_outputfilter_trimwhitespace($source, $template);
		}
		
		return $source;
	}
}