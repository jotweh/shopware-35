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
 	var form = new Ext.FormPanel({
        title: 'Import von Artikeln, Kategorien, Lagerbeständen, Kunden und Artikelpreise',
        bodyStyle:'padding:20px',
        layout:'form',
        labelWidth: 300,
        fileUpload: true,
        id: 'form',
        region:'center',
        height: 260,
        defaults: {anchor: '100%',xtype:'textfield'},
        items: [
	        new Ext.form.ComboBox({
                fieldLabel: 'Kategorien löschen',
                hiddenName:'delete_old_categories',
                name:'delete_old_categories',
                store: new Ext.data.SimpleStore({
                    fields: ['valueID', 'value'],
                    data : [[0, 'keine'],[1, 'alle vor Import'],[2, 'nicht importierte'],[3, 'leere']]
                }),
                value: 0,
                valueField:'valueID',
                displayField:'value',
                mode: 'local',
                //emptyText:'Bitte wählen...',
                selectOnFocus:true,
                anchor:'50%',
                allowBlank: false,
                typeAhead: true,
                triggerAction: 'all',
                forceSelection : true
        	}),
        	new Ext.form.ComboBox({
                fieldLabel: 'Artikel löschen',
                hiddenName:'delete_old_articles',
                name:'delete_old_articles',
                store: new Ext.data.SimpleStore({
                    fields: ['valueID', 'value'],
                    data : [[0, 'keine'],[1, 'alle vor Import'],[2, 'nicht importierte']]
                }),
                value: 0,
                valueField:'valueID',
                displayField:'value',
                mode: 'local',
                //emptyText:'Bitte wählen...',
                selectOnFocus:true,
                anchor:'50%',
                allowBlank: false,
                typeAhead: true,
                triggerAction: 'all',
                forceSelection : true
        	}),{
	            fieldLabel: 'Cache leeren',
	            name: 'delete_article_cache',
	            xtype: 'checkbox'
	        },{
	            fieldLabel: 'Artikel-Bilder importieren',
	            name: 'article_images',
	            xtype: 'checkbox'
	        },{
	            fieldLabel: 'Anzahl in einem Schritt zu importierende Artikel',
	            name: 'import_step',
	            xtype: 'numberfield',
	            value: 10
	        },
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
	                    success: function(fp, o) {
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
    
    var export_orders_form = new Ext.FormPanel({
        title: 'Export von Bestellungen',
        id: 'export_orders_form',
        layout:'form',
        fileUpload: true,
        bodyStyle:'padding:20px',
        labelWidth: 300,
        defaults: {anchor: '100%',xtype:'textfield'},
        height: 275,
        items: [{
                    fieldLabel: 'Bestellnummer ab',
                    hiddenName:'ordernumber',
                    name: 'ordernumber'
                }, 
                 new Ext.form.ComboBox({
		      		fieldLabel: 'Bestellstatus',
				    displayField:'name',
				    hiddenName:'orderstateID',
				    lazyInit: false,
				    valueField:'id',
				    typeAhead: true,
				    store:  new Ext.data.Store({
				    	url: 'getValues.php?name=orderstate',
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
		       new Ext.form.ComboBox({
		      		fieldLabel: 'Zahlstatus',
		      		hiddenName:'paymentstateID',
		      		lazyInit: false,
				    displayField:'name',
				    valueField:'id',
				    typeAhead: true,
				    store:  new Ext.data.Store({
				    	url: 'getValues.php?name=paymentstate',
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
               		 new Ext.form.DateField({
						fieldLabel: 'Von',
						hiddenName:'fromDate',
					    typeAhead: true,
					    format: 'd.m.Y',
					    id: 'fromDate',
					    endDateField: 'toDate',
					    emptyText:'Bitte auswählen'
					})
					,
					new Ext.form.DateField({
						fieldLabel: 'Bis',
						hiddenName:'toDate',
					    typeAhead: true,
					    format: 'd.m.Y',
					    id: 'toDate',
					    startDateField: 'fromDate',
					    emptyText:'Bitte auswählen'
					})
					, 
	                new Ext.form.ComboBox({
			      		fieldLabel: 'Bestellstatus updaten',
					    displayField:'name',
					    hiddenName:'updatestateID',
					    lazyInit: false,
					    valueField:'id',
					    typeAhead: true,
					    store:  new Ext.data.Store({
					    	url: 'getValues.php?name=orderstate',
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
					    forceSelection: true
					})
					,
					new Ext.form.ComboBox({
                        fieldLabel: 'Dateiformat',
                        hiddenName:'formatID',
                        name:'formatID',
                        store: new Ext.data.SimpleStore({
                            fields: ['formatID', 'format'],
                            data : [[3, 'XML'],[1, 'CSV']/*[2, 'TSV'],*/]
                        }),
                        //value: 1,
                        valueField:'formatID',
                        displayField:'format',
                        mode: 'local',
                        emptyText:'Bitte wählen...',
                        selectOnFocus:true,
                        anchor:'50%',
                        allowBlank: false,
                        typeAhead: true,
                        triggerAction: 'all',
                        forceSelection : true
                	}) 	
        ],
        buttonAlign:'right',
        buttons: [{
            text: 'Start',
            handler: function(){
            	var form = Ext.getCmp('export_orders_form').getForm();
	            form.submit({url:'export.orders.php?sAPI=<?php echo$sCore->sCONFIG['sAPI']?>', success: function (el, r){
	            	
	            }});
	        }
        }]
    });
    
   var export_article_form = new Ext.FormPanel({
        title: 'Export von Artikeln und Kategorien',
        bodyStyle:'padding:20px',
        layout:'form',
        labelWidth: 300,
        id: 'export_article_form',
        region:'north',
        defaults: {anchor: '100%',xtype:'textfield'},
        fileUpload: true,
        height: 185,
        items: [
        	new Ext.form.ComboBox({
                fieldLabel: 'Daten',
                hiddenName:'typID',
                name:'typID',
                store: new Ext.data.SimpleStore({
                    fields: ['typID', 'typ'],
                    data : [[1, 'Artikel'], [2, 'Kategorien'], [7, 'Artikel und Kategorien']]
                }),
                valueField:'typID',
                displayField:'typ',
                mode: 'local',
                emptyText:'Bitte wählen...',
                selectOnFocus:true,
                anchor:'50%',
                allowBlank: false,
                typeAhead: true,
                triggerAction: 'all',
                forceSelection : true
        	}),
			new Ext.form.ComboBox({
                fieldLabel: 'Dateiformat',
                hiddenName:'formatID',
                name:'formatID',
                store: new Ext.data.SimpleStore({
                    fields: ['formatID', 'format'],
                    data : [[1, 'CSV'], [2, 'XML'], [3, 'Excel']]
                }),
                valueField:'formatID',
                displayField:'format',
                mode: 'local',
                emptyText:'Bitte wählen...',
                selectOnFocus:true,
                anchor:'50%',
                allowBlank: false,
                typeAhead: true,
                triggerAction: 'all',
                forceSelection : true
        	}),{
	            fieldLabel: 'Übersetzungen exportieren',
	            name: 'article_translations',
	            xtype: 'checkbox'
	        },{
	            fieldLabel: 'Kundengruppenpreise exportieren',
	            name: 'article_customergroup_prices',
	            xtype: 'checkbox'
	        }
        ],
        buttonAlign:'right',
        buttons: [{
            text: 'Start',
            handler: function(){
            	var form = Ext.getCmp('export_article_form').getForm();
                if(!form.isValid()) return;
                //Ext.MessageBox.wait("","Die Daten werden exportiert ..."); 
                form.submit({
                	url: 'export.php',
                	success: function(fp, o){
                		//Ext.MessageBox.alert("Fertiggestellt", ""); 
                	}
                });
            }
        }]
    });
    
    var export_form = new Ext.FormPanel({
        title: 'Export Sonstiges',
        bodyStyle:'padding:20px',
        layout:'form',
        labelWidth: 300,
        id: 'export_form',
        region:'north',
        defaults: {anchor: '100%',xtype:'textfield'},
        fileUpload: true,
        height: 120,
        items: [
        	new Ext.form.ComboBox({
                fieldLabel: 'Daten',
                hiddenName:'typID',
                name:'typID',
                store: new Ext.data.SimpleStore({
                    fields: ['typID', 'typ'],
                    data : [[3, 'Lagerbestände'], [4, 'Kunden'] ,[5, 'Newsletter-Empfänger'], [6, 'Artikel ohne Lagerbestand'], [8, 'Artikelpreise'], [9, 'Artikelbilder']]
                }),
                valueField:'typID',
                displayField:'typ',
                mode: 'local',
                emptyText:'Bitte wählen...',
                selectOnFocus:true,
                anchor:'50%',
                allowBlank: false,
                typeAhead: true,
                triggerAction: 'all',
                forceSelection : true
        	})
        ],
        buttonAlign:'right',
        buttons: [{
            text: 'Start',
            handler: function(){
            	var form = Ext.getCmp('export_form').getForm();
                if(!form.isValid()) return;
                //Ext.MessageBox.wait("","Die Daten werden exportiert ..."); 
                form.submit({
                	url: 'export.php',
                	success: function(fp, o){
                		//Ext.MessageBox.alert("Fertiggestellt", ""); 
                	}
                });
            }
        }]
    });
    
    progress = function(config)
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
    end = function(config)
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
    var info = {
    	html: "<div style=\"padding:20px;font-family:arial,tahoma,helvetica,sans-serif;font-size:12px;font-size-adjust:none;font-style:normal;font-variant:normal;font-weight:bold;line-height:normal;color:red\">"+
			"Die Import/Export-Möglichkeiten unterstützen gegebenenfalls nicht alle von Ihnen gepflegten Felder, prüfen Sie daher bitte unbedingt die Dokumentation im <a href=\"http://www.shopware-ag.de/wiki/Datenaustausch_detail_308.html\" target=\"_blank\">Wiki</a> bevor Sie mit dem Modul arbeiten."+
	 	"</div>"
    };
    var viewport = new Ext.Viewport({
    	layout:'fit',
    	autoScroll:true,
    	items: new Ext.Panel({
	    	autoScroll: true,
	    	border: false,
    		items: [info, export_article_form, export_orders_form, export_form, form]
    	})
    });
 });
</script>
</head>
<body>
</body>
</html>