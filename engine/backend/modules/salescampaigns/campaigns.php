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
<?php
if ($_GET["categoryID"] && $_GET["categoryID"]!=1){
	$getCategoryName = mysql_query("
	SELECT description FROM s_categories WHERE id={$_GET["categoryID"]}
	");
	$categoryName = @mysql_result($getCategoryName,0,"description");
	if (!$categoryName) die("Kategorie nicht gefunden");
}elseif($_GET["categoryID"]==1){
	$categoryName = $sLang["salescampaigns"]["campaigns_startsite"];
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>core.campaigns</title>
 	<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
	<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>

	
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
 	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

 	
	<style type="text/css">

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
	
	<script type="text/javascript">
    
	var myExt = function(){
    var root, tree, currentNode, parentNode;
    var Tree = Ext.tree;
    return {
    reload : function(){
    	try {
    		currentNode.reload();
    	} catch (e){
    		this.reloadParent();
    	}
    },
    reloadParent : function(){
    	parentNode.reload();
    },
    init : function(){
    	
    Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
     onRender : function(ct, position){
          this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
     }
	}); 

<?php
if (!$_GET["categoryID"]){
?>
	tree = new Tree.TreePanel({
    	region:'west',
    	split:true,
    	fitToFrame: true,
        animate:false, 
        collapsible: true,
        title:'<?php echo $sLang["salescampaigns"]["campaigns_Categories"] ?>',
        width: 200,
        height:'100%',
        margins:'0 0 0 0',
        minSize: 175,
        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getCategories.php'}),
        enableDD:true,
        enableEdit:true,
        autoScroll: true
	});
            
    tree.on('click', function(e){
		var url = "start.php?id="+e.attributes.id;
		Ext.get('framepanelmyiframe').dom.src = url;
		parentNode = e.parentNode;
		currentNode = e;
	});  
<?php
} else {
?>

	tree = new Tree.TreePanel({
    	region:'west',
    	split:true,
    	fitToFrame: true,
        animate:false, 
        collapsible: true,
        title:'<?php echo $sLang["salescampaigns"]["campaigns_Actions"] ?>',
        width: 250,
        margins:'0 0 0 0',
        minSize: 175,
        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getCampaigns.php'}),
        enableDD:true,
        enableEdit:true,
        autoScroll: true
	});
	
	
	tree.on('click', function(e){
		var id = e.attributes.id.replace(/#(.*)/g, "");
		
		
		switch (e.attributes.type){
    		case "CAMPAIGNS":
    			var url = "campaignsedit.php?id="+id;
    			break;
    		case "CAMPAIGN":
    			var url = "campaignsedit.php?id="+id+"&category=<?php echo $_GET["categoryID"]?>";
    			break;
    		case "BANNER":
    			var url = "banneredit.php?id="+id;
    			break;
    		case "LINKS":
    			var url = "linksedit.php?id="+id;
    			break;
    		case "LINK":
    			var url = "linkdetails.php?id="+id;
    			break;
    		case "TEXT":
    			var url = "textedit.php?id="+id;
    			break;
    		case "ARTICLES":
    			var url = "articlesedit.php?id="+id;
    			break;
    		case "ARTICLE":
    			var url = "articledetails.php?id="+id;
    			break;
    		default:
    			var url = "campaignsedit.php?category=<?php echo $_GET["categoryID"]?>";
    		break;
        }
		
		Ext.get('framepanelmyiframe').dom.src = url;
		parentNode = e.parentNode;
		currentNode = e;
	});  
<?php
}
?>
<?php
if (!$_GET["categoryID"]){
?>
	var root = new Tree.AsyncTreeNode({
		 text: 'Start',
		 draggable:true,
		 id:'1'
	});
<?php
}else {
?>
	var root = new Tree.AsyncTreeNode({
        text: '<?php echo $categoryName ?>',
        draggable:true,
        id:'CAMPAIGNS:<?php echo $_GET["categoryID"] ?>'
	});
	
	tree.on('nodedrop',function(e){
			var affectedNodes = tree.getNodeById(e.target.attributes.parentId);
			var group = e.target.attributes.parentId;
			var nodeData =affectedNodes.childNodes;
			//console.log(nodeData);
			var nodeTest = new Array();
			
			
			
			//nodeTest[2] = 3;
	        for(var i = 0, len = nodeData.length; i < len; i++){
	        	 var data = nodeData[i];
	        	 var nodeId = data.attributes.dbId;
	        	 //console.log(nodeId);
	        	 var nodePosition = i+1;
	        	 
	        	 nodeTest[i] = new Object;
				 nodeTest[i].position = nodePosition;
				 nodeTest[i].id = nodeId;
			      	 
	        }
	        
	      
	        
	        conn = new Ext.data.Connection();
				conn.request({
				url: '../../../backend/ajax/reposCampaign.php',
				method: "POST",
				params: {
						nodes: Ext.encode(nodeTest),
						group: group
				},
				callback: function(o, success, response) {
					if ( !success ) {
						parent.Growl('<?php echo $sLang["salescampaigns"]["campaigns_serverconnection_failed"] ?>');
						return false;
					}else {
						parent.Growl('<?php echo $sLang["salescampaigns"]["campaigns_Element_has_been_postponed"] ?>');
						return false;
					}
							
				}
				});
    		});
  
	tree.on('nodedragover',function(e){
		//console.log(e);
		//console.log(e.target.attributes);
		// Einfügen in andere Kategorie verhindern
		if (e.point=="append") return false;
		// Verschieben nur bei gleichem Parent-Element
		if (e.target.attributes.parentId != e.data.node.attributes.parentId) return false;
		return true;
		
	});
    tree.on('beforeclick', function(node){
    	return;
        if(this.getSelectionModel().isSelected(node)){
        	
            ge.node = node;
            
            ge.startEdit(node.ui.getAnchor(), node.text);
            return false;
        }
        
	});
<?php
}
?>
	
	var iframe = new Ext.ux.IFrameComponent({ 
		region:'center',
		split:true,
		animate:true, 
		fitToFrame: true,
		title:'<?php echo $sLang["salescampaigns"]["campaigns_settings"] ?>',
		width:700,
        height:500,
		collapsible: true,
		id: "myiframe", 
		url: '<?php echo !$_GET["categoryID"] ? "start.php" : "" ?>' 
	});
	
 
    
	
    
  
    		
	tree.setRootNode(root);
	root.expand();
	
	<?php
	if ($_GET["categoryID"]){
	?>
	var header = new Ext.BoxComponent({ // raw
                    region:'north',
                    el: 'north',
                    height:32
    });
                
	var viewport = new Ext.Viewport({
	    layout:'border',
	    items:[
	    		header,
				iframe,
				tree
	     ]
	});
    <?php
	}else {
    ?>
                
	var viewport = new Ext.Viewport({
	    layout:'border',
	    items:[
				iframe,
				tree
	     ]
	});
	
    <?php
	}
    ?>
      
         
}};
}();
Ext.onReady(function(){
	myExt.init();
});
</script>
</head>
<body>
<div id="north">


		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onclick="document.location.href='campaigns.php'" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["campaigns_to_actionoverview"] ?></div></button></li>	
		</ul>
		</div>
</div>
</body>
</html>

