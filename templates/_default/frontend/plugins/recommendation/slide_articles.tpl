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
		
		{if $article.purchaseunit}
            <div class="article_price_unit">
                <p>
                    <strong>{se name="SlideArticleInfoContent"}{/se}:</strong> {$article.purchaseunit} {$article.sUnit.description}
                </p>
                {if $article.purchaseunit != $article.referenceunit}
                    <p>
                        {if $article.referenceunit}
                            <strong class="baseprice">{se name="SlideArticleInfoBaseprice"}{/se}:</strong> {$article.referenceunit} {$article.sUnit.description} = {$article.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
                        {/if}
                    </p>
                {/if}
            </div>
        {/if}
		
		<p class="price">
			<span class="price">{if $article.priceStartingFrom && !$article.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
		</p>
		</div>
	{/foreach}
</div>
<div class="pages">{$pages}</div>