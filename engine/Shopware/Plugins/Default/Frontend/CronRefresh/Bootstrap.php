<?php
/**
 * Plugin to cleanup shopware statistic tables in intervals
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author st.hamann
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_CronRefresh_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Defining Cronjob-Events
	 * @return bool
	 */
	public function install()
	{		
		$this->subscribeEvent($this->createEvent('Shopware_CronJob_Clearing', 'onCronJobClearing'));
		$this->subscribeEvent($this->createEvent('Shopware_CronJob_Translation', 'onCronJobTranslation'));
		$this->subscribeEvent($this->createEvent('Shopware_CronJob_Search', 'onCronJobSearch'));
		return true;
	}

	/**
	 * Clear s_emarketing_lastarticles / s_statistics_search / s_core_log in 30 days interval
	 * Delete all entries older then 30 days.
	 * To change this time - modify sql-queries
	 * @static
	 * @param Shopware_Components_Cron_CronJob $job
	 * @return void
	 */
	public static function onCronJobClearing(Shopware_Components_Cron_CronJob $job)
	{
		// Delete all entries from lastarticles older then 30 days
		$sql = '
			DELETE FROM s_emarketing_lastarticles WHERE `time` < date_add(current_date, interval -30 day)
		';
		$result = Shopware()->Db()->query($sql);
		$data['lastarticles']['rows'] = $result->rowCount();

		// Delete all entries from search statistic older then 30 days
		$sql = '
			DELETE FROM s_statistics_search WHERE datum < date_add(current_date, interval -30 day)
		';
		$result = Shopware()->Db()->query($sql);
		$data['search']['rows'] = $result->rowCount();

		// Delete all entries from s_core_log older then 30 days
		$sql = '
			DELETE FROM s_core_log WHERE datum < date_add(current_date, interval -30 day)
		';
		$result = Shopware()->Db()->query($sql);
		$data['log']['rows'] = $result->rowCount();
	}

	/**
	 * Cleanup / Regenerate Shopware translation table used in search for example
	 * @static
	 * @param Shopware_Components_Cron_CronJob $job
	 * @return void
	 */
	public static function onCronJobTranslation(Shopware_Components_Cron_CronJob $job)
	{
		Shopware()->Modules()->Articles()->sCreateTranslationTable();
	}

	/**
	 * Recreate shopware search index 
	 * @static
	 * @param Shopware_Components_Cron_CronJob $job
	 * @return void
	 */
	public static function onCronJobSearch(Shopware_Components_Cron_CronJob $job)
	{
		@ini_set("memory_limt","265M");
		@set_time_limit(0);
		Shopware()->Modules()->Search()->sCreateIndex();
	}
}