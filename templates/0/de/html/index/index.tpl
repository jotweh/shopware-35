<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--
**********************************************
******  Shopware 3.0 Core    		   *******
**********************************************
Shopware ist ein Produkt der shopware AG
			www.shopware.ag
-->
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="{$sConfig.sSnippets.sIndexMetaAuthor}" />
<meta name="copyright" content="{$sConfig.sSnippets.sIndexMetaCopyright}" />
<meta name="robots" content="{$sConfig.sSnippets.sIndexMetaRobots}" />
<meta name="revisit-after" content="{$sConfig.sSnippets.sIndexMetaRevisit}" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<meta name="keywords" content="{if ($sArticle.keywords || $sArticle.sDescriptionKeywords) && $_GET.sViewport=="detail"}{if $sArticle.keywords}{$sArticle.keywords}{elseif $sArticle.sDescriptionKeywords}{$sArticle.sDescriptionKeywords}{/if}{elseif $sCategoryContent.metakeywords}{$sCategoryContent.metakeywords}{else}{$sConfig.sSnippets.sIndexMetaKeywordsStandard}{/if}" />
<meta name="description" content="{if $sArticle.description_long && $_GET.sViewport=="detail"}{if $sArticle.description}{$sArticle.description}{else}{$sArticle.description_long|strip_tags}{/if}{elseif $sCategoryContent.metadescription}{$sCategoryContent.metadescription}{else}{$sConfig.sSnippets.sIndexMetaDescriptionStandard}{/if}" />
{if !$_GET.sViewport}
<link rel="canonical" href="{$sConfig.sBASEFILE}" />
{elseif $_GET.sViewport == 'cat'}
<link rel="canonical" href="{$sConfig.sBASEFILE}?sViewport=cat&sCategory={$_GET.sCategory|intval}" title="{$sCategoryContent.description}" />
{elseif $_GET.sViewport == 'detail'}
<link rel="canonical" href="{$sConfig.sBASEFILE}?sViewport=detail&sArticle={$_GET.sArticle|intval}" title="{$sArticle.articleName}" />
{/if}
<title>{strip}
{if $sArticle.articleName && $_GET.sViewport=="detail"}{$sArticle.articleName} | {/if}
{if $sBreadcrumb}{foreach from=$sBreadcrumb item=breadcrumb}{$breadcrumb.name} | {/foreach}{/if}
{$sShopname}
{/strip}</title>
{if $sConfig.sGOOGLECODE}
{include file="index/google_analytics.tpl"}
{/if}
<script type="text/javascript">
//<![CDATA[
	var sSearchShowAllResults = "{* sSnippet: Show all results *}{$sConfig.sSnippets.sSearchshowallresults}";
	var sSearchManufacturer = '{* sSnippet: Manufacturer: *}{$sConfig.sSnippets.sSearchmanufacturer}';
	var sSearchCategories = "{* sSnippet: Categories: *}{$sConfig.sSnippets.sSearchcategories}";
	var sViewportAjax = "{if $_SERVER.SERVER_PORT == 80}http{else}https{/if}://{$sConfig.sBASEPATH}/{$sConfig.sBASEFILE}/sViewport,ajax/";
	var sCompareMaxReached = "{* sSnippet: you can compare up to 5 items in one step! *}{$sConfig.sSnippets.sIndexcompareupto5articles}";
	var basepath =  "{if $_SERVER.SERVER_PORT == 80}http{else}https{/if}://{$sConfig.sBASEPATH}";
	var minsearchlenght = {if $sConfig.sMINSEARCHLENGHT}{$sConfig.sMINSEARCHLENGHT}{else}3{/if};
	var sServerTime = {$smarty.now};
//]]>
</script>
{if !$sConfig.sDONTGZIP}
	{literal}
	<script type="text/javascript" src="../../media/js/get.php?file=core.js"></script>
	{/literal}
{else}
	{literal}
	<script type="text/javascript" src="../../media/js/core.js"></script>
	{/literal}
{/if} 

{if $_GET.sViewport=="sale" || $_GET.sRefererAllowed}
	{literal}
	<script type="text/javascript">
		 if(top!=self){
		  	top.location=self.location;
		 }
	</script>
	{/literal}
{/if}



{if !$sConfig.sDONTGZIP}
	<link href="../../media/css/get.php?file=basic.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../../media/css/get.php?file=print.css" rel="stylesheet" type="text/css" media="print" />
	<link href="../../media/css/get.php?file=lightbox.css" rel="stylesheet" type="text/css" />
	<!--[if lte IE 6]>
		<link href="../../media/css/get.php?file=lteie6.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if IE 7]>
		<link href="../../media/css/get.php?file=lteie7.css" rel="stylesheet" type="text/css" />
	<![endif]-->
{else}
	<link href="../../media/css/basic.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="../../media/css/print.css" rel="stylesheet" type="text/css" media="print" />
	<link href="../../media/css/lightbox.css" rel="stylesheet" type="text/css" />
	<!--[if lte IE 6]>
		<link href="../../media/css/lteie6.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if IE 7]>
		<link href="../../media/css/lteie7.css" rel="stylesheet" type="text/css" />
	<![endif]-->
{/if}
<link href="../../media/css/305.css" rel="stylesheet" type="text/css" media="screen" />
{* 303 Zoomviewer *}
{if $sConfig.sUSEZOOMPLUS && $_GET.sViewport=="detail"}
<link href="../../media/zoomplus/magiczoomplus.css" rel="stylesheet" type="text/css" media="screen" />
	<script type="text/javascript" src="../../media/zoomplus/get.php?hash={$sConfig.sPREMIUM}"></script>
{/if}
</head>
<body 
{* Definition wann Rechte bzw. Linke Spalte ausgeblendet werden soll *}
{if $_GET.sViewport=="admin" && $_GET.sAction=="orders"}
	id="hideLeft"
{elseif 
	$_GET.sViewport=="basket" || 
	$_GET.sViewport=="registerFC" ||
	$_GET.sViewport=="login" ||
	$_GET.sViewport=="register2shipping" ||
	$_GET.sViewport=="register3" ||
	$_GET.sViewport=="admin" ||
	$_GET.sViewport=="register2" ||
	$_GET.sViewport=="register1" ||
	$_GET.sViewport=="password" ||
	$_GET.sViewport=="sale" ||
	$_GET.sViewport=="note" ||
	$_GET.sViewport=="tellafriend" ||
	$_GET.sViewport=="ticketview"
}
	id="hideLeft"
{elseif $_GET.sCategory==$sCategoryStart}
	id="hideRight"
{elseif $_GET.sViewport=="detail"}
	id="hideRight" class="bg_right"
{/if}>
<a name="top"></a>

<!-- Ausgabe Content Start -->
<div id="container">

{if $sLanguages|@count > 1 || $sCurrencies|@count > 1}
<div class="languages">
 
{if $sCurrencies|@count > 1 && ($_POST.sAction!="doSale")}
 <!-- Ausgabe Währungen  -->
  <div class="container_waehrung">
   <div class="currency_label">{* sSnippet: currency *}{$sConfig.sSnippets.sIndexcurrency}</div> 
   {foreach from=$sCurrencies item=sCurrency}
    <!-- Währungsauswahl -->
    <form method="post" class="form_currency" action="">
     <input type="hidden" name="sCurrency" value="{$sCurrency.id}"/> 
     <input type="submit" value="{$sCurrency.currency}" class="currency_val" />
    </form>
    <!-- // Währungsauswahl -->
   {/foreach}
  </div>
 <!-- Ausgabe Währungen -->
{/if}
{if $sLanguages|@count > 1}
<!-- Ausgabe Länderflaggen -->
  <div class="container_language">
   <div class="lang_label">{* sSnippet: language *}{$sConfig.sSnippets.sIndexlanguage}</div> 
 
   {foreach from=$sLanguages item=sLanguage}
    {if $sLanguage.flag}
     <div class="flag{$sLanguage.isocode}">
     {$sLanguage.isocode}
     </div>
    {/if} 
   {/foreach}
 
   <form id="sLanguageForm" method="post" action="{$sStart}">
    <select name="sLanguage" onchange="$('sLanguageForm').submit();" class="lang_select">
     {foreach from=$sLanguages item=sLanguage}
      <option value="{$sLanguage.id}" {if $sLanguage.flag}selected="selected"{/if}>
      {if $sLanguage.isocode == de}{* sSnippet: german *}{$sConfig.sSnippets.sIndexgerman}{elseif $sLanguage.isocode == en}{* sSnippet: english *}{$sConfig.sSnippets.sIndexenglish}{elseif $sLanguage.isocode == fr}{* sSnippet: french *}{$sConfig.sSnippets.sIndexfrench}{else}{$sLanguage.name}{/if}
      </option>
     {/foreach}
    </select>
   </form>
   </div>  
  
  
{/if}
 </div>
 <!-- Ausgabe Länderflaggen -->
 
{/if}

	<div class="header"></div>

	<!-- logo -->
	<a href="{$sBasefile}" id="logo" title="{$sShopname} - {* sSnippet: back to home *}{$sConfig.sSnippets.sIndexbacktohome}">{$sShopname}</a>
	<!-- /logo -->

	<!-- Elemente / Links im Header -->

	<!-- Navigation Hauptkategorien -->
	<div id="navigation">
		<ul>
			<li {if $_GET.sCategory==$sCategoryStart}class="active"{else} style="background-color:#F3F3F3;color:#777"{/if}>
				<a href="{$sBasefile}" title="{* sSnippet: home *}{$sConfig.sSnippets.sIndexhome}" {if $_GET.sCategory==$sCategoryStart}class="active"{else}style="background-color:#F3F3F3;color:#777"{/if}>
					{* sSnippet: home *}{$sConfig.sSnippets.sIndexhome}
				</a>
			</li>
            {foreach from=$sMainCategories item=sMainCategory}
				<li {if $sMainCategory.flag} class="active"{elseif $sMainCategory.blog} style="background-color:#F3F3F3;color:#777"{/if}>
                	<a href="{$sMainCategory.link}" title="{$sMainCategory.1}" {if $sMainCategory.flag} class="active"{elseif $sMainCategory.blog} style="background-color:#F3F3F3;color:#777"{/if}>{$sMainCategory.1}</a>
                </li>
			{/foreach}
		</ul>
	</div>
	<!-- /Navigation Hauptkategorien -->
	
	
	
	<!-- Weitere Shop-Optionen -->
	<div id="shopnav">
	
			<ul class="myaccount_ul">
			 <li class="myaccount">
			 	<a href="{$sBasefile}?sViewport=admin&sUseSSL=1" title="{* sSnippet: view my account *}{$sConfig.sSnippets.sIndexviewmyaccount}">{* sSnippet: my account *}{$sConfig.sSnippets.sIndexaccount}</a>
			 </li>
			</ul>
			
 			<ul class="mypage_ul">
			 <li class="mypage">
			 	<a href="{$sBasefile}?sViewport=note" title="{* sSnippet: show notepad *}{$sConfig.sSnippets.sIndexshownotepad}">{* sSnippet: notepad *}{$sConfig.sSnippets.sIndexnotepad}</a>
			 </li>
 			 <li>
				<div class="note_active">{$sNotesQuantity} {* sSnippet: article *}{$sConfig.sSnippets.sIndexarticle}</div>
			 </li>
			</ul>
			
			<ul class="mybasket_ul">
			 <li class="{if $sBasket.Quantity}mybasket_full{else}mybasket{/if}"><a href="{$sBasefile}?sViewport=basket" title="{* sSnippet: my basket *}{$sConfig.sSnippets.sIndexmybasket}">
				 {* sSnippet: basket *}{$sConfig.sSnippets.sIndexbasket}
				 
				 </a>
				<div class="basket_active">{$sBasket.Quantity} {* sSnippet: article *}{$sConfig.sSnippets.sIndexarticle}</div>
			 </li>
		</ul>
		
	</div>
	<!-- /Weitere Shop-Optionen --> 
	
	
	<!-- Suche -->
	<div id="hidesearch"><div id="searchresults"></div></div>
	<div id="searchcontainer">
	<div class="inner_searchcontainer">
	<p>{$sConfig.sSnippets.sSearchSearch}</p>
		<form action="{$sStart}" method="get" id="searchform">
			<input type="hidden" name="sViewport" value="searchFuzzy" />
			{*<input type="hidden" name="sFilter_category" value="{$sCategoryStart}" />*}
			<input type="hidden" name="sLanguage" value="{$sCurrentLanguage}" />
			<input type="text" name="sSearch" id="searchfield" value="{* sSnippet: search *}{$sConfig.sSnippets.sIndexsearch}" maxlength="30" autocomplete="off" />
			<input type="submit" value="{* sSnippet: searchbutton *}{$sConfig.sSnippets.sIndexsearchbutton}" id="submit_search" onclick="hideSearchAfterClick();"/>	
		</form>
	</div>
	</div>
	
	{*
	Shopware 2.1
	Compare articles
	*}
	<div id="compareContainerAjax">
		{include file="ajax/index_top_comparisons.tpl"}
	</div>

	<!-- breadcrumb -->
	<div id="breadcrumb">	
			{* sSnippet: you are here *}{$sConfig.sSnippets.sIndexyouarehere} <a href="{$sStart}">{$sShopname}</a> 
			{foreach name=breadcrumbObj from=$sBreadcrumb item=breadcrumb}
				{if $smarty.foreach.breadcrumbObj.last == true}
					/ <a href="#" title="{$breadcrumb.name}" class="last">{$breadcrumb.name}</a>
				{else} 
					/ <a href="{$breadcrumb.link}" title="{$breadcrumb.name}">{$breadcrumb.name}</a>
				{/if}
			{/foreach}
	</div>
	<!-- /breadcrumb -->
		
	<!-- Beginn Seiten-Inhalt -->	
	<div id="content" class="clearfix">

		{if ($_GET.sViewport=="admin" && $_GET.sAction=="orders") || $_GET.sViewport=="ticketview"}
			{* Wenn Bestellübersicht *}
		{elseif $_GET.sViewport=="basket" OR $_GET.sViewport=="registerFC" OR $_GET.sViewport=="login" OR $_GET.sViewport=="register2shipping" OR $_GET.sViewport=="register3" OR $_GET.sViewport=="admin" OR $_GET.sViewport=="register2" OR $_GET.sViewport=="register1" OR $_GET.sViewport=="password" OR $_GET.sViewport=="sale" OR $_GET.sViewport=="note" OR $_GET.sViewport=="tellafriend" OR $_GET.sViewport=="ticketview"}
			{* Wenn einer der oben definierten Viewports *}
		{else}
			{* Bei jedem anderen Viewport *}
			<!-- LINKE SPALTE -->	
			<div id="left">
				{if $_GET.sViewport=="searchFuzzy"}
					{* Bei Viewport EQ intelligente Suche, Links Filtermöglichkeiten Hersteller/Preis einblenden *}
					{$sContainerRight}
				{else}
				{include file="category/category_left_campaigns.tpl" sCategoryCampaigns=$sCampaigns.leftTop}
					<!-- Augabe der Kategorien Links -->	
					<div class="cat_box">
					{if !$_GET.sCategory OR $_GET.sCategory == $sCategoryStart}
						{* Auf Startseite Hauptkategorien *}
						<div class="box3">
						<ul id="mainbuttons">
				            {foreach from=$sMainCategories item=sMainCategory}
								<li{if $sMainCategory.flag} class="active"{/if}>
				                	<a href="{$sMainCategory.link}" title="{$sMainCategory.1}" style="text-transform:uppercase;" {if $sMainCategory.flag}class="active"{/if}>{$sMainCategory.1}</a>
				                </li>
							{/foreach}
							</ul>
						</div>
					{else}
						{* Ansonsten Ausgabe der Unterkategorein *}
						<div class="box3">
						<ul id="mainbuttons">
				            {foreach from=$sMainCategories item=sMainCategory}
								<li{if $sMainCategory.flag} class="active"{/if}>
				                	<a href="{$sMainCategory.link}" title="{$sMainCategory.1}" style="text-transform:uppercase;" {if $sMainCategory.flag}class="active"{/if}>{$sMainCategory.1}</a>
				                </li>
								{if $sMainCategory.flag}
								<ul id="categories">
									{if $sCategories}
										{* Rekursives Laden der verschiedenen Hierarchie-Ebenen *}
										{include file="category/category_maincategories.tpl" sCategories=$sCategories}
									{/if}
								</ul>
								{/if}
							{/foreach}
							</ul>
						</div>
					{/if}
					
					<!-- /Augabe der Kategorien Links -->	
				{include file="category/category_left_campaigns.tpl" sCategoryCampaigns=$sCampaigns.leftMiddle}					
       				<!-- Augabe von Links zu statischen Seiten  -->	
			        <div class="box3">
						<ul id="servicenav">
						{foreach from=$sMenu.gLeft item=menuitem}
							<li><a href="{if $menuitem.link}{$menuitem.link}{else}{$sBasefile}?sViewport=custom&cCUSTOM={$menuitem.id}{/if}" title="{$menuitem.description}" {if $menuitem.target}target="{$menuitem.target}"{/if}>{$menuitem.description}</a></li>
						{/foreach}
						</ul>
						<div class="box3_cap"></div>
					</div>
					<!-- / Augabe von Links zu statischen Seiten  -->	
        
</div>
					<!-- PayPal Logo -->     
					{if $sConfig.sPaypalLogo}
						<div class="box3" style="margin-top:1px;padding:15px 0 17px 0;">
							<center>
								<a href="#" onclick="javascript:window.open('https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=500');">
									<img  src="../../media/img/default/store/lockbox_100x45.gif" border="0" alt="{$sConfig.sSnippets.sIndexPayPallabel}">
								</a>
						    </center>
						</div>
					{/if}
					<!-- /PayPal  Logo -->
					
	        		<!-- Trusted Shop Logo -->     
					{if $sConfig.sTSID}
						<div class="box3" style="margin-top:1px;padding:15px 0 17px 0;">
							<center><a href="https://www.trustedshops.de/de/tshops/seal_de.php3?shop_id={$sConfig.sTSID}" title="{* sSnippet: trusted shops label - please check validity here! *}{$sConfig.sSnippets.sIndextrustedshopslabel}" target="_blank">
						    <img src="../../../../0/de/media/img/default/store/logo_trusted_shop.gif" title="{* sSnippet: trusted shops label - please check validity here! *}{$sConfig.sSnippets.sIndextrustedshopslabel}" /></a>
						    <p style="padding:5px;">{* sSnippet: Certified online shop with money-back guarantee from Trusted Shops. Click on the seal in order to verify the validity. *}{$sConfig.sSnippets.sIndexcertifiedonlineshop}</p>
						    </center>
						</div>
					{/if}
					<!-- /Trusted Shop Logo -->  
					
					<!--Zuletzt angesehene Artikel -->
					{if $sLastArticles}
						{include file="articles/article_viewlast.tpl" sLastArticles=$sLastArticles}
					{/if}	
					<!-- /Zuletzt angesehene Artikel -->
				{include file="category/category_left_campaigns.tpl" sCategoryCampaigns=$sCampaigns.leftBottom}
				{/if}{*  /if $_GET.sViewport=="searchFuzzy" *}
      
        </div>
		<!-- / LINKE SPALTE -->
		{/if}{* / Bei jedem anderen Viewport *}
		
		
		<!-- START MITTLERER BEREICH HEADLINE -->
		{if ($sCategoryListing==true && $_GET.sViewport=="cat")} 
		{* Auf Kategorieseite Anzahl Ergebnisse und Kategorie-Name ausgeben *}
			<div class="subheadline">
				{foreach name=breadcrumbObj from=$sBreadcrumb item=breadcrumb}
					{if $smarty.foreach.breadcrumbObj.last == true}
						{$breadcrumb.name}
					{/if}
				{/foreach} 
				({* sSnippet: site *}{$sConfig.sSnippets.sIndexsite} {if $_GET.sPage}{$_GET.sPage}{else}1{/if} {* sSnippet: from *}{$sConfig.sSnippets.sIndexfrom} {$sNumberPages})
			</div>
		{/if}
		
		{if $sSupport.sElements && $_GET.sViewport!="ticketview"}
		{* Auf Formularseiten hier Subheadline / Title des Formulars ausgeben *}
			<div class="subheadline">{$sSupport.name}</div>
		{/if}
	
		{if $_GET.sViewport=="custom"}
		{* Auf statischen Seiten letzten Eintrag des Verlaufs (Name der statischen Seite) ausgeben *}
			<div class="subheadline">
				{foreach name=breadcrumbObj from=$sBreadcrumb item=breadcrumb}
					{if $smarty.foreach.breadcrumbObj.last == true}
						{$breadcrumb.name}
					{/if}
				{/foreach}
			</div>
		{/if}
	
		{if $_GET.sViewport=="searchFuzzy" && $sSearchResults.sArticles}
		{* Bei Suche mit Ergebnissen Anzahl der Ergebnisse + Filtermöglichkeiten ausgeben *}
			<div class="searchheadline">
			{* sSnippet: to *}{$sConfig.sSnippets.sIndexto} &bdquo;{$sRequests.sSearchOrginal}&rdquo; {* sSnippet: were *}{$sConfig.sSnippets.sIndexwere} {$sSearchResults.sArticlesCount} {* sSnippet: articles found *}{$sConfig.sSnippets.sIndexarticlesfound}
			</div>
			{include file="search/filter_category.tpl"}
		{/if}
	
		{if $_GET.sViewport=="admin" && $sUserMail}
		{* Im Adminbereich Begrüßungstext für Kunden ausgeben *}
			<div class="adminheadline">{* sSnippet: hallo *}{$sConfig.sSnippets.sIndexhello} {$sUserName} {if $sUserGroupText}( {$sUserGroupText} ){/if} {* sSnippet: {and welcome to your personal *}{$sConfig.sSnippets.sIndexwelcometoyour} {$sShopname} {* sSnippet: client account *}{$sConfig.sSnippets.sIndexclientaccount}</div>
		{/if}
		<!-- / START MITTLERER BEREICH HEADLINE -->
	
		<!-- START MITTLERER BEREICH INHALT / CONTENT -->
		<div id="center">
		{* sContainer enthält das im Viewport definierte Template z.B. Artikeldetailseite *}
			
			{$sContainer}

		</div>
		<!-- / START MITTLERER BEREICH INHALT / CONTENT -->
		
		<!-- START (OPTIONALE) RECHTE SPALTE -->
		{if $_GET.sCategory==$sCategoryStart OR ($sCategoryListing==true && $_GET.sViewport=="cat") || $_GET.sViewport=="searchFuzzy"} 
			{* Auf Startseite / Kategorieseiten und Suche keine rechte Spalte einblenden *}
			{if $_GET.sViewport=="detail"}
			<div id="right">
				{* sContainerRight wird im Viewport definiert *}
				{$sContainerRight}
			</div>
			{/if}
		{else}
			<div id="right">
				{* sContainerRight wird im Viewport definiert *}
				{$sContainerRight}
			</div>
		{/if}
			
	    {if $_GET.sViewport=="registerFC"}
	    	{* Rechte Spalte für Registrierung *}
			<div id="right">
				{include file="register/register_right.tpl"}
			</div>
		{/if}
	

	
		{if ($_GET.sViewport=="admin" || $_GET.sViewport=="note") && $sUserMail}
		{* Rechte Spalte im Adminbereich *}
			<!-- col_right2 -->
			<div class="col_right2">
				<!-- adminbox -->
				<div class="adminbox">
					<p class="heading">{* sSnippet: my account *}{$sConfig.sSnippets.sIndexaccount}</p>
					<ul id="submenu">
						<li><a href="{$sBasefile}?sViewport=admin&sUseSSL=1">{* sSnippet: overview *}{$sConfig.sSnippets.sIndexoverview}</a></li>
						<li><a href="{$sBasefile}?sViewport=admin&sAction=orders&sUseSSL=1">{* sSnippet: my orders *}{$sConfig.sSnippets.sIndexmyorders}</a></li>
						<li><a href="{$sBasefile}?sViewport=admin&sAction=downloads&sUseSSL=1">{* sSnippet: my instant downloads *}{$sConfig.sSnippets.sIndexmyinstantdownloads}</a></li>
						<li><a href="{$sBasefile}?sViewport=admin&sAction=billing&sUseSSL=1">{* sSnippet: change billing address *}{$sConfig.sSnippets.sIndexchangebillingaddress}</a></li>
						<li><a href="{$sBasefile}?sViewport=admin&sAction=shipping&sUseSSL=1">{* sSnippet: change delivery address *}{$sConfig.sSnippets.sIndexchangedeliveryaddress}</a></li>
						<li><a href="{$sBasefile}?sViewport=admin&sAction=payment&sUseSSL=1">{* sSnippet: change payment *}{$sConfig.sSnippets.sIndexchangepayment}</a></li>		
						{if $sTICKETLicensed}
							<li><a href="{$sBasefile}?sViewport=ticketview&sUseSSL=1">{* sSnippet: support manage *}{$sConfig.sSnippets.sTicketSysSupportManagement}</a></li>
						{/if}
						<li><a href="{$sBasefile}?sViewport=note">{$sConfig.sSnippets.sBasketnotepad}</a></li>
						<li><a href="{$sBasefile}?sViewport=logout">{* sSnippet: logout *}{$sConfig.sSnippets.sIndexlogout}</a></li>
					</ul>	
					
				</div>
				<!-- /adminbox -->
			</div>
			<!-- /col_right2 -->
		{/if}
	
		
		{if ($_GET.sViewport=="ticketview") && $sUserMail}
		{* Rechte Spalte im Adminbereich / Supportverwaltung *}
			<!-- col_right2 -->
			<div class="col_right2">
				<!-- adminbox -->
				<div class="adminbox">
					<p class="heading">{* sSnippet: support manage *}{$sConfig.sSnippets.sTicketSysSupportManagement}</p>
					<ul id="submenu">
						<li><a href="{$sBasefile}?sViewport=admin&sUseSSL=1">{* sSnippet: support back *}{$sConfig.sSnippets.sSupportback}</a></h3></li>	
						<li><a href="{$sBasefile}?sViewport=ticketview&sAction=request&sUseSSL=1">{* sSnippet: support order *}{$sConfig.sSnippets.sTicketSysSupportOrder}</a></li>		
						<li><a href="{$sBasefile}?sViewport=ticketview&sAction=overview&sUseSSL=1">{* sSnippet: support overview *}{$sConfig.sSnippets.sSupportoverview}</a></li>		
						<li><a href="{$sBasefile}?sViewport=logout">{* sSnippet: logout *}{$sConfig.sSnippets.sIndexlogout}</a></li>	
					</ul>	
				</div>
				<!-- /adminbox -->
			</div>
			<!-- /col_right2 -->
		{/if}
	
 
		{if $_GET.sViewport=="basket"}	
			{include file="basket/basket_right.tpl" sBasket=$sBasket}
	 	{/if}
	 
		{if $_GET.sViewport=="sale"}
		{* Rechte Spalte auf Kassenseite *}
			{if !$showConfirmation}
			{* Vor Bestellabschluss *}
				<div id="right" style="width:194px;">{include file="register/register_right2.tpl"}</div>
			{else}
			{* Nach Bestellabschluss *}
				<div id="right" style="width:194px;">{include file="register/register_right3.tpl"}</div>
			{/if}
		{/if}
	

	</div>
	<!-- Ende Seiten-Inhalt -->	
	
	<div class="fixfloat">
	</div>

	
	<div id="compare_bigbox_overlays" style="position:fixed;top:0;left:0;display:none;z-index:8000;background-color:black;width:100%;height:100%;opacity:0;" onclick="hideCompareList()"></div>
	<div id="compare_bigbox" style="display:none;z-index:9000;overflow-y:scroll;border:3px solid #999999;"></div>
	
</div>
<!-- /Ende Container / Content  -->

<div class="fixfloat">
</div>

<!-- footer -->
<div id="footer">
	<div id="footer_center">
		{if $sConfig.sARTICLESOUTPUTNETTO}
			<p>{* sSnippet: * all prices exclude VAT and *}{$sConfig.sSnippets.sAllpricesexcludevat}</p>
		
		{else}
			<p>{* sSnippet: * all prices incl. VAT plus *}{$sConfig.sSnippets.sAllpricesinclvat}</p>
		{/if}
		
		<div class="horline4"></div>

		
		<p>
			{foreach from=$sMenu.gBottom item=menuitem  key=key name="counter"}
				<a href="{if $menuitem.link}{$menuitem.link}{else}{$sBasefile}?sViewport=custom&cCUSTOM={$menuitem.id}{/if}" title="{$menuitem.description}" {if $menuitem.target}target="{$menuitem.target}"{/if}>{$menuitem.description}</a> |
		
			{/foreach}
			

			
			
			
		</p>
		
		<p>
			{foreach from=$sMenu.gBottom2 item=menuitem key=key name="counter"}
				<a href="{if $menuitem.link}{$menuitem.link}{else}{$sBasefile}?sViewport=custom&cCUSTOM={$menuitem.id}{/if}" title="{$menuitem.description}" {if $menuitem.target}target="{$menuitem.target}"{/if}>{$menuitem.description}</a>
				{if $key==$smarty.foreach.counter.total-1 OR $key==$smarty.foreach.list.total-1}
				
				{else}
				|
				{/if}
			{/foreach}
		</p>  
		
		<div class="fixfloat"></div>
		
				
	</div>
		<div class="shopware">
			<p>{* sSnippet: shopware.ag copyright 2009 - all rights reserved. *}{$sConfig.sSnippets.sIndexcopyright}</p>
			{* sSnippet: realized with *}{$sConfig.sSnippets.sIndexrealizedwith}<a href="http://www.shopware.ag" target="_blank" title="{* sSnippet: realized with the Webshop system of Hamann-media *}{$sConfig.sSnippets.sIndexrealizedwiththeshopsystem}"> {* sSnippet: shopware *}{$sConfig.sSnippets.sIndexshopware}</a>
		</div>
</div>
<!-- /footer -->
</body>
</html>