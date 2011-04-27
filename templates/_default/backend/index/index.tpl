{extends file="backend/index/parent.tpl"}
{block name="backend_index_css" append}
	<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
	<link href="{link file='backend/_resources/styles/index.css'}" rel="stylesheet" type="text/css" />
	<link href="{link file='engine/backend/plugins/moo.tabs/css/default.css'}" rel="stylesheet" type="text/css" />
{/block}
{block name="backend_index_javascript"}
	<script type="text/javascript">
	//<![CDATA[
		var baseUrl = "{$BaseUrl}";
		var basePath = "{$BasePath}";
	//]]>
	</script>
	<script type="text/javascript" src="{link file='engine/vendor/ext/adapter/ext/ext-base.js'}"></script>
	<script type="text/javascript" src="{link file='engine/vendor/ext/ext-all.js'}"></script>
	<script type="text/javascript" src="{link file='engine/vendor/ext/build/locale/ext-lang-de.js'}" charset="utf-8"></script>
	<script type="text/javascript" src="{link file='engine/backend/js/moo12-core.js'}"></script>
	<script type="text/javascript" src="{link file='engine/backend/js/moo12-more.js'}"></script>
	<script type="text/javascript" src="{link file='engine/backend/js/framework.php'}"></script>
	<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/Ext.ux.TabScrollerMenu.js'}" charset="utf-8"></script>
	<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/Ext.Grid.RowExpander.js'}" charset="utf-8"></script>
	{block name="backend_index_index_onload"}
	<script type="text/javascript">
	//<![CDATA[
		var sConfirmationObj = new sConfirmation($('ConfirmationBox'), { child: 0, pipe: 0, parameter: ''});
		window.addEvent('domready',function() { 
			
			{if $ShowActivate}
				openAction('activate');
			{elseif !{config name=HideStart}}
				openAction('Widgets');
			{/if}
			
			myExt.reload.periodical({config name='RefreshDashboard'}, this);

			{block name="backend_index_index_onload_inline"}{/block}
		});
	//]]>
	</script>
	{/block}
	{block name="backend_index_index_extjs"}
		{include file="backend/index/index_ext.tpl"}
	{/block}
{/block}
{block name='backend_index_header_title'}
	Shopware {config name='Version'} - 08.12.2010 - Backend (c) 2010,2011 shopware AG
{/block}

{block name="backend_index_body_attributes"}onresize="myExt.resizeWindowEvent();"{/block}

{block name="backend_index_body_inline"}
	<div id="header">
		
		
		{block name="backend_index_index_menu"}
			{include file="backend/index/menu.tpl"}
		{/block}
		
		{block name="backend_index_index_search"}
			<div id="mastersearch">
				<form name="frmSuche" method="post" action=""  style="float:left;">
					<input name="search" id="search" type="text" class="mastersearch" value="{s name='SearchLabel'}Suche{/s}" maxlength="30" />
				</form>
				<div id="searchfocus" class="search_disabled" style="top:0px;left:122px;position:relative;float:none;margin:0pt;"></div>
				<div id="result" style="display:none;position:relative;top:-1px;left:-95px;"></div>
			</div>
		{/block}
		{block name="backend_index_index_logo"}
			<div id="{$Logo}"></div>	
		{/block}
	</div>
	
	{block name="backend_index_index_window_template"}
		{include file="backend/index/window.tpl"}
	{/block}
	
	{block name="backend_index_index_instantmessenger"}
		<div id="im" style="width:150px;position:fixed;bottom:35px;display:none;z-index:100000">
		</div>
	{/block}
	
	{block name="backend_index_index_footer"}
		<!-- STATUSBAR -->
		<div id="footer">
			<div class="status">
				<a href="{url action='logout'}">{s name='LogoutLabel'}Logout{/s}</a><img src="{link file='engine/backend/img/default/footer/line.gif'}" alt="line" />
				<a href="#" onclick="javascript:loadSkeleton('live',true);">{s name='LiveViewLabel'}Shop-Ansicht{/s}</a>
				<img src="{link file='engine/backend/img/default/footer/line.gif'}" alt="line" />
				<a class="ico2 status_online" href="#" onclick="javascript:loadSkeleton('auth',true);" title="{s name='UserLabel'}Benutzer: {$UserName}{/s}" style="width: 8px;"></a>
				<img src="{link file='engine/backend/img/default/footer/line.gif'}" alt="line" />
				<a class="ico2 balloons" href="#" id="imHandler" onclick="if ($('im').getStyle('display')=='none'){ $('im').setStyle('display','block'); myExt.reload(); myExt.displayLicense(); }else{ $('im').setStyle('display','none');}" title="Instant Messenger" style="width: 8px;"></a>
				<img src="{link file='engine/backend/img/default/footer/line.gif'}" alt="line" />
				<div id="windowTracker" class="windowTracker" style="position:absolute;left:160px;padding-top:3px;top:-5px;width:450px">
				</div>
			</div>
		</div>
		<!-- /STATUSBAR -->
	{/block}
	
	{block name="backend_index_index_footer_tabs"}
		<!-- TAB TEMPLATE -->	
		<div id="myTabTemplate" class="tabGroupContainer" style="display:none; height:15px">
			<div class="panelContainer">
				<div id="panelTemplate" class="panelHide" style="display:none">
				</div>
			</div>
		</div>
		<!-- // TAB -->
	{/block}
	
	{block name="backend_index_index_right"}
		<div id="myPanel"></div>
	{/block}
	
	{block name="backend_index_index_body"}
		<div id="body"></div>
	{/block}
	
	{block name="backend_index_index_top"}
		<div id="north"></div>
	{/block}
	
	{block name="backend_index_index_account"}
		{if !{config name='AccountId'}}
			<div id="saccount">
					<p style="font-weight:bold">
						{se name='AccountMissing' style='color:#F00'}Kein Account angelegt!{/se}
						<br /><br />
					</p>
			</div>
		{else}
			<div id="saccount">
				{if $Amount <= 0}
					{se name='AccountBalance' style='color:#F00'}Guthaben: {$Amount} SC{/se}
				{else}
					<p style="font-size:11px; padding:5px;">
						{se name='AccountBalance'}Guthaben: {$Amount} SC{/se}
					</p>
				{/if}
			</div>
		{/if}
	{/block}
	
	{block name="backend_index_index_ticket"}
		<div id="ticketCount-div" style="display:none">
			<div id="ticketCountText" style="float:left;"></div>
			<div id="ticketCountSkeleton" style="float:right;">
				<a class="ico pencil_arrow" style="cursor:pointer" onclick="loadSkeleton('ticket_system');">&nbsp;</a>
			</div>
		</div>
	{/block}
	

	<div style="display:none">
		<div id="licence" style="padding:10px 10px 10px 10px">
			{if !$PremiumLicence}
				<p style="font-weight:bold;color:#F00">Dieser Premium-Inhalt ist Wartungskunden vorbehalten!</p>
				<br /><br />
				Mit einem Wartungsvertrag für Ihre Shopware sind Sie bestens für die Zukunft gerüstet. <br />
				Denn neben einem kostenfreien Support erhalten Sie ab sofort alle Updates und Neuerungen für Ihre Shopware automatisch, völlig kostenlos und dank der neuen Premium-Inhalte sogar schon lange vor dem offiziellen Release. So haben Sie die Möglichkeit, neue Funktionen und Module direkt nach Fertigstellung in Ihr Shopsystem integrieren zu lassen und müssen nicht erst auf die nächste Version von Shopware warten. Dieser zeitliche Vorsprung ist ein echter Wettbewerbsvorteil und stellt sicher, dass Ihr Shop immer am Puls der Zeit und Ihrer Konkurrenz meilenweit voraus ist.
				<br /><br />
				<p style="font-weight:bold;">Bestandteile des Wartungsvertrags:</p>
				<br />
				<ul>
				<li>- Alle Shopware Minor- und Major-Updates sind im Preis enthalten</li>
				<li>- Installation der Updates inbegriffen (Ausnahme Template-Erweiterungen)</li>
				<li>- Zugriff auf exklusive Premium-Inhalte, die Wartungskunden vorbehalten sind (Funktionen, die normalerweise erst mit dem nächsten Major-Release kommen würden) Eine Übersicht aktueller Premium-Inhalte finden Sie hier</li>
				<li>- Auf Anfrage eigene Testumgebung auf einem unserer FTP-Server</li>
				<li>- Premium Support</li>
				</ul>
				<br />
				<a href="http://www.shopware-ag.de/shopware.php/sViewport,support/sFid,26?sCategory=164" target="_blank"><img src="{link file='engine/backend/img/default/account/bt_wartung.gif'}"></a>
			{/if}
		</div>
	</div>
	
	</div>
{/block}