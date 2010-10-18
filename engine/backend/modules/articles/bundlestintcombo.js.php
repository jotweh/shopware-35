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
// *****************
?>
<?php /*Editor highlight hack*/ if(false) {?> <script type="text/javascript"> <?php } ?>

Ext.ns('de', 'de.shopware');



//ABGELEITETES EDITORGRIDPANEL
de.shopware.BundleCombo = Ext.extend(Ext.form.ComboBox, {

    renderTo: 'user-grid'
	,store: new Ext.data.SimpleStore({fields:[['id'], ['ordernumber']], data:[[]]})
	,displayField: 'ordernumber'
	,valueField: 'id'
	,mode: 'local'
	,onSelect:Ext.emptyFn
	,tpl: '<tpl for="."></tpl>'
	,editable: false

    ,initComponent : function() {

    	if(this.checkboxgroupItems == null) alert("missing config checkboxgroupItems for BundleCombo");


    	/* LISTENERS */
    	this.addListener('render', this.onComboRender);
    	this.addListener('expand', this.onComboExpand);
    	this.addListener('collapse', this.onComboCollapse);
    	this.addListener('focus', function(){
	    	this.setValue(null);
		});

        //Call supermothod
        de.shopware.BundleCombo.superclass.initComponent.call(this);
    }

    /* LISTENERS */
    /* onComboRender */
    ,onComboRender : function(combobox){
    	combobox.store.add(new Ext.data.Record({id:-1, ordernumber:"Keine Beschränkung"}));
    	combobox.setValue(-1);

		//Set default value
		try{
			var tmpValue = $('sBundleStint').value;
			if(tmpValue.trim() != "")
				this.setValue(tmpValue);
			else
				this.setValue(-1);
		}catch(e){}
	}

    /* onComboExpand */
    ,onComboExpand : function(combobox){
    	this.setValue("Daten werden geladen..");
    	Ext.destroy(Ext.getCmp('checkboxgroupForm'));

    	var checkboxgroup = new Ext.form.CheckboxGroup({
			xtype: 'checkboxgroup',
			hideLabel: true,
			itemCls: 'x-check-group-alt',
			id: 'x1',
			columns: 1,
			items: this.checkboxgroupItems
		});

		var checkboxgroupForm = new Ext.FormPanel({
			id: 'checkboxgroupForm',
	        labelWidth: 75,
	        frame:false,
	        bodyStyle:'padding:5px 5px 0',
	        width: 350,
	        defaults: {width: 230},
	        defaultType: 'textfield',

	        items: checkboxgroup
	    });
	    checkboxgroupForm.render(this.innerList);

	    //Get Stint Ordernumbers
	    //and set checked
	    var sBundleStint = $('sBundleStint');
	    if(sBundleStint != null)
	    {
	    	sBundleStintOrdn = sBundleStint.value;
	    	sBundleStintOrdn = sBundleStintOrdn.trim();
	    	if(sBundleStintOrdn != "")
	    	{
	    		sBundleStintOrdnArray = sBundleStintOrdn.split(';');
	    		for(var i=0; i < sBundleStintOrdnArray.length; i++)
	    		{
	    			try{
	    				var chbID = "chkb_"+sBundleStintOrdnArray[i];
	    				Ext.getCmp(chbID).setValue(true);
	    			}catch(e){}
				}
	    	}
	    }
	}

	,onComboCollapse : function(combobox){
		var newValues = "";
		var values = Ext.getCmp('checkboxgroupForm').getForm().getValues();
		for(var item in values)
		{
			if(values[item]=='on')
			{
				if(newValues != "")
					newValues+= ";"+item;
				else
					newValues = item;
			}
		}

		try{
			$('sBundleStint').value = newValues;
			if(newValues!="")
			{
				this.setValue(newValues);
			}else{
				this.setValue(-1);
			}

		}catch(e){}
	}
});
<?php if(false) {?> </script> <?php } ?>