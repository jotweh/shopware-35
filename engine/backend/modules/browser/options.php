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
// *****************
?>
<?php

?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

</head>
<script>
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "delete":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete=1&id=<?php echo $_REQUEST["id"]?>";
			break;
	}
}

function deleteFile(text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["browser"]["options_Should_the_file"] ?> "'+text+'" <?php echo $sLang["browser"]["options_really_be_deleted"] ?>',window,'delete',0);
	}
</script>
<style>
.clear { /*  - fixfloat */
	clear: both;
	padding: 0;
	margin:0;
	width: 0px;
	height: 0px;
	line-height: 0px;
	font-size: 0px;
}
td {
	font-size:10px
}
</style>
<body style="padding: 10 10 10 10; margin: 0 0 0 0; ">
<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?sSave=1&id=".$_GET["id"] ?>">

<?php
$prepath = "../../../..";
//print_r($_FILES);
if ($_FILES["filecontent"]["tmp_name"]){
	if ($_POST["filename"]){
		$target = $_POST["filename"];
	}else {
		$target = $_FILES["filecontent"]["name"];
	}
	
	$filename = strtolower($target);
	
	// Datei-Endung
	$filename = explode(".", $filename);
	$filenameext = $filename[count($filename)-1];
	if (in_array(strtolower($filenameext),array("php","php5","php4","phtml","cgi","pl","php3","py","exe","bat","com"))){
		//echo $filenameext;
		die("Upload of $filenameext files is forbidden");
	}
	
	if (move_uploaded_file($_FILES["filecontent"]["tmp_name"], $prepath.$_REQUEST["id"]."/".$target)){
		$upload = true;
	}else {
		$upload = false;
	}
	
	if ($upload){
		echo "<script>
		parent.parent.Growl('".$sLang["browser"]["options_upload_complete"]."');
		parent.myExt.reload();
		</script>
		";
	}else {
		echo "<script>
		parent.parent.Growl('".$sLang["browser"]["options_upload_failed"]."');
		</script>
		";
	}
}
?>
<?php
if ($_POST["dirname"]){
	if (mkdir($prepath.$_REQUEST["id"]."/".$_POST["dirname"])){
		chmod($prepath.$_REQUEST["id"]."/".$_POST["dirname"],0777);
		echo "<script>
		parent.parent.Growl('".$sLang["browser"]["options_Directory_was_created"]."');
		parent.myExt.reload();
		</script>
		";
	}else {
		echo "<script>
		parent.parent.Growl('".$sLang["browser"]["options_Could_not_be_created"]."');
		</script>
		";
	}
	
}
if ($_GET["delete"]){
	
	if (unlink($prepath.$_REQUEST["id"])){
		$delete = true;
	}else {
		$delete = false;
	}
	
	if ($delete){
		echo "<script>
		parent.parent.Growl('".$sLang["browser"]["options_file_Successfully_deleted"]."');
		parent.myExt.reload();
		</script>
		";
	}else {
		echo "<script>
		parent.parent.Growl('".$sLang["browser"]["options_Could_not_be_deleted"]."');
		</script>
		";
	}
}
?>
<?php
if ($_REQUEST["id"]){


$subject = $prepath.$_REQUEST["id"];
?>



<fieldset style="margin: -29px 0 0 0; padding:0 0 0 0;">
<legend><?php echo $sLang["browser"]["options_informations_about"] ?> <?php echo $_REQUEST["id"]?></legend>
 <table width="100%"  border="0" cellpadding="2" cellspacing="1" bordercolor="#CCCCCC">
   <tr>
         <td colspan="2" nowrap="nowrap" class="th_bold"></td>
   </tr 
	<tr style="background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x; height:38px;">
	     <td  width="30%" nowrap="nowrap"><?php echo $sLang["browser"]["options_Access_rights"] ?></td>
	     <td  width="70%">
	    	<?php
			clearstatcache();
			echo file_perms($subject,true);
			if (!is_writeable($subject)){
				echo "<span style=\"font-weight:bold;color:#F00\"> - ".$sLang["browser"]["options_write_protection"]."</span>";
			}else {
				echo "<span style=\"font-weight:bold;color:#0F0\"> - ".$sLang["browser"]["options_Full_access"]."</span>";
			}
			?>
		</td>          	
	</tr>
	<?php
	if (is_file($subject)){
	?>
	<tr style="background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x; height:38px;">
	     <td  width="30%" nowrap="nowrap"><?php echo $sLang["browser"]["options_file_size"] ?></td>
	     <td  width="70%">
	     	<?php
			echo get_size(filesize($subject));
	     	?>
		</td>          	
	</tr>
	<?php
	}
	?>
	<?php
	if (is_file($subject)){
	?>
	<tr style="background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x; height:38px;">
	     <td  width="30%" nowrap="nowrap"><?php echo $sLang["browser"]["options_url"] ?></td>
	     <td  width="70%">
	     	<?php
			echo "http://".$sCore->sCONFIG['sBASEPATH'].$_REQUEST["id"];
	     	?>
		</td>          	
	</tr>
	<?php
	}
	?>
	<?php
	if (is_file($subject)){
	?>
	<tr valign="top" style="height:120px;">
	     <td  width="30%" nowrap="nowrap"><?php echo $sLang["browser"]["options_preview"] ?></td>
	     <td  width="70%">
	     	<?php
			if (preview($subject)){
				echo "<img src=\"$subject\" style=\"max-width:200px;max-height:150px;float:left\">";
			}
	     	?>
	     	<div class="clear"></div>
	     	<a href="<?php echo $subject ?>" style="width:150px" target="_blank" class="ico3 disc"><?php echo $sLang["browser"]["options_download_file"] ?></a>
		</td>          	
	</tr>
	<?php
	}
	?>
	<?php
	if (is_file($subject) && is_writeable($subject)){
	?>
	<tr valign="top" style="height:120px;">
	     <td  width="30%" nowrap="nowrap"><?php echo $sLang["browser"]["options_file_options"] ?></td>
	     <td  width="70%">
	     	<?php
			//  href="<?php echo $_SERVER["PHP_SELF"]."?delete=1&id=".$_GET["id"]
	     	?>
	     	<a onclick="deleteFile('<?php echo $_GET["id"] ?>')" style="width:150px;cursor:pointer" class="ico3 delete"><?php echo $sLang["browser"]["options_delete_file"] ?></a>     	
		</td>          	
	</tr>
	<?php
	}
	?>
	<?php
	if (is_dir($subject) && is_writeable($subject)){
	?>
	<tr valign="top" style="height:120px;">
	     <td  width="30%" nowrap="nowrap"><?php echo $sLang["browser"]["options_upload_file"] ?></td>
	     <td  width="70%">
	     	<ul>
		     	<li id="$fieldName"><label for="name"><?php echo $sLang["browser"]["options_file"] ?></label><input name="filecontent" type="file"  style="height:25px;width:250px" class="w200" /></li>
				<li class="clear"/>
				<li id="$fieldName"><label for="name"><?php echo $sLang["browser"]["options_filetitle"] ?></label><input name="filename" type="text"  style="height:25px;width:250px" class="w200" /></li>
				<li class="clear"></li>
				<div class="buttons" id="buttons">
					<ul>
						<li id="buttonTemplate" class="buttonTemplate">
						<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["browser"]["options_save"] ?></div></button>
						</li>	
					</ul>
				</div>
				<li class="clear"/>
	     	</ul>    	
		</td>          	
	</tr>
	<?php
	}
	?>
	<?php
	if (is_dir($subject) && is_writeable($subject)){
	?>
	<tr valign="top" style="height:120px;">
	     <td  width="30%" nowrap="nowrap"><?php echo $sLang["browser"]["options_new_Directory"] ?></td>
	     <td  width="70%">
	     	<ul>
	
				<li id="$fieldName"><label for="name"><?php echo $sLang["browser"]["options_Directoryname"] ?></label><input name="dirname" type="text"  style="height:25px;width:250px" class="w200" /></li>
				<li class="clear"></li>

				<div class="buttons" id="buttons">
					<ul>
						<li id="buttonTemplate" class="buttonTemplate">
						<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["browser"]["options_create_directory"] ?></div></button>
						</li>	
					</ul>
				</div>
	     	</ul>    	
		</td>          	
	</tr>
	<?php
	}
	?>
</table>
</fieldset>

<?php



//print_r($_REQUEST);
}
?>
<?php

function preview($file){
	// Get File - Extension
	$extension = strtolower(array_pop(explode(".", $file)));
	$previewExtensions = array("jpg","gif","jpeg","png");
	if (in_array($extension,$previewExtensions)){
		return true;
	}else {
		return false;
	}
}

function file_perms($file, $octal = false)
{
    if(!file_exists($file)) return false;

    $perms = fileperms($file);

    $cut = $octal ? 2 : 3;

    return substr(decoct($perms), $cut);
}

function get_size($size) {
$bytes = array('B','KB','MB','GB','TB');
  foreach($bytes as $val) {
   if($size > 1024){
    $size = $size / 1024;
   }else{
    break;
   }
  }
  return round($size, 2)." ".$val;
}
?>

</form>





</body>

</html>