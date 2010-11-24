<div class="blogbox">
	{block name='frontend_blog_col_blog_entry'}
	
	{* Article name *}
	{block name='frontend_blog_col_article_name'}
	<h2>
		<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{$sArticle.articleName}</a>
	</h2>
	{/block}
	
	{* Meta data *}
	{block name='frontend_blog_col_meta_data'}
	<p class="post_metadata">
		<span class="first">
			{s name="BlogInfoFrom"}{/s} {if $sArticle.linkSupplier}<a href="{$sArticle.linkSupplier}" title="{$sArticle.supplierName}">
			{$sArticle.supplierName}</a>{else}{$sArticle.supplierName}{/if}
		</span>
		<span>{$sArticle.changetime|date:date_long} {$sArticle.changetime|date:time_short}</span>
		{if $sArticle.categoryInfo.description}<span>{if $sArticle.categoryInfo.linkCategory}<a href="{$sArticle.categoryInfo.linkCategory}" title="{$sArticle.categoryInfo.description}">{$sArticle.categoryInfo.description}</a>{else}{$sArticle.categoryInfo.description}{/if}</span>{/if}
		<span {if $sArticle.sVoteAverange.averange =="0.00"}class="last"{/if}><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{if $sArticle.sVoteAverange.count}{$sArticle.sVoteAverange.count}{else}0{/if} {s name="BlogInfoComments"}{/s}</a></span>
		{if $sArticle.sVoteAverange.averange!="0.00"}
        	<span class="last star star{$sArticle.sVoteAverange.averange*2|round}">{se name="BlogInfoRating"}{/se}</span>
        {/if}
	</p>
	{/block}
	
	{* Article picture *}
	{if $sArticle.image.src.3}
	<div class="blog_picture">
		{block name='frontend_blog_col_article_picture'}
		{if !$homepage}
		<a href="{$sArticle.linkDetails}"title="{$sArticle.articleName}">
		{else}
		<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}" class="main_image">
		{/if}
	    <img src="{$sArticle.image.src.3}" alt="{$sArticle.articleName}" border="0" title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
	    </a>
	    {/block}
	</div>
	{/if}
	
	{* Article Description *}
	<div>
		{block name='frontend_blog_col_description'}
			{if $sArticle.description}{$sArticle.description|nl2br}{else}{$sArticle.description_long}{/if}
		{/block}
	</div>
	
	<div class="clear">&nbsp;</div>
	
	{* Read more button *}
	{block name='frontend_blog_col_read_more'}	
	<p>
		<a href="{url controller=detail sArticle=$sArticle.articleID}" title="{$sArticle.articleName}" class="more_info">{se name="BlogLinkMore"}{/se}</a>
	</p>
	{/block}
	{/block}
</div>