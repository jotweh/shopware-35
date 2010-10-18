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
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Reorder TreePanel</title>
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
var store = new Ext.data.Store({
	url: 'getUsers.php',
	reader: new Ext.data.JsonReader({
		root: 'users',
		totalProperty: 'count',
		id: 'userID',
		fields: [
			'userID','customernumber','name','lastname','email','adress','risk_result',{name: 'risk_date', type: 'date', dateFormat: 'timestamp'}
		]
	}),
	remoteSort: true
});
function renderOptions(value, p, r){
	return String.format(
		'<a class="ico pencil" style="cursor:pointer" onclick="parent.loadSkeleton({0},false,{1})"></a><a class="ico bulb_off" style="cursor:pointer" onclick="doRating('+r.data.userID+',unescape(\''+escape(r.data.lastname)+'\'));"></a> ',"'userdetails'","{'user':"+r.data.userID+"}"
	);
}
function renderRating(value, p, r){
	var color;
	switch(value)
	{
		case "GRUEN":
			color = 'green';
			break;
		case "GELB":
			color = 'yellow';
			break;
		case "ROT":
			color = 'red';
			break;
		case "WHITE":
			color = '#fff';
			break;
		default:
			return;
	}
	return String.format(
		'<div style="width:10px;height:10px;float:left;margin:2px 4px 0;border: 1px solid #000;background-color:'+color+'"></div> '+value
	);
}
function searchFilter () {
	var search = Ext.getCmp("search");
	store.baseParams["search"] = search.getValue();
	//store.lastOptions.params["start"] = 0;
	store.reload();
}
function doRating(userID,user)
{	
	Ext.MessageBox.show({
		title: 'Bonit&auml;tsüberpr&uuml;fung',
		msg: 'Die Bonit&auml;tsüberpr&uuml;fung des Kunden "'+user+'" durchf&uuml;hren?',
		buttons: Ext.MessageBox.YESNOCANCEL,
		fn: function(btn, text){
			if(btn=="yes")
			{
				Ext.MessageBox.wait("","Bitte warten..."); 
				Ext.Ajax.request({
					url:'doRating.php',
					params : {sUserID : userID},
					method: 'POST',
					success: function ( result, request ) {
						if(result.responseText)
						{
							result = Ext.util.JSON.decode(result.responseText);
							if(result&&result.sErrorMessage)
							{
								Ext.Msg.alert("Fehler", result.sErrorMessage);
							}
							else
							{
								Ext.MessageBox.alert("Erfolgreich", "Die Bonit&auml;tsüberpr&uuml;fung wurde erfolgreich durchgef&uuml;hrt!");
							}
						}
						store.reload();
					}
				});
			}
		}
	});
}


var top = new Ext.Panel({
	region: 'north',
	margins:'0 0 0 0',
	height: 100,
	bbar: ['Suche:&nbsp;&nbsp;',{
		xtype: 'textfield',
		id: 'search',
		selectOnFocus: true,
		width: 120,
		listeners: {
			'render': {fn:function(ob){
				ob.el.on('keyup', searchFilter, this, {buffer:500});
			}, scope:this}
		}
	}],
	html: "<ul style=\"padding:10px;font-family:arial,tahoma,helvetica,sans-serif;font-size:11px;font-size-adjust:none;font-style:normal;font-variant:normal;font-weight:normal;line-height:normal;\">"+
		 "<li style=\"clear:both;\"><div style=\"width:60px;float:left\"><div style=\"margin:2px 4px 0;width:10px;height:10px;border: 1px solid #000;background-color:green;float:left\"></div>GRUEN</div> Keine Negativmerkmale vorhanden / Niedriger Risikobereich</li>"+
		 "<li style=\"clear:both;\"><div style=\"width:60px;float:left\"><div style=\"margin:2px 4px 0;width:10px;height:10px;border: 1px solid #000;background-color:yellow;float:left\"></div>GELB</div> Keine Negativmerkmale vorhanden / Mittlerer Risikobereich</li>"+
		 "<li style=\"clear:both;\"><div style=\"width:60px;float:left\"><div style=\"margin:2px 4px 0;width:10px;height:10px;border: 1px solid #000;background-color:red;float:left\"></div>ROT</div> Negativmerkmale vorhanden / Hoher Risikobereich</li>"+
		 "<li style=\"clear:both;\"><div style=\"width:60px;float:left\"><div style=\"margin:2px 4px 0;width:10px;height:10px;border: 1px solid #000;background-color:#fff;float:left\"></div>WHITE</div> keine Bewertung möglich, zu wenig Daten bzw. keine eindeutige Übereinstimmung</li>"+
	 "</ul>"
});

var pager = new Ext.PagingToolbar({
	pageSize: 25,
	store: store,
	displayInfo: true,
	displayMsg: 'Kunden: {0} - {1} Gesamt: {2}',
	emptyMsg: "Keine Kunden in Ansicht"
});


var grid = new Ext.grid.GridPanel({
	region:'center',
	//title:'Kundenübersicht',
	store: store,
	height: '100%',
	columns: [
		{header: "Kundennummer", width: 75, sortable: true, dataIndex: 'customernumber'},
		{header: "Kunde", width: 75, sortable: true, dataIndex: 'name'},
		{header: "Adresse", width: 160, sortable: true, dataIndex: 'adress'},
		{header: "Ergebnis der Überprüfung", width: 75, sortable: true, dataIndex: 'risk_result', renderer: renderRating},
		{header: "Letzte Überprüfung", width: 75, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), dataIndex: 'risk_date'},
		{header: "Optionen", width: 40, sortable: true, dataIndex: 'options', renderer: renderOptions}
	],
	viewConfig: {
		forceFit:true
	},
    bbar: pager
});

var bottom = new Ext.Panel({
	region: 'south',
	margins:'0 0 0 0',
	height: 50,
	
});

grid.store.load();
 var viewport = new Ext.Viewport({
 	items: [top, grid, bottom]
 });
</script>
</head>
<body>
</body>
</html>