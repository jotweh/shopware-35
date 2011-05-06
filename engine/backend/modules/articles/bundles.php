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

$articleID = intval($_GET['article']);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $sLang["auth"]["auth_overview"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
</head>
<style>
.x-node-ctx {
    background:#eee !important;
    border:1px solid #ccc !important;
}.x-tab-panel-header {
    border-bottom-width:0 !important;
}
#main-tabs .x-tab-panel-body {
    background:transparent;
    border:0 none;
}
.x-tree-node .x-tree-selected a span{
	background:transparent;
	color:#15428b;
    font-weight:bold;
}
.x-tree-selected {
    border:1px dotted #a3bae9;
    background:#DFE8F6;
}
.tree {
    border:1px solid #fff;
    margin:3px;
}
.x-tree-node div.feeds-node{
    background:#eee url(images/cmp-bg.gif) repeat-x;
    margin-top:1px;
    border-top:1px solid #ddd;
    border-bottom:1px solid #ccc;
    padding-top:2px;
    padding-bottom:1px;
}

.x-tree-node-leaf .x-tree-node-icon {

    background-image:url();
}

.tree-node .x-tree-node-icon {
    display:none;
}
.x-tree {
    background:#fff !important;
}
</style>
<body>


<?php 
if (!$sCore->sCheckLicense("","",$sCore->sLicenseData["sBUNDLE"])){ 
echo "<div style='height:22px;'></div>";
echo $sCore->sDumpLicenceInfo(
	"../../../",
	"Bundle-Modul",
	"Mit der \"Bundle\"-Funktion können Sie beliebige Artikel in Shopware miteinander kombinieren, 
	um diese dann als Bundle zu einem vergünstigten Preis anzubieten. Auf diese Weise können Sie 
	beispielsweise Zubehörartikel zu einem reduzierten Preis in Verbindung mit dem Hauptartikel anbieten.
	", "http://www.shopware.de/Bundle-Modul-_detail_485_180.html","sBUNDLE");
	exit;
}
?>


<script type="text/javascript">
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteSupplier":

			//Fenster leeren
			Ext.get('framepanelmyiframe').dom.src = "";

			// Redirect
			//ndow.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?group=<?php echo $_GET["group"]?>&delete="+sId;
				Ext.Ajax.request({
	                waitMsg: 'Speichere Bundle',
	                url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/bundle/bundle_deleteAll.php',
	                params: {
	                    bundleID: sId
	                },
	                failure:function(response,options){
	                    Ext.MessageBox.alert('Bundle konnte nicht gelöscht werden');
	                },
	                success:function(response,options){
						parent.Growl('Bundle wurde gelöscht');
						myExt.reload();
	                }
            	});
			break;
	}
}


function deleteArticle(ev,text){
			parent.parent.sConfirmationObj.show('Soll das Bundle "'+text+'" wirklich gelöscht werden?',window,'deleteSupplier',ev);
}

	var myExt = function(){
		var store;
		var storeid;
		var myTab;
		var tree;

	return {
	reload : function(){
    	tree.root.reload();
    },
	init : function(){
	   	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

		Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
		onRender : function(ct, position){
		  this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
		}
		});

		tree = new Ext.tree.TreePanel({
		id:'feed-tree',
		region:'west',
		title:'Bundles',
		split:true,
		width: 225,
		minSize: 175,
		maxSize: 400,
		collapsible: true,
		margins:'0 0 0 0',
		cmargins:'0 5 5 5',
		rootVisible:false,
		lines:false,
		loader: new Ext.tree.TreeLoader({dataUrl:"../../../backend/ajax/getBundles.php?articleID=<?php echo $articleID; ?>"}),
		autoScroll:true,
		root: new Ext.tree.AsyncTreeNode({
		 text: '<?php echo $sLang["auth"]["auth_user"] ?>',
		 cls:'feeds-node',
		 expanded:true,
		 id:'0'
		}),
		collapseFirst:false,

		tbar: [{
		    iconCls:'add-feed',
		    text:'Hinzufügen',
		    handler: function(){showWindow("new");},
		    scope: this
		},{
		    id:'delete',
		    iconCls:'delete-icon',
		    text:'Löschen',
		    handler: function(){

		        var s = tree.getSelectionModel().getSelectedNode();
		        if(s && parseInt(s.attributes.id)){

		            deleteArticle(s.attributes.id,s.attributes.text);
		        }
		    },
		    scope: this
		}]
		});

	    tree.on('click', function(e){
	    	if (parseInt(e.attributes.id)){
				 showWindow(e.attributes.id);
				 if (e.attributes.count>0){
				 	Ext.getCmp('delete').disable();
				 }else {
				 	Ext.getCmp('delete').enable();
				 }
	    	}
	    });

	    var iframe = new Ext.ux.IFrameComponent({
			region:'center',
			split:true,
			animate:true,
			fitToFrame: true,
			title:'<?php echo $sLang["auth"]["auth_details"] ?>',
			width:700,
	        height:500,
			collapsible: true,
			id: "myiframe",
			url: ''
		});

		function showWindow(ID){
			if(ID == "new")
			{
				var url = "bundle_mask.php?new=1&articleID=<?php echo $articleID; ?>";
			}else if(ID == "empty")
			{
				var url = "";
			}else{
				var url = "bundle_mask.php?bundleID="+ID+"&articleID=<?php echo $articleID; ?>";
			}

			Ext.get('framepanelmyiframe').dom.src = url;
		}
		function removeUser(id){
			Ext.MessageBox.confirm('<?php echo $sLang["auth"]["auth_validation"] ?>', '<?php echo $sLang["auth"]["auth_really_delete_marked_user"] ?>', function deleteClientConfirmed(btn,id){
	    		if (btn=="yes"){
	    				var user = tree.getSelectionModel().getSelectedNode().attributes.id;

	    				if (user){

							Ext.Ajax.request({
			                    waitMsg: '<?php echo $sLang["auth"]["auth_saving_changes"] ?>',
			                    url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteAuth.php',
			                    params: {
			                        user: user
			                    },
			                    failure:function(response,options){
			                        Ext.MessageBox.alert('<?php echo $sLang["auth"]["auth_warning"] ?>','<?php echo $sLang["auth"]["auth_oops"] ?>');
			                    },
			                    success:function(response,options){
									parent.Growl('<?php echo $sLang["auth"]["auth_user"] ?> '+user+' <?php echo $sLang["auth"]["auth_deleted"] ?>');
									myExt.reload();
			                    }
			            	});
	    				}
			    	}
    	})};

		myTab = new Ext.TabPanel({
		    region:'center',
		    deferredRender:false,
		    activeTab:0,
		    closeable:true
		});

		var viewport = new Ext.Viewport({
		layout:'border',
		items:[
		    tree,iframe
		 ]
		});



}};
}();
Ext.onReady(function(){
	myExt.init();
});
</script>

</body>
</html>