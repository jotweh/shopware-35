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
$_GET["article"] = $_GET["article"] ? $_GET["article"] : 21604;
?>
<?php
	if (!$_GET["article"]){
		die($sLang["articles"]["artikeln3_No_article_referenced"]);
	}
	//print_r($_SESSION);
	//echo session_id();
	$port = (empty($_SERVER["HTTPS"]) ? "http" : "https");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/moo12-core.js"></script>
<script type="text/javascript" src="../../../backend/js/moo12-more.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="../../../vendor/swfupload/css/default.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<!-- Flashupload -->
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/swfupload/source/swfupload.js"></script>
<script type="text/javascript" src="js/handlers.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>


<!-- // -->
<script>
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deletePicture":
			// Redirect
			// Starting Ajax-Call
			if (sId){
					new Request({url: '<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deletePicture.php?id='+sId, 
					onFailure: function (el){
						parent.parent.Growl('Bild konnte nicht gelöscht werden');
					},
					onComplete: function (response){
						parent.parent.Growl("Bild wurde gelöscht");
					}
					}).get();	
				$(sId).destroy();
			}
			break;
	}
}
</script>
<script type="text/javascript">
	var upload1;
	
		window.onload = function() {
			
			upload1 = new SWFUpload({
				// Backend Settings
				upload_url: "../../../backend/modules/articles/upload.php?article=<?php echo $_GET["article"] ?>&sUsername=<?php echo $_SESSION["sUsername"] ?>&sPassword=<?php echo $_SESSION["sPassword"]?>&sSession=<?php echo session_id()?>",	// Relative to the SWF file (or you can use absolute paths)
				
				// File Upload Settings
				file_size_limit : "102400",	// 100MB
				file_types : "*.jpg;*.png;*.gif",
				file_types_description : "Image-Files (JPG,PNG,GIF)",
				file_upload_limit : "1000",
				file_queue_limit : "0",
				
				// Event Handler Settings (all my handlers are in the Handler.js file)
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				button_placeholder_id : "spanButtonPlaceholder",
				button_width: 150,
				button_image_url: "../../../backend/img/default/window/bg_bt_end.gif",
				button_height: 27,
				button_text : '<span class="button">Bilder auswählen</span>',
				button_text_style : '.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; } .buttonSmall { font-size: 10pt; }',
				button_text_top_padding: 2,
				button_text_left_padding: 15,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,

				// Flash Settings
				flash_url : "../../../vendor/swfupload/source/swfupload.swf",	// Relative to this file (or you can use absolute paths)
				custom_settings : {
					upload_target : "divFileProgressContainer"
				},

				// UI Settings
				swfupload_element_id : "swfu_container",
				

				// Debug Settings
				debug: false
			});
		
			
		}
		
	
	</script>

</head>

<body>
<div class="container" style="width: 95%;">
<div class="col1_a">



<div id="swfu_container" style="margin-left:5px;margin-top:5px;">
<fieldset style="min-width: 200px; width: 200px;padding: 5 5 5 5">
<legend style="font-weight:bold;"><a class="ico images"></a><?php echo $sLang["articles"]["artikeln3_upload_picture"] ?></legend>
<?php echo $sLang["articles"]["artikeln3_Please_arrange"] ?> 
<?php
$abfrage = mysql_query("
SELECT * FROM s_articles, s_articles_supplier WHERE s_articles.id={$_GET["article"]} AND s_articles.supplierID=s_articles_supplier.id 
");
if (@mysql_num_rows($abfrage)){
echo "<strong>".mysql_result($abfrage,0,"name")."</strong>";
}else {
die($sLang["articles"]["artikeln3_article_not_found"]);
} 
?>
 <?php echo $sLang["articles"]["artikeln3_Please_arrange_1"] ?><br/><br/>
<div>
<form action="upload.php" method="post" enctype="multipart/form-data">


<div style="background: transparent url(../../../backend/img/default/window/bg_bt.gif) no-repeat scroll 0 0; padding: 0 0 0 10;width: 150px">
			<span id="spanButtonPlaceholder"></span>
</div>

</form>
</div>
</fieldset>
<div id="divFileProgressContainer" style="height: 75px;"></div>
</div>


</div>

<?php
	$queryImages = mysql_query("SELECT * FROM s_articles_img WHERE articleID=".$_GET["article"]." ORDER BY position,main, id ");
	$countImages = mysql_num_rows($queryImages);
?>
<div class="col2_a" style="width:60%">
<fieldset class="col2_artikeln3" style="margin-top:10px;">
<legend style="font-weight:bold;"><a class="ico help"></a> <?php echo $sLang["articles"]["artikeln3_tip"] ?></legend>
Führen Sie einen Doppelklick auf das Bild aus, um es als Vorschaubild zu markieren. Sie können die Bilder per Drag & Drop verschieben.<br />
	<?php echo $countImages ? sprintf($sLang["articles"]["artikeln3_assigned_pictures"],$countImages) : $sLang["articles"]["artikeln3_assigned_no_pictures"]."<br /><br />" ?>
</fieldset>

<fieldset style="margin-top:-15px;margin-left:20px;" >
<legend>Bilder</legend>
<div id="options" style="height:200px;width:600px;position:absolute;z-index:10000;left:50%;display:none">

<input type="hidden" name="optionsID" id="optionsID" value="">
<fieldset style="margin-top:-10px">
<legend>Bild - Optionen:</legend>
<a href="#" id="optionsLink" target="_blank" class="ico3 disk" style="font-size:10px;padding: 2px 0 2px 25px; display: block; background-position: 0 2px;width:150px;">Original herunterladen</a>
<label style="width:90px;text-align:left">Bildtitel:</label>
<input type="text" style="width:150px" name="optionsTitle" id="optionsTitle" value=""><br />
<label style="width:90px;text-align:left" >Bildzuordnung:</label>
<textarea style="width:150px" name="optionsRelations" id="optionsRelations" value=""></textarea><br /><br />
<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button type="button" value="send" class="button" onclick="saveOptions()"><div class="buttonLabel">Speichern</div></button>
			</li>	
		</ul>
</div>
</fieldset>

</div>

	<?php
if ($countImages){
	?>
	<ul class="images">
	<?php
}
while ($image=mysql_fetch_array($queryImages)){
if (empty($image["extension"])) $image["extension"] = "jpg";
$i++;
	?>
	
	<li id="thumb<?php echo $image["id"]?>" <?php if ($image["main"]==1) { ?> class="main thumb" <?php } ?> class="thumb" original="../../../../images/articles/<?php echo $image["img"].".".$image["extension"]?>" title="<?php echo $image["description"]?>" relations="<?php echo $image["relations"]?>">
		<div><?php if ($image["main"]==1) { ?><div class="first"><?php echo $sLang["articles"]["artikeln3_preview"] ?></div><?php } ?><img id="handle" onDblClick="makeMain('thumb<?php echo $image["id"] ?>')"  src="../../../../images/articles/<?php echo $image["img"]."_2.".$image["extension"]; ?>"  style="max-height:75px;max-width:90px" /></div>
		
		<a  onClick="parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["artikeln3_realy_want_to_delete"] ?>',window,'deletePicture','thumb<?php echo $image["id"] ?>');" class="ico3 delete" style="cursor:pointer;font-size:10px;padding: 2px 0 2px 25px; display: block; background-position: 0 2px;width:91px;"> <?php echo $sLang["articles"]["artikeln3_delete_picture"] ?></a>
		<a  onClick="showOptions('<?php echo $image["id"]?>')" class="ico3 pencil" style="cursor:pointer;font-size:10px;padding: 2px 0 2px 25px; display: block; background-position: 0 2px;width:91px;">Optionen</a>
	</li>
<?php
}
if ($countImages){
?>
	</ul>
	<?php
}
?><div class="clear"></div><br/>
<!-- ausgabe ende -->
</div>
<div class="clear"></div>
</div>
</fieldset>
<style>
.thumb {
	cursor:move
}
</style>
<script language="javascript">
function showOptions(sId){
//	$('options').setStyle('display','block');
	var image = $('thumb'+sId);
	$('optionsLink').setProperty('href',image.getProperty('original'));
	$('optionsTitle').setProperty('value',image.getProperty('title'));
	$('optionsRelations').setProperty('value',image.getProperty('relations'));
	$('optionsID').setProperty('value',sId);
	var original = image.getProperty('original');
	var html = original.substring(0, original.length-4)+'_2'+original.substring(original.length-4);
	html = '<img src="'+html+'">';
	//.replace(/\./,"_2\.")
	//console.log(Ext.getCmp('photo'));
	
	Ext.getCmp('title').setValue(image.getProperty('title'));
	myExt.originalImage = image.getProperty('original');
	myExt.imageID = sId;
	myExt.store.load({params:{start:0,id:sId, article:'<?php echo $_GET["article"] ?>'}});
	var res = "";
	var match = "";
	try {
		res = image.getProperty('relations');
		
		if (res.match(/\&\{/)){
			var match = "&";
		}else if (res.match(/\|\|\{/)){
			var match = "||";
		}else {
			var match = "&";	
		}
	}catch (e){}
	
	if (match){
		Ext.getCmp('filterdispatch').setValue(match,true);
	}else {
		
		Ext.getCmp('filterdispatch').setValue("&",true);
	}
	/*
	var originalImage;
	var imageID;
	*/
	
	
	Ext.getCmp('photo').html = html;
	try {
	Ext.getCmp('photo').el.update(html);
	} catch (e){}
	Ext.getCmp('window1234').show();

	
}

function saveOptions(title,relations,id){
	
	var image = $('thumb'+id);
	image.setProperty('title',title);
	image.setProperty('relations',relations);
	
	
	//var myAjax = new Ajax('../../../backend/ajax/saveOptions.php?id='+id, {method: 'post'}).request({"title":title,"relations":relations});
	new Request({url: '../../../backend/ajax/saveOptions.php?id='+id, 
		onFailure: function (el){
			parent.parent.Growl("Bild-Eigenschaften konnten nicht gespeichert werden");
		},
		onComplete: function (response){
			parent.parent.Growl('Bild-Eigenschaften wurden gespeichert');
		}
		}).post({"title":title,"relations":relations});	
	
	
	$('options').setStyle('display','none');
	
	
	
}
function makeMain(sId){
	// Iterate through all pictures
	
	$$('.main').each (function (el){
		
		// Previous thumbnail gets new state
		$(el).removeClass('main');
		//console.log($(el));
		$(el).getElement('.first').destroy();
		});
		// And setting visuals for current thumb
		$(sId).addClass('main');
		var div = new Element('div');
		div.addClass('first');
		div.set('html','Vorschaubild');
		div.injectBefore($(sId).getFirst().getFirst());
		// Ajax-Call with Id of the new main-picture
		
		new Request({url: '../../../backend/ajax/setCover.php?id='+sId, 
		onFailure: function (el){
			parent.parent.Growl("Bild konnte nicht als Vorschaubild markiert werden");
		},
		onComplete: function (response){
			parent.parent.Growl('Vorschaubild wurde geändert');
		}
		}).get();	
		
	
	
}
function validateForm() {

}

window.addEvent('domready', function(){
	
new Sortables($$('.images'), {
	handles: $$('#thumb'),
	onComplete: function handler (info){
		
	    var posString = "";
		
		$$('.thumb').forEach(function(x){
			id = $(x).getProperty('id');
			id = id.replace(/thumb/,"");
			if (id!=undefined && id){
				posString += id+"#";
			}
		});
		
		new Request({url: '../../../backend/ajax/setImagePositions.php?id=<?php echo $_GET["article"]?>', 
		onFailure: function (el){
			//parent.parent.Growl("Bild-Eigenschaften konnten nicht gespeichert werden");
		},
		onComplete: function (response){
			//parent.parent.Growl('Bild-Eigenschaften wurden gespeichert');
		}
		}).post({"positions":posString});	
		
		
	}
	});
});
var myExt = function(){
	var originalImage;
	var imageID;
	var store;
	var mode;
return {
save : function(){
	// Saving data
	
	if (myExt.mode==1){
		var relations;
		Ext.select('.markedOrders').each ( function (e){
			if (e.dom.checked){
				// Save values
				//e.dom.value
				relations = e.dom.value;
				
			}
		});
		var title = (Ext.getCmp('title').getValue());
		// Save relation
		saveOptions(title,relations,myExt.imageID);
	}else {
		var relations = new Array();
		var a = 0;
		Ext.select('.markedOrders').each ( function (e){
			if (e.dom.checked){
				// Save values
				//e.dom.value
				e.dom.value = e.dom.value.replace("/", " ");
				relations[a] = e.dom.value;
				a++;
			}
		});
		var formatedRelations = relations.join("/");
		formatedRelations = Ext.getCmp('filterdispatch').getValue()+"{"+formatedRelations+"}";
		var title = (Ext.getCmp('title').getValue());
		// Save relation
		saveOptions(title,formatedRelations,myExt.imageID);
		Ext.getCmp('window1234').hide();
	}
},
openImage: function (){
	window.open(myExt.originalImage,'Bild','width=1024,height=768');
},
removeRelations: function (){
	var title = (Ext.getCmp('title').getValue());
		// Save relation
	saveOptions(title,'',myExt.imageID);
	Ext.getCmp('window1234').hide();
},
init : function(){
	
    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	
   
	var dispatches = [['&','UND'],['||','ODER']];
    // trigger the data store load
    var dispatchstore = new Ext.data.SimpleStore({
	    fields: ['id', 'state'],
	    data : dispatches
	});
	var windowForm = new Ext.FormPanel({
		    labelAlign: 'top',
		    region: 'center',
	        frame:true,
	        title: 'Einstellungen',
	        bodyStyle:'padding:5px 5px 0',
	        height: 350,
	        width: 600,
		      items: [
		      {
		        layout:'column',
		        items:[
		        {
		            columnWidth:.5,
		            layout: 'form',
		            items: [{
		                xtype:'textfield',
		                fieldLabel: 'Titel',
		                name: 'first',
		                anchor:'95%',
		                id: 'title'
		            },
		              new Ext.form.ComboBox({
				      	fieldLabel: 'Verknüpfung',
				      	id: 'filterdispatch',
				      	name:'dispatch',
				      	hiddenName:'dispatch',
				      	layout: 'form',
				      	store: dispatchstore,
				      	valueField:'id',
				      	displayField:'state',
				      	editable:false,
				      	forceSelection : true,
				      	shadow:false,
				      	mode: 'local',
				      	triggerAction:'all',
				      	maxHeight: 200
				      })
			      	],
			      	 buttons: [{
	           		 text: 'Bild herunterladen', handler: myExt.openImage
		        	},{
	           		 text: 'Zuordnungen löschen', handler: myExt.removeRelations
		        	}
			        ]
		        }
			      ,
			      {
			      columnWidth:.5,
		          html: 'Test',
		          id: 'photo',
		          autoShow: true
		          
			      }
			      ]
    		   }
    		   ]
    });
	
   
	
   

    // The form elements are standard HTML elements. By assigning an id (as we did above)
    // we can manipulate them like any other element
  
  

    
     var cm = new Ext.grid.ColumnModel([
   		{
           id: 'datum', 
           header: "Variante",
           dataIndex: 'combination',
           width: 250
        },
        {
           id: 'article', 
           header: "Bild anzeigen",
           dataIndex: 'marked',
           width: 50,
           sortable: false,
    	   locked:true,
    	   renderer: function (v,p,r,rowIndex,i,ds){
    	   	myExt.mode = r.data.mode;
    	   	if (r.data.marked){
    	   		var checked = "checked";
    	   	}else {
    	   		var checked = "";
    	   	}
    	   	if (r.data.mode==1){
    	   		return '<input type="radio" class="markedOrders" name="markedOrders" value="'+r.data.id+'" style="float:left;margin-right:3px" '+checked+'/>';
    	   	}else {
    			return '<input type="checkbox" class="markedOrders" name="markedOrders" value="'+r.data.id+'" style="float:left;margin-right:3px" '+checked+'/>';
    	   	}
    		}
        }
        ]);
     myExt.store = new Ext.data.Store({
	        url: '<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getCombinations.php',
	        baseParams: {article:'<?php echo $_GET["article"] ?>',image:''},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'articles',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'id','combination','marked','mode'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
	});
    
	myExt.store.on('load',function(){
		if (myExt.mode==1){
			Ext.getCmp('filterdispatch').hide();
		}
	});
	var grid = new Ext.grid.GridPanel({
	      	region:'south',
	        title:'Zuordnung',
	        store: myExt.store,
	        cm: cm,
	        height: 200,
	        autoScroll: true,
	        trackMouseOver:true,
	        sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
	        loadMask: true
    });
	
 	
	var window = new Ext.Window({
	        title: 'Bild zuordnen',
	        width: 500,
	        id: 'window1234',
	        closeAction: 'hide',
	        height:450,
	        minWidth: 300,
	        minHeight: 400,
	        layout: 'border',
	        hidden:true,
	        plain:true,
	        bodyStyle:'padding:5px;',
	        buttonAlign:'center',
	        items: [windowForm,grid],
	        buttons: [{
	            text: 'Speichern', handler: function (){
	            	myExt.save();
	            }
	        },
	        {
	            text: 'Abbrechen', handler: function (){
	            	Ext.getCmp('window1234').hide();
	            }
	        }]
   
   	 });
  
}}}();



Ext.onReady(function(){
	myExt.init();
});
</script>
</body>
</html>
