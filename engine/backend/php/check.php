<?php
if(empty($_SESSION)) {
	session_start();
}

unset($DB_USER);
unset($DB_PASSWORD);
unset($DB_HOST);
unset($DB_DATABASE);

/*error_reporting(E_ALL);
ini_set("display_errors",1);*/
// If not AuthFile spezified, check for GUI-Access
define('sVersion',1);
if (@defined('sConfigPath')){
	
	include(sConfigPath."config.php");
} else {
	if (!@defined('sAuthFile') && !@defined('logout')){
		
		define('sAuthFile', 'sGUI');
		define('login',true);
		include("../../../"."config.php");
	}else {
		
		include("../"."config.php");
	}
}

error_reporting(0);

mysql_pconnect($DB_HOST, $DB_USER, $DB_PASSWORD);
mysql_select_db($DB_DATABASE);
mysql_query("SET NAMES 'latin1'");

// Load-configuration and default functions
include("functions.php");
include("language_de.php");

if (is_file("language_de_custom.php")){
	include("language_de_custom.php");
}

$sCore = new sFunctions;
$sCore->sInitConfig();
$sCore->sLoadHookPoints();
$sCore->sGetLicenseData();
$sCore->sCheckReferer();

/*
Reset API-Key firsttime 
*/
$host = $_SERVER["HTTP_HOST"];

$checkIfApiSet = mysql_query("
SELECT value FROM s_core_config WHERE name='sAPI'
");

if (@mysql_num_rows($checkIfApiSet)){
	$checkhost = mysql_result($checkIfApiSet,0,"value");
	$checkhost = explode("-",$checkhost);
	if (empty($checkhost[0]) || $checkhost[0] != $host){
		$apiBuildString = $_SERVER["HTTP_HOST"]."-".md5("#AXASDASdASDASD".$sCore->sLicenseData["sCore"]);
		$updateAPIKey = mysql_query("
		UPDATE s_core_config SET value='$apiBuildString' WHERE name='sAPI'
		");
	}
}


class	checkLogin
{
	var $sTimeout;
	var $sSession;
	var $_d;
	
	function __construct(){
		global $d;
		$this->_d = $d;
	}
	function checkUser(){
		$this->sTimeout = 60 * 90; // 90 Minuten
		
		if (!session_id()) $this->report("ERROR_SESSION");
		$filename = explode("/",$_SERVER['SCRIPT_NAME']);
		$filelen = count($filename);
		if ($filename[$filelen-3]=="modules"){
			$module = $filename[$filelen-2];
			// Search for id 
			$sql = "
			SELECT id FROM s_core_menu WHERE onclick LIKE '%$module\'%'
			";
			
			$queryId = mysql_query($sql);
			if (@mysql_num_rows($queryId) && isset($_SESSION["sRights"])){
				$id = mysql_result($queryId,0,"id");
				if (!is_array($_SESSION["sRights"])) $_SESSION["sRights"] = array();
				if (!in_array($id,$_SESSION["sRights"]) && $id && !$_SESSION["sAdmin"]){
					die("Insufficient rights");
				}
			}
		}

		if (isset($_SESSION["sUsername"]) && isset($_SESSION["sPassword"]) && !@defined('login')){
			return $this->renew();
		}else {
			$this->login();
		}
		
	}
	
	function renew(){
		
		$session = $this->sSession ? $this->sSession  : session_id();
		
		$session = mysql_real_escape_string($session);
		
		$sUsername = htmlspecialchars(mysql_real_escape_string($_SESSION["sUsername"]));
		$sPassword = htmlspecialchars(mysql_real_escape_string($_SESSION["sPassword"]));
		
		$checkUserLoginState = "
			SELECT * FROM s_core_auth WHERE sessionID='$session' AND username='$sUsername' AND password='$sPassword' AND UNIX_TIMESTAMP(lastlogin)>=(UNIX_TIMESTAMP(now())-".$this->sTimeout.")
		";
		
		
		$checkUserLoginState = mysql_query($checkUserLoginState);
		
		if (!@mysql_num_rows($checkUserLoginState)){
			return $this->report ("ERROR_TIMEOUT");
		}
				
		$user= mysql_fetch_assoc($checkUserLoginState);
		$_SESSION["sName"] = $user["name"];
		$_SESSION["sSidebar"] = $user["sidebar"];
		$_SESSION["sWindow_Width"] = $user["window_width"];
		$_SESSION["sWindow_Height"] = $user["window_height"];
		$_SESSION["sRights"] = unserialize($user["rights"]);
		
		$_SESSION["sAdmin"] = $user["admin"];
		$_SESSION["sWindow_Size"] = unserialize($user["window_size"]);
		
		// Update last-check-time
		$updateUserCheckTime = mysql_query("
			UPDATE s_core_auth SET lastlogin=NOW() WHERE sessionID='$session'
		");
		
		return $this->report("SUCCESS");
		
	}
	
	function login(){
		// Login the user
		if (empty($_POST["sUsername"]) || empty($_POST["sPassword"])) return false;
		$_POST["sUsername"] = htmlspecialchars(mysql_real_escape_string($_POST["sUsername"]));
		
		// Check Salt
		$getSalt = mysql_query("SELECT salted FROM s_core_auth WHERE username = '{$_POST["sUsername"]}'");
		$salt = mysql_result($getSalt,0,"salted");
		if (empty($salt)){

			$_POST["sPassword"] = md5($_POST["sPassword"]);
		}else {
			$_POST["sPassword"] = md5("A9ASD:_AD!_=%a8nx0asssblPlasS$".md5($_POST["sPassword"]));
		}
		$checkUserLoginState = "
			SELECT * FROM s_core_auth WHERE username='{$_POST["sUsername"]}' AND password='{$_POST["sPassword"]}' AND active=1
		";
		
		$checkUserLoginState = mysql_query($checkUserLoginState);
		
		if (!@mysql_num_rows($checkUserLoginState)){
			 return $this->report("ERROR_USER");
		}
		
		$sUserId = mysql_result($checkUserLoginState,0,"id");
		$salted = mysql_result($checkUserLoginState,0,"salted");
		$updateUser = mysql_query("
			UPDATE s_core_auth SET sessionID='".session_id()."', lastlogin=NOW() WHERE id=$sUserId
		");
		$user= mysql_fetch_assoc($checkUserLoginState);
		$_SESSION["sName"] = $user["name"];
		$_SESSION["sID"] = $sUserId;
		$_SESSION["sSidebar"] = $user["sidebar"];
		$_SESSION["sWindow_Width"] = $user["window_width"];
		$_SESSION["sWindow_Height"] = $user["window_height"];
		
		
		$_SESSION["sWindow_Size"] = unserialize($user["window_size"]);
		$_SESSION["sRights"] = unserialize($user["rights"]);
		$_SESSION["sAdmin"] = $user["admin"];
		
		$_SESSION["sUsername"] = $_POST["sUsername"];
		$_SESSION["sPassword"] = $_POST["sPassword"];
		
		$_SESSION["reload"] = 1;
		
		if (empty($salted)){
			$this->report("UNSALTED");
			exit;
		}
		$this->report("SUCCESS");
	}
	
	function logout(){
		// Logout the user
		if (isset($_COOKIE[session_name()])) {
		    setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
		unset($_SESSION);
	}
	
	function report($code){
		if (defined('login')){
			die ($code);	
		}else {
			return $code;
		}
	}	
}

if (@defined('login')){
	$checkLogin = new checkLogin();
	$checkLogin->checkUser();
}
if (@defined('logout')){
	$checkLogin = new checkLogin();
	$checkLogin->logout();
}
?>