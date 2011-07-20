<?php
/**
 * Shopware bootstrap file.
 */

require_once(dirname(__FILE__).'/engine/Shopware/Shopware.php');

$s = new Shopware('production');

return $s->run();
