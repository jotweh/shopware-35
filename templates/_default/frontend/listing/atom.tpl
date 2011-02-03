<?xml version="1.0" encoding="{encoding}" ?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<link href="{$sCategoryContent.rssFeed|rewrite:$sCategoryContent.description|escape}" rel="self" type="application/rss+xml" />
	<author>
		<name>{$sShopname}</name>
	</author>
	{block name='frontend_listing_atom_title'}{$sCategoryContent.description}{/block}
	<id>{$sCategoryContent.rssFeed|rewrite:$sCategoryContent.description|escape}</id>
	<updated>{time()|date:atom}</updated>
{foreach from=$sArticles item=sArticle key=key name="counter"}
{block name='frontend_listing_atom_entry'}
	<entry> 
		<title>{block name='frontend_listing_atom_title'}{$sArticle.articleName|strip_tags|strip|truncate:80:"...":true|escape}{/block}</title>
		<id>{block name='frontend_listing_atom_article_name'}{$sArticle.linkDetails|rewrite:$sArticle.articleName|escape}{/block}</id>
		<link href="{block name='frontend_listing_atom_link'}{$sArticle.linkDetails|rewrite:$sArticle.articleName|escape}{/block}"/>
		<summary type="html">
			{block name='frontend_listing_atom_short_description'}
			{if $sArticle.description}
				{$sArticle.description|strip_tags|strip|truncate:280:"...":true|escape}
			{else}
				{$sArticle.description_long|strip_tags|strip|truncate:280:"...":true|escape}
			{/if}
			{/block}
		</summary>
		<content type="html">{$sArticle.description_long|escape}</content>
		{if $sArticle.changetime}
			<updated>{$sArticle.changetime|date:atom}</updated>
		{/if}
	</entry>
{/block}
{/foreach}
</feed>