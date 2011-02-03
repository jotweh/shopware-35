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
<html>
<head>
  <title><?php echo $sLang["statistics"]["statistics_stat"] ?></title>
 	<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
	<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

	<script type="text/javascript" src="ux/gridsummary.js"></script>
	<script type="text/javascript" src="ux/uxmedia.js"></script>
	<script type="text/javascript" src="ux/uxflash.js"></script>
	<script type="text/javascript" src="ux/uxchart.js"></script>
	<script type="text/javascript" src="ux/uxfusion.js"></script>
	
	<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
 	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
	html, body {
        font:normal 12px verdana;
        margin:0;
        padding:0;
        border:0 none;
        height:100%;
    }
	p {
	    margin:5px;
	}
    .settings {
        background-image:url(../shared/icons/fam/folder_wrench.png);
    }
    .nav {
        background-image:url(../shared/icons/fam/folder_go.png);
    }
    .x-panel-body {
    	background-color:#FFF
    }
    Ext.ux.grid.GridSummary plugins */
	.x-grid3-summary-row{border-left:1px solid #fff;border-right:1px solid #fff;color:#333;background:#f1f2f4;}
	.x-grid3-summary-row .x-grid3-cell-inner{font-weight:bold;padding-bottom:4px;}
	.x-grid3-cell-first .x-grid3-cell-inner{padding-left:16px;}
	.x-grid-hide-summary .x-grid3-summary-row{dis play:none;}
	.x-grid3-summary-msg{padding:4px 16px;font-weight:bold;}
	
	
	/* [REQUIRED] (by Ext.ux.grid.GridSummary plugin) */
	.x-grid3-gridsummary-row-inner{overflow:hidden;width:100%;}/* IE6 requires width:100% for hori. scroll to work */
	.x-grid3-gridsummary-row-offset{width:10000px;}
	.x-grid-hide-gridsummary .x-grid3-gridsummary-row-inner{display:none;}
	
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
	
	<script type="text/javascript">
	var fusion;
    Ext.lib.Ajax.forceActiveX = true;
    

var myExt = function(){
	var grid;
	var store;
	var options = {};
	return {
		
	loadRefererKeywords: function (referer)
	{
		options.keywords = 1;
		options.node = referer;
		myExt.filterGrid();
	},
	loadReferer: function (referer){
		options.keywords = 0;
		options.node = referer;
		myExt.filterGrid();
	},
	filterGrid: function (e)
	{
		var refresh = !e||!e.attributes;
		
		
		if (!e||!e.attributes){
			var e = Ext.getCmp('reports').getSelectionModel().getSelectedNode();
		}
		if (!e){
			Ext.Msg.alert('Hinweis!','Wählen Sie zuerst eine Auswertung bevor Sie die Filterfunktion einsetzen!');
			return false;
		}

		var startDate = Ext.getCmp("startdt");
		startDate = startDate.getValue();
		startDate = startDate.dateFormat("d.m.Y");

		var endDate = Ext.getCmp("enddt");
		endDate = endDate.getValue();
		endDate = endDate.dateFormat("d.m.Y");

		//var state = Ext.getCmp("filterstate").getValue();
		var state = 0;
		var tax = Ext.getCmp("filtertax").getValue();

		if (e.attributes.file){
			load = e.attributes.file;
		}else {
			load = e.attributes.id;
		}
		// Load Grid
		grid.loadMask.el.mask("Bitte warten...");
		store = new Ext.data.Store({
			autoLoad: true,
			url: "charts2.php?r="+load+"|"+startDate+"|"+endDate+"|"+e.attributes.id+"|<"+e.attributes.range+"|"+"&table=1&group="+state+"&tax="+tax,
			reader: new Ext.data.JsonReader(
			{root: 'rows', totalProperty:'totalCount',id:0}
			)
		});
		grid.store = store;
		Ext.getCmp('myToolbar').bindStore(store,true);
		//Ext.getCmp('myToolbar').bind(store);
		store.on('load',function(){
			store.on('load',grid.loadMask.el.unmask());
		});
		store.on("metachange", grid.onMetaChange, grid);

		store.load({params: {meta: true,start:0,limit:100, node: options.node, keywords: options.keywords}});

		if(refresh)
		{
			if (e.attributes.chart)
			{
				var dataUrl = "charts2.php?r="+load+"|"+startDate+"|"+endDate+"|"+e.attributes.id+"|<"+e.attributes.range+"|"+state+"|"+tax;
				Ext.getCmp('chartpanel').load(dataUrl);
			}
		}
		else
		{
			Ext.getCmp('statisticPanel').remove('chartpanel');
			Ext.destroy(Ext.getCmp('chartpanel'));
			if (e.attributes.chart){
				var chartURL = "../../../vendor/flashgraph/Charts/"+e.attributes.chart+".swf";
				var dataUrl = "charts2.php?r="+load+"|"+startDate+"|"+endDate+"|"+e.attributes.id+"|<"+e.attributes.range+"|"+state+"|"+tax;

				var tab = {
					xtype: 'fusionpanel',
					title       : 'Chart',
					autoScroll : true,
					id       : 'chartpanel',
					chartURL : chartURL,
					dataURL  : dataUrl,
				};
				Ext.getCmp('statisticPanel').add(tab);
			}
			Ext.getCmp('statisticPanel').activate(0);
		}

	
		Ext.get("south").dom.innerHTML = "<a href='http://www.shopware-ag.de/dev/wiki/Hilfe:Marketing#Statistiken_.2F_Diagramme' target='_blank'>Weitere Infos zu den Auswertungen erhalten Sie in unserem Wiki</a>";
		Ext.get("south").highlight();
	},
	init : function(){
		
		// Iframe component
	    Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
	     onRender : function(ct, position){
	          this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
	     }
		}); 
		
		// Init Quicktips
		//Ext.QuickTips.init();
	    //Ext.QuickTips.getQuickTip().interceptTitles = true;
	    //Ext.QuickTips.enable();
    	
	   
	    /*
	    Tree menu to navigate through the statistics
	    */
    	var Tree = Ext.tree;
    	var tree = new Tree.TreePanel({
    		id: 'reports',
    		region:'west',
    		split:true,
    		fitToFrame: true,
    		animate:false,
    		collapsible: true,
    		title:'Verfügbare Reporte',
    		width: 250,
    		height:'100%',
    		margins:'0 0 0 5',
    		padding:'10 10 10 10',
    		minSize: 175,
    		loader: new Tree.TreeLoader({dataUrl:'getStatistics.php'}),
    		enableDD:false,
    		enableEdit:false,
    		autoScroll: true,
    		rootVisible: false
    	});
    	var root = new Tree.AsyncTreeNode({
    		text: 'Auswertung',
    		draggable:true,
    		id:'1',
    		direct:''
    	});

		/*
		Form-Panel (north) to filter data
		*/

		var statesForm = [[-1,'nach Tagen'],[1,'nach Kalenderwochen']];
    	// trigger the data store load
	    var statestoreForm = new Ext.data.SimpleStore({
	 	    fields: ['id', 'state'],
		    data : statesForm
		});
		
		var statesTax = [[-1,'Brutto'],[1,'Netto']];
    	// trigger the data store load
	    var statetaxForm = new Ext.data.SimpleStore({
	 	    fields: ['id', 'state'],
		    data : statesTax
		});
		
		var dr = new Ext.FormPanel({
			height: 75,
			frame: true,
			title: '<?php echo $sLang["orderlist"]["orders_filter"] ?>',
			bodyStyle:'padding:5px 5px 0',
			region: 'north',
			split:true,
			collapsible: true,
			layout: 'table',
			defaults: {
				width: 220,
				layout: 'form',
				labelWidth: 45,
				defaultType: 'datefield',
				bodyStyle:'padding:5px 5px 0;',
				defaults: {
					width: 125
				}
			},
			items: [{
				items: [{
					fieldLabel: '<?php echo $sLang["orderlist"]["orders_from"] ?>',
			        name: 'startdt',
			        id: 'startdt',
			        format: 'd.m.Y',
			        value: '<?php echo date("d.m.Y",mktime(0,0,0,date("m"),date("d")-7,date("Y"))) ?>',
			        endDateField: 'enddt' // id of the end date field
				}]
			},{
				items: 
				[{
					fieldLabel: '<?php echo $sLang["orderlist"]["orders_until"] ?>',
			        name: 'enddt',
			        id: 'enddt',
			        format: 'd.m.Y',
			        value: '<?php echo date("d.m.Y") ?>',
			        startDateField: 'startdt' // id of the start date field
				}]
			},
			{
				items:
				[
					new Ext.form.ComboBox({
			      		fieldLabel: 'Ausgabe',
					    store: statetaxForm,
					    displayField:'state',
					    valueField:'id',
					    typeAhead: true,
					    mode: 'local',
					    id: 'filtertax',
					    triggerAction: 'all',
					    emptyText:'Bitte wählen',
					    selectOnFocus:true,
					    editable: false,
					    value:-1
					})
				]
			}
			,
			{
				items: [new Ext.Button  ( {
			    	text: 'Aktualisieren',
			    	width: 75,
			        handler: myExt.filterGrid
				})]
			}
			
			]
		});
	
	    /*
	    South-Panel with shows further information about the charts
	    */
		var info = new Ext.Panel({
			region: 'south',
			contentEl: 'south',
	        split:true,
	        height: 100,
	        minSize: 100,
	        maxSize: 200,
	        collapsible: true,
	        title:'Statistik Hilfe',
	        margins:'0 0 0 0',
	        autoScroll: true
	
		});
	
		/*
	  	Adds unique parameter to a http request
	  	*/
		
		var summary = new Ext.ux.grid.GridSummary();
		
		/*
		Our main-panel (grid)
		*/
		myExt.store = new Ext.data.Store({
			url: '',
			reader: new Ext.data.JsonReader(
				{root: 'rows', totalProperty:'totalCount',id:0}
			)
        });
          
		grid = new Ext.ux.AutoGridPanel({
	      	region:'center',
	      	id:'grid',
	      	deferRowRender: false,
	        title:'Tabelle',
	        defaultSortable: true,
	        store : myExt.store,
	        //trackMouseOver:true,
	        loadMask: true,
	        plugins: [summary],
	        stripeRows: true,
	        viewConfig: {
	            stripeRows: true
	        },
	        tbar: new Ext.PagingToolbar({
	            pageSize: 100,
	            id: 'myToolbar',
	            store: myExt.store,
	            displayInfo: true,
	            displayMsg: 'Datensätze {0} - {1} von {2}',
	            emptyMsg: "Keine Daten vorhanden",
	            items: [
	            '-',
	            new Ext.Button  ( {
	            	text: 'CSV-Export',
	                handler: exportCSV
            	})
	            ]
        	})
	    });
	 
	   	
	    function exportCSV()
	    {
	    	
			var e = tree.getSelectionModel().getSelectedNode();
			
			if (!e){
				Ext.Msg.alert('Hinweis!','Wählen Sie zuerst eine Auswertung bevor Sie die Filterfunktion einsetzen!');
				return false;
			}
			
			var startDate = Ext.getCmp("startdt");
		    startDate = startDate.getValue();
		    startDate = startDate.dateFormat("d.m.Y");
		    
		    var endDate = Ext.getCmp("enddt");
		    endDate = endDate.getValue();
		    endDate = endDate.dateFormat("d.m.Y");
		    

		    var state = 0;
		    var tax = Ext.getCmp("filtertax").getValue();
		    if (e.attributes.file){
		    	load = e.attributes.file;
		    }else {
		    	load = e.attributes.id;
		    }
		    var url = "csv.php?chart="+load+"&date="+startDate+"&date2="+endDate+"&id="+e.attributes.id+"&group="+state+"table=1&csv=1&tax="+tax;
	   		window.open(url,"Fenstername","width=300,height=300");
	   	}
	   	
	  	tree.on('click', function(e){
	  		options.keywords = 0;
			options.node = '';
	  		myExt.filterGrid(e);
    	});  
    	
    	/*
    	Main center panel (grid/chart)
    	*/
	    var statisticPanel = new Ext.TabPanel(
	    {
	    	id: 'statisticPanel',
	    	region:'center',
	    	activeTab:0,
	    	tabPosition:'bottom',
	    	items: [
	    		grid
	    	]
	    }
	    );
	    
	    /*
	    Center Viewport (North / South)
	   	*/
	    var center =   new Ext.Panel({
			layout: 'border',
			collapsible: true,
			region:'center',
			items: [
				statisticPanel,
				info
			]
		});
	
		tree.setRootNode(root);
		root.expand();

		var viewport = new Ext.Viewport({
		    layout:'border',
		    items:[
	    		dr,
	    		tree,
				center
		     ]
		});
} } }();

    Ext.onReady(function(){
    	myExt.init();
    });
</script>
</head>
<div id="south" class="south" style="padding:10 10 10 10;background-color:#FFF;font-size:11px;font-family:arial"></div>
<body>
</body>
</html>