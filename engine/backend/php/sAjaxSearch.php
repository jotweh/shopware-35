<?php

$location = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://';
$location .= $_SERVER['HTTP_HOST'];
$location .= $_SERVER['REQUEST_URI'];
$location = str_replace('/engine/core/php/sAjaxSearch.php', '/shopware.php', $location);
$_POST['sViewport'] = 'ajax_search'; $_POST['sAction'] = 'json_search'; unset($_POST['sLanguage']);
$location .= '?'.http_build_query($_POST, '', '&');

header('Location: '.$location, true, 301);