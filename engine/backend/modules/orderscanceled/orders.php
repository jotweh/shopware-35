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
    
    .blue-row .x-grid3-cell-inner{
      color:blue;
    }
    .red-row .x-grid3-cell-inner{
      color:red;
    }
    .green-row .x-grid3-cell-inner{
      color:green;
    } 

    </style>
	<script>
	function sWrapper(sFunction, sId){
		switch (sFunction){
			case "sendVoucher":
				var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/handleCancelOrders.php",{method: 'get', onComplete: function(json){
						parent.Growl(json);
						myExt.reload();
					}}).request('sendVoucher='+sId);
				//window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?sendVoucher="+sId+"&voucherID="+$('voucherID'+sId).value;
				break;
			case "sendQuestion":
				var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/handleCancelOrders.php",{method: 'get', onComplete: function(json){
						parent.Growl(json);
						myExt.reload();
					}}).request('sendQuestion='+sId);
				//window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?sendQuestion="+sId;
				break;
			case "delete":
				//window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
				break;
		}
	}
	
	
	function sendVoucher(ev,text,voucher){
			ev = ev+"|"+voucher;
			//console.log(ev);
			parent.sConfirmationObj.show('<?php echo $sLang["orderscanceled"]["orders_should_the_customer"] ?> "'+text+'" <?php echo $sLang["orderscanceled"]["orders_a_voucher_really"] ?>',window,'sendVoucher',ev);
	}
	function sendQuestion(ev,text){
			parent.sConfirmationObj.show('<?php echo $sLang["orderscanceled"]["orders_should_the_customer_1"] ?> "'+text+'" <?php echo $sLang["orderscanceled"]["orders_really_be_questioned"] ?>',window,'sendQuestion',ev);
	}
	function deleteOrder(ev,text){
			parent.sConfirmationObj.show('<?php echo $sLang["orderscanceled"]["orders_aborted_appointment_of_customer"] ?> "'+text+'" <?php echo $sLang["orderscanceled"]["orders_really_deleted"] ?>',window,'delete',ev);
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
    openClient : function(id,name){
			//console.log(target);
		   // Define iFrames
		    var stammdaten = new Ext.ux.IFrameComponent({ 
				id: "idStammdaten"+id+myExt.GetRandom(1,10000), 
				url: '../orders/main.php?id='+id
			});
			
			
			
		    var customer = new Ext.TabPanel({
		                    deferredRender:true,
		                    width:700,
		                    enableTabScroll:true,
		                    forceFit:true,
		        			height:500,
		        			id:'CT'+myExt.GetRandom(1,10000),
		        			title:name,
		                    activeTab:0,
		                    closable:true,
		                    items:[
			                    stammdaten
		                    ]
		    });
		   
		    myTab.add(
	    		customer
		    ).show();
			  
		    
	},
	markAll: function(){
    	$$('.markedArticles').each(function(e){
    		e.checked = true;
    	});
    },
    deleteOrders: function(){
    	Ext.MessageBox.confirm('<?php echo $sLang["orderscanceled"]["orders_confirm"] ?>', '<?php echo $sLang["orderscanceled"]["orders_marked_orders_really_deleted"] ?>', function deleteClientConfirmed(btn){
	    	
    		if (btn=="yes"){
    			
	    		var deleted = false;
		    	$$('.markedArticles').each(function(e){
		    		if (e.checked){
		    		
		    			var articleID = e.getProperty('value');
		    			
		    			var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteOrder.php",{method: 'post', onComplete: function(json){
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
	/*
	Action handler
	*/
	handleAction: function (value,customer){
		var action = $('SEL'+value).value;
		//alert(customer); alert(value);
		
		if (action && value){
			if (action == 1){
				// Grund erfragen
				sendQuestion(value,customer);
			}else if (action.match(/V/)){
				// Gutschein schicken
				sendVoucher(value,customer,action);
			}
		}
		
	},
	init : function(){
       Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

     store = new Ext.data.Store({
    	
     	
        	url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getOrdersCancel.php',
       
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'order',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'id','phone','email','ordertimeFormated','details','ordernumber','comment','invoice_amount','transactionID','statusDescription','status','cleared','clearingDescription','paymentDescription', 'customer','userID'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
    store.setDefaultSort('ordertime', 'desc');

    
   storeBasket = new Ext.data.Store({
   		
	    	 url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getBaskets.php',
	   
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'baskets',
	            totalProperty: 'totalCount',
	            id: 'datum',
	            fields: [
	                'datum','baskets','basketavg','visits','hits'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
    storeBasket.setDefaultSort('ssvdate', 'desc');
    
   storeArticles = new Ext.data.Store({
   		
	   	 	url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getBaskets.php?return=articles',
	   
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'articles',
	            totalProperty: 'totalCount',
	            id: 'ordernumber',
	            fields: [
	                'ordernumber','articlename','quantity'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
    storeArticles.setDefaultSort('quantity', 'asc');
    
    storePages = new Ext.data.Store({
    	
	     	url: '<?php echo empty($_SERVER["HTTPS"]) ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getBaskets.php?return=pages',
	    
	        baseParams: {pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'pages',
	            totalProperty: 'totalCount',
	            id: 'viewport',
	            fields: [
	                'percent','absolute','viewport'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    
    storePages.setDefaultSort('percent', 'asc');
    /*
    Bestellstati
    */
    
 
	
	var paymentArray = new Array();
    /*
    Bestellstati
    */
    <?php
    // Read possible order states
    $getPayment = mysql_query("
    SELECT id, description FROM s_core_paymentmeans ORDER BY id ASC
    ");
    while ($payment=mysql_fetch_assoc($getPayment)){
     	$paymentmeans[] = "[{$payment["id"]},'{$payment["description"]}']";
     	?>
     		paymentArray[<?php echo $payment["id"] ?>] = '<?php echo $payment["description"] ?>';
     	<?php
    }
    ?>
    var paymentmeans = [[-1,'<?php echo $sLang["orderscanceled"]["orders_show_all"] ?>'],<?php echo implode(",",$paymentmeans) ?>];
    // trigger the data store load
    var paymentstore = new Ext.data.SimpleStore({
	    fields: ['id', 'state'],
	    data : paymentmeans
	});
	

	
	function renderStatus(val, p, r){	
		if (val==2 || val==7 || val==12){
			return '<span style="color:#009933;">' + r.data.statusDescription + '</span>';
		}else {
			return '<span style="color:red;">' + r.data.statusDescription + '</span>';
		}
    } 
    function renderCleared(val, p, r){	
    	if (val==11 || val==12){
			return '<span style="color:#009933;">' + r.data.clearingDescription + '</span>';
		}else {
			return '<span style="color:red;">' + r.data.clearingDescription + '</span>';
		}
    } 
   
    function renderAction(value, p, r){
    	if (r.data.comment && r.data.comment != "Frage gesendet"){
    		return r.data.comment;
    	}else {
    		<?php
			// Query possible vouchers for select-field
			$sql = "
				SELECT ev.id, ev.description, ev.value
				FROM s_emarketing_vouchers ev
				WHERE  ev.modus = 1 AND (ev.valid_to >= now() OR ev.valid_to='0000-00-00')
				AND (ev.valid_from <= now() OR ev.valid_from='0000-00-00')
				AND (
					SELECT evc.id
					FROM s_emarketing_voucher_codes evc
					WHERE evc.voucherID = ev.id
					AND evc.userID = 0
					AND evc.cashed = 0
					LIMIT 1
				)
			";
			$vouchers = array();
			$result = mysql_query($sql);
			if($result&&mysql_num_rows($result))
			{
				while ($row = mysql_fetch_assoc($result)) {
					$vouchers[$row["id"]] = $row;
				}
			}else{
				echo "
					if(r.data.comment == 'Frage gesendet')
					{
						return r.data.comment;
					}";
			}
			
			foreach ($vouchers as $id => $value){
				$options .= '<option value="V'.$id.'">'.$sLang["orderscanceled"]["orders_send_voucher"].' '.str_replace("'","",$value["description"]).' '.$sLang["orderscanceled"]["orders_worth"].' '.$value["value"].')'.'</option>';
			}
			?>
			if(r.data.comment)
			{
				return String.format('<select id="SEL{1}" onchange="myExt.handleAction({1},{0})"><option value=0><?php echo $sLang["orderscanceled"]["orders_please_select"] ?></option><?php echo $options ?></select>',"'"+r.data.customer+"'",r.data.id);
			}else{
				return String.format('<select id="SEL{1}" onchange="myExt.handleAction({1},{0})"><option value=0><?php echo $sLang["orderscanceled"]["orders_please_select"] ?></option><option value=1><?php echo $sLang["orderscanceled"]["orders_Ask_why"] ?></option><?php echo $options ?></select>',"'"+r.data.customer+"'",r.data.id);	
			}
    	}
    }
    var expander = new Ext.grid.RowExpander({
        tpl : new Ext.Template(
 			'<div style="padding: 5 5 5 5"><?php echo$sLang["orderscanceled"]["orders_customer"] ?> {customer}<br /><?php echo $sLang["orderscanceled"]["orders_Phone"] ?> {phone}<br /><?php echo $sLang["orderscanceled"]["orders_email"] ?> {email}<br/><br/><?php echo $sLang["orderscanceled"]["orders_Order_positions"] ?><br />{details}</div>'             
        )
    });
    
    
    var cm = new Ext.grid.ColumnModel([
    expander,
		{
    		header: "",
    		width: 30,
    		sortable: false,
    		locked:true,
    		renderer: function (v,p,r,rowIndex,i,ds){
    			return '<input type="checkbox" class="markedArticles" name="markedArticles" value="'+r.data.id+'"/>';
    		}
    	},
   		{
           id: 'customernumber', 
           header: "<?php echo $sLang["orderscanceled"]["orders_Time"] ?>",
           dataIndex: 'ordertimeFormated',
           width: 150
        },
        {
           id: 'action', 
           header: "<?php echo $sLang["orderscanceled"]["orders_action"] ?>",
           locked:true,
           renderer: renderAction,
           width: 70
        },
        {
           header: "<?php echo $sLang["orderscanceled"]["orders_Amount"] ?>",
           dataIndex: 'invoice_amount',
           width: 35,
           align: 'right'
        },
    	{
           id: 'company', 
           header: "<?php echo $sLang["orderscanceled"]["orders_Transaction"] ?>",
           dataIndex: 'transactionID',
           width: 80
        },
    	{
           header: "<?php echo $sLang["orderscanceled"]["orders_Payment"] ?>",
           dataIndex: 'paymentDescription',
           width: 75
        },
        {
           header: "<?php echo $sLang["orderscanceled"]["orders_customer"] ?>",
           dataIndex: 'customer',
           width: 75
        },
        {
         header: "Optionen",
         dataIndex: 'options',
         renderer: renderOptions,
         width: 30
        }
        ]);
    cm.defaultSortable = true;
 
    function renderOptions(value, p, r){
		var id = r.data.id;
		var name = r.data.ordernumber + " " + r.data.customer;
		
		return String.format(
		'<a class="ico pencil_arrow" style="cursor:pointer" onclick="parent.loadSkeleton({2},false,{3})"></a>',id,"'"+name+"'","'orders'","{'id':"+r.data.id+"}"
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
    
    var pager2 = new Ext.Toolbar({
    items: [
     		new Ext.Button  ({
            	text: '<?php echo $sLang["orderscanceled"]["orders_mark_all_orders"] ?>',
           		handler: myExt.markAll
            }),
            '-',
        	new Ext.Button  ( {
            	text: '<?php echo $sLang["orderscanceled"]["orders_delete_marked_orders"] ?>',
            	handler: myExt.deleteOrders
            })
           ]}
    );
    var pager = new Ext.PagingToolbar({
            pageSize: 25,
            store: store,
            displayInfo: true,
            displayMsg: '<?php echo $sLang["orderscanceled"]["orders_orders"] ?> {0} - {1} von {2}',
            emptyMsg: "<?php echo $sLang["orderscanceled"]["orders_no_orders_found"] ?>",
            items:[
            'Anzahl',
            { 
            	xtype: 'combo',
            	id: 'status',
            	fieldLabel: 'Last Name',
            	typeAhead: false,
            	title:'<?php echo $sLang["orderscanceled"]["orders_Number_of_Orders"] ?>',
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
        	'-'
        	,
            '<?php echo $sLang["user"]["user_search"] ?> ',
            {
            	xtype: 'textfield',
            	id: 'search',
            	selectOnFocus: true,
            	width: 120,
            	listeners: {
	            	'render': {fn:function(ob){
	            		ob.el.on('keyup', searchFilter, this, {buffer:500});
	            	}, scope:this}
            	}
            }
           
            ]
        });
    
    var grid = new Ext.grid.GridPanel({
      	region:'center',
        width:700,
        height:500,
        title:'<?php echo $sLang["orderscanceled"]["orders_canceled_orders"] ?>',
        store: store,
        cm: cm,
        plugins:[expander],
        autoSizeColumns: true,
        trackMouseOver:true,
        loadMask: true,
        stripeRows: true,
        viewConfig: {
            forceFit:true,
            stripeRows: true,
            getRowClass : function(record, rowIndex, p, store){
              //return 'red-row';
            }
        },
     	bbar: pager,
     	tbar: pager2
    });
    
    var gridBasket = new Ext.grid.GridPanel({
      	region:'center',
        width:700,
        height:500,
        title:'<?php echo $sLang["orderscanceled"]["orders_overview"] ?>',
        store: storeBasket,
        cm: new Ext.grid.ColumnModel([
		   		{
		           id: 'customernumber', 
		           header: "<?php echo $sLang["orderscanceled"]["orders_date"] ?>",
		           dataIndex: 'datum',
		           width: 150
		        },
		        {
		           id: 'action', 
		           header: "<?php $sLang["orderscanceled"]["orders_abandoned_shopping_carts"] ?>",
		           dataIndex: 'baskets',
		           locked:true,
		           width: 70
		        },
		        {
		           header: "<?php echo $sLang["orderscanceled"]["orders_shopping_cart"] ?>",
		           dataIndex: 'basketavg',
		           width: 35,
		           align: 'right'
		        },
		    	{
		           id: 'company', 
		           header: "<?php echo $sLang["orderscanceled"]["orders_Visitor"] ?>",
		           dataIndex: 'visits',
		           width: 80
		        },
		    	{
		           header: "<?php echo $sLang["orderscanceled"]["orders_Page_views"] ?>",
		           dataIndex: 'hits',
		           width: 75
		        }
		        ]),
        autoSizeColumns: true,
        trackMouseOver:true,
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
     
     var gridArticles = new Ext.grid.GridPanel({
      	region:'center',
        width:700,
        height:500,
        title:'<?php echo $sLang["orderscanceled"]["orders_article"] ?>',
        store: storeArticles,
        cm: new Ext.grid.ColumnModel([
		   		{
		           id: 'customernumber', 
		           header: "<?php echo $sLang["orderscanceled"]["orders_order_number"] ?>",
		           dataIndex: 'ordernumber',
		           width: 150
		        },
		        {
		           id: 'action', 
		           header: "<?php echo $sLang["orderscanceled"]["orders_article"]?>",
		           dataIndex: 'articlename',
		           locked:true,
		           width: 70
		        },
		        {
		           header: "<?php echo $sLang["orderscanceled"]["orders_quantity"] ?>",
		           dataIndex: 'quantity',
		           width: 35,
		           align: 'right'
		        }
		        ]),
        autoSizeColumns: true,
        trackMouseOver:true,
        loadMask: true,
        stripeRows: true,
        viewConfig: {
            forceFit:true,
            stripeRows: true,
            getRowClass : function(record, rowIndex, p, store){
              //return 'red-row';
            }
        }
    });
    
    var gridPages = new Ext.grid.GridPanel({
      	region:'center',
        width:700,
        height:500,
        title:'<?php echo $sLang["orderscanceled"]["orders_Exit_Pages"] ?>',
        store: storePages,
        cm: new Ext.grid.ColumnModel([
		   		{
		           id: 'customernumber', 
		           header: "Prozent",
		           dataIndex: 'percent',
		           width: 35
		        },
		        {
		           id: 'action', 
		           header: "<?php echo $sLang["orderscanceled"]["orders_number"] ?>",
		           dataIndex: 'absolute',
		           locked:true,
		           width: 35
		        },
		        {
		           header: "<?php echo $sLang["orderscanceled"]["orders_viewport"] ?>",
		           dataIndex: 'viewport',
		           width: 90,
		           align: 'right'
		        }
		        ]),
        autoSizeColumns: true,
        trackMouseOver:true,
        loadMask: true,
        stripeRows: true,
        viewConfig: {
            forceFit:true,
            stripeRows: true,
            getRowClass : function(record, rowIndex, p, store){
              //return 'red-row';
            }
        }
    });
    

   function limitFilter () {
	    var status = Ext.getCmp("status");
	    grid.store.baseParams["limit"] = status.getValue();
	    pager.pageSize = status.getValue();
	    grid.store.lastOptions.params["start"] = 0;
	    grid.store.reload();
	}
	
	
	
	
	
	function searchFilter () {
	    var search = Ext.getCmp("search");
	    store.baseParams["search"] = search.getValue();
	    store.lastOptions.params["start"] = 0;
	    store.reload();
	}	
	store.load({params:{start:0, limit:25}});
	storeBasket.load({params:{start:0, limit:25}});
	storeArticles.load({params:{start:0, limit:25}});
	storePages.load({params:{start:0, limit:25}});
	store.paymentArray = paymentArray;

	
	store.on('load',function(x,y,z){
		// Reload store
		var data = x.reader.jsonData;
		// => Refresh statistics
    	var amountData = [
		    ['<?php echo $sLang["orderscanceled"]["orders_exchange"] ?>',data.totalAmount],
		    ['<?php echo $sLang["orderscanceled"]["orders_orders"] ?>',data.totalCount]
    	];
    	
    	for (var item in data){
    		if (item.match(/payment/)){
    			var paymentID = item.replace(/payment/,"");
    			amountData[amountData.length] = ['Per '+x.paymentArray[paymentID],data[item]];
    		}
    		
    	}

    	
    	
    	
	   	storeAmount.loadData(amountData);
	});
	
	
    var dr = new Ext.FormPanel({
      labelWidth: 80,
      frame: true,
      title: '<?php echo $sLang["orderscanceled"]["orders_filter"] ?>',
	  bodyStyle:'padding:5px 5px 0',
	  width: 230,
	  region: 'west',
	  split:true,
	  collapsible: true,
      defaults: {width: 120},
      defaultType: 'datefield',
      items: [{
        fieldLabel: '<?php echo $sLang["orderscanceled"]["orders_from"] ?>',
        name: 'startdt',
        id: 'startdt',
        format: 'd.m.Y',
        value: '<?php echo date("d.m.Y",mktime(0,0,0,date("m"),date("d")-7,date("Y"))) ?>',
        endDateField: 'enddt' // id of the end date field
      },{
        fieldLabel: '<?php echo $sLang["orderscanceled"]["orders_until"] ?>',
        name: 'enddt',
        id: 'enddt',
        format: 'd.m.Y',
        value: '<?php echo date("d.m.Y") ?>',
        startDateField: 'startdt' // id of the start date field
      },
     new Ext.form.ComboBox({
      		fieldLabel: '<?php echo $sLang["orderscanceled"]["orders_method_of_payment"] ?>',
		    store: paymentstore,
		    displayField:'state',
		    valueField:'id',
		    typeAhead: true,
		    mode: 'local',
		    id: 'filterpayment',
		    triggerAction: 'all',
		    emptyText:'<?php echo $sLang["orderscanceled"]["orders_please_select"] ?>',
		    selectOnFocus:true,
		    value:-1
		}),
		new Ext.Button  ( {
	    	text: '<?php echo $sLang["orderscanceled"]["orders_filter"] ?>',
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
	    
	   
	    var payment = Ext.getCmp("filterpayment");
	    payment = payment.getValue();
	    
	    // Reload Grid
	    store.baseParams["startDate"] = startDate;
	    store.baseParams["endDate"] = endDate;
	    // Basket
	    storeBasket.baseParams["startDate"] = startDate;
	    storeBasket.baseParams["endDate"] = endDate;
 		storeArticles.baseParams["startDate"] = startDate;
	    storeArticles.baseParams["endDate"] = endDate;
	    storePages.baseParams["startDate"] = startDate;
	    storePages.baseParams["endDate"] = endDate;
	    
	    store.baseParams["filterPayment"] = payment;
	    
	    store.lastOptions.params["start"] = 0;
	    store.reload();
	    
	    storeBasket.lastOptions.params["start"] = 0;
	    storeBasket.reload();

	    storeArticles.lastOptions.params["start"] = 0;
	    storeArticles.reload();
	    
	    storePages.lastOptions.params["start"] = 0;
	    storePages.reload();
	    
    }
    var storeAmount = new Ext.data.SimpleStore({
        fields: [
           {name: 'description'},
           {name: 'value', type: 'float'}
        ]
    });
    var amountData = [
    ['<?php echo $sLang["orderscanceled"]["orders_exchange"] ?>',0],
    ['<?php echo $sLang["orderscanceled"]["orders_orders"] ?>',0],
    ['<?php echo $sLang["orderscanceled"]["orders_new_customer"] ?>',0],
    ['<?php echo $sLang["orderscanceled"]["orders_Visitor"] ?>',0],
    ['<?php echo $sLang["orderscanceled"]["orders_Impressions"] ?>',0]
    ];
    storeAmount.loadData(amountData);

	var statisticGrid = new Ext.grid.GridPanel({
        store: storeAmount,
        columns: [ 
            {id:'company',header: "<?php echo $sLang["orderscanceled"]["orders_description"] ?>", width: 160, sortable: true, dataIndex: 'description', hidden: false},
            {header: "<?php echo $sLang["orderscanceled"]["orders_worth_1"] ?>", width: 75, sortable: true, dataIndex: 'value', hidden: false}
        ],
        stripeRows: true,
		autoScroll: true,
        autoExpandColumn: 'company',
        height:450,
        title:'<?php echo $sLang["orderscanceled"]["orders_statistics"] ?>',
        viewConfig: {
            forceFit:true
        }
    });
	   
   var leftPanel = 
   	new Ext.TabPanel({
		split:true,
		minSize: 100,
		frame: true,
		width: 230,
		collapsible: true,
		region: 'west',
		margins:'0 0 0 0',
		activeTab:0,
		items:[dr,statisticGrid]
	});
	
   basketTab = new Ext.TabPanel({
            region:'center',
            deferredRender:false,
            activeTab:0,
            closeable:true,
            title:'<?php echo $sLang["orderscanceled"]["orders_abandoned_shopping_carts_1"] ?>',
            items:[gridBasket, gridArticles,gridPages]
   });
   myTab = new Ext.TabPanel({
            region:'center',
            deferredRender:false,
            activeTab:0,
            closeable:true,
            items:[grid, basketTab]
   });
    	
   var viewport = new Ext.Viewport({
        layout:'border',
        items:[
            leftPanel,myTab
         ]
    });
    
       
         
}};
}();
	Ext.grid.RowExpander = function(config){
		Ext.apply(this, config);
	
		this.addEvents({
			beforeexpand : true,
			expand: true,
			beforecollapse: true,
			collapse: true
		});
	
		Ext.grid.RowExpander.superclass.constructor.call(this);
	
		if(this.tpl){
			if(typeof this.tpl == 'string'){
				this.tpl = new Ext.Template(this.tpl);
			}
			this.tpl.compile();
		}
	
		this.state = {};
		this.bodyContent = {};
	};

	Ext.extend(Ext.grid.RowExpander, Ext.util.Observable, {
	    header: "",
	    width: 20,
	    sortable: false,
	    fixed:true,
	    menuDisabled:true,
	    dataIndex: '',
	    id: 'expander',
	    lazyRender : true,
	    enableCaching: true,
	
	    getRowClass : function(record, rowIndex, p, ds){
	        p.cols = p.cols-1;
	        var content = this.bodyContent[record.id];
	        if(!content && !this.lazyRender){
	            content = this.getBodyContent(record, rowIndex);
	        }
	        if(content){
	            p.body = content;
	        }
	        return this.state[record.id] ? 'x-grid3-row-expanded' : 'x-grid3-row-collapsed';
	    },
	
	    init : function(grid){
	        this.grid = grid;
	
	        var view = grid.getView();
	        view.getRowClass = this.getRowClass.createDelegate(this);
	
	        view.enableRowBody = true;
	
	        grid.on('render', function(){
	            view.mainBody.on('mousedown', this.onMouseDown, this);
	        }, this);
	    },
	
	    getBodyContent : function(record, index){
	        if(!this.enableCaching){
	            return this.tpl.apply(record.data);
	        }
	        var content = this.bodyContent[record.id];
	        if(!content){
	            content = this.tpl.apply(record.data);
	            this.bodyContent[record.id] = content;
	        }
	        return content;
	    },
	
	    onMouseDown : function(e, t){
	        if(t.className == 'x-grid3-row-expander'){
	            e.stopEvent();
	            var row = e.getTarget('.x-grid3-row');
	            this.toggleRow(row);
	        }
	    },
	
	    renderer : function(v, p, record){
	        p.cellAttr = 'rowspan="2"';
	        return '<div class="x-grid3-row-expander">&#160;</div>';
	    },
	
	    beforeExpand : function(record, body, rowIndex){
	        if(this.fireEvent('beforeexpand', this, record, body, rowIndex) !== false){
	            if(this.tpl && this.lazyRender){
	                body.innerHTML = this.getBodyContent(record, rowIndex);
	            }
	            return true;
	        }else{
	            return false;
	        }
	    },
	
	    toggleRow : function(row){
	        if(typeof row == 'number'){
	            row = this.grid.view.getRow(row);
	        }
	        this[Ext.fly(row).hasClass('x-grid3-row-collapsed') ? 'expandRow' : 'collapseRow'](row);
	    },
	
	    expandRow : function(row){
	        if(typeof row == 'number'){
	            row = this.grid.view.getRow(row);
	        }
	        var record = this.grid.store.getAt(row.rowIndex);
	        var body = Ext.DomQuery.selectNode('tr:nth(2) div.x-grid3-row-body', row);
	        if(this.beforeExpand(record, body, row.rowIndex)){
	            this.state[record.id] = true;
	            Ext.fly(row).replaceClass('x-grid3-row-collapsed', 'x-grid3-row-expanded');
	            this.fireEvent('expand', this, record, body, row.rowIndex);
	        }
	    },
	
	    collapseRow : function(row){
	        if(typeof row == 'number'){
	            row = this.grid.view.getRow(row);
	        }
	        var record = this.grid.store.getAt(row.rowIndex);
	        var body = Ext.fly(row).child('tr:nth(1) div.x-grid3-row-body', true);
	        if(this.fireEvent('beforcollapse', this, record, body, row.rowIndex) !== false){
	            this.state[record.id] = false;
	            Ext.fly(row).replaceClass('x-grid3-row-expanded', 'x-grid3-row-collapsed');
	            this.fireEvent('collapse', this, record, body, row.rowIndex);
	        }
	    }
	});
    Ext.onReady(function(){
    	myExt.init();
    	
    });
	</script>
</head>
<body>
</body>
</html>


