{* COL_CENTER_LISTING *}
<div class="col_center_listing">

<div id="suchresultate">
	{if $sSearchResults.sArticles}
	{elseif $sRequests.sSearchOrginal}
        <div class="error">
        {* NO RESULTS *}
        <h2>{* sSnippet: Unfortunately there were *}{$sConfig.sSnippets.sSearchunfortunatelytherewere} "{$sRequests.sSearchOrginal}" {* sSnippet: no articles found *}{$sConfig.sSnippets.sSearchnoarticlesfound}</h2>
        </div>
        {* /NO RESULTS *}
    {else}
        <div class="error">
        {* NO RESULTS *}
        <h2>{* sSnippet: the entered search term is too short *}{$sConfig.sSnippets.sSearchsearchtermtooshort}</h2>
        </div>
        {* /NO RESULTS *}
	{/if}
</div>


{if $sSearchResults.sArticles}
{* LISTING_BOX *}
<div class="listing_box">
    {* LISTING_BOX_TOP *}
    <div class="listing_box_top">
        {* ARTICLE-OPTIONS *}
        <div class="article-options clearfix">
        
		{* PAGE_FLIP *}
	{if $sRequests.sPage==0&&!$sPages.next}
	{else}
	<div style="float: left;">
	<span>{* sSnippet: browse *}{$sConfig.sSnippets.sSearchbrowse} </span>
	{if $sRequests.sPage!=0}
		<a class="flip_previous" href="{$sLinks.sPage}&sPage={$sPages.before}"></a>
	{/if}
	{foreach from=$sPages.pages item=page}
		{if $sRequests.sPage==$page}
		<a title="{$sCategoryInfo.name}" class="navi on">{$page+1}</a>
		{else}
		<a href="{$sLinks.sPage}&sPage={$page}" title="{$sCategoryInfo.name}" class="navi">
		{$page+1}
		</a>
		{/if}
	{/foreach}
	{if $sPages.next}
		<a class="flip_next" href="{$sLinks.sPage}&sPage={$sPages.next}"></a>
	{/if}
	</div>
	{/if}
	 {* /PAGE_FLIP *}
     
	 {* PAGE_SORT *}  
	<form name="frmsort" method="get" action="{$sLinks.sSort}" id="frmsort">
		<label for="cSORT" style="width: 60px; height: 20px;">{* sSnippet: sort *}{$sConfig.sSnippets.sSearchsort}</label>
		<select name="sSort" id="cSORT" onChange="$('frmsort').submit()" style="z-index:99999;">
			<option value="6"{if $sRequests.sSort == 6} selected{/if}>{* sSnippet: relevance *}{$sConfig.sSnippets.sSearchrelevance}</option>
			<option value="1"{if $sRequests.sSort == 1} selected{/if}>{* sSnippet: release date *}{$sConfig.sSnippets.sSearchreleasedate}</option>
			<option value="2"{if $sRequests.sSort == 2} selected{/if}>{* sSnippet: popularity *}{$sConfig.sSnippets.sSearchpopularity}</option>
			<option value="3"{if $sRequests.sSort == 3} selected{/if}>{* sSnippet: lowest price *}{$sConfig.sSnippets.sSearchlowestprice}</option>
			<option value="4"{if $sRequests.sSort == 4} selected{/if}>{* sSnippet: highest price *}{$sConfig.sSnippets.sSearchhighestprice}</option>
			<option value="5"{if $sRequests.sSort == 5} selected{/if}>{* sSnippet: item title *}{$sConfig.sSnippets.sSearchitemtitle}</option>
		</select>
	</form>	
	{* /PAGE_SORT *}
        </div>
        {* /ARTICLE_OPTIONS *}
    </div>
   {* /LISTING_BOX_TOP *}
	
	<div style="padding: 0px;">
		{foreach from=$sSearchResults.sArticles item=searchResult key=key name=list}
				{include file="articles/article_box_4col.tpl" sArticle=$searchResult}
		{/foreach}
		<div class="fixfloat"></div>
	</div>
	
   {* LISTING_BOX_CAP *}
    <div class="listing_box_cap">
    
		{* ARTICLE-OPTIONS *}
	<div class="article-options clearfix">
		{* PAGE_FLIP *}
	{if $sRequests.sPage==0&&!$sPages.next}
	{else}
	<div style="float: left;">
	<span>{* sSnippet: browse *}{$sConfig.sSnippets.sSearchbrowse} </span>
	{if $sRequests.sPage!=0}
		<a class="flip_previous" href="{$sLinks.sPage}&sPage={$sPages.before}"></a>
	{/if}
	{foreach from=$sPages.pages item=page}
		{if $sRequests.sPage==$page}
		<a title="{$sCategoryInfo.name}" class="navi on">{$page+1}</a>
		{else}
		<a href="{$sLinks.sPage}&sPage={$page}" title="{$sCategoryInfo.name}" class="navi">
		{$page+1}
		</a>
		{/if}
	{/foreach}
	{if $sPages.next}
		<a class="flip_next" href="{$sLinks.sPage}&sPage={$sPages.next}"></a>
	{/if}
	</div>
	{/if}
	 {* /PAGE_FLIP *}
	 
	{* ARTICLE_PER_PAGE *}
	<p style="float: right;">
		<span>{* sSnippet: articles per page *}{$sConfig.sSnippets.sSearcharticlesperpage} </span>
		{foreach from=$sPerPage item=perPage key=sKey}
		{if $sRequests.sPerPage==$perPage}
			<a title="{$sCategoryInfo.name}" class="perpage on">{$perPage}</a>
		{else}
			<a href="{$sLinks.sPerPage}&sPerPage={$perPage}" title="{$sCategoryInfo.name}" class="perpage">{$perPage}</a>
		{/if}
		{/foreach}
	</p> 
	{* /ARTICLE_PER_PAGE *}
   </div>
    {* /ARTICLE-OPTIONS *}
    </div>
    {* /LISTING_BOX_CAP *}
    </div>
{* /LISTING_BOX *}
{/if}
</div>
{* /COL_CENTER_LISTING *}