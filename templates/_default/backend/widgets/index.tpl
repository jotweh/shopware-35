{extends file='backend/index/parent.tpl'}

{block name="backend_index_css" replace}
<link href="{link file='backend/_resources/javascript/ext-4.0.0/resources/css/ext-all.css'}" rel="stylesheet" type="text/css" />
<style>
-moz-linear-gradient(center top , #D9EDF6, #D5EBF5 45%, #C4E3F1 46%, #C5E4F1 50%, #C5E4F1 51%, #D4EBF5);

#app-header {
    color: #596F8F;
    font-size: 22px;
    font-weight: 200;
    padding: 8px 15px;
    text-shadow: 0 1px 0 #fff;
}
#app-msg {
    background: #D1DDEF;
    border: 1px solid #ACC3E4;
    padding: 3px 15px;
    font-weight: bold;
    font-size: 13px;
    position: absolute;
    right: 0;
    top: 0;
}
.x-panel-ghost {
    z-index: 1;
}
.x-border-layout-ct {
    background: #DFE8F6;
}
.x-portal-body {
    padding: 0 0 0 8px;
}
.x-portal .x-portal-column {
    /* columns must have vertical padding to avoid losing dimensions when empty */
    padding: 8px 8px 0 0;
}
.x-portal .x-panel-dd-spacer {
    border: 2px dashed #99bbe8;
    background: #f6f6f6;
    border-radius: 4px;
    -moz-border-radius: 4px;
    margin-bottom: 10px;
}
.x-portlet {
    margin-bottom:10px;
    padding: 1px;
}
.x-portlet .x-panel-body {
    background: #fff;
}
.portlet-content {
    padding: 10px;
    font-size: 11px;
}

#app-options .portlet-content {
    padding: 5px;
    font-size: 12px;
}
</style>
{/block}

{block name="backend_index_javascript" replace}
	<script type="text/javascript" src="{link file='backend/_resources/javascript/ext-4.0.0/bootstrap.js'}"></script>
	<script type="text/javascript">
		Ext.Loader.setConfig({ enabled:true});
		Ext.Loader.setPath('Ext.app', '{link file="backend/_resources/javascript/plugins"}');
		Ext.Loader.setPath('Swag.Config','{url action=getWidgetSettings}');
		Ext.Loader.setPath('Swag.Admin','{url action=getWidgetAdmin}');
		Ext.Loader.setPath('Swag.Widget','{url action=getWidgetItem}');

		Ext.Loader.getPath = function(className) {
			var tempClass = className;
            var path = '',
                paths = this.config.paths,
                prefix = this.getPrefix(className);

            if (prefix.length > 0) {
                if (prefix === className) {
                    return paths[prefix];
                }

                path = paths[prefix];
                className = className.substring(prefix.length + 1);
            }

            if (path.length > 0) {
                path += '/';
            }
			
			if (tempClass.match(/Swag/)){
				return path.replace(/\/\.\//g, '/') + "load/" +className.replace(/\./g, "/");
			}
            return path.replace(/\/\.\//g, '/') + className.replace(/\./g, "/") + '.js';
		};
		Ext.require([
			'Ext.layout.container.*',
			'Ext.resizer.Splitter',
			'Ext.fx.target.Element',
			'Ext.fx.target.Component',
			'Ext.window.Window',
			'Ext.app.Portal',
			'Ext.app.PortalColumn',
			'Ext.app.PortalPanel',
			'Ext.app.Portlet',
			'Ext.app.PortalDropZone',
			'Ext.app.GridPortlet',
			'Ext.app.ChartPortlet'
		]);

		Ext.define('Ext.app.Admin',
		{
			extend: 'Ext.window.Window',
			initComponent: function(){
				Ext.apply(this, {
					title: 'Widget Administration',
					height: 500,
					closeable: true,
					padding: '10 10 10 10',
					width: 900,
					layout: 'border'
				});

				this.tree = Ext.create('Ext.tree.Panel', {
					region: 'west',
					title: 'Verfügbare Widgets',
					parent: this,
					useArrows: true,
					resizable: true,
					width: 300,
					rootVisible: false,
					store: Ext.create('Ext.data.TreeStore', {
						proxy: {
							type: 'ajax',
							url: '{url action=getWidgets}'
						},
						root: {
							text: 'Ext JS',
							id: 'available',
							expanded: true
						},
						fields: [
						'id','leaf','text','widgetUid','widgetType'
						],
						folderSort: true,
						sorters: [{
							property: 'text',
							direction: 'ASC'
						}]
					})
				});

				this.tree.on('itemclick',function(view,data){
					var id = data.data.widgetType;

					this.parent.form.setTitle("Widget " + data.data.text);
					this.parent.form.getEl().mask('Lade Settings');

					var test = Ext.create('Swag.Admin.'+id+"_"+new Date().getTime());
					this.parent.form.removeAll(true);
					this.parent.form.add(test);
					this.parent.form.doLayout();
					this.parent.form.getEl().unmask();


				});
				this.form = Ext.create('Ext.form.Panel',
				{
					title: '',
					region: 'center',
					padding: '10 10 10 10',
					autoScroll:true,
					buttons: [
						{
							text: 'Speichern',
							width: 250,
							handler: function (){
								if (this.up('form').getForm().isValid()){
									this.up('form').getForm().submit(
									{
										url: '{url action=saveAdmin}'
									}
									);
								}
							}
						}
					]
				}
				);
				this.items = [this.tree,this.form];

				this.callParent(arguments);
			}

		});

		Ext.define('Ext.app.AddWidget',
		{
			extend: 'Ext.window.Window',
			initComponent: function(){
				Ext.apply(this, {
					title: 'Widget hinzufügen',
					height: 500,
					closeable: true,
					padding: '10 10 10 10',
					width: 900,
					layout: 'border'
				});

				this.tree = Ext.create('Ext.tree.Panel', {
					region: 'west',
					title: 'Verfügbare Widgets',
					parent: this,
					useArrows: true,
					resizable: true,
					width: 300,
					tbar: Ext.create(
					'Ext.toolbar.Toolbar',
					{
						items: [
							{
								text: 'Aktualisieren',
								handler: function(){
									this.tree.store.load();
								},
								scope:this
							},
							{
								text: 'Löschen',
								handler: function(){
									var node = this.tree.getSelectionModel();

									if (node.hasSelection()){

										var node = node.getSelection();
										var uid = node[0].data.widgetUid;
										if (uid){
											Ext.Ajax.request({
												url: '{url action=deleteWidget}',
												params: {
													widgetUid: uid
												},
												success: function(response){

												}
											});
											// Delete widget
											Ext.Msg.alert('Info', 'Widget wurde entfernt');
											this.tree.store.load();
										}
									}

								},
								scope:this
							}
						]
					}
					),
					rootVisible: false,        
					store: Ext.create('Ext.data.TreeStore', {
						proxy: {
							type: 'ajax',
							url: '{url action=getWidgets}'
						},
						root: {
							text: 'Ext JS',
							id: 'src',
							expanded: true
						},
						folderSort: true,
						fields: [
						'id','leaf','text','widgetUid','widgetType'
						],
						sorters: [{
							property: 'text',
							direction: 'ASC'
						}]
					})
				});

				this.tree.on('itemclick',function(view,data){
					var id = data.data.widgetType;
					var uid = data.data.widgetUid;

					this.parent.form.setTitle("Widget " + data.data.text);
					this.parent.form.getEl().mask('Lade Settings');
				    
					var test = Ext.create('Swag.Config.'+id+"_"+uid+"_"+new Date().getTime(),{
						uid:  uid
					});
					this.parent.form.removeAll(true);
					this.parent.form.add(test);
					this.parent.form.doLayout();
					this.parent.form.getEl().unmask();
					

				});
				this.form = Ext.create('Ext.form.Panel',
				{
					title: '&nbsp;',
					region: 'center',
					padding: '10 10 10 10',
					autoScroll:true,
					buttons: [
						{
							text: 'Speichern',
							autoWidth: true,
							handler: function (){
								this.up('form').getForm().submit(
								{
									url: '{url action=saveSettings}',
									success: function(form, action){
										var result = action.result.data.widgetUid;
										var widgetType = action.result.data.widgetType;
										Ext.getCmp('widgetUid').setValue(result);
										Ext.Msg.show(
										{
											title: 'Status',
											msg: 'Widget added successfully',
											buttons: Ext.Msg.OK,
											icon: Ext.window.MessageBox.INFO,
											fn: function(){
												document.location.reload();
											}
										}
										);

										

									},
									failure: function(form,action){
										Ext.Msg.alert('Failed', action.result.msg);

									},
									scope: this
								}
								);
							}
						}
					]
				}
				);
				this.items = [this.tree,this.form];
				this.callParent(arguments);
			}

		});
		Ext.define('Ext.app.Portal', {

			extend: 'Ext.container.Viewport',

			uses: ['Ext.app.PortalPanel', 'Ext.app.PortalColumn', 'Ext.app.GridPortlet', 'Ext.app.ChartPortlet'],

			getTools: function(){
				return [{
					xtype: 'tool',
					type: 'refresh',
					handler: function(e, target, panelHeader, tool){

						var portlet = panelHeader.ownerCt;
						var item = portlet.items.items[0];
						try {
							item.refreshComponent();
						}catch (e){
							//alert('Method refreshComponent not found');
						}
						portlet.setLoading('Working...');
						Ext.defer(function() {
							portlet.setLoading(false);
						}, 2000);
					}
				}];
			},
			initComponent: function(){
				var content = '<div class="portlet-content">'+"Hallo Welt"+'</div>';

				Ext.apply(this, {
					id: 'app-viewport',
					title: 'Test',
					layout: {
						type: 'border',
						padding: '0 0 11 0'
					},
					items: [{
						xtype: 'portalpanel',
						bodyStyle: "background: transparent url({link file="templates/_default/backend/_resources/images/index/background_sample.jpg"}) repeat-x scroll !important",
												
						region: 'center',
						layout: 'border',
						 listeners: {
							'drop': function(e){
								var column = e.columnIndex;
								var panel = e.panel.id;
								var position = e.position;
								
								Ext.Ajax.request({
									url: '{url action=savePosition}',
									params: {
										column: column,
										widget: panel,
										position: position
									},
									success: function(response){
										
									}
								});

							}},
						items: [
						{if $firstUse == true}
						{
							id: 'colTemp',
							flex: 1,
							items: [
								{
									html: 'Fügen Sie Widgets zu Ihrer Startseite hinzu. Installieren Sie hierzu das Plugin "Shopware Standard Widgets" im Plugin-Manager!'
								}
							]
						}
						{else}
							{foreach from=$panel.cols item=col}
							{
								id: '{$col.id}',
								{if $col.width}columnWidth: {$col.width},{else}flex: {$col.flex},{/if}
								items: [
									{foreach from=$col.items item=widget}
									{
										id: '{$widget.uid}',
										widget: '{$widget.widgetType}',
										title: '{$widget.configuration.widgetLabel|escape}',
										tools: this.getTools(),
										items: [
											Ext.create('Swag.Widget.'+'{$widget.widgetType}'+"_"+'{$widget.uid}'+"_"+new Date().getTime(),{
											})
										],
										listeners: {
											'close': Ext.bind(this.onPortletClose, this)
										}
									}{if !$widget@last},{/if}
									{/foreach}
								]
							}{if !$col@last},{/if}
							{/foreach}
						{/if}
						]
					},
					{
						id: 'app-footer',
						xtype: 'toolbar',
						region: 'south',
						height: 30,
						items: [
								{
								text: 'Widget hinzufügen',
								handler: function(){
									Ext.create('Ext.app.AddWidget').show();
								},
								scope: this
							},
							{if $isAdmin}
							{
								text: 'Admin',
								handler: function(){
									Ext.create('Ext.app.Admin').show();
								},
								scope: this
							},{/if}
							'-',
							'Based on Enlight "Corporate Information System" (c) 2011 shopware AG <a href="http://www.enlight.de" target="_blank">www.enlight.de</a>'
						]
					}
					]
				});
				this.tools = [{
					xtype: 'tool',
					type: 'plus',
					handler: function(e, target, panelHeader, tool){
						alert('Test');
					}
				}];
				this.callParent(arguments);
			},

			onPortletClose: function(portlet) {
				this.showMsg('"' + portlet.title + '" was removed');
			},

			showMsg: function(msg) {
				var el = Ext.get('app-msg'),
					msgId = Ext.id();

				this.msgId = msgId;
				el.update(msg).show();

				Ext.defer(this.clearMsg, 3000, this, [msgId]);
			},

			clearMsg: function(msgId) {
				if (msgId === this.msgId) {
					Ext.get('app-msg').hide();
				}
			}
		});
        Ext.onReady(function(){
            Ext.create('Ext.app.Portal');
        }); 
	</script>
{/block}