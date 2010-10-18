{* listing_box *}
<div class="listing_box" style="width:653px; float:left;">
{if $sPages.numbers.2.value}
    {* listing_box_top *}
    <div class="listing_box_top">
        {* article-options *}
        <div class="article-options clearfix">
            {* page_flip *}
			<div style="float: left;">
			<span>{* sSnippet: browse *}{$sConfig.sSnippets.sContentbrowse}</span>
			{if $sPages.previous}
			<a href="{$sPages.previous}" title="{* sSnippet: go back one page *}{$sConfig.sSnippets.sContentgobackonepage}" class="flip"><img src="../../media/img/default/store/ico_arrow5.gif" alt="{* sSnippet: go back one page *}{$sConfig.sSnippets.sContentgobackonepage}" align="absmiddle" /></a>
			{/if}
			{foreach from=$sPages.numbers item=page}
				{if $page.value<$_GET.sPage+4 AND $page.value>$_GET.sPage-4}
					{if ($page.value != 1 AND $page.value!=$_GET.sPage-3) OR (!$sPages.next AND $_GET.sPage == 1)}{/if}
					{if $page.markup AND (!$sOffers OR $_GET.sPage)}
					<span class="on">{$page.value}</span>
					{else}
					<a href="{$page.link}" title="{$sCategoryInfo.name}" class="navi">{$page.value}</a>
					{/if}
				{elseif $page.value==$_GET.sPage+4 OR $page.value==$_GET.sPage-4}...{/if}
				{/foreach}
				{if $sPages.next}
				<a href="{$sPages.next}" title="{* sSnippet: browse one page forward *}{$sConfig.sSnippets.sContentbrowseforward}" class="flip"><img src="../../media/img/default/store/ico_arrow4.gif" alt="{* sSnippet: browse one page forward *}{$sConfig.sSnippets.sContentbrowseforward}" align="absmiddle" /></a>
				{/if}
			</div>
            {* /page_flip *}     
       	</div>
        {* /article-options *} 
	</div>
    {* /listing_box_top *}
{/if}

{if $sContent}
{foreach from=$sContent item=sContentItem key=key name="counter"}
	<div style="height: 38px; width:643px; margin: 0 0 10px 0px; border: 5px solid #f3f3f3;">
		<div href="{$sContentItem.link}" title="{$sContentItem.description}" class="box_1col_description" style="margin:0;width:100%;">
			<h2 class="blue" style="float:left;margin:12px;">{if $sContentItem.date}{$sContentItem.date|date_format:"%d.%m.%Y"} - {/if}{$sContentItem.description}</h2>
			<a href="{$sContentItem.link}" class="box_1col_more" style="float:right;position:static;margin:9px;">{* sSnippet: more *}{$sConfig.sSnippets.sContentmore}</a>
		</div>
	</div>
	<div class="fixfloat"></div>
{/foreach}
{else}
	<p style="height: 100px; width:600px; margin: 0 10px 0 20px;">{* sSnippet: currently no entries *}{$sConfig.sSnippets.sContentcurrentlynoentries}</p>
{/if}
    
{if $sPages.numbers.2.value}
    {* listing_box-cap *}
    <div class="listing_box_cap">
        {* article-options *}
		<div class="article-options clearfix">
	

            {* page_flip *}
            <div style="float: left;">
                <span>{* sSnippet: browse *}{$sConfig.sSnippets.sContentbrowse}</span>
                {if $sPages.previous}
                <a href="{$sPages.previous}" title="{* sSnippet: go back one page *}{$sConfig.sSnippets.sContentgobackonepage}" class="flip"><img src="../../media/img/default/store/ico_arrow5.gif" alt="{* sSnippet: go back one page *}{$sConfig.sSnippets.sContentgobackonepage}" align="absmiddle" /></a>
                {/if}
                {foreach from=$sPages.numbers item=page}
                    {if $page.value<$_GET.sPage+4 AND $page.value>$_GET.sPage-4}
                        {if ($page.value != 1 AND $page.value!=$_GET.sPage-3) OR (!$sPages.next AND $_GET.sPage == 1)}{/if}
                        {if $page.markup AND (!$sOffers OR $_GET.sPage)}
                        <span class="on">{$page.value}</span>
                        {else}
                        <a href="{$page.link}" title="{$sCategoryInfo.name}" class="navi">{$page.value}</a>
                        {/if}
                    {elseif $page.value==$_GET.sPage+4 OR $page.value==$_GET.sPage-4}...{/if}
                    {/foreach}
                    {if $sPages.next}
                    <a href="{$sPages.next}" title="{* sSnippet: browse one page forward *}{$sConfig.sSnippets.sContentbrowseforward}" class="flip"><img src="../../media/img/default/store/ico_arrow4.gif" alt="{* sSnippet: browse one page forward *}{$sConfig.sSnippets.sContentbrowseforward}" align="absmiddle" /></a>
                    {/if}
            </div>
            {* /page_flip *}  

        </div>
        {* /article-options *}
        
       </div>
    {* /listing_box_cap *}
{/if}
</div>
{* /listing_box *}
