{* COL_CENTER_LISTING *}
<div class="col_center_listing">

{* LIVE-SHOPPING - START *}
{if $sLiveShopping}
{foreach from=$sLiveShopping.liveshoppingData item=liveArt}
	{include file="articles/liveshopping/include_liveshopping.tpl" liveArt=$liveArt}
{/foreach}
{/if}
{* LIVE-SHOPPING - END *}

{if $sBanner}
		{if $sBanner.extension=="swf"}
			  <object classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" 
	          	codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0" width="653" height="300">
			    <param name="movie" value="{$sBanner.img}">
			    <param name="quality" value="high">
			    <param name="scale" value="exactfit">
			    <param name="menu" value="true">
			    <param name="bgcolor" value="#000040">
			    <embed src="{$sBanner.img}" quality="high" scale="exactfit" menu="false" width="653" height="300"
			           bgcolor="#000000"  swLiveConnect="false"
			           type="application/x-shockwave-flash"
			           pluginspage="http://www.macromedia.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash">
			    </embed>
		 	  </object>
		{elseif $sBanner.liveshoppingData}
			{include file="articles/liveshopping/include_liveshopping.tpl" liveArt=$sBanner.liveshoppingData}
		{else}
     		<!--  homepagebanner -->
	        {if $sBanner.link == "#"}{else}<a href="{$sBanner.link}" class="cat_banner" {if $sBanner.link_target}target="{$sBanner.link_target}"{/if} title="{$sBanner.description}">{/if}
	        {if $sBanner.img}<img src="{$sBanner.img}" class="cat_banner" alt="{$sBanner.description}" name="{$sBanner.description}" border="0" title="{$sBanner.description}" />{/if}
	        {if $sBanner.link == "#"}{else}</a>{/if}
	        <!-- /homepagebanner -->
	   {/if}
	{/if}

    {if $sCategoryContent.cmsheadline}
    
        {* CATEGORY_TEXT *}
            <div class="cat_text">
                <h1>{$sCategoryContent.cmsheadline}</h1>
                <p>{$sCategoryContent.cmstext}</p>
            </div>
        {* /CATEGORY_TEXT *}
        
    {/if}


{* LISTING_BOX *}
<div class="listing_box">
    {* LISTING_BOX_TOP *}
    <div class="listing_box_top">
        {* ARTICLE-OPTIONS *}
        <div class="article-options clearfix">
       	 {if $sPages.numbers.2.value}

            {* PAGE_FLIP *}
			<div style="float: left;">
			<span>{* sSnippet: scroll *}{$sConfig.sSnippets.sArticlescroll}:</span>
			{if $sPages.previous}
			<a href="{$sPages.previous}" title="{* sSnippet: one site back *}{$sConfig.sSnippets.sArticleonesiteback}" class="flip_previous"></a>
			{/if}
			{foreach from=$sPages.numbers item=page}
				{if $page.value<$_GET.sPage+4 AND $page.value>$_GET.sPage-4}
					{if $page.markup AND (!$sOffers OR $_GET.sPage)}
					<a title="{$sCategoryInfo.name}" class="navi on">{$page.value}</a>
					{else}
					<a href="{$page.link}" title="{$sCategoryInfo.name}" class="navi">{$page.value}</a>
					{/if}
				{elseif $page.value==$_GET.sPage+4 OR $page.value==$_GET.sPage-4}<div class="more">...</div>{/if}
				{/foreach}
			{if $sPages.next}
			<a href="{$sPages.next}" title="{* sSnippet: one site forward *}{$sConfig.sSnippets.sArticleonesiteforward}" class="flip_next"></a>
			{/if}
			</div>
            {* /PAGE_FLIP *}
            {/if}
            
			{* PAGE_SORT *} 
			<form name="frmsort" method="POST" action="" id="frmsort">
				<input type="hidden" name="cID" value="{$cID}" />
				<input type="hidden" name="cMan" value="{$cMan}" />
				<label for="cSORT">{* sSnippet: sort *}{$sConfig.sSnippets.sArticlesort}</label>
				<select name="sSort" id="cSORT" onChange="$('frmsort').submit()">
				<option value="1"{if $_POST.sSort == 1} selected{/if}>{* sSnippet: release date *}{$sConfig.sSnippets.sArticlereleasedate}</option>
				<option value="2"{if $_POST.sSort == 2} selected{/if}>{* sSnippet: popularity *}{$sConfig.sSnippets.sArticlepopularity}</option>
				<option value="3"{if $_POST.sSort == 3} selected{/if}>{* sSnippet: lowest price *}{$sConfig.sSnippets.sArticlelowestprice}</option>
				<option value="4"{if $_POST.sSort == 4} selected{/if}>{* sSnippet: highest price *}{$sConfig.sSnippets.sArticlehighestprice}</option>
				<option value="5"{if $_POST.sSort == 5} selected{/if}>{* sSnippet: item title *}{$sConfig.sSnippets.sArticleitemtitle}</option>
				</select>
				<!--<input type="submit" name="Submit" value="Ok" />-->
			</form>	
			{* /PAGE_SORT *}
            </div>
        {* /ARTICLE-OPTIONS *}
    </div>
    {* /LISTING_BOX_TOP *}
    
    
{* SUPPLIER_FILTER *}

{if $sSupplierInfo} <div id="homepagebanner">
{* sSnippet: products of *}{$sConfig.sSnippets.sArticleproductsof}
{if $sSupplierInfo.image}
<img src="{$sSupplierInfo.image}" alt="{$sSupplierInfo.name}" name="{$sSupplierInfo.name}" border="0" id="aktionsname" title="{$sSupplierInfo.name}" />
{else}
{$sSupplierInfo.name}
{/if} 
<a href="{$sSupplierInfo.link}">{* sSnippet: show all *}{$sConfig.sSnippets.sArticleshowall} </a>
</div>
{/if}
{* /SUPPLIER_FILTER *}



{if $sOffers AND !$_GET.sPage}
	{foreach from=$sOffers item=offer key=key}
		
			{if $key % 2 == 0 and $key != 0}
				<div class="art_hor_line"><div><!-- blank --></div></div>
			{/if}
			{if $key % 2 != 0}
				<div class="art_vert_line"></div>
			{/if}
			{if $offer.mode == "gfx"}
				 {include file="articles/article_box_img.tpl" sArticle=$offer}
			{elseif $offer.mode == "livefix" || $offer.mode == "liverand" || $offer.mode == "liverandcat"}
				{include file='articles/liveshopping/include_liveshopping.tpl' liveArt=$offer.liveshoppingData}
			{else}
				{include file="articles/article_box_3col.tpl" sArticle=$offer}
			{/if}	
		
	{/foreach}
{else}
	{foreach from=$sArticles item=article key=key}
		{include file="articles/article_box_3col.tpl" sArticle=$article key=$key}
	{/foreach}
{/if}
        <div class="fixfloat"></div>


    {* LISTING_BOX_CAP *}
    <div class="listing_box_cap">
		{* ARTICLE-OPTIONS *}
		<div class="article-options clearfix">
	
		 {if $sPages.numbers.2.value}
            {* PAGE_FLIP *}
			<div style="float: left;">
			<span>{* sSnippet: scroll *}{$sConfig.sSnippets.sArticlescroll}:</span>
			{if $sPages.previous}
			<a href="{$sPages.previous}" title="{* sSnippet: one site back *}{$sConfig.sSnippets.sArticleonesiteback}" class="flip_previous"></a>
			{/if}
			{foreach from=$sPages.numbers item=page}
				{if $page.value<$_GET.sPage+4 AND $page.value>$_GET.sPage-4}
					{if $page.markup AND (!$sOffers OR $_GET.sPage)}
					<a title="{$sCategoryInfo.name}" class="navi on">{$page.value}</a>
					{else}
					<a href="{$page.link}" title="{$sCategoryInfo.name}" class="navi">{$page.value}</a>
					{/if}
				{elseif $page.value==$_GET.sPage+4 OR $page.value==$_GET.sPage-4}<div class="more">...</div>{/if}
				{/foreach}
			{if $sPages.next}
			<a href="{$sPages.next}" title="{* sSnippet: one site forward *}{$sConfig.sSnippets.sArticleonesiteforward}" class="flip_next"></a>
			{/if}
			</div>
            {* /PAGE_FLIP *}
            {/if} 
			{* ARTICLE_PER_PAGE *}
				<p style="float: right;">
				<span>{* sSnippet: article per page *}{$sConfig.sSnippets.sArticlearticleperpage}:</span>
				{foreach from=$sPerPage item=perPage}
	                {if $perPage.markup}
	                    <a title="{$sCategoryInfo.name}" class="perpage on">{$perPage.value}</a>
	                {else}
	                    <a href="{$perPage.link}" title="{$sCategoryInfo.name}" class="perpage">{$perPage.value}</a>
	                {/if} 
				{/foreach}
				</p>
			{* /ARTICLE_PER_PAGE *}
        </div>
        {* /ARTICLE-OPTIONS *}    </div>
    {* /LISTING_BOX_CAP *}
    </div>
{* /LISTING_BOX *}
</div>
{* /COL_CENTER_LISTING *}