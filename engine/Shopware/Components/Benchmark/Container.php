<?php
class Shopware_Components_Benchmark_Container
{
	protected $Benchmarks;
	
	public function Start($label){
		$object = new Shopware_Components_Benchmark_Point();
		$object->Start($label);
		$this->Benchmarks[] = $object;
		return $object;
	}
	public function getBenchmarks(){
		return $this->Benchmarks;
	}
}