<script>
var myExt = function(){
		var store;
		var storeOnline;
		var imStore;
		var storeid;
		var myTab;
		var bottomOperations;
		var clientlist;
		var xStore;
		var parentPanel;
		var dashboard;
		var currentonline;
		var ticketoverview;
		var sBuyRent; var sBuyFrame;
	return {
		sShowBuy: function(mode,modul){
			var url = "https://support.shopware2.de/oopaccount/modules/modul/buy.php?domain={$this->config('Host')}&pairing={$this->config('AccountId')}&module="+modul+"&mode="+mode;
			Ext.getCmp('sBuyFrame').url = url;
			this.sBuyRent.show();
			$('framepanelsBuyFrame').src = url;
		},
		displayLicense : function(){
			{if !$PremiumLicence}
		   		licence_win = new Ext.Window({
			      width:600,
			      height:400,
			      title:"Nicht lizenziert",
			      autoScroll:true,
			      bodyStyle:'padding:20px',
			      modal:true,
			      html: $('licence').get('html')
			   	});
			   	licence_win.show();
			{/if}
		},
		resizeWindowEvent : function(){
			var w = window.getSize().x - 210;
    		Ext.get('windowTracker').setWidth(w);
    		var tmpobj = Ext.getCmp('myTPanel').add({});
    		  Ext.getCmp('myTPanel').remove(tmpobj); 		
    		
		},
		resign : function (){
			parentPanel.setHeight(window.getSize().y);
		},
		openTicket : function(ticketID)
		{
			parent.parent.parent.loadSkeleton('ticket_details',false, { 'ticketID':ticketID});
		},
		reload : function(){
			
			try {
				store.reload();
				Ext.get('dashboard').fadeIn();
			} catch (e){
				
			}
			try {				
				Ext.Ajax.request({
				   url: '{$Scheme}://{$this->config('BasePath')}/engine/backend/ajax/getTicketStore.php',
				   success: function(conn, options){
				   		var json = Ext.decode(conn.responseText);
				   		var total = json.total;
				   		if(total == '0')
				   		{
				   			var text = "keine offenen Tickets";
				   		}else if(total == '1')
				   		{
				   			var text = "1 offenes Ticket";
				   		}else{
				   			var text = total+" offene Tickets";
				   		}
				   		$('ticketCountText').innerHTML = text;
				   },
				   params: { dir:'DESC', filter_status:'1', sort:'receipt', start:'0', limit:'50'}
				});
				Ext.get('ticketpanel').fadeIn();

			} catch (e){
				
			}
			try {
				storeOnline.reload();
				Ext.get('currentonline').fadeIn();
			} catch (e){
				
			}
			try {
				imStore.reload();
			} catch(e) {
				
			}
		},
		deleteRow: function(id, subject){
			Ext.MessageBox.confirm('Nachricht löschen', 'Soll die Nachricht mit dem Betreff "'+subject+'" wirklich gelöscht werden?', function(btn){
			if(btn == 'yes')
			{
				Ext.Ajax.request({
				   url: '{$Scheme}://{$this->config('BasePath')}/engine/backend/ajax/deleteIm.php',
				   success: function(){
				   		parent.Growl('Nachricht '+id+' wurde gelöscht!');
				   		myExt.reload();
				   },
				   params: { 'delete': id }
				});
			}else{
				parent.Growl('Löschen abgebrochen');
			}
		});
		}
		,
		init : function(){
		    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
			 Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
		     onRender : function(ct, position){
		          this.el = ct.createChild( { tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
		     }
			}); 
		    function change(val){
		        if(val > 0){
		            return '<span style="color:green;">' + val + '</span>';
		        }else if(val < 0){
		            return '<span style="color:red;">' + val + '</span>';
		        }
		        return val;
		    }
		    function pctChange(val){
		        if(val > 0){
		            return '<span style="color:green;">' + val + '%</span>';
		        }else if(val < 0){
		            return '<span style="color:red;">' + val + '%</span>';
		        }
		        return val;
		    }
		    
		store = new Ext.data.Store({
	        url: '{$Scheme}://{$this->config('BasePath')}/engine/backend/ajax/dashboard.php',
	        baseParams: { pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({ 
	            root: 'dashboard',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'desc','today','yesterday','link'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
    	
    
		
		
		imStore = new Ext.data.Store({
	        url: '{$Scheme}://{$this->config('BasePath')}/engine/backend/ajax/getIm.php',
	        baseParams: { pagingID:storeid},
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'dashboard',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'from','date','text','id','subject','status','notRead'
	            ]
	        }),
			listeners: {
	        	'load' : { fn: function(){
	        		if(imStore.data.items[0].data.notRead){
	        			// Open items
	        			$('imHandler').removeClass('ballons');
	        			$('imHandler').removeClass('balloons_arrow');
	        			$('imHandler').addClass('balloons_arrow');
	        			
	        		}else {
	        			// no open items
	        			$('imHandler').removeClass('ballons');
	        			$('imHandler').removeClass('balloons_arrow');
	        			$('imHandler').addClass('balloons');
	        		}
	        	}}
        	},
	        // turn on remote sorting
	        remoteSort: true
    	});
		
		
		storeOnline = new Ext.data.Store({
	        url: '{$Scheme}://{$this->config('BasePath')}/engine/backend/ajax/dashboard_online.php',
	        baseParams: { pagingID:storeid},
	         
	        // create reader that reads the Topic records
	        reader: new Ext.data.JsonReader({
	            root: 'dashboard',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'customer','amount'
	            ]
	        }),
	
	        // turn on remote sorting
	        remoteSort: true
    	});
	
		
		function renderColumn(val,k,p){
			if (p.data.link){
				return "<span style=\"cursor:pointer;color:red\" onclick=\""+p.data.link+"\">"+val+"</span>";
			}else {
	        	return val;
			}
	    }
		dashboard = new Ext.grid.GridPanel({
			store: store,
	        columns: [ 
	            { id:'month',header: "Vergleich", width: 160, sortable: false, dataIndex: 'desc', hidden: false,renderer:renderColumn},
	            { header: "Heute", width: 90, sortable: false, renderer: change, dataIndex: 'today', hidden: false},
	            { id:'yesterday',header: "Gestern", width: 100, sortable: false, renderer: change, dataIndex: 'yesterday', hidden: false}
	        ],
	        stripeRows: true,
	        autoExpandColumn: 'yesterday',
	        padding: '0 0 0 0',
	        id: 'dashboard',
	        autoHeight: true,
	        collapsible: true,
	        title:'Umsatz',
	        viewConfig: {
	            forceFit:true
	        }
	    });
	     var pager = new Ext.PagingToolbar({
            pageSize: 25,
            store: imStore,
            displayInfo: true,
            displayMsg: '{0} - {1} von {2}',
            emptyMsg: "Keine Einträge",
            items:[
            '-'
        	,
            'Suche',
            {
            	xtype: 'textfield',
            	id: 'search',
            	selectOnFocus: true,
            	width: 120,
            	listeners: {
	            	'render': { fn:function(ob){
	            		ob.el.on('keyup', searchFilter, this, { buffer:500});
	            	}, scope:this}
            	}
            }]
        });
        
       function searchFilter(){
       	var search = Ext.getCmp("search");
	    imStore.baseParams["search"] = search.getValue();
	    imStore.lastOptions.params["start"] = 0;
	    imStore.reload(); 	
       }
        
       
	   var toolbarButton =new Ext.Toolbar.Button  ( {
	                text: 'Nachricht hinzufügen',
	                tooltip: 'Klicken Sie hier um eine Nachricht hinzuzufügen',
					cls:'ico add',
					disabled: false,
	                handler: addRecord
	    });
	    
	    
	    
        var expander = new Ext.grid.RowExpander({
        	{literal}
    		tpl : new Ext.Template(
            '<p><a style="cursor:pointer" onclick="myExt.deleteRow({id},\'{subject}\')" class="ico3 delete">Nachricht löschen</a><br /><b>Nachricht:</b> {text}</p><br>'),
            {/literal}
            listeners: {
	         	'beforeexpand' : { fn: function(expander, record, body, rowIndex){
					
					Ext.Ajax.request({
					   url: '{$Scheme}://{$this->config('BasePath')}/engine/backend/ajax/refreshIm.php',
					   success: function(){
					   		
					   },
					   params: { 'id': record.data.id }
					});
				}, scope:this},
	         	'beforecollapse' : { fn: function(expander, record, body, rowIndex){
	
	         	}, scope:this}
       		}	
        });
        function renderSubject(v,p,row){
        	
        	if (row.data.status){
        		return v;
        	}else {
        		return "<span style=\"font-weight:bold\">"+v+"</span>";
        	}
        }
	    imGrid = new Ext.grid.GridPanel({
			store: imStore,
	        columns: [
	        expander,
	            { id:'imFrom',header: "Von", width: 160, sortable: false, dataIndex: 'from', hidden: false},
	            { id:'imFrom2',header: "Wann", width: 160, sortable: false, dataIndex: 'date', hidden: false},
	            { id:'imFrom3',header: "Betreff", width: 160, sortable: false, dataIndex: 'subject', hidden: false,renderer:renderSubject}
	        ],
	        stripeRows: true,
	        padding: '0 0 0 0',
	        id: 'imGrid',
	        renderTo:'im',
	        width:510,
	        plugins:[expander],
	        height:400,

	        bbar: pager,
	        tbar: [
	           toolbarButton
	        ]
	    });
		    
	    currentonline = new Ext.grid.GridPanel({
			store: storeOnline,
	        columns: [ 
	            { header: "Kunde", width: 160, sortable: false, dataIndex: 'customer', hidden: false},
	            { id:'basket',header: "Warenkorb", width: 90, sortable: false, renderer: change, dataIndex: 'amount', hidden: false}
	        ],
	        stripeRows: true,

	        padding: '0 0 0 0',
	        id: 'currentonline',
	        autoHeight: true,
	        collapsible: true,
	        title:'Aktuell Online',
	        viewConfig: {
	        	stripeRows: true,
	            forceFit:true

	        }
	    });
		    
	    {if $TicketSystemActive}
	    	Ext.get('ticketCount-div').setStyle('display','block');
		    var ticketCount = new Ext.BoxComponent({
			    el: 'ticketCount-div'
			});
			var ticketCountPanel = new Ext.Panel({
				id: 'ticketpanel',
		    	padding: 5,
				title: 'Ticket System',
				items: [ticketCount]
			});
		{else}
			Ext.get('ticketCount-div').setStyle('display','none');
		{/if}
		myExt.reload();
	    var userForm = [[-1,'An alle'],{$BackendUsers}];
	    
	    // trigger the data store load
	    var userStore = new Ext.data.SimpleStore({
	 	    fields: ['id', 'user'],
		    data : userForm
		});
	     var form1234 = new Ext.form.FormPanel({
	        baseCls: 'x-plain',
	        id: 'form1234',
	       
	        labelWidth: 55,
	        url:'{$Scheme}://{$this->config('BasePath')}/engine/backend/ajax/saveIm.php',
	        defaultType: 'textfield',
	        items: [
	        new Ext.form.ComboBox({
		      	fieldLabel: 'An',
		      	id: 'filteruser',
		      	name:'user',
		      	hiddenName:'user',
		      	layout: 'form',
		      	store:userStore,
		      	valueField:'id',
		      	displayField:'user',
		      	allowBlank:true,
		      	editable:false,
		      	forceSelection : true,
		      	shadow:false,
		      	mode: 'local',
		      	triggerAction:'all',
		      	maxHeight: 200,
		      	lazyInit: false,
                emptyText:'An alle'

 		 	}),
	        {
	            fieldLabel: 'Betreff',
	            name: 'subject',
	            allowBlank:false,
	            anchor: '100%'  // anchor width by percentage
	        }, {
	            xtype: 'textarea',
	            fieldLabel: 'Text',
                allowBlank:false,
	            name: 'msg',
	            anchor: '100%'  // anchor width by percentage and height by raw adjustment
	        }, {
	            xtype: 'textarea',
	            fieldLabel: 'Info',
                disabled: true,{literal}
                value: "Erlaubte Tags\nÖffnen von Artikeln: {'article',Artikelnummer}\nÖffnen von Bestellungen: {'order',Bestellnummer}\nÖffnen von Kundenkonten: {'userdetails',Kundennummer}",
	            name: 'msgInfo',{/literal}
	            height: 180,
	            anchor: '100% 280'  // anchor width by percentage and height by raw adjustment
	        }
        	],
	        buttons: [{
	            text: 'Speichern',handler: saveRow
	        },{
	            text: 'Abbrechen',handler: abortRow
	        }]
    	});
		var nav = new Ext.Panel({
            title       : 'Navigation',
            region      : 'south',
            split       : false,
            width       : 200,
            height		: 450,
            collapsible : false,
            margins     : '3 0 3 3',
            cmargins    : '3 3 3 3',
            html: ' '
        }); 
        myExt.sBuyFrame = new Ext.ux.IFrameComponent({ 
			region:'center',
			split:true,
			animate:true, 
			fitToFrame: true,
			title:'Einstellungen',
			width:700,
	        height:500,
			collapsible: true,
			id: "sBuyFrame"
			});
		myExt.sBuyRent = new Ext.Window({
	        title: 'Modul kaufen',
	        width: 500,
	        id: 'window1234NN',
	        closeAction: 'hide',
	        height:350,
	        minWidth: 300,
	        minHeight: 200,
	        layout: 'fit',
	        hidden:true,
	        plain:true,
	        bodyStyle:'padding:5px;',
	        buttonAlign:'center',
	        items: [myExt.sBuyFrame]
	    });
	    var window1234 = new Ext.Window({
	        title: 'Nachricht hinzufügen',
	        width: 500,
	        id: 'window1234',
	        closeAction: 'hide',
	        height:350,
	        minWidth: 300,
	        minHeight: 200,
	        layout: 'fit',
	        hidden:true,
	        plain:true,
	        bodyStyle:'padding:5px;',
	        buttonAlign:'center',
	        items: [form1234]
	    });
	    function abortRow(el){
	    	Ext.get('window1234').hide();
	    }
	    function addRecord(){
	    	window1234.setVisible(true);
	    	window1234.setPosition(600,300);
	    	window1234.el.setStyle('visibility','');
	    	window1234.show();
	    }
	    function saveRow(el){
	    	
			el.ownerCt.ownerCt.getForm().submit(
			{
				url:this.url
				,scope:this
				,success: function (f) {
                	myExt.reload();
					Ext.get('window1234').hide();
            	},
				params:{ cmd:'save'}
				,waitMsg:'Saving...'
			}
			);  	
	    }
		
	    {if $rssData}
	    	var rssData = {$rssData};
	    {else}
	   		var rssData = [[1,'Keine Einträge vorhanden','http://www.shopware-ag.de']];
	    {/if}
	    
	    var rssStore = new Ext.data.SimpleStore({
	 	    fields: ['id', 'text','link'],
		    data : rssData
		});
		
		function renderLink(val){
	        return "<span style=\"cursor:pointer\">"+val+"</span>";
	    }
	    
	    function renderIcon(val){
	    	return "<p style=\"background-image: url( {link file="engine/backend/img/default/rss/logo.png"}); background-repeat: no-repeat; background-position: 0pt 0.4em; height:16px; width:16px\"></p>";
	    }
		
	    rssfeed = new Ext.grid.GridPanel({
			store: rssStore,
	        columns: [ 
	            { width: 15, sortable: false, dataIndex: 'id', hidden: false,renderer: renderIcon},
	            { id:'rsshead',header: "Titel", width: 125, sortable: false, renderer: renderLink, dataIndex: 'text', hidden: false}
	        ],
	        stripeRows: true,
	        autoExpandColumn: 'rsshead',
	        padding: '0 0 0 0',
	        id: 'rssfeed',
	        autoHeight: true,
	        collapsible: true,
	        title:'Aktuelles',
	        viewConfig: {
	        	stripeRows: true,
	            forceFit:true
	        }
	    });
	    
	    rssfeed.on('rowclick',function(grid,number){
	    	loadSkeleton('rss',1,{ 'link':grid.store.data.items[number].data.link});
	    });
	   
	    
	    account = new Ext.Panel({
	        padding: '0 0 0 0',
	        id: 'accountpanel',
	        autoHeight: true,
	        collapsible: true,
	        title:'Shopware Account',
	        contentEl: 'saccount',
	        viewConfig: {
	            forceFit:true
	        },
	        buttons: [
	        	new Ext.Button  ( {
			    	text: 'Account öffnen',
			    	handler: openAccount
	        	})
	        ]
	    });
		    
	    
	    
	    parentPanel = new Ext.Panel({
					title: 'Dashboard',
					deferredRender: false,
					width: 200,
					autoHeight:false,
					tools: [{ id:'refresh',
					on:{
						click: function(){
							myExt.reload();
						}
					}}
					],
					collapsible: false,
					region: 'east',
					items: [
						dashboard,
						currentonline,
						{if $TicketSystemActive}ticketCountPanel,{/if}
						account,
						rssfeed
						]
					
				});
		parentPanel.on('resize',function(e){
			parentPanel.setHeight(window.getSize().y);
	    });
	    
      	var viewport = new Ext.Viewport({ 
            layout:'border',
           
            items:[
           	 	new Ext.BoxComponent({ 
	            	region: 'north',
	            	el: 'north',
	            	height: 45
	            }),
	            new Ext.BoxComponent({ 
	            	region: 'south',
	            	el: 'body',
	            	height: 25
	            }),
	            new Ext.BoxComponent({ 
	            	region: 'center',
	            	el: 'body'
	            }),
				{if $SidebarActive}parentPanel{else} 
				new Ext.BoxComponent({ 
	            	region: 'left',
	            	el: 'body'
	            }){/if}
             ]
        }); 
      }};
     

}();
function openAccount(){
	//loadSkeleton('account');
	var url = '{$accountUrl}';
	window.open(url,'Shopware');
}
Ext.onReady(function(){
	Ext.QuickTips.init();
	// Init Tab
	
	var w = window.getSize().x - 250;
	Ext.get('windowTracker').setWidth(w);
	
	var scrollerMenu = new Ext.ux.TabScrollerMenu({
		maxText  : 15,
		pageSize : 5
	});
	
	var myTab = new Ext.TabPanel({
		activeTab       : 0,
		id              : 'myTPanel',
		enableTabScroll : true,
		resizeTabs      : true,
		minTabWidth     : 140,
		frame			: false,
		defaults: { autoScroll:true},
		border          : false,
		autoWidth		: true,
		footer			: false,
		plain			: true,
		plugins         : [ scrollerMenu ],
		renderTo: 'windowTracker'
	});
	
	
	myTab.on('tabchange',function (panel,tab){
		
		tab.window.focus();
	
	});
	myTab.on('beforeremove',function (tab,comp){
		try {
			comp.window.close();
		} catch (e){
			
		}
	});
	
	myExt.init();
},myExt,true);
</script>