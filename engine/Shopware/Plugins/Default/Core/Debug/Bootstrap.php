<?php
/**
 * Debug plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Core_Debug_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Plugin install method
	 */
	public function install()
	{		
		$event = $this->createEvent(
			'Enlight_Controller_Front_StartDispatch',
			'onStartDispatch'
		);
		$this->subscribeEvent($event);
		$form = $this->Form();
	 	
		$form->setElement('text', 'AllowIP', array('label'=>'Auf IP beschränken','value'=>''));
		$form->save();
		return true;
	}
	
	/**
	 * Plugin uninstall method
	 */
	public function uninstall()
	{
		$this->unsubscribeEvents();
		return true;
	}
	
	/**
	 * Plugin event method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onStartDispatch(Enlight_Event_EventArgs $args)
	{
		if(!Shopware()->Bootstrap()->hasResource('Log')){
			return;
		}
		
		$config = Shopware()->Plugins()->Core()->Debug()->Config();
		
		if (!empty($_SERVER['REMOTE_ADDR']) && strpos($config->AllowIP, $_SERVER['REMOTE_ADDR'])===false){
			return;
		}

		if(!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'FirePHP/')!==false) {
			$writer = new Zend_Log_Writer_Firebug();
			$writer->setPriorityStyle(8, 'TABLE');
			$writer->setPriorityStyle(9, 'EXCEPTION');
			$writer->setPriorityStyle(10, 'DUMP');
			$writer->setPriorityStyle(11, 'TRACE');
			Shopware()->Log()->addWriter($writer);
		}
		
		$error_handler = Shopware()->Plugins()->Core()->ErrorHandler();
		$error_handler->setEnabledLog(true);
		Shopware()->Db()->getProfiler()->setEnabled(true);
		
		$debug = Shopware()->Plugins()->Core()->Debug();
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Controller_Front_DispatchLoopShutdown',
	 		array($debug, 'onDispatchLoopShutdown')
	 	);
		Shopware()->Events()->registerListener($event);
		
		$event = new Enlight_Event_EventHandler(
	 		'Enlight_Plugins_ViewRenderer_PreRender',
	 		array($debug, 'onAfterRenderView')
	 	);
		Shopware()->Events()->registerListener($event);
	}
	
	/**
	 * Plugin event method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onAfterRenderView(Enlight_Event_EventArgs $args)
	{
		$template = $args->getTemplate();
		$this->logTemplate($template);
	}
	
	/**
	 * Plugin event method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
	{
		$this->logDb();
		$this->logError();
		$this->logException();
	}
	
	/**
	 * Log error method
	 */
	public function logError()
	{
		$error_handler = Shopware()->Plugins()->Core()->ErrorHandler();
		$errors = $error_handler->getErrorLog();
		
		$counts = array();
		foreach ($errors as $error_key => $error)
		{
			$counts[$error_key] = $error['count'];
		}
		array_multisort($counts, SORT_NUMERIC, SORT_DESC, $errors);
		
		if(!empty($errors))
		{
			$rows = array();
			foreach ($errors as $error)
			{
				if(!$rows) $rows[] = array_keys($error);
				$rows[] = array_values($error);
			}
			$table = array('Error Log ('.count($errors).')',
				$rows
			);
			Shopware()->Log()->table($table);
		}
	}
	
	/**
	 * Log database method
	 */
	public function logDb()
	{
		$profiler = Shopware()->Db()->getProfiler();
		$rows = array(array('time','count','sql','params'));
		$counts = array(10000);
		$total_time = 0;
		$querys = $profiler->getQueryProfiles();
		if(!$querys) {
			return;
		}
		foreach ($querys as $query)
		{
			$id = md5($query->getQuery());
			$total_time += $query->getElapsedSecs();
			if(!isset($rows[$id])){
				$rows[$id] = array(
					number_format($query->getElapsedSecs(), 5, '.', ''),
					1,
					$query->getQuery(),
					$query->getQueryParams()
				);
				$counts[$id] = $query->getElapsedSecs();
			} else {
				$rows[$id][1]++;
				$counts[$id] += $query->getElapsedSecs();
				$rows[$id][0] = number_format($counts[$id], 5, '.', '');
			}
		}
		array_multisort($counts, SORT_NUMERIC, SORT_DESC, $rows);
		$rows = array_values($rows);
		$total_time = round($total_time, 5);
		$total_count = $profiler->getTotalNumQueries();
		$label = "Database Querys ($total_count @ $total_time sec)";
		$table = array($label,
			$rows
		);
		Shopware()->Log()->table($table);
	}

	/**
	 * Log template method
	 */
	public function logTemplate($template)
	{		
		$template_vars = $template->getTemplateVars();
		unset($template_vars['smarty']);
		if(empty($template_vars)) return;
		$rows = array(array('spec', 'value'));
		foreach ($template_vars as $template_spec => $template_var)
		{
			$template_var = $this->encode($template_var);
			$rows[] = array($template_spec, $template_var);
		}
		$table = array('Template Vars ('.(count($template_vars)).')',
			$rows
		);
		Shopware()->Log()->table($table);
		$config_vars = $template->getConfigVars();
		if(!empty($config_vars))
		{
			$rows = array(array('spec', 'value'));
			foreach ($config_vars as $config_spec => $config_var)
			{
				$rows[] = array($config_spec, $config_var);
			}
			$table = array('Config Vars',
				$rows
			);
			Shopware()->Log()->table($table);
		}
	}
	
	/**
	 * Encode data method
	 */
	public function encode($data)
	{
		if(is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->encode($value);
			}
		} elseif(is_string($data)) {
			if(strlen($data)>250) {
				$data = substr($data, 0, 10).'...';
			}
		} elseif($data instanceof ArrayObject) {
			$data = $this->encode($data->getArrayCopy());
		} elseif(is_object($data)) {
			$data = get_class($data);
		}
		return $data;
	}
	
	/**
	 * Log exception method
	 */
	public function logException()
	{
		$response = Shopware()->Front()->Response();
		$exceptions = $response->getException();
		if(!empty($exceptions))
		{
			$rows = array(array('code', 'name', 'message', 'line', 'file', 'trace'));
			foreach ($exceptions as $exception)
			{
				$rows[] = array(
					$exception->getCode(),
					get_class($exception),
					$exception->getMessage(),
					$exception->getLine(),
					$exception->getFile(),
					explode("\n", $exception->getTraceAsString())
				);
			}
			$table = array('Exception Log ('.count($exceptions).')',
				$rows
			);
			Shopware()->Log()->table($table);
			
			foreach ($exceptions as $exception)
			{
				Shopware()->Log()->err((string) $exception);
			}
		}
	}
}