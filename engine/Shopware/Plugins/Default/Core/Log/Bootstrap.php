<?php
/**
 * Firebug Log Plugin
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Core_Log_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install log plugin
	 * @return bool
	 */
	public function install()
	{		
		$event = $this->createEvent(
			'Enlight_Bootstrap_InitResource_Log',
			'onInitResourceLog'
		);
		$this->subscribeEvent($event);
		$event = $this->createEvent(
			'Enlight_Controller_Front_RouteStartup',
			'onRouteStartup'
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('checkbox', 'logDb', array('label'=>'Fehler in Datenbank schreiben', 'value'=>1));
		$form->setElement('checkbox', 'logMail', array('label'=>'Fehler an Shopbetreiber senden', 'value'=>0));
		
		$form->save();
		
		return true;
	}

	/**
	 * Resource handler for log plugin
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return Zend_Log
	 */
	public static function onInitResourceLog(Enlight_Event_EventArgs $args)
	{
		$channel = Shopware_Plugins_Core_Log_HttpHeaders::getInstance();
		
		$log = new Zend_Log();
		$log->addPriority('TABLE', 8);
		$log->addPriority('EXCEPTION', 9);
		$log->addPriority('DUMP', 10);
		$log->addPriority('TRACE', 11);

		$log->setEventItem('date', date('Y-m-d H:i:s'));
	
		$config = Shopware()->Plugins()->Core()->Log()->Config();
		

		if(!empty($config->logDb)) {
			$writer = Zend_Log_Writer_Db::factory(array(
				'db' => Shopware()->Db(),
				'table' => 's_core_log',
				'columnmap' => array(
					'key' => 'priorityName',
					'text' => 'message',
					'datum' => 'date',
					'value2' => 'remote_address',
					'value3' => 'user_agent',
				)
			));
			$writer->addFilter(Zend_Log::ERR);
			$log->addWriter($writer);
		}
		if(!empty($config->logMail)) {
			$mail = clone Shopware()->Mail();
			$mail->addTo(Shopware()->Config()->Mail);
			$writer = new Zend_Log_Writer_Mail($mail);
			$writer->setSubjectPrependText('Fehler im Shop "'.Shopware()->Config()->Shopname.'" aufgetreten!');
			$writer->addFilter(Zend_Log::WARN);
			$log->addWriter($writer);
		}
		
		$log->addWriter(new Zend_Log_Writer_Null());
				
		return $log;
	}

	/**
	 * On Route add user-agent and remote-address to log component
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return void
	 */
	public static function onRouteStartup(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$log = Shopware()->Log();
		$log->setEventItem('remote_address', $request->getServer('REMOTE_ADDR'));
		$log->setEventItem('user_agent', $request->getServer('HTTP_USER_AGENT'));
	}
}