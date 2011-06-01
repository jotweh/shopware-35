{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
	<!-- Common CSS -->
<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
<style>
.search-item {
    font:normal 11px tahoma, arial, helvetica, sans-serif;
    padding:3px 10px 3px 10px;
    border:1px solid #fff;
    border-bottom:1px solid #eeeeee;
    white-space:normal;
    color:#555;
    cursor:pointer;
    height:50px;
}
.search-item h3 {
    display:block;
    font:inherit;
    font-weight:bold;
    color:#222;
}

.search-item h3 span {
    float: right;
    font-weight:normal;
    margin:0 0 5px 5px;
    width:100px;
    display:block;
    clear:none;
}
.x-action-col-cell .x-grid3-cell-inner {
    padding-top: 1px;
    padding-bottom: 1px;
}

.x-action-col-icon {
    cursor: pointer;
}

.x-grid3-hd-inner {
    position:relative;
	cursor:inherit;
	padding:4px 3px 4px 5px;
}

.x-grid3-row-body {
    white-space:normal;
}

.x-grid3-body-cell {
    -moz-outline:0 none;
    outline:0 none;
}
.statistics {
	font-size:12px;
	font-weight: bold;
}
</style>
{/block}

{block name="backend_index_body_inline"}
<script>
Ext.ns('Shopware.Coupons');	
Ext.grid.ActionColumn = Ext.extend(Ext.grid.Column, {
    header: '&#160;',

    actionIdRe: /x-action-col-(\d+)/,

    constructor: function(cfg) {
        var me = this,
            items = cfg.items || (me.items = [me]),
            l = items.length,
            i,
            item;

        Ext.grid.ActionColumn.superclass.constructor.call(me, cfg);

        me.renderer = function(v, meta,r,index) {
        	
        	
            meta.css += ' x-action-col-cell';
            v = '';
            for (i = 0; i < l; i++) {
                item = items[i];
                handler = item.handler;
                if (item.check){
                	 if (r.data.stateID == -1){
                	 	item.icon = "{link file='engine/backend/img/default/icons2/forbidden.png'}";
                	 	item.tooltip = "Coupon wurde storniert";
                	 }else {
                	 	if (r.data.stateID == 0){
                	 		if (r.data.pdf || r.data.senddate != '0000-00-00 00:00:00'){
                	 	 		item.icon = "{link file='engine/backend/img/default/icons4/tick.png'}";
                	 	 		item.tooltip = "Coupon bearbeitet";
                	 		}else {
                	 			item.icon = "{link file='engine/backend/img/default/icons4/flag_red.png'}";
                	 			item.tooltip = "Coupon nicht bearbeitet";
                	 		}
                	 	}else {
                	 		item.icon = "{link file='engine/backend/img/default/icons2/reload3.png'}";
                	 		item.tooltip = "Coupon wurde eingelöst";
                	 	}
                	 }
                }
               
                id = index;
               
                if (handler){
                	handler = handler.replace('#',id);
                }
                v += '<img onclick="'+handler+'" src="' + (item.icon || Ext.BLANK_IMAGE_URL) +
                    '" class="x-action-col-icon x-action-col-' + String(i) + ' ' + (item.iconCls || '') + '"' +
                    ((item.tooltip) ? ' ext:qtip="' + item.tooltip + '"' : '') + '>';
            }
            if (cfg.renderer) {
                v = cfg.renderer.apply(this, arguments);
            }

            return v;
        };
    },

    destroy: function() {
        delete this.items;
        delete this.renderer;
        return Ext.grid.ActionColumn.superclass.destroy.apply(this, arguments);
    }
});


Ext.grid.Column.types = {
    gridcolumn : Ext.grid.Column,
    booleancolumn: Ext.grid.BooleanColumn,
    numbercolumn: Ext.grid.NumberColumn,
    datecolumn: Ext.grid.DateColumn,
    templatecolumn: Ext.grid.TemplateColumn,
    actioncolumn: Ext.grid.ActionColumn
};
Shopware.Coupons.Form = Ext.extend(Ext.FormPanel,
{
	labelWidth: 75, // label settings here cascade unless overridden
    frame:true,
    bodyStyle:'padding:5px 5px 0',
   //defaults: { width: 230},
    defaultType: 'textfield',
	initComponent: function() {
		
		this.storeArticles = new Ext.data.Store({
	        url: '{url module=backend controller=CouponsAdmin action=getArticles}'
	        ,{literal}
	        reader: new Ext.data.JsonReader({
	            root: 'items',
	            totalProperty: 'total',
	            id: 'id'
	        }, [
	            {name: 'name'},
	            {name: 'ordernumber'},
	            {name: 'supplier'},
	            {name: 'id'}
	        ]){/literal}
	    });
			    
	    this.storeVoucher = new Ext.data.Store({
	        url: '{url module=backend controller=CouponsAdmin action=getVouchers}'
	        ,{literal}
	        reader: new Ext.data.JsonReader({
	            root: 'items',
	            totalProperty: 'total',
	            id: 'id2'
	        }, [
	            {name: 'name'},
	            {name: 'id'},
	            {name: 'value'}
	        ]){/literal}
	    });
		
		this.items = [
				new Ext.Panel(
				{
					title: 'Wichtige Hinweise',
					height: 120,
					width: 650,
					style: { marginBottom: '20px'},
					html: '<strong>Folgende Eigenschaften werden bei der Zuordnung eines Artikels zu einem Coupon automatisch gesetzt:</strong> <ul><li>- Lagerbestand wird mit verfügbaren Gutschein-Codes synchronisiert</li><li>- Die max. Bestellmenge wird auf 1 beschränkt</li><li>- Eine automatische Preisanpassung findet nicht statt! Der Artikel Preis sollte daher dem Gutschein-Wert entsprechen</li></ul><strong>Achten Sie daher darauf, dass Sie den korrekten Artikel auswählen, bevor Sie den Coupon speichern. Der zugeordnete Gutschein muss vom Typ Individuelle Codes / Absoluter Wert sein.</strong>',
					border: true,
					frame: true
				}
				),
	        	{
	                xtype: 'hidden',
	                anchor: '100%',
	                width: 150,
	                name: 'id',
	                id: 'id'
	            },
	        	{
	                fieldLabel: 'Coupon Name',
	                name: 'name',
	                allowBlank:false
	            },new Ext.form.ComboBox({
			        store: this.storeArticles,
			       // store: new Ext.data.SimpleStore({ fields:[["id"],["name"]],data:[[]]}),
			        displayField:'name',
			        lazyRender: false,
		            mode: 'remote',
		            triggerAction: 'all',
			        fieldLabel: 'Artikel',
			       	forceSelection: true,
			       	itemId: 'articleCombo',
			        valueField: 'id',
			        hiddenName: 'articleID',
			        typeAhead:  false,
			        loadingText: 'Searching...',
			        width: 570,
			        pageSize:10,
			        hideTrigger:false,
	                allowBlank:false,
		            listeners: {
		            	'expand': { fn:function(combobox){
		            		//combobox.store = this.storeArticles;
		            	},scope:this}
	                }
			    })
				,
                new Ext.form.ComboBox({
			        store: this.storeVoucher,
			        mode: 'remote',
			        displayField:'name',
			        fieldLabel: 'Gutschein',
			        valueField: 'id',
					lazyRender: false,
			        minChars: 2,
		        	itemId: 'voucherCombo',
			        hiddenName: 'voucherID',
			        typeAhead:  false,
			        loadingText: 'Searching...',
			        width: 570,
			        pageSize:10,
			        hideTrigger:false,
	                allowBlank:false
			    })
	            , new Ext.form.Checkbox({
	            	name: 'active',
	            	checked: true,
	            	labelStyle: 'width:287px;padding:0px 0px 0px 0px',
	            	fieldLabel: 'Aktiv',
	            	inputValue: 1
	            })
        	];
        	
	        this.buttons = [{
		            text: 'Speichern',
		            handler: function(){
		            	
		            	this.getForm().submit({ url: '{url module=backend controller=CouponsAdmin action=saveCoupon}'});
		            	
		            },
		            scope:this
	        }];
			Shopware.Coupons.Form.superclass.initComponent.call(this);
			this.url = '{url module=backend controller=CouponsAdmin action=saveCoupon}';
	}
}
);

Shopware.Coupons.Window = Ext.extend(Ext.Window, {
    title: 'Coupon Optionen',
	layout:'border',
	width: 700,
	height: 400,
	closeAction: 'hide',
	plain: true,
	resizable:false,
	autoScroll:false,
	modal:true,        
    initComponent: function() {
    	
        this.Fieldset = new Ext.Panel(
        	{
        		title: 'Daten zum Coupon',
        		region: 'north',
        		html: 'Content',
        		height:120
        	}
        );
        this.Form = new Ext.form.FormPanel({
        	title: 'Coupon verschicken',
        	autoScroll:true,
    		region: 'center',
        	items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Empfänger-eMail',
                        name: 'email',
                        width: 220
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Absender',
                        name: 'frommail',
                        width: 220
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: 'Betreff',
                        name: 'subject',
                        width: 220
                    },
                    {
                        xtype: 'htmleditor',
                        height: 150,
                        fieldLabel: 'Nachricht',
                        name: 'content'
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: 'PDF erzeugen',
                        name: 'sendPdf',
                        id: 'sendPdf'
                    },
                    new Ext.Button  ({
			            	text: 'Gutschein verschicken',
			            	plain:false,
			            	border:true,
			            	handler: function(){
			            		var pdf = Ext.getCmp('sendPdf');
			            	    if (pdf.getValue()==true){
			            	    	var url = '{url module=backend controller=CouponsAdmin action=getPdf}/id/'+this.data.id;
									Ext.Ajax.request({
										url : url,
										scope:this,
										method: 'GET',
											success: function ( result, request ) { 
												result = Ext.util.JSON.decode(result.responseText);
												this.data['pdf'] = result.pdf;
												this.xTemplate.overwrite(this.Fieldset.body, this.data);
												this.store.reload();
												{literal}
	             								this.Fieldset.body.highlight('#c3daf9', {block:true});
	             								{/literal}
	             								this.Form.getForm().submit({ url: '{url module=backend controller=OrderState action=send}/couponID/'+this.data.id});
			            						this.store.reload();
											},
											failure: function ( result, request) { 
											} 
									});
			            	    }else {
			            	    	this.Form.getForm().submit({ url: '{url module=backend controller=OrderState action=send}/couponID/'+this.data.id});
			            			this.store.reload();
			            	    }
			            		
			            	},
			            	scope:this
             		})
            ]
        });
        
        this.Form.on('actioncomplete',function(form,action){
			if (action.type=="load") return;
			this.store.reload();
			Ext.MessageBox.show({
	           title: 'Hinweis',
	           msg: 'Coupon wurde erfolgreich verschickt',
	           buttons: Ext.MessageBox.OK,
	           animEl: 'mb9',
	           icon: Ext.MessageBox.INFO
  			});
        },this);
    	this.items = [this.Fieldset,this.Form];
    	this.tbar = [
						new Ext.Button  ({
			            	text: 'Kundenkonto aufrufen',
			            	plain:false,
			            	border:true,
			            	handler: function(){
			            		parent.loadSkeleton('userdetails',false,{ user:this.data.userID});
			            	},
			            	scope:this
		             	}),
		             	'-'
		             	,
		             	new Ext.Button  ({
			            	text: 'Bestellung aufrufen',
			            	handler: function(){
			            		parent.loadSkeleton('orders',false,{ id:this.data.orderID});
			            	},
			            	scope:this
		             	}),'-'
		             	,
		             	new Ext.Button  ({
			            	text: 'PDF generieren',
			            	handler: function(){
			            		var url = '{url module=backend controller=CouponsAdmin action=getPdf}/id/'+this.data.id;
								Ext.Ajax.request({
									url : url,
									scope:this,
									method: 'GET',
										success: function ( result, request ) { 
											result = Ext.util.JSON.decode(result.responseText);
											this.data['pdf'] = result.pdf;
											this.xTemplate.overwrite(this.Fieldset.body, this.data);
											this.store.reload();
											{literal}
             								this.Fieldset.body.highlight('#c3daf9', {block:true});
             								{/literal}
										},
										failure: function ( result, request) { 
										} 
								});
			            	},
			            	scope:this
		             	}),'-'
		             	,
		             	new Ext.Button  ({
			            	text: 'Vorgang stornieren & Gutschein freigeben',
			            	handler: function(){
			            		 Ext.MessageBox.show({
						           title: 'Frage',
						           msg: 'Soll der Vorgang '+ this.data.ordernumber +' wirklich storniert werden? Der Gutschein-Code wird hierbei freigeben. Der Bestellstatus wird nicht verändert!',
						           width:300,
						           buttons: Ext.MessageBox.OKCANCEL,
						           fn: this.deleteCouponRow.createDelegate(null,[this,0],true),
						           animEl: 'mb3'
						         });
			            	},
			            	scope:this
		             	}),'-'
		             	,
		             	new Ext.Button  ({
			            	text: 'Fenster schließen',
			            	handler: function(){
			            		this.hide();
			            	},
			            	scope:this
		             	})
					];
        Shopware.Coupons.Window.superclass.initComponent.call(this);
    },
    deleteCouponRow: function(result,unused,unused2,component,nodeID){
    	if (result=="ok"){
    		var id = component.data.id;
    		var url = '{url module=backend controller=CouponsAdmin action=deleteCouponRow}/id/'+id;
				Ext.Ajax.request({
					url : url,
					scope:this,
					method: 'GET',
						success: function ( result, request ) { 
							Ext.MessageBox.show({
					           title: 'Hinweis',
					           msg: 'Vorgang wurde erfolgreich gelöscht',
					           buttons: Ext.MessageBox.OK,
					           animEl: 'mb9',
					           icon: Ext.MessageBox.INFO
				      		});
				      		this.Coupons.Window.hide();
				      		this.Coupons.store.reload();
						},
						failure: function ( result, request) { 
							Ext.MessageBox.show({
					           title: 'Hinweis',
					           msg: 'Vorgang konnte nicht gelöscht werden',
					           buttons: Ext.MessageBox.OK,
					           animEl: 'mb9',
					           icon: Ext.MessageBox.ERROR
				      		});
						} 
				});
    	}
    }
});



Ext.QuickTips.init();
(function(){
	View = Ext.extend(Ext.Viewport, {
		layout: 'border',
		deleteQuestion: function(){
			var n = this.tree.getSelectionModel().getSelectedNode();
			
			if (!n){
				Ext.MessageBox.show({
		           title: 'Hinweis',
		           msg: 'Bitte wählen Sie einen Coupon der gelöscht werden soll',
		           buttons: Ext.MessageBox.OK,
		           animEl: 'mb9',
		           icon: Ext.MessageBox.ERROR
	      		});
			}else {
				// Confirm me
				 Ext.MessageBox.show({
		           title: 'Frage',
		           msg: 'Soll der Coupon '+ n.attributes.text +' wirklich gelöscht werden?',
		           width:300,
		           buttons: Ext.MessageBox.OKCANCEL,
		           fn: this.deleteCoupon.createDelegate(null,[this,n.attributes.id],true),
		           animEl: 'mb3'
		         });
			}
		},
		deleteCoupon: function(result,unused,unused2,tree,nodeID){
			if (result=="ok"){
				var url = '{url module=backend controller=CouponsAdmin action=deleteCoupon}/id/'+nodeID;
				Ext.Ajax.request({
					url : url,
					scope:this,
					method: 'GET',
						success: function ( result, request ) { 
							Ext.MessageBox.show({
					           title: 'Hinweis',
					           msg: 'Coupon wurde erfolgreich gelöscht',
					           buttons: Ext.MessageBox.OK,
					           animEl: 'mb9',
					           icon: Ext.MessageBox.INFO
				      		});
				      		tree.tree.getRootNode().reload();
						},
						failure: function ( result, request) { 
							Ext.MessageBox.show({
					           title: 'Hinweis',
					           msg: 'Coupon konnte nicht gelöscht werden',
					           buttons: Ext.MessageBox.OK,
					           animEl: 'mb9',
					           icon: Ext.MessageBox.ERROR
				      		});
						} 
				});
			}
		},
		addWindow: function(id){
			
			this.Window.show();
			var data = this.store.getAt(id).data;
			this.Window.Form.load({ url: '{url module=backend controller=OrderState action=read}/id/'+data.orderID+'/status/3/mailtype/PluginCouponsSendCoupon/couponID/'+data.id});
		    this.Window.id = id;
		    this.Window.data = data;
		    this.Window.store = this.store;
		    fieldset = this.Window.Fieldset;
			{literal}
    	    this.xTemplate = new Ext.XTemplate(
    	     		'<div class="statistics" style="padding: 5px 5px 5px 5px;line-height:1.4em">',
    	     		'<tpl if="status!=\'0\'">',
	    	     		'<tpl if="pdf!=\'\'">',
	    	     		'<p>Status: <span style="font-weight:normal">PDF generiert am {pdfdate}</span></p>',
	                    '</tpl>',
	                    '<tpl if="senddate!=\'0000-00-00 00:00:00\'">',
	    	     		'<p>Status: <span style="font-weight:normal">eMail verschickt am {senddate}</span></p>',
	                    '</tpl>',
                    '</tpl>',
                    '<tpl if="status==\'0\'">',
                    '<p>Status: <span style="font-weight:normal;color:#F00">VORGANG STORNIERT! CODE GESPERRT!</span></p>',
                     '</tpl>',
    	     		'<p>Datum: <span style="font-weight:normal">{datum}</span> Bestellung: <span style="font-weight:normal">{ordernumber}</span> Kunde: <span style="font-weight:normal">{customer}</a> Zahlungsart: <span style="font-weight:normal">{payment}</span></p>',
                    '<p>Coupon: <span style="font-weight:normal">{name}</span> Zugeordneter Gutschein-Code: <span style="font-weight:normal">{code}</span> Bestellstatus: <span style="font-weight:normal">{statusText}</span> Zahlstatus: <span style="font-weight:normal">{clearedText}</span></p>',
                    '<tpl if="pdf!=\'\'">',
                        '<p><a class="ico page_white_acrobat" style="padding-left:25px;float:none" target="_blank" href="{/literal}{url module=backend controller=CouponsAdmin action=loadPdf}{literal}/pdf/{pdf}">Download PDF-Datei</a></p>',
                    '</tpl></p>',
                    '</div>'
              );
      		 
           //  console.log(fieldset.body);
             this.xTemplate.overwrite(fieldset.body, data);
             this.Window.xTemplate = this.xTemplate;
             fieldset.body.highlight('#c3daf9', {block:true});
            {/literal}
			
		},
		initComponent: function() {
			this.tree = new Ext.tree.TreePanel( {
					region:'west',
					split:true,
					fitToFrame: true,
					animate:false,
					title:'Angelegte Coupons',
					width: 200,
					height:'100%',
					margins:'0 0 0 0',
					minSize: 175,
					loader: new Ext.tree.TreeLoader({ dataUrl:'{url module=backend controller=CouponsAdmin action=getCoupons}'}),
					enableDD:false,
					enableEdit:false,
					autoScroll: true,
					rootVisible: false,
					root: new Ext.tree.AsyncTreeNode({ 
						 text: 'Test',
						 draggable:true,
						 id:'1'
					}),
					tbar: [
						new Ext.Button  ({
			            	text: 'Hinzufügen',
			            	handler: function(){
			            		//console.log(this);
			            		coupon = new Shopware.Coupons.Form ({ parent: this});
							    coupon.on('actioncomplete',function(form,action){
							    	if (action.result.id != null){
							    		form.findField('id').setValue(action.result.id);
									}
									form.parent.tree.root.reload();
									Ext.MessageBox.show({
							           title: 'Hinweis',
							           msg: 'Coupon wurde erfolgreich gespeichert',
							           buttons: Ext.MessageBox.OK,
							           animEl: 'mb9',
							           icon: Ext.MessageBox.INFO
						  			});
							    });
							   this.tabs.add({
						            title: 'Neuer Coupon',
						            items: [coupon],
						            closable:true
						        }).show();
							
			            	},
			            	scope:this
		             	}),
		             	new Ext.Button  ({
			            	text: 'Löschen',
			            	handler: function (){
			            		this.deleteQuestion();
			            	},
			            	scope:this
		             	})
					]
			});
			this.tree.parent = this;
			this.tree.on('click', function(e){
		 		var id = e.attributes.id;
		 		var text = e.attributes.text;
		 		var coupon = new Shopware.Coupons.Form ({ parent: this, edit: id});
		 		coupon.load({ url: '{url module=backend controller=CouponsAdmin action=getCoupon}/id/'+id});
		 		
		 		coupon.on('actioncomplete',function(form,action){
		 			if (action.type=="load"){
						
		 				this.getComponent('articleCombo').valueNotFoundText = action.result.data.articleName;
		 				this.getComponent('voucherCombo').valueNotFoundText = action.result.data.voucherName;
		 				
		 				form.findField('articleID').setValue(action.result.data.articleID);
		 				form.findField('voucherID').setValue(action.result.data.voucherID);
		 				//console.log(form.findField('voucherID'));
		 				
		 				return;
		 			}
			    	if (action.result.id != null){
			    		form.findField('id').setValue(action.result.id);
					}
					form.parent.tree.root.reload();
					Ext.MessageBox.show({
			           title: 'Hinweis',
			           msg: 'Coupon wurde erfolgreich gespeichert',
			           buttons: Ext.MessageBox.OK,
			           animEl: 'mb9',
			           icon: Ext.MessageBox.INFO
		  			});
		    	});
		 		this.tabs.add({
		            title: 'Coupon '+text,
		            items: [coupon],
		            closable:true
		        }).show();
				
			},this);
			
		   
		    
		    
		    	
	       	this.store = new Ext.data.Store({
		        url: '{url module=backend controller=CouponsAdmin action=getOrders}',
		        // create reader that reads the Topic records
		        reader: new Ext.data.JsonReader({
		            root: 'items',
		            totalProperty: 'total',
		            id: 'id',
		            fields: [
		                'id','datum','ordernumber','customer','orderID','userID','couponID','name','code','quantity','clearedText','statusText','payment','stateID','pdf','pdfdate','senddate'
		            ]
		        }),
		        // turn on remote sorting
		        remoteSort: true,
		        autoLoad:true
	    	});
	    	
	    	/*
	    	
	    	*/
	    	this.Window = new Shopware.Coupons.Window();
		    var cm = new Ext.grid.ColumnModel([
		    	{
	                xtype: 'actioncolumn',
	                header: 'Info',
	                dataindex: 'statusimg',
	                width: 50,
	                items: [{
	                    icon: '{link file='engine/backend/img/default/icons4/arrow_down_green.png'}',   // Use a URL in the icon config
	                    scope: this,
	                    tooltip: 'Coupon wurde gekauft',
	                    check: 1
	                }]
	        	},
		    	{
		           header: "Datum",
		           dataIndex: 'datum',
		           width: 120,
		    	   sortable: false
	        	},
        	  	{
		           header: "Bestellnummer",
		           dataIndex: 'ordernumber',
		           width: 90,
		    	   sortable: false,
		    	   renderer: function (v,r,p){
		    	   		var string = 'parent.loadSkeleton(\'orders\',false,{ id:'+p.data.orderID+'});';
		    	   		return '<a href="#" onclick="'+string+'">'+v+'</a>';
		    	   }
	        	}, 
	        	{
		           header: "Kunde",
		           dataIndex: 'customer',
		           width: 110,
		    	   sortable: false,
		    	   renderer: function (v,r,p){
		    	   		var string = 'parent.loadSkeleton(\'userdetails\',false,{ user:'+p.data.userID+'});';
		    	   		return '<a href="#" onclick="'+string+'">'+v+'</a>';
		    	   }
	        	},
        	  	{
		           header: "Coupon",
		           dataIndex: 'name',
		           width: 120,
		    	   sortable: false,
		    	   renderer: function (v,r,p){
		    	   		return v;
		    	   }
	        	}, 
	        	{
		           header: "Code",
		           dataIndex: 'code',
		           width: 120,
		    	   sortable: false,
		    	   editor: new Ext.form.TextField({})
	        	}, 
	        	{
		           header: "Anzahl",
		           dataIndex: 'quantity',
		           width: 50,
		    	   sortable: false
	        	},
	        	{
		           header: "Zahlstatus",
		           dataIndex: 'clearedText',
		           width: 100,
		    	   sortable: false
	        	},
	        	{
		           header: "Bestellstatus",
		           dataIndex: 'statusText',
		           width: 100,
		    	   sortable: false
	        	},
	        	{
		           header: "Zahlungsart",
		           dataIndex: 'payment',
		           width: 100,
		    	   sortable: false
	        	},
        	  	{
		           header: "Couponstatus",
		           dataIndex: 'stateID',
		           width: 100,
		    	   sortable: false
	        	},{
	                xtype: 'actioncolumn',
	                header: 'Optionen',
	                dataIndex: 'optionen',
	                editable:false,
	                width: 80,
	                items: [{
	                    icon: '{link file='engine/backend/img/default/icons4/sticky_note_arrow.png'}',   // Use a URL in the icon config
	                    handler: 'Coupons.addWindow(#);',
	                    scope: this,
	                    tooltip: 'Coupon bearbeiten'
	                }]
	        	}
		    ]);
		    var AutoHeightGridView = Ext.extend(Ext.grid.GridView, {
                    
                    onLayout: function () {
                        Ext.get(this.innerHd).setStyle("float", "none");
                        this.scroller.setStyle("overflow-x", "auto");
                    }
                    
            });

			this.grid = new Ext.grid.EditorGridPanel({
				layout:'fit',
				cm: cm,
				store: this.store,
				autoScroll:true,
				autoWidth:true,
				stripeRows:true,
				autoHeight:true,
				view: new AutoHeightGridView(),
				bbar: new Ext.PagingToolbar({
		            pageSize: 25,
		            store: this.store,
		            displayInfo: true/*,
		            items: [
			            {
			            'Suche:'	
			            },
				        {
	            			xtype: 'textfield',
			            	id: 'search',
			            	selectOnFocus: true,
			            	width: 120,
			            	listeners: {
				            	'render': { fn:function(ob){
				            		//ob.el.on('keyup', searchFilter, this, { buffer:500});
				            	}, scope:this}
			            	}
			            }
		            ]*/
				})
			});
			
			this.tabs = new Ext.TabPanel({
		        region: 'center',
		        activeTab: 0,
		        bodyBorder: false,
		        border: false,
		        plain:true,
		        hideBorders:false,
		        defaults:{ autoScroll: true},
		        items:[{
		                title: 'Übersicht',
		                items: [this.grid]
		            }
		        ]
		    });
		

			this.items = [this.tree,this.tabs];
	        View.superclass.initComponent.call(this);
		}
	});
	Shopware.Coupons.View = View;
})();;
Ext.onReady(function(){
	Ext.QuickTips.init();
	Coupons = new Shopware.Coupons.View;
	Coupons.grid.el.select(".x-grid3-viewport").setStyle("overflow-x", "auto");
});
</script>
{/block}