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

if(!empty($_REQUEST["delete"]))
{
	$delete = intval($_REQUEST["delete"]);
	switch ($_REQUEST["get"])
	{
		case "fields":
			$sql = "DELETE FROM s_search_fields WHERE id = $delete LIMIT 1;";
			break;
		case "tables":
			$sql = "DELETE FROM s_search_tables WHERE id = $delete LIMIT 1;";
			break;
		default:
			exit();
	}
	mysql_query($sql);
}
if(!empty($_REQUEST["set"]))
{
	switch ($_REQUEST["set"])
	{
		case "fields":
			$fieldID = intval($_REQUEST["fieldID"]);
			$tableID = intval($_REQUEST["tableID"]);
			$relevance = intval($_REQUEST["relevance"]);
			$name = mysql_real_escape_string(trim(utf8_decode($_REQUEST["name"])));
			$field = mysql_real_escape_string(trim(utf8_decode($_REQUEST["field"])));
			if(empty($fieldID))
			{
				$sql = "
					INSERT INTO `s_search_fields` (`name`, `relevance`, `field`, `tableID`)
					VALUES ('$name', $relevance, '$field', $tableID);
				";
			}
			else
			{
				$sql = "
					UPDATE s_search_fields
					SET name='$name', relevance='$relevance', field='$field', tableID='$tableID'
					WHERE id=$fieldID
				";
			}
			break;
		case "tables":
			$tableID = intval($_REQUEST["tableID"]);
			$table = mysql_real_escape_string(trim(utf8_decode($_REQUEST["table"])));
			$referenz_table = mysql_real_escape_string(trim(utf8_decode($_REQUEST["referenz_table"])));
			$referenz_table = empty($referenz_table) ? 'NULL' : "'$referenz_table'";
			$foreign_key = mysql_real_escape_string(trim(utf8_decode($_REQUEST["foreign_key"])));
			$foreign_key = empty($foreign_key) ? 'NULL' : "'$foreign_key'";
			$where = mysql_real_escape_string(trim(utf8_decode($_REQUEST["where"])));
			$where = empty($where) ? 'NULL' : "'$where'";
			if(empty($tableID))
			{
				$sql = "
					INSERT INTO `s_search_tables` (`table`, `referenz_table`, `foreign_key`, `where`)
					VALUES ('$table', $referenz_table, $foreign_key, $where);
				";
			}
			else
			{
				$sql = "
					UPDATE s_search_tables
					SET `table`='$table', referenz_table=$referenz_table, foreign_key=$foreign_key, `where`=$where
					WHERE id=$tableID
				";
			}
			echo $sql;
			break;
		case "config":
			$name = mysql_real_escape_string($_REQUEST["name"]);
			$value = mysql_real_escape_string($_REQUEST["value"]);
			$sql = "
				UPDATE s_core_config SET value='$value' WHERE name='$name'
			";
			break;
		default:
			exit();
	}
	mysql_query($sql);
	exit();
}
if(!empty($_REQUEST["get"]))
{
	require_once("../../../backend/ajax/json.php");
	$json = new Services_JSON();
	switch ($_REQUEST["get"])
	{
		case "fields":
			$sql = "
				SELECT f.id as fieldID, f.name, f.relevance, f.field, f.tableID, t.table
				FROM s_search_fields f, s_search_tables t
				WHERE t.id=f.tableID
				ORDER BY tableID, name
			";
			break;
		case "tables":
			$sql = "
				SELECT id as tableID, `table`, referenz_table, foreign_key, `where`
				FROM s_search_tables
				ORDER BY `table`
			";
			break;
		case "config":
			$sql = "
				SELECT c.name, c.value, c.description
				FROM s_core_config c, s_core_config_groups g
				WHERE c.group = g.id
				AND g.name = 'Intelligente Suche'
			";
			break;
		default:
			exit();
	}
	$result = mysql_query($sql);
	$nodes = array();
	if ($result&&mysql_num_rows($result))
	while ($row = mysql_fetch_assoc($result))
	{
		if(isset($row["description"]))
			$row["description"] = utf8_encode($row["description"]);
		if(isset($row["name"]))
			$row["name"] = utf8_encode($row["name"]);
		if(isset($row["value"]))
			$row["value"] = utf8_encode($row["value"]);
		if(!empty($row["count"]))
			$row["name"] .= " (".$row["count"].")";
		$nodes[] = $row;
	}
	echo $json->encode(array("articles"=>$nodes,"count"=>count($nodes)));
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>
  <meta http-equiv="Content-Language" content="de" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title></title>
	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="../../../vendor/ext/resources/css/ext-all.css" rel="stylesheet" type="text/css" />
	<style>
		button.pencil {
			background: url(../../../backend/img/default/icons/pencil.png) no-repeat 0px 0px;
		}
		button.delete {
			background: url(../../../backend/img/default/icons/delete.png) no-repeat 0px 0px;
		}
		button.add {
			background: url(../../../backend/img/default/icons/add.png) no-repeat 0px 0px;
		}
		button.folders_plus {
			background: url(../../../backend/img/default/icons4/folders_plus.png) no-repeat 0px 0px;
		}
	</style>
	<?php

	if (!$sCore->sCheckLicense("","",$sCore->sLicenseData["sFUZZY"])){
	?>
	<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
	<?php
echo $sCore->sDumpLicenceInfo("../../../","Modul Intelligente Suche","Helfen Sie potentiellen Käufern, Artikel besser zu finden und steigern Sie somit effektiv Ihren Umsatz. Die intelligente Suche ist fehlertolerant und liefert die richtigen Ergebnisse, auch wenn der Suchbegriff mit Rechtschreibfehlern, Vertippern und andere Wortzusammenstellungen eingegeben wurde. Ebenfalls werden Teilbegriffe ausgewertet und es findet eine automatische Gewichtung der Suchergebnisse statt, die anschließend weiter nach Hersteller, Preis, Kategorie etc. gefiltert werden können.","http://www.shopware-ag.de/Intelligente-Suchfunkti.-_detail_69_198.html","sFUZZY");
$licenceFailed = true;
exit;
}?>
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
 	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script>
Ext.onReady(function(){

	var fields = new Ext.grid.EditorGridPanel({
		title:'Relevanz/Felder',
		id:'fields',
		//viewConfig: { forceFit:true },
		store: new Ext.data.Store({
			url: '?get=fields',
			autoLoad: true,
			reader: new Ext.data.JsonReader({
				root: 'articles',
				totalProperty: 'count',
				id: 'fieldID',
				fields: [
					'fieldID', 'name', 'relevance', 'field', 'tableID', 'table'
				]
			})
		}),
		selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
		cm: new Ext.grid.ColumnModel([
			{id:'name', dataIndex: 'name', header: "Name", width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: false})},
			{id:'relevance', dataIndex: 'relevance', header: "Relevanz", width: 200, sortable: true, editor: new Ext.form.NumberField({allowBlank: true, decimalPrecision : 0})},
			{id:'field', dataIndex: 'field', header: "Feld", width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: false})},
			{id:'tableID', dataIndex: 'tableID', header: "Tabelle", width: 200, sortable: true,
				editor: new Ext.form.ComboBox({
					typeAhead: true,
					forceSelection: true,
					triggerAction: 'all',
					editable: false,
					store:  new Ext.data.Store({
						url: '?get=tables',
						autoLoad: true,
						reader: new Ext.data.JsonReader({
							root: 'articles',
							totalProperty: 'count',
							id: 'tableID',
							fields: ['tableID','table']
						})
					}),
					displayField: 'table',
					valueField: 'tableID',
					mode: 'remote',
				}),
				renderer: function(value, p, record, row, col, store){
					return record.data.table;
				}
			}
		]),
		listeners : {
			"afteredit" : {fn: function(e){
				if(e.field=="tableID")
				{
					var record = e.grid.colModel.config[e.column].editor.store.getById(e.value);
					e.record.set("table", record.data.table);
				}
			}, scope:this}
		},
		buttonAlign:'right',
		buttons: [{
			text: 'Speichern',
			handler: function(){
				Ext.getCmp('fields').store.each(function(record){
					new Request({method: 'post', url: 'search.php?set=fields', async: true, data: record.data}).send();
				});
				Ext.getCmp('fields').store.commitChanges();
				Ext.getCmp('fields').store.load();
			}
		}],
		tbar:[{
            text:'Feld hinzuf&uuml;gen',
            iconCls:'add',
            handler: function (){
            	var r = Ext.data.Record.create([]);
            	var c = new r();
            	Ext.getCmp('fields').stopEditing();
            	Ext.getCmp('fields').store.insert(0, c);
            	Ext.getCmp('fields').startEditing(0, 0);
            },
        },'-',{
            text:'Feld l&ouml;schen',
            handler: function (a, b, c){
            	if(!Ext.getCmp('fields').selModel.getSelected()) return;
            	var tableID = Ext.getCmp('fields').selModel.getSelected().id;
            	Ext.MessageBox.confirm('', 'Wollen Sie diesen Eintrag wirklich entfernen?', function(r){
            		if(r=='yes')
            		{
            			Ext.getCmp('fields').store.load({params:{"delete": tableID}});
            		}
	            });
            },
            iconCls:'delete'
        }]
	});
	
	var tables = new Ext.grid.EditorGridPanel({
		title:'Tabellen',
		id:'tables',
		//viewConfig: { forceFit:true },
		store: new Ext.data.Store({
			url: '?get=tables',
			autoLoad: true,
			reader: new Ext.data.JsonReader({
				root: 'articles',
				totalProperty: 'count',
				id: 'tableID',
				fields: [
					'tableID', 'table', 'referenz_table', 'foreign_key', 'where'
				]
			})
		}),
		selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
		cm: new Ext.grid.ColumnModel([
			{id:'table', dataIndex: 'table', header: "Tabelle", width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: false})},
			{id:'referenz_table', dataIndex: 'referenz_table', header: "Referenz-Tabelle", width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: true})},
			{id:'foreign_key', dataIndex: 'foreign_key', header: "Fremdschl&uuml;ssel", width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: true})},
			{id:'where', dataIndex: 'where', header: "Zus&auml;tzliche Bedingungen", width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: true})},
		]),
		listeners : {
			"afteredit" : {fn: function(e){
				
			}, scope:this}
		},
		buttonAlign:'right',
		buttons: [{
			text: 'Speichern',
			handler: function(){
				Ext.getCmp('tables').store.each(function(record){
					new Request({method: 'post', url: 'search.php?set=tables', async: true, data: record.data}).send();
				});
				Ext.getCmp('tables').store.load();
			}
		}],
		tbar:[{
            text:'Tabelle hinzuf&uuml;gen',
            iconCls:'add',
            handler: function (){
            	var r = Ext.data.Record.create([]);
            	var c = new r();
            	Ext.getCmp('tables').stopEditing();
            	Ext.getCmp('tables').store.insert(0, c);
            	Ext.getCmp('tables').startEditing(0, 0);
            },
        },'-',{
            text:'Tabelle l&ouml;schen',
            handler: function (a, b, c){
            	if(!Ext.getCmp('tables').selModel.getSelected()) return;
            	var tableID = Ext.getCmp('tables').selModel.getSelected().id;
            	Ext.MessageBox.confirm('', 'Wollen Sie diesen Eintrag wirklich entfernen?', function(r){
            		if(r=='yes')
            		{
            			Ext.getCmp('tables').store.load({params:{"delete": tableID}});
            		}
	            });
            },
            iconCls:'delete'
        }]
	});
	
	var config = new Ext.grid.EditorGridPanel({
		title:'Allgemeine Einstellungen',
		id: 'config',
		closable:false,
		store: new Ext.data.Store({
			url: '?get=config',
			autoLoad: true,
			reader: new Ext.data.JsonReader({
				root: 'articles',
				totalProperty: 'count',
				id: 'fieldID',
				fields: [
					'name', 'description', 'value'
				]
			})
		}),
		selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
		cm: new Ext.grid.ColumnModel([
			{id:'description', dataIndex: 'description', header: "Beschreibung", width: 300, sortable: true},
			{id:'value', dataIndex: 'value', header: "Wert", width: 300, sortable: true, editor: new Ext.form.TextField({allowBlank: true})}
		]),
		listeners : {
			"afteredit" : {fn: function(e){
				
			}, scope:this}
		},
		buttonAlign:'right',
		buttons: [{
			text: 'Speichern',
			handler: function(){
				Ext.getCmp('config').store.each(function(record){
					new Request({method: 'post', url: 'search.php?set=config', async: true, data: record.data}).send();
				});
				Ext.getCmp('config').store.commitChanges();
			}
		}]
	});
	        
	var tabs = new Ext.TabPanel({
		region:'center',
		enableTabScroll:true,
		deferredRender:false,
		activeTab:0,
		defaults: {autoScroll:true},
		items: [config, fields, tables]
	});

	var viewport = new Ext.Viewport({
		layout:'fit',
		items: tabs
	});
});
</script>
</head>
<body>
</body>
</html>