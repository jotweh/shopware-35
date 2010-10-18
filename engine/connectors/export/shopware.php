<?php
$location = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://';
$location .= $_SERVER['HTTP_HOST'];
$location .= $_SERVER['REQUEST_URI'];
$location = preg_replace('/\/engine\/connectors\/export\/(.*)\/(.*)\/(.*)/s', '/shopware.php/\\3', $location);

$location = str_replace(".php",".php/backend/export/index",$location);

$location .= '?'.http_build_query($_GET, '', '&');

header('Location: '.$location, true, 301);
?>