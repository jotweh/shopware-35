<?php
class Enlight_Template_BlockManager
{
	protected $template_manager;
	protected $listeners = array();

	public function registerListener(Enlight_Template_BlockHandler $handler)
	{
		$this->listeners[$handler->getName()][] = $handler;
	}
	
	public function removeListener(Enlight_Template_BlockHandler $handler)
	{
		if(!empty($this->listeners[$handler->getName()]))
		foreach ($this->listeners[$handler->getName()] as $i => $callable)
		{
			if ($handler->getListener() === $callable->getListener())
			{
				unset($this->listeners[$handler->getName()][$i]);
			}
		}
	}

	public function hasListeners($block)
	{
		return isset($this->listeners[$block]);
	}
	
	public function getListeners($block)
	{
		if(isset($this->listeners[$block]))
			return $this->listeners[$block];
		else
			return array();
	}
	
	public function loadBlock($block, Enlight_Template_TemplateManager $template_manager)
	{
		if(!$this->hasListeners($block)) return $value;
		$blockArgs = new Enlight_Template_BlockArgs($block);
		$blockArgs->setReturn($value);
		$blockArgs->setName($block);
		$blockArgs->setProcessed(false);
		foreach ($this->getListeners($block) as $listener)
		{
			$template_manager->extendsBlock($block, $listener->execute($blockArgs), $listener->getType());
		}
		$blockArgs->setProcessed(true);
		return $blockArgs->getReturn();
	}
	
	public function addSubscriber(Enlight_Template_BlockSubscriber $subscriber)
	{
		$listeners = $subscriber->getSubscribedBlocks();
		if(!empty($listeners))
		foreach ($listeners as $listener)
		{
			$this->registerListener($listener);
		}
	}
}