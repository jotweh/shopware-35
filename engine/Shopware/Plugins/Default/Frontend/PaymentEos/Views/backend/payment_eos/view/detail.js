Ext.define('PaymentEos.view.Detail', {
    extend: 'Ext.form.Panel',

    width: 400,
    layout: 'anchor',
    defaults: {
        anchor: '100%',
        readOnly: true,
		//disabled: true
		//allowBlank: false
    },
    defaultType: 'textfield',
	bodyPadding: 7,
    
    items: [{
        xtype: 'hiddenfield',
        name: 'payment_key'
    },{
        xtype: 'hiddenfield',
        name: 'userID'
    },{
        xtype: 'hiddenfield',
        name: 'currency'
    },{
        xtype: 'hiddenfield',
        name: 'secret'
    }, {
        xtype: 'hiddenfield',
        name: 'werbecode'
    }, {
		fieldLabel: 'Transaktions-ID',
		name: 'transactionID'
	},{
		fieldLabel: 'Bestellnummer',
		name: 'order_number'
	},{
		fieldLabel: 'Kunde',
		name: 'customer'
	},{
		fieldLabel: 'Referenz',
		name: 'reference'
	},{
		fieldLabel: 'Bankkonto/Karte',
		name: 'bank_account'
	},{
        xtype: 'textarea',
        fieldLabel: 'Letzte Fehlermeldung',
        name: 'fail_message'
    },{
        xtype: 'datefield',
        fieldLabel: 'Anlegungsdatum',
        name: 'added'
    },{
		xtype: 'numberfield',
		decimalPrecision: 2,
        fieldLabel: 'Reservierter Betrag',
        name: 'amount'
    },{
        xtype: 'datefield',
        fieldLabel: 'Buchungsdatum',
        name: 'book_date',
        hiddenName: 'book_date',
        readOnly: false
    },{
        xtype: 'numberfield',
        decimalPrecision: 2,
        fieldLabel: 'Buchungsbetrag',
        name: 'book_amount',
        hiddenName: 'book_amount',
        readOnly: false
    },{
        xtype: 'numberfield',
        decimalPrecision: 2,
        fieldLabel: 'Gutschriftsbetrag',
        name: 'memo_amount',
        hiddenName: 'memo_amount',
        readOnly: false
    }],
    
    onSubmitFailure: function(form, action) {
		switch (action.failureType) {
			case Ext.form.action.Action.CLIENT_INVALID:
				Ext.Msg.alert('Fehler', 'Form fields may not be submitted with invalid values');
				break;
			case Ext.form.action.Action.CONNECT_FAILURE:
				Ext.Msg.alert('Fehler', 'Ajax communication failed');
				break;
			default:
			case Ext.form.action.Action.SERVER_INVALID:
				Ext.Msg.alert('Fehler', action.result.message);
				break;
		}
	},
    
    initComponent: function() {
        
        this.buttons = [{
        	itemId: 'refreshButton',
			text: 'Aktualisieren',
			handler: function (a, b, c){
				var form = this.getForm();
				if (!form.isValid()) {
					return;
				}
				Ext.MessageBox.wait('Bitte warten ...', 'Aktualisieren'); 
				form.submit({
					url: '{url module=frontend controller=payment_eos action=refresh forceSecure}',
					success: function(form, action) {
						Ext.Msg.alert('Erfolgreich', action.result.message);
						this.loadDetail();
					},
					failure: this.onSubmitFailure,
					scope: this
				});
			},
			scope: this
		}, {
			itemId: 'cancelButton',
			text:'Stornieren',
			handler: function (a, b, c){
				Ext.MessageBox.confirm('Confirm', 'Wollen Sie wirklich diese Zahlung stornieren?', function(r){
					if(r!='yes') {
						return;
					}
					var form = this.getForm();
					if (!form.isValid()) {
						return;
					}
					Ext.MessageBox.wait('Bitte warten ...', 'Stornieren'); 
					form.submit({
						url: '{url module=frontend controller=payment_eos action=cancel forceSecure}',
						success: function(form, action) {
							Ext.Msg.alert('Erfolgreich', 'Die Zahlung konnte erfolgreich storniert werden.');
							this.loadDetail();
						},
						failure: this.onSubmitFailure,
						scope: this
					});
				}, this);
			},
			scope: this
		}, {
        	itemId: 'memoButton',
			text: 'Gutschrift',
			handler: function (a, b, c){
				var form = this.getForm();
				if (!form.isValid()) {
					return;
				}
				Ext.MessageBox.wait('Bitte warten ...', 'Gutschrift'); 
				form.submit({
					url: '{url module=frontend controller=payment_eos action=memo forceSecure}',
					success: function(form, action) {
						Ext.Msg.alert('Erfolgreich', 'Die Gutschrift konnte erfolgreich angelegt werden.');
						this.loadDetail();
					},
					failure: this.onSubmitFailure,
					scope: this
				});
			},
			scope: this
		}, {
        	itemId: 'bookButton',
			text: 'Buchen',
			handler: function (a, b, c){
				var form = this.getForm();
				if (!form.isValid()) {
					return;
				}
				Ext.MessageBox.wait('Bitte warten ...', 'Buchen'); 
				form.submit({
					url: '{url module=frontend controller=payment_eos action=book forceSecure}',
					success: function(form, action) {
						Ext.Msg.alert('Erfolgreich', 'Der Betrag konnte erfolgreich gebucht werden.');
						this.loadDetail();
					},
					failure: this.onSubmitFailure,
					scope: this
				});
			},
			scope: this
		}];
        
        this.callParent();
    },
    
    loadDetail: function() {
    	var id = this.getForm().getRecord().getId();
    	var store = this.listView.store;
    	store.load({
		    scope   : this,
		    callback: function(records, operation, success) {
				this.updateDetail(store.getById(id));
		    }
		});
    },

    updateDetail: function(record) {
		var form = this.getForm();
		var buttons = this.getDockedComponent(0);
		
		form.loadRecord(record);
		
		if(record.get('clear_status') == 1 || record.get('clear_status') == 2) {
			buttons.getComponent('cancelButton').show();
		} else {
			buttons.getComponent('cancelButton').hide();
		}
		
		if(record.get('clear_status') == 1 || record.get('book_amount')) {
			form.findField('book_date').show();
			form.findField('book_amount').show();
		} else {
			form.findField('book_date').hide();
			form.findField('book_amount').hide();
		}
				
		if(record.get('clear_status') == 1) {
			buttons.getComponent('bookButton').show();
			form.findField('book_date').setReadOnly(false);
			form.findField('book_amount').setReadOnly(false);
			
			form.findField('book_amount').setMaxValue(record.get('amount'));
			var maxValue = new Date(record.get('added').getTime());
			maxValue.setDate(maxValue.getDate() + 12)
			form.findField('book_date').setMaxValue(maxValue);
			form.findField('book_date').setMinValue(new Date());
		} else {
			buttons.getComponent('bookButton').hide();
			form.findField('book_date').setReadOnly(true);
			form.findField('book_amount').setReadOnly(true);
			form.findField('book_date').setMinValue(null);
		}
		
		form.findField('memo_amount').setValue(null);
		
		if(record.get('clear_status') == 2) {
			buttons.getComponent('memoButton').show();
			form.findField('memo_amount').show();
			form.findField('memo_amount').setMaxValue(record.get('book_amount'));
		} else {
			buttons.getComponent('memoButton').hide();
			form.findField('memo_amount').hide();
		}
    }
});