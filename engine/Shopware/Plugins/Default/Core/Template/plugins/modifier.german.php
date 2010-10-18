<?php
function smarty_modifier_german ($string)
{
	if(!Shopware()->Template()->isTemplateOld())
	{
		throw new Enlight_Exception('Modifier "german" not allowed for new templates');
	}
    $replace = array ("January"=>"Januar","February"=>"Februar","March"=>"März","April"=>"April","May"=>"Mai","June"=>"Juni","July"=>"Juli","August"=>"August","September"=>"September","October"=>"Oktober","November"=>"November","December"=>"Dezember");
    foreach ($replace as $k => $v){

    	$string = str_replace($k,$v,$string);
    }
    return $string;
}