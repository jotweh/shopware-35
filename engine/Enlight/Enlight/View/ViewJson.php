<?php
namespace Enlight\Core\View;
use Enlight\Core\Application as Enlight;
use Enlight\Core\Exception;

class ViewJson extends ViewPattern
{
	protected $_assign = array();
	protected $_options = 0;
	public function init()
    {
    	$front = Enlight::Instance()->Bootstrap()->getRessource('Front');
    	$front->Response()->setHeader('Content-Type', 'application/json', true);
    }
    public function render()
    {
    	return json_encode($this->_assign, $this->_options);
    }
    public function assign($spec, $value = null, $nocache = false, $scope = null)
    {
    	if(isset($value))
			$this->_assign[$spec] = $value;
		else
			unset($this->_assign[$spec]);
	}
	public function clearAssign($spec = null)
	{
		if(isset($spec))
			unset($this->_assign[$spec]);
		else
			$this->_assign = array();
	}
	public function getAssign($spec = null)
	{
		return isset($this->_assign[$spec]) ? $this->_assign[$spec] : null;
	}
}