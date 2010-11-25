<?php
class Shopware_Plugins_Core_Benchmark_Bootstrap extends Shopware_Components_Plugin_Bootstrap implements Enlight_Event_EventSubscriber
{
	protected $results = array();
	protected $start_time = null;
	protected $start_memory = null;
	public $customBenchmark;
	
	public function init()
	{
		if(!Shopware()->Bootstrap()->hasResource('Log')){
			return;
		}
		
		Shopware()->Template()->setDebugging(true);
		Shopware()->Template()->setDebugTemplate('string:');
		Shopware()->Events()->addSubscriber($this);
	}
	
	public function install()
	{		
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
		return true;
	}
	
	
	public static function onInitResourceBenchmark(Enlight_Event_EventArgs $args)
	{
		$instance = Shopware()->Plugins()->Core()->Benchmark();
		$benchmark = new Shopware_Components_Benchmark_Container();
		$instance->customBenchmark = $benchmark;
		return $benchmark;
	}
	
	public static function onStartDispatch(Enlight_Event_EventArgs $args)
    {
    	Shopware()->Plugins()->Core()->Benchmark();
    }
	
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
    
    public function logController()
    {
    	$total_time = $this->formatTime(microtime(true)-$this->start_time);
		$label = "Benchmark Controller ($total_time sec)";
		$table = array($label,
			$this->results
		);
		Shopware()->Log()->table($table);
    }
	
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
    
    public static function formatMemory($size)
    {
    	if(empty($size)) return '0.00 b';
    	$unit=array('b','kb','mb','gb','tb','pb');
    	return @number_format($size/pow(1024,($i=floor(log($size,1024)))),2,'.','').' '.$unit[$i];
    }
    
    public static function formatTime($time)
    {
    	return number_format($time, 5, '.', '');
    }
}