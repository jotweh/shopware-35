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
        font:normal 11px arial;
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
    </style>
	<script>
		
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
     GetRandom : function ( min, max ) {
          if( min > max ) {	   
                  return( -1 );
          }
          if( min == max ) {
                  return( min );
          }
          var r = parseInt( Math.random() * ( max+1 ) );
          return( r + min <= max ? r + min : r );
      },
    filterByChar: function(key){
    	store.baseParams["search"] = key;
	    store.lastOptions.params["start"] = 0;
	    store.reload();
    },
    filterGroup: function(key){
    	store.baseParams["group"] = key;
	    store.lastOptions.params["start"] = 0;
	    store.reload();
    },
    deleteArticles: function(){
    	Ext.MessageBox.confirm('<?php echo $sLang["user"]["user_confirmation"] ?>', '<?php echo $sLang["user"]["user_delete_agree"] ?>', function deleteClientConfirmed(btn){
	    	
    		if (btn=="yes"){
    			
	    		var deleted = false;
		    	$$('.markedArticles').each(function(e){
		    		if (e.checked){
		    			var articleID = e.getProperty('value');
		    			var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteArticle.php",{method: 'post', onComplete: function(json){
						}}).request('delete='+articleID);
		    			
		    			// DELETE ARTICLES
		    			deleted = true; 
		    		}
		    	});
		    	if (deleted){
		    		// Reload Grid
		    		myExt.reload();
		    	}
	    	}
    	
    	});
    },
     openClient : function(id,name){
			//console.log(target);
		   // Define iFrames
		    var stammdaten = new Ext.ux.IFrameComponent({ 
				title:'Stammdaten',
				id: "idStammdaten"+id+myExt.GetRandom(1,10000), 
				url: '../userdetails/main.php?ext=1&id='+id
			});
		    var bestellungen = new Ext.ux.IFrameComponent({ 
				title:'Bestellungen',
				id: "idStammdaten"+id+myExt.GetRandom(1,10000), 
				url: '../userdetails/orders.php?ext=1&id='+id
			});
		    var umsatz = new Ext.ux.IFrameComponent({ 
				title:'Umsatz',
				id: "idStammdaten"+id+myExt.GetRandom(1,10000), 
				url: '../userdetails/statistics.php?ext=1&id='+id
			});
			
			
			
		    var customer = new Ext.TabPanel({
		                    deferredRender:true,
		                    width:700,
		                    enableTabScroll:true,
		                    forceFit:true,
		        			height:500,
		        			id:'CT'+myExt.GetRandom(1,10000),
		        			title:unescape(name),
		                    activeTab:0,
		                    closable:true,
		                    items:[
			                    stammdaten,bestellungen,umsatz
		                    ]
		    });
		   
		    myTab.add(
	    		customer
		    ).show();
			  
		    
	    },
	init : function(){
       Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

       store = new Ext.data.Store({
	        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getUser.php',
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'user',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'id','customernumber','regdate','company','firstname','lastname','zipcode','city', 'amount','countOrders','customergroup'
	         
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
    store.setDefaultSort('lastpost', 'desc');
    
    var cm = new Ext.grid.ColumnModel([

   		{
           id: 'customernumber', 
           header: "<?php echo $sLang["user"]["user_customer_number"] ?>",
           dataIndex: 'customernumber',
           width: 60,
           sortable: true
        },
        {
           id: 'regdate', 
           header: "<?php echo $sLang["user"]["user_data"] ?>",
           dataIndex: 'regdate',
           width: 120,
           sortable: true
        },
        {
           id: 'customergroup', 
           header: "Kundengruppe",
           dataIndex: 'customergroup',
           width: 120,
           sortable: true
        },
    	{
           id: 'company', 
           header: "<?php echo $sLang["user"]["user_company"] ?>",
           dataIndex: 'company',
           width: 120,
           sortable: true
        },
    	{
           id: 'firstname', 
           header: "<?php echo $sLang["user"]["user_firstname"] ?>",
           dataIndex: 'firstname',
           width: 80,
           sortable: true
        },
        {
           id: 'lastname', 
           header: "<?php echo $sLang["user"]["user_lastname"] ?>",
           dataIndex: 'lastname',
           width: 80,
           sortable: true
        },
        {
           header: "<?php echo $sLang["user"]["user_zip"] ?>",
           dataIndex: 'zipcode',
           width: 50,
           sortable: true
        },
        {
           header: "<?php echo $sLang["user"]["user_city"] ?>",
           dataIndex: 'city',
           width: 50,
           sortable: true
        },
        {
           header: "<?php echo $sLang["user"]["user_ordercount"] ?>",
           dataIndex: 'countOrders',
           width: 50,
           sortable: true
        },
        {
           header: "<?php echo $sLang["user"]["user_total"] ?>",
           dataIndex: 'amount',
           width: 25,
           sortable: true
        },
        {
           header: "<?php echo $sLang["user"]["user_options"] ?>",
           dataIndex: 'options',
           width: 150,
           renderer: renderOptions
        }
        ]);
    cm.defaultSortable = true;
 
    function renderOptions(value, p, r){
		var id = r.data.id;
		var name = r.data.lastname;
		
		return String.format(
		'<a class="ico pencil" style="cursor:pointer" onclick="myExt.openClient({0},{1})"></a><a class="ico pencil_arrow" style="cursor:pointer" onclick="parent.loadSkeleton({2},false,{3})"></a>',id,"'"+escape(name)+"'","'userdetails'","{'user':"+id+"}"
		);
    }
    
	Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
	 onRender : function(ct, position){
	      this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
	 }
	}); 
	
    var limitArray = [['25'],['50'],['100'],['250'],['500']];
	var limitStore = new Ext.data.SimpleStore({
        fields: ['limitArray'],
        data : limitArray
    });
    
    var pager = new Ext.PagingToolbar({
        pageSize: 25,
        store: store,
        displayInfo: true,
        displayMsg: '<?php echo $sLang["user"]["user_customer"] ?> {0} - {1} <?php echo $sLang["user"]["user_customer_total"] ?> {2}',
        emptyMsg: "<?php echo$sLang["user"]["user_no_customer_view"] ?>",
        items:[
            '<?php echo $sLang["user"]["user_search"] ?> ',
        {
        	xtype: 'textfield',
        	id: 'search',
        	title:'<?php echo $sLang["user"]["user_test"] ?>',
        	selectOnFocus: true,
        	width: 120,
        	listeners: {
            	'render': {fn:function(ob){
            		ob.el.on('keyup', searchFilter, this, {buffer:500});
            	}, scope:this}
        	}
        },
        '-',
        'Anzahl Kunden',
        { 
        	xtype: 'combo',
        	id: 'status',
        	fieldLabel: 'Last Name',
        	typeAhead: false,
        	title:'Anzahl Kunden',
        	forceSelection: false,
        	triggerAction: 'all',
        	store: limitStore,
        	displayField: 'limitArray',
        	lazyRender: false,
        	lazyInit: false,
        	mode:'local',
        	width: 120,
        	selectOnFocus:true,
        	listClass: 'x-combo-list-small',
        	listeners: {
            	'change' : {fn: limitFilter, scope:this}
        	}
    	}]
    });
    
    var grid = new Ext.grid.GridPanel({
      	region:'center',
        width:700,
        height:500,
        title:'Kundenübersicht',
        store: store,
        cm: cm,
        trackMouseOver:true,
        sm: new Ext.grid.RowSelectionModel({selectRow:Ext.emptyFn}),
        loadMask: true,
        stripeRows: true,
        viewConfig: {
            forceFit:true,
            stripeRows: true,
            enableRowBody:true,
            showPreview:true,
            getRowClass : function(record, rowIndex, p, store){
               
            }
        },
     bbar: pager

    });

  	function limitFilter () {
	    var status = Ext.getCmp("status");
	    grid.store.baseParams["limit"] = status.getValue();
	    pager.pageSize = parseInt(status.getValue());
	    grid.store.lastOptions.params["start"] = 0;
	    grid.store.reload();
	}
	
	function searchFilter () {
	    var search = Ext.getCmp("search");
	    store.baseParams["search"] = search.getValue();
	    store.lastOptions.params["start"] = 0;
	    store.reload();
	}	
    // trigger the data store load
    store.load({params:{start:0, limit:25}});
    
	   var tab = new Ext.Panel({
					layout: 'fit',
					title: 'Filter',
					split:true,
					tools: [{id:'refresh'}],
					minSize: 100,
					width: 200,
					collapsible: true,
					region: 'west',
					margins:'0 0 0 0',
	
					contentEl:  filter
					
				});
	   
		    
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
	            tab,myTab
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


<div id="north">

</div>
<script>
function x(){
	
}
</script>
<div id="filter" style="padding: 5 5 5 5">
<?php
for ($i=65;$i<=90;$i++){
	echo "<a href='javascript:x()' class='letter' id=\"ID".chr($i)."\" onclick=\"myExt.filterByChar('".chr($i)."');\">".chr($i)."</a>";
}
?><br />
<div style="clear:both"></div>
<p><?php echo $sLang["user"]["user_filters"] ?></p>
<select name="filterByCustomergroup" id="filterByCustomergroup" onchange="myExt.filterGroup(this.value)">
<option value=""><?php echo $sLang["user"]["user_show_all"] ?></option>
<?php
$queryCustomerGroups = mysql_query("SELECT groupkey,description FROM s_core_customergroups ORDER BY id ASC
");
while ($customergroup=mysql_fetch_assoc($queryCustomerGroups)){
?>
	<option value="<?php echo $customergroup["groupkey"] ?>"><?php echo $customergroup["description"] ?></option>
<?php
}
?>

</select>


</div>
</body>
</html>


