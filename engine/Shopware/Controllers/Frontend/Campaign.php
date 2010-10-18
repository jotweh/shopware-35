<?php
class Shopware_Controllers_Frontend_Campaign extends Enlight_Controller_Action
{
	
	public function indexAction()
	{
		if (empty($this->request()->sCampaign)){
			return $this->forward("index","index");
		}
		
		$campaign = Shopware()->Modules()->Marketing()->sCampaignsGetDetail(intval($this->request()->sCampaign));
		if (empty($campaign["id"])){
			return $this->forward("index","index");
		}
		
		$this->View()->sCampaign = $campaign;
	}
}