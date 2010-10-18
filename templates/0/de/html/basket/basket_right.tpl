<div class="col_right2">
{if $sShippingcostsDifference.float}
<div class="shippingfree_basket">
<strong>{$sConfig.sSnippets.sArticlefreeshipping}</strong>
{* sSnippet: Difference to shippingfree *}{$sConfig.sSnippets.sBasketshippingdifference|replace:"#1":$sConfig.sCURRENCYHTML|replace:"#2":$sShippingcostsDifference.formated}
</div>
{/if}
{if $sPremiums}
<div class="accept_box">
{* sSnippet: please, choose between the following premiums *}{$sConfig.sSnippets.sBasketbetweenfollowingpremium}
</div>
<div class="box_premiumcontainer">
{foreach from=$sPremiums item=premium key=key}
	<div class="box4_middle" style="padding-top:10px;">
	    
	    <!-- article name start -->
	        {if $premium.sArticle.active}
	        <a href="{$premium.sArticle.linkDetails}" title="{* sSnippet: more informations *}{$sConfig.sSnippets.sBasketmoreinformations} {$premium.sArticle.articleName}">
	        {if $premium.sArticle.image.src}<img src="{$premium.sArticle.image.src.1}" alt="{$premium.sArticle.articleName}" title="{$premium.sArticle.articleName}" border="0"/>
	        {else}<img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: no picture available*}{$sConfig.sSnippets.sBasketnopictureavailable}" />{/if}
	        </a>
	        
	        <a href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">{$premium.sArticle.articleName|wordwrap:39:"":true}</a>
	        {else}
	        	<a  title="{* sSnippet: more informations *}{$sConfig.sSnippets.sBasketmoreinformations} {$premium.sArticle.articleName}">
	        {if $premium.sArticle.image.src}
	        	<img src="{$premium.sArticle.image.src.3}" alt="{$premium.sArticle.articleName}" title="{$premium.sArticle.articleName}" border="0"/>
	        {else}
	        	<img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: no picture available*}{$sConfig.sSnippets.sBasketnopictureavailable}" />{/if}
	        	</a>
	            
	        <a href="{$premium.sArticle.linkDetails}" title="{$premium.sArticle.articleName}">{$premium.sArticle.articleName|wordwrap:39:"":true}</a>
	        {/if}
	        <!-- article name end -->
	       
	   <div class="fixfloat"></div>
	   {if $premium.available}
		  <form class="clearfix" action="{$sStart}" method="get" id="sAddPremiumForm{$key}" name="sAddPremiumForm{$key}"/>
		  <input type="hidden" name="sViewport" value="basket">
	 	  {if $premium.sVariants&&$premium.sVariants|@count>1}
	 	   <select style="" class="variant" id="sAddPremium{$key}" onchange="" name="sAddPremium">
		 	<option value="{$premium.sArticle.ordernumber}">{* sSnippet: please choose *}{$sConfig.sSnippets.sBasketpleasechoose}</option>
		  {foreach from=$premium.sVariants item=variant}
				<option value="{$variant.ordernumber}">{$variant.additionaltext}</option>
		   {/foreach}
		   </select>
		  {else}
		   <input type="hidden" name="sAddPremium" value="{$premium.sArticle.ordernumber}">
		  {/if}
		  
		  
		    <input type="submit" class="bt_basket_bonus" name="{* sSnippet: into the basket *}{$sConfig.sSnippets.sBasketchoosepremium}" title="{$premium.sArticle.articleName} {* sSnippet: in the basket *}{$sConfig.sSnippets.sBasketinthebasket}" value="{* sSnippet: into the basket *}{$sConfig.sSnippets.sBasketchoosepremium}" />
		    
		  
		  </form>
	  {else}
	  <div class="bonus_price">
	  <p class="pr1">({$sConfig.sSnippets.sBasketPremiumDifference} {$premium.sDifference} {$sConfig.sCURRENCYHTML})</p>
	  <p class="pr2">{* sSnippet: from *}{$sConfig.sSnippets.sBasketfrom} {$premium.startprice} {$sConfig.sCURRENCYHTML}</p>
	  </div>{/if}
	  
</div>
{/foreach}
</div>
{/if}
	
	
</div>

<div class="fixfloat"></div>
