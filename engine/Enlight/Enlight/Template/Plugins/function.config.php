<?php
function smarty_function_config($params, $smarty, $template)
{
	if(empty($params['name'])||!Shopware()->Bootstrap()->issetResource('Config')) {
		return null;
	}
	return Shopware()->Config()->get($params['name'], isset($params['default']) ? $params['default'] : null);
}