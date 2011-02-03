<?php
require_once(dirname(__FILE__).'/Application.php');
class Enlight extends Enlight_Application
{

}

/**
 * Enter description here...
 *
 * @return Enlight
 */
function Enlight()
{
	static $instance;
	if(!isset($instance))
	{
		$instance = Enlight::Instance();
	}
	return $instance;
}