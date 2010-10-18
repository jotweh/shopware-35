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

var myExt = function(){

	var sGrid;
	var sGridProxy;
	var sGridReader;
	var sGridWriter;
	var sGridStore;
	var sGridColumns;

	var sPARAMS;
	var sParamMissingFlag;

	var sTotalPrices;

	return{
		manageBundlePrices: function(display)
		{
			if(display)
			{
				$('bundlePricesDiv').setStyle('display', 'block');
				$('bundlePricesInfoDiv').setStyle('display', 'none');

				for(k in myExt.sTotalPrices)
				{
					var tmpPriceDiv = $(k);
					if(tmpPriceDiv != null)
					{
						tmpPriceDiv.innerHTML = number_format(myExt.sTotalPrices[k], 2);
					}
				}

			}else{
				$('bundlePricesDiv').setStyle('display', 'none');
				$('bundlePricesInfoDiv').setStyle('display', 'block');
			}
		},
		init : function(sPARAMS)
		{
			this.sPARAMS = sPARAMS;

			if(null == this.sPARAMS.bundleID || "" == this.sPARAMS.bundleID){
				alert("missing config param bundleID");
				sParamMissingFlag = true;
			}

			if(sParamMissingFlag != true)
			{
				//Initialisierung der Komponenten
				this.initGridProxy();
				this.initGridReader();
				this.initGridWriter();
				this.initGridStore();
				this.initGridColumns();

				//Laden der Gridstore
				this.loadGridStore();

				//Erstellung des GridPanels
				this.createGrid();
			}
		}

		,initGridProxy : function()
		{
			this.sGridProxy = new Ext.data.HttpProxy({
				api: {
					read : 		'bundle/bundle_read.php',
					create : 	'bundle/bundle_create.php',
					update : 	'bundle/bundle_update.php',
					destroy : 	'bundle/bundle_destroy.php'
				}
			});
		}

		,initGridReader : function()
		{
			var i=0;
			var readFiels = new Array();
			var field = new Ext.data.Field({name: 'ordernumber'}); 	readFiels[i]=field;  i++;
			var field = new Ext.data.Field({name: 'name'}); 		readFiels[i]=field;  i++;
			var field = new Ext.data.Field({name: 'main'}); 		readFiels[i]=field;  i++;

			//Dynamische Felder hinzufügen
			for(var k in this.sPARAMS.customerGrps)
			{
				var field = new Ext.data.Field({
				    name: 'cGrp_'+this.sPARAMS.customerGrps[k]['groupkey']
				});
				readFiels[i]=field;
				i++;
			}

			this.sGridReader = new Ext.data.JsonReader({
			    totalProperty: 'total',
			    successProperty: 'success',
			    idProperty: 'id',
			    root: 'data',
			    messageProperty: 'message',
			    fields: readFiels
			});
		}

		,initGridWriter : function()
		{
			this.sGridWriter = new Ext.data.JsonWriter({
			    encode: true,
			    writeAllFields: false
			});
		}

		,initGridStore : function()
		{
			this.sGridStore = new Ext.data.Store({
			    id: 'bundleStore',
			    proxy: this.sGridProxy,
			    reader: this.sGridReader,
			    writer: this.sGridWriter,
			    autoSave: true,
			    baseParams: {
			    	bundleID  : this.sPARAMS.bundleID
			    },
			    //Preisliste verwalten/aktualisieren
			    listeners: {'load': function(store, rec, opt){

			    	//Wenn ein Bundleartikel vorhanden ist
			    	if(store.totalLength > 1)
			    	{
			    		//Array zur Speicherung der Preise
				    	var tmpPrices = new Object();

				    	//Durchläuft jede Zeile der Store
				    	//summiert alle Preise der einzelnen Kundengruppen
				    	//und speichert diese in dem Object myExt.sTotalPrices
				    	//
				    	//Aufbaubeispiel:
				    	//cGrp_EK > 435.82
				    	//cGrp_H  > 351.82
				    	//usw.
				    	store.each(function(recOne){
				    		Ext.each(recOne.data, function(item, index, allItems){

				    			//Durchläuft alle verfügbaren Kundengruppen
				    			for(var k in myExt.sPARAMS.customerGrps)
				    			{
				    				var tmpCustomerKey = myExt.sPARAMS.customerGrps[k]['groupkey'];
				    				tmpKey = "cGrp_"+tmpCustomerKey;
				    				var tmpPrice = item[tmpKey];
				    				tmpPrice = tmpPrice.replace(',', '.');

				    				if(tmpPrices[tmpKey] != null)
				    				{
				    					tmpPrices[tmpKey] = eval(tmpPrices[tmpKey])+eval(tmpPrice);
				    				}else{
				    					tmpPrices[tmpKey] = tmpPrice;
				    				}
				    			}
							});
						}, this);

						myExt.sTotalPrices = tmpPrices;
						myExt.manageBundlePrices(true);
			    	}else{
			    		//Keine Bundleartikel vorhanden
			    		myExt.manageBundlePrices(false);
					}
				}}
			});
		}

		,initGridColumns : function()
		{
			function mainRenderer(value,p,r,rowIndex,i,ds){
				var main = r.data.main;
				if(1 == main)
					return "<b>"+value+"</b>";
            	else
            		return value;
            }

			var columns =  new Ext.grid.ColumnModel([
			    {header: "Artikelnummer", width: 120, sortable: false, dataIndex: 'ordernumber', renderer: mainRenderer, editor: new Ext.form.TextField({
			    	listeners: {
			    		specialkey: function(field, e){ Ext.getCmp('bundleGrid').onEnterTextField(field, e); },
			    		focus: function(field){
			    			var bundleGrid = Ext.getCmp('bundleGrid');

			    			//Nur editierbar, wenn eine neue Zeile hinzugefügt wurde
			    			//und die erste Zeile selektiert wurde
			    			if(!bundleGrid.unsavedRow || field.gridEditor.row != 0)
			    			{
			    				bundleGrid.stopEditing();
			    			}
			    		}
			    	}
				})},
			    {header: "Bezeichnung", width: 150, sortable: false, dataIndex: 'name', renderer: mainRenderer}
			]);

			//Dynamische Spalten hinzufügen
			for(var k in this.sPARAMS.customerGrps)
			{
				var curColConfig = columns.config;
				curColConfig.push({
				    header: this.sPARAMS.customerGrps[k]['description'],
				    width: 70,
				    sortable: false,
				    dataIndex: 'cGrp_'+this.sPARAMS.customerGrps[k]['groupkey'],
				    renderer: mainRenderer
				});
				columns.setConfig(curColConfig, true)
			}

			//Werte übernehmen
			this.sGridColumns = columns;
		}

		,createGrid : function()
		{
			this.sGrid = new de.shopware.BundleProxyGrid({
				id: 'bundleGrid',
		        title: 'Bundle Artikel',
				renderTo: 'bundleGridDiv',
		        store: this.sGridStore,
		        cm : this.sGridColumns,
		        listeners: {
		            rowclick: function(g, index, ev) {
		                var rec = g.store.getAt(index);
		                /*userForm.loadRecord(rec);*/
		            },
		            destroy : function() {
		                /*userForm.getForm().reset();*/
		            }
		        }
		    });
		}

		,loadGridStore : function()
		{
			this.sGridStore.load();
		}
	}
}();

//ABGELEITETES EDITORGRIDPANEL
de.shopware.BundleProxyGrid = Ext.extend(Ext.grid.EditorGridPanel, {
    renderTo: 'user-grid',
    title: 'default title',
    frame: true,
    clicksToEdit: 1,
    autoScroll: true,
    height: 350, //<-- Standardhöhe
    stripeRows: true,
    style: 'margin:0px 5px',

    initComponent : function() {

        // typical viewConfig
        this.viewConfig = {
            forceFit: true
        };

        //true = Neue Zeile hinzugefügt und noch nicht gespeichert
        this.unsavedRow = false;

        // relay the Store's CRUD events into this grid so these events can be conveniently listened-to in our application-code.
        this.relayEvents(this.store, ['destroy', 'save', 'update']);

        // build toolbars and buttons.
        this.tbar = this.buildTopToolbar();
        //this.bbar = this.buildBottomToolbar();
        this.bbar = this.buildUI();
        //this.buttons = this.buildUI();

        this.addListener('cellclick', this.onCellclick);

        // super
        de.shopware.BundleProxyGrid.superclass.initComponent.call(this);
    },

    /**
     * buildTopToolbar
     */
    buildTopToolbar : function() {
        return [{
            text: 'Hinzufügen',
            iconCls: 'silk-add',
            handler: this.onAdd,
            scope: this
        }, '-', {
            text: 'Löschen',
            id: 'deleteBtn',
            disabled: true,
            iconCls: 'silk-delete',
            handler: this.onDelete,
            scope: this
        }, '-'];
    },

    /**
     * buildUI
     */
    buildUI : function() {
        return [{
            text: 'Speichern',
            id: 'saveBtn',
            disabled: true,
            iconCls: 'icon-save',
            handler: this.onSave,
            scope: this
        }];
    },



    /**
     * onCellclick
     */
    onCellclick : function(grid, rowIndex, columnIndex, e) {
    	//Löschbutton aktivieren (außer bei dem neuen Feld und dem Hauptartikel)
    	//Im Hinzufügemodus
    	if(true == this.unsavedRow)
    	{
    		if(0 == rowIndex || 1 == rowIndex)
    			Ext.getCmp('deleteBtn').disable();
    		else
    			Ext.getCmp('deleteBtn').enable();
    	}else{
    		if(0 == rowIndex)
    			Ext.getCmp('deleteBtn').disable();
    		else
    			Ext.getCmp('deleteBtn').enable();
		}
    },


    /**
     * onEnterTextField
     */
    onEnterTextField : function(field, e) {
    	if (e.getKey() == e.ENTER) {
    		this.stopEditing();
    		this.onSave();
		}
    },


    /**
     * onSave
     */
    onSave : function(btn, ev) {
        //Update
        /*
        	var rs = [].concat(this.store.getModifiedRecords());
        	this.store.doTransaction('update', rs);
        */
        //CREATE
        //Überprüfung, ob eine neue Zeile hinzugefügt und
        //noch nicht gespeichert wurde
        if(this.unsavedRow != true) return false;

        //Erste Zeile (neues Element) auslesen
        var phantoms = this.store.getAt(0);

        //Überprüfung, ob die Bestellnummer bereits
        //dem Bundle hinzugefügt wurde und ob diese
        //überhaupt existiert
        var waitMask = new Ext.LoadMask(Ext.getBody(), {msg:"Bitte warten..."});
        waitMask.show();
        var tmpGridId = this.id;
        Ext.Ajax.request({
		   url: 'bundle/bundle_create.php',
		   success: function(r, op){
		   		var response = r.responseText;

				waitMask.hide();
				var sGrid = Ext.getCmp(tmpGridId);
		   		switch(response.trim())
		   		{
		   			case 'SUCCESS':
		   				Ext.Msg.alert('Status', 'Der Artikel wurde erfolgreich dem Bundle zugeordnet!');
        				sGrid.store.reload();
        				sGrid.unsavedRow = false;
		   			break;
		   			case 'EMPTY':
		   				Ext.Msg.alert('Fehler', 'Bitte geben Sie zunächst eine Bestellnummer ein!');
		   			break;
		   			case 'ARTICLE_OWN':
		   				Ext.Msg.alert('Fehler', 'Diese Artikelnummer gehört diesem Artikel und kann daher nicht zum Bundle hinzugefügt werden!');
		   			break;
		   			case 'ARTICLE_NOT_EXIST':
		   				Ext.Msg.alert('Fehler', 'Der gewünschte Artikel existiert nicht!');
		   			break;
		   			case 'ALREADY_EXIST':
		   				Ext.Msg.alert('Fehler', 'Dieser Artikel wurde bereits dem Bundle hinzugefügt!');
		   			break;
		   			default:
		   				Ext.Msg.alert('Fehler', 'Bei der Überprüfung der Bestellnummer ist ein unbekannter Fehler aufgetreten!');
		   			break;
		   		}
		   },
		   failure: function(){
		   		Ext.Msg.alert('Fehler', 'Bei der Überprüfung der Bestellnummer ist ein unbekannter Fehler aufgetreten!');
		   		waitMask.hide();
		   },
		   params: { ordernumber: phantoms.data.ordernumber, bundleID: this.store.baseParams.bundleID}
		});
    },

    /**
     * onAdd
     */
    onAdd : function(btn, ev) {
	    Ext.getCmp('deleteBtn').disable();
        this.stopEditing();

        if(this.unsavedRow != true)
        {
        	this.setUnsavedRowState(true);

        	var u = new this.store.recordType({
	            articleID : ''
	        });

        	//Neuen Datensatz hinzufügen
       		this.store.insert(0, u);
        }

        //Editierung starten [Zeile 0; Spalte 0]
        this.startEditing(0, 0);
    },

    setUnsavedRowState : function(state){
    	if(state){
    		this.unsavedRow = true;
    		Ext.getCmp('saveBtn').enable();
    	}else{
    		this.unsavedRow = false;
		}
    },

    /**
     * onDelete
     */
    onDelete : function(btn, ev) {
        var index = this.getSelectionModel().getSelectedCell();
        if (!index) {
            return false;
        }

    	var rec = this.store.getAt(index[0]);
    	var ordernumber = rec.get('ordernumber');

        Ext.Msg.confirm('Bestätigung', 'Soll der Artikel <b>'+ordernumber+'</b> wirklich aus dem Bundle entfernt werden?', this.onDeleteAck, this);
    },
    onDeleteAck : function(opt) {

    	var index = this.getSelectionModel().getSelectedCell();
    	var store = this.store;
    	var rec = store.getAt(index[0]);
    	var ordernumber = rec.get('ordernumber');

    	if("yes" == opt) {
    		var waitMask = new Ext.LoadMask(Ext.getBody(), {msg:"Bitte warten..."});
    		Ext.Ajax.request({
			   url: 'bundle/bundle_delete.php',
			   success: function(r, op){
			   		var response = r.responseText;

					waitMask.hide();
			   		switch(response.trim())
			   		{
			   			case 'SUCCESS':
			   				Ext.Msg.alert('Status', 'Der Artikel wurde erfolgreich aus dem Bundle entfernt!');
			   				store.remove(rec);
			   				Ext.getCmp('deleteBtn').disable();
			   				store.reload();
			   			break;
			   			case 'MISSING_PARAM_ORDERNUMBER':
			   				Ext.Msg.alert('Fehler', 'Es wurde keine Bestellnummer übergeben!');
			   			break;
			   			case 'DB_DELETE_FAILED':
			   				Ext.Msg.alert('Fehler', 'Der Bundleartikel konnte nicht aus der Datenbank entfernt werden!');
			   			break;
			   			default:
			   				Ext.Msg.alert('Fehler', 'Bei der Überprüfung der Bestellnummer ist ein unbekannter Fehler aufgetreten!');
			   			break;
			   		}
			   },
			   failure: function(){
			   		Ext.Msg.alert('Fehlercode 404', 'Die Ajaxdatei konnte nicht geladen werden!');
			   		waitMask.hide();
			   },
			   params: { ordernumber: ordernumber, bundleID: this.store.baseParams.bundleID}
			});
		}
	}
});
<?php if(false) {?> </script> <?php } ?>