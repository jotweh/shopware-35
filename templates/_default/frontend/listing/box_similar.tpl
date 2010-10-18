{block name="frontend_listing_box_similar"}
<div class="artbox">
	<div class="inner">
        
		{* Article picture *}
		{block name='frontend_listing_box_similar_article_picture'}
		<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}" class="artbox_thumb {if !$sArticle.image.src}no_picture{/if}" {if $sArticle.image.src} 
			style="background: #fff url({$sArticle.image.src.1}) no-repeat center center"{/if}>&nbsp;
		{/block}
		</a>
		
		<div class="title_price">
			{* Article name *}
			<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}">
				{block name='frontend_listing_box_similar_name'}
				<strong class="title">{$sArticle.articleName|truncate:47}</strong>
				{/block}
			</a>
		
			{* Price *}
			{block name='frontend_listing_box_similar_price'}
			<p class="price">
		        {if $sArticle.pseudoprice}
		        	<span class="pseudo">{$sArticle.pseudoprice|currency}</span>
		        {/if}
		        <span class="price">{$sArticle.price|currency} *</span>
	        </p>
	        {/block}
        </div>
       	
       	{* Compare and more *}
       	{block name='frontend_listing_box_similar_actions'}
       	<div class="actions">
			<a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{s name='SimilarBoxMore'}{/s} {$sArticle.articleName}" class="more">{se name='SimilarBoxLinkDetails'}{/se}</a>
		</div>
		{/block}
		
	</div>
</div>
{/block}