<?php
class Shopware_Plugins_Frontend_Recommendation_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
	 		'onPostDispatchDetail'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch_Frontend_Index',
	 		'onPostDispatchIndex'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Action_PostDispatch_Frontend_Listing',
	 		'onPostDispatchListing'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Recommendation',
	 		'onGetControllerPath'
	 	);
	 	$this->subscribeEvent($event);
	 	
		$event = $this->createEvent(
 		'Enlight_Controller_Dispatcher_ControllerPath_Backend_RecommendationAdmin',
 		'onGetControllerPathBackend'
	 	);
	 	$this->subscribeEvent($event);
	 	
	 	$form = $this->Form();
	 	
		$form->setElement('text', 'max_banner', array('label'=>'Limit Banner','value'=>'12', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'max_supplier', array('label'=>'Limit Hersteller','value'=>'255', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'supplier_page', array('label'=>'Hersteller pro Slider','value'=>'4', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		
		$form->setElement('text', 'max_seen_articles', array('label'=>'"Auch angeschaut" Limit','value'=>'40', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'page_seen_articles', array('label'=>'"Auch angeschaut" pro Seite','value'=>'4', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'max_bought_articles', array('label'=>'"Auch gekauft" Limit','value'=>'40', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'page_bought_articles', array('label'=>'"Auch gekauft" pro Seite','value'=>'4', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'max_simlar_articles', array('label'=>'"Ähnliche Interessen" Limit','value'=>'20', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'page_similar_articles', array('label'=>'"Ähnliche Interessen" pro Seite','value'=>'3', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'max_new_articles', array('label'=>'Neue Artikel Limit','value'=>'20', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('text', 'page_new_articles', array('label'=>'Neue Artikel pro Seite','value'=>'3', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		
		$form->setElement('text', 'block_detail', array('label'=>'Standard Block Detailseite','value'=>'frontend_detail_index_tabs', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('checkbox', 'bought_too', array('label'=>'"Kunden kauften auch" anzeigen','value'=>'1', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('checkbox', 'similary_viewed', array('label'=>'"Kunden haben sich ebenfalls angesehen" anzeigen','value'=>'1', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
		
		/*
		
		this->View()->block_detail = $config->block_detail;
		$this->View()->bought_too = $config->bought_too;
		$this->View()->similary_viewed = $config->similary_viewed;<b>
		*/
		$form->save();
	 	$parent = $this->Menu()->findOneBy('label', 'Marketing');
		

		$item = $this->createMenuItem(array(
			'label' => 'Slider Komponenten',
			'onclick' => 'openAction(\'RecommendationAdmin\');',
			'class' => 'ico2 layout',
			'active' => 1,
			'parent' => $parent,
			'style' => 'background-position: 5px 5px;'
		));
		
		$this->Menu()->addItem($item);
		
		$this->Menu()->save();
	 	return true;
	}
	
	public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/RecommendationAdmin.php';
    }
    
	public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Recommendation.php';
    }
    
	public static function onPostDispatchDetail(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		if(!$request->isDispatched()||$response->isException()) {
			return;
		}
		$config = Shopware()->Plugins()->Frontend()->Recommendation()->Config();
		
		if ($config->bought_too){
			$view->extendsBlock($config->block_detail,'<div class="slider"></div>','prepend');
		}
		if ($config->similary_viewed){
			$view->extendsBlock($config->block_detail,'<div class="slider2"></div>','prepend');
		}
		$view->extendsTemplate('frontend/plugins/recommendation/blocks_detail.tpl');
	}
	
	public static function onPostDispatchListing(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$category = $request->sCategory;
		$config = Shopware()->Plugins()->Frontend()->Recommendation()->Config();
		
		$getElements = Shopware()->Db()->fetchRow("
		SELECT * FROM s_plugin_recommendations WHERE categoryID = ?
		",array($category));
		
		if (!empty($getElements["id"])){
			if ($getElements["banner_active"]){
				$banner = Shopware()->Modules()->Marketing()->sBanner($category,$config->max_banner);
				$view->banners = $banner;
				$view->banner_active = 1;
			}
			if ($getElements["supplier_active"]){
				$suppliers = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($category,$config->max_supplier);
				$suppliers = array_chunk($suppliers,$config->supplier_page);
				$view->suppliers = $suppliers;
				$view->supplier_active = 1;
			}
			if ($getElements["bought_active"]){
				$view->bought_active = 1;
			}
			if ($getElements["new_active"]){
				$view->new_active = 1;
			}
			$view->extendsTemplate('frontend/plugins/recommendation/blocks_listing.tpl');
			
		}

		if(!$request->isDispatched()||$response->isException()) {
			return;
		}
		
	}
	
	public static function onPostDispatchIndex(Enlight_Event_EventArgs $args)
	{
		
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();
		$category = Shopware()->Shop()->get('parentID');
		$config = Shopware()->Plugins()->Frontend()->Recommendation()->Config();
		// Check which elements should be loaded
		$getElements = Shopware()->Db()->fetchRow("
		SELECT * FROM s_plugin_recommendations WHERE categoryID = ?
		",array($category));
		
		if (!empty($getElements["id"])){
			if ($getElements["banner_active"]){
				$banner = Shopware()->Modules()->Marketing()->sBanner($category,$config->max_banner);
				$view->banners = $banner;
				$view->banner_active = 1;
			}
			if ($getElements["supplier_active"]){
				$suppliers = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($category,$config->max_supplier);
				$suppliers = array_chunk($suppliers,$config->supplier_page);
				$view->suppliers = $suppliers;
				$view->supplier_active = 1;
			}
			if ($getElements["bought_active"]){
				$view->bought_active = 1;
			}
			if ($getElements["new_active"]){
				$view->new_active = 1;
			}
			$view->extendsTemplate('frontend/plugins/recommendation/blocks_index.tpl');
			
		}

		if(!$request->isDispatched()||$response->isException()) {
			return;
		}
		
	}
}