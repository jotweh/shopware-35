<?php
class Shopware_Controllers_Backend_Cron extends Enlight_Controller_Action
{	
	public function init()
	{
		Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
	}
	
	public function indexAction()
	{
		$this->View()->setTemplate();
		
		while ($job = Shopware()->Cron()->readCronJob()) {
			echo "Processing ".$job->getName()."\n";
			Shopware()->Cron()->runCronJob($job);
		}
	}
}