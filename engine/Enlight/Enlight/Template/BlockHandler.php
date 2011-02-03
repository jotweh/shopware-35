<?php
class Enlight_Template_BlockHandler
{
    protected $block;
    protected $listener;
    protected $position;
    protected $plugin;
    
    const TypeAppend = 'append';
    const TypePrepend = 'prepend';
    const TypeReplace = 'replace';
	
	public function __construct ($block, $listener, $type=self::TypeAppend, $position=0, $plugin=0)
	{
		if(empty($block)||empty($listener))
		{
			throw new Enlight_Exception('Some parameters are empty');
		}
		if(!is_callable($listener, true, $listener_name))
		{
			throw new Enlight_Exception('Listener "'.$listener_name.'" is not callable');
		}
		$this->name = $block;
		$this->listener = $listener;
		$this->setType($type);
		$this->setPosition($position);
		$this->setPlugin($plugin);
	}
	
	public function setType($type)
	{
		if(!in_array($type, array(
			self::TypeReplace,
			self::TypePrepend,
			self::TypeAppend
		)))
		{
			throw new Enlight_Exception('Block type is unknown');
		}
		$this->type = $type;
		return $this;
	}
	
	public function setPosition($position)
	{
		if(!is_numeric($position))
		{
			throw new Enlight_Exception('Position is not numeric');
		}
		$this->position = $position;
		return $this;
	}
	
	public function setPlugin($plugin)
	{
		$this->plugin = $plugin;
		return $this;
	}
 
	public function getName()
	{
		return $this->name;
	}
	
	public function getListener()
	{
		return $this->listener;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getPosition()
	{
		return $this->position;
	}
	
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	public function execute($args=null)
	{
		return call_user_func($this->listener, $args);
	}
}