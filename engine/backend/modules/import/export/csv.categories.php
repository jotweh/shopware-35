<?php
if (!defined('sAuthUser')) die();


$categories = $export->sCategories();

if($_REQUEST["formatID"]==1)
{
	header("Content-Type: text/x-comma-separated-values;charset=iso-8859-1");
	header('Content-Disposition: attachment; filename="export.csv"');
	
	$api->convert->csv->sSettings['newline'] = "\r\n";
	echo $api->convert->csv->encode($categories);
}
else
{
	header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
	header("Content-Disposition: attachment; filename=\"export.xls\"");
	require_once(dirname(dirname(__FILE__)).'/excel.php');
	$excel = new Excel();
	$excel->setTitle('Categories Export');
	$excel->addRow(array_keys(reset($categories)));
	$excel->addArray($categories);
	echo $excel->getAll();
}
?>