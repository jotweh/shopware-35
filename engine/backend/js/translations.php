<?php
define('sAuthFile', 'sSUMMARY');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");

$result = new checkLogin();

$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("");
}

if (1!=1) { ?> <script> <?php }

if (1!=1){
?>
function sTranslations(element, key, id, object, language,secondkey){
	try {
		parent.Growl("&Uuml;bersetzung ist nicht lizensiert");
	}catch (e){
		parent.parent.Growl("&Uuml;bersetzung ist nicht lizensiert");
	}
return true;
}
<?php
} else {
?>

/*
SHOPWARE 2 - Mootools Framework -
(c)2008, Hamann-Media GmbH
*/
function sTranslations(element, key, id, object, language,secondkey){
	
	var parentElement = $(element);
	
	var left = $(element).getPosition().x;
	var top = $(element).getPosition().y + $(element).getSize().y - 20;
	
	if (top<=0){
		var left = $(element+'_parent').getPosition().x +  ($(element+'_parent').getSize().x/3);
		var top = $(element+'_parent').getPosition().y - 20;
		
		if (top<=0){
			top = 50;
		}else {
			//alert(top);
		}
			
	}
	// Reset Translation Form
	$('sTranslationsId').setProperty('value','');
	$('sTranslationsKey').setProperty('value','');
	$('sTranslationsKey2').setProperty('value','');
	$('sTranslationsObject').setProperty('value','');
	$('sTranslationsLanguage').setProperty('value','');
	//$('sTranslationsValue').setText('');
	// Hide translation window
	$('sTranslations').setStyle('display','none');
	$('sTranslationsLoader').setStyle('display','block');
	$('sTranslationsMask').setStyle('display','none');
	
	
	
	$('sTranslations').setStyles({'display':'block','left':left+'px','top':top+'px'});
	
	if (!secondkey || secondkey=="") var secondkey = "";
	
	var ajaxRequest = new Ajax
		('<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getTranslation.php?key='+key+'&id='+id+'&object='+object+'&language='+language+'&secondkey='+secondkey, 
		{
		onStart: function(el){},
		onFailure: function (el){
			parent.Growl("Übersetzung konnte nicht geladen werden");
		},
		onComplete: function(skeleton){
			// Disable tiny if previously activated
			try {
				disableTiny(tinyMCE,'sTranslationsValue');
			} catch (e){
				
			}
			$('sTranslationsLoader').setStyle('display','none');
			$('sTranslationsMask').setStyle('display','block');
			
			
		
			$('sTranslationsValue').setProperty('value',skeleton);
		
			// Dynamic convert to tiny, if original one is tiny
			if ($(element).getProperty('mce_editable')){
				enableTiny(tinyMCE,'sTranslationsValue');
			}

			// Pass element information
			$('sTranslationsId').setProperty('value',id);
			$('sTranslationsKey').setProperty('value',key);
			$('sTranslationsKey2').setProperty('value',secondkey);
			$('sTranslationsObject').setProperty('value',object);
			$('sTranslationsLanguage').setProperty('value',language);
			
			$('sNotifier').setHTML('DE > '+language.toUpperCase()+' ('+key+')');
			
			$('sTranslationsForm').removeEvents('submit');
			$('sTranslationsForm').addEvent("submit",cancelMe);
			$('sTranslationsForm').addEvent("submit",function(e){
				new Event(e).stop();
			
				new Ajax('<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/setTranslation.php', {data: this, onComplete: function(el){
					switch (el){
						case "FAILURE":
							parent.Growl("Fehler - Übersetzung nicht gespeichert -");
						break;
						case "SUCCESS":
							try {
								parent.Growl("Übersetzung wurde gespeichert");
							}catch (e){
								parent.parent.parent.Growl("Übersetzung wurde gespeichert");
							}
							// Remove object information
							$('sTranslationsId').setProperty('value','');
							$('sTranslationsKey').setProperty('value','');
							$('sTranslationsKey2').setProperty('value','');
							$('sTranslationsObject').setProperty('value','');
							$('sTranslationsLanguage').setProperty('value','');
							//$('sTranslationsValue').setText('');
							// Hide translation window
							$('sTranslations').setStyle('display','none');
							$('sTranslationsLoader').setStyle('display','block');
							$('sTranslationsMask').setStyle('display','none');
							
							
							
							break;
						default:
							parent.Growl("Unbekannter Rückgabewert");
						
					}
					
				},}).request();
			});
		
		}}
		).request();
}

function cancelMe(event) { if( !event.preventDefault ) { this.returnValue = false; } else { event.preventDefault() }}

function disableTiny(tinyMCE,sEditorID) {
    try {
    	tinyMCE.execCommand('mceRemoveControl', false, sEditorID);
    } catch (e){
    	
    }
}
function enableTiny(tinyMCE,sEditorID){
	 tinyMCE.execCommand('mceAddControl', false,sEditorID);
	 var textControl = tinyMCE.get(sEditorID);
	 var tempContent = textControl.getContent();
	 textControl.setContent(tempContent);

	//window.setTimeout("",250);
}

<?php if (1!=1) { ?> </script> <?php } ?>
<?php }?>