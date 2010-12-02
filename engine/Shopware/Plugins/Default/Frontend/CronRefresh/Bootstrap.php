<?php
class Shopware_Plugins_Frontend_CronRefresh_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
		$this->subscribeEvent($this->createEvent('Shopware_CronJob_Clearing', 'onCronJobClearing'));
		$this->subscribeEvent($this->createEvent('Shopware_CronJob_Translation', 'onCronJobTranslation'));
		$this->subscribeEvent($this->createEvent('Shopware_CronJob_Search', 'onCronJobSearch'));
		return true;
	}
	
	public static function onCronJobClearing(Shopware_Components_Cron_CronJob $job)
	{		
		if(empty($data['lastarticles']['range'])) {
			$data['lastarticles']['range'] = 2592000; //30 Days
		}
		if(empty($data['search']['range'])) {
			$data['search']['range'] = 2592000; //30 Days
		}
		if(empty($data['log']['range'])) {
			$data['log']['range'] = 2592000; //30 Days
		}
		
		$sql = '
			DELETE FROM s_emarketing_lastarticles WHERE time < ?
		';
		$result = Shopware()->Db()->query($sql, array(
			date('Y-m-d H:i:s', time()-$data['lastarticles']['interval'])
		));
		$data['lastarticles']['rows'] = $result->rowCount();
		
		$sql = '
			DELETE FROM s_statistics_search WHERE datum < ?
		';
		$result = Shopware()->Db()->query($sql, array(
			date('Y-m-d H:i:s', time()-$data['search']['interval'])
		));
		$data['search']['rows'] = $result->rowCount();
		
		$sql = '
			DELETE FROM s_core_log WHERE datum < ?
		';
		$result = Shopware()->Db()->query($sql, array(
			date('Y-m-d H:i:s', time()-$data['log']['interval'])
		));
		$data['log']['rows'] = $result->rowCount();
	}
	
	public static function onCronJobTranslation(Shopware_Components_Cron_CronJob $job)
	{
		Shopware()->Modules()->Articles()->sCreateTranslationTable();
	}
	
	public static function onCronJobSearch(Shopware_Components_Cron_CronJob $job)
	{
		Shopware()->Modules()->Search()->sCreateIndex();
	}
}