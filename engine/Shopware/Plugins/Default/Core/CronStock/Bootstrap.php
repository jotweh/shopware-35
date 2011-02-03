<?php
class Shopware_Plugins_Core_CronStock_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
	 	$event = $this->createEvent(
	 		'Shopware_CronJob_ArticleStock',
	 		'onRun'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	
	public static function onRun(Shopware_Components_Cron_CronJob $job)
	{		
		
		$sql = '
			SELECT
				d.id,
				a.id as `articleID`,
				a.name,
				a.description,
				a.description_long,
				a.shippingtime,
				a.datum as `added`,
				a.shippingfree,
				a.releasedate,
				a.topseller,
				a.free,
				a.keywords,
				a.minpurchase,
				a.purchasesteps,
				a.maxpurchase,
				a.purchaseunit,
				a.referenceunit,
				a.taxID,
				a.supplierID,
				a.unitID,
				a.changetime as `changed`,
				d.id as `articledetailsID`,
				d.ordernumber,
				d.suppliernumber,
				d.kind,
				d.additionaltext,
				d.impressions,
				d.sales,
				d.active,
				d.instock,
				d.stockmin,
				d.esd,
				d.weight,
				d.position,
				at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10, 
				at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20,
				s.name as supplier,
				u.unit,
				t.tax
			FROM s_articles a
			INNER JOIN s_articles_details as d
			INNER JOIN s_articles_attributes as at
			LEFT JOIN s_articles_supplier as s
			ON a.supplierID = s.id
			LEFT JOIN s_core_units as u
			ON a.unitID = u.id
			LEFT JOIN s_core_tax as t
			ON a.taxID = t.id
			WHERE
				d.articleID = a.id
			AND
				d.id = at.articledetailsID
			AND
				stockmin > instock
		';
		$articles = Shopware()->Db()->fetchAssoc($sql);			
		$data = array(
			'count' =>  count($articles),
			'articledetailsIDs' =>  array_keys($articles),
		);
		$job->data = $data;
		
		if(empty($articles)) return;

		$template = clone Shopware()->Config()->Templates->{$job->inform_template};
		$template->tomail = $job->inform_mail;
		$mail = clone Shopware()->Mail();
		
		$templateEngine = Shopware()->Template();
		$templateData = $templateEngine->createData();
		
		$templateData->assign('sConfig', Shopware()->Config());
		$templateData->assign('sData', $data);
		$templateData->assign('sJob', array('articles'=>$articles));
		
		$template->frommail = $templateEngine->fetch('string:'.$template->frommail, $templateData);
		$template->fromname = $templateEngine->fetch('string:'.$template->fromname, $templateData);
		$template->tomail = $templateEngine->fetch('string:'.$template->tomail, $templateData);
		$template->subject = $templateEngine->fetch('string:'.$template->subject, $templateData);
		$template->content = $templateEngine->fetch('string:'.$template->content, $templateData);
		
		$mail->setFrom($template->frommail, $template->fromname);
		$mail->addTo($template->tomail);
		$mail->setSubject($template->subject);
		$mail->setBodyText($template->content);
		
		$mail->send();
	}
	
}