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
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />

<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
</head>

<body>


<script type="text/javascript">
var myExt = function(){
	
	//Column Model Obj
	var cm;
	//Grid-Store
	var gridStore;
	//PagingBar
	var pager;
	//Grid Obj
	var myGrid;
	
	return {
		
		//=============================================================================================
		// Create Column Model
		//=============================================================================================
				
		initColumnModel: function(){
			cm = new Ext.grid.ColumnModel([
			{
	    		header: "Überprüft",
	    		width: 40,
	    		sortable: false,
	    		locked:true,
	    		renderer: renderCheckbox
	    	},	
	   		{
	           id: 'area', 
	           header: "Bereich",
	           dataIndex: 'area',
	           width: 100,
	    	   sortable: true
	        },	
	   		{
	           id: 'subarea', 
	           header: "Unterpunkt",
	           dataIndex: 'subarea',
	           width: 200,
	    	   sortable: true
	        },
	   		{
	           id: 'option', 
	           header: "Option",
	           dataIndex: 'option',
	           width: 150,
	    	   sortable: true
	        },	
	   		{
	           id: 'edit', 
	           header: "Bearbeiten",
	           dataIndex: 'edit',
	           width: 40,
	    	   sortable: false,
           	   renderer: renderOptions
	        }]);	        
	        
	        // Option Renderer -----------------------------------------------------------------------
			
			function renderOptions(value, p, r){
				
				var skeleton = r.data.skeleton;
				if(skeleton != "" && skeleton != null)   
				{
					return '<a class="ico pencil" style="cursor:pointer" onclick="parent.parent.parent.loadSkeleton(\''+skeleton+'\');"></a>';
				}else{
					return '<a class="ico pencil" style="cursor:pointer" onclick="parent.parent.parent.loadSkeleton(\'checklistopt\', false, {\'id\': \''+r.data.id+'\'});"></a>';
				}
							
    			
		    }
		    	        
	        // Checkbox Renderer ---------------------------------------------------------------------
		    
	        function renderCheckbox(v,p,r,rowIndex,i,ds){
	    			
	    			//  << Load values / checkstatus >>
	    			if (r.data.checked == 1){
	    				var checked = 'checked="checked"';
	    			}else  {
	    				var checked = '';
	    			}
	    			
	    			var chkBoxId = Ext.id();
	    			
	    			return '<input type="checkbox"'+checked+' id="'+chkBoxId+'" value="'+rowIndex+'" style="float:left;margin-right:3px" onclick="myExt.eCheckbox(this);" />';
	    		}
		},
		
		//=============================================================================================
		// Create Grid-Store
		//=============================================================================================
		
		initGridStore: function(){
			gridStore = new Ext.data.Store({
				id: 'gridStore',
		        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getChecklist.php',
		        reader: new Ext.data.JsonReader({
		            root: 'data',
		            totalProperty: 'total',
		            id: 'id',
		            fields: [
		                'id', 'type', 'checked', 'area', 'subarea', 'option', 'skeleton'
		            ]
	        	})
			});
			gridStore.load({params:{start:0, limit:25}});
		},
		
		//=============================================================================================
		// Create Paging
		//=============================================================================================
		
		initPaging: function(){
			pager = new Ext.PagingToolbar({
	            pageSize: 25,
	            store: gridStore,
	            displayInfo: true,
	            displayMsg: 'Anpassungen {0} - {1} von {2}',
	            items:[
	            new Ext.Button  ( {
	            	text: 'Überprüfte Aufgaben anzeigen',
	                handler: function(btn){
	                	if(btn.pressed)
	                	{
	                		gridStore.setBaseParam('displayChecked', 1);
	                		myExt.reloadStore();
	                	}else{
	                		gridStore.setBaseParam('displayChecked', 0);
	                		myExt.reloadStore(true);
	                	}
	                },
	                enableToggle: true
	            })
	            ]
	        });
		},
		
		//=============================================================================================
		// Create Grid
		//=============================================================================================
		
		initGrid: function(){
			myGrid = new Ext.grid.EditorGridPanel({
				region:'center',
				id: 'orderlist_grid',
				width:700,
				height:500,
				title:'Checkliste',
				store: gridStore,
				cm: cm,
				autoSizeColumns: true,
				trackMouseOver:true,
				sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
				loadMask: true,
				stripeRows: true,
				viewConfig: {
					forceFit:true,
					stripeRows: true,
					getRowClass : function(record, rowIndex, p, store){
						//return 'red-row';
					}
				},
				bbar: pager
			});
		},
		
		//=============================================================================================
		// Render Viewport
		//=============================================================================================
		
		renderViewport: function(){
			new Ext.Viewport({
				layout: 'border',
				items: [myGrid]
			});
		},
		
		//=============================================================================================
		// Functions
		//=============================================================================================
		
		// Grid Store reload (reset start) ------------------------------------------------------------
		
		reloadStore: function(reset_start){	
			if(reset_start == true)
			{
				gridStore.reload({params:{start:0, limit:25}});						
			}else{
				gridStore.reload();	
			}
				
		},
		
		// Set the checked flag -----------------------------------------------------------------------
		
		setCheckedFlag: function(checklistItemId, checked){	
			Ext.Ajax.request({
			   url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/setChecklistCheckedFlag.php',
			   success: function(){
			   },
			   params: { checklistItemId: checklistItemId, checked: checked }
			});
		},
		
		//=============================================================================================
		// Events
		//=============================================================================================
		
		// Checkbox Event // chkBox = input-Element [type=checkbox] -----------------------------------
				
		eCheckbox: function(chkBox){
			
			//get rowIndex (value of chkbox)
			var rowIndex = chkBox.value;
			var record = gridStore.getAt(rowIndex);
			
			//modus
			if(chkBox.checked == true)
			{
				myExt.setCheckedFlag(record.data.id, 1);
			}else{
				myExt.setCheckedFlag(record.data.id, 0);
			}
		}
	}
}();

var myEvent = function(){
	
	return {
		
		//=============================================================================================
		// Checkbox Event
		//
		// chkBox = input-Element [type=checkbox]
		//=============================================================================================
				
		eCheckbox: function(chkBox){
			
			//get rowIndex (value of chkbox)
			var rowIndex = chkBox.value;
		}
	}
}();

Ext.onReady(function(){
	myExt.initColumnModel();
	myExt.initGridStore();
	myExt.initPaging();
	myExt.initGrid();
	myExt.renderViewport();
});
</script>

</body>

</html>