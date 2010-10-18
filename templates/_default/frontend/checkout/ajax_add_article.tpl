<div class="heading">
	<h2>{if !$sBasketInfo}{s name="AjaxAddHeader"}{/s}{else}{s name='AjaxAddHeaderError'}Hinweis:{/s}{/if}</h2>
	
	{* Close button *}
	<a href="#" class="modal_close" title="{s name='LoginActionClose'}{/s}">
		{s name='LoginActionClose'}{/s}
	</a>
</div>

{if $sBasketInfo}
<div class="error_container">
	<p class="text">
		{$sBasketInfo}
	</p>
	<div class="clear">&nbsp;</div>
</div>
{/if}

<div class="ajax_add_article">
	<div class="middle">
		{if $sArticle}
		<div class="article_box">
		
			{* Thumbnail *}
			<div class="thumbnail">
				<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}" class="artbox_thumb" {if $sArticle.image.src} 
					style="background: url({$sArticle.image.src.1}) no-repeat center center"{/if}>
					{if !$sArticle.image.src}<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />{/if}
				</a>
			</div>
			
			{* Title *}
			<strong class="title">{$sArticleName|truncate:37|strip_tags}</strong>
			
			{* Ordernumber *}
			<span class="ordernumber">{s name="AjaxAddLabelOrdernumber"}{/s}: {$sArticle.ordernumber}</span>
			
			{* Price *}
			<strong class="price">{$sArticle.price|currency}</strong>
			
			{* Quantity *}
			<span class="quantity">{s name="AjaxAddLabelQuantity"}{/s}: {$sArticle.quantity}</span>
		</div>
		{/if}
		
		{* Actions *}
		<div class="actions">
			{block name='frontend_checkout_ajax_add_article_action_buttons'}
			<a title="{s name='AjaxAddLinkBack'}{/s}" class="button-middle large modal_close">
				{se name="AjaxAddLinkBack"}{/se}
			</a>
			<a href="{url action='cart'}" class="button-middle" title="{s name='AjaxAddLinkCart'}{/s}">
				{se name="AjaxAddLinkCart"}{/se}<i></i>
			</a>
			<a href="{url action='confirm'}" class="button-right large right checkout" title="{s name='AjaxAddLinkConfirm'}{/s}">
				{se name="AjaxAddLinkConfirm"}{/se}<i></i>
			</a>
			<div class="clear">&nbsp;</div>
			{/block}
		</div>
		<div class="space">&nbsp;</div>
	</div>
	
	<div class="bottom">
		{block name='frontend_checkout_ajax_add_article_cross_selling'}
		{if $sCrossSimilarShown|@count || $sCrossBoughtToo|@count}
			<h2>{se name="AjaxAddHeaderCrossSelling"}{/se}</h2>
			<div class="slider_modal">
				{* Similar articles *}
				{if $sCrossSimilarShown}
					{assign var=count value=0}
					{foreach from=$sCrossSimilarShown item=article}
						{if $count == 0}
							<div class="slide">
						{/if}
						
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
						<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:28}</a>
						<p class="price">
							<span class="price">{if $article.priceStartingFrom && !$article.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
						</p>
						</div>
						
						{assign var=count value=$count + 1}
						{if $count == 4}
							</div>
							{assign var=count value=0}
						{/if}
					{/foreach}
				{* Bought too articles *}
				{else if !$sCrossSimilarShown && $sCrossBoughtToo}
					{assign var=count value=0}
					{foreach from=$sCrossSimilarShown item=article}
						{if $count == 0}
							<div class="slide">
						{/if}
						
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
						<a title="{$article.articleName}" class="title" href="{$article.linkDetails}">{$article.articleName|truncate:28}</a>
						<p class="price">
							<span class="price">{if $article.priceStartingFrom && !$article.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$article.price|currency} *</span>
						</p>
						</div>
						
						{assign var=count value=$count + 1}
						{if $count == 4}
							</div>
							{assign var=count value=0}
						{/if}
					{/foreach}
				{/if}
			</div>
		{/if}
		{/block}
	</div>
</div>