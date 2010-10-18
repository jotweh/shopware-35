<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	echo "FAIL";
	die();
}?>
<?php

class sSystemCheck
{
	static public $sConfig;
	static public $sBrowser;
	static public $sVar;
	static public $sDB;
	
	static public  $sImage = array(
		"accept" => "89504e470d0a1a0a0000000d49484452000000100000001008060000001ff3ff610000000467414d410000afc837058ae90000001974455874536f6674776172650041646f626520496d616765526561647971c9653c0000029f4944415438cba593eb4b53611cc7fd3b76ce766c03096444218284507b1524123b9a0dc5bc2c4bd3ddbdd23663d84c8b32725ec648292db3d46a734e6dead4e6d6943271d95142ccbde942b56174b16fe7ecc54c12237ae00b0f0fcfe7f37bae7100e2fe277f0c14f80e8972a70feab33d294cc6e3fd61e988382c7525325267a23eddbe57b4ab20cf9b4acb3cc9219d47869b410bfa96bba2e95c6c4689eb1824bd5448d243d13b0ad8aa74e67852e4ea9c11ced57ed85e5e8369ae321aaeef58ed43e34c150ed8f891641b9fde26c8f1a40833dd49eb1ccc4dac0994a1c25f02cdcc19e8d894b3a9f69d85fdf53d5c9caa80d842ae8baf93c29840e64e32a8c6b2a29539980355de53504c1742315500f55421dc6b8330789570b092dc9e2348b8421a62828c1131d3196c8635d8840a5f31944fe42c980fe5241b4f3ec6dfb8c0b550640d6dcf1b619b6b447c3dc9c40447ed091b0f566ea336a081d65bc4ae64008b1fe6a19a28c4d8da5014feb1f91d6dcf2ec130518cbe6027044662232690dca736fa97bb61f4ab50ebd7e2d3d78f51e8fd97b731d8325b0fb52b1b7af769f4073b20a8f95dd0433137e69bd0be7019ba4939ccbe6a44be7d8ec29b3f3761797a0165ce2c6858414ba00eed5e33a82a626b0ba9b70406f9401aec2b77513d2187da7d1275d3e5587ab780b6d906940e1e87c271023a670eecafba916e3d0caa92d83ac4642b5fb88fbd1ab3478387cc1d548ee6413b9c03d5900c4a07170ecec6a3a52e18878a2150f3d6293521dcf690129b483abe818898dc6570b092d68019fad1229c1b96a395dd829d85f5ce22f04b791116a6777cca7b1a489a321121698704ed7e337a5f58d13b6f85c56b425a4b2a040a5e885211f4ae9f893a4f8a2803a1a76a0846a0e585052a5e98adc8b0d153a584e8afbff15ff30b67d0ace579bad4630000000049454e44ae426082",
		"delete" => "89504e470d0a1a0a0000000d49484452000000100000001008060000001ff3ff610000000467414d410000afc837058ae90000001974455874536f6674776172650041646f626520496d616765526561647971c9653c0000025d4944415438cba593fb4b536118c7fd5bb61fa204896e8484515098f33277d6dadca6ceb34c8f5b2c62698e9d619a418dc55cf843a8a576d5ca5f324d6c945a466ad7a1e5b1f2d24e4d6a6d9eb36bcab7b960262e237ae1fbcbcbfbf9bc3c0fcf930220e57fb2e66256af17ce5015e6f7541933a525b949ad867b47aa997152697695c885eb0ae6747ae22345b1df1c3684079d88bc1945646408fcdd0ecc1a4af1aa90609fabc54452c1323c5d4ef10bb73bb034f516d1be3b88b6db116d3e871f379bb038dc078fc980516536ff4c9e49ac12cce8748218ece6aeb7638971217af10c7c760bfcb66a842f581069a011b69e40f8de0dcc1d25317c689ffbb16c8f202188c1f47cad198baeb1f88f21070def59238275ba55e14e9208775dc1cbbc0c0c4833e884e003758409745d45b4b3193e6b25be16499366a192046fab81c7528587443a931030e5da60e47e17a20e0b7ca70df8d3f12872e0a50ae16f6e84336f47302198248b82a1ce5684eb0d089e2a5b57305faa82bfa911fde22d2b82891215e3b5d62064a7c11d57c71f268d320fbe5a13664d46f489d2564a7015cb69462642b0b30d3ead0cde52c91a989567832d9020d07e0943e26de8cddab4d2c4d74552c10b0de19eabd02070ab35deb07995f817a8c8052b8bc13231f8b6168c174b976177b768a360d5208d15e410238a03fcf4611502d75af1bd9e868754e2b35a016f9d19fce5164ca8f3d19395cac76022e9283f95ef279e1cdccb8e4a76e14bb511de86f3f17caa3a8647395b9761f67738e9320d4a770b07c4e96627b1937990bf9deb176de67a73d3989eec547377e606e15fb7f15ff3131dd2ceb949721bfe0000000049454e44ae426082"
	);
	static public $sRequired = array(
		"libxml" => 		array("required_enable"=>true,"required_version"=>"2.6.26"),
		"pdo_mysql" => 		array("required_enable"=>true,"required_version"=>"1"),
		"pdo" => 			array("required_enable"=>true,"required_version"=>"1"),
		"gd" => 			array("required_enable"=>true,"required_version"=>"2.0.0"),
		"session" => 		array("required_enable"=>true,"required_version"=>"1"),
		//"sockets" => 		array("required_enable"=>true,"required_version"=>"1"),
		"curl" => 			array("required_enable"=>true,"required_version"=>"2.0.0"),
		"php" => 			array("required_enable"=>true,"required_version"=>"5.2.5"),
		"apache" => 		array("required_enable"=>true,"required_version"=>"2.0.0"),
		"mysql" => 			array("required_enable"=>true,"required_version"=>"5.0.0"),
		"mod_rewrite" => 	array("required_enable"=>true,"required_version"=>"1"),
		"mb_string" => 		array("required_enable"=>true,"required_version"=>"1")
	);
	static public $sRequiredOptions = array(
		"memory_limit" => 	array("required_enable"=>true,"required_version"=>"128M"),
		"magic_quotes" => 	array("required_enable"=>true,"required_version"=>"0"),
		"allow_url_fopen" =>array("required_enable"=>true,"required_version"=>"1"),
		"disk_free_space" =>array("required_enable"=>true,"required_version"=>"1 GB"),
	);
	static public $sRequiredBrowser = array(
		"browser" => 		array("required_enable"=>true,"required_version"=>array("Firefox"=>"3.0","Safari"=>"3.0")),
		"flash" => 			array("required_enable"=>true,"required_version"=>"10"),
		"cookie" => 		array("required_enable"=>true,"required_version"=>"1")
	);
	static public $sDir;
	static public $sDirs = array (
		'engine/Shopware/Proxies/' 		=> array(),
		'engine/Shopware/Plugins/Community/' => array(),
		'cache/database/' 				=> array(),
		'uploads/' 						=> array(),
		'engine/connectors/api/tmp'		=> array(),
		'cache/templates/' 				=> array(),
		'files/cms/'				 	=> array(),
		'files/documents/' 				=> array(),
		'files/downloads/' 				=> array(),
		'images/articles/' 				=> array(),
		'images/banner/' 				=> array(),
		'images/cms/' 					=> array(),
		'images/supplier/' 				=> array(),
		'engine/vendor/html2ps/temp/' 	=> array(),
		'engine/vendor/html2ps/cache/'	=> array(),
		'engine/Enlight/Vendor/mpdf/tmp/' => array(),
		'engine/Enlight/Vendor/mpdf/ttfontdata/' => array()
	);
	function sGetImage($img)
	{
		if(isset(self::$sImage[$img]))
		{
			header('Content-type: image/png');
			echo pack("H*", (self::$sImage[$img]));
		}
		exit();
	}
	function sPrintTables()
	{
		self::$sDir = realpath(dirname (__FILE__))."/";
		if(!file_exists(self::$sDir."config.php"))
		{
			self::$sDir = realpath(dirname (__FILE__)."/../../../../")."/";
		}
		self::sDatabaseConnect();
		self::sGetConfig();
		self::sVersionCheck();
		self::sOptionCheck();
		self::sBrowserCheck();
		self::sDirCheck();
?>
<fieldset style="width:300px; float:left; margin-right:20px; margin-bottom: 35px; margin-top: -18px;">
<legend>Server-Konfiguration</legend>
<table class="systemcheck">
<thead>
 <tr>
  <th>Name</th>
  <td>Ben&ouml;tigt</td>
  <td>Vorhanden</td>
  <td class="status">Status</td>
 </tr>
</thead>
<tbody>
<?php
self::sPrintModuleTable();
?>
</tbody>
</table>
</fieldset>
<fieldset style="width:300px; float:left; margin-right: 20px; margin-bottom: 35px; margin-top: -18px;">
<legend>Server-Verzeichnisrechte</legend>
<table class="systemcheck">
<thead>
 <tr>
  <th>Verzeichnis</th>
  <td>Status</td>
 </tr>
</thead>
<tbody>
<?php
self::sPrintDirTable();
?>
</tbody>
</table>
</fieldset>
<fieldset style="width:300px; float:left; margin-top: -18px;">
<legend>Ihr Browser</legend>
<table class="systemcheck">
<thead>
 <tr>
  <th>Funktion</th>
  <td>Ben&ouml;tigt</td>
  <td>Vorhanden</td>
  <td>Status</td>
 </tr>
</thead>
<tbody class="systemcheck">
<?php /*
 <tr>
  <th>Browser</th>
  <th>Firefox <?php echo self::$sRequiredBrowser['browser']['required_version']['Firefox']?></th>
  <th <?php if(empty(self::$sBrowser['passed'])) echo "class=\"red\"";?>><?php if(!empty(self::$sBrowser['version'])){?><?php echo self::$sBrowser['name']?> <?php echo self::$sBrowser['version']?><?php }?></th>
  <td class="status"><?php if(!empty(self::$sBrowser['status'])) echo self::$sBrowser['status']; elseif (!empty(self::$sBrowser['version'])) echo "<img src=\"".basename(__FILE__)."?img=\"/>"; else echo "<img src=\"".basename(__FILE__)."?img=delete\"/>"; ?></td>
 </tr>
 */?>
 <tr>
  <th>Javascript</th>
  <th></th>
  <th></th>
  <td class="status" id="script"><img src="<?php echo basename(__FILE__);?>?img=delete"></td>
 </tr>
 <tr>
  <th>Flash</th>
  <th><?php echo self::$sRequiredBrowser['flash']['required_version']?></th>
  <th id="flash_version"></th>
  <td class="status" id="flash">not found</td>
 </tr>
 <tr>
  <th>Cookies</th>
  <th></th>
  <th></th>
  <td class="status" id="cookie"><?php if(isset($_COOKIE)){?><img src="<?php echo basename(__FILE__);?>?img=accept"><?php }else{?><img src="<?php echo basename(__FILE__);?>?img=delete"><?php }?></td>
 </tr>
</tbody>
</table>
</fieldset>
<script language="javascript" type="text/javascript">
	function refresh ()
	{
		if(window.navigator.cookieEnabled==true)
		{
			document.getElementById("cookie").innerHTML = '<img src="<?php echo basename(__FILE__);?>?img=accept">';
		}
		else
		{
			document.getElementById("cookie").innerHTML = '<img src="<?php echo basename(__FILE__);?>?img=delete">';
		}
		var latestFlashVersion = 10;
		if (navigator.plugins != null && navigator.plugins.length > 0) {
			var flashPlugin = navigator.plugins['Shockwave Flash'];
			if (typeof flashPlugin == 'object')	{
				for (var i = latestFlashVersion; i >= 3; i--) {
					if (flashPlugin.description.indexOf(i + '.') != -1) {
						if(i>=<?php echo self::$sRequiredBrowser['flash']['required_version']?>) {
							document.getElementById("flash").innerHTML = '<img src="<?php echo basename(__FILE__);?>?img=accept">';
						}
						document.getElementById("flash_version").innerHTML = i;
						break;
					}
				}
			}
		}
		document.getElementById("script").innerHTML = '<img src="<?php echo basename(__FILE__);?>?img=accept">';
	}
	refresh();
</script>
<?php
	}
	function sOptionCheck ()
	{
		self::$sVar = array_merge_recursive(self::$sVar,self::$sRequiredOptions);
		if ((function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc())||(ini_get('magic_quotes_sybase')&&(strtolower(ini_get('magic_quotes_sybase'))!="off")))
		{
		    self::$sVar['magic_quotes']['version'] = "1";
		    self::$sVar['magic_quotes']['passed'] = false;
		}
		else 
		{
			self::$sVar['magic_quotes']['passed'] = true;
			self::$sVar['magic_quotes']['version'] = "0";
		}
		self::$sVar['magic_quotes']['enable'] = true;
		self::$sVar['magic_quotes']['installed'] = true;
		
		if (!function_exists("ini_get"))
		{
		    self::$sVar['memory_limit']['version'] = "?";
		    self::$sVar['memory_limit']['passed'] = false;
		}
		else 
		{
			self::$sVar['memory_limit']['version'] = ini_get("memory_limit");
			if(self::sDecodePhpSize(self::$sVar['memory_limit']['version'])<self::sDecodePhpSize(self::$sVar['memory_limit']['required_version']))
				self::$sVar['memory_limit']['passed'] = false;
			else 
				self::$sVar['memory_limit']['passed'] = true;
		}
		self::$sVar['memory_limit']['enable'] = true;
		self::$sVar['memory_limit']['installed'] = true;
		
		if (!function_exists("ini_get"))
		{
		    self::$sVar['allow_url_fopen']['version'] = "?";
		    self::$sVar['allow_url_fopen']['passed'] = false;
		}
		else 
		{
			self::$sVar['allow_url_fopen']['version'] = strtolower(ini_get("allow_url_fopen"));
			if(self::$sVar['allow_url_fopen']['version']!="1"&&self::$sVar['allow_url_fopen']['version']!="On")
				self::$sVar['allow_url_fopen']['passed'] = false;
			else 
				self::$sVar['allow_url_fopen']['passed'] = true;
		}
		self::$sVar['allow_url_fopen']['enable'] = true;
		self::$sVar['allow_url_fopen']['installed'] = true;
				
		if (!function_exists("disk_free_space"))
		{
		    self::$sVar['disk_free_space']['version'] = "?";
		    self::$sVar['disk_free_space']['passed'] = false;
		}
		else 
		{
			self::$sVar['disk_free_space']['version'] = disk_free_space(dirname(__FILE__));
			if(self::$sVar['disk_free_space']['version']<self::sDecodeSize(self::$sVar['disk_free_space']['version']))
				self::$sVar['disk_free_space']['passed'] = false;
			else 
				self::$sVar['disk_free_space']['passed'] = true;
			self::$sVar['disk_free_space']['version'] = self::sEncodeSize(self::$sVar['disk_free_space']['version']);
		}
		self::$sVar['disk_free_space']['enable'] = true;
		self::$sVar['disk_free_space']['installed'] = true;
		

		
	}
	function sDatabaseCheck ()
	{
		if (!self::$sDB) {
		    self::$sVar['mysql']['status'] = "not connect";
		    return false;
		}
		$version = mysql_get_server_info(self::$sDB);
		if (strpos($version, "-") !== false)
			$version = substr($version, 0, strpos($version, "-"));
		if(!empty($version))
		{
			self::$sVar['mysql']['version'] = $version;
			self::$sVar['mysql']['enable'] = true;
			self::$sVar['mysql']['installed'] = true;
		}
	}
	function sDatabaseConnect ()
	{
		if(!file_exists(self::$sDir.'config.php'))
			return false;
		include(self::$sDir.'config.php');
		self::$sDB = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD );
		if (!self::$sDB) {
		    return false;
		}
		if (!mysql_select_db($DB_DATABASE, self::$sDB)) {
			return false;
		}
		return true;
	}
	function sVersionCheck ()
	{
		/* Mysql */
		if (!self::$sDB) {
		    self::$sVar['mysql']['status'] = "not connect";
		}
		else 
		{
			$version = mysql_get_server_info(self::$sDB);
			if (strpos($version, "-") !== false)
				$version = substr($version, 0, strpos($version, "-"));
			if(!empty($version))
			{
				self::$sVar['mysql']['version'] = $version;
				self::$sVar['mysql']['enable'] = true;
				self::$sVar['mysql']['installed'] = true;
			}
		}
		/* LibXML */
		if(!empty(self::$sConfig['libXML support'])||!empty(self::$sConfig['DOM/XML']))
		{
			if (!empty(self::$sConfig['DOM/XML'])&&empty(self::$sConfig['libXML support'])) if(self::$sConfig['DOM/XML']=="enabled"){
				self::$sConfig['libXML support'] = "active";
			}
			if (!empty(self::$sConfig['libxml Version'])&&empty(self::$sConfig['libXML Version'])){
				if(strlen(self::$sConfig['libxml Version'])%2==1)
					self::$sConfig['libxml Version'] = self::$sConfig['libxml Version'];
				self::$sConfig['libXML Version'] = wordwrap(self::$sConfig['libxml Version'],2,".",true);
			}	
			self::$sVar['libxml']['installed'] = true;
			if(self::$sConfig['libXML support'] == "active")
				self::$sVar['libxml']['enable'] = true;
			else 
				self::$sVar['libxml']['enable'] = false;
			if(!empty(self::$sConfig['libXML Version']))
			{
				self::$sVar['libxml']['version'] = self::$sConfig['libXML Version'];
			}
		}
		else 
		{
			self::$sVar['libxml']['installed'] = false;
		}
		/* GD */
		if(!empty(self::$sConfig['GD Support']))
		{
			self::$sVar['gd']['installed'] = true;
			if(self::$sConfig['GD Support'] == "enabled")
				self::$sVar['gd']['enable'] = true;
			else 
				self::$sVar['gd']['enable'] = false;
			if(!empty(self::$sConfig['GD Version']))
			{
				$reg = "#[0-9.]+#";
				if(preg_match($reg,self::$sConfig['GD Version'],$treffer))
				{
					if(substr_count($treffer[0],".")==1)
						$treffer[0] .=".0";
					self::$sVar['gd']['version'] = $treffer[0];
				}
				else 
				{
					self::$sVar['gd']['version'] = self::$sConfig['GD Version'];
				}
			}
		}
		else 
		{
			self::$sVar['gd']['installed'] = false;
		}
				

   	 	/* Session */
   	 	if(!empty(self::$sConfig['Session Support']))
		{
			self::$sVar['session']['installed'] = true;
			if(self::$sConfig['Session Support'] == "enabled") {
				self::$sVar['session']['enable'] = true;
				self::$sVar['session']['version'] = 1;
			} else {
				self::$sVar['session']['enable'] = false;
				self::$sVar['session']['version'] = "0";
			}
		}
		else 
		{
			self::$sVar['session']['installed'] = false;
		}
   	 	/* Sockets */
   	 	/*
   	 	if(!empty(self::$sConfig['Sockets Support']))
		{
			self::$sVar['sockets']['installed'] = true;
			if(self::$sConfig['Sockets Support'] == "enabled") {
				self::$sVar['sockets']['enable'] = true;
				self::$sVar['sockets']['version'] = 1;
			} else {
				self::$sVar['sockets']['enable'] = false;
				self::$sVar['sockets']['version'] = "0";
			}
		}
		else 
		{
			self::$sVar['sockets']['installed'] = false;
		}
		*/
   	 	/* cURL */
		if(!empty(self::$sConfig['cURL support'])||!empty(self::$sConfig['CURL support']))
		{
			if(empty(self::$sConfig['cURL support']))
				self::$sConfig['cURL support'] = self::$sConfig['CURL support'];
			self::$sVar['curl']['installed'] = true;
			if(self::$sConfig['cURL support'] == "enabled")
				self::$sVar['curl']['enable'] = true;
			else 
				self::$sVar['curl']['enable'] = false;
			if(!empty(self::$sConfig['cURL']['version']))
			{
				self::$sVar['curl']['version'] = self::$sConfig['cURL']['version'];
			}
		}
		else 
		{
			self::$sVar['curl']['installed'] = false;
		}
		
		/* pdo */
		if(!empty(self::$sConfig['MODULES']['PDO']))
		{
			self::$sVar['pdo']['version'] = 1;
			self::$sVar['pdo']['installed'] = true;
			if(self::$sConfig['MODULES']['PDO']['PDO support'] == "enabled") {
				self::$sVar['pdo']['enable'] = true;
			} else {
				self::$sVar['pdo']['enable'] = false;
			}
			self::$sVar['pdo_mysql']['version'] = 1;
			if(strpos(self::$sConfig['MODULES']['PDO']['PDO drivers'], 'mysql')!==false) {
				self::$sVar['pdo_mysql']['installed'] = true;
				self::$sVar['pdo_mysql']['enable'] = true;
			} else {
				self::$sVar['pdo_mysql']['installed'] = false;
				self::$sVar['pdo_mysql']['enable'] = false;
			}
		} else {
			self::$sVar['pdo_mysql']['installed'] = false;
			self::$sVar['pdo']['installed'] = false;
		}
		
		/* mb_string */
		if(!empty(self::$sConfig['Multibyte Support']))
		{
			self::$sVar['mb_string']['version'] = 1;
			self::$sVar['mb_string']['installed'] = true;
			if(self::$sConfig['Multibyte Support'] == "enabled") {
				self::$sVar['mb_string']['enable'] = true;
			} else {
				self::$sVar['mb_string']['enable'] = false;
			}
		} else {
			self::$sVar['mb_string']['installed'] = false;
		}

		/* PHP */
		self::$sVar['php']['version'] = self::$sConfig['PHP Version'];
		self::$sVar['php']['enable'] = true;
		self::$sVar['php']['installed'] = true;
		/* Apache */
		$reg = "#Apache/(.+?) \((.+?)\)|Apache#";
		if(preg_match($reg,self::$sConfig['Apache Version'],$treffer))
		{
			self::$sVar['apache']['installed'] = true;
			self::$sVar['apache']['enable'] = true;
			self::$sVar['apache']['version'] = empty($treffer[1]) ? "" : $treffer[1];
			self::$sVar['apache']['passed'] = true;
		}
		elseif(preg_match($reg,$_SERVER['SERVER_SOFTWARE'],$treffer)) 
		{
			self::$sVar['apache']['installed'] = true;
			self::$sVar['apache']['enable'] = true;
			self::$sVar['apache']['version'] = empty($treffer[1]) ? "" : $treffer[1];
			self::$sVar['apache']['passed'] = true;
		}
		else 
		{
			self::$sVar['apache']['installed'] = false;
		}
		/* mod_rewrite */
		self::$sVar['mod_rewrite'] = false;
		if (!empty(self::$sConfig['Apache Modules'])&&in_array("mod_rewrite",self::$sConfig['Apache Modules']))
		{
			self::$sVar['mod_rewrite']['installed'] = true;
			self::$sVar['mod_rewrite']['enable'] = true;
			self::$sVar['mod_rewrite']['version'] = "1";
		}
		elseif(file_exists(self::$sDir.".htaccess")&&file_exists(self::$sDir."shopware.php"))
		{
			$headers = @get_headers("http://".$_SERVER["HTTP_HOST"].rtrim(dirname($_SERVER["PHP_SELF"]),"/")."/shopware.dll"); 
			if(!empty($headers[0])&&preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$headers[0]))
			{
				self::$sVar['mod_rewrite']['installed'] = true;
				self::$sVar['mod_rewrite']['enable'] = true;
				self::$sVar['mod_rewrite']['version'] = "1";
			}
		}
		self::$sVar = array_merge_recursive(self::$sVar,self::$sRequired);
		foreach (self::$sVar as $module=>$vars)
		{
			if(isset($vars['passed'])) continue;
			if(!empty($vars['required_version'])&&!empty($vars['version']))
			{
				if(version_compare($vars['version'], $vars['required_version'], ">="))
				{
					self::$sVar[$module]['passed'] = true;
				}
				else 
				{
					self::$sVar[$module]['passed'] = false;
				}
			}
			elseif (!empty($vars['required_version'])&&empty($vars['version']))
			{
				self::$sVar[$module]['passed'] = false;
			}
			else 
			{
				self::$sVar[$module]['passed'] = true;
			}
		}
	}
	function sBrowserCheck ()
	{
		$reg = "#Firefox/([0-9.]+)#";
		preg_match($reg,$_SERVER['HTTP_USER_AGENT'],$result);
		
		if(!empty($result[1]))
		{
			self::$sBrowser['name'] = "Firefox";
			self::$sBrowser['installed'] = true;
			self::$sBrowser['version'] = $result[1];
			if(version_compare($vars['version'], $vars['required_version']['Firefox'], ">="))
			{
				self::$sBrowser['passed'] = true;
			}
			else 
			{
				self::$sBrowser['passed'] = false;
			}
			return;
		}
		
		$reg = "#Version/([0-9.]+) Safari#";
		preg_match($reg,$_SERVER['HTTP_USER_AGENT'],$result);
		
		if(!empty($result[1]))
		{
			self::$sBrowser['name'] = "Safari";
			self::$sBrowser['installed'] = true;
			self::$sBrowser['version'] = $result[1];
			if(version_compare($vars['version'], $vars['required_version']['Safari'], ">="))
			{
				self::$sBrowser['passed'] = true;
			}
			else 
			{
				self::$sBrowser['passed'] = false;
			}
			return;
		}
	}
	function sDirCheck ()
	{
		
		foreach (array_keys(self::$sDirs) as $dir)
		{
			if (!file_exists(self::$sDir.$dir))
			{
				self::$sDirs[$dir]['status'] = "not found";
			}
			elseif (!is_readable(self::$sDir.$dir))
			{
				self::$sDirs[$dir]['status'] = "not readable";
			}
			elseif (!is_writable(self::$sDir.$dir))
			{
				self::$sDirs[$dir]['status'] = "not writable";
			}
			
		}
	}
	function sGetConfig() 
	{
		ob_start();
		phpinfo(-1);
		$s = ob_get_contents();
		ob_end_clean();
		$a = $mtc = array();
		if (preg_match_all('/<tr><td class="e">(.*?)<\/td><td class="v">(.*?)<\/td>(:?<td class="v">(.*?)<\/td>)?<\/tr>/',$s,$mtc,PREG_SET_ORDER)) {
			foreach($mtc as $v){
				if($v[2] == '<i>no value</i>') continue;
				$a[trim($v[1])] = trim($v[2]);
			}
		}
		ob_start();
		phpinfo(INFO_MODULES);
		$s = ob_get_contents();
		ob_end_clean();
		$s = strip_tags($s,'<h2><th><td>');
		$s = preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
		$s = preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
		$vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
		$vModules = array();
		for ($i=1;$i<count($vTmp);$i++) {
			if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
				$vName = trim($vMat[1]);
				$vTmp2 = explode("\n",$vTmp[$i+1]);
				foreach ($vTmp2 AS $vOne) {
				 $vPat = '<info>([^<]+)<\/info>';
				 $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
				 $vPat2 = "/$vPat\s*$vPat/";
				 	if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
				 		$vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]),trim($vMat[3]));
				 	} elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
				 		$vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
				 	}
				}
			}
		}
		$a['MODULES'] = $vModules;
		if(strpos(phpversion(), '-'))
			$a['PHP Version'] = substr(phpversion(),0,strpos(phpversion(), '-'));
		else 
			$a['PHP Version'] = phpversion();
		$a['PHP INI'] = ini_get_all();
		if (function_exists('apache_get_modules')) {
			$a['Apache Modules'] = apache_get_modules();
		}
		if (function_exists('curl_version')) {
			$a['cURL'] = curl_version();
		}
		self::$sConfig = $a;
	}
	function sPrintModuleTable()
	{
		foreach (self::$sVar as $module=>$vars)
		{
			echo " <tr>\n";
			echo "  <th>$module</th>\n";
			if(isset($vars['required_version']))
				echo "  <td>{$vars['required_version']}</td>\n";
			else 
				echo "  <td>&nbsp;</td>\n";
			if(empty($vars['passed'])&&isset($vars['version']))
				echo "  <td class=\"red\">{$vars['version']}</td>\n";
			elseif(isset($vars['version']))
				echo "  <td>{$vars['version']}</td>\n";
			else 
				echo "  <td>&nbsp;</td>\n";
			echo "  <td class=\"status\">";
			if(!empty($vars['status']))
				echo $vars['status'];
			elseif(empty($vars['installed']))
				echo "not found";
			elseif (empty($vars['enable']))
				echo "not enabled";
			elseif (empty($vars['passed']))
				echo "not passed";
			else 
				echo "<img src=\"".basename(__FILE__)."?img=accept\">";
			echo "</td>\n";
			echo " </tr>\n";
		}
	}
	function sPrintDirTable()
	{
		$dirs = self::$sDirs;
		ksort($dirs);
		foreach ($dirs as $dir=>$vars)
		{
			echo " <tr>\n";
			echo "  <th>$dir</th>\n";
			echo "  <td class=\"status\">";
			if(!empty($vars['status']))
				echo $vars['status'];
			else 
				echo "<img src=\"".basename(__FILE__)."?img=accept\">";
			echo "</td>\n";
			echo " </tr>\n";
			if (preg_match("/html2ps/",$dir)){
				echo "<tr><td colspan=2>Wird für Neu-Installationen 3.5 nicht benötigt</td></tr>";
			}
		}
	}
	function sDecodePhpSize ($v)
	{
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) 
	    {
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }
	    return $val;
	}
	function sDecodeSize ($v)
	{
	    $val = trim($val);
	    list($val, $last) = explode(" ",$val);
	    switch(strtoupper($last))
	    {
	    	case 'TB':
	            $val *= 1024;
	        case 'GB':
	            $val *= 1024;
	        case 'MB':
	            $val *= 1024;
	        case 'KB':
	            $val *= 1024;
	        case 'B':
	            $val = (int) $val;
	    }
	    return $val;
	}
	function sEncodeSize($bytes)
	{
	    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
	    return( round( $bytes, 2 ) . " " . $types[$i] );
	}
}

if(isset($_REQUEST['img']))
{
	sSystemCheck::sGetImage($_REQUEST['img']);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
       "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title><?php echo $sLang["systeminfo"]["sSystemCheck_system_check"] ?></title>
<style>

		table.systemcheck {
			font-family:Arial,Verdana,Helvetica,sans-serif;
			font-size:12px;
			font-size-adjust:none;
			line-height:148%;
			float:left;
		}
		.systemcheck  tbody {
			color:#A6A6A6;
		}
		.systemcheck thead {
			text-align:left;
		}
		.systemcheck th, .systemcheck td {
			background-color:#FFFFFF;
			border-bottom:1px solid #E9E9E9;
			border-left:1px solid #E9E9E9;
			margin:0pt;
			padding:2px 5px 2px 2px;
		}
		.systemcheck tbody th {
			text-align:left;
		}
		.systemcheck tbody td {
			text-align:right;
			white-space:nowrap;
		}
		.systemcheck tbody td.status {
			text-align:left;
			color:red;
		}
		.systemcheck .red {
			color:red;
		}
</style>
</head>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<body>
<?php
sSystemCheck::sPrintTables();
?>
</body>
</html>