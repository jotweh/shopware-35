<!-- col_center -->
<div class="col_center">


	{* LIVE-SHOPPING - START *}
	{if $sLiveShopping}
	{foreach from=$sLiveShopping.liveshoppingData item=liveArt}
		{include file="articles/liveshopping/include_liveshopping.tpl" liveArt=$liveArt}
	{/foreach}
	{/if}
	{* LIVE-SHOPPING - END *}

	{if $sBanner}
		{if $sBanner.extension=="swf"}
			  <object classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" 
	          	codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0">
			    <param name="movie" value="{$sBanner.img}">
			    <param name="quality" value="high">
			    <param name="scale" value="exactfit">
			    <param name="menu" value="true">
			    <param name="bgcolor" value="#000040">
			    <embed src="{$sBanner.img}" quality="high" scale="exactfit" menu="false" width="653" height="170"
			           bgcolor="#000000"  swLiveConnect="false"
			           type="application/x-shockwave-flash"
			           pluginspage="http://www.macromedia.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash">
			    </embed>
		 	  </object>
		{elseif $sBanner.liveshoppingData}
			{include file="articles/liveshopping/include_liveshopping.tpl" liveArt=$sBanner.liveshoppingData}
		{else}
	        <!--  homepagebanner -->
	        {if $sBanner.link == "#" || $sBanner.link == ""}<div class="cat_banner">{else}<a href="{$sBanner.link}" class="cat_banner" {if $sBanner.link_target}target="{$sBanner.link_target}"{/if} title="{$sBanner.description}">{/if}
	        {if $sBanner.img}<img src="{$sBanner.img}" alt="{$sBanner.description}" name="{$sBanner.description}" border="0" title="{$sBanner.description}" />{/if}
	        {if $sBanner.link == "#" || $sBanner.link == ""}</div>{else}</a>{/if}
	        <!-- /homepagebanner -->
	   {/if}
	{/if}

	{if $sCategoryContent.cmsheadline}
        {* CATEGORY_TEXT *}
            <div class="cat_text">
                <h1>{$sCategoryContent.cmsheadline}</h1>
                <p>{$sCategoryContent.cmstext}</p>
            </div>
        {* /CATEGORY_TEXT *}
	{/if}
	
	
	
	<!-- listing_box2 -->
	<div class="listing_box2">
        <div class="listing_box2_top"></div>
            {foreach from=$sOffers item=offer key=key  name="counter"}
                {if $offer.mode == "gfx"}
                    {include file="articles/article_box_img.tpl" sArticle=$offer}
                {elseif $offer.mode == "livefix" || $offer.mode == "liverand" || $offer.mode == "liverandcat"}
					{include file='articles/liveshopping/include_liveshopping.tpl' liveArt=$offer.liveshoppingData}
                {else}
                    {include file="articles/article_box_3col.tpl" sArticle=$offer}
                {/if}	
            {/foreach}
            <div class="fixfloat"></div>
            <div class="listing_box_cap2"></div>
            
		    {if $sBlog.sArticles|@count}
			<div class="listing_box">
			<h1>{* sSnippet: article *}{$sConfig.sSnippets.sBlogNewInTheBlog}:</h1>
			{foreach from=$sBlog.sArticles item=article key=key name="counter"}
				{include file="blog/col.tpl" sArticle=$article key=$key homepage=true}
			{/foreach}
			</div>
			{/if}     
	</div>
	<!-- /listing_box2 -->
</div>
<!-- /col_center -->


    <!-- col_right1 -->
    <div class="col_right1">
        {include file="category/category_right_charts.tpl" sCharts=$sCharts}
        <div class="fixfloat"></div>
    </div>
    
    <!-- /col_right1 -->
    <div class="fixfloat"></div>

	<!-- tagcloud -->
    <div class="tagcloud_promotion">
        <div class="fixfloat"></div>
        {foreach from=$sCloud item=sCloudItem}
        	<a href="{$sCloudItem.link}" title="{$sCloudItem.name}" class="{$sCloudItem.class}">{$sCloudItem.name}</a> 
        {/foreach}
        <div class="fixfloat"></div>
    </div>&nbsp;
	<!-- /tagcloud -->

 <div class="fixfloat"></div>


