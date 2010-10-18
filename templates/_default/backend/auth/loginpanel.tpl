{* <script> *}
Shopware.Auth.Components.LoginPanel = Ext.extend(Ext.form.FormPanel, {
    title: 'Login Shopware Backend',
    width: 440,
    height: 180,
    padding: 3,
    layout: 'auto',
    x: 500,
    y: 240,
    frame: true,
	constructor: function (config){
    	
		 Shopware.Auth.Components.LoginPanel.superclass.constructor.call(this, Ext.applyIf(config || {},
         {         	

         }));
	},
	
    initComponent: function() {
    	this.emblem = new Ext.form.FieldSet({
            	id: 'emblem',
                width: 120,
                height: 140,
                title: '',
                border: false
        });
        this.items = [
            {
                xtype: 'fieldset',
                width: 300,
                title: '',
                border: false,
                style: 'float:left',
                items: [
                    {
                        xtype: 'textfield',
                        name:'username',
                        fieldLabel: 'Benutzername',
                        anchor: '100%',
                        allowBlank: false,
                        blankText: 'Geben Sie einen Benutzernamen an!',
                        listeners: { 'specialkey': function(el, e){
                        	if (e.getKey() == e.ENTER) {
                        		this.getForm().submit({ url: '{url action="login"}',waitMsg:'Login...'});
                        	}
                        }, scope:this }
                    },
                    {
                        xtype: 'textfield',
                        id:'password',
                        fieldLabel: 'Passwort',
                        inputType: 'password',
                        anchor: '100%',
                        allowBlank: false,
                        blankText: 'Geben Sie ein Passwort an!',
                        listeners: { 'specialkey': function(el, e){
                        	if (e.getKey() == e.ENTER) {
                        		this.getForm().submit({ url: '{url action="login"}',waitMsg:'Login...'});
                        	}
                        }, scope:this }
                    },
                    {
                        xtype: 'combo',
                        value:'Deutsch',
                        fieldLabel: 'Sprache',
                        disabled: true,
                        mode: 'local',
                        anchor: '100%',
                        valueField: 'id',
                        displayField: 'value',
                        listeners: { 'specialkey': function(el, e){
                        	if (e.getKey() == e.ENTER) {
                        		this.getForm().submit({ url: '{url action="login"}',waitMsg:'Login...'});
                        	}
                        }, scope:this },
                        store: new Ext.data.SimpleStore({
                        	fields: ['id', 'value'],
                        	data: [[1, 'Deutsch']]
                        })
                    },
                    {
		                xtype: 'button',
		                text: 'Anmelden',
		                iconCls: 'icon-key',
		                style:'margin-left:205px;margin-top:10px',
		                handler: function() {
		                	this.getForm().submit({ url: '{url action="login"}',waitMsg:'Login...'});
		                },
		                scope: this
		            }                    
                ]
            },
            this.emblem
            
        ];
        
     
        this.emblem.style = { background:"url({link file='backend/_resources/images/index/logo_login.png'}) no-repeat center center"};
        Shopware.Auth.Components.LoginPanel.superclass.initComponent.call(this);
    },
    
    submit: function(){
    	this.getForm().submit();
    },
    
    actioncomplete: function(el, action){
    	if(action.result.success == true){
	    	document.location.href = action.result.location;
		}     	
    },
    actionfailed: function(el){
		Ext.Msg.show({
		   title:'Anmeldung fehlgeschlagen!',
		   msg: 'Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden.',
		   buttons: Ext.Msg.OK,
		   icon: Ext.MessageBox.ERROR
		});
    },
    
    initEvents: function(){
		this.on('afterrender', this.setPanelPosition, this);
		this.parent.on('resize', this.setPanelPosition, this);
		this.on('actioncomplete', this.actioncomplete, this);
		this.on('actionfailed', this.actionfailed, this);
		//this.emblem.applyStyles({ 'background-color','#0F0'});
		Shopware.Auth.Components.LoginPanel.superclass.initEvents.call(this);
    },
    
    setPanelPosition: function(){
    	vheight = this.parent.getHeight();
    	pheight = this.getHeight();
    	theight = vheight-pheight;
    	
    	vwidth = this.parent.getWidth();
    	pwidth = this.getWidth();
    	twidth = vwidth-pwidth;
    	
    	this.setPosition(twidth/2, theight/2);
    }
});
{* </script> *}