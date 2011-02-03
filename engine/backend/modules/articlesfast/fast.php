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
    openDetails : function(name,id){
    	//name = "Test";
    	 // Define iFrames
	    <?php
	   	   
	    foreach ($components as $component){
	    	$ids[] =  str_replace("-","",strtolower($component["title"]));
	    	?>
	    	var <?php echo str_replace("-","",strtolower($component["title"]))?> = new Ext.ux.IFrameComponent({ 
			title:'<?php echo $component["title"]?>',
			id: "id<?php echo str_replace("-","",strtolower($component["title"]))?>"+id, 
			url: '<?php echo $component["url"]?>'+id
	    	});
	    	<?php
	    }
	    ?>

	    var articlEditor = new Ext.TabPanel({
	                    deferredRender:true,
	                    width:700,
	                    enableTabScroll:true,
	                    forceFit:true,
	        			height:500,
	        			title:name,
	                    activeTab:0,
	                    closable:true,
	                    items:[
		                   <?php echo implode($ids,",")?>
	                    ],
						listeners: {'tabchange': function (tTab, panel){
						      $('framepanel'+(panel.id)).src = panel.url;
						}} 
	    });
	    
	    myTab.add(
    	articlEditor
	    ).show();
    },
	init : function(){
       Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

        articleRecord = Ext.data.Record.create([
	        {name: 'articleID'},
            {name: 'articleName'},
            {name: 'ordernumber'},
            {name: 'supplier'},  
            {name: 'price'},
            {name: 'active', type: 'float'},
            {name: 'image'},
            {name: 'instock'},
            {name: 'tax'},
            {name: 'payments'},
            {name: 'active'},
            {name: 'info'}
            
        ]);
        
       store = new Ext.data.GroupingStore({
	        url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/fast/getArticles.php',
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'articles',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'articleName','articleID','ordernumber','supplier','price','active','image','payments'
	            ]
	        },articleRecord),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
	    store.setDefaultSort('lastpost', 'desc');
	   //console.log(store);
	    // Dynamic read supplier
	    dsSupplier = new Ext.data.Store({
	   url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/fast/getSupplier.php', //url to data object (server side script)
	   reader:  new Ext.data.JsonReader(
			{
				root: 'suppliers',//name of the property that is container for an Array of row objects
				id: 'id'//the property within each row object that provides an ID for the record (optional)
			},
			[
				{name: 'id'},//name of the field in the stock table (not the industry table)
				{name: 'name'}
			]
	    )
		});//end dsIndustry   
		dsSupplier.load();
		
		// Dynamic read supplier
	    dsTax = new Ext.data.Store({
		   url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/fast/getTax.php', //url to data object (server side script)
		   reader:  new Ext.data.JsonReader(
				{
					root: 'taxes',//name of the property that is container for an Array of row objects
					id: 'id'//the property within each row object that provides an ID for the record (optional)
				},
				[
					{name: 'id'},//name of the field in the stock table (not the industry table)
					{name: 'name'}
				]
		    )
		});//end dsIndustry   
		dsTax.load();
	
	
		function addRecord() {
			var r = new articleRecord({
	        //specify default values
		        articleName: '',
				articleID: 0, 
		        ordernumber: '',
		        supplier:0,
		        price: 0.00,
		        active: 0,
		        image: '',
		        instock: 0,
		        tax: 1,
		        active:0,
		        payments: 0,
		        info:''
	   	 	});
	        grid.stopEditing();//stops any acitve editing
	
	            
	        store.insert(0, r); //1st arg is index,
	                         //2nd arg is Ext.data.Record[] records
	            
			//start editing the specified rowIndex, colIndex
	        //make sure you pick an editable location
			//otherwise it won't initiate the editor
			grid.startEditing(0, 0);
	    }; // end addRecord 

    
    	function updateDB(oGrid_Event) {
			if (oGrid_Event.value instanceof Date)
			{   //format the value for easy insertion into MySQL
				var fieldValue = oGrid_Event.value.format('Y-m-d H:i:s');
			} else
			{
				var fieldValue = oGrid_Event.value;
			}	
					
			//submit to server
            Ext.Ajax.request( //alternative to Ext.form.FormPanel? or Ext.BasicForm
                {   //Specify options (note success/failure below that
                    //receives these same options)
                    waitMsg: 'Saving changes...',
                    //url where to send request (url to server side script)
                    url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/fast/updateArticle.php', 
					
					//If specify params default is 'POST' instead of 'GET'
                    //method: 'POST', 
					
					//params will be available server side via $_POST or $_REQUEST:
                    params: { 
                        task: "update", //pass task to do to the server script
                        key: 'articleID',//pass to server same 'id' that the reader used
                        category: storeid,
                        
						//For existing records this is the unique id (we need
						//this one to relate to the db). We'll check this
						//server side to see if it is a new record                    
                        keyID: oGrid_Event.record.data.articleID,
						
						//For new records Ext creates a number here unrelated
						//to the database
					    //-bogusID: oGrid_Event.record.id,

                        field: oGrid_Event.field,//the column name
                        value: fieldValue,//the updated value
                        
						//The original value (oGrid_Event.orginalValue does
						//not work for some reason) this might(?) be a way
						//to 'undo' changes other than by cookie? When the
						//response comes back from the server can we make an
						//undo array?                         
                        originalValue: oGrid_Event.record.modified
						
                    },//end params
                    
					//the function to be called upon failure of the request
					//(404 error etc, ***NOT*** success=false)
                    failure:function(response,options){
                        Ext.MessageBox.alert('Warning','Oops...');
                        //ds.rejectChanges();//undo any changes
                    },//end failure block      
                    
					//The function to be called upon success of the request                                
                    success:function(response,options){
						//Ext.MessageBox.alert('Success','Yeah...');
                        
						
						//if this is a new record need special handling
						if(oGrid_Event.record.data.articleID == 0){
							var responseData = Ext.util.JSON.decode(response.responseText);//passed back from server
							
							//Extract the ID provided by the server
							var newID = response.responseText;//responseData.newID;
							//oGrid_Event.record.id = newID;
							
							//Reset the indicator since update succeeded
							oGrid_Event.record.set('newRecord','no');
							
							//Assign the id to the record
							oGrid_Event.record.set('articleID',newID);
							//Note the set() calls do not trigger everything
							//since you may need to update multiple fields for
							//example. So you still need to call commitChanges()
							//to start the event flow to fire things like
							//refreshRow()
							
							//commit changes (removes the red triangle which
							//indicates a 'dirty' field)
							store.commitChanges();

						    //var whatIsTheID = oGrid_Event.record.modified;
						
						//not a new record so just commit changes	
						} else {
							//commit changes (removes the red triangle
							//which indicates a 'dirty' field)
							store.commitChanges();
						}
                    }//end success block                                      
                 }//end request config
            ); //end request  
        }; //end updateDB 
	
	    var limitArray = [['25'],['50'],['100'],['250'],['500']];
		var limitStore = new Ext.data.SimpleStore({
	        fields: ['limitArray'],
	        data : limitArray
	    });
    
	    function rendererOptionBar(value, meta, rec, rowI, colI, store)
		{
			var rec = store.getAt(rowI);
			var name = rec.get('articleName').replace(/\"/,"");
			name = name.replace(/\'/,"");
			var id = rec.get('articleID');
			return String.format(
			'<a class="ico pencil" style="cursor:pointer" onclick="myExt.openDetails(\''+name+'\','+id+')"></a><a class="ico pencil_arrow" style="cursor:pointer" onclick="parent.loadSkeleton(\'articles\',false, {\'article\':'+id+'});"></a><a class="ico delete" style="cursor:pointer" onclick="deleteArticle('+id+',\''+name+'\');"></a><a class="ico folder_plus" style="cursor:pointer" onclick="duplicateArticle('+id+',\''+name+'\')"></a>'
//			'<a class="ico pencil" style="cursor:pointer" onclick="parent.loadSkeleton({2},false, {3})"></a><a class="ico delete" style="cursor:pointer" onclick="deleteArticle({0},{1})"></a><a class="ico folders_plus" onclick="duplicateArticle({0},{1})" style="cursor:pointer" onclick="duplicateArticle({0},{1})"></a>',id,"'"+name+"'","'articles'","{'article':"+id+"}"
			);
		}
	    
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
           id: 'ordernumber', 
           header: "Bestellnummer",
           dataIndex: 'ordernumber',
           width: 60,
           sortable: true,
           editor: new Ext.form.TextField({
               allowBlank: false
           })
        },
        {
           id: 'supplier', 
           header: "Hersteller",
           dataIndex: 'supplier',
           sortable: true,
           width: 120,
			editor: new Ext.form.ComboBox({ 
				typeAhead: false, 
				editable: false,
				triggerAction: 'all',
				lazyRender: true,//should always be true for editor
				store: dsSupplier,
				displayField: 'name',
				valueField: 'id'
			}),
			renderer:  //custom rendering specified inline
					function(data) {
						record = dsSupplier.getById(data);
						if(record) {
							return record.data.name;
						} else {
							//return data;
							return 'missing data';
						}
					}
        },
    	{
           id: 'name', 
           header: "Bezeichnung",
           dataIndex: 'articleName',
           width: 120,
           sortable: true,
           editor: new Ext.form.TextField({
               allowBlank: false
           })
        },
        {
           id: 'price', 
           <?php
            $queryPriceGroup = mysql_query("
			SELECT taxinput FROM s_core_customergroups WHERE groupkey='EK'
			");
			$priceWithTax = mysql_result($queryPriceGroup,0,"taxinput");
			
			// Add tax if brutto mode
			if ($priceWithTax["taxinput"]){
			?>
			header: "Brutto VK",
			<?php
			}else {
			?>
			header: "Netto VK",
			<?php
			}
           ?>
           dataIndex: 'price',
           width: 60,
           sortable: true,
           align: 'right',
           editor: new Ext.form.TextField({
               allowBlank: false
           })
        },
        {
           header: "id",
           dataIndex: 'articleID',
           width: 0,
           hidden:true
        },
        {
           header: "image",
           dataIndex: 'image',
           width: 0,
           hidden:true
        },
        {
           id: 'tax', 
           header: "MwSt",
           dataIndex: 'tax',
           sortable: true,
           align: 'right',
           width: 40,
			editor: new Ext.form.ComboBox({ 
				typeAhead: false, 
				editable: false,
				triggerAction: 'all',
				lazyRender: true,//should always be true for editor
				store: dsTax,
				displayField: 'name',
				valueField: 'id'
			}),
			renderer:  //custom rendering specified inline
					function(data) {
						record = dsTax.getById(data);
						if(record) {
							return record.data.name;
						} else {
							//return data;
							return 'missing data';
						}
					}
        },
    	{
           id: 'instock', 
           header: "Lagerbestand",
           align: 'right',
           dataIndex: 'instock',
           renderer: renderInstock,
           sortable: true,
           width: 60,
           editor: new Ext.form.TextField({
               allowBlank: false
           })
        },
        {
           id: 'payments', 
           header: "Verkäufe",
           align: 'right',
           dataIndex: 'payments',
           sortable: true,
           width: 60
        },
        {
           id: 'active', 
           header: "Aktiv",
           dataIndex: 'active',
           align: 'right',
           sortable: true,
           width: 40,
           editor: new Ext.form.TextField({
               allowBlank: false
           }),
           renderer: renderActive
        }
        ,
        {
           id: 'info', 
           header: "Info",
           dataIndex: 'info',
           align: 'right',
           width: 60
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
        
     	function renderActive(value, p, r){
    	var id = r.data.active;
    	if (id==1){
    		return "<a class='ico accept'></a>";
    	}else {
    		return "<a class='ico exclamation'></a>";
    	}
    	
	    }
	    cm.defaultSortable = true;
	   
	    function renderInstock(val, p, r){
	    	if(val > 0){
			//-> this = obj (row from grid, properties of id, name='change', style)
	            return '<span style="color:green;">' + val + '</span>';
	        }else if(val <= 0){
	            return '<span style="color:red;">' + val + '</span>';
	        }
	        return val;
	    }
    

	    var toolbarButton =new Ext.Toolbar.Button  ( {
	                text: 'Artikel hinzufügen',
	                tooltip: 'Klicken Sie hier um einen Artikel hinzuzufügen',
					iconCls: 'blist2',
					disabled: true,
	                handler: addRecord //what happens when user clicks on it
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
	            	lazyRender: true,
	            	lazyInit: true,
	            	mode:'local',
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
	            })
            ]
	    });
	    var grid = new Ext.grid.EditorGridPanel({
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
	            showPreview:true,
	            getRowClass : function(record, rowIndex, p, store){
	              // console.log(record);
	            }
	        },
	        bbar: pager,
	        //Add a top bar      
	        tbar: [
	           toolbarButton,'-', {
		        text: 'Ansicht splitten',
		        iconCls: 'blist',
		        tooltip: {text:'Aktivieren Sie diese Funktion, um im unteren Grid-Bereich direkt die Artikel-Stammdaten bearbeiten zu können', title:'Split-View'},
		        enableToggle: true,
		        toggleHandler: toogleView,
		        pressed: false
		    },'-','Klicken Sie doppelt auf eine Spalte, um diese zu bearbeiten'
	        ]
	    });
	    
	    
    
	    function limitFilter () {
		    var status = Ext.getCmp("status");
		    grid.store.baseParams["limit"] = status.getValue();
		    pager.pageSize = parseInt(status.getValue());
		    grid.store.lastOptions.params["start"] = 0;
		    grid.store.reload();
		}
	    grid.addListener('rowclick', myRowClick);
  		grid.addListener('afteredit', handleEdit);
  	
		function handleEdit(editEvent) {
			//determine what column is being edited
			var gridField = editEvent.field;
			
			//start the process to update the db with cell contents
			updateDB(editEvent);
				
		}
	    // trigger the data store load
	    //dsSupplier.load();
	    store.load({params:{start:0, limit:25}});
	    
	    var Tree = Ext.tree;
       
       
		var tree = new Tree.TreePanel({
	                	split:true,
				        animate:false, 
				        collapsible: true,
				        title:'Kategorie auswählen',
				        width: 220,
				        margins:'0 0 0 0',
				        minSize: 175,
				        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getCategories.php'}),
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
						//Ext.get('newButton').highlight();
					}
				},
				failure: function ( result, request) { 
					//Ext.MessageBox.alert('Failed', result.responseText);
					toolbarButton.disable();
					//Ext.get('newButton').highlight(); 
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
	 
		var activeRow;
	                        
	 

	    function myRowClick(grid,number,event){
			if (!show) return;
	    	if (grid.store.data.items[number].data.articleID==activeRow) return false;
	    	var gridField = grid.store.data.items[number].data;
	    	//console.log(gridField);
	    	gridField.articleName = gridField.articleName.replace(/\'/,"");
	    	gridField.articleName = gridField.articleName.replace(/\"/,"");
	
	    	var id = gridField.articleID;
	    	
	    	<?php
	    	foreach ($ids as $id){
	    	?>
	    	Ext.get('framepanelMid<?php echo $id?>').dom.src = Ext.get('framepanelMid<?php echo $id?>').dom.src.replace(/article\=(.*)/,"article="+id);
	    	<?php
	    	}
	    	?>
	    	
	    	activeRow = gridField.articleID;
	    };
    
	    var supplierStore = new Ext.data.Store({
		    url: '../../../backend/ajax/getSupplier.php',		
		    reader: new Ext.data.JsonReader({
		           root: '',
		           fields : ['id', 'name']
		        })
		});
		supplierStore.load();
    
		function startFilter()
		{
			var selected_id = "";
			$(document.body).getElements('input[name=filter_selection]').each(function(item, index, allItems){
				if(item.checked == true)
				{
					switch(item.id)
					{
						case "radio_1":
							if(Ext.getCmp('f_supplier').getValue() != "")
							{
								var f_id = 	Ext.getCmp('f_supplier').getValue();
								Ext.getCmp('article_grid').store.baseParams.filter = 'filter_supplier';						
								Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;						
								Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});
							}
						break;
						case "radio_2":
							if(Ext.getCmp('f_ordernumber').getValue() != "")
							{
								var f_id = 	Ext.getCmp('f_ordernumber').getValue();		
								Ext.getCmp('article_grid').store.baseParams.filter = 'f_ordernumber';						
								Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;					
								Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});
							}
						break;
						case "radio_3":
							if(Ext.getCmp('f_articlename').getValue() != "")
							{
								var f_id = 	Ext.getCmp('f_articlename').getValue();	
								Ext.getCmp('article_grid').store.baseParams.filter = 'f_articlename';						
								Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;					
								Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});
							}
						break;
						case "radio_4":			
							Ext.getCmp('article_grid').store.baseParams.filter = 'f_instock';						
							Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;			
							Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});							
						break;
						case "radio_5":		
							Ext.getCmp('article_grid').store.baseParams.filter = 'f_nocat';						
							Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;				
							Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});							
						break;
						case "radio_6":	
							Ext.getCmp('article_grid').store.baseParams.filter = 'f_noimg';						
							Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;					
							Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});							
						break;
						case "radio_7":	
							Ext.getCmp('article_grid').store.baseParams.filter = 'f_bundles';						
							Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;					
							Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});							
						break;
						case "radio_8":	
							Ext.getCmp('article_grid').store.baseParams.filter = 'f_live';						
							Ext.getCmp('article_grid').store.baseParams.filter_id = f_id;					
							Ext.getCmp('article_grid').store.load({params:{start:0,id:id, limit:25}});							
						break;
					}
				}
			});
		}
		
		function checkRadio(el)
		{
			$(document.body).getElements('input[name=filter_selection]').each(function(item, index, allItems){
				if(item.checked == true)
				{
					Ext.getCmp(item.id).setValue(false);
				}
			});
			
			Ext.getCmp(el).setValue(true);
//			Ext.getCmp('filter_btn').enable();
		}
		
	    //Filter Form
	    var top = new Ext.FormPanel({
	        frame:true,
	        title: 'Filter / Suche',
	        iconCls: 'filterE',
	        bodyStyle:'padding:5px 5px 0',
	        width: 600,
	        height:500,
	        items: [{
	                columnWidth:.5,
	                layout: 'form',
	                items: [{
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_1',
	                    boxLabel: 'Suche nach Hersteller',
	                    hideLabel: true
	                },{
	                    xtype:'combo',
	                    id:'f_supplier',
	                    width:200,
	                    hideLabel: true,
	                    store: supplierStore,
	                    valueField:'id',
	                    displayField:'name',
	                    mode: 'local',
	                    typeAhead: true,
	                    triggerAction: 'all',
	                    forceSelection : true,
	                    listeners: {'select': function(){
	                    	checkRadio('radio_1');
	                    },'keyup': function(el, e){
	                    	if(e.getKey() == 13)
	                    	{
	                    		startFilter();	
	                    	}
	                    }},
	                    enableKeyEvents: true
	                }, {
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_2',
	                    boxLabel: 'Suche nach Bestellnnummer',
	                    hideLabel: true
	                },{
	                    xtype:'textfield',
	                    id:'f_ordernumber',
	                    width:200,
	                    hideLabel: true,
	                    listeners: {'focus': function(){
	                    	checkRadio('radio_2');
	                    },'keyup': function(el, e){
	                    	if(e.getKey() == 13)
	                    	{
	                    		startFilter();	
	                    	}
	                    }},
	                    enableKeyEvents: true
	                }, {
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_3',
	                    boxLabel: 'Suche nach Artikelbezeichnung',
	                    hideLabel: true
	                },{
	                    xtype:'textfield',
	                    id:'f_articlename',
	                    width:200,
	                    hideLabel: true,
	                    listeners: {'focus': function(){
	                    	checkRadio('radio_3');
	                    },'keyup': function(el, e){
	                    	if(e.getKey() == 13)
	                    	{
	                    		startFilter();	
	                    	}
	                    }},
	                    enableKeyEvents: true
	                }, {
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_4',
	                    boxLabel: 'Nicht vorrätige Artikel anzeigen',
	                    hideLabel: true
	                }, {
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_5',
	                    boxLabel: 'Artikel ohne Kategorie-Zuordnung anzeigen',
	                    hideLabel: true
	                }, {
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_6',
	                    boxLabel: 'Artikel ohne Bilder anzeigen',
	                    hideLabel: true
	                }, {
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_7',
	                    boxLabel: 'Artikel mit Bundles',
	                    hideLabel: true
	                }, {
	                    xtype:'radio',
	                    name: 'filter_selection',
	                    id: 'radio_8',
	                    boxLabel: 'Artikel mit Liveshopping',
	                    hideLabel: true
	                }],

		        buttons: [
		        {
		            id: 'filter_btn',
                    text: 'Anzeigen',
                    style: 'margin-left:-20px;',
                    listeners: {'click': function(){
                    	startFilter();	
                    }}	
		        }]

	        }]
	    });

      
	    myTab = new Ext.TabPanel({
			region:'center',
			width:700,
			enableTabScroll:true,
			forceFit:true,
			height:500,
			activeTab:0,
			items:[grid]
	    });
    
	    myTabLeft = new Ext.TabPanel({
			region:'west',
			split:true,
			collapsible: true,
			deferredRender:true,
			activeTab:1,
			deferredRender:false,
			width:260,
			enableTabScroll:true,
			forceFit:false,
			height:500,
			items:[tree, top]
	    });
    	
	    <?php
    	unset($ids);
	    foreach ($components as $component){
	    	$ids[] =  "myExt.".str_replace("-","",strtolower($component["title"]));
	    	?>
	    	<?php echo "myExt.".str_replace("-","",strtolower($component["title"]))?> = new Ext.ux.IFrameComponent({ 
				title:'<?php echo $component["title"]?>',
				id: "Mid<?php echo str_replace("-","",strtolower($component["title"]))?>", 
				url: '<?php echo $component["url"]?>',
				lazyRender: false
	    	});
	    	<?php
	   	    }
	    ?>
	    
	
		
    	var bottomPanel = new Ext.TabPanel({
	                    deferredRender:false,
	                    width:700,
	                    enableTabScroll:true,
	                    forceFit:true,
	        			region: 'south',
	        			title:'ARTIKEL',
	        			split: true,
	        			height: 400,
	                    activeTab:0,
	                    closable:true,
	                    items:[<?php echo implode(",",$ids)?>]
	    });
	    var show = false;
	    function toogleView(){
	    	if (!show){ 
	    		bottomPanel.show();
	    		bottomPanel.ownerCt.ownerCt.doLayout();
	    		show = true;
	    	}else {
	    		bottomPanel.hide();
	    		bottomPanel.ownerCt.ownerCt.doLayout();
	    		show = false;
	    	}
	    }
	    bottomPanel.hide();
	    //bottomPanel.ownerCt.ownerCt.doLayout();
	  // bottomPanel.hide();
	   var articleoptions = {
	                region:'east',
	                title: 'Artikel Optionen',
	                collapsible: true,
	                width: 225,
	                minSize: 175,
	                maxSize: 400,
	                margins:'0 0 0 0',
	                contentEl: 'articleOptions',
	                autoScroll:true    
	             };
	    
	   var viewport = new Ext.Viewport({
	        layout:'border',
	        items:[
	        
	            new Ext.Panel(
	            {
	            	 margins:'0 5 5 0',
	            	 region: 'center',
	            	 id:'main-view',
		             layout:'border',
		             hideMode:'offsets',
		             split: true,
	            	 items: [
			            
			             
			             	myTab,bottomPanel
			             
	            	 ]

	            }
	            ),
	             myTabLeft
	         ]
		});
       
   
   		myTabLeft.activate(0);      
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




<div id="articleOptions" style="padding: 5px;">

</div>


 

 </body>
</html>

