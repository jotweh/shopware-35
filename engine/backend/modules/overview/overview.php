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
  <title><?php echo $sLang["user"]["user_user_list"] ?></title>
 	<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
	<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>

	
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
 	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
 	<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
 	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
 	
	<style type="text/css">
	html, body {
        font:normal 12px verdana;
        margin:0;
        padding:0;
        border:0 none;
        overflow:hidden;
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
    
    .blue-row .x-grid3-cell-inner{
      color:blue;
    }
    .red-row .x-grid3-cell-inner{
      color:red;
    }
    .green-row .x-grid3-cell-inner{
      color:green;
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

    </style>
	<script>
	function loadSkeleton(x,y, z){
		parent.loadSkeleton(x,y,z);
	}
	</script>
	<script type="text/javascript">
	var myExt = function(){
		var store;
		var storeid;
		var myTab;
	return {
	reload : function(){
    	store.load({params:{start:0,id:storeid, limit:25}});
    },
	init : function(){
		 Ext.ns("Ext.ux.grid.GridSummary");

	Ext.ux.grid.GridSummary = function(config) {
			Ext.apply(this, config);
	};
	
	Ext.extend(Ext.ux.grid.GridSummary, Ext.util.Observable, {
		init : function(grid) {
			this.grid = grid;
			this.cm = grid.getColumnModel();
			this.view = grid.getView();
			
			var v = this.view;
	
			v.onLayout = this.onLayout; // override GridView's onLayout() method
	
			v.afterMethod('render', this.refreshSummary, this);
			v.afterMethod('refresh', this.refreshSummary, this);
			v.afterMethod('syncScroll', this.syncSummaryScroll, this);
			v.afterMethod('onColumnWidthUpdated', this.doWidth, this);
			v.afterMethod('onAllColumnWidthsUpdated', this.doAllWidths, this);
			v.afterMethod('onColumnHiddenUpdated', this.doHidden, this);
			v.afterMethod('onUpdate', this.refreshSummary, this);
			v.afterMethod('onRemove', this.refreshSummary, this);
	
			// update summary row on store's add / remove / clear events
			grid.store.on('add', this.refreshSummary, this);
			grid.store.on('remove', this.refreshSummary, this);
			grid.store.on('clear', this.refreshSummary, this);
	
			if (!this.rowTpl) {
				this.rowTpl = new Ext.Template(
					'<div class="x-grid3-summary-row x-grid3-gridsummary-row-offset">',
						'<table class="x-grid3-summary-table" cellspacing="0" cellpadding="0" style="{tstyle} background: #f0f0f0; border-top:1px solid #dfdfdf; border-right: 1px solid #dfdfdf;">',
							'<tbody><tr>{cells}</tr></tbody>',
						'</table>',
					'</div>'
				);
				this.rowTpl.disableFormats = true;
			}
			this.rowTpl.compile();
	
			if (!this.cellTpl) {
				this.cellTpl = new Ext.Template(
					'<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
						'<div class="x-grid3-cell-inner x-grid3-col-{id}" unselectable="on" {attr}>{value}</div>',
					"</td>"
				);
				this.cellTpl.disableFormats = true;
			}
			this.cellTpl.compile();
		},
	
		calculate : function(rs, cm) {
			var data = {}, cfg = cm.config;
			for (var i = 0, len = cfg.length; i < len; i++) { // loop through all columns in ColumnModel
				var cf = cfg[i], // get column's configuration
						cname = cf.dataIndex; // get column dataIndex
				
				// initialise grid summary row data for 
				// the current column being worked on
				data[cname] = 0;
				
				if (cf.summaryType) {
					for (var j = 0, jlen = rs.length; j < jlen; j++) {
						var r = rs[j]; // get a single Record
						data[cname] = Ext.ux.grid.GridSummary.Calculations[cf.summaryType](r.get(cname), r, cname, data, j);
					}
				}
			}
	
			return data;
		},
	
		onLayout : function(vw, vh) {
			if (Ext.type(vh) != 'number') { // handles grid's height:'auto' config
				return;
			}
			// note: this method is scoped to the GridView
			if (!this.grid.getGridEl().hasClass('x-grid-hide-gridsummary')) {
				// readjust gridview's height only if grid summary row is visible
				this.scroller.setHeight(vh - this.summary.getHeight());
			}
		},
	
		syncSummaryScroll : function() {
			var mb = this.view.scroller.dom;
			this.view.summaryWrap.dom.scrollLeft = mb.scrollLeft;
			this.view.summaryWrap.dom.scrollLeft = mb.scrollLeft; // second time for IE (1/2 time first fails, other browsers ignore)
		},
	
		doWidth : function(col, w, tw) {
			var s = this.view.summary.dom;
			s.firstChild.style.width = tw;
			s.firstChild.rows[0].childNodes[col].style.width = w;
		},
	
		doAllWidths : function(ws, tw) {
			var s = this.view.summary.dom, wlen = ws.length;
			s.firstChild.style.width = tw;
			var cells = s.firstChild.rows[0].childNodes;
			for (var j = 0; j < wlen; j++) {
				cells[j].style.width = ws[j];
			}
		},
	
		doHidden : function(col, hidden, tw) {
			var s = this.view.summary.dom,
					display = hidden ? 'none' : '';
			s.firstChild.style.width = tw;
			s.firstChild.rows[0].childNodes[col].style.display = display;
		},
		roundVal: function (val){
			var dec = 2;
			var result = Math.round(val*Math.pow(10,dec))/Math.pow(10,dec);
			return result;
		},
		renderSummary : function(o, cs, cm) {
			cs = cs || this.view.getColumnData();
			var cfg = cm.config,
					buf = [],
					last = cs.length - 1;
	
			for (var i = 0, len = cs.length; i < len; i++) {
				var c = cs[i], cf = cfg[i], p = {};
				p.id = c.id;
				p.style = c.style;
				p.css = i == 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
				if (cf.summaryType || cf.summaryRenderer) {
					p.value = (cf.summaryRenderer || c.renderer)(o.data[c.name], p, o);
					p.value = this.roundVal(p.value);
				} else {
					p.value = '';
				}
				if (p.value == undefined || p.value === "") p.value = "&#160;";
				buf[buf.length] = this.cellTpl.apply(p);
			}
	
			return this.rowTpl.apply({
				tstyle: 'width:' + this.view.getTotalWidth() + ';',
				cells: buf.join('')
			});
		},
	
		refreshSummary : function() {
			var g = this.grid, ds = g.store,
					cs = this.view.getColumnData(),
					cm = this.cm,
					rs = ds.getRange(),
					data = this.calculate(rs, cm),
					buf = this.renderSummary({data: data}, cs, cm);
			
			if (!this.view.summaryWrap) {
				this.view.summaryWrap = Ext.DomHelper.insertAfter(this.view.scroller, {
					tag: 'div',
					cls: 'x-grid3-gridsummary-row-inner'
				}, true);
			} else {
				this.view.summary.remove();
			}
			this.view.summary = this.view.summaryWrap.insertHtml('afterbegin', buf, true);
		},
	
		toggleSummary : function(visible) { // true to display summary row
			var el = this.grid.getGridEl();
			if (el) {
				if (visible === undefined) {
					visible = el.hasClass('x-grid-hide-gridsummary');
				}
				el[visible ? 'removeClass' : 'addClass']('x-grid-hide-gridsummary');
	
				this.view.layout(); // readjust gridview height
			}
		},
	
		getSummaryNode : function() {
			return this.view.summary
		}
		});

		/*
		 * all Calculation methods are called on each Record in the Store
		 * with the following 5 parameters:
		 *
		 * v - cell value
		 * record - reference to the current Record
		 * colName - column name (i.e. the ColumnModel's dataIndex)
		 * data - the cumulative data for the current column + summaryType up to the current Record
		 * rowIdx - current row index
		 */
		Ext.ux.grid.GridSummary.Calculations = {
			sum : function(v, record, colName, data, rowIdx) {
				v = v.replace(/,/,".");
				//console.log();
				return data[colName] + parseFloat(v);
			},
		
			count : function(v, record, colName, data, rowIdx) {
				return rowIdx + 1;
			},
		
			max : function(v, record, colName, data, rowIdx) {
				return Math.max(Ext.num(v, 0), data[colName]);
			},
		
			min : function(v, record, colName, data, rowIdx) {
				return Math.min(Ext.num(v, 0), data[colName]);
			},
		
			average : function(v, record, colName, data, rowIdx) {
				var t = data[colName] + Ext.num(v, 0), count = record.store.getCount();
				return rowIdx == count - 1 ? (t / count) : t;
			}
		}
       Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

       store = new Ext.data.Store({
	        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getOrderSummary.php',
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'order',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'id','datumFormated','amount','countOrders','averageOrders','averageUsers','countCustomers','visits','hits'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
   

    var cm = new Ext.grid.ColumnModel([

   		{
           id: 'datum', 
           header: "Datum",
           dataIndex: 'datumFormated',
           width: 150
        },
        {
           id: 'regdate', 
           header: "Umsatz",
           align: 'right',
           dataIndex: 'amount',
           width: 70,
           summaryType: 'sum'
        },
        {
           header: "Bestellungen",
           dataIndex: 'countOrders',
           width: 35,
           align: 'right',
           summaryType: 'sum'
        },
    	{
           id: 'company', 
           header: "Ø Bestellwert",
           dataIndex: 'averageOrders',
           align: 'right',
           width: 80
        },
    	{
           id: 'orderstate', 
           header: "Ø User/Order",
           dataIndex: 'averageUsers',
           width: 100
        },
        {
           id: 'lastname', 
           header: "Neukunden",
           dataIndex: 'countCustomers',
           width: 100,
           summaryType: 'sum'
        },
        {
           header: "Besucher",
           dataIndex: 'visits',
           width: 75,
           summaryType: 'sum'
        },
        {
           header: "Seitenzugriffe",
           dataIndex: 'hits',
           width: 75,
           summaryType: 'sum'
        }
        ]);
    cm.defaultSortable = true;

    
    var summary = new Ext.ux.grid.GridSummary(); 
    
    var grid = new Ext.grid.EditorGridPanel({
      	region:'center',
        width:700,
        height:500,
        title:'Auswertung',
        store: store,
        cm: cm,
        autoSizeColumns: true,
        trackMouseOver:true,
        sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
        loadMask: true,
         plugins: [summary],
        stripeRows: true,
        viewConfig: {
            forceFit:true,
            stripeRows: true,
            getRowClass : function(record, rowIndex, p, store){
              //return 'red-row';
            }
        }
    });
    
    store.setDefaultSort('datum', 'desc');
	store.load({params:{start:0, limit:25}});
	
	
	
	
	
    var dr = new Ext.FormPanel({
      labelWidth: 80,
      frame: true,
      title: '<?php echo $sLang["orderlist"]["orders_filter"] ?>',
	  bodyStyle:'padding:5px 5px 0',
	  width: 230,
	  region: 'west',
	  split:true,
	  collapsible: true,
      defaults: {width: 120},
      defaultType: 'datefield',
      items: [{
        fieldLabel: '<?php echo $sLang["orderlist"]["orders_from"] ?>',
        name: 'startdt',
        id: 'startdt',
        format: 'd.m.Y',
        value: '<?php echo date("d.m.Y",mktime(0,0,0,date("m"),date("d")-7,date("Y"))) ?>',
        endDateField: 'enddt' // id of the end date field
      },{
        fieldLabel: '<?php echo $sLang["orderlist"]["orders_until"] ?>',
        name: 'enddt',
        id: 'enddt',
        format: 'd.m.Y',
        value: '<?php echo date("d.m.Y") ?>',
        startDateField: 'startdt' // id of the start date field
      },
		new Ext.Button  ( {
	    	text: '<?php echo $sLang["orderlist"]["orders_filters"] ?>',
	        handler: filterGrid
    	})
      ]
    });
    
    function filterGrid(e,f,p){
		/*
		Filter Grid 
		*/
		var startDate = Ext.getCmp("startdt");
	    startDate = startDate.getValue();
	    startDate = startDate.dateFormat("Y-m-d");
	    
	    var endDate = Ext.getCmp("enddt");
	    endDate = endDate.getValue();
	    endDate = endDate.dateFormat("Y-m-d");
	    
	    // Reload Grid
	    store.baseParams["startDate"] = startDate;
	    store.baseParams["endDate"] = endDate;
	    store.lastOptions.params["start"] = 0;
	    store.reload();
	    
   }
   
   myTab = new Ext.TabPanel({
            region:'center',
            deferredRender:false,
            activeTab:0,
            closeable:true,
            items:[grid]
   });
    	
   var viewport = new Ext.Viewport({
        layout:'border',
        items:[
            dr,myTab
         ]
    });
       
       
         
}};
}();
    Ext.onReady(function(){
    	myExt.init();
    });
	</script>
</head>
<body>
</body>
</html>


