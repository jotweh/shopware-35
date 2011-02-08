<?php
require_once(dirname(__FILE__).'/Application.php');
class Enlight extends Enlight_Application
{
	public function __construct($environment, $options = null)
	{
		Enlight($this);
		parent::__construct($environment, $options);
	}
}

/**
 * Enter description here...
 *
 * @return Enlight
 */

function Enlight($newInstance=null)
{
	static $instance;
	if(isset($newInstance)) {
		$oldInstance = $instance;
		$instance = $newInstance;
		return $oldInstance;
	}
	elseif(!isset($instance)) {
		$instance = Shopware::Instance();
	}
	return $instance;
}