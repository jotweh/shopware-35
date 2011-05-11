<?php
/**
 * Shopware Cron for article ratings
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Plugins/Core/Cron
 */
class Shopware_Plugins_Core_CronRating_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Bootstrap Installation method
	 * @return bool
	 */
	public function install()
	{		
	 	$event = $this->createEvent(
	 		'Shopware_CronJob_ArticleComment',
	 		'onRun'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}

	/**
	 * Cron-Listener Method
	 * Get all completed orders within a defined timerange
	 * and send the rating call
	 * @static
	 * @param Shopware_Components_Cron_CronJob $job
	 * @return bool
	 */
	public static function onRun(Shopware_Components_Cron_CronJob $job)
	{		
		if( Shopware()->Config()->sVOTESENDCALLING == true) {
			$sendTime = intval(Shopware()->Config()->sVOTECALLINGTIME);
			
			$export = Shopware()->Api()->Export();
			
			list($y,$m,$d) = explode("-",date("Y-m-d"));
			$time = Shopware()->Adodb()->DBDate(mktime(0,0,0,$m,$d-$sendTime,$y));
			
			$orders = $export->sGetOrders (array("where"=>"(o.status = 2 OR o.status = 7) AND DATE(o.ordertime) = $time"));
			
			if(empty($orders)) {
				return true;
			}
			
			$orderIDs = array_keys($orders);
			$customers = $export->sOrderCustomers(array("orderIDs"=> $orderIDs));
			$positions = $export->sOrderDetails(array("orderIDs"=> $orderIDs));
			
			// Load Template Obj. / Mail
			
			$templateEngine = Shopware()->Template();
			$templateData = $templateEngine->createData();
			
			
			foreach ($orderIDs as $orderID)
			{
				$mail = clone Shopware()->Mail();
				$mail->clearRecipients();
				$mail->clearSubject();
				$mail->clearFrom();
				$shop = Shopware()->Shop();
				$shop->setShop($orders[$orderID]["subshopID"]);
				$shop->registerResources(Shopware()->Bootstrap());
				
				$templateData->assign('sConfig', Shopware()->Config());
				$templateData->assign('sData', $job["data"]);
				
				$template = clone Shopware()->Config()->Templates->{sARTICLECOMMENT};
			    $template->frommail = $templateEngine->fetch('string:'.$template->frommail, $templateData);
				$template->fromname = $templateEngine->fetch('string:'.$template->fromname, $templateData);
				$mail->setFrom($template->frommail, $template->fromname);
			
				$templateData->assign("sOrder",$orders[$orderID]);
				$templateData->assign("sUser",$customers[$orderID]);
				
				foreach ($positions[$orderID] as &$sArticle){
					$articleID = $sArticle["articleID"];
					$sArticle["link"] = Shopware()->Router()->assemble(array('module'=>'frontend','sViewport'=>'detail','sArticle'=>$articleID));
				}
				
				$templateData->assign("sArticles",$positions[$orderID]);
				
				$subject = $templateEngine->fetch('string:'.$template->subject, $templateData);
				$content = $templateEngine->fetch('string:'.$template->content, $templateData);
				$contentHTML = $templateEngine->fetch('string:'.$template->contentHTML, $templateData);
				
				$mail->setSubject($subject);
				
				if (!empty($customers[$orderID]["email"])){
					$mail->addTo($customers[$orderID]["email"]);
					
					if($template->ishtml == true) {
						$mail->setBodyText($content);
						$mail->setBodyHtml($contentHTML);
					}
					else{
						$mail->setBodyText($content);
					}
					$mail->send();
				}
			}
			
		}
	}

}