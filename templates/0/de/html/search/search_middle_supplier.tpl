{if $sSearchResults}
   <div class="searchheadline" style="margin-bottom:10px;"> {* sSnippet: to *}{$sConfig.sSnippets.sSearchto} &bdquo;{$_GET.sSearch}&rdquo; {* sSnippet: were *}{$sConfig.sSnippets.sSearchwere} {$sSearchResultsNum} {* sSnippet: articles found *}{$sConfig.sSnippets.sSearcharticlesfound}</div>
    {else}
    {* NO RESULTS *}
    	<h2>{* sSnippet: Unfortunately there were *}{$sConfig.sSnippets.sSearchunfortunatelytherewere} "{$_GET.sSearch}" {* sSnippet: no articles found *}{$sConfig.sSnippets.sSearchnoarticlesfound}</h2>
    {* /NO RESULTS *}
{/if}
<div class="clearfix">
	 {* LISTING_BOX *}
<div class="listing_box" style="width:653px;">	
	 {if $sPages.numbers.2.value}

    {* LISTING_BOX_TOP *}
    <div class="listing_box_top">
        {* ARTICLE-OPTIONS *}
	 <div class="article-options clearfix">
         	 {* PAGE_FLIP *}
			<div style="float: left;">
			<span>{* sSnippet: browse *}{$sConfig.sSnippets.sSearchbrowse}</span>
			{if $sPages.previous}
						<a href="{$sPages.previous}" title="{* sSnippet: one page back *}{$sConfig.sSnippets.sSearchonepageback}" class="flip_previous"></a>
			
			{/if}
			{foreach from=$sPages.numbers item=page}
            	
            	{if $page.value<$_GET.sPage+3 AND $page.value>$_GET.sPage-3}
					{if ($page.value != 1 AND $page.value!=$_GET.sPage-2) OR (!$sPages.next AND $_GET.sPage == 1)}{/if}
					{if $page.markup AND (!$sOffers OR $_GET.sPage)}
					<a href="#" class="navi on">{$page.value}</a>
					{else}
					<a href="{$page.link}" title="{$sCategoryInfo.name}" class="navi">{$page.value}</a>
					{/if}
				{elseif $page.value==$_GET.sPage+3 OR $page.value==$_GET.sPage-3}{/if}
				{/foreach}
				{if $sPages.next}
				<a href="{$sPages.next}" title="{* sSnippet: next page *}{$sConfig.sSnippets.sSearchnextpage}" class="flip_next"></a>
				
				{/if}
			</div>
            {* /PAGE_FLIP *}
         </div>
         <div class="fixfloat"></div>
     </div>
    {* /LISTING_BOX_TOP *}
       {/if}
	<div style="padding: 0px;">
	{foreach from=$sSearchResults item=searchResult key=key name="counter"}
		{if $key % 2 == 0 and $key != 0}
	
		{/if}
		{if $key % 2 != 0}
		
		{/if}
			{include file="articles/article_box_4col.tpl" sArticle=$searchResult}
	{/foreach}
	</div>
	<div class="fixfloat"></div>
	
	{if $sPages.numbers.2.value && $sPerPage}
	 {* LISTING_BOX_CAP *}
    <div class="listing_box_cap" >
		{* ARTICLE-OPTIONS *}
		<div class="article-options clearfix">
	
		 {if $sPages.numbers.2.value}
		 
		 
         	 {* PAGE_FLIP *}
			<div style="float: left;">
			<span>{* sSnippet: browse *}{$sConfig.sSnippets.sSearchbrowse}</span>
			{if $sPages.previous}
						<a href="{$sPages.previous}" title="{* sSnippet: one page back *}{$sConfig.sSnippets.sSearchonepageback}" class="flip_previous"></a>
			
			{/if}
			{foreach from=$sPages.numbers item=page}
            	
            	{if $page.value<$_GET.sPage+3 AND $page.value>$_GET.sPage-3}
					{if ($page.value != 1 AND $page.value!=$_GET.sPage-2) OR (!$sPages.next AND $_GET.sPage == 1)}{/if}
					{if $page.markup AND (!$sOffers OR $_GET.sPage)}
					<a href="#" class="navi on">{$page.value}</a>
					{else}
					<a href="{$page.link}" title="{$sCategoryInfo.name}" class="navi">{$page.value}</a>
					{/if}
				{elseif $page.value==$_GET.sPage+3 OR $page.value==$_GET.sPage-3}{/if}
				{/foreach}
				{if $sPages.next}
				<a href="{$sPages.next}" title="{* sSnippet: next page *}{$sConfig.sSnippets.sSearchnextpage}" class="flip_next"></a>
				
				{/if}
			</div>
            {* /PAGE_FLIP *}
            
            
            {/if} 
			{* ARTICLE_PER_PAGE *}
            	
				<p style="float: right;">
				<span>{* sSnippet: articles per page *}{$sConfig.sSnippets.sSearcharticlesperpage} </span>
				{foreach from=$sPerPage item=perPage key=sKey}
					
	                {if $perPage.markup}
	                    <a href="#" class="perpage on">{$perPage.value}</a>
	                {else}
	                    <a href="{$perPage.link}" class="perpage">{$perPage.value} </a>
	                {/if} 
	    
				{/foreach}
				</p>
			{* /ARTICLE_PER_PAGE *}
        </div>
        {* /ARTICLE-OPTIONS *}
    </div>
    {* /LISTING_BOX_CAP *}
    {/if}
        </div>
{* /LISTING_BOX *}
	
</div>