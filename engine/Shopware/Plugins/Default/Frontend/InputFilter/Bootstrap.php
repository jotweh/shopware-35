<?php
/**
 * Shopware InputFilter Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_InputFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public $sql_regex = 's_core_|s_order_|benchmark.*\(|insert.+into|update.+set|(?:delete|select).+from|drop.+(?:table|database)|truncate.+table|union.+select';
	public $xss_regex = 'javascript:|src\s*=|on[a-z]+\s*=|style\s*=';
	public $rfi_regex = '\.\./|\\0|2\.2250738585072011e-308';
	
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Front_RouteStartup',
			'onRouteStartup',
			-100
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('text', 'sql_protection', array('label'=>'SQL-Injection-Schutz aktivieren', 'value'=>1));
		$form->setElement('text', 'sql_regex', array('label'=>'SQL-Injection-Filter', 'value'=>$this->sql_regex));
		$form->setElement('text', 'xss_protection', array('label'=>'XSS-Schutz aktivieren', 'value'=>1));
		$form->setElement('text', 'xss_regex', array('label'=>'XSS-Filter', 'value'=>$this->xss_regex));
		$form->setElement('text', 'rfi_protection', array('label'=>'RemoteFileInclusion-Schutz aktivieren', 'value'=>1));
		$form->setElement('text', 'rfi_regex', array('label'=>'RemoteFileInclusion-Filter', 'value'=>$this->rfi_regex));
		
		$form->save();
				
		return true;
	}
	
	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPreDispatch(Enlight_Event_EventArgs $args)
	{
		
	}

	/**
	 * Event listener method
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onRouteStartup(Enlight_Event_EventArgs $args)
	{		
		$request = $args->getSubject()->Request();
		if($request->getModuleName() && $request->getModuleName()!='frontend') {
			return;
		}
		
		$intVals = array('sCategory', 'sContent', 'sCustom');
		foreach ($intVals as $parameter) {
			if (!empty($_GET[$parameter])){
				$_GET[$parameter] = (int) $_GET[$parameter];
			}
			if (!empty($_POST[$parameter])){
				$_POST[$parameter] = (int) $_POST[$parameter];
			}
		}
		
		$config = Shopware()->Plugins()->Frontend()->InputFilter()->Config();
						
		$regex = array();
		if(!empty($config->sql_protection) && !empty($config->sql_regex)) {
			$regex[] = $config->sql_regex;
		}
		if(!empty($config->xss_protection) && !empty($config->xss_regex)) {
			$regex[] = $config->xss_regex;
		}
		if(!empty($config->rfi_protection) && !empty($config->rfi_regex)) {
			$regex[] = $config->rfi_regex;
		}
		
		if(empty($regex)) {
			return;
		}
		
		$regex = '#' . implode('|', $regex) . '#msi';
		
		$process = array(
			&$_GET, &$_POST, &$_COOKIE, &$_REQUEST, &$_SERVER
		);
		while (list($key, $val) = each($process)) {
			foreach ($val as $k => $v) {
				unset($process[$key][$k]);
				if (is_array($v)) {
					$process[$key][self::filterValue($k, $regex)] = $v;
					$process[] = &$process[$key][self::filterValue($k, $regex)];
				} else {
					$process[$key][self::filterValue($k, $regex)] = self::filterValue($v, $regex);
				}
			}
		}
		unset($process);
	}
	
	/**
	 * Filter value by regex
	 *
	 * @param string $value
	 * @param string $regex
	 * @return string
	 */
	public static function filterValue($value, $regex)
	{
		if(!empty($value)) {
			$value = strip_tags($value);
			if (preg_match($regex, $value)) {
				$value = null;
			}
		}
		return $value;
	}
}