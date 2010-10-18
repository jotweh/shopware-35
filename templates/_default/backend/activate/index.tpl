{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
	<!-- Common CSS -->
<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
<link href="{link file='engine/backend/css/modules.css'}" rel="stylesheet" type="text/css" />
<link href="{link file='backend/_resources/styles/activate.css'}" rel="stylesheet" type="text/css" />

{/block}
{block name="backend_index_body_inline"}
<script>
Ext.ns('Shopware.Activate');	
Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
     onRender : function(ct, position){
          this.el = ct.createChild({ tag: 'iframe', id: 'iframe-'+ this.id, frameBorder: 0, src: this.url});
     }
});
	Ext.QuickTips.init();
	(function(){
		
		
	Shopware.Activate.Form = Ext.extend(Ext.FormPanel,
	{
		labelWidth: 75, // label settings here cascade unless overridden
	    frame:true,
	    bodyStyle:'padding:5px 5px 0',
	   //defaults: { width: 230},
	    defaultType: 'textfield',
		initComponent: function() {
			this.items = [
		        	{
		                fieldLabel: 'Lizenzschlüssel',
		                name: 'license',
		                allowBlank:false,
		                width: 650
		            }
		            ,
		        	{
		                fieldLabel: 'Aktivierungskey',
		                name: 'key',
		                allowBlank:false,
		                width: 650
		            },
		        	{
		        		xtype: 'textarea',
		                fieldLabel: 'Sonstige Lizenzen',
		                name: 'sql',
		                allowBlank:true,
		                width: 650
		            }
	        	];
	        	
		        this.buttons = [{
			            text: 'Aktivieren',
			            handler: function(){
			            	
			            	this.getForm().submit({ url: '{url module=backend controller=activate action=activate}'});
			            	
			            },
			            scope:this
		        }];
		        
		        
				Shopware.Activate.Form.superclass.initComponent.call(this);
				
		},
		initEvents: function (){
	    	this.on('actioncomplete',function(form,action){
					if (action.type=="load") return;
					var result = action.result;
					if (!result.error){
						Ext.MessageBox.show({
				           title: 'Aktivierung abgeschlossen!',
				           msg: 'Das Backend wird nun neu geladen. Bitte leeren Sie anschließend den Shopcache!',
				           buttons: Ext.MessageBox.OK,
				           animEl: 'mb9',
					           fn: function (){
				           		parent.location.href = '{url module=backend controller=index}';
				           } ,
				           icon: Ext.MessageBox.INFO
			  			});
			  			
					}else {
						Ext.MessageBox.show({
				           title: 'Aktivierung fehlgeschlagen!',
				           msg: 'Die eingebenen Daten konnten nicht validiert werden. Bitte überprüfen Sie Ihre Eingaben!',
				           buttons: Ext.MessageBox.OK,
				           animEl: 'mb9',
				           icon: Ext.MessageBox.ERROR
			  			});
					}
	        },this);
	    	
	    	Shopware.Activate.Form.superclass.initEvents.call(this);
	    }
	}
	);
		View = Ext.extend(Ext.Viewport, {
			layout: 'border',
			initComponent: function() {
				this.form = new Shopware.Activate.Form();
				this.tabs = new Ext.TabPanel({
			        region: 'center',
			        activeTab: 0,
			        bodyBorder: false,
			        border: false,
			        plain:true,
			        hideBorders:false,
			        defaults:{ autoScroll: true},
			        items:[{
			                title: 'Informationen',
			                contentEl:'information'
			            },{
			                title: 'Aktivierung',
			                layout: 'fit',
			                items: [ new Ext.ux.IFrameComponent({ id: id, url: "http://account.shopware.de/register/panel1.php?domain={$domain}" }) ],
			                disabled: false
			            },{ 
			                title: 'Eingabe Lizenz',
			                items: [this.form],
			                disabled: false
			            }
			        ]
			    });
				
				
				this.items = [this.tabs];
		        View.superclass.initComponent.call(this);
			}
		});
		Shopware.Activate.View = View;
	})();;
	Ext.onReady(function(){
		View = new Shopware.Activate.View;
		
	});
</script>
<div id="information">
<img src="{link file='backend/_resources/images/activate/ce_info_band.png'}" style="position:absolute;right:10px">
	<div class="container">
		<div class="col_1">
			
			<div class="sw_buddy"></div>
			<div class="buttons">
				<a class="btn_green" title="Community Version freischalten" href="#" onclick="View.tabs.activate(1);">Community Version freischalten</a>
				<a class="btn_blue" title="Verhandenen Lizenzkey eingeben" href="#" onclick="View.tabs.activate(2);">Verhandenen Lizenzkey eingeben</a>
			</div>
		</div>
		<div class="col_2">
			<h2>Aktivierung Lizenz</h2><br />
			<p>Für die Verwendung von Shopware ist die einmalige Anforderung einer domainbezogenen Lizenz notwendig.
			Falls Sie Shopware in der Community Version nutzen möchten, klicken Sie einfach auf den Button <strong>"Community Version freischalten"</strong>.
			Im nachfolgenden Aktivierungsassisten generieren Sie Ihre persönliche Shopware-ID, mit der Sie Zugriff auf die Shopware Community
			und den Plugin-Store erhalten. Nach Abschluss der Aktivierung erhalten Sie automatisch Ihren Lizenzschlüssel und Aktivierungskey,
			den Sie einfach im Reiter "Eingabe Lizenz" hinterlegen können.
			<br /><br />			
			Falls Sie eine reguläre Lizenz besitzen und das System freischalten möchten, klicken Sie auf den Button
			<strong>"Vorhandenen Lizenzkey eingeben".</strong>
			</p>
			<br /><h2>Wofür benötige ich die Shopware-ID?</h2><br />
			<p>
			Mit der Shopware-ID und Ihrem Passwort haben Sie vollen Zugriff auf die Shopware-Community Seiten.
			Dort können Sie unter anderem Plugins herunterladen, die Roadmap von Shopware mitgestalten, an Foren-Diskussionen teilnehmen oder auch
			Testversionen von Erweiterungsmodulen anfordern.</p>
			<br /><h2>Welche Daten werden für die Aktivierung benötigt?</h2><br />
			<p>Für die reine Aktivierung der Lizenz müssen Sie nur Ihre Domain, Ihre Mail-Adresse, die gewünschte Shopware-ID und Ihr Wunschpasswort hinterlegen. Alle weiteren Daten sind optional!</p>
			<div class="clear"></div>
		</div>
			
			
		</div>
	</div>
</div>
{/block}