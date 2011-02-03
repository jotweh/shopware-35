<?php
class Shopware_Plugins_Core_CronBirthday_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{		
	 	$event = $this->createEvent(
	 		'Shopware_CronJob_Birthday',
	 		'onRun'
	 	);
		$this->subscribeEvent($event);
		
		return true;
	}
	
	public static function onRun(Shopware_Components_Cron_CronJob $job)
	{		
		
		
		$sBirthdayField = Shopware()->Config()->get("sBIRTHDAYFIELD");
		if(empty($sBirthdayField))
			$sBirthdayField = "birthday";
			
		$sVoucher = $cron->sSystem->sCONFIG['sBIRTHDAYVOUCHER'];
		if(empty($sVoucher))
			$sVoucher = "birthday";

		$like = Shopware()->Db()->quote("%-".date('m-d'));
			
			
		$sql = "
			SELECT 
				`userID`,
				`company`,
				`department`,
				`salutation`,
				`customernumber`,
				`firstname`,
				`lastname`,
				`street`,
				`streetnumber`,
				`zipcode`,
				`city`,
				`phone`,
				`fax`,
				`countryID`,
				`ustid`,
				`text1`,
				`text2`,
				`text3`,
				`text4`,
				`text5`,
				`text6`,
				`email`,
				`paymentID`,
				`firstlogin`,
				`lastlogin`,
				`newsletter`,
				`affiliate`,
				`customergroup`
			FROM 
				`s_user_billingaddress`,
				`s_user`
			WHERE 
				`accountmode` = 0
			AND
				`active` = 1
			AND
				`userID` = `s_user`.`id`
			AND
				$sBirthdayField LIKE $like
		";
		
		$users = Shopware()->Db()->fetchAll($sql);
		if($users === false) 
			return false;
		if(empty($users))
			return true;
			
		
		
		
		$sql = "
			SELECT evc.voucherID
			FROM s_emarketing_vouchers ev, s_emarketing_voucher_codes evc
			WHERE  modus = 1 AND (valid_to >= now() OR valid_to='0000-00-00')
			AND evc.voucherID = ev.id
			AND evc.userID = 0
			AND evc.cashed = 0
			AND ev.ordercode= ?
		";
		$sVoucherID =Shopware()->Db()->fetchOne($sql,array($sVoucher));
		if (empty($sVoucherID)) {
			
			$job["data"]["error"] = 1;
			return false;
		}
		
		// Load Template
		$template = clone Shopware()->Config()->Templates->{"sBIRTHDAY"};
		// Load Mailer Object
		$mail = clone Shopware()->Mail();
		// Load Template-Engine
		$templateEngine = Shopware()->Template();
		// Template-Object
		$templateData = $templateEngine->createData();
		
		$templateData->assign('sConfig', Shopware()->Config());
		$templateData->assign('sData', $job["data"]);
		
		$template->frommail = $templateEngine->fetch('string:'.$template->frommail, $templateData);
		$template->fromname = $templateEngine->fetch('string:'.$template->fromname, $templateData);
		$mail->setFrom($template->frommail, $template->fromname);
		
		
		
		
		foreach ($users as $user)
		{
			
			$sql = "
			SELECT evc.id as vouchercodeID, evc.code
			FROM s_emarketing_voucher_codes evc
			WHERE evc.voucherID = $sVoucherID
			AND evc.userID = 0
			AND evc.cashed = 0
			";
			$voucher = Shopware()->Db()->fetchRow($sql);
			if (empty($voucher)) {
				$data["error"] = 1;
				return false;
			}
			$sql = "
				UPDATE s_emarketing_voucher_codes evc
				SET
					userID={$user["userID"]}
				WHERE
					id={$voucher["vouchercodeID"]}
				AND
					userID=0
			";
			$result = Shopware()->Db()->query($sql);
			if(empty($result))
			{
				unset($voucher);
				continue;
			}
			$result = $result->rowCount();
			if(empty($result))
			{
				unset($voucher);
				continue;
			}
			$templateData->assign('sUser', $user);
			$templateData->assign('sVoucher', $voucher);
			
			
			$subject = $templateEngine->fetch('string:'.$template->subject, $templateData);
			
			$content = $templateEngine->fetch('string:'.$template->content, $templateData);
			$contentHTML = $templateEngine->fetch('string:'.$template->contentHTML, $templateData);
			
			$mail->clearRecipients();
			$mail->clearSubject();
			
			$mail->addTo($user["email"]);
			$mail->setSubject($subject);
			
			
			if ($template->ishtml){
				$mail->setBodyText($content);
				$mail->setBodyHtml($contentHTML);
				
			}else {
				$mail->setBodyText($content);
			}
			$mail->send();
			
		}
		
	}
}