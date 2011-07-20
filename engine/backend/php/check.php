<?php
if(empty($_SESSION)) {
	session_start();
}

unset($DB_USER);
unset($DB_PASSWORD);
unset($DB_HOST);
unset($DB_DATABASE);

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
			return $this->report("ERROR_TIMEOUT");
		}
		
	}
	
	function renew(){
		
		$session = $this->sSession ? $this->sSession  : session_id();
		
		$session = mysql_real_escape_string($session);
		
		$sUsername = htmlspecialchars(mysql_real_escape_string($_SESSION["sUsername"]));
		$sPassword = htmlspecialchars(mysql_real_escape_string($_SESSION["sPassword"]));
		
		$checkUserLoginState = "
			SELECT * FROM s_core_auth WHERE sessionID='$session' AND username='$sUsername' AND password='$sPassword'
			AND lastlogin >= '".date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s')-$this->sTimeout))."'
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
			UPDATE s_core_auth SET lastlogin='".date('Y-m-d H:i:s')."' WHERE sessionID='$session'
		");
		
		return $this->report("SUCCESS");
		
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