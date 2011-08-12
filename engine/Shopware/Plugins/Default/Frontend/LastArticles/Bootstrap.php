<?php
/**
 * Shopware LastArticles Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_LastArticles_Bootstrap extends Shopware_Components_Plugin_Bootstrap
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
			'onPostDispatch'
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('checkbox', 'show', array(
			'label'=>'Artikelverlauf anzeigen',
			'value'=>1,
			'scope'=>Shopware_Components_Form::SCOPE_SHOP
		));
		$form->setElement('text', 'controller', array(
			'label'=>'Controller-Auswahl',
			'value'=>'index, listing, detail, custom, newsletter, sitemap, campaign',
			'scope'=>Shopware_Components_Form::SCOPE_SHOP
		));
		$form->setElement('text', 'thumb', array(
			'label'=>'Vorschaubild-Größe',
			'value'=>Shopware()->Config()->LastArticlesThumb,
			'scope'=>Shopware_Components_Form::SCOPE_SHOP
		));
		$form->setElement('text', 'time', array(
			'label'=>'Speicherfrist in Tagen',
			'value'=>15
		));
		$form->save();
				
		return true;
	}
	
	/**
	 * Event listener method
	 * 
	 * Read the last article in defined controllers
	 * Saves the last article in detail controller
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatch(Enlight_Event_EventArgs $args)
	{		
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		
		if(!$request->isDispatched()
		  || $response->isException()
		  || $request->getModuleName() != 'frontend'
		  || !empty(Shopware()->Session()->Bot)) {
			return;	
		}
		
		$config = Shopware()->Plugins()->Frontend()->LastArticles()->Config();
		
		if($request->getControllerName() == 'detail') {
			$article = $args->getSubject()->View()->sArticle;
			$thumb = $config->thumb !== null ? (int) $config->thumb : (int) Shopware()->Config()->LastArticlesThumb;
			$time = $config->time > 0 ? (int) $config->time : 15;
						
			Shopware()->Modules()->Articles()->sSetLastArticle(
				isset($article['image']['src'][$thumb]) ? $article['image']['src'][$thumb] : null,
				$article['articleName'],
				$article['articleID']
			);
			
			$id = 'Shopware_LastArticles_Cleanup';
			$cache = Shopware()->Cache();
			if ($cache->load($id) === false) {
				$sql = '
					DELETE FROM s_emarketing_lastarticles
					WHERE time < DATE_SUB(CONCAT(CURDATE(), ?), INTERVAL ? DAY)
				';
				Shopware::Instance()->Db()->query($sql, array(' 00:00:00', $time));
				$cache->save(true, $id, array('Shopware_Plugin'), 86400);
			}
		}
		
		if(empty($config->show)) {
			return;
		}
		if(!empty($config->controller)
		  && strpos($config->controller, $request->getControllerName()) === false) {
			return;
		}
		
		$args->getSubject()->View()->assign(
			'sLastArticles',
			Shopware()->Modules()->Articles()->sGetLastArticles(),
			true
		);
	}
}