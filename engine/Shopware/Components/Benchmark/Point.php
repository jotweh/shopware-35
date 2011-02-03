<?php
class Shopware_Components_Benchmark_Point
{
	public $start;
	public $end;
	public $label;
	public $start_ram;
	public $stop_ram;
	public $stopped = false;
	
	public function Start($label){
		$this->label = $label;
		$this->start = microtime(true);
		$this->start_ram = memory_get_peak_usage(true);
		return $this;
	}
	
	public function Stop(){
		$this->stopped = true;
		$this->end = microtime(true);
		$this->stop_ram = memory_get_peak_usage(true);
	}
}