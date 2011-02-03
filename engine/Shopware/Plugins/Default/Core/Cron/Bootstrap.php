<?php
class Shopware_Plugins_Core_Cron_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	protected $results = array();

	public function install()
	{		
		$event = $this->createEvent(
	 		'Enlight_Controller_Dispatcher_ControllerPath_Backend_Cron',
	 		'onGetControllerPath'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Controller_Front_AfterSendResponse',
	 		'onAfterSendResponse'
	 	);
		$this->subscribeEvent($event);
		
		$event = $this->createEvent(
	 		'Enlight_Bootstrap_InitResource_Cron',
	 		'onInitResourceCron'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	
	public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/Cron.php';
    }
    
    public static function onAfterSendResponse(Enlight_Event_EventArgs $args)
    {
		//Shopware()->Cron()->runCronJobs();
    }
    
    public static function onInitResourceCron(Enlight_Event_EventArgs $args)
    {
		return new Shopware_Components_Cron_CronManager();
    }
    
}