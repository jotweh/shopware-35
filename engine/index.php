<?php
define('sAuthFile', 'sGUI');
include("backend/php/check.php");

$path = $_SERVER["HTTPS"] ? "https" : "http";
$path .= "://";
$path .= $sCore->sCONFIG["sBASEPATH"]."/backend/index";

header("location: $path");
exit;
?>