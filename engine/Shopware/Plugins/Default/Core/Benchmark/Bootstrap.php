<?php
/**
 * Benchmark Plugin
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Core_Benchmark_Bootstrap extends Shopware_Components_Plugin_Bootstrap implements Enlight_Event_EventSubscriber
{
	protected $results = array();
	protected $start_time = null;
	protected $start_memory = null;
	public $customBenchmark;

	/**
	 * Activate template debugging
	 * @return 
	 */
	public function init()
	{
		if(!Shopware()->Bootstrap()->hasResource('Log')){
			return;
		}
		
		Shopware()->Template()->setDebugging(true);
		Shopware()->Template()->setDebugTemplate('string:');
		Shopware()->Events()->addSubscriber($this);
	}

	/**
	 * Handler for backend sql-monitor controller
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return string
	 */
	public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
		return dirname(__FILE__).'/SqlMonitor.php';
    }

	/**
	 * Install benchmark plugin
	 * @return bool
	 */
	public function install()
	{
		$form = $this->Form();
		$form->setElement('checkbox', 'sqlMonitor', array('label'=>'SQL-Monitor aktivieren', 'value'=>1));
		$form->save();

		$event = $this->createEvent(
	 		'Enlight_Controller_Dispatcher_ControllerPath_Backend_SqlMonitor',
	 		'onGetControllerPath'
	 	);
	 	$this->subscribeEvent($event);
		
	 	$parent = $this->Menu()->findOneBy('label', 'Einstellungen');
		$item = $this->createMenuItem(array(
			'label' => 'Sql-Monitor',
			'onclick' => 'openAction(\'SqlMonitor\');',
			'class' => 'ico2 monitor',
			'active' => 1,
			'parent' => $parent,
			'style' => 'background-position: 5px 5px;'
		));
		$this->Menu()->addItem($item);
		$this->Menu()->save();
		
		$event = $this->createEvent(
			'Enlight_Controller_Front_StartDispatch',
			'onStartDispatch'
		);
		$this->subscribeEvent($event);
		$event = $this->createEvent(
			'Enlight_Bootstrap_InitResource_Benchmark',
			'onInitResourceBenchmark'
		);
		$this->subscribeEvent($event);

		$event = $this->createEvent(
	 		'Enlight_Controller_Front_DispatchLoopShutdown',
	 		'onDispatchLoopShutdown'
	 	);
		$this->subscribeEvent($event);
		return true;
	}
	
	/**
	 * Define Benchmark resource for custom benchmarks
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return Shopware_Components_Benchmark_Container
	 */
	public static function onInitResourceBenchmark(Enlight_Event_EventArgs $args)
	{
		$instance = Shopware()->Plugins()->Core()->Benchmark();
		$benchmark = new Shopware_Components_Benchmark_Container();
		$instance->customBenchmark = $benchmark;
		return $benchmark;
	}

	/**
	 * On Dispatch start activate db profiling
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return void
	 */
	public static function onStartDispatch(Enlight_Event_EventArgs $args)
    {
    	Shopware()->Plugins()->Core()->Benchmark();
		Shopware()->Db()->getProfiler()->setEnabled(true);
    }

	/**
	 * On Dispatch Shutdown collect sql performance results and dump to log component
	 * @static
	 * @param Enlight_Event_EventArgs $args
	 * @return
	 */
	public static function onDispatchLoopShutdown(Enlight_Event_EventArgs $args){
		if(!Shopware()->Bootstrap()->hasResource('Log')){
			return;
		}
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
	 * Benchmark Controllers
	 * @param Enlight_Event_EventArgs $args
	 * @return void
	 */
	public function onBenchmarkEvent(Enlight_Event_EventArgs $args)
    {
    	
    	if(empty($this->results))
    	{
    		$this->results[] = array('name', 'memory', 'time');
    		$this->start_time = microtime(true);
			$this->start_memory = memory_get_peak_usage(true);
    	}
    	
    	$this->results[] = array(
    		0 => str_replace('Enlight_Controller_', '', $args->getName()),
    		1 => $this->formatMemory(memory_get_peak_usage(true)-$this->start_memory),
    		2 => $this->formatTime(microtime(true)-$this->start_time)
    	);
    	
    	if($args->getName()=='Enlight_Controller_Front_DispatchLoopShutdown')
    	{
    		$this->logTemplate();
    		$this->logController();
    		$this->logCustom();
    	}
    }

	/**
	 * Custom-Log function 
	 * @return
	 */
    public function logCustom(){
    	$results = array();
    	if (empty($this->customBenchmark)) return;
    	$benchmarks = $this->customBenchmark->getBenchmarks();
    	$results[] = array('name', 'memory', 'time');
		if (!empty($benchmarks)){
    		foreach ($benchmarks as $bench){
	    		if (($bench->stopped==true)){
	    			$totalRam += $bench->stop_ram-$bench->start_ram;
	    			$totalTime += $bench->end-$bench->start;
	    			$results[] = array(
			    		0 => $bench->label,
			    		1 => $this->formatMemory($bench->stop_ram-$bench->start_ram),
			    		2 => $this->formatTime($bench->end-$bench->start)
			    	);
	    		}
    		}
    		$results[] = array(
	    		0 => "Total",
	    		1 => $this->formatMemory($totalRam),
	    		2 => $this->formatTime($totalTime)
	    	);
	    	Shopware()->Log()->table(array("Benchmark Custom",$results));
		}
    }

	/**
	 * Log template compile and render times
	 * @return void
	 */
    public function logTemplate()
    {
    	$rows = array(array('name', 'compile_time', 'render_time', 'cache_time'));
		$total_time = 0;
		foreach (Smarty_Internal_Debug::$template_data as $template_file)
		{
			//$total_time += $template_file['compile_time'];
			$total_time += $template_file['render_time'];
			$total_time += $template_file['cache_time'];
			$template_file['name'] = str_replace(Shopware()->CorePath(), '', $template_file['name']);
			$template_file['name'] = str_replace(Shopware()->AppPath(), '', $template_file['name']);
			$template_file['name'] = str_replace(Shopware()->OldPath(), '', $template_file['name']);
			$template_file['compile_time'] = $this->formatTime($template_file['compile_time']);
			$template_file['render_time'] = $this->formatTime($template_file['render_time']);
			$template_file['cache_time'] = $this->formatTime($template_file['cache_time']);
			unset($template_file['start_time']);
			$rows[] = array_values($template_file);
		}
		$total_time = round($total_time, 5);
		$total_count = count($rows)-1;
		$label = "Benchmark Template ($total_count @ $total_time sec)";
		$table = array($label,
			$rows
		);
		Shopware()->Log()->table($table);
    }

	/**
	 * Get total execution time in controller
	 * @return void
	 */
    public function logController()
    {
    	$total_time = $this->formatTime(microtime(true)-$this->start_time);
		$label = "Benchmark Controller ($total_time sec)";
		$table = array($label,
			$this->results
		);
		Shopware()->Log()->table($table);
    }

	/**
	 * Monitor execution time and memory on specified event points in application
	 * @return array
	 */
	public function getSubscribedEvents()
    {
    	$events = array(
    		'Enlight_Controller_Front_RouteStartup',
			'Enlight_Controller_Front_RouteShutdown',
			'Enlight_Controller_Front_DispatchLoopStartup',
			'Enlight_Controller_Front_PreDispatch',
			'Enlight_Controller_Front_PostDispatch',
			'Enlight_Controller_Front_DispatchLoopShutdown',
			
			'Enlight_Controller_Action_Init',
			'Enlight_Controller_Action_PreDispatch',
			'Enlight_Controller_Action_PostDispatch',
			
			'Enlight_Plugins_ViewRenderer_PreRender',
			'Enlight_Plugins_ViewRenderer_PostRender'
    	);
    	
    	$event_handlers = array();
    	
    	foreach ($events as $event)
    	{
    		$event_handlers[] = new Enlight_Event_EventHandler($event, array($this, 'onBenchmarkEvent'), -99);
    	}
    	return $event_handlers;
    }

	/**
	 * Format memory in a proper way
	 * @static
	 * @param  $size
	 * @return string
	 */
    public static function formatMemory($size)
    {
    	if(empty($size)) return '0.00 b';
    	$unit=array('b','kb','mb','gb','tb','pb');
    	return @number_format($size/pow(1024,($i=floor(log($size,1024)))),2,'.','').' '.$unit[$i];
    }

	/**
	 * Format time for human readable
	 * @static
	 * @param  $time
	 * @return string
	 */
    public static function formatTime($time)
    {
    	return number_format($time, 5, '.', '');
    }
}