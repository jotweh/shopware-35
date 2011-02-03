<?php
function smarty_modifier_translate ($value = null, $path = null, $locale = null)
{
	if(!Enlight()->Bootstrap()->hasResource('Locale')) return $value;
	
	$locale = Enlight()->Locale();
	if(empty($locale)) return $value;
	if($path=='currency')
	{
		$path = 'nametocurrency';
	}
    return $locale->getTranslation($value, $path, $locale);
}