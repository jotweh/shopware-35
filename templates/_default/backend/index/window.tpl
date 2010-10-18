<div id="myWindow" class="container" style="top:290px; left: 240px; height:250px;width:400px;display:none" >
	<!-- SHADOW TOP -->

	<div class="fixfloat"></div>
	<!-- /SHADOW TOP -->
	<!-- WINDOW-CONTENT-START -->
	<div class="windowContent" style="background-color:#D8D8D8">	
	<div id="ironmask" class="ironmask" style="width:100px;height:100px;z-index:1000;position:absolute;margin-top:35px;"></div>

	<!-- WINDOW TITLE AND OPTIONS -->
		<div id="windowTitle" class="windowTitle active">
			<div class="handle">
			</div>
			<div id="description" class="description">Fenster - 1</div>
			<ul class="window_bt_opt" style="z-index:1">
				<li id="help" class="help"><a href="#"  class="bt_opt_info" title="Hilfe aufrufen"></a></li>
				<li id="refreshme" class="refreshme"><a href="#"  class="bt_opt_refresh" title="Fenster aktualisieren"></a></li>
				<li id="minime" class="minime"><a href="#"  class="bt_opt_minimize" title="Fenster minimieren"></a></li>
				<li id="maxme" class="maxme"><a id="windowmax" href="#" class="bt_opt_maximize" title="Fenster maximieren"></a></li>
				<li id="closeme" class="closeme"><a href="#" class="bt_opt_close" title="Fenster schliessen"></a></li>
			</ul>
		</div>	
	<!-- /WINDOW TITLE AND OPTIONS -->
		<!-- TAB PLACEHOLDER -->	
		<div class="tabTemplate" id="tabTemplate" style="display:none">
		<div class="tabGroupContainer" style="height: 15px;">
			<div class="tabContainer default">
				<ul id="tabNode" class="tabNode">
					<li id="tabNodeChild" class="current" show="save" style="display:none">
					<span>Stammdaten</span>
					<div id="tabNodeChildContent" class="tabNodeChildContent" style="display:none">Test</div>
					</li>
				</ul>
			</div>
		</div>
		</div>
		<!-- // TAB PLACEHOLDER -->		
	<!-- CONTENT-CONTAINER  --> 
		<div id="content" class="content" style="height:200px;padding:10px 10px 0px 10px;background-color:#D8D8D8;overflow:hidden;">
		</div>
		<div class="fixfloat"></div>
	<!-- /CONTENT-CONTAINER -->
	
		

		<!--
		<div style="position:absolute;right:0px;top:1px;width:100%;height:5px;cursor:s-resize;" class="resize-n"></div>
		<div style="position:absolute;left:1px;top:0px;width:5px;height:100%;cursor:w-resize;" class="resize-w"></div>
		<div style="position:absolute;left:0px;bottom:0px;width:5px;height:5px;cursor:sw-resize;" class="resize-sw resize"></div>
		-->
		<div style="position:absolute;right:1px;top:0px;width:5px;height:100%;cursor:w-resize;" class="resize-e"></div>
		<div style="position:absolute;right:0px;bottom:0px;width:100%;height:5px;cursor:s-resize;" class="resize-s"></div>
		<div style="position:absolute;right:0px;bottom:0px" class="resize-se resize"></div>
		
		<div class="footer" style="min-height:15px;">
			<div class="buttons" id="buttons" style="display:none;margin-left:25px;width:100%;height:30px;">
			<ul>
			<li id="buttonTemplate" class="buttonTemplate" style="display:none"><button name="#NAME#" type="submit" value="send" class="button"><div class="buttonLabel">#LABEL#</div></button></li>
			</ul>
			</div>
		</div>
	</div>
	<!-- /WINDOW-CONTENT-END -->
	<div class="fixfloat"></div>
	<!-- SHADOW BOTTOM -->
	<!-- /SHADOW BOTTOM -->
</div>