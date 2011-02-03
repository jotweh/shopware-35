{extends file="backend/index/parent.tpl"}
{block name="backend_index_body_inline"}
<script>
	Ext.ns('Shopware.Cache');	
	(function(){
		View = Ext.extend(Ext.Viewport, {
			autoScroll: true,
			layout:'fit',
			initComponent: function() {
				this.panelTop = new Ext.Panel ({
					region: 'north',
					padding: '10px 10px 10px 10px',
					height:80,
					title: 'Hinweise',
					html: 'Bitte beachten Sie, dass der Cache sich in bestimmten Abständen selbstständig generiert und nur in Ausnahmefällen manuell gelöscht werden sollte. Nach dem Leeren des Cache kann es einige Sekunden dauern, bis das Shop-Frontend wieder erreichbar ist!',
					style: 'font-family: tahoma,arial,verdana,sans-serif;font-size:11px;font-weight:bold'
				});
				this.panelBottom = new Ext.Panel ({
					//padding: '10px 10px 10px 10px',
					title: 'Cache-Informationen',
					preventBodyReset: true,
					html: {"
						<ul>
							<li><strong>Backend:</strong> {$CacheInformation.backend}</li>
							<li><strong>Verzeichnis:</strong> {$CacheInformation.cache_dir}</li>
							<li><strong>Dateien:</strong> {$CacheInformation.cache_files}</li>
							<li><strong>Größe:</strong> {$CacheInformation.cache_size}</li>
							<li><strong>Freier Speicher:</strong> {$CacheInformation.free_space}</li>
						</ul>
					"|utf8_encode|json_encode},
					style: 'font-family: tahoma,arial,verdana,sans-serif;font-size:11px;font-weight:normal'
				});
				/*
				this.panelBottom = new Ext.Panel ({
					region: 'south',
					padding: '10px 10px 10px 10px',
					title: 'Automatisierung',
					height:70,
					html: 'Viele Caching-Routinen, wie z.B. die Erzeugung der SEO-Urls, können Sie über Cronjobs automatisieren. Beachten Sie die Einstellungsmöglichkeiten in der Cronjob-Konfiguration!',
					style: 'font-family: tahoma,arial,verdana,sans-serif;font-size:11px;font-weight:normal'
				});	
				*/			
				this.cacheForm = new Ext.form.FormPanel({
					title: 'Welche Bereiche sollen geleert werden?',
					padding: '10px 10px 10px 10px',
					id: 'cacheForm',
					autoScroll:true,
					items: [
						{
							id: 'cache_db',
							name: 'cache[adodb]',
							xtype: 'checkbox',
							tooltip: 'Test',
							boxLabel: 'Datenbank Query-Cache',
							hideLabel:true
			
						},
						{
							id: 'cache_config',
							name: 'cache[config]',
							xtype: 'checkbox', 
							boxLabel: 'Config-Cache (Textbausteine etc.)',
							hideLabel:true
			
						},
						/*
						{
							id: 'cache_smarty',
							name: 'cache[template]',
							xtype: 'checkbox',
							boxLabel: 'Seiten-Cache',
							hideLabel:true
			
						},
						*/
						{ 
							id: 'cache_seo',
							name: 'cache[seo]',
							xtype: 'checkbox',
							boxLabel: 'SEO URL Cache',
							hideLabel:true
			
						},
						{
							id: 'cache_search',
							name: 'cache[search]',
							xtype: 'checkbox',
							boxLabel: 'Intelligente Suche Index/Keywords',
							hideLabel:true
						}
						,
						{
							id: 'cache_plugin',
							name: 'cache[plugins]',
							xtype: 'checkbox',
							boxLabel: 'Plugin Cache',
							hideLabel:true
						}
					],
					buttons: [{
			            text: 'Alle markieren',
			            handler: function (){
			            	 Ext.each(Ext.getCmp('cacheForm').items.items,function(item){
				                item.setValue(true);
			                }
			                );
			            }
				        },{
			            text: 'Leeren',
			            handler: function (){
			            	Ext.getCmp('cacheForm').getForm().submit({ url:'{url action="clearCache"}', waitMsg:'Cache leeren...', submitEmptyText: false});	
			            }
				        }
			        ]
				});
				this.cacheForm2 = new Ext.form.FormPanel({
					title: 'Welche weiteren Bereiche sollen geleert werden?',
					padding: '10px 10px 10px 10px',
					id: 'cacheForm2',
					region: 'center',
					autoScroll:true,
					items: [
						{
							name: 'cache[compiler]',
							xtype: 'checkbox',
							boxLabel: 'Template-Cache',
							hideLabel:true,
			
						},
						{
							name: 'cache[locale]',
							xtype: 'checkbox',
							boxLabel: 'Datei-Cache',
							hideLabel:true
						}
					],
					buttons: [{
			            text: 'Alle markieren',
			            handler: function () {
			            	Ext.each(Ext.getCmp('cacheForm2').items.items,function(item){
				                item.setValue(true);
			                });
			            }
			        }, {
			            text: 'Leeren',
			            handler: function (){
			            	Ext.getCmp('cacheForm2').getForm().submit({ url:'{url action="clearStaticCache"}', waitMsg:'Cache leeren...', submitEmptyText: false});	
			            }
			        }]
				});				
				this.items = new Ext.Panel({
			    	border: false,
			    	autoScroll: true,
		    		items: [this.panelTop,this.cacheForm, this.cacheForm2, this.panelBottom]
		    	});
		        View.superclass.initComponent.call(this);
			}
		});
		Shopware.Cache.View = View;
	})();;
	Ext.onReady(function(){
		Ext.QuickTips.init();
		Cache = new Shopware.Cache.View;
	});
</script>
{/block}