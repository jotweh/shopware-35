<?php
class Shopware_Components_Cron_CronManager
{
	protected function startCronJob(Shopware_Components_Cron_CronJob $job)
	{
		$job->previous = $job->next;
		$job->next = strtotime($job->next);
		do {
			$job->next += $job->interval;
		} while($job->next<time());
		$job->next = $job->next;
		$job->start = time();
		
		$sql = '
			UPDATE `s_crontab` 
			SET `start` = ?,
			`end` = NULL
			WHERE `id` = ?
			AND `end` IS NOT NULL
		';
		
		$result = Shopware()->Db()->query($sql, array(
			date('Y-m-d H:i:s', $job->start),
			$job->id
		));
		
		return $result->rowCount()>0;
	}
	
	protected function endCronJob(Shopware_Components_Cron_CronJob $job)
	{
		$sql = '
			UPDATE `s_crontab` 
			SET `end` = ?,
				`data` = ?,
				`next` = ?
			WHERE `id` = ?
		';
		$result = Shopware()->Db()->query($sql, array(
			date('Y-m-d H:i:s', time()),
			empty($job->data) ? '' : serialize($job->data),
			date('Y-m-d H:i:s', $job->next),
			$job->id
		));
	}
	
	public function runCronJob(Shopware_Components_Cron_CronJob $job)
	{
		try {
			if($this->startCronJob($job)) {
				Shopware()->Events()->notifyUntil($job->getName(), $job);
				$this->endCronJob($job);
			}
		} catch (Exception $e) {
			$job->data = array('error'=>(string) $e);
			$this->stopCronJob($job);
		}
	}
	
	public function runCronJobs()
	{
		while ($job = $this->readCronJob()) {
			$this->runCronJob($job);
		}
	}
	
	public function stopCronJob (Shopware_Components_Cron_CronJob $job)
	{
		$sql = '
			UPDATE s_crontab
			SET active=0, end=?, data=?
			WHERE id = ?
		';
		$result = Shopware()->Db()->query($sql, array(
			date('Y-m-d H:i:s', time()),
			empty($job->data) ? '' : serialize($job->data),
			$job->id,
		));
	}
	
	public function readCronJob()
	{
		$sql = '
			SELECT id, `action` as name, `name` as description, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`
			FROM s_crontab WHERE active=1 AND next < ? AND end IS NOT NULL
			ORDER BY next 
		';
		$job = Shopware()->Db()->fetchRow($sql, array(date('Y-m-d H:i:s', time())));
		if(!$job) {
			return null;
		}
		$name = 'Shopware_CronJob_'.str_replace(' ','',ucwords(str_replace('_',' ',$job['name'])));
		
		$job = new Shopware_Components_Cron_CronJob($name, $job);
		return $job;
	}
}