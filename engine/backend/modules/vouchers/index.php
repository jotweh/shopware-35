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
  <title><?php echo $sLang["vouchers"]["skeleton_voucher"] ?></title>
	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="../../../vendor/ext/resources/css/ext-all.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		/*for selecting cells */
		.x-grid3-row td,
		.x-grid3-summary-row td,
		.x-grid3-cell-text,
		.x-grid3-hd-text,
		.x-grid3-hd,
		.x-grid3-row {
			-moz-user-select:inherit;
			-khtml-user-select:text;
		}
    </style>
	<!--<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />-->
	<link href="css/vouchers.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
	<script type="text/javascript" src="js/TabCloseMenu.js"></script>
	<script type="text/javascript" src="js/DDView.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
 	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
	<script>
	Ext.onReady(function(){
		Ext.QuickTips.init();
		var store = new Ext.data.Store({
			url: 'ajax/getVouchers.php',
			remoteSort: true,
			reader: new Ext.data.JsonReader({
				root: 'articles',
				totalProperty: 'count',
				id: 'id',
				fields: [
				'id', 'description', 'vouchercode', 'numberofunits', 'customergroup', 'value', 'valid_from', 'valid_to', 'checkedIn', 'percental', 'modus','subshop'
				]
			})
		});

		// shorthand alias
		var fm = Ext.form, Ed = Ext.grid.GridEditor;

		var codeListStore = new Ext.data.Store({
			url: 'ajax/getValues.php?name=voucherList',
			autoLoad: true,
			remoteSort: true,
			reader: new Ext.data.JsonReader({
				root: 'articles',
				totalProperty: 'count',
				id: 'id',
				fields: ['id', 'voucherID', 'customernumber','code', 'firstname', 'lastname', 'cashed']
			})
		});
		
		function searchFilter () {
		    var search = Ext.getCmp("search");
		    store.baseParams["search"] = search.getValue();
		    store.reload();
		}
		
		function searchCodeFilter () {
		    var search = Ext.getCmp("searchCodes");
		    codeListStore.baseParams["search"] = search.getValue();
		    codeListStore.reload();
		}
		
		var pagesize = 50;
		var paging_toolbar = new Ext.PagingToolbar({
             pageSize: pagesize,
             displayInfo: true,
             emptyMsg: 'Keine Daten vorhanden',
             store: codeListStore,
             items:[
	            'Suche: ',
		        {
		        	xtype: 'textfield',
		        	id: 'searchCodes',
		        	selectOnFocus: true,
		        	width: 120,
		        	listeners: {
		            	'render': {fn:function(ob){
		            		ob.el.on('keyup', searchCodeFilter, this, {buffer:500});
		            	}, scope:this}
		        	}
		        }
	        ]
		});
		
		
		

		var paging_voucher_toolbar = new Ext.PagingToolbar({
	        pageSize: 20,
	        store: store,
	        displayInfo: true,
	        displayMsg: 'Gutschein {0} - {1} von insgesamt {2}',
	        emptyMsg: "Keine Tickets angelegt",
	        items:[
            'Suche: ',
	        {
	        	xtype: 'textfield',
	        	id: 'search',
	        	selectOnFocus: true,
	        	width: 120,
	        	listeners: {
	            	'render': {fn:function(ob){
	            		ob.el.on('keyup', searchFilter, this, {buffer:500});
	            	}, scope:this}
	        	}
	        }
	        ]
	    });
		
		
		
		function loadTab(feedID)
		{
			edittabs.baseParams.feedID =  feedID;
			var voucherList = Ext.getCmp('voucherList');
			voucherList.store.baseParams.feedID =  feedID;
			voucherList.store.load({params:{start:0,limit:pagesize}});
			
			setCodeGenBtnHide(feedID);
		}
		
		function setCodeGenBtnHide(feedID)
		{
			Ext.Ajax.request({
			   url: 'ajax/getValues.php?name=voucherList',
			   success: function(r, o ){
			   		var json = Ext.decode(r.responseText);
			   		if(json.count != '0')
			   		{
			   			Ext.getCmp('codegenerate').disable();
			   		}else{
			   			Ext.getCmp('codegenerate').enable();
			   		}
			   },
			   params: { feedID: feedID }
			});			
		}
		
		
		// Show and Hide Fields
		function show(){
			hideField(Ext.getCmp('dispatch_vouchercode'));
    		hideField(Ext.getCmp('dispatch_numorder'));
    		Ext.getCmp('voucherList').enable();
			Ext.getCmp('voucherList').show();
		}
		function hide(){
			showField(Ext.getCmp('dispatch_vouchercode')); 
    		showField(Ext.getCmp('dispatch_numorder'));
    		Ext.getCmp('voucherList').disable();
    		Ext.getCmp('voucherList').hide();
		}
		
		function hideField(field)
		{
			field.setDisabled(true);
			field.disable();// for validation
			field.hide();
			field.getEl().up('.x-form-item').setDisplayed(false); // hide label
		}
		
		function showField(field)
		{
			field.setDisabled(false);
			field.enable();
			field.show();
			field.getEl().up('.x-form-item').setDisplayed(true);// show label
		}
		
		var voucherCodeCount = 0;// variable to check that the Ordercode is unique
		var orderCodeCount = 0;// variable to check that the Ordercode is unique
		
		function countOrderCode(textField, ordercode) {
			var id = edittabs.baseParams.feedID
			conn = new Ext.data.Connection();
			
			if(ordercode) {
	        	conn.request({
				   url: 'ajax/getValues.php?name=ordercode&value='+ordercode+'&id='+id,
				   success: function(result){
				   result =  Ext.util.JSON.decode(result.responseText);
					   orderCodeCount = result.count;
					   textField.validate();
				   }
				});
			}
			orderCodeCount = -1; // undefined value
		}
		
		function countCode(textField, code) {
			var id = edittabs.baseParams.feedID
			conn = new Ext.data.Connection();
			
			if(code) {
	        	conn.request({
				   url: 'ajax/getValues.php?name=vouchercode&value='+code+'&id='+id,
				   success: function(result){
				   result =  Ext.util.JSON.decode(result.responseText);
					   voucherCodeCount = result.count;
					   textField.validate();
				   }
				});
			}
			voucherCodeCount = -1; // undefined value
		}
		
		//********************Renderer************************
		function formatVoucherCount(input) {
			var prefix;
			var hundred;
			input = input.toString();
			if(input.length > 3) {
				prefix = input.substr(0,input.length-3);
				hundred = input.substr(input.length-3,input.length);
				return prefix+"."+hundred;
			}
			return input;
			
		}
		 // renderer function checkedInVouchers
	    function checkedInVouchers(value,p,r) {
	    	var checkedInVouchers = r.data.checkedIn;
	        if(value > checkedInVouchers){
	        	checkedInVouchers = formatVoucherCount(checkedInVouchers);
	    		value = formatVoucherCount(value);
	            return '<span style=\"color:green;\">' + checkedInVouchers + ' / '+ value +'</span>';
	        }
	        else {
	        	checkedInVouchers = formatVoucherCount(checkedInVouchers);
	    		value = formatVoucherCount(value);
	            return '<span style="color:red;">' + checkedInVouchers + ' / '+ value +'</span>';
	        }
	    }
	    
	    // renderer function percental
	    function cashed(value,p,r) {
	        if(value == 1){
	            return 'Ja' ;
	        }
	        else {
	            return 'Nein';
	        }
	    }
	
	 // renderer function percental
	    function percental(value,p,r) {
	    	var percental = r.data.percental;
	        if(percental == 1){
	            return '% '+ value ;
	        }
	        else {
	            return '&euro; '+ value;
	        }
	    }
		//***************************End Renderer************************
	
	
		function renderType(v,p,r,rowIndex,i,ds){
			if (r.data.modus == "0"){
				return 'Allgemein gültig';
			}else  {
				return 'Individuell';
			}
		}

		var grid = new Ext.grid.GridPanel({
			id:'grid',
			closable:false,
			loadMask: true,
			bbar:paging_voucher_toolbar,
			store: store,
			selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
			cm: new Ext.grid.ColumnModel([
			{id:'description', dataIndex: 'description', header: "Bezeichnung", width: 40, sortable: true},
			{id:'vouchercode', dataIndex: 'vouchercode', header: "Code", width: 40, sortable: true},
			{id:'modus', dataIndex: 'modus', header: "Gutschein-Modus", width: 40, sortable: true, renderer: renderType},
			{id:'numberofunits', renderer: checkedInVouchers, dataIndex: 'numberofunits', header: "Eingelöst", width: 40, sortable: true},
			{id:'value', renderer: percental, dataIndex: 'value', header: "Wert", width: 40, sortable: true},
			{id:'valid_from', dataIndex: 'valid_from', header: "Von", width: 40, sortable: true},
			{id:'valid_to', dataIndex: 'valid_to', header: "Bis", width: 40, sortable: true},
			]),
			viewConfig: {
				forceFit:true
			},
			tbar:[{
				text:'Gutschein hinzufügen',
				iconCls:'add',
				handler: function (){
					loadTab(0);
					edittabs.getForm().reset();
					edittabs.enable();
					tabs.activate(1);
					Ext.getCmp('modusCB').setValue(0); // Default Value
					hide();
				},
			},  '-', {
				text:'Gutschein duplizieren',
				iconCls:'folders_plus',
				handler: function (){
					var feedID = Ext.getCmp('grid').selModel.getSelected().id;
					edittabs.baseParams.feedID = feedID;
					edittabs.getForm().load({url:'ajax/getVouchers.php?clone=1', waitMsg:'Laden...', success: function(){
						edittabs.baseParams.feedID = 0;
						Ext.getCmp('dispatch_ordercode').markInvalid("Der Wert existiert bereits.");
						edittabs.enable();
						tabs.activate(1);
						if(Ext.getCmp('modusCB').getValue() == 0){
							hide();
						}
						else{
							show();
						}
						Ext.getCmp('codegenerate').enable();
						try{
						Ext.getCmp('voucherList').store.removeAll();
						}catch(e){}
					}});
				},
			},  '-', {
				text:'Gutschein editieren',
				handler: function (){
					if(!Ext.getCmp('grid').selModel.getSelected())
					return;
					var feedID = Ext.getCmp('grid').selModel.getSelected().id;
					loadTab(feedID);
					edittabs.getForm().load({url:'ajax/getVouchers.php', waitMsg:'Laden...', success: function(){
						edittabs.enable();
						tabs.activate(1);
						if(Ext.getCmp('modusCB').getValue() == 0){
							hide();
						}
						else{
							show();
						}
					
						
					}});
				},
				iconCls:'pencil'
			},'-',{
				text:'Gutschein löschen',
				handler: function (a, b, c){
					Ext.MessageBox.confirm('Bestätigung', 'Wollen Sie den Gutschein wirklich löschen?', function(r) {
						if(r=='yes')
						{
							var deletedVoucherID = Ext.getCmp('grid').selModel.getSelected().id;
							store.load({params:{"delete": deletedVoucherID}});
							if(deletedVoucherID==edittabs.baseParams.feedID) {
								edittabs.disable();
							}
						}
					});
				},
				iconCls:'delete'
			}],
			frame:true,
			title:'Angelegte Gutscheine',
		});

		var tabs = new Ext.TabPanel({
			region:'center',
			enableTabScroll:true,
			deferredRender:false,
			activeTab:0,
			defaults: {autoScroll:true},
			plugins: new Ext.ux.TabCloseMenu()
		});
		
		//Data for the ModusCombobox
		var modusData = [
		     [0, 'Allgemein gültig','<b>Modus - Allgemein gültig</b><br /> Es wird ein einheitlicher Gutschein-Code bereitgestellt.'],
		     [1, 'Individuell','<b>Modus - Individuelle Gutscheincodes</b><br />Es werden soviele individuelle Gutschein-Codes erzeugt, wie Sie unter &ldquo;Stückzahl&ldquo; angeben.<br /> Jeder Kunde erhält also seinen eigenen, individuellen Code. ']
		];
		
		//Data for the allowanceCombobox
		var allowanceData = [
		     [0, 'Absolut'],
		     [1, 'Prozentual']
		];
		
		

		var weekdays = Array();
		Date.dayNames.each(function(d,i){
			if(!i) return;
			weekdays[i-1]=Array();
			weekdays[i-1][0]=i;
			weekdays[i-1][1]=d;
		});
		weekdays[6]=Array();
		weekdays[6][0]=7;
		weekdays[6][1]=Date.dayNames[0];
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
				"feedID", "description" , "vouchercode" , "numberofunits" , "value" , "minimumcharge" , "shippingfree" , "bindtosupplier" , "valid_from" , "valid_to" , "ordercode", "restrictarticles", "modus" , "percental" , "numorder", "customergroup","strict","subshop"
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
				height: 250,
				minheight: 200,
				bodyStyle:'padding:10px',
				items:[{
					columnWidth:.5,
					layout: 'form',
					width: 180,
					border:false,
					bodyStyle:'padding-right: 20px',
					defaults: {
						anchor: '95%',
						xtype:'textfield'},
					items: [
					{
						fieldLabel: 'Bezeichnung',
						name: 'description',
						id: 'dispatch_description',
						allowBlank: false,
						blankText: 'Bitte ausfüllen...'
					},
					
					new Ext.form.ComboBox({
						fieldLabel: 'Gutscheincode Modus',
						tpl: '<tpl for="."><div ext:qtip="{tip}" class="x-combo-list-item">{text}</div></tpl>',
						name: 'modus',
						hiddenName: 'modus',
						id: 'modusCB',
						editable: false,
						typeAhead: true,
					    triggerAction: 'all',
					    forceSelection: false,
					    allowBlank: false,
					    store: new Ext.data.SimpleStore({
					        fields:
					            ['id', 'text', 'tip']
					        ,data: modusData
					    }),
					    valueField:'id',
					    displayField:'text',
					    mode:'local',
					    listeners: {
	                        'select': {fn:function(cB,record,index){
	                        	if(index == 0) {
	                        		hide();
	                        	}
	                        	else{
	                        		show();
	                        	}
	                        }}
                        }
					})	
					,
					{
						fieldLabel: 'Code',
						name: 'vouchercode',
						id: 'dispatch_vouchercode',
						allowBlank: false,
						blankText: 'Bitte ausfüllen...',						
						enableKeyEvents: true,
						listeners: {
						    'keyup': function(textField, event) {
						      	countCode(textField, textField.getValue());
						   }
						},
						validator: function(value)
		                {
		                	if(voucherCodeCount> 0) {
		                		return 'Der Wert existiert bereits.';
		                	}
		                	return true;
		                }
					},{
						fieldLabel: 'Bestellnummer',
						name: 'ordercode',
						id: 'dispatch_ordercode',
						allowBlank: false,
						blankText: 'Bitte ausfüllen...',
						enableKeyEvents: true,
						listeners: {
						    'keyup': function(textField, event) {
						      	countOrderCode(textField, textField.getValue());
						   }
						},
						validator: function(value)
		                {
		                	if(orderCodeCount> 0) {
		                		return 'Der Wert existiert bereits.';
		                	}
		                	return true;
		                }
					},{
						fieldLabel: 'Stückzahl',
						name: 'numberofunits',
						xtype: 'numberfield',
						id: 'dispatch_numberofunits',
						allowBlank: false,
						blankText: 'Bitte ausfüllen...'
					},
					{
						fieldLabel: 'Wert',
						name: 'value',
						xtype: 'numberfield',
						id: 'dispatch_value',
						allowBlank: false,
						blankText: 'Bitte ausfüllen...',
						validator: function(){
							var percentalVal = Ext.get('percental').getValue();
							
							//percental
							if(percentalVal == "1")
							{
								// > 100%
								if(Ext.getCmp('dispatch_value').getValue() > 100)
								{
									return "Bei einem prozentuellem Abzug beträgt der Maximalwert 100(%)";
								}
							}
							return true;
						}
					},
					{
						fieldLabel: 'Einlösbare Gutscheine je Kunde',
						name: 'numorder',
						xtype: 'numberfield',
						id: 'dispatch_numorder',
					},
					
					new Ext.form.ComboBox({
						fieldLabel: 'Abzug',
						name: 'percental',
						hiddenName: 'percental',
						editable: false,
						typeAhead: true,
					    triggerAction: 'all',
					    forceSelection: false,
					    allowBlank: false,
					    store: new Ext.data.SimpleStore({
					        fields:
					            ['id', 'text']
					        ,data: allowanceData
					    }),
					    valueField:'id',
					    displayField:'text',
					    mode:'local'
					    
					})	
					
					
					]
				},
				{
					columnWidth:.5,
					layout: 'form',
					border:false,
					defaults: {anchor: '95%',xtype:'textfield', bodyStyle:'padding-top:10px'},
					items: [
					{
						fieldLabel: 'Mindestumsatz',
						name: 'minimumcharge',
						id: 'dispatch_minimumcharge',
						xtype: 'numberfield',
						validator: function(){
							var percentalVal = Ext.get('percental').getValue();
							
							//absolut
							if(percentalVal != "1")
							{
								// mindestumsatz muss >= gutscheinwert sein
								if(Ext.getCmp('dispatch_minimumcharge').getValue() < Ext.getCmp('dispatch_value').getValue())
								{
									return "Bei einem absolutem Abzug muss der Mindestumsatz größer oder gleich Gutscheinwert sein";
								}
							}
							return true;
						}
						
					},
					new Ext.form.ComboBox({
						fieldLabel: 'Beschränkt auf Hersteller',
						name:'bindtosupplier',
						hiddenName:'bindtosupplier',
						store:  new Ext.data.Store({
							url: 'ajax/getValues.php?name=supplier',
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
						selectOnFocus:true,
						editable:true,
						triggerAction:'all',
						forceSelection : false,
						listeners: {
						'blur': {fn:function(el){
							if(!el.getRawValue())
							el.setValue(null);
						}}
						}
					})
					,
					
					new Ext.form.ComboBox({
                        fieldLabel: 'Beschränkt auf Kundengruppe',
                        name:'customergroup',
                        hiddenName:'customergroup',
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
                        selectOnFocus:true,
                        editable:true,
                        triggerAction:'all',
                        forceSelection : false,
                        listeners: {
	                        'blur': {fn:function(el){
	                        	if(!el.getRawValue())
	                        		el.setValue(null);
	                        }}
                        }
                    }),
					
					new Ext.form.ComboBox({
                        fieldLabel: 'Beschränkt auf Subshop',
                        name:'subshop',
                        hiddenName:'subshop',
                        store:  new Ext.data.Store({
							url: 'ajax/getValues.php?name=subshop',
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
                        selectOnFocus:true,
                        editable:true,
                        triggerAction:'all',
                        forceSelection : false,
                        listeners: {
	                        'blur': {fn:function(el){
	                        	if(!el.getRawValue())
	                        		el.setValue(null);
	                        }}
                        }
                    })
					,
					
				      new Ext.form.DateField({
						fieldLabel: 'Von',
						name:'valid_from',
					    typeAhead: true,
					    id: 'valid_from',
					    endDateField: 'valid_to',
					})
					,
					new Ext.form.DateField({
						fieldLabel: 'Bis',
						name:'valid_to',
					    typeAhead: true,
					    id: 'valid_to',
					    startDateField: 'valid_from',
					}),
					{
						fieldLabel: 'Auf Artikel begrenzt',
						name: 'restrictarticles',
						id: 'dispatch_restrictarticles',
					},
					{
						fieldLabel: 'Versandkostenfrei',
						name: 'shippingfree',
						id: 'dispatch_shippingfree',
						xtype: 'checkbox'
					},
					{
						fieldLabel: 'Rabatt nur auf definierte Artikel/Hersteller',
						name: 'strict',
						id: 'strict',
						xtype: 'checkbox'
					}


					]}
			]
			},
			
			//**********************VoucherCodeList**********************
			{
				xtype:'tabpanel',
				deferredRender: false,
				id: 'voucherListPanel',
				name:'voucherListPanel',
				region:'center',
				split:true,
				height: 400,
				minSize: 200,
				maxSize: 200,
				autoScroll: true,
				collapsible: false,
				title:'South',
				activeTab: 0,
				defaults:{bodyStyle:'padding:10px'},
				items:[{
					title:'Individuelle Codes',
					id: 'voucherList',
					name:'voucherList',
					xtype: 'editorgrid',
					autoScroll: true,
					loadMask: true,
					bodyStyle:'padding:0px',
					store: codeListStore,
					bbar:paging_toolbar,
					cm: new Ext.grid.ColumnModel([
					{id:'code', dataIndex: 'code', header: "Code", align: 'right', width: 40, sortable: true},
					{id:'customernumber', dataIndex: 'customernumber', header: "Kundennummer", align: 'right', width: 40, sortable: true},
					{id:'firstname', dataIndex: 'firstname', header: "Vorname", align: 'right', width: 40, sortable: true},
					{id:'lastname', dataIndex: 'lastname', header: "Nachname", align: 'right', width: 40, sortable: true},
					{id:'cashed', dataIndex: 'cashed', header: "Eingelöst", align: 'right', width: 40,renderer: cashed, sortable: true},
					]),
					
					viewConfig: {
						forceFit:true
					},
					tbar: [
					{
						text: 'Download Codes',
						iconCls:'disk',
						handler : function()
						{
							if(Ext.getCmp('voucherList').store.getTotalCount() > 0){
								window.open('ajax/csvDownload.php?feedID='+edittabs.baseParams.feedID);
							}
							else{
								Ext.Msg.alert('Warnung', 'Es sind noch keine Gutscheincodes vorhanden!');
							}
						}
					},
					{
						text: 'Neue Codes generieren',
						id: 'codegenerate',
						iconCls:'add',
						handler : function()
						{
							var id = edittabs.baseParams.feedID;
							var voucherCodeListMask = new Ext.LoadMask(Ext.get('voucherList'), {msg:"Generiere Codes..."});
							var numberOfUnits =  Ext.getCmp('dispatch_numberofunits').getValue();
							if(id) {
								voucherCodeListMask.show();
								conn = new Ext.data.Connection();
					        	conn.request({
								   url: 'ajax/codeGenerator.php?id='+id+'&numberofunits='+numberOfUnits,
								   success: function(result){
								   	loadTab(id);
								   	voucherCodeListMask.hide();
								   	setCodeGenBtnHide(id);
								   },
								   failure: function(result){
								   	voucherCodeListMask.hide();
								   }
								   
								});
							}
							else{
								Ext.Msg.alert('Warnung', 'Sie müssen den Gutschein erst Speichern');
							}
						}
					}
					]
				},
				]
			}],
			
			
			buttonAlign:'right',
			buttons: [{
				text: 'Speichern',
				handler: function(){
					var form = edittabs.getForm();
					if(form.isValid()){
						form.submit({url:'ajax/saveVouchers.php', waitMsg:'Speichern...',
						 	success: function (e1,result) {
						 		if(result&&result.response&&result.response.responseText&&result.response.responseText!='')
				            	{
				            		
						 			result =  Ext.util.JSON.decode(result.response.responseText);
									var feedID = result["feedID"].toInt();
									edittabs.baseParams.feedID =  feedID;
				            	}
						 		
		            		}
						});
					}
				}
			}]
		});

		tabs.add(grid).show();
		tabs.add(edittabs).show();
		store.load();
		edittabs.getForm().load({url:'ajax/getVouchers.php', waitMsg:'Laden...'});

		var viewport = new Ext.Viewport({
			layout:'fit',
			items:	tabs
		});
	});
</script>
</head>
<body>
</body>
</html>