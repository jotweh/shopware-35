<?php
require_once(dirname(__FILE__).'/engine/Shopware/Shopware.php');

$s = new Shopware('production');

function shutdown_function()
{
	global $s;
	$last_error = error_get_last();
	if(!empty($last_error)) {
		
		$r = $s->Front()->Response();
		
		$r ->setException(new ErrorException($last_error['message'], 0, $last_error['type'], $last_error['file'], $last_error['line']));
		
		$s->run();
	}
}

ini_set('display_errors', 0);
register_shutdown_function('shutdown_function');

return $s->run();


