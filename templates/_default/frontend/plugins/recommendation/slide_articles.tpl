<div class="slide">
	{foreach from=$articles item=article}
		{assign var=image value=$article.image.src.2}
		<div class="article_box">
		<!-- article 1 -->
		{if $image}
		<a style="background: url({$image}) no-repeat scroll center center transparent;" class="artbox_thumb" title="{$article.articleName}" href="{$article.linkDetails}">
		</a>
		{else}
		<a class="artbox_thumb no_picture" title="{$article.articleName}" href="{$article.linkDetails}">
		</a>
		{/if}
		<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:35}</a>
		<p class="price">
			<span class="price">{if $article.priceStartingFrom && !$article.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
		</p>
		</div>
	{/foreach}
</div>
<div class="pages">{$pages}</div>