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
$sCore->sInitTranslations(1,"config_dispatch","true");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>
  <meta http-equiv="Content-Language" content="de" />
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title><?php echo $sLang["shipping"]["skeleton_forwarding_expenses"] ?></title>
	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="../../../vendor/ext/resources/css/ext-all.css" rel="stylesheet" type="text/css" />
	<link href="css/multiselect.css" rel="stylesheet" type="text/css" />
	<link href="css/premium_shipping.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
	<script type="text/javascript" src="js/TabCloseMenu.js"></script>
	<script type="text/javascript" src="js/DDView.js"></script>
	<script type="text/javascript" src="js/MultiSelect.js"></script>
	<script type="text/javascript" src="js/ItemSelector.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
 	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
	<script>

Ext.onReady(function(){

    Ext.QuickTips.init();

    var store = new Ext.data.Store({
		url: 'ajax/getFeeds.php',
		//baseParams: {pagingID:storeid},
	   	reader: new Ext.data.JsonReader({
	    	root: 'articles',
	        totalProperty: 'count',
	        id: 'feedID',
	        fields: [
	        	'feedID', 'name', 'group', 'comment', 'active', 'customergroup',  'multishop', 'position', 'type'
	        ]
	    })
   });
   function loadTab(feedID)
   {
   	edittabs.baseParams.feedID =  feedID;
   	
   	var categories = Ext.getCmp('categories');
   	categories.loader.baseParams.feedID = feedID;
   	categories.getRootNode().reload();
   	categories.fireEvent('render', categories);

   	var paymentmeans = Ext.getCmp('paymentmeans');
   	paymentmeans.fromStore.baseParams.feedID = feedID;
   	paymentmeans.toStore.baseParams.feedID = feedID;
   	paymentmeans.fromStore.reload();
   	paymentmeans.toStore.reload();

   	var countries = Ext.getCmp('countries');
   	countries.fromStore.baseParams.feedID = feedID;
   	countries.toStore.baseParams.feedID = feedID;
   	countries.fromStore.reload();
   	countries.toStore.reload();
   	
   	//var shippingcosts = Ext.getCmp('shippingcosts');
   	//shippingcosts.store.baseParams.feedID = feedID;
   	//shippingcosts.store.reload();
   	
   	Ext.getCmp('name_translation').load({url: "ajax/getTranslationFlags.php", params:{feedID: feedID, field: "name"}});
   	Ext.getCmp('description_translation').load({url: "ajax/getTranslationFlags.php", params:{feedID: feedID, field: "description"}});
   	Ext.getCmp('status_link_translation').load({url: "ajax/getTranslationFlags.php", params:{feedID: feedID, field: "status_link"}});
   	
   	loadShippingcostsTab(feedID);
   }

   function changeType(value)
   {
   	var form = edittabs.getForm();
   	form.findField("tax_calculation").show();
   	form.findField("tax_calculation").getEl().up('.x-form-item').setDisplayed(true);
   	form.findField("surcharge_calculation").show();
   	form.findField("surcharge_calculation").getEl().up('.x-form-item').setDisplayed(true);
   	form.findField("status_link").show();
   	form.findField("status_link").getEl().up('.x-form-item').setDisplayed(true);
   	form.findField("dispatch_description").show();
   	form.findField("dispatch_description").getEl().up('.x-form-item').setDisplayed(true);
   	Ext.getCmp('name_translation').show();
   	Ext.getCmp('description_translation').show();
   	Ext.getCmp('status_link_translation').show();
   	
   	form.findField("multishopID").enable();
   	form.findField("customergroupID").enable();
   	Ext.getCmp('paymentmeans').enable();
   	Ext.getCmp('countries').enable();
   	Ext.getCmp('categories').enable();
   	Ext.getCmp('settings').enable();
   	
   	if(value==1)
   	{
   		form.findField("multishopID").disable();
   		form.findField("customergroupID").disable();
   		form.findField("multishopID").setValue(null);
   		form.findField("customergroupID").setValue(null);
   		Ext.getCmp('paymentmeans').disable();
   		Ext.getCmp('countries').disable();
   		Ext.getCmp('categories').disable();
   		Ext.getCmp('settings').disable();
   		Ext.getCmp('southtab').activate('shippingcosts');
   	}
   	else if(value==2||value==3)
   	{
   		//form.findField("tax_calculation").hide();
   		//form.findField("tax_calculation").getEl().up('.x-form-item').setDisplayed(false);
   		form.findField("surcharge_calculation").hide();
   		form.findField("surcharge_calculation").getEl().up('.x-form-item').setDisplayed(false);
   		form.findField("status_link").hide();
   		form.findField("status_link").getEl().up('.x-form-item').setDisplayed(false);
   		form.findField("dispatch_description").hide();
   		form.findField("dispatch_description").getEl().up('.x-form-item').setDisplayed(false);
   		
   		Ext.getCmp('name_translation').hide();
   		Ext.getCmp('description_translation').hide();
   		Ext.getCmp('status_link_translation').hide();
   	}
   }

   function showAdoptionWindow()
   {
   		var values;
    	new Request.JSON({url: "ajax/getValues.php?name=dispatch", async: false,onSuccess: function(result){
    		values = result;
    	}}).get();
    	if(values.articles)
    	{
	    	var items = [{html: ''}];
	    	values.articles.each(function(field)
	    	{
	    		 items[items.length] = {
	    		 	xtype: 'combo',
	    		 	fieldLabel: field.name,
	    		 	name:'dispatch['+field.id+']',
	    		 	hiddenName:'dispatch['+field.id+']',
	    		 	store:  new Ext.data.Store({
	    		 		url: 'ajax/getValues.php?name=premium_dispatch',
	    		 		autoLoad: true,
	    		 		baseParams: {active:0},
	    		 		reader: new Ext.data.JsonReader({
	    		 			root: 'articles',
	    		 			totalProperty: 'count',
	    		 			id: 'id',
	    		 			fields: ['id','name']
	    		 		})
	    		 	}),
	    		 	valueField:'id',
	    		 	displayField:'name',
	    		 	mode: 'remote',
	    		 	emptyText:'Bitte wählen ...',
	    		 	selectOnFocus:true,
	    		 	editable:false,
	    		 	triggerAction:'all',
	    		 	forceSelection : true,
	                allowBlank:false,
	    		 	listeners: {
		    		 	'blur': {fn:function(el){
		    		 		if(!el.getRawValue())
		    		 			el.setValue(null);
		    		 	}}
	    		 	}
	    		 };
	    	});
    	}
    	else
    	{
    		var items = [{html: '<strong>keine Übernahme nötig</strong>'}];
    	}
    	new Ext.Window({
			id: 'adoption_window',
			title: 'Alte Versandarten übernehmen',
			width: 500,
			//height:150,
			minWidth: 500,
			minHeight: 100,
			closable: true,
			layout: 'fit',
			bodyStyle:'padding:10px 5px;',
			items: [{
				baseCls: 'x-plain',
				id: 'login_form',
				defaults: {anchor: '100%'},
				xtype: 'form',
		        labelWidth: 120,
		        url:'ajax/saveAdoption.php',
		        //defaultType: 'textfield',
		        items: items
			}],
			buttons: [{
				text: values.count ? 'Übernehmen' : 'OK',
				handler  : function(){
					Ext.getCmp('login_form').getForm().submit({
					    success: function(form, action) {
					    	Ext.getCmp('adoption_window').hide();
					    	if(values.count)
					    	{
					    		Ext.Msg.alert('', 'Übernahme&nbsp;erfolgreich!', function(r){
					    			window.location.reload();
					    		});
					    	}
					    	else
					    	{
					    		window.location.reload();
					    	}
					    },
					    failure: function(form, action) {
					        switch (action.failureType) {
					            case Ext.form.Action.CLIENT_INVALID:
					                Ext.Msg.alert("Fehler", "Bitte überprüfen Sie ihre Eingaben");
					                break;
					            case Ext.form.Action.CONNECT_FAILURE:
					                Ext.Msg.alert("Fehler", "Ein unbekannter Fehler ist aufgetreten");
					                break;
					            case Ext.form.Action.SERVER_INVALID:
					            default:
					               Ext.Msg.alert("Fehler", action.result.msg);
					       }
					    }
					});
                }
			}]
		}).show();
   }
   
   function loadShippingcostsTab(feedID)
   {
   	
   	var type = edittabs.getForm().findField("calculation").getValue().toInt();
   	switch (type)
   	{
   		case 1:
   			var decimalPrecision = 2;
   			var minChange = 0.01;
   			var startValue = 0;
   			break;
   		case 2:
   		case 3:
   			var decimalPrecision = 0;
   			var minChange = 1;
   			var startValue = 1;
   			break;
   		case 0:
   		default:
   			var decimalPrecision = 3;
   			var minChange = 0.001;
   			var startValue = 0;
   			break;
   	}
   	Ext.getCmp('southtab').remove('shippingcosts');
   	Ext.destroy(Ext.getCmp('shippingcosts'));
   	var tab = {
   		title:'Versandkosten',
   		id: 'shippingcosts',
   		xtype: 'editorgrid',
   		autoScroll: true,
   		bodyStyle:'padding:0px',
   		clicksToEdit:1,
   		forceLayout: true,
   		store: new Ext.data.Store({
   			url: 'ajax/getShippingcosts.php',
   			baseParams: {feedID: feedID, startValue: startValue, minChange: minChange},
   			autoLoad: true,
   			reader: new Ext.data.JsonReader({
   				root: 'articles',
   				totalProperty: 'count',
   				id: 'to',
   				fields: ['from', 'to', 'value', 'factor']
   			})
   		}),
   		cm: new Ext.grid.ColumnModel([
	   		{id:'from', dataIndex: 'from', header: "Von", align: 'right', width: 200, sortable: false, editor: new Ext.form.NumberField({allowBlank: true, decimalPrecision : decimalPrecision, decimalSeparator: ',', readOnly:true, allowNegative: false}),renderer: function(value, p, record){
	   			if((value&&value.toFloat())||value===0)
	   				return value.toFloat().toFixed(decimalPrecision).split(".").join(",");
	   			else
	   				return (0).toFixed(decimalPrecision).split(".").join(",");
	   		}},
	   		{id:'to', dataIndex: 'to', header: "Bis", align: 'right', width: 200, sortable: false, editor: new Ext.form.NumberField({
	   			allowBlank: true,
	   			decimalPrecision : decimalPrecision,
	   			decimalSeparator: ',',
	   			allowNegative: false,
	   			validator: function(value)
	   			{
	   				//value = value.split(",").join(".").toFloat().toFixed(decimalPrecision);
	   				if(this.gridEditor.row)
	   				{
	   					this.minValue = this.gridEditor.record.get("from");
	   				}
	   				else
	   				{
	   					this.minValue = startValue;
	   				}
	   				this.minValue = this.minValue.toFloat().toFixed(decimalPrecision);
	   				/*
	   				if(value < this.minValue)
	   				{
	   					return String.format(this.minText, this.minValue);
	   				}
	   				*/
	   				return true;
	   			}
	   		}),renderer: function(value, p, record){
	   			if((value&&value.toFloat())||value===0)
	   				return value.toFloat().toFixed(decimalPrecision).split(".").join(",");
	   			else
	   				return "beliebig";
	   		}},
	   		{id:'value', dataIndex: 'value', header: "Versandkosten", align: 'right', width: 200, sortable: false, editor: new Ext.form.NumberField({allowBlank: true, decimalPrecision : 2, decimalSeparator: ','}),renderer: function(value, p, record){
	   			if(value&&value.toFloat())
	   				return value.toFloat().toFixed(2).split(".").join(",");
	   			else
	   				return "";
	   		}},
	   		{id:'factor', dataIndex: 'factor', header: "Faktor (%)", width: 200, align: 'right', sortable: false, editor: new Ext.form.NumberField({allowBlank: true, decimalPrecision : 2})}
   		]),
   		viewConfig: {
   			forceFit:true
   		},
   		listeners : {
	   		"afteredit" : {fn: function(e)
	   		{
	   			if(e.field=="to")
	   			{
	   				if(e.grid.store.data.keys[e.row+1])
	   				{
	   					while(e.grid.store.data.keys[e.row+1])
	   					{
	   						var recordID = e.grid.store.data.keys[e.row+1]
	   						var record = e.grid.store.getById(recordID);
	   						if(record.get("to")&&record.get("to")<=e.value)
	   						{
	   							e.grid.store.remove(record);
	   						}
	   						else
	   						{
	   							record.set("from",e.value+minChange);
	   							break;
	   						}
	   					}
	   				}
	   				else if(e.value||e.value===0)
	   				{
	   					var r = Ext.data.Record.create([
	   					{name: 'from'},
	   					{name: 'to'},
	   					{name: 'value'},
	   					{name: 'factor'}
	   					]);
	   					var c = new r({
	   						from: e.value+minChange,
	   						to: "",
	   						value: "",
	   						factor: ""
	   					});
	   					e.grid.stopEditing();
	   					e.grid.store.insert(e.row+1, c);
	   					e.grid.startEditing(e.row+1, 1);
	   				}
	   			}
	   		}, scope:this}
   		},
   		tbar: [{
   			text: 'Letzte Staffel entfernen',
   			iconCls:'delete',
   			handler : function()
   			{
   				var shippingcosts = Ext.getCmp('shippingcosts');
   				var count = shippingcosts.store.getCount();
   				if(count&&count>1)
   				{
   					var recordID = shippingcosts.store.data.keys[count-2]
   					var record = shippingcosts.store.getById(recordID);
   					record.set("to","");
   					shippingcosts.store.remove(shippingcosts.store.data.items[count-1]);
   				}
   			}
   		}]
   	};
   	Ext.getCmp('southtab').insert(0,tab);
	Ext.getCmp('southtab').activate(tab.id);
   }

   var grid = new Ext.grid.GridPanel({
        id:'grid',
        closable:false,
        store: store,
        border: false,
        selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
        cm: new Ext.grid.ColumnModel([
        	//{id:'position', dataIndex: 'position', header: "Sortierung", width: 40, sortable: true},
            {id:'name', dataIndex: 'name', header: "Name", width: 200, sortable: true},
            {id:'comment', dataIndex: 'comment', header: "Kommentar", width: 300, sortable: true},
            {id:'active', dataIndex: 'active', header: "Aktiv", width: 50, sortable: true, renderer: function (v){ return v ? 'Ja' : 'Nein'}},
            {id:'type', dataIndex: 'type', header: "Typ", width: 200, sortable: true, renderer: function (v){
            	switch (v)
            	{
            		case 3:
            			return 'Abschlag-Versandregel';
            		case 2:
            			return 'Aufschlag-Versandregel';
            		case 1:
            			return 'Ausweich-Versandart';
            		case 0:
            		default:
            			return 'Standard-Versandart';
            	}
           	}},
            //{id:'group', dataIndex: 'group', header: "Gruppe", width: 40, sortable: true},
            {id:'multishop', dataIndex: 'multishop', header: "Shop", width: 200, sortable: true},
            {id:'customergroup', dataIndex: 'customergroup', header: "Kundengruppe", width: 200, sortable: true}
        ]),
        //viewConfig: { forceFit:true },
        tbar:[{
            text:'Versandart hinzufügen',
            iconCls:'add',
            handler: function (){
            	edittabs.getForm().reset();
            	loadTab(0);
            	edittabs.enable();
            	tabs.activate(1);
            	changeType(0);
            },
        },'-',{
            text:'Versandart duplizieren',
            iconCls:'folders_plus',
            handler: function (){
            	if(!Ext.getCmp('grid').selModel.getSelected())
            		return;
            	var feedID = Ext.getCmp('grid').selModel.getSelected().id;
            	edittabs.getForm().load({url:'ajax/getFeeds.php', params: {feedID: feedID}, waitMsg:'Laden...', success: function(){
            		loadTab(feedID);
					edittabs.baseParams.duplicateFeed = feedID;
            		edittabs.baseParams.feedID = 0;
            		Ext.getCmp('categories').loader.baseParams.feedID = 0;
	            	Ext.getCmp('paymentmeans').fromStore.baseParams.feedID = 0;
	   				Ext.getCmp('paymentmeans').toStore.baseParams.feedID = 0;
	   				Ext.getCmp('countries').fromStore.baseParams.feedID = 0;
	   				Ext.getCmp('countries').toStore.baseParams.feedID = 0;
	   				Ext.getCmp('shippingcosts').store.baseParams.feedID = 0;
	   	   			Ext.getCmp('name_translation').load({url: "ajax/getTranslationFlags.php", params:{feedID: 0, field: "name"}});
   					Ext.getCmp('description_translation').load({url: "ajax/getTranslationFlags.php", params:{feedID: 0, field: "description"}});
   					Ext.getCmp('status_link_translation').load({url: "ajax/getTranslationFlags.php", params:{feedID: 0, field: "status_link"}});
	   				edittabs.enable();
            		tabs.activate(1);
            		changeType(edittabs.getForm().findField("type").getValue());
            	}});
            },
        },'-',{
            text:'Versandart editieren',
            handler: function (){
            	if(!Ext.getCmp('grid').selModel.getSelected())
            		return;
 				var feedID = Ext.getCmp('grid').selModel.getSelected().id;
            	edittabs.getForm().load({url:'ajax/getFeeds.php', params: {feedID: feedID}, waitMsg:'Laden...', success: function(){
            		loadTab(feedID);
            		edittabs.enable(); 
            		tabs.activate(1);
            		changeType(edittabs.getForm().findField("type").getValue());
            	}});
            },
            iconCls:'pencil'
        },'-',{
            text:'Versandart löschen',
            handler: function (a, b, c){
            	Ext.MessageBox.confirm('', 'Wollen Sie wirklich diese Versandart löschen?', function(r){
            		if(r=='yes')
            		{
            			var feedID = Ext.getCmp('grid').selModel.getSelected().id;
            			store.load({params:{"delete": feedID}});
            			if(feedID==edittabs.baseParams.feedID)
            				edittabs.disable();
            		}
	            });
            },
            iconCls:'refresh'
        },'-',{
            text:'Aktualisieren',
            handler: function (a, b, c){
            	store.load();
            }
        }
        <?php if(empty($sCore->sCONFIG['sPREMIUMSHIPPIUNGADOPTION'])) { ?>
        ,'-',{
            text:'Alte Versandarten übernehmen',
            handler: function (a, b, c){
            	showAdoptionWindow();
            }//,iconCls:'delete'
        }
        <?php } ?>
        ],
        title:'Versandarten',
    });
    
    var tabs = new Ext.TabPanel({
    	region:'center',
        enableTabScroll:true,
        deferredRender:false,
        activeTab:0,
        defaults: {autoScroll:true},
        plugins: new Ext.ux.TabCloseMenu()
    });
    
    var weekdays = Array();
    weekdays[0]=Array();
    weekdays[0][0]=0;
    weekdays[0][1]='keine Auswahl';
    Date.dayNames.each(function(d,i){
    	if(!i) return;
    	weekdays[i]=Array();
    	weekdays[i][0]=i;
    	weekdays[i][1]=d;
    });
    weekdays[7]=Array();
    weekdays[7][0]=7;
    weekdays[7][1]=Date.dayNames[0];
    
    var edittabs = new Ext.FormPanel({
        title: 'Einstellungen',
        id: 'edittab',
        border:false,
        disabled : true,
        layout:'border',
        reader: new Ext.data.JsonReader({
	    	root: 'articles',
	        totalProperty: 'count',
	        id: 'feedID',
	        autoLoad: false,
	        fields: [
	        	'feedID', "name", "description", "comment", "active", "position", "calculation", "surcharge_calculation",
	        	"multishopID", "customergroupID", "bind_shippingfree", "bind_instock", "bind_laststock",
	        	"bind_weekday_from", "bind_weekday_to", "bind_weight_from", "bind_weight_to",
	        	"status_link", "holidays", "bind_price_from", "bind_price_to", "bind_sql", "shippingfree", "tax_calculation",
	        	"type", "calculation_sql",
	        	{name: 'bind_time_from', type: 'date', dateFormat: 'timestamp'},
	        	{name: 'bind_time_to', type: 'date', dateFormat: 'timestamp'}
	        ]
	    }),
	    autoLoad: false,
        baseParams: {feedID:0},
        url:'getFeeds.php',
        labelWidth: 180,
        items: [{
            layout:'column',
            border:false,
            region: 'north',
            height: 200,
            bodyStyle:'padding:10px',
            items:[{
                columnWidth:.5,
                layout: 'form',
                border:false,
                defaults: {anchor: '100%',xtype:'textfield'},
                items: [{
                    fieldLabel: 'Name',
                    name: 'name',
                    id: 'dispatch_name',
                    allowBlank: false
                },{
                    fieldLabel: 'Beschreibung',
                    name: 'description',
                    id: 'dispatch_description',
                    xtype:'textarea',
       				height: 50
                },{
	               	fieldLabel: 'Tracking-URL',
	               	name: 'status_link',
	               	id: 'dispatch_status_link',
                },{
                    fieldLabel: 'Kommentar',
                    name: 'comment'
                },{
                	fieldLabel: 'Sortierung',
                	name: 'position',
                	xtype: 'numberfield'
                }, {
                	fieldLabel: 'Aktiv',
                	name: 'active',
                	xtype: 'checkbox'
                }]
            },{
            	layout: 'form',
            	width: 150,
                border:false,
                defaults: {anchor: '100%',xtype:'panel',border:false,height:(22+4),bodyStyle:'padding-top:4px'},
                items: [
                	{id: 'name_translation'},
                	{id: 'description_translation', height: (50+4)},
                	{id: 'status_link_translation'},
                	{id: 'padding_translation'}
                ]
            },{          	
                columnWidth:.5,
                layout: 'form',
                border:false,
                defaults: {anchor: '100%',xtype:'textfield'},
                items: [
                	new Ext.form.ComboBox({
                        fieldLabel: 'Shop',
                        name:'multishopID',
                        hiddenName:'multishopID',
                        store:  new Ext.data.Store({
							url: 'ajax/getValues.php?name=multishop',
							autoLoad: true,
							baseParams: {active:0},
						   	reader: new Ext.data.JsonReader({
						    	root: 'articles',
						        totalProperty: 'count',
						        id: 'id',
						        fields: ['id','name']
						    })
			            }),
                        valueField:'id',
                        displayField:'name',
                        mode: 'remote',
                        emptyText:'allgemein gültig',
                        selectOnFocus:true,
                        editable:false,
                        value: 0,
                        triggerAction:'all',
                        forceSelection : true,
                        listeners: {
	                        'blur': {fn:function(el){
	                        	if(!el.getRawValue())
	                        		el.setValue(null);
	                        }}
                        }
                    })
                 ,
                 	new Ext.form.ComboBox({
                        fieldLabel: 'Kundengruppe',
                        name:'customergroupID',
                        hiddenName:'customergroupID',
                        store:  new Ext.data.Store({
							url: 'ajax/getValues.php?name=customergroup',
							autoLoad: true,
							baseParams: {active:0},
						   	reader: new Ext.data.JsonReader({
						    	root: 'articles',
						        totalProperty: 'count',
						        id: 'id',
						        fields: ['id','name']
						    })
			            }),
                        valueField:'id',
                        displayField:'name',
                        mode: 'remote',
                        emptyText:'allgemein gültig',
                        editable:false,
                        value: 0,
                        selectOnFocus:true,
                        triggerAction:'all',
                        forceSelection : true,
                        listeners: {
	                        'blur': {fn:function(el){
	                        	if(!el.getRawValue())
	                        		el.setValue(null);
	                        }}
                        }
                    })
                 ,
                  	new Ext.form.ComboBox({
                        fieldLabel: 'Versandkosten-Berechung nach',
                        name:'calculation',
                        hiddenName:'calculation',
                        store: new Ext.data.SimpleStore({
                            fields: ['id', 'name'],
                            data : [[0, 'Gewicht'],[1, 'Preis'],[2, 'Artikelanzahl'],[3, 'eigene Berechung']]
                        }),
                        value: 1,
                        valueField:'id',
                        displayField:'name',
                        mode: 'local',
                        //emptyText:'',
                        selectOnFocus:true,
                        allowBlank: false,
                        typeAhead: false,
                        triggerAction: 'all',
                        forceSelection : true,
                        value: 0,
                        listeners: {
	                        'change': {fn:function(el, value, oldValue){
	                        	Ext.MessageBox.confirm('', 'Bitte beachten Sie, dass Ihre bereits eingegebene Staffel gelöscht wird, wenn Sie die Versandkosten-Berechung ändern. <br /> Wollen Sie die Versandkosten-Berechung weiterhin ändern?', function(r){
				            		if(r=='yes')
				            		{
				            			loadShippingcostsTab(0);
				            		}
				            		else
				            		{
				            			el.setValue(oldValue);
				            		}
					            });
	                        }}
                        }
                    })
                   ,{
                    	fieldLabel: 'Versandkostenfrei ab',
                    	name: 'shippingfree',
                    	xtype: 'numberfield',
                    	decimalPrecision : 2,
                    	decimalSeparator: ',',
                    	allowNegative: false
                    },
                    new Ext.form.ComboBox({
                        fieldLabel: 'Versandart-Typ',
                        name:'type',
                        hiddenName:'type',
                        store: new Ext.data.SimpleStore({
                            fields: ['id', 'name'],
                            data : [[0, 'Standard-Versandart'],[1, 'Ausweich-Versandart'],[2, 'Aufschlag-Versandregel'],[3, 'Abschlag-Versandregel']]
                        }),
                        value: 1,
                        valueField:'id',
                        displayField:'name',
                        mode: 'local',
                        //emptyText:'',
                        selectOnFocus:true,
                        allowBlank: false,
                        typeAhead: false,
                        triggerAction: 'all',
                        forceSelection : true,
                        value: 0,
                        listeners: {
	                        'change': {fn:function(el, value, oldValue){
	                        	changeType(value);
	                        }}
                        }
                    })
                   ,
                  	new Ext.form.ComboBox({
                        fieldLabel: 'Zahlungsart-Aufschlag',
                        name:'surcharge_calculation',
                        hiddenName:'surcharge_calculation',
                        store: new Ext.data.SimpleStore({
                            fields: ['id', 'name'],
                            data : [[0, 'immer berechnen'],[1, 'nicht bei versandkostenfreien Artikeln berechnen'],[2, 'nie berechnen'],[3, 'als eigene Warenkorb-Position ausgeben']]
                        }),
                        value: 1,
                        valueField:'id',
                        displayField:'name',
                        mode: 'local',
                        //emptyText:'',
                        selectOnFocus:true,
                        allowBlank: false,
                        typeAhead: false,
                        triggerAction: 'all',
                        forceSelection : true,
                        value: 0
                    }),
                    new Ext.form.ComboBox({
                        fieldLabel: 'Steuersatz',
                        name:'tax_calculation',
                        hiddenName:'tax_calculation',
                        store:  new Ext.data.Store({
							url: 'ajax/getValues.php?name=tax',
							autoLoad: true,
						   	reader: new Ext.data.JsonReader({
						    	root: 'articles',
						        totalProperty: 'count',
						        id: 'id',
						        fields: ['id','name']
						    })
			            }),
                        valueField:'id',
                        displayField:'name',
                        mode: 'remote',
                        //emptyText:'',
                        editable:false,
                        //value: 0,
                        selectOnFocus:true,
                        triggerAction:'all',
                        forceSelection : true
                    })
                ]
            }]
        },{
            xtype:'tabpanel',
        	deferredRender: false,
            region:'center',
            id: 'southtab',
            split:true,
            height: 200,
            minSize: 200,
            maxSize: 700,
            autoScroll: true,
            collapsible: false,
            activeTab: 0,
            defaults:{bodyStyle:'padding:10px'},
	        items:[{
	            xtype:"itemselector",
	            title:'Zahlungsart Auswahl',
            	id:"paymentmeans",
	            layout:'fit',
	            hideLabel: true,
	            name:"paymentmeans",
	            id:"paymentmeans",
	            valueField:"id",
	            displayField:"name",
	            imagePath:"img/",
	            allowDup: false,
	            toLegend:"Ausgewählt",
	            fromLegend:"Verfügbar",
	            toSortField:"name",
			    fromSortField:"name",
	            toStore: new Ext.data.Store({
					url: 'ajax/getValues.php',
					autoLoad: false,
					baseParams: {active:1,name:'paymentmean'},
				   	reader: new Ext.data.JsonReader({
				    	root: 'articles',
				        totalProperty: 'count',
				        id: 'id',
				        fields: ['id', 'name']
				    })
	            }),
	            fromStore: new Ext.data.Store({
					url: 'ajax/getValues.php',
					autoLoad: false,
					baseParams: {active:0,name:'paymentmean'},
				   	reader: new Ext.data.JsonReader({
				    	root: 'articles',
				        totalProperty: 'count',
				        id: 'id',
				        fields: ['id','name']
				    })
	            })
	         },{
	            xtype:"itemselector",
	            title:'Länder Auswahl',
	            layout:'fit',
	            name:"countries",
	            id:"countries",
	            valueField:"id",
	            displayField:"name",
	            allowDup: false,
	            imagePath:"img/",
		        toLegend:"Ausgewählt",
		        fromLegend:"Verfügbar",
		        toSortField:"name",
				fromSortField:"name",
	            toStore: new Ext.data.Store({
					url: 'ajax/getValues.php',
					autoLoad: false,
					baseParams: {active:1,name:'countries'},
				   	reader: new Ext.data.JsonReader({
				    	root: 'articles',
				        totalProperty: 'count',
				        id: 'id',
				        fields: ['name', 'id']
				    })
	            }),
	            fromStore: new Ext.data.Store({
					url: 'ajax/getValues.php',
					autoLoad: false,
					baseParams: {active:0,name:'countries'},
				   	reader: new Ext.data.JsonReader({
				    	root: 'articles',
				        totalProperty: 'count',
				        id: 'id',
				        fields: ['name','id']
				    })
	            })
            },{
           		xtype: 'treepanel',
            	title:'Kategorien sperren',
            	name:'categories',
            	id:'categories',
            	layout:'fit',
            	loader: new Ext.tree.TreeLoader({
            		dataUrl:'ajax/getCategories.php',
            		baseAttr: {
            			checked: false,
						uiProvider: Ext.tree.TreeNodeUI
					}
            	}),
            	root: new Ext.tree.AsyncTreeNode({
            		text: 'root',
            		draggable:false,
            		id:'1'
            	}),
            	selModel: new Ext.tree.MultiSelectionModel(),
            	enableDD: false,
            	autoScroll: true,
            	containerScroll: true,
            	rootVisible:false,
	            listeners: {
		          	'render': {fn:function(tree){
		          		var form = edittabs.getForm();
		          		Ext.Ajax.request({
						   url: 'ajax/getCategoryPaths.php',
						   success: function(result){
							   if(!result.responseText)
								   return;
							   result =  Ext.util.JSON.decode(result.responseText);
							   if(!result||result.length<1)
								   return;
							   for(var i = 0; i < result.length; i++){
							   	   tree.expandPath(result[i]);
							   }
						   },
						   params: { feedID: form.baseParams.feedID }
						});
		          	}}
	            }
			},{
	            layout:'column',
	            border:false,
	            height: 200,
	            bodyStyle:'padding:10px',
	            title:'Erweiterte Einstellungen',
	            autoScroll: true,
	            id: 'settings',
	            items:[{
	               	columnWidth:.5,
	                layout:'form',
	                border:false,
	                defaults: {anchor: '95%',xtype:'textfield'},
	                defaultType: 'textfield',
	                items: [
	                    {
		               		fieldLabel: 'Nur Abverkaufsartikel',
		                	name: 'bind_laststock',
		                	xtype: 'checkbox'
	               		}, 
	                    new Ext.form.ComboBox({
	                    	fieldLabel: 'Versandkostenfreie Artikel',
	                    	name:'bind_shippingfree',
	                    	hiddenName:'bind_shippingfree',
	                    	store: new Ext.data.SimpleStore({
	                    		fields: ['id', 'name'],
	                    		data : [[0, 'unterstützen'],[1, 'nicht unterstützen und Versandart sperren'],[2, 'unterstützen aber Versandkosten trotzdem berechnen']]
	                    	}),
	                    	value: 1,
	                    	valueField:'id',
	                    	displayField:'name',
	                    	mode: 'local',
	                    	selectOnFocus:true,
	                    	allowBlank: false,
	                    	typeAhead: false,
	                    	triggerAction: 'all',
	                    	forceSelection : true,
	                    	value: 0
	                    }),
	               		new Ext.form.ComboBox({
	                        fieldLabel: 'Bestand größer',
	                        name:'bind_instock',
	                        hiddenName:'bind_instock',
	                        store: new Ext.data.SimpleStore({
	                            fields: ['id', 'name'],
	                            data : [[0, 'keine Auswahl'],[1, 'Bestellmenge'],[2, 'Bestellmenge+Mindestbestand']]
	                        }),
	                        valueField:'id',
	                        displayField:'name',
	                        mode: 'local',
	                        emptyText:'keine Auswahl',
	                        value: 0,
	                        selectOnFocus:true,
	                        allowBlank: true,
	                        typeAhead: false,
	                        triggerAction: 'all',
	                        editable:false,
	                        forceSelection : true,
	                        listeners: {
		                        'blur': {fn:function(el){
		                        	if(!el.getRawValue())
		                        		el.setValue(null);
		                        }}
	                        }
	                    })
		               	,{
		                    fieldLabel: 'Uhrzeit von',
		                    name: 'bind_time_from',
		                    hiddenName:'bind_time_from',
		                    xtype: 'timefield',
		                    format: 'H:i',
		                    increment: 60
		                },{
		                    fieldLabel: 'Uhrzeit bis',
		                    name: 'bind_time_to',
		                    hiddenName:'bind_time_to',
		                    xtype: 'timefield',
		                    format: 'H:i',
		                    increment: 60
		                },{
		                    fieldLabel: 'Gewicht von',
		                    name: 'bind_weight_from',
		                    hiddenName:'bind_weight_from',
		               		decimalPrecision : 3,
		                	xtype: 'numberfield',
		                	decimalSeparator: ',',
		                	allowNegative: false,
			                listeners: {
				            	'change': {fn:function(el){
				            		var minValue = el.getValue();
				            		var to = edittabs.getForm().findField("bind_weight_to");
				            		to.minValue = minValue;
				            		if(to.isValid(true))
				            		{
				            			to.validate();
				            		}
				            	}}
			                }
		                },{
		                    fieldLabel: 'Gewicht bis',
		                    name: 'bind_weight_to',
		                    hiddenName:'bind_weight_to',
		               		decimalPrecision : 3,
		                	xtype: 'numberfield',
		                	decimalSeparator: ',',
		                	allowNegative: false,
		                    listeners: {
				            	'change': {fn:function(el){
				            		var maxValue = el.getValue();
				            		if(!maxValue) maxValue = Number.MAX_VALUE;
				            		var from = edittabs.getForm().findField("bind_weight_from");
				            		from.maxValue = maxValue;
				            		if(from.isValid(true))
				            		{
				            			from.validate();
				            		}
				            	}}
			            	}
		                },{
		                    fieldLabel: 'Preis von',
		                    name: 'bind_price_from',
		                    hiddenName:'bind_price_from',
		               		decimalPrecision : 2,
		                	xtype: 'numberfield',
		                	decimalSeparator: ',',
		                	allowNegative: false,
			                listeners: {
				            	'change': {fn:function(el){
				            		var minValue = el.getValue();
				            		var to = edittabs.getForm().findField("bind_price_to");
				            		to.minValue = minValue;
				            		if(to.isValid(true))
				            		{
				            			to.validate();
				            		}
				            	}}
			                }
		                },{
		                    fieldLabel: 'Preis bis',
		                    name: 'bind_price_to',
		                    hiddenName:'bind_price_to',
		               		decimalPrecision : 2,
		                	xtype: 'numberfield',
		                	decimalSeparator: ',',
		                	allowNegative: false,
		                    listeners: {
				            	'change': {fn:function(el){
				            		var maxValue = el.getValue();
				            		if(!maxValue) maxValue = Number.MAX_VALUE;
				            		var from = edittabs.getForm().findField("bind_price_from");
				            		from.maxValue = maxValue;
				            		if(from.isValid(true))
				            		{
				            			from.validate();
				            		}
				            	}}
			            	}
		                },
		                new Ext.form.ComboBox({
	                        fieldLabel: 'Wochentage von',
	                        name:'bind_weekday_from',
	                        hiddenName:'bind_weekday_from',
	                        store: new Ext.data.SimpleStore({
	                            fields: ['id', 'name'],
	                            data : weekdays
	                        }),
	                        valueField:'id',
	                        displayField:'name',
	                        mode: 'local',
	                        emptyText:'keine Auswahl',
	                        selectOnFocus: true,
	                        allowBlank: true,
	                        typeAhead: false,
	                        triggerAction: 'all',
	                        value: 0,
	                        editable:false,
	                        forceSelection : true,
	                    }),
	                    new Ext.form.ComboBox({
	                        fieldLabel: 'Wochentage bis',
	                        name:'bind_weekday_to',
	                        hiddenName:'bind_weekday_to',
	                        store: new Ext.data.SimpleStore({
	                            fields: ['id', 'name'],
	                            data : weekdays
	                        }),
	                        valueField:'id',
	                        displayField:'name',
	                        mode: 'local',
	                        emptyText:'keine Auswahl',
	                        selectOnFocus:true,
	                        allowBlank: true,
	                        typeAhead: false,
	                        triggerAction: 'all',
	                        value: 0,
	                        editable:false,
	                        forceSelection : true,
	                    })
	                ]
	            },{
	               	columnWidth:.5,
	                layout:'form',
	                border:false,
	                defaults: {anchor: '95%',xtype:'textfield'},
	                defaultType: 'textfield',
	                items: [{
				            xtype:"multiselect",
				            fieldLabel:"Feiertage sperren",
				            name:"holidays",
				            dataFields:["id", "name"], 
				            valueField:"id",
				            displayField:"name",
				            width:300,
				            height:100,
				            //anchor:'50%',
				            allowBlank:true,
				            store: new Ext.data.Store({
								url: 'ajax/getValues.php',
								autoLoad: true,
								baseParams: {name:'holiday'},
							   	reader: new Ext.data.JsonReader({
							    	root: 'articles',
							        totalProperty: 'count',
							        id: 'id',
							        fields: ['name', 'id']
							    })
				            })
				            /*tbar:[{
				                text:"clear",
				                handler:function(){
					                edittabs.getForm().findField("bind_weekday").reset();
					            }
				            }]*/
		                },{
		                	fieldLabel: 'Eigene Bedingungen',
		                	name: 'bind_sql',
		                	xtype: 'textarea'
		                },{
		                	fieldLabel: 'Eigene Versandkosten-Berechung',
		                	name: 'calculation_sql',
		                	xtype: 'textarea'
		                }
	                ]
	            }]
            }]
        }],
        buttonAlign:'right',
        buttons: [{
            text: 'Speichern',
            handler: function(){
            	var form = edittabs.getForm();
            	var categories = "";
            	if(Ext.getCmp('categories'))
            	{
            		var nodes = Ext.getCmp('categories').getChecked();
            		for(var i = 0; i < nodes.length; i++){
            			if(i!=0) categories += ",";
            			categories += nodes[i].id;
            		}
            	}
            	form.baseParams.categories = categories;
	            form.submit({url:'ajax/saveFeeds.php', waitMsg:'Speichern...', success: function (el, r){
	            	if(r&&r.response&&r.response.responseText&&r.response.responseText!='')
	            	{
	            		parent.parent.Growl ('Versandkosten Einstellungen wurden erfolgreich gespeichert'); 
	            		var feedID = r.result.feedID.toInt();
	            		edittabs.baseParams.feedID =  feedID;
	            		Ext.getCmp('categories').loader.baseParams.feedID = feedID;
            			Ext.getCmp('paymentmeans').fromStore.baseParams.feedID = feedID;
   						Ext.getCmp('paymentmeans').toStore.baseParams.feedID = feedID;
   						Ext.getCmp('countries').fromStore.baseParams.feedID = feedID;
   						Ext.getCmp('countries').toStore.baseParams.feedID = feedID;
   						Ext.getCmp('shippingcosts').store.baseParams.feedID = feedID;
	            	}
	            	grid.store.load();
	            	Ext.getCmp('shippingcosts').store.each(function(record){
	            		var params = record.data;
	            		params.feedID = Ext.getCmp('shippingcosts').store.baseParams.feedID;
	            		new Request({method: 'post', url: 'ajax/saveShippingcosts.php', async: false, data: params}).send();
	            	});
	            	Ext.getCmp('shippingcosts').store.commitChanges();
	            }});
	        }
        }]
    });
    
    
    tabs.add(grid).show();
    tabs.add(edittabs).show();
    store.load();
    edittabs.getForm().load({url:'ajax/getFeeds.php', waitMsg:'Laden...'});

    var viewport = new Ext.Viewport({
    	layout:'fit',
    	items:	tabs
    });

});
</script>
</head>
<body>
<?php include("../../../backend/elements/window/translations.htm");?>
<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>
</html>