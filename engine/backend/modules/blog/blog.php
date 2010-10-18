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
  <title>articles.fast</title>
 	<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
	<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
	
	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
 	
 	
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
    .x-selectable, .x-selectable * {
		-moz-user-select: text!important;
		-khtml-user-select: text!important;
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
						myExt.reload(true);
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
	<?php
	 $components[] = array("title"=>"Stammdaten","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/artikeln1.inc.php?ext=1&article=");
	    $components[] = array("title"=>"Kategorien","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/artikeln2.inc.php?ext=1&article=");
	    $components[] = array("title"=>"Bilder","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/artikeln3.inc.php?ext=1&article=");
	    $components[] = array("title"=>"Eigenschaften","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/filter.php?ext=1&article=");
	    $components[] = array("title"=>"Varianten","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/varianten.php?ext=1&article=");
	    $components[] = array("title"=>"Konfigurator","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/config.php?ext=1&article=");
	    $components[] = array("title"=>"Links","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/links.php?ext=1&article=");
	    $components[] = array("title"=>"Downloads","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/downloads.php?ext=1&article=");
	    $components[] = array("title"=>"Cross-Selling","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/cross.php?ext=1&article=");
	    $components[] = array("title"=>"ESD","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/esd.php?ext=1&article=");
	    $components[] = array("title"=>"Bundles","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/bundles.php?ext=1&article=");
	    $components[] = array("title"=>"Statistiken","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/statistics.php?ext=1&article=");
	    $components[] = array("title"=>"Live-Shopping","url"=>(empty($_SERVER["HTTPS"]) ? "http" : "https")."://".$sCore->sCONFIG['sBASEPATH']."/engine/backend/modules/articles/liveshopping/main.php?ext=1&article=");

	?>
	var myExt = function(){
		var store;
		var storeid;
		var myTab;
		var window;
		var main; var categories; var images; var properties; var links; var downloads; var cross;
		var blogTabs;
		<?php
		 foreach ($components as $component){
	    	echo "var ".str_replace("-","",strtolower($component["title"])).";";
		 }
		?>
	return {
	reload : function(useLastOptions){
		
		if(useLastOptions == true)
		{
			var startAt = store.lastOptions.params["start"];
			store.load({params:{start:startAt,id:storeid, limit:25}});
		}else{
			store.load({params:{start:0,id:storeid, limit:25}});
		}
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
		    			var myAjax = new Ajax("<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteArticle.php",{method: 'post', onComplete: function(json){
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
    newArticle : function (){
    	var category = store.baseParams.pagingID;
    	myExt.window.show();
    	var url = Ext.get('framepanelMidMain').dom.src.replace(/article\=(.*)/,"article=");
    	url = url.replace(/category\=(.*)\&/,"category="+category+"\&");
    	Ext.get('framepanelMidMain').dom.src = url;
    
    	myExt.blogTabs.activate(0);
    	myExt.categories.disable();
    	myExt.images.disable();
    	myExt.cross.disable();
    	myExt.downloads.disable();
    	myExt.links.disable();
    	myExt.properties.disable();
    	
    },
    unlockTabs : function (id){
    	myExt.categories.enable();
    	myExt.images.enable();
    	myExt.cross.enable();
    	myExt.downloads.enable();
    	myExt.links.enable();
    	myExt.properties.enable();
    	Ext.get('framepanelMidMain').dom.src = Ext.get('framepanelMidMain').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidCategories').dom.src = Ext.get('framepanelMidCategories').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidImages').dom.src = Ext.get('framepanelMidImages').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidCross').dom.src = Ext.get('framepanelMidCross').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidDownloads').dom.src = Ext.get('framepanelMidDownloads').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidLinks').dom.src = Ext.get('framepanelMidLinks').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidProperties').dom.src = Ext.get('framepanelMidProperties').dom.src.replace(/article\=(.*)/,"article="+id);
    	
    },
    openDetails : function(name,id){
    	myExt.window.show();
    	myExt.blogTabs.activate(0);
    	myExt.categories.enable();
    	myExt.images.enable();
    	myExt.cross.enable();
    	myExt.downloads.enable();
    	myExt.links.enable();
    	myExt.properties.enable();
    	Ext.get('framepanelMidMain').dom.src = Ext.get('framepanelMidMain').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidCategories').dom.src = Ext.get('framepanelMidCategories').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidImages').dom.src = Ext.get('framepanelMidImages').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidCross').dom.src = Ext.get('framepanelMidCross').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidDownloads').dom.src = Ext.get('framepanelMidDownloads').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidLinks').dom.src = Ext.get('framepanelMidLinks').dom.src.replace(/article\=(.*)/,"article="+id);
    	Ext.get('framepanelMidProperties').dom.src = Ext.get('framepanelMidProperties').dom.src.replace(/article\=(.*)/,"article="+id);
    
    	
    },
    reloadBlog: function(){
    	store.load({params:{start:0,id:storeid,showDefect:1,limit:25}});
    },
	init : function(){
       Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
       store = new Ext.data.GroupingStore({
	        url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/blog/getArticles.php',
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'articles',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'articleName','articleID','ordernumber','supplier','price','active','image','datum'
	            ]
	        }),
	        // turn on remote sorting
	        remoteSort: true
    	});
    
	    store.setDefaultSort('lastpost', 'desc');

	    var limitArray = [['25'],['50'],['100'],['250'],['500']];
		var limitStore = new Ext.data.SimpleStore({
	        fields: ['limitArray'],
	        data : limitArray
	    });

    	var cm = new Ext.grid.ColumnModel([
		{
    		header: "",
    		width: 30,
    		sortable: false,
    		locked:true,
    		renderer: function (v,p,r,rowIndex,i,ds){
    			return '<input type="checkbox" class="markedArticles" name="markedArticles" style=\"height:10px;padding:0;margin:0\" value="'+r.data.articleID+'"/>';
    		}
    	},
   		{
           id: 'supplier', 
           header: "Hersteller",
           dataIndex: 'supplier',
           sortable: true,
           width: 120
        },
        {
           id: 'datum', 
           header: "Datum",
           dataIndex: 'datum',
           sortable: true,
           width: 120
        },
    	{
           id: 'name', 
           header: "Bezeichnung",
           dataIndex: 'articleName',
           width: 120,
           sortable: true
        },
        {
           header: "id",
           dataIndex: 'articleID',
           width: 0,
           hidden:true
        },
        {
           id: 'active', 
           header: "Aktiv",
           dataIndex: 'active',
           align: 'right',
           sortable: true,
           width: 40,
           renderer: renderActive
        }
        ,
        {
           id: 'options', 
           header: "Optionen",
           dataIndex: 'options',
           width: 100,
           renderer: rendererOptionBar
        }
        ]);
        function rendererOptionBar(value, meta, rec, rowI, colI, store)
		{
			var rec = store.getAt(rowI);
			var name = rec.get('articleName').replace(/\"/,"");
			name = name.replace(/\'/,"");
			var id = rec.get('articleID');
			return String.format(
			'<a class="ico pencil" style="cursor:pointer" onclick="myExt.openDetails(\''+name+'\','+id+')"></a></a><a class="ico delete" style="cursor:pointer" onclick="deleteArticle('+id+',\''+name+'\');"></a><a class="ico folder_plus" style="cursor:pointer" onclick="duplicateArticle('+id+',\''+name+'\')"></a>'
			);
		}
     	function renderActive(value, p, r){
	    	var id = r.data.active;
	    	if (id==1){
	    		return "<a class='ico accept'></a>";
	    	}else {
	    		return "<a class='ico exclamation'></a>";
	    	}
	    }
	    
	    cm.defaultSortable = true;

	    var toolbarButton =new Ext.Toolbar.Button  ( {
	                text: 'Artikel hinzufügen',
	                tooltip: 'Klicken Sie hier um einen Artikel hinzuzufügen',
					iconCls: 'blist2',
					disabled: true,
	                handler: myExt.newArticle
	    });
    
    	var pager =  new Ext.PagingToolbar({
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
	            	 iconCls: 'markAllE',
	           		handler: myExt.markAll
	            }),
	            '-',
            	new Ext.Button  ( {
	            	text: 'Markierte Artikel löschen',
	            	 iconCls: 'delete',
	            	handler: myExt.deleteArticles
	            }),
	            '-',
            	new Ext.Button  ( {
	            	text: 'Alle Blog-Artikel anzeigen',
	            	iconCls: 'delete',
	            	handler: myExt.reloadBlog
	            })
            ]
	    });
	    var grid = new Ext.grid.GridPanel({
	    	id: 'article_grid',
	        title:'Artikel - anlegen / bearbeiten',
	        store: store,
	        clicksToEdit:2,//number of clicks to activate cell editor, default = 2        
	        cm: cm,
	        split:true,
	        region: 'center',
	        trackMouseOver:false,
	        selModel: new Ext.grid.RowSelectionModel({singleSelect:false}),//true to limit row selection to 1 row})
	        loadMask: true,
	        viewConfig: {
	            forceFit:true,
	            enableRowBody:true,
	            stripeRows: true,
	            showPreview:true
	        },
	        bbar: pager,
	        //Add a top bar      
	        tbar: [
	           toolbarButton
	        ]
	    });

	    function limitFilter () {
		    var status = Ext.getCmp("status");
		    grid.store.baseParams["limit"] = status.getValue();
		    pager.pageSize = parseInt(status.getValue());
		    grid.store.lastOptions.params["start"] = 0;
		    grid.store.reload();
		}
	  
	    store.load({params:{start:0, limit:25}});
	    
	    var Tree = Ext.tree;
       
       
		var tree = new Tree.TreePanel({
	                	split:true,
				        animate:false, 
				        collapsible: true,
				        title:'Kategorie auswählen',
				        width: 200,
				        margins:'0 0 0 0',
				        region: 'west',
				        minSize: 175,
				        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getBlogCategories.php'}),
				        enableDD:true,
				        enableEdit: true,
				        containerScroll: true,
				        autoScroll: true
		});
		var root = new Tree.AsyncTreeNode({
			        text: 'Shopware',
			        draggable:false,
			        id:'1'
		});
		tree.on('click', function(e){
			var id = e.attributes.id;		
			Ext.Ajax.request({
			url : '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/fast/checkCategory.php' , 
			params : { category : id },
			method: 'GET',
				success: function ( result, request ) { 
					if (result.responseText=="true"){
						toolbarButton.enable();
					}else {
						toolbarButton.disable();
					}
				},
				failure: function ( result, request) { 
					toolbarButton.disable();
				} 
			});

			storeid = id;
			store.baseParams.pagingID = id;		
			store.baseParams.filter = '';						
			store.baseParams.filter_id = '';
			store.load({params:{start:0,id:id, limit:25}});
			
	    });  
    		
	   tree.setRootNode(root);
	   root.expand();
	
	   // Iframe-Component
	   Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
		 onRender : function(ct, position){
		      this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
		 }
	   }); 
	   /*
	   var window;
		var main; var categories; var images; var blogTabs;
	   */
	   myExt.main = new Ext.ux.IFrameComponent({ 
				title:'Stammdaten',
				id: "MidMain", 
				forceFit:true,
				deferredRender: false,
				url: 'blogEdit.php?ext=1&category=&article='
	   });
	   myExt.categories = new Ext.ux.IFrameComponent({ 
				title:'Kategorien',
				id: "MidCategories", 
				forceFit:true,
				deferredRender: false,
				url: '../articles/artikeln2.inc.php?blog=1&ext=1&article='
	   });
	   myExt.images = new Ext.ux.IFrameComponent({ 
				title:'Bilder',
				id: "MidImages", 
				forceFit:true,
				deferredRender: false,
				url: '../articles/artikeln3.inc.php?ext=1&article='
	   });
	   
	   myExt.properties = new Ext.ux.IFrameComponent({ 
				title:'Eigenschaften',
				id: "MidProperties", 
				forceFit:true,
				deferredRender: false,
				url: '../articles/filter.php?ext=1&article='
	   });
	   myExt.links = new Ext.ux.IFrameComponent({ 
				title:'Links',
				id: "MidLinks", 
				forceFit:true,
				deferredRender: false,
				url: '../articles/links.php?ext=1&article='
	   });
	   myExt.downloads = new Ext.ux.IFrameComponent({ 
				title:'Downloads',
				id: "MidDownloads", 
				forceFit:true,
				deferredRender: false,
				url: '../articles/downloads.php?ext=1&article='
	   });
	   myExt.cross = new Ext.ux.IFrameComponent({ 
				title:'Cross-Selling',
				id: "MidCross", 
				forceFit:true,
				deferredRender: false,
				url: '../articles/cross.php?ext=1&article='
	   });
	   
	 
	   myExt.blogTabs = new Ext.TabPanel({
        enableTabScroll:true,
        forceFit:false,
		title:'Test',
		deferredRender: false,
		region: 'center',
		autoSize: true,
        activeTab:0,
        closable:false
	   });
	   myExt.blogTabs.add(myExt.main);
	   myExt.blogTabs.add(myExt.categories);
	   myExt.blogTabs.add(myExt.images);
	   myExt.blogTabs.add(myExt.properties);
	   myExt.blogTabs.add(myExt.links);
	   myExt.blogTabs.add(myExt.downloads);
  	   myExt.blogTabs.add(myExt.cross);
	   
	   myExt.window = new Ext.Window({
			      width:800,
			      layout: 'border',
			      height:400,
			      forceFit:true,
			      closeAction: 'hide',
			      title:"Blog-Eintrag hinzufügen / bearbeiten",
			      autoScroll:true,
			      modal:true,
			      items:[myExt.blogTabs]
	   });
	   
	   var viewport = new Ext.Viewport({
	        layout:'border',
	        items:[
	           grid,tree
	         ]
		});
	}};
}();
Ext.onReady(function(){
	Ext.QuickTips.init();
	myExt.init();
	$('body').setStyle('top',0);
	$('body').setStyle('left',0);
});
</script>
</head>
<body id="body">
</body>
</html>