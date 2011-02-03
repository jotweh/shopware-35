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
  <title>articles.overview</title>
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
    </style>
	<script>
		function sWrapper(sFunction, sId){
			switch (sFunction){
				case "deleteArticle":
					var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteArticle.php",{method: 'post', onComplete: function(json){
						parent.Growl('Artikel wurde gelöscht');
						myExt.reload();
					}}).request('delete='+sId);
					break;
				case "duplicateArticle":
					
					var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/copyArticle.php",{method: 'post', onComplete: function(json){
						parent.Growl('Artikel wurde dupliziert');
						myExt.reload();
					}}).request('duplicate='+sId);
					break;
			}
		}
		
		
		function deleteArticle(ev,text){
				parent.sConfirmationObj.show('Soll der Artikel "'+text+'" wirklich gel&ouml;scht werden?',window,'deleteArticle',ev);
		}
		
		function duplicateArticle(ev,text){
				parent.sConfirmationObj.show('Soll der Artikel "'+text+'" wirklich dupliziert werden?',window,'duplicateArticle',ev);
		}
	</script>
	<script type="text/javascript">
	var myExt = function(){
		var store;
		var storeid;
	return {
	reload : function(){
    	store.load({params:{start:0,id:storeid}});
    },
    markAll: function(){
    	$$('.markedArticles').each(function(e){
    		e.checked = true;
    	});
    },
    deleteArticles: function(){
    	Ext.MessageBox.confirm('Bestätigung', 'Sollen die markierten Artikel wirklich gelöscht werden?', function deleteClientConfirmed(btn){
	    	
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
	init : function(){
       Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

       store = new Ext.data.Store({
	        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getArticles.php',
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'articles',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'articleName','articleID','ordernumber','supplier','price','active'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
    store.setDefaultSort('lastpost', 'desc');
    
    var cm = new Ext.grid.ColumnModel([
    	{
    		header: "",
    		width: 30,
    		sortable: false,
    		locked:true,
    		renderer: function (v,p,r,rowIndex,i,ds){
    			return '<input type="checkbox" class="markedArticles" name="markedArticles" value="'+r.data.articleID+'"/>';
    		}
    	},
   		{
           id: 'ordernumber', 
           header: "Bestellnummer",
           dataIndex: 'ordernumber',
           width: 60
        },
        {
           id: 'supplier', 
           header: "Hersteller",
           dataIndex: 'supplier',
           width: 120
        },
    	{
           id: 'name', 
           header: "Artikel",
           dataIndex: 'articleName',
           width: 120
        },
    	{
           id: 'price', 
           header: "Preis",
           dataIndex: 'price',
           width: 30,
           align: 'right'
        },
        {
           id: 'active', 
           header: "Aktiv",
           dataIndex: 'active',
           width: 20,
           renderer: renderActive
        },
        {
           header: "id",
           dataIndex: 'articleID',
           width: 100,
           hidden:true
        },
        {
           header: "Optionen",
           dataIndex: 'options',
           width: 150,
           renderer: renderOptions
        }
        ]);
    cm.defaultSortable = true;
    function renderActive(value, p, r){
    	var id = r.data.active;
    	if (id==1){
    		return "<a class='ico accept'></a>";
    	}else {
    		return "<a class='ico exclamation'></a>";
    	}
    	
    }
    function renderOptions(value, p, r){
    	
    	//console.log(r.data);
		var id = r.data.articleID;
		var name = r.data.articleName;
		
		return String.format(
		'<a class="ico pencil" style="cursor:pointer" onclick="parent.loadSkeleton({2},false, {3})"></a><a class="ico delete" style="cursor:pointer" onclick="deleteArticle({0},{1})"></a><a class="ico folders_plus" onclick="duplicateArticle({0},{1})" style="cursor:pointer" onclick="duplicateArticle({0},{1})"></a>',id,"'"+name+"'","'articles'","{'article':"+id+"}"
		);
		
		/*<a onclick="duplicateArticle(<?php echo$article["articleID"]?>,'<?php echo$article["articleName"]?>')" style="cursor: pointer;" class="ico disk_multiple"></a>
		<a onclick="deleteArticle(<?php echo$article["articleID"]?>,'<?php echo$article["articleName"]?>')" style="cursor: pointer;" class="ico delete"></a>
		*/

    	
    }
    var limitArray = [['25'],['50'],['100'],['250'],['500']];
	var limitStore = new Ext.data.SimpleStore({
        fields: ['limitArray'],
        data : limitArray
    });
    
    var pager = new Ext.PagingToolbar({
            pageSize: 25,
            store: store,
            displayInfo: true,
            displayMsg: 'Zeige Artikel {0} - {1} von {2}',
            emptyMsg: "Keine Artikel gefunden",
            items: [
            	'-',
	            'Anzahl Artikel',
	            { 
	            	xtype: 'combo',
	            	id: 'status',
	            	fieldLabel: 'Last Name',
	            	typeAhead: false,
	            	title:'Anzahl Artikel',
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
            	},
            	'-',
	            new Ext.Button  ({
	            	text: 'Alle Artikel markieren',
	           		handler: myExt.markAll
	            }),
	            '-',
            	new Ext.Button  ( {
	            	text: 'Markierte Artikel löschen',
	            	handler: myExt.deleteArticles
	            })
            ]
        });
        
    var grid = new Ext.grid.GridPanel({
      	region:'center',
        width:700,
        height:500,
        title:'Artikel - Übersicht',
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
  

    // trigger the data store load
    store.load({params:{start:0, limit:25}});
    
    var Tree = Ext.tree;
       
       
	var tree = new Tree.TreePanel({
                	region:'west',
                	split:true,
			        animate:false, 
			        collapsible: true,
			        title:'Kategorien',
			        width: 200,
			        margins:'0 0 0 0',
			        minSize: 175,
			        autoScroll: true,
			        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getCategories.php'}),
			        enableDD:true,
			        enableEdit: true
	});
	var root = new Tree.AsyncTreeNode({
		        text: 'Shopware',
		        draggable:false,
		        id:'1'
	});
	tree.on('click', function(e){
		var id = e.attributes.id;		
		storeid = id;
		store.baseParams.pagingID = id;		
		store.load({params:{start:0,id:id, limit:25}});
		
    });  
    		
	   tree.setRootNode(root);
	   root.expand();
	    
       var viewport = new Ext.Viewport({
            layout:'border',
            items:[
               
                 grid
                 ,
                tree
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

