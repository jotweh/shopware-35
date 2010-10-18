<?php
class Shopware_Plugins_Frontend_InputFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public $sql_regex = 's_core|s_order|benchmark.*\(|insert.+into|update.+set|delete.+from|select.+from|drop.+(?:table|database)|truncate.+table|union.+select';
	public $xss_regex = 'javascript:|src\s*=|on[a-z]+\s*=|style\s*=';
	
	public function install()
	{
		$event = $this->createEvent(
			'Enlight_Controller_Front_PreDispatch',
			'onPreDispatch',
			-100
		);
		$this->subscribeEvent($event);
		
		$form = $this->Form();
		
		$form->setElement('text', 'sql_protection', array('label'=>'SQL-Injection-Schutz aktivieren', 'value'=>1));
		$form->setElement('text', 'sql_regex', array('label'=>'SQL-Injection-Filter','value'=>$this->sql_regex));
		$form->setElement('text', 'xss_protection', array('label'=>'XSS-Schutz aktivieren', 'value'=>1));
		$form->setElement('text', 'xss_regex', array('label'=>'XSS-Filter','value'=>$this->xss_regex));
		
		$form->save();
				
		return true;
	}
			
	public static function onPreDispatch(Enlight_Event_EventArgs $args)
	{		
		$request = $args->getSubject()->Request();
		if($request->getModuleName()&&$request->getModuleName()!='frontend'){
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
		if(!empty($config->sql_protection)&&!empty($config->sql_regex)) {
			$regex[] = $config->sql_regex;
		}
		if(!empty($config->xss_protection)&&!empty($config->xss_regex)) {
			$regex[] = $config->xss_regex;
		}
		
		if(empty($regex)) {
			return;
		}
		
		$regex = '#'.implode('|', $regex).'#msi';
	
		$search = array('_REQUEST','_GET','_POST','_COOKIE');
		foreach ($search as $global) {
			if(!empty($GLOBALS[$global])) {
				self::filterValues($regex, $GLOBALS[$global]);
			}
		}
	}
	
	public static function filterValues($regex, &$values)
	{
		foreach ($values as &$value) {
			if (is_string($value)) {
				$value = strip_tags($value);
				if (preg_match($regex, $value, $match)){
					$value = null;
				}
			} elseif (is_array($value)) {
				self::filterValues($value);
			}
		}
	}
}