{* DETAIL_BOX_TOP *}
		<div class="detail_box2_top">
			<div class="article_back">{if $sArticle.sNavigation.sPrevious}
				<a href="{$sArticle.sNavigation.sPrevious.link}" title="{$sArticle.sNavigation.sPrevious.name}" class="article_back">{* sSnippet: back *}{$sConfig.sSnippets.sArticleback}</a>
			{/if}</div>
			<p class="article_overview">{$sArticle.sNavigation.sCurrent.position}{* sSnippet: of *} {$sConfig.sSnippets.sArticleof1} {$sArticle.sNavigation.sCurrent.count} (<a href="{$sArticle.sNavigation.sCurrent.sCategoryLink}" title="{$sArticle.sNavigation.sCurrent.sCategoryName}">{* sSnippet: overview *}{$sConfig.sSnippets.sArticleoverview}</a>)</p>
			<div class="article_next">{if $sArticle.sNavigation.sNext}
				<a href="{$sArticle.sNavigation.sNext.link}" title="{$sArticle.sNavigation.sNext.name}" class="article_next">{* sSnippet: next *}{$sConfig.sSnippets.sArticlenext}</a>
			{/if}</div>
		</div>
{* /DETAIL_BOX_TOP *}
{* DETAIL_BOX START *}
<div class="detail_box">


{* ARTICLE_NOTIFICATION *}
	{include file="articles/notification/article_confirm_notification.tpl" sArticle=$sArticle}
{* /ARTICLE_NOTIFICATION *}
{* DETAIL_COL1 *}
<div class="detail_col1">
	{if $sArticle.esd}
	    {* ESD-INFO *}
	
	    {* /ESD-INFO *}
	{/if}
	
{* 303 Zoomviewer Support *}
{if $sConfig.sUSEZOOMPLUS}
	{if $sArticle.image.src.4}
		{if $sArticle.image.res.relations}
		{* Saving image resource, for support variant depending images *}
		<div id="img{$sArticle.image.res.relations}" style="display:none">
		    <a href="{$sArticle.image.src.5}"  title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" >
		    <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" /> </a>
   		</div>
		{/if}
		<div id="imgTarget">
		    <a href="{$sArticle.image.src.5}"  id="zoom1" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" class="MagicZoom MagicThumb">
		    <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
		    </a>
	    </div>
	{else}
		<img src="../../media/img/de/layout/no_picture.jpg" alt="{$sArticle.articleName}" width="62" height="63" />
	{/if}
	{if $sArticle.images}
		<div class="thumb_box">
		{if $sArticle.image.src.4}
		  <a href="{$sArticle.image.src.5}" title="{if $sArticle.image.description}{$sArticle.image.description}{else}{$sArticle.articleName}{/if}" rel="zoom1" rev="{$sArticle.image.src.4}" style="background: #fff url({$sArticle.image.src.1}) no-repeat center center;"></a> 
		{/if}
		{foreach from=$sArticle.images item=sArticleImage}
			{if $sArticleImage.relations}
	
		    {* Saving image resource, for support variant depending images *} 
		    <div id="img{$sArticleImage.relations}" style="display:none"><a href="{$sArticleImage.src.5}" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}"><img src="{$sArticleImage.src.4}" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" /></a></div> 
		    {else}
		     <a href="{$sArticleImage.src.5}" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" rel="zoom1" rev="{$sArticleImage.src.4}" style="background: #fff url({$sArticleImage.src.1}) no-repeat center center;"></a> 
		    {/if}
		{/foreach}
		<div class="clearfix"></div>
		</div>
	{/if}
{else}
	{if $sArticle.image.src.4}
		{if $sArticle.image.res.relations}
		{* Saving image resource, for support variant depending images *}
		<div id="img{$sArticle.image.res.relations}" style="display:none">
		    <a href="{$sArticle.image.src.5}" rel="lightbox[photos]" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" class="main_image">
		    <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" /> </a>
   		</div>
		{/if}
		<div id="imgTarget">
		    <a href="{$sArticle.image.src.5}" rel="lightbox[photos]" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" class="main_image">
		    <img src="{$sArticle.image.src.4}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
		    </a>
	    </div>
	{else}
		<img src="../../media/img/de/layout/no_picture.jpg" alt="{$sArticle.articleName}" width="62" height="63" />
	{/if}	
	{if $sArticle.images}
		<div class="thumb_box">
		{foreach from=$sArticle.images item=sArticleImage}
			{if $sArticleImage.relations}
	
		    {* Saving image resource, for support variant depending images *} 
		    <div id="img{$sArticleImage.relations}" style="display:none"><a href="{$sArticleImage.src.5}" rel="lightbox[photos]" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}"><img src="{$sArticleImage.src.4}" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" /></a></div> 
		    {else}
		     <a href="{$sArticleImage.src.5}" rel="lightbox[photos]" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" style="background: #fff url({$sArticleImage.src.1}) no-repeat center center;">
		     </a> 
		    {/if}
		{/foreach}
		<div class="clearfix"></div>
		</div>
	{/if}
{/if}
</div>



{* BUNDLE VARIANTS IMG *} 
{* Original *} 
<div id='img_1_{$sArticle.ordernumber}' style="display:none"> 
						{if $sArticle.image.src.1}
							<a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url({$sArticle.image.src.1});"></a>
						{else}
							<a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url(../../media/img/de/layout/no_picture.jpg);"></a>
						{/if}
</div> 
{* /Original *} 
 
{foreach from=$sArticle.images item=sArticleImage} 
        {if $sArticleImage.relations} 
 
                <div id="img_1_{$sArticleImage.relations}" style="display:none"> 
                
                {if $sArticleImage.src.1} 
                        <a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url({$sArticleImage.src.1});"></a>
                {else} 
						<a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url(../../media/img/de/layout/no_picture.jpg);"></a>
                {/if} 
                </div> 
        {/if} 
{/foreach} 
{* /BUNDLE VARIANTS IMG *}
{* /DETAIL_COL1 *}        


{* DETAIL_COL2 *}
<div class="detail_col2">
	<h1 class="detail_name">{$sArticle.articleName|replace:"'":"&#39;"|replace:"\"":"&quot;"}</h1>
	
    {* ARTICLE_DETAILS *}
    {if $sArticle.supplierName}
    <strong>{* sSnippet: of *}{$sConfig.sSnippets.sArticleof}: {$sArticle.supplierName|replace:"'":"&#39;"|replace:"\"":"&quot;"}</strong>
    {/if}
    <div id="article_details">
		{include file="articles/article_details_data.tpl" sArticle=$sArticle}
	</div>
	{* /ARTICLE_DETAILS *}
	
	<div id="detail_more"></div>
</div>
{* /DETAIL_COL2 *}

{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2} <div class="fixfloat"></div>{/if}
    {* DETAIL_COL3 *}
	<div class="detail_col3"{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2} style="float:none; width:792px;" {/if}>
	

	

    {* BUY_BOX_START *}
    {if !$sArticle.sConfigurator && !$sArticle.sVariants && $sArticle.laststock==1 && $sArticle.instock<=0}
    	{assign var=buy_box_display value='display:none;'}
    {/if}
	<div class="buy_box"{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2} style="float:none; width:792px;"{/if} >
	
	{* ARTIKEL - KONFIGURATOR DROP-DOWN MENUS *}
	{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type!=2}
		{include file="articles/`$sArticle.sConfiguratorSettings.template`" sArticle=$sArticle}
	{/if}
	

	{* ARTICLE_NOTIFICATION *}
		{include file="articles/notification/article_notification.tpl" sArticle=$sArticle}
	{* /ARTICLE_NOTIFICATION *}
	
	<form name="sAddToBasket" method="GET" action="{$sStart}" {if $sArticle.showBasketOnNotification && $sArticle.notification && $sArticle.instock <= 0}style="display: none;"{/if} class="clearfix">
		{if $sArticle.sConfigurator&&$sArticle.sConfiguratorSettings.type==3}
			{foreach from=$sArticle.sConfigurator item=group}
			<input type="hidden" name="group[{$group.groupID}]" value="{$group.selected_value}" />
	        {/foreach}   
		{/if}
		<input type="hidden" name="sViewport" value="basket" />
		<input type="hidden" name="sActionIdentifier" value="{$sUniqueRand}" />
		
		<input type="hidden"  name="sAddAccessories" id="sAddAccessories" value="">
		{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}
		{include file="articles/`$sArticle.sConfiguratorSettings.template`" sArticle=$sArticle}
		{else}
		
		<select name="sAdd" onchange="changeDetails(this.value);" id="sAdd" class="variant" style="{if !$sArticle.sVariants}display:none; width: 175px; margin: 0 0 10px 0;{/if} ">
			{if $sArticle.sVariants}
				<!-- In the case variants are available -->
				<option value="0">{* sSnippet: please choose *}{$sConfig.sSnippets.sArticlepleasechoose}</option>
				<option value="{$sArticle.ordernumber}">{if $sArticle.additionaltext}{$sArticle.additionaltext}{else}{$sArticle.articleName}{/if}</option>
			{else}
				<option value="{$sArticle.ordernumber}" selected>{if $sArticle.additionaltext}{$sArticle.additionaltext}{else}{* sSnippet: mainarticle *}{$sConfig.sSnippets.sArticlemainarticle}{/if}</option>
			{/if}
			
			{foreach name=line from=$sArticle.sVariants item=variante}
				<option value="{$variante.ordernumber}">{if $variante.additionaltext}{$variante.additionaltext} {else}{$variante.ordernumber}{/if}</option>
			{/foreach}
		</select>
		
		{/if}
		<div class="fixfloat"></div>

 		{* ARTICLE_ACCESSORIES *}			
		 {if $sArticle.sAccessories}
		 <script>
			{literal}
			function iterateOrder(){
				$('sAddAccessories').setProperty('value','');
				
				var x = document.getElementsBySelector('.sValueChanger');
				
				x.each(
					function (e){
						if (e.checked){
							var value = $('sAddAccessories').getProperty('value');
							value = value + e.value + ";";
							$('sAddAccessories').setProperty('value',value);
						}
		
					}
				);
			}
			{/literal}
			</script>
			{foreach from=$sArticle.sAccessories item=sAccessory}
			<div class="fixfloat"></div>
			<p><strong>{$sAccessory.groupname}</strong></p>
			<p class="groupdescription">{$sAccessory.groupdescription}</p>
			
			
				{foreach from=$sAccessory.childs item=sAccessoryChild}
				{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}<div style="float:left; margin-right:10px;">{/if}
	                <input style="margin: 7px 7px 0 0;" type="checkbox"  class="sValueChanger chkbox" name="sValueChange" id="CHECK{$sAccessoryChild.ordernumber}" onchange="iterateOrder()" value="{$sAccessoryChild.ordernumber}"><label onmouseover="$('DIV{$sAccessoryChild.ordernumber}').setStyle('display','block');" onmouseout="$('DIV{$sAccessoryChild.ordernumber}').setStyle('display','none');" style="width:140px;float: left; line-height: 1.2em;margin: 5px 0 0 0; padding: 0; height: 20px; cursor: pointer;" for="CHECK{$sAccessoryChild.ordernumber}">{$sAccessoryChild.optionname|truncate:35} <br />({* sSnippet: surcharge *}{$sConfig.sSnippets.sArticlesurcharge}: {$sAccessoryChild.price} {$sConfig.sCURRENCYHTML})</label><br />
	                <div id="DIV{$sAccessoryChild.ordernumber}" style="display:none; position:absolute; margin-left:-262px;margin-top:-190px">
	                    {include file="articles/article_box_accessory.tpl" sArticle=$sAccessoryChild.sArticle key=1}
	                </div><br />
	                {if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}</div>{/if}
				{/foreach}
			 {/foreach}
		{/if}
		<div class="fixfloat"></div>

{assign var="sCountConfigurator" value=$sArticle.sConfigurator|@count}	
{assign var="sPostGroupCount" value=$_POST.group|@count} 
{if ($sArticle.sConfiguratorSettings.type!=1 || $sPostGroupCount == $sCountConfigurator) && (!isset($sArticle.active) || $sArticle.active)}	

{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}
<div style="float:left;margin-right:10px;width:200px;padding:60px 0 0 0;">
{/if}
<label for="sQuantity" style="{$buy_box_display}">{* sSnippet: amount *}{$sConfig.sSnippets.sArticleamount}:</label>
<select name="sQuantity" style="width:110px; margin: 5px 5px 5px 3px; float: left; {$buy_box_display}">
{if $sArticle.laststock}
{section name="i" start=$sArticle.minpurchase loop=$sArticle.instock+1 step=$sArticle.purchasesteps}
{*		*}<option value="{$smarty.section.i.index}" >{$smarty.section.i.index} {if $sArticle.packunit}{$sArticle.packunit}{else}{$sArticle.sUnit.description}{/if}</option>
{/section}
{else}
{section name="i" start=$sArticle.minpurchase loop=$sArticle.maxpurchase+1 step=$sArticle.purchasesteps}
{*		*}<option value="{$smarty.section.i.index}" >{$smarty.section.i.index} {if $sArticle.packunit}{$sArticle.packunit}{else}{$sArticle.sUnit.description}{/if}</option>
{/section}
{/if}		
</select><br/>
		
{* ADD_TO_BASKET *}			
<input type="submit" id="basketButton" title="{$sArticle.articleName} {* sSnippet: add article to basket *}{$sConfig.sSnippets.sArticleinthebasket}" name="{* sSnippet: add to basked *}{$sConfig.sSnippets.sArticleaddtobasked}" value="{$sConfig.sSnippets.sArticleaddtobasked}" style="{$buy_box_display}"{if $sArticle.sVariants}{/if} />
	
{if $sArticle.sVariants}
{literal}
	<script type="text/javascript">
		$('basketButton').setStyle("opacity", "0.5");
	</script>
{/literal}
{/if}

{* /ADD_TO_BASKET *}
{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}
</div>
{/if}
{/if}
</form>
{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}
<ul id="buybox" style="width:200px; float:left;">
	<li><a href="{$sArticle.linkTellAFriend}" rel="nofollow" title="{$sArticle.articleName} {* sSnippet: recommend and get a voucher *}{$sConfig.sSnippets.sArticlerecommendandvoucher}">{* sSnippet: recommend articles and collect voucher! *}{$sConfig.sSnippets.sArticlecollectvoucher}</a></li>
	{if !$sConfig.sVOTEDISABLE}
		<li><a href="#tabbox" rel="nofollow" style="cursor: pointer;" onclick="loadTab('bewertungen')" title="{$sArticle.articleName} bewerten">{* sSnippet: write review *}{$sConfig.sSnippets.sArticlewritereview}</a></h3></li>
	{/if}
	<li><a href="{$sArticle.linkNote}" rel="nofollow" title="{$sArticle.articleName} {* sSnippet: add to notepad *}{$sConfig.sSnippets.sArticleonthenotepad}">{* sSnippet: add to notepad *}{$sConfig.sSnippets.sArticleaddtonotepad}</a></li>
	<li><a href="{$sInquiry}" rel="nofollow" title="{$sArticle.articleName} {* sSnippet: add to notepad *}{$sConfig.sSnippets.sArticleonthenotepad}">{* sSnippet: questions about the article? *}{$sConfig.sSnippets.sArticlequestionsaboutarticle}</a></li>
	<li><a onclick="addCompare('{$sArticle.articleID}')" rel="nofollow" style="cursor: pointer;">{* sSnippet: Compare *}{$sConfig.sSnippets.sArticleCompareDetail}</a></li>
</ul>
<div class="fixfloat"></div>
{/if}


	
</div>
{* /BUY_BOX_END *}
	{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}{else}
<ul id="buybox">
	<li><a href="{$sArticle.linkTellAFriend}" rel="nofollow" title="{$sArticle.articleName} {* sSnippet: recommend and get a voucher *}{$sConfig.sSnippets.sArticlerecommendandvoucher}">{* sSnippet: recommend articles and collect voucher! *}{$sConfig.sSnippets.sArticlecollectvoucher}</a></li>
    {if !$sConfig.sVOTEDISABLE}
		<li><a href="#tabbox" rel="nofollow" style="cursor: pointer;" onclick="loadTab('bewertungen')" title="{$sArticle.articleName} bewerten">{* sSnippet: write review *}{$sConfig.sSnippets.sArticlewritereview}</a></h3></li>
	{/if}
	<li><a href="{$sArticle.linkNote}" rel="nofollow" title="{$sArticle.articleName} {* sSnippet: add to notepad *}{$sConfig.sSnippets.sArticleonthenotepad}">{* sSnippet: add to notepad *}{$sConfig.sSnippets.sArticleaddtonotepad}</a></li>
	<li><a href="{$sInquiry}" rel="nofollow" title="{$sArticle.articleName} {* sSnippet: add to notepad *}{$sConfig.sSnippets.sArticleonthenotepad}">{* sSnippet: questions about the article? *}{$sConfig.sSnippets.sArticlequestionsaboutarticle}</a></li>
	{if !$sHideCompare}<li><a onclick="addCompare('{$sArticle.articleID}')" rel="nofollow" style="cursor: pointer;">{* sSnippet: Compare *}{$sConfig.sSnippets.sArticleCompareDetail}</a></li>{/if}
</ul>
{/if}
</div>
{* /DETAIL_COL3 *}


<div class="fixfloat"></div>
</div>
{* /DETAIL_BOX *}	

{* DETAILINFO *}
	<div class="detailinfo">
    
{* DETAILINFO_COL1 *}
	<div class="detailinfo_col1">
    
	{literal}
	<script language="javascript">
	function loadTab(tab){
		
		$$('.aTab').each(function(el){
			el.setStyle('display','none');
		});
		$(tab).setStyle('display','block');
		$$('.tabs-selected').each(function(el){
			el.removeClass('tabs-selected');
		});
		
		$('link'+tab).addClass('tabs-selected');
	}
	</script>
	{/literal}
	
	{* BUNDLE && RELATEDBUNDLES *}
	{include file="articles/bundles/bundle_box_include.tpl" }
	
	
    {* TAB_NAVIGATION *}
	<div id="tabContainer">
		<ul id="article_info_tabs" class="clearfix tabs-nav">
			<li class="tabs-selected" id="linkbeschreibung"><a  href="#beschreibung" class="updateMe" onclick="loadTab('beschreibung')">{* sSnippet: description *}{$sConfig.sSnippets.sArticledescription}</a></li>
						{if $sArticle.sRelatedArticles && !$sArticle.crossbundlelook}
			<li id="linkzubehoer"><a href="#zubehoer" class="updateMe" onclick="loadTab('zubehoer')">{* sSnippet: accessories *}{$sConfig.sSnippets.sArticleaccessories} [{$sArticle.sRelatedArticles|@count}]</a></li>
			{/if}
	        {if $sArticle.sFinance}
			<li id="linkfinance"><a href="#finance" class="updateMe" onclick="loadTab('finance')">{* sSnippet: finance *}{$sConfig.sSnippets.sArticlefinance}</a></li>
			{/if}
			
			<li id="linkbewertungen" {if $sConfig.sVOTEDISABLE} style="display:none;" {/if}><a href="#bewertungen" class="updateMe" onclick="loadTab('bewertungen')"{if $sArticle.sVoteAverange.count} style="width:160px;"{/if}>{* sSnippet: reviews *}{$sConfig.sSnippets.sArticlereviews} {if $sArticle.sVoteAverange.count}[{$sArticle.sVoteAverange.count}] {/if}
                {if $sArticle.sVoteAverange.count}
                    {if $sArticle.sVoteAverange.averange < 0.5}
                    <img src="../../media/img/default/stars/star_0.gif" alt="{* sSnippet: zero Points *}{$sConfig.sSnippets.sArticlezeropoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 0.5 && $sArticle.sVoteAverange.averange < 1}
                    <img src="../../media/img/default/stars/star_01.gif" alt="{* sSnippet: one Point *}{$sConfig.sSnippets.sArticleonepoint}" />
                    {elseif $sArticle.sVoteAverange.averange >= 1.0 && $sArticle.sVoteAverange.averange < 1.5}
                    <img src="../../media/img/default/stars/star_02.gif" alt="{* sSnippet: two Points *}{$sConfig.sSnippets.sArticletwopoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 1.5 && $sArticle.sVoteAverange.averange < 2}
                    <img src="../../media/img/default/stars/star_03.gif" alt="{* sSnippet: three Points *}{$sConfig.sSnippets.sArticlethreepoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 2.0 && $sArticle.sVoteAverange.averange < 2.5}
                    <img src="../../media/img/default/stars/star_04.gif" alt="{* sSnippet: four Points *}{$sConfig.sSnippets.sArticlefourpoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 2.5 && $sArticle.sVoteAverange.averange < 3}
                    <img src="../../media/img/default/stars/star_05.gif" alt="{* sSnippet: five Points *}{$sConfig.sSnippets.sArticlefivepoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 3.0 && $sArticle.sVoteAverange.averange < 3.5}
                    <img src="../../media/img/default/stars/star_06.gif" alt="{* sSnippet: six Points *}{$sConfig.sSnippets.sArticlesixpoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 3.5 && $sArticle.sVoteAverange.averange < 4}
                    <img src="../../media/img/default/stars/star_07.gif" alt="{* sSnippet: seven Points *}{$sConfig.sSnippets.sArticlesevenpoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 4.0 && $sArticle.sVoteAverange.averange < 4.5}
                    <img src="../../media/img/default/stars/star_08.gif" alt="{* sSnippet: eight Points *}{$sConfig.sSnippets.sArticleeightpoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 4.5 && $sArticle.sVoteAverange.averange < 5}
                    <img src="../../media/img/default/stars/star_09.gif" alt="{* sSnippet: nine Points *}{$sConfig.sSnippets.sArticleninepoints}" />
                    {elseif $sArticle.sVoteAverange.averange >= 5.0}
                    <img src="../../media/img/default/stars/star_10.gif" alt="{* sSnippet: ten Points *}{$sConfig.sSnippets.sArticletenpoints}" />
                    {/if}
                {else} {/if}
                 </a></li>
		</ul>
        
		{* DESCRIPTION *}
		<div id="beschreibung" class="aTab clearfix">
		<h2>{* sSnippet: product information *}{$sConfig.sSnippets.sArticletipproductinformation} "{$sArticle.articleName}"</h2>
		{if $sArticle.sProperties}
		<table cellspacing="0">
			{foreach from=$sArticle.sProperties item=sProperty}
			<tr>
			<td>{$sProperty.name}</td><td>{$sProperty.value}</td>
			</tr>	
			{/foreach}
		</table>
		{/if}

		{$sArticle.description_long|replace:"<table":"<table id=\"zebra\""}
		
				
		{if $sArticle.sLinks}
			<h2>{* sSnippet: more information about *}{$sConfig.sSnippets.sArticletipmoreinformation} "{$sArticle.articleName}"</h2>
			{foreach from=$sArticle.sLinks item=information}
				{if $information.supplierSearch}
					<a href="{$information.link}" target="{$information.target}" class="ico link">{* sSnippet: more information about *}{$sConfig.sSnippets.sArticletipmoreinformation} {$information.description}</a><br />
				{else}
					<a href="{$information.link}" target="{$information.target}" rel="nofollow" class="ico link">{$information.description}</a><br />
				{/if}
			{/foreach}
		{/if}
        
        
        {if $sArticle.sDownloads}
			<h2>{* sSnippet: available downloads *}{$sConfig.sSnippets.sArticleavailabledownloads}</h2>
			{foreach from=$sArticle.sDownloads item=download}
			
				<a href="{$download.filename}" target="_blank" class="ico link">{* sSnippet: download *}{$sConfig.sSnippets.sArticledownload} {$download.description}</a><br />
		
			{/foreach}
		{/if}

		{if $sArticle.attr3}
			<div id="unser_kommentar">
				<h3>{* sSnippet: our comment on *}{$sConfig.sSnippets.sArticleourcommenton} "{$sArticle.articleName}"</h3>
					{$sArticle.attr3}
				
			</div>	
		{/if}		
		</div>
		{* /DESCRIPTION *}
        
        
		{* ACCESSORIES *}
		{if $sArticle.sRelatedArticles}
            <div id="zubehoer" class="aTab clearfix">
                <!-- HIERZU PASSENDE ARTIKEL -->
                <h2>{* sSnippet: these matching items: *}{$sConfig.sSnippets.sArticlematchingitems}</h2>
                    {foreach from=$sArticle.sRelatedArticles item=sArticleSub key=key name="counter"}
                            {include file="articles/article_box_3col_related.tpl" sArticle=$sArticleSub}
                    {/foreach}
                <div class="fixfloat"></div>
            </div>
        {/if}
		{* /ACCESSORIES *}
        
		{* FINANCE HANSEATIC *}
		{if $sArticle.sFinance}
            <div id="finance" class="aTab clearfix"><br>
                <h1>{$sConfig.sSnippets.sArticlefinance}</h1>
                    {include file="articles/article_box_3col_hanseatic.tpl" sArticle=$sArticle.sFinance}
                <div class="fixfloat"></div>
            </div>
        {/if}
        
		{* RATINGS *}
		<div id="bewertungen" class="aTab clearfix">
			<h2>{* sSnippet: customer reviews for *}{$sConfig.sSnippets.sArticlecustomerreviews} "{$sArticle.articleName}"</h2>
			{if $sArticle.sVoteAverange.count}
				<p class="stat"> <strong>{* sSnippet: average customer review: *}{$sConfig.sSnippets.sArticletopaveragecustomerrevi}</strong> 
	            {if $sArticle.sVoteAverange.averange < 0.5}
	            	<img src="../../media/img/default/stars/star_0.gif" alt="{* sSnippet: zero Points *}{$sConfig.sSnippets.sArticlezeropoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 0.5 && $sArticle.sVoteAverange.averange < 1}
	            	<img src="../../media/img/default/stars/star_01.gif" alt="{* sSnippet: one Point *}{$sConfig.sSnippets.sArticleonepoint}" />
	            {elseif $sArticle.sVoteAverange.averange >= 1.0 && $sArticle.sVoteAverange.averange < 1.5}
	            	<img src="../../media/img/default/stars/star_02.gif" alt="{* sSnippet: two Points *}{$sConfig.sSnippets.sArticletwopoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 1.5 && $sArticle.sVoteAverange.averange < 2}
	            	<img src="../../media/img/default/stars/star_03.gif" alt="{* sSnippet: three Points *}{$sConfig.sSnippets.sArticlethreepoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 2.0 && $sArticle.sVoteAverange.averange < 2.5}
	            	<img src="../../media/img/default/stars/star_04.gif" alt="{* sSnippet: four Points *}{$sConfig.sSnippets.sArticlefourpoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 2.5 && $sArticle.sVoteAverange.averange < 3}
	            	<img src="../../media/img/default/stars/star_05.gif" alt="{* sSnippet: five Points *}{$sConfig.sSnippets.sArticlefivepoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 3.0 && $sArticle.sVoteAverange.averange < 3.5}
	           		<img src="../../media/img/default/stars/star_06.gif" alt="{* sSnippet: six Points *}{$sConfig.sSnippets.sArticlesixpoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 3.5 && $sArticle.sVoteAverange.averange < 4}
	            	<img src="../../media/img/default/stars/star_07.gif" alt="{* sSnippet: seven Points *}{$sConfig.sSnippets.sArticlesevenpoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 4.0 && $sArticle.sVoteAverange.averange < 4.5}
	            	<img src="../../media/img/default/stars/star_08.gif" alt="{* sSnippet: eight Points *}{$sConfig.sSnippets.sArticleeightpoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 4.5 && $sArticle.sVoteAverange.averange < 5}
	            	<img src="../../media/img/default/stars/star_09.gif" alt="{* sSnippet: nine Points *}{$sConfig.sSnippets.sArticleninepoints}" />
	            {elseif $sArticle.sVoteAverange.averange >= 5.0}
	            	<img src="../../media/img/default/stars/star_10.gif" alt="{* sSnippet: ten Points *}{$sConfig.sSnippets.sArticletenpoints}" />
	            {/if}
			{/if}
			{if $sArticle.sVoteAverange.count}({* sSnippet: out *}{$sConfig.sSnippets.sArticleout} {$sArticle.sVoteAverange.count} {* sSnippet: reviews *}{$sConfig.sSnippets.sArticlereviews}){/if}
		</p>
	
		<p class="rezension_schreiben"><a href="#tabbox" class="ico comment" style="width: 350px; margin-bottom: 25px;" title="{* sSnippet: write an assessment *}{$sConfig.sSnippets.sArticlewriteanassessment}">{* sSnippet: write an assessment *}{$sConfig.sSnippets.sArticlewriteanassessment}</a></p>
		
		
		{if $_POST.sAction == "saveComment"}
			<div id="rezension">
				{if $sErrorFlag}
					<div class="error">{* sSnippet: please fill out all fields marked in red *}{$sConfig.sSnippets.sArticlefilloutallredfields}</div>
				{else}
				{if $sConfig.sOPTINVOTE && !$_GET.sConfirmation}
					<div class="allright2" style="margin:10px 0; width:374px;">{* sSnippet: the commit save was successful *}{$sConfig.sSnippets.sArticleCommitSavedOptIn}</div>
				{else}
					<div class="allright2" style="margin:10px 0; width:374px;">{* sSnippet: the commit save was successful *}{$sConfig.sSnippets.sArticleCommitSaved}</div>
				{/if}
				{/if}
			</div>
		{/if}			
		
		{if $sArticle.sVoteComments}
			{foreach from=$sArticle.sVoteComments item=vote}
				<div class="horline"></div>
		        {* REZENSION *}
				<div {if $vote.points <= 2.5}class="rezension1"{else}class="rezension2"{/if}>
					<h3>
					{if $vote.points < 0.5}
						<img src="../../media/img/default/stars/star_0.gif" alt="{* sSnippet: zero Points *}{$sConfig.sSnippets.sArticlezeropoints}" />
					{elseif $vote.points >= 0.5 && $vote.points < 1}
						<img src="../../media/img/default/stars/star_01.gif" alt="{* sSnippet: one Point *}{$sConfig.sSnippets.sArticleonepoint}" />
					{elseif $vote.points >= 1.0 && $vote.points < 1.5}
						<img src="../../media/img/default/stars/star_02.gif" alt="{* sSnippet: two Points *}{$sConfig.sSnippets.sArticletwopoints}" />
					{elseif $vote.points >= 1.5 && $vote.points < 2}
						<img src="../../media/img/default/stars/star_03.gif" alt="{* sSnippet: three Points *}{$sConfig.sSnippets.sArticlethreepoints}" />
					{elseif $vote.points >= 2.0 && $vote.points < 2.5}
						<img src="../../media/img/default/stars/star_04.gif" alt="{* sSnippet: four Points *}{$sConfig.sSnippets.sArticlefourpoints}" />
					{elseif $vote.points >= 2.5 && $vote.points < 3}
						<img src="../../media/img/default/stars/star_05.gif" alt="{* sSnippet: five Points *}{$sConfig.sSnippets.sArticlefivepoints}" />
					{elseif $vote.points >= 3.0 && $vote.points < 3.5}
						<img src="../../media/img/default/stars/star_06.gif" alt="{* sSnippet: six Points *}{$sConfig.sSnippets.sArticlesixpoints}" />
					{elseif $vote.points >= 3.5 && $vote.points < 4}
						<img src="../../media/img/default/stars/star_07.gif" alt="{* sSnippet: seven Points *}{$sConfig.sSnippets.sArticlesevenpoints}" />
					{elseif $vote.points >= 4.0 && $vote.points < 4.5}
						<img src="../../media/img/default/stars/star_08.gif" alt="{* sSnippet: eight Points *}{$sConfig.sSnippets.sArticleeightpoints}" />
					{elseif $vote.points >= 4.5 && $vote.points < 5}
						<img src="../../media/img/default/stars/star_09.gif" alt="{* sSnippet: nine Points *}{$sConfig.sSnippets.sArticleninepoints}" />
					{elseif $vote.points >= 5.0}
						<img src="../../media/img/default/stars/star_10.gif" alt="{* sSnippet: ten Points *}{$sConfig.sSnippets.sArticletenpoints}" />
					{/if}
					{$vote.headline}</h3>
					<span class="rezension_datum">{$vote.date}</span>
					<p class="rezension_autor">{* sSnippet: by *}{$sConfig.sSnippets.sArticleby} {$vote.name}</p>
					<p style="margin-top:20px;">
						{$vote.comment}
					</p>
				</div>
				{* /REZENSION *}
			{/foreach}
	
		{/if}

{* SUBMIT VOTE *}

<form name="frmComment" method="POST" action="#bewertungen" id="schnellregistrierung">
	<input name="sAction" type="hidden" value="saveComment" />
	<input name="sViewport" type="hidden" value="{$_GET.sViewport}" />
	<input name="sArticle" type="hidden" value="{$_GET.sArticle}" />
	<!-- form_box -->
	<div class="form_box">
		<a name="tabbox"></a>	
		<p class="heading">{* sSnippet: write review *}{$sConfig.sSnippets.sArticlewritereview}</h2>
		<p style="padding:5px 30px;">{* sSnippet: reviews will be released after verification *}{$sConfig.sSnippets.sArticlereleasedafterverificat}</p>
				<fieldset>
					<p><label for="sVoteName">{* sSnippet: your name *}{$sConfig.sSnippets.sArticleyourname}*: </label>
						<input name="sVoteName" type="text" id="sVoteName" value="{$_POST.sVoteName}" class="normal {if $sErrorFlag.sVoteName}instyle_error{/if}" /></p>
					{if $sConfig.sOPTINVOTE}
					<p><label for="sVoteMail">{* sSnippet: your email *}{$sConfig.sSnippets.sArticleyourmail}*: </label>
						<input name="sVoteMail" type="text" id="sVoteMail" value="{if $_POST.sVoteMail}{$_POST.sVoteMail}{else}{$_GET.sVoteMail}{/if}" class="normal {if $sErrorFlag.sVoteMail}instyle_error{/if}" /></p>
					{/if}
					<p><label for="sVoteSummary">{* sSnippet: summary *}{$sConfig.sSnippets.sArticlesummary}*:</label>
						<input name="sVoteSummary" type="text" value="{$_POST.sVoteSummary}" id="sVoteSummary" class="normal {if $sErrorFlag.sVoteSummary}instyle_error{/if}" /></p>    
					<p><label for="sVoteStars">{* sSnippet: review *}{$sConfig.sSnippets.sArticlereview1}*:</label>  
						<select name="sVoteStars" class="normal" id="sVoteStars">
							<option value="10">{* sSnippet: 10 (very well) *}{$sConfig.sSnippets.sArticle10}</option>
							<option value="9">{* sSnippet: 9 *}{$sConfig.sSnippets.sArticle9}</option>
							<option value="8">{* sSnippet: 8 *}{$sConfig.sSnippets.sArticle8}</option>
							<option value="7">{* sSnippet: 7 *}{$sConfig.sSnippets.sArticle7}</option>
							<option value="6">{* sSnippet: 6 *}{$sConfig.sSnippets.sArticle6}</option>
							<option value="5">{* sSnippet: 5 *}{$sConfig.sSnippets.sArticle5}</option>
							<option value="4">{* sSnippet: 4 *}{$sConfig.sSnippets.sArticle4}</option>
							<option value="3">{* sSnippet: 3 *}{$sConfig.sSnippets.sArticle3}</option>
							<option value="2">{* sSnippet: 2 *}{$sConfig.sSnippets.sArticle2}</option>
							<option value="1">{* sSnippet: 1 (very bad) *}{$sConfig.sSnippets.sArticle1}</option>
						</select></p>
						<p class="textarea">
							<label for="sVoteComment">{* sSnippet: your opinion *}{$sConfig.sSnippets.sArticleyouropinion}</label>
							<textarea name="sVoteComment" id="sVoteComment" class="normal {if $sErrorFlag.sVoteComment}instyle_error{/if}">{$_POST.sVoteComment|escape}</textarea>
						</p> 
						<div class="captcha" style="padding:15px;">
							<img src="{$sStart}?sCaptcha=1&sCoreId={$sCoreId}"/>
							<div class="code" style="margin-left:15px;">
								<label style="height: 55px; width: 150px;">{* sSnippet: please enter the numbers in the following text box *}{$sConfig.sSnippets.sArticleenterthenumbers}</label>
								<input type="text" name="sCaptcha" style="width:154px;" class="{if $sErrorFlag.sCaptcha}instyle_error{else}instyle{/if}">
							<p style="display:none;"><input type="text" name="sCaptchaTest" /></p>
							</div>
						</div>
							<div class="fixfloat"></div>
					<p style="padding:10px 75px;">{* sSnippet: the fields marked with * are mandatory. *}{$sConfig.sSnippets.sArticlethefieldsmarked}</p>
				</fieldset>
		<div class="buttons">
			<input class="button" type="submit" name="Submit" value="{* sSnippet: save *}{$sConfig.sSnippets.sArticletosave}" style="float: right; margin-right:10px;"/>	
		</div><div class="fixfloat"></div>
		
	
	</div>
	<div class="fixfloat"></div>
</form>	 			
{* /SUBMIT VOTE *}	

</div>
{* /RATINGS *}
	</div>
	<div id="tabContainercap"></div>


{* TAB_CONTAINER *}
<div class="fixfloat"></div>
</div>
{* /DETAILINFO_COL1 *}



{* DETAILINFO_COL2 *}	
    <div class="detailinfo_col2">
        {if $sArticle.sSimilarArticles}
            {* ARTICLE_SIMILAR *}
            <div id="aehnlich" class="box">
                <h2>{* sSnippet: similar articles *}{$sConfig.sSnippets.sArticlesimilararticles}</h2>
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



{literal}
<script type="text/javascript">
window.onload=function()
{
	$('bewertungen').setStyle('display','none');
	try {
		$('datenblatt').setStyle('display','none');
	} catch (e){}
	try {
		$('zubehoer').setStyle('display','none');
	} catch (e){}
	 try {
		$('finance').setStyle('display','none');
	} catch (e){}

	$$('.updateMe').each(function(el){
		el.removeProperty('href');
	});
	
	stripe('zebra');
	
	Lightbox.init({descriptions: '.lightboxDesc', showControls: true});
	
	//Set Comment on focus by commentsubmit
	{/literal}
	{if $_POST.sAction == "saveComment"||!empty($_GET.sVoteMail)}
	{literal}
		loadTab('bewertungen');		
		$('bewertungen').setStyle('display', 'block');	
		$('bewertungen').focus();	
	{/literal}
	{/if}
	{literal}
}
</script>
{/literal}
{* OUTPUT DEFAULT - DATA *}
    <div id="{$sArticle.ordernumber}" style="height:1px;z-index:-1;overflow-y:hidden; display:none;">
        {include file="articles/article_details_data.tpl" sArticle=$sArticle}
    </div>
{* /OUTPUT DEFAULT - DATA *}


{* OUTPUT VARIANTS - DATA *}
    {foreach name=line from=$sArticle.sVariants item=sVariant}
    <div id="{$sVariant.ordernumber}" style="height:1px;z-index:-1;overflow:hidden; display:none;">
        {include file="articles/article_details_data.tpl" sArticle=$sVariant}
    </div>
    {/foreach}
{* /OUTPUT VARIANTS - DATA *}


{* DEFAULT VIEW FOR VARIANTS *}
<div id="variant" style="height:1px;z-index:-1;overflow:hidden; display:none;">
	{* ORDERNUMBER AND ATTRIBUTES *}

	<div class='article_details_bottom'>
	{if $sArticle.priceStartingFrom}
				<div class='article_details_price'>
					<strong>ab {$sConfig.sCURRENCYHTML} {$sArticle.priceStartingFrom}</strong>
				</div>
	            
				<p class="tax_attention">{* sSnippet: prices *}{$sConfig.sSnippets.sArticleprices} {if $sConfig.sARTICLESOUTPUTNETTO}{* sSnippet: plus *}{$sConfig.sSnippets.sAccountplus}{else}{* sSnippet: incl *}{$sConfig.sSnippets.sArticleincl}{/if} {* sSnippet: legal *}{$sConfig.sSnippets.sArticlelegal}<br />{* sSnippet: tax + *}{$sConfig.sSnippets.sArticletaxplus1} <a href="{$sBasefile}?sViewport=custom&cCUSTOM=6" title="{* sSnippet: information on shipping *}{$sConfig.sSnippets.sArticleshippinginformation}">{* sSnippet: shipping *}{$sConfig.sSnippets.sArticleshipping}*</a></p>
	{else}
				<div {if $sArticle.pseudoprice} class='article_details_price2'>{else} class='article_details_price'>{/if}
				{if $sArticle.pseudoprice}
	            	<s style="color:#666666;">{$sArticle.pseudoprice}</s><br />
	            {/if}
					<strong>{$sConfig.sCURRENCYHTML} {$sArticle.price}</strong>
				</div>
				<p class="tax_attention">{* sSnippet: prices *}{$sConfig.sSnippets.sArticleprices} {if $sConfig.sARTICLESOUTPUTNETTO}{* sSnippet: plus *}{$sConfig.sSnippets.sAccountplus}{else}{* sSnippet: incl *}{$sConfig.sSnippets.sArticleincl}{/if} {* sSnippet: legal *}{$sConfig.sSnippets.sArticlelegal}<br />{* sSnippet: tax + *}{$sConfig.sSnippets.sArticletaxplus1} <a href="{$sBasefile}?sViewport=custom&cCUSTOM=6" title="{* sSnippet: information on shipping *}{$sConfig.sSnippets.sArticleshippinginformation}">{* sSnippet: shipping *}{$sConfig.sSnippets.sArticleshipping}*</a></p>
	{/if}
	</div>
</div>
<div id='selected_ordernumber' style="display:none;"></div>
<script language="JavaScript" type="text/javascript">
{literal}
function changeDetails(ordernumber)
{
	if(typeof(window.checkNotification) == "function") {
		checkNotification(ordernumber);
	}
	if (ordernumber != "0"){
		$('article_details').setHTML($(ordernumber).innerHTML);
		$('selected_ordernumber').setHTML(ordernumber);
		// Swap pictures
		try {
			{/literal}
			{if $sConfig.sUSEZOOMPLUS}
			MagicZoom_stopZooms();
			$('imgTarget').setHTML($('img'+ordernumber).innerHTML);
			$('imgTarget').getFirst().setProperty('id','zoom1');
			$('imgTarget').getFirst().addClass('MagicZoom MagicThumb');
			MagicZoom_findZooms();
			MagicThumb.refresh();
			{else}
			$('imgTarget').setHTML($('img'+ordernumber).innerHTML);
			{/if}
			{literal}
			
		}catch (e) {}
		if($('basketButton'))
		{
			$('basketButton').removeEvents('click');
			$('basketButton').setStyle('opacity',1);
		}
	}else {
		$('basketButton').removeEvents('click');
		$('basketButton').addEvent('click', function(event){
			new Event(event).stop();
			alert("{/literal}{* sSnippet: please choose first execution *}{$sConfig.sSnippets.sArticlechoosefirstexecu}{literal}");
		});
	}	
	{/literal}{include file="articles/bundles/changeDetails_bundle.tpl" }{literal}
}

{/literal}
</script>
<script language="JavaScript" type="text/javascript">
{literal}
window.addEvent('domready', function() {
{/literal}
// Set default-view to base-article
{if !$sArticle.sVariants}
	changeDetails("{$sArticle.ordernumber}");
{else}
	{literal}
	// Set view for variant articles
	// price starting from
	$('article_details').setHTML($('variant').innerHTML);
	
	$('basketButton').removeEvents('click');
	$('basketButton').addEvent('click', function(event){
		new Event(event).stop();
		alert("{/literal}{* sSnippet: please choose first execution *}{$sConfig.sSnippets.sArticlechoosefirstexecu}{literal}");
	});
	{/literal}
{/if}
{literal}
});

</script>
{/literal}