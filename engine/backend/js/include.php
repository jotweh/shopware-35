<?php
if(empty($_REQUEST['module'])) {
	exit();
}
$base_path = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://';
$base_path .= $_SERVER['HTTP_HOST'].dirname(dirname(dirname($_SERVER['REQUEST_URI'])));
$base_dir = dirname(dirname(dirname(__FILE__))).'/';
$module = basename($_REQUEST['module']);
$include = empty($_REQUEST['include']) ? 'skeleton.php' : (string) $_REQUEST['include'];
$include = preg_replace('/[^a-z0-9\\/\\\\_.:-]|\.\.+/i', '', $include);
if(file_exists($base_dir.'local_old/modules/'.$module.'/'.$include)) {
	header('Location: '.$base_path.'/local_old/modules/'.$module.'/'.$include, 301);
} elseif(file_exists($base_dir.'backend/modules/'.$module.'/'.$include)) {
	header('Location: '.$base_path.'/backend/modules/'.$module.'/'.$include, 301);
} else {
	header('x', true, 404);
}