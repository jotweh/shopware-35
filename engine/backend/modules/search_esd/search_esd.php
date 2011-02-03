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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Suche</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
</head>
<style>
/*
 * Ext JS Library 1.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */



</style>
<body>

<?php
if (!$_GET["id"]) die("No article");
if ($_GET["delete"]){
	$queryDelete = mysql_query("
	DELETE FROM s_articles_esd_serials WHERE esdID={$_GET["id"]}
	AND id={$_GET["delete"]}
	");
}
if ($_GET["deleteAll"]){
	$getAllSerials = mysql_query("
	SELECT id FROM s_articles_esd_serials WHERE esdID={$_GET["id"]}
	");
	while ($check = mysql_fetch_array($getAllSerials)){
		
		$query = mysql_query("
		SELECT id FROM s_order_esd WHERE serialID = {$check["id"]}
		");
		if (!@mysql_num_rows($query)){
		
			$deleteSerial = mysql_query("
			DELETE FROM s_articles_esd_serials WHERE id={$check["id"]}
			");
		}
	}
}
if ($_GET["deleteConnection"]){
	$queryDelete = mysql_query("
	DELETE FROM s_order_esd WHERE serialID={$_GET["deleteConnection"]}
	");
}

if ($_GET["suchmodus"]) $_POST["suchmodus"] = $_GET["suchmodus"];
if ($_GET["select_hersteller"]) $_POST["select_hersteller"] = $_GET["select_hersteller"];
if ($_GET["select_bestellnummer"]) $_POST["select_bestellnummer"] = $_GET["select_bestellnummer"];
if ($_GET["select_bezeichnung"]) $_POST["select_bezeichnung"] = $_GET["select_bezeichnung"];

?>
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "startSearch":
			// Redirect
			$('search').submit();
			break;
		case "deleteSerial":
			parent.Growl('Seriennummer wurde gelöscht');
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId+"&id=<?php echo $_GET["id"]?>";
		break;
		case "deleteConnection":
			parent.Growl('Zuordnung wurde gelöscht');
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?deleteConnection="+sId+"&id=<?php echo $_GET["id"]?>";
		break;
		case "deleteAll":
			parent.Growl('Nicht zugeordnete Seriennummern gelöscht');
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?id=<?php echo $_GET["id"] ?>&deleteAll=1";
		break;
	}
}

function deleteSerial(ev,text){
		parent.parent.sConfirmationObj.show('Soll die Seriennummer "'+text+'" wirklich gel&ouml;scht werden?',window,'deleteSerial',ev);
}
function disconnect(ev,text){
		parent.parent.sConfirmationObj.show('Soll die Zuordnung zum Benutzer  "'+text+'" wirklich gel&ouml;scht werden?',window,'deleteConnection',ev);
}
function deleteAll(ev,text){
		parent.parent.sConfirmationObj.show('Sollen alle nicht zugeordneten Seriennummern wirklich gelöscht werden?',window,'deleteAll',ev);
}

	
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.sWindows.focus.shake(50);";
		}
	?>
	
};
</script>



<fieldset>
	<legend>Suchoptionen</legend>
	<form id="search" name="frm1" method="post" action="<?php echo $_SERVER['PHP_SELF']."?id=".$_GET["id"]; ?>">
	


	
	<ul>
	<li><input id="search2" name="suchmodus" type="radio" class="inputdynwidth" value="2" <?php if ($_POST['suchmodus']==2 || !$_POST["suchmodus"]){echo "checked";}?> /></li>
	<li><label onclick="$('search2').setProperty('checked',1)" for="select_bestellnummer" style="width:250px;text-align:left">Suche nach Seriennummer:</label>  
  	<input onfocus="$('search2').setProperty('checked',1)" name="searchSerial" type="text" class="w200" id="select_bestellnummer" value="<?php echo $_POST['select_bestellnummer']; ?>" /></li>
	<li class="clear"></li>
	<ul>
	<li><input id="search3" name="suchmodus" type="radio" class="inputdynwidth" value="3" <?php if ($_POST['suchmodus']==3){echo "checked";}?> /></li>
	<li><label onclick="$('search3').setProperty('checked',1)" style="width:250px;text-align:left" for="suche3">Suche nach Kunde (eMail):</label>  
  	<input onfocus="$('search3').setProperty('checked',1)" name="searchUser" type="text" class="w200" value="<?php echo $_POST['select_bezeichnung']; ?>" /></li>
	<li class="clear"></li>
	
	
	
	
	</form><li class="clear"></li><br />
	<a class="ico delete" style="cursor:pointer" onclick="deleteAll()"></a> Alle nicht vergebenen Seriennummern löschen
</fieldset>

<p>
  <?php
                  if ($artid && $newkat){
                  		$abfrage  = mysql_query("
                  		UPDATE s_core_articles SET categoryID={$_POST["newkat"]} WHERE id={$_POST["artid"]}
                  		");
                  		echo "Hauptkategorie wurde angepasst<br>";
                  		unset($_POST['suchmodus']);
                  }
			if ($_POST['suchmodus']){
			// Suchmodus
			
			// ...............................................
			
			switch ($_POST['suchmodus']){
				case 2:
					$sql = "
					SELECT id, serialnumber FROM s_articles_esd_serials
					WHERE esdID={$_GET["id"]} AND serialnumber LIKE '%{$_POST["searchSerial"]}%'
					";
					break;
				case 3:
				
					$_POST["searchUser"] = strtolower(trim($_POST["searchUser"]));
					$sql = "
					SELECT s_articles_esd_serials.id AS id, serialnumber, userID FROM s_articles_esd_serials, s_user, s_order_esd
					WHERE s_articles_esd_serials.esdID={$_GET["id"]} AND s_user.email LIKE '%{$_POST["searchUser"]}%'
					AND s_order_esd.userID = s_user.id AND s_order_esd.serialID=s_articles_esd_serials.id
					";
					break;
			}
		
		
			//echo $sql;
				$abfrage = mysql_query($sql);
				if (!$abfrage){ echo mysql_error()."<br>".$sql; }
				$countArticles = mysql_num_rows($abfrage);
			?>
                  <?php if (!$countArticles){ ?>
                  
                  <b>Es wurden keine Seriennummern gefunden</b>
                  <?php ?>
                  <br>
                  <?php } else { ?>
                  <b>Es wurden <?php echo $countArticles; ?> Seriennummern gefunden</b>
                  <?php } 
			
			}?>
                  
</p>
<script>
<?php
if ($countArticles){
	?>
var headers = [
{
"text":"Nummer",
"key":"ID","sortable":true,
"fixedWidth":true,"defaultWidth":"250px"},
{
"text":"Zugeordnet",
"key":"related","sortable":true,
"fixedWidth":true,"defaultWidth":"200px"},
{"text":"Optionen",
"key":"options","sortable":false,
"fixedWidth":true,"defaultWidth":"150px"}
];
	<?php
		echo "var data = [";
		while ($serial = mysql_fetch_array($abfrage)){
			
			// Check if serial is attached
			$checkSerial = mysql_query("
			SELECT userID FROM s_order_esd WHERE serialID={$serial["id"]}
			");
			
			if (@mysql_num_rows($checkSerial)){
				$serial["userID"] = mysql_result($checkSerial,0,"userID");
				// Get eMail
				$getMail = mysql_query("
				SELECT email FROM s_user WHERE id={$serial["userID"]}
				");
				if (@mysql_num_rows($getMail)){
					$user = mysql_result($getMail,0,"email");
				}else {
					$user = "Benutzer nicht gefunden";
				}
			}else {
				unset($user);
			}
			$i++;
			if ($i==$countArticles){
				$comma = "";
			}else {
				$comma = ",";
			}
			?>
{"ID":"<?php echo $serial["serialnumber"] ?>","related":"<?php echo $user ?>","options":"<a class=\"ico delete\" style=\"cursor:pointer\" onclick=\"deleteSerial(<?php echo $serial['id']?>,'<?php echo $serial['serialnumber']?>')\"></a><?php 
if ($serial["userID"]) { ?><a class=\"ico user\" style=\"cursor:pointer\" onclick=\"parent.parent.loadSkeleton('userdetails',false, {'user':<?php echo $serial["userID"] ?>})\"></a><a class=\"ico disconnect\" style=\"cursor:pointer\" onclick=\"disconnect(<?php echo $serial['id']?>,'<?php echo $user ?>')\"></a><?php } ?>"}<?php echo $comma ?>
			<?php 
		}
		echo "];"; 
	}

if ($countArticles){
?>



window.addEvent('domready',function(){

	
				function exampleClick(ev){
					//parent.parent.Growl( 'You picked row ' + (this.data.id) );
					parent.parent.loadSkeleton('articles',false, "{article:"+this.data.id+"}");
				}
				mootable = new MooTable( 'test', {debug: false, height: '300px', headers: headers, sortable: true, useloading: false, resizable: true});
				mootable.addEvent( 'afterRow', function(data, row){
					
					//debug.log( row );
					//row.cols[0].element.innerHTML = ( data.ID + 1);
					<?php
					if ($countArticles){
					?>
					//row.cols[2].element.setStyle('cursor', 'pointer');
					//row.cols[2].element.addEvent( 'click', exampleClick.bind(row) );
					<?php
					}
					?>
				});
				mootable.loadData( data );
				});
		</script>
<div id='test'>&nbsp;</div>
<?php
}
?>
</html>