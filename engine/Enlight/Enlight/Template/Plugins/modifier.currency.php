<?php
function smarty_modifier_currency($value, $config=null, $position=null)
{
	if(!Enlight()->Bootstrap()->hasResource('Currency')) return $value;
	
	if(empty($config)) $config = array();
	if(is_string($config))
	{
		switch (strtolower($config))
		{
			case 'no_symbol':
				$config = array('display'=>Zend_Currency::NO_SYMBOL);
				break;
			case 'use_symbol':
				$config = array('display'=>Zend_Currency::USE_SYMBOL);
				break;
			case 'use_shortname':
				$config = array('display'=>Zend_Currency::USE_SHORTNAME);
				break;
			case 'use_name':
				$config = array('display'=>Zend_Currency::USE_NAME);
				break;
		}
	}
	if(is_string($position))
	{
		switch (strtolower($position))
		{
			case 'standard':
				$config['position'] = Zend_Currency::STANDARD;
				break;
			case 'right':
				$config['position'] = Zend_Currency::RIGHT;
				break;
			case 'left':
				$config['position'] = Zend_Currency::LEFT;
				break;
		}
	}
	$currency = Enlight()->Currency();
	if(empty($currency)) return $value;
	$value = floatval(str_replace(',', '.', $value));
	$value = $currency->toCurrency($value, $config);
	$value = htmlentities($value, ENT_COMPAT, 'UTF-8', false);
	return $value;
}