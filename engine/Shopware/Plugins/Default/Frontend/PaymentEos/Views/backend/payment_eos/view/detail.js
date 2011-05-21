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
		fieldLabel: 'Kontonummer',
		name: 'account_number'
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
        readOnly: false,
        minValue: new Date()
    },{
        xtype: 'numberfield',
        decimalPrecision: 2,
        fieldLabel: 'Buchungsbetrag',
        name: 'book_amount',
        hiddenName: 'book_amount',
        readOnly: false
    }],
    
    initComponent: function() {
        
        this.buttons = [{
        	itemId: 'bookButton',
			text: 'Buchen',
			handler: function (a, b, c){
				var form = this.getForm();
				if (!form.isValid()) {
					return;
				}
				Ext.MessageBox.wait('Bitte warten ...', 'Buchen'); 
				form.submit({
					url: '{url module=frontend controller=payment_eos action=book}',
					success: function(form, action) {
						Ext.Msg.alert('Success', action.result.message);
					},
				    failure: function(form, action) {
				        switch (action.failureType) {
				            case Ext.form.action.Action.CLIENT_INVALID:
				                Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
				                break;
				            case Ext.form.action.Action.CONNECT_FAILURE:
				                Ext.Msg.alert('Failure', 'Ajax communication failed');
				                break;
				            case Ext.form.action.Action.SERVER_INVALID:
				               Ext.Msg.alert('Failure', action.result.message);
				       }
				    }
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
					form.submit({
						url: '{url module=frontend controller=payment_eos action=cancel}',
						success: function(form, action) {
							Ext.Msg.alert('Success', action.result.msg);
						},
						failure: function(form, action) {
							Ext.Msg.alert('Failed', action.result.msg);
						}
					});
				}, this);
			},
			scope: this
		}];
        
        this.callParent();
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
		
		//	form.findField('book_date').hide();
		//	form.findField('book_amount').hide();
		
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
		} else {
			buttons.getComponent('bookButton').hide();
			form.findField('book_date').setReadOnly(true);
			form.findField('book_amount').setReadOnly(true);
		}
    }
});