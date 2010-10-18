{* Article picture *}
{if $sArticle.image.src.4}
	{block name='frontend_blog_images_main_image'}
	{if $sArticle.image.res.relations}
		<div id="img{$sArticle.image.res.relations}" class="displaynone">
	    	<a href="{$sArticle.image.src.original}" 
	    		rel="lightbox" 
	    		title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" 
	    		class="main_image">
	    			
	    		<img src="{$sArticle.image.src.4}" 
	    			alt="{$sArticle.articleName}" 
	    			border="0" 
	    			title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
	    		&nbsp;
	    	</a>
			</div>
	{/if}
	{/block}
	<div id="imgTarget">
	    <a href="{$sArticle.image.src.original}" 
	    	rel="lightbox[photos]" 
	    	title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" 
	    	class="main_image">
	    		
	    	<img src="{$sArticle.image.src.4}" 
	    	alt="{$sArticle.articleName}" 
	    	border="0" 
	    	title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" />
	    </a>
    </div>
	    	
	{* Thumbnails *}
	{if $sArticle.images}
		{block name='frontend_blog_images_thumbnails'}
		<div class="thumbnail_box">
			{foreach from=$sArticle.images item=sArticleImage}
				{if $sArticleImage.relations}
					<div id="img{$sArticleImage.relations}" class="displaynone">
						<a href="{$sArticleImage.src.5}" rel="lightbox[photos" 
							title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}">
			   						
							<img src="{$sArticleImage.src.4}" title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" />
						</a>
					</div> 
				{else}
					<a href="{$sArticleImage.src.5}" 
						rel="lightbox" 
						title="{if $sArticleImage.description}{$sArticleImage.description}{else}{$sArticle.articleName}{/if}" 
						style="background: #fff url({$sArticleImage.src.1}) no-repeat center center;">
			    			&nbsp;
			    	</a> 
			    {/if}
			{/foreach}
	    	<div class="space">&nbsp;</div>
	    </div>
	    {/block}
	{/if}
{/if}