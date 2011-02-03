{extends file='frontend/index/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
<div class="blogbox grid_16 last">
	
	{* Article name *}
	{block name='frontend_blog_detail_title'}
		<h1>{$sArticle.articleName}</h1>
	{/block}
	<p class="post_metadata">
		
		{* Author *}
		{block name='frontend_blog_detail_author'}
		<span class="first">
			{se name="BlogInfoFrom"}{/se} {$sArticle.supplierName}
		</span>
		{/block}
		
		{* Date *}
		{block name='frontend_blog_detail_date'}
		<span>
			{$sArticle.changetime|date:DATE_LONG} {$sArticle.changetime|date:time_short}
		</span>
		{/block}
		
		{* Category *}
		{block name='frontend_blog_detail_category'}
		<span {if $sArticle.sVoteAverange.averange == "0.00"}class="last"{/if}>
			{se name="BlogInfoCategories"}{/se}
		</span>
		{/block}
		
		{* Comments *}
		{block name='frontend_blog_detail_comments'}
		{if $sArticle.sVoteAverange.averange!="0.00"}
		<span class="last">
			<a href="#commentcontainer" title="{s name="BlogLinkComments"}{/s}">
				{if $sArticle.sVoteAverange.count}
					{$sArticle.sVoteAverange.count}
				{else}
					0
				{/if} 
				{se name="BlogInfoComments"}{/se}
			</a>
		</span>
		{/if}
		{/block}
	</p>
	
	{* Description *}
	{block name='frontend_blog_detail_description'}
		<div class="description">
			{$sArticle.description_long|nl2br}
		</div>
	{/block}
		
	<div class="grid_6 social last">
		{* Image + Thumbnails *}
		{block name='frontend_blog_detail_images'}
			{include file="frontend/blog/images.tpl"}
		{/block}
		
		<h2 class="headingbox">{s name="BlogHeaderSocialmedia"}{/s}</h2>
		
		<div class="outer">
			{* Bookmarks *}
			{block name='frontend_blog_detail_bookmarks'}
				{include file="frontend/blog/bookmarks.tpl"}
			{/block}
			
			{* Rating*}
			{block name='frontend_blog_detail_rating'}
			<div class="rating">
				<h5 class="bold">
					{se name="BlogHeaderRating"}{/se}:
				</h5>
				<div class="star star{$sArticle.sVoteAverange.averange*2|round}">{se name="BlogHeaderRating"}{/se}</div>
			</div>
			{/block}
			
			{* Tags *}
			{if $sArticle.sProperties}
				{block name='frontend_blog_detail_tags'}
				<div class="tags">
					<h5 class="bold">
						{se name="BlogInfoTags"}{/se}:
					</h5>
					{foreach from=$sArticle.sProperties item=sProperty}
						<span class="tag">{$sProperty.value}</span>
					{/foreach}
					<div class="clear">&nbsp;</div>
				</div>
				{/block}
			{/if}
		</div>
	</div>
	
	<div class="doublespace">&nbsp;</div>
	
	{* Links *}
	{if $sArticle.sLinks|@count>1}
		{block name='frontend_blog_detail_links'}
		<div class="links">
		<h2>
			{se name="BlogHeaderLinks"}{/se}
		</h2>
			{foreach from=$sArticle.sLinks item=information}
				{if !$information.supplierSearch}
					<a href="{$information.link}" title="{$information.description}" target="{$information.target}" rel="nofollow" class="ico link">
						{$information.description}
					</a>
				{/if}
			{/foreach}
		</div>
		{/block}
	{/if}        
     
	   	{* Downloads *}
	    {if $sArticle.sDownloads}
	    	{block name='frontend_blog_detail_downloads'}
			<div class="downloads">
				<h2>
					{se name="BlogHeaderDownloads"}{/se}
				</h2>
				{foreach from=$sArticle.sDownloads item=download}
					<a href="{$download.filename}" title="{$download.description}" target="_blank" class="ico link">
						{se name="BlogLinkDownload"}{/se} {$download.description}
					</a>
				{/foreach}
				<div class="doublespace">&nbsp;</div>
				{/block}
			</div>
		{/if}
		
		{* Cross selling *}
		{if $sArticle.sRelatedArticles}
			<h2 class="headingbox">{s name="BlogHeaderCrossSelling"}{/s}</h2>
			<div class="bloglisting" id="listing-blog">
				{foreach from=$sArticle.sRelatedArticles item=related name=relatedarticle}
					{if $smarty.foreach.relatedarticle.last}
						{assign var=lastitem value=1}
					{else}
						{assign var=lastitem value=0}
					{/if}
					{include file="frontend/listing/box_blog.tpl" sArticle=$related lastitem=$lastitem}
				{/foreach}
			</div>
		{/if}
		
		{* Our Comment *}
		{if $sArticle.attr3}
			{block name='frontend_blog_detail_comment'}
			<div id="unser_kommentar">
				<p>
					{se name="BlogInfoComment"}{/se} "{$sArticle.articleName}"
				</p>
				<blockquote>
					{$sArticle.attr3}
				</blockquote>
			</div>
			{/block}
		{/if}		
				
		{* Comments *}
		{block name='frontend_blog_detail_comments'}
			{include file="frontend/blog/comments.tpl"}
		{/block}
	</div>
{/block}

{* Empty sidebar right *}
{block name='frontend_index_content_right'}{/block}