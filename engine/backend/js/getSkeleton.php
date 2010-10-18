<?php

if(empty($_REQUEST['module'])) exit();
$base_dir = dirname(dirname(dirname(__FILE__))).'/';
$module = basename($_REQUEST['module']);

if(file_exists($base_dir.'custom/backend/modules/'.$module.'/skeleton.php'))
{
	chdir($base_dir.'custom/backend/modules/'.$module.'/');
	require($base_dir.'custom/backend/modules/'.$module.'/skeleton.php');
}
elseif(file_exists($base_dir.'backend/modules/'.$module.'/skeleton.php'))
{
	chdir($base_dir.'backend/modules/'.$module.'/');
	require($base_dir.'backend/modules/'.$module.'/skeleton.php');
}
else
{
	header('x', true, 404);
}