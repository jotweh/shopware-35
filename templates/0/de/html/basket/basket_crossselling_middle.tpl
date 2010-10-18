<!-- col_center2 -->
<div class="col_center2">
        <div class="bg_cross">
            <h1>{$sArticleName} {* sSnippet: added to the basket *}{$sConfig.sSnippets.sBasketaddedtothebasket}</h1>
            <a href="javascript:history.back()" title="{* sSnippet: back to mainpage *}{$sConfig.sSnippets.sBasketbacktomainpage}" class="bt_continue">{* sSnippet: continue shopping *}{$sConfig.sSnippets.sBasketcontinueshopping}</a>
            <a href="{$sBasefile}?sViewport=basket" class="bt_gobasket" title="{* sSnippet: show basket *}{$sConfig.sSnippets.sBasketshowbasket}">{* sSnippet: show basket *}{$sConfig.sSnippets.sBasketshowbasket}</a>
            <a href="{$sBasefile}?sViewport=sale&sUseSSL=1" title="{* sSnippet: to checkout! *}{$sConfig.sSnippets.sBaskettocheckout}" class="bt_toorder2">{* sSnippet: checkout *}{$sConfig.sSnippets.sBasketcheckout}</a>
        </div>
    
    <!-- Kunden kauften auch -->
    {if $sCrossBoughtToo}
        <!-- cross_box -->
        <div class="cross_box">
            <div class="cross_box_top1"><h2 style="color:#a1894e;">{* sSnippet: Customers with your goods basket contents, also shop *}{$sConfig.sSnippets.sBasketcheckoutcustomerswithyo}</h2></div>
                <div style="padding:0px;">
                {foreach from=$sCrossBoughtToo item=sArticle key=key}				
                    {include file="articles/article_box_4col_cross_boughttoo.tpl" sArticle=$sArticle}		
                {/foreach}
                <div class="fixfloat"></div>
                </div>
            <div class="cross_box_cap"></div>
        </div>
        <!-- cross_box -->
    {/if}
    <!-- /Kunden kauften auch -->
    
    <!-- Kunden haben auch angeschaut -->
    {if $sCrossSimilarShown}
        <!-- cross_box -->
            <div class="cross_box">
                <div class="cross_box_top2"><h2 style="color:#a1894e;">{* sSnippet: Customers with similar interests, have also looked *}{$sConfig.sSnippets.sBasketcustomerswithyoursimila}</h2></div>
                    <div style="padding: 0px;">
                    {foreach from=$sCrossSimilarShown item=offer key=key}
                        {include file="articles/article_box_4col_cross_similar.tpl" sArticle=$offer}
                    {/foreach}
                    <div class="fixfloat"></div>
                    </div>
                <div class="cross_box_cap"></div>
            </div>
        <!-- cross_box -->
    {/if}
    <!-- /Kunden haben auch angeschaut -->
    
    <div class="fixfloat"></div>
</div>
<!-- /col_center2 -->



