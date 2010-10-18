<?php
class Shopware_Plugins_Backend_Menu_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Menu',
	 		'onInitResourceMenu'
	 	);
		$this->subscribeEvent($event);
		return true;
	}
	
	public static function onInitResourceMenu(Enlight_Event_EventArgs $args)
	{
		$menu = new Shopware_Components_Menu();
		
		$saveHandler = new Shopware_Components_Menu_SaveHandler_DbTable();
		$menu->setSaveHandler($saveHandler);
		
		$menu->load();
				
        return $menu;
	}
}