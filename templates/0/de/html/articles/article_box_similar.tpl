{* SIMILAR RULE *}
    <div class="similar_rule"{if $sArticle.purchaseunit} style="height:85px;" {/if}>
    
        {* ARTICLE PICTURE START *}
            <a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|replace:"&":"&"}" class="article_image">
                {if $sArticle.image.src}
                    <img src="{$sArticle.image.src.1}" alt="{$sArticle.articleName|replace:"&":"&"}" title="{$sArticle.articleName|replace:"&":"&"}" border="0"/>
                {else}
                    <img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: No picture available *}{$sConfig.sSnippets.sArticlenoPicture}" />
                {/if}
            </a>
       {* ARTICLE PICTURE END *}
        
    
        {* ARTICLE NAME START *}
            <a href="{$sArticle.linkDetails}" class="article_description" title="{$sArticle.articleName|replace:"&":"&"}">{$sArticle.articleName|wordwrap:39:"\n":true}</a>
        {* ARTICLE NAME END *}
        
        <p {if $sArticle.pseudoprice} class="article_price2"{else} class="article_price"{/if}{if $sArticle.purchaseunit} style="line-height:18px;" {/if}>
        {if $sArticle.pseudoprice}<s>{$sConfig.sCURRENCYHTML} {$sArticle.pseudoprice}</s><br/>{/if}
        <strong {if $sArticle.pseudoprice} style="color:#FF0033;"{else} style="font-size: 10px;"{/if}>{if $sArticle.priceStartingFrom}{$sConfig.sSnippets.sArticlefrom} {/if}{$sConfig.sCURRENCYHTML} {$sArticle.price}*</strong>
         
        {if $sArticle.purchaseunit}
	    	{if $sArticle.purchaseunit == $sArticle.referenceunit} {else}
		    
		     {if $sArticle.referenceunit}<br/>{$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice} {$sConfig.sCURRENCYHTML}{/if}
		     
	     	{/if}
     	{/if} 
        </p>
         
        
	<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|replace:"&":"&"}" class="more">{$sConfig.sSnippets.sArticleButtonMore}</a>
    </div>
{* /SIMILAR RULE *}