<?php
function smarty_modifier_config($key)
{
	$config = Enlight::Instance()->Bootstrap()->getResource('Config');

	return $config->get($key);
}