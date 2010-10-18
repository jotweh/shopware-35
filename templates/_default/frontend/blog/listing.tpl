<div class="listing-blog">
	{if $sCategoryContent.cmsheadline || $sCategoryContent.cmstext}
		{include file="frontend/listing/text.tpl"}
	{/if}
	
	{if $sOffers}
		{foreach from=$sOffers item=offer key=key name="counter"}
				{if $offer.mode == "gfx"}
					{include file="frontend/listing/promotion_image.tpl" sArticle=$offer}
				{else}
					{include file="frontend/blog/box.tpl" sArticle=$offer}
				{/if}	
		{/foreach}
	{/if}
	{if $sArticles}
		{foreach from=$sArticles item=article key=key name="counter"}
			{include file="frontend/blog/box.tpl" sArticle=$article key=$key}
		{/foreach}
	{/if}
</div>