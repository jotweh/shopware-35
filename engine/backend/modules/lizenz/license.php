<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();



if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

<title>Lizenzen</title>
</head>

<body>
<fieldset class="col2_cat2">
<legend><a class="ico help"></a><?php echo $sLang["license"]["license_shopware_license"] ?></legend>
<?php echo $sLang["license"]["license_You_can_easily_licensed_modules"] ?><br />
<!--
<strong><?php echo $sLang["license"]["license_more_informations"] ?></strong><br /><br />

<a class="ico3 information" style="width:150px" href="http://www.shopware-ag.de/Professional-_cat_180.html" target="_blank"><span style="margin-left:25px"><?php echo $sLang["license"]["license_modlue_overview"] ?></span></a>
-->
<span>Weitere Module können Sie bequem über den Shopware-Account beziehen</span>

</fieldset>

<form name="form1" method="post" action="license.php">

<fieldset style="min-width:500px;">
	<legend><?php echo $sLang["license"]["license_license"] ?></legend>
	<ul>
	
	<li class="break">
	 <label for="nehmer"><?php echo $sLang["license"]["license_licensee"] ?></label>  
  	<input name="nehmer" type="text" id="nehmer" value="<?php echo $_SERVER['HTTP_HOST'];?>" style="width:350px" readonly /></li>
	<li class="clear"></li>
	
	<li class="break">
	 <label for="nummer"><?php echo $sLang["license"]["license_license_number"] ?></label>  
  	<input name="nummer" type="text" id="nummer" value="<?php echo $sCore->sLicenseData["sCORE"];?>" style="width:350px" readonly /></li>
	<li class="clear"></li>
	<li class="break">
	 <label for="nummer"><?php echo $sLang["license"]["license_modules"] ?></label>
	 <ul style="float:left;">
	 <?php	foreach ($sCore->sLicenseData as $key => $value){ ?>
	 <li style="width:120px;clear:both;text-align:right;"><?php echo $key;?>:</li>
	 <li style="margin-right:0px"><?php echo $value;?></li>
	 <?php } ?>
	 </ul>
  	</li>
	<li class="clear"></li>	
	</ul>
</fieldset>
</form>



  
</body>
</html>

