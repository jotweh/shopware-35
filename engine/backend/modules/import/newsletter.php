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
	<title>Shopware Import/Export</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="../../../vendor/ext/resources/css/ext-all.css" rel="stylesheet" type="text/css" />
	<link href="css/FileUploadField.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
	<script type="text/javascript" src="js/FileUploadField.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
 	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script>
Ext.onReady(function(){
	
	var progress = function(config)
	{
		Ext.Ajax.request({
			url: 'import.php',
			params : config,
			method: 'POST',
			timeout: 60000,
			success: function (result, request) {
				if(result.responseText)
				{
					result = Ext.util.JSON.decode(result.responseText);
					if(result)
					{
						Ext.getCmp('import_progress').updateProgress(result.progress, result.text);
						if(result.progress<1)
						{
							progress(result.sConfig);
						}
						else
						{
							end(result.sConfig);
						}
					}
				}
			},
			failure: function ( result, request) {
				Ext.destroy(Ext.getCmp('import_window'));
				Ext.Msg.alert('Import', 'Der Import ist fehlgeschlagen!');
			}
		});
	};
    var end = function(config)
    {
    	Ext.Ajax.request({
    		url: 'end.import.php',
    		params : config,
    		method: 'POST',
    		success: function (result, request) {
    			Ext.destroy(Ext.getCmp('import_window'));
    			result = Ext.util.JSON.decode(result.responseText);
				if(result)
				{
					Ext.Msg.alert('Import', result.message);
				}
    		}
    	});
    };
	
 	 var form = new Ext.FormPanel({
        title: 'Import von Newsletter-Empfänger',
        bodyStyle:'padding:20px;width:600px',
        layout:'form',
        labelWidth: 180,
        fileUpload: true,
        id: 'form',
        region:'center',
        height: 260,
        defaults: {anchor: '100%',xtype:'textfield'},
        items: [
        	{
	            xtype: 'hidden',
	            name: 'typ',
	            value: 5
	        },
        	 new Ext.form.ComboBox({
	      		fieldLabel: 'Gruppe',
			    displayField:'name',
			    hiddenName:'group',
			    lazyInit: false,
			    valueField:'id',
			    typeAhead: true,
			    store:  new Ext.data.Store({
			    	url: 'getValues.php?name=newsletter_groups',
			    	autoLoad: true,
			    	reader: new Ext.data.JsonReader({
			    		root: 'articles',
			    		totalProperty: 'count',
			    		id: 'id',
			    		fields: ['id','name']
			    	})
		         }),
		        mode: 'remote',
			    triggerAction: 'all',
			    emptyText:'<?php echo $sLang["orderlist"]["orders_Please_select"] ?>',
			    selectOnFocus:true,
			    forceSelection: true,
			    value: '<?php echo $sLang["orderlist"]["orders_Please_select"] ?>'
			}),
        	{
	            xtype: 'fileuploadfield',
	            emptyText: 'Bitte wählen...',
	            fieldLabel: 'Datei',
	            allowBlank: false,
	            name: 'articles_file',
	            buttonText: '',
	            buttonCfg: {
	                iconCls: 'upload-icon'
	            }
	        }
        ],
        buttonAlign:'right',
        buttons: [{
            text: 'Start',
            handler: function(){
            	var form = Ext.getCmp('form').getForm();
                if(form.isValid()){
                	new Ext.Window({
                		id: 'import_window',
                		title: 'Import',
                		width: 300,
                		height: 150,
                		closable: false,
                		//layout: 'fit',
                		modal: true,
                		bodyStyle:'padding:20px 10px',
                		items: new Ext.ProgressBar({
                			id:'import_progress',
                			cls:'left-align'
                		})
                	}).show();
                	Ext.getCmp('import_progress').wait({text:'Die Datei wird hochgeladen ...'});
	                form.submit({
	                    url: 'start.import.php',
	                    baseParams: {typ: 5},
	                    success: function(fp, o){
	                        Ext.getCmp('import_progress').reset();
							Ext.getCmp('import_progress').updateProgress(0, 'Die Datei "'+o.result.sConfig.sFileName+'" wurde hochgeladen');
							progress(o.result.sConfig);
	                    },
			    		failure: function (fp, o) {
			    			Ext.destroy(Ext.getCmp('import_window'));
    						Ext.Msg.alert('Fehler', o.result.msg);
			    		}
	                });
                }
            }
        }]
    });
    var info = {
    	region:'north',
    	height: 70,
    	html: "<div style=\"padding:20px;font-family:arial,tahoma,helvetica,sans-serif;font-size:12px;font-size-adjust:none;font-style:normal;font-variant:normal;font-weight:bold;line-height:normal;color:red\">"+
			"Die Import/Export-Möglichkeiten unterstützen gegebenenfalls nicht alle von Ihnen gepflegten Felder, prüfen Sie daher bitte unbedingt die Dokumentation im <a href=\"http://www.shopware-ag.de/wiki/Datenaustausch_detail_308.html\" target=\"_blank\">Wiki</a> bevor Sie mit dem Modul arbeiten."+
	 	"</div>"
    };
    var viewport = new Ext.Viewport({
    	layout:'border',
    	autoScroll:true,
	    border: false,
    	items: [info, form]
    });
 });
</script>
</head>
<body>
</body>
</html>