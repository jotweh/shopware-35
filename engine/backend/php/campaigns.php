<?php
$location = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://';
$location .= $_SERVER['HTTP_HOST'];
$location .= $_SERVER['REQUEST_URI'];
$location = str_replace('?', '&', $location);
$location = str_replace('/engine/backend/php/campaigns.php', '/shopware.php?module=backend&controller=newsletter', $location);
$location = str_replace('/engine/core/php/campaigns.php', '/shopware.php?module=backend&controller=newsletter', $location);
header('Location: '.$location, true, 301);