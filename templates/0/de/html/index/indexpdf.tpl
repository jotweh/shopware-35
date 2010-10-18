<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>

</title>

<link href="../../media/css/basic.css" rel="stylesheet" type="text/css" media="screen" />

</head>

<body id="hideRight" style="background:none;"">

<!-- Ausgabe Content Start -->
<div id="container">
	
	<!-- logo -->
	<h1 id="logo"><a href="{$sBasefile}" title="{$sShopname} - {* sSnippet: back to home *}{$sConfig.sSnippets.sIndexbacktohome}">{$sShopname}</a></h1>
	<!-- /logo -->

	<!-- Elemente / Links im Header -->

	
	<!-- Weitere Shop-Optionen -->

	<!-- /Weitere Shop-Optionen --> 
		
	{* Ausgabe von Shop-Fehlermeldungen / Kritische Fehler etc. *}
	{$sCoreWarning}


		
	<!-- Beginn Seiten-Inhalt -->	
	<div id="content" class="clearfix">
		
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
		<!-- / START MITTLERER BEREICH HEADLINE -->
	
		<!-- START MITTLERER BEREICH INHALT / CONTENT -->
		<div id="center">

		
{* DETAIL_BOX_TOP *}

{* /DETAIL_BOX_TOP *}

{* DETAIL_BOX START *}
<div class="detail_box">


{* DETAIL_COL1 *}
	<div class="detail_col1">

    
	{if $sArticle.image.src.4}
        <a href="{$sArticle.image.src.original}" rel="lightbox[photos]" title="{* sSnippet: to see on the picture *}{$sConfig.sSnippets.sIndexonthepicture} {$sArticle.articleName}" class="main_image">
        <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" border="0" title="{$sArticle.articleName}" />
        </a>
	{else}
		<img src="../../media/img/de/layout/no_picture.jpg" alt="{$sArticle.articleName}" width="62" height="63" />
	{/if}	
    
    
    {if $sArticle.images}
	<div class="thumb_box">
	{foreach from=$sArticle.images item=sArticleImage}
	<a href="{$sArticleImage.src.original}" rel="lightbox[photos]" title="{* sSnippet: to see on the picture *}{$sConfig.sSnippets.sIndexonthepicture} {$sArticle.articleName}" style="background-color:#fff; border:1px solid #efefef;"><img src="{$sArticleImage.src.1}" title="{* sSnippet: to see on the picture *}{$sConfig.sSnippets.sIndexonthepicture} {$sArticle.articleName}" /></a>
	{/foreach}
	<div class="clearfix"></div>
	</div>
{/if}


    </div>
{* /DETAIL_COL1 *}        


{* DETAIL_COL2 *}
	<div class="detail_col2">
		<h1 class="detail_name">{$sArticle.articleName}</h1>
		
        {* ARTICLE_DETAILS *}
        	<div id="article_details">
  				{include file="articles/article_details_data_pdf.tpl" sArticle=$sArticle}
  			</div>
		{* /ARTICLE_DETAILS *}

	<div id="detail_more"></div>
	
		{if $sArticle.attr10 AND ($sArticle.attr10=="ohne Altersbeschränkung" OR $sArticle.attr10=="ab 6 Jahre" OR $sArticle.attr10=="ab 12 Jahre" OR $sArticle.attr10=="ab 16 Jahre" OR $sArticle.attr10=="ab 18 Jahre")}
            {* USK_FREIGABE *}
                            {if $sArticle.attr10 eq "ohne Altersbeschränkung" OR $sArticle.attr10 eq "keine Auswahl"}	
                            <img src="../../media/img/default/usk/usk_0.gif" alt="{* sSnippet: released *}{$sConfig.sSnippets.sIndexreleased}" width="101" height="101" border="0" title="{* sSnippet: no age restriction *}{$sConfig.sSnippets.sIndexnoagerestriction}" />
                            {elseif $sArticle.attr10 eq "ab 6 Jahre"}
                            <img src="../../media/img/default/usk/usk_6.gif" alt="{* sSnippet: released *}{$sConfig.sSnippets.sIndexreleased}" width="101" height="101" border="0" title="{* sSnippet: released from 6 years *}{$sConfig.sSnippets.sIndexreleasedfrom6years}" />
                            {elseif $sArticle.attr10 eq "ab 12 Jahre"}
                            <img src="../../media/img/default/usk/usk_12.gif" alt="{* sSnippet: released *}{$sConfig.sSnippets.sIndexreleased}" width="101" height="101" border="0" title="{* sSnippet: released from 12 years *}{$sConfig.sSnippets.sIndexreleasedfrom12years}" />
                            {elseif $sArticle.attr10 eq "ab 16 Jahre"}
                            <img src="../../media/img/default/usk/usk_16.gif" alt="{* sSnippet: released *}{$sConfig.sSnippets.sIndexreleased}" width="101" height="101" border="0" title="{* sSnippet: released from 16 years *}{$sConfig.sSnippets.sIndexreleasedfrom16years}" />
                            {elseif $sArticle.attr10 eq "ab 18 Jahre"}
                            <img src="../../media/img/default/usk/usk_18.gif" alt="{* sSnippet: released *}{$sConfig.sSnippets.sIndexreleased}" width="101" height="101" border="0" title="{* sSnippet: released from 18 years *}{$sConfig.sSnippets.sIndexreleasedfrom18years}" />
                            {/if}<br />			
            {* /USK_FREIGABE *}
		{/if}
		

         
         {* ADDITIONAL ATTRIBUTES *}
		 {if $sArticle.attr13 || $sArticle.attr14 || $sArticle.attr12 || $sArticle.attr18 || $sArticle.attr20}
			<br />
				{if $sArticle.attr4 AND $sArticle.attr4 != "nodata"}
				<strong>{* sSnippet: language *}{$sConfig.sSnippets.sIndexlanguage}</strong> {$sArticle.attr4}<br />
				{/if}
				{if $sArticle.attr13}
				<strong>{* sSnippet: page number *}{$sConfig.sSnippets.sIndexpagenumber}</strong> {$sArticle.attr13}<br />
				{/if}
				{if $sArticle.attr14}
				<strong>{* sSnippet: extra *}{$sConfig.sSnippets.sIndexextra}</strong> {$sArticle.attr14}<br />
				{/if}
				{if $sArticle.attr12}
				<strong>{* sSnippet: printing *}{$sConfig.sSnippets.sIndexprinting}</strong> {$sArticle.attr12}<br />
				{/if}
				{if $sArticle.attr18}
				<strong>{* sSnippet: cover *}{$sConfig.sSnippets.sIndexcover}</strong> {$sArticle.attr18}<br />
				{/if}
				{if $sArticle.attr20}
				<strong>{* sSnippet: appear *}{$sConfig.sSnippets.sIndexappear}</strong> {$sArticle.attr20}
				{/if}
		
		{/if}
	</div>
    {* /DETAIL_COL2 *}


    {* DETAIL_COL3 *}
	<div class="detail_col3">
	

    {* BUY_BOX_START *}
	<div class="buy_box" style="border: 1px solid #efefef;">

	{if $sArticle.sConfigurator}
		{foreach from=$sArticle.sConfigurator item=sConfigurator}
			<p><strong>{$sConfigurator.groupname}</strong></p>
			{if $sConfigurator.groupimage}<img src="../gfx_produkt/{$produktkonfigurator.gruppen_grafik}" style="float:left" />{/if}
			<p class="groupdescription">{$sConfigurator.groupdescription}</p>
			<ul>
			{foreach from=$sConfigurator.values item=configValue}
				<li>{$configValue.optionname}{if $configValue.upprice} {if $configValue.upprice > 0}+{/if}{$configValue.upprice}{$sConfig.sCURRENCY}{/if}</li>
			{/foreach}
			
			</ul>
		{/foreach}

	{/if}
	
{if $sArticle.attr10 != "ab 18 Jahre" OR $sGroup.groupkey=="H" OR $sGroup.groupkey=="USK"}
<!-- BASKET OR $sGroup.groupkey="H" OR $sGroup.groupkey="USK"-->
<!-- ############################### -->
	
{* OUTPUT OF ORDERNUMBER / NAME / PRICE *}
	<ul>
		<li><strong>{* sSnippet: order number *}{$sConfig.sSnippets.sIndexordernumber} </strong>{$sArticle.ordernumber}<br/>{if $sArticle.additionaltext}{$sArticle.additionaltext} - &euro; {$sArticle.price}{else}{$sArticle.articleName} - {$sArticle.price}{/if}</li>
	{foreach name=line from=$sArticle.sVariants item=variante}
		<li><strong>{* sSnippet: order number *}{$sConfig.sSnippets.sIndexordernumber} </strong>{$variante.ordernumber}<br/>{if $variante.additionaltext}{$variante.additionaltext} - &euro; {$variante.price}{else}{$variante.ordernumber} - &euro; {$variante.price}{/if}</li>
	{/foreach}
	</ul>
<div class="fixfloat"></div>
{* /OUTPUT OF ORDERNUMBER / NAME / PRICE *}

	

{* ADD_TO_BASKET *}			

{* /ADD_TO_BASKET *}

{else}
<div align="center">
<a onmouseover="$('usk18notice').setStyle('display','block');" onmouseout="$('usk18notice').setStyle('display','none');"><img src="../../media/img/default/usk/freischalten.gif"></a>
</div>
{/if}
<div id="usk18notice" style="display:none;background-color:#FFF;left:150px;position:absolute;width:600px;opacity:0.9;border: 2px solid #F00;top:200px;padding:10px;height:350px;color:#000">
<strong>{* sSnippet: how can i acquire the plays which are released only from 18 years? *}{$sConfig.sSnippets.sIndexhowcaniacquire}</strong>
<br /><br />
<p>
{* sSnippet: For reasons of information we display in our on-line shop also the plays which own no gift suitable for young people. However, there is for customers who have already completed the eighteenth year, even though a possibility to purchase these plays. Now you can order with arktis.de also quite simply USK 18 plays above the postal dispatch way. To fulfil the requirements of the protection of children and young people-sedate you must be personalised in addition simply by Postident. This works quite simply: *}{$sConfig.sSnippets.sIndexforreasonofinformation}
</p>
<br />
<p>
{* sSnippet: 1. They load the PDF registration form in your customer area and print out it. Please, present this form together with your identity card or passport in a branch of the German post. Mark the appropriate field and sign the form. *}{$sConfig.sSnippets.sIndexyouloadthepdf}
</p><br />
<p>
{* sSnippet: 2. POSTIDENT: The German post provides a POSTIDENT form in the confirmation of your majority. Please, sign this also. Then the German post sends both signed documents to ARKTIS. *}{$sConfig.sSnippets.sIndexpostident}
</p><br /><p>
{* sSnippet: 3. Activation: As soon as both satisfactory forms are given to us and provided that your signatures match, we switch you for plays from 18 freely. Afterwards we immediately send you a confirmation email. Then you can quite simply order the USK 18 title comfortably about the web shop under www.arktis.de. *}{$sConfig.sSnippets.sIndexactivation}
</p>
</div>

	
	</div>
	{* /BUY_BOX_END *}
	
	<ul id="buybox">

	</ul>
	
	</div>
	{* /DETAIL_COL3 *}
<div class="fixfloat"></div>
</div>
{* /DETAIL_BOX *}	
<div class="fixfloat"></div>
{* DETAILINFO *}
	<div class="detailinfo">
    
{* DETAILINFO_COL1 *}
	<div class="detailinfo_col1">
		<div style="width:500px;">
		<h2 style="font-size:15px;">{* sSnippet: product information *}{$sConfig.sSnippets.sIndexproductinformations} "{$sArticle.articleName}"</h2>
		
		{$sArticle.description_long|nl2br|replace:"<table":"<table id=\"zebra\""|replace:"<b>":""|replace:"</b>":""}

{if $sArticle.attr9}
			<h2 style="font-size:15px;">{* sSnippet: system requirements for *}{$sConfig.sSnippets.sIndexsystemrequirementsfor} "{$sArticle.articleName}"</h2>
			<p>{$sArticle.attr9|nl2br}</p>
		{/if}	
				        
        
        {if $sArticle.sDownloads}
		<h2 style="font-size:15px;">{* sSnippet: available downloads *}{$sConfig.sSnippets.sIndexavailabledownloads}</h2>
		{foreach from=$sArticle.sDownloads item=download}
		
			<a href="{$download.filename}" target="_blank" class="ico link">{* sSnippet: download *}{$sConfig.sSnippets.sIndexdownload} {$download.description}</a><br />
	
		{/foreach}
		{/if}
{if $sArticle.attr5}
			<div id="unser_kommentar">
				<h3>{* sSnippet: our comment to *}{$sConfig.sSnippets.sIndexourcommentto} "{$sArticle.articleName}"</h3>
					{$sArticle.attr5}
				
			</div>	
			{/if}	


{* ACCESSORIES *}
		{if $sArticle.sRelatedArticles}
            <div>
                <!-- HIERZU PASSENDE ARTIKEL -->
                
                
                    <h1>{* sSnippet: suitable articles *}{$sConfig.sSnippets.sIndexsuitablearticles}</h1>
                        {foreach from=$sArticle.sRelatedArticles item=sArticleSub key=key name="counter"}
                                {include file="articles/article_box_3col_related.tpl" sArticle=$sArticleSub}
                        {/foreach}
                    <div class="fixfloat"></div>
            </div>
            {/if}
		{* /ACCESSORIES *}

</div>




<div class="fixfloat"></div>
</div>
{* /DETAILINFO_COL1 *}



{* DETAILINFO_COL2 *}	
    <div class="detailinfo_col2">
        {if $sArticle.sSimilarArticles}
    
            {* ARTICLE_SIMILAR *}
            <div id="aehnlich" class="box">
                <h2>{* sSnippet: similar articles *}{$sConfig.sSnippets.sIndexsimilararticles}</h2>
                {foreach name=line from=$sArticle.sSimilarArticles item=sSimilarArticle key=key name="counter"}
                {include file="articles/article_box_similar.tpl" sArticle=$sSimilarArticle}
                {/foreach}
                <div class="boxcap2_blue"></div>
            </div>
            {* /ARTICLE_SIMILAR *}
            
        {/if}
    </div>


<div class="fixfloat"></div>
<div class="detailinfocap"></div>
</div>


		</div>
		<!-- / START MITTLERER BEREICH INHALT / CONTENT -->
		
	</div>
	<!-- Ende Seiten-Inhalt -->	
	<div class="fixfloat">
	</div>

</div>
<!-- /Ende Container / Content  -->
<div class="fixfloat">
</div>

</body>
</html>