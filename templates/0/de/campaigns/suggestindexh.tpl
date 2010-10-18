<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
	<tr>
	<td>            
            <h2 style="color:#666666; font-size:11px; font-weight:bold; padding:10px 0 10px 30px; margin:0;">{$sRecommendations.description}</h2>
            <table width="100%" border="0" cellpadding="5" cellspacing="0"> 
            {foreach from=$sRecommendations.data item=sArticle name=suggestList key=key}
           
                 {if ($key) % 3}{else}<tr>{/if}
            <!-- ANFANG Artikel  -->
                <td height="247" width="33%" valign="bottom" align="center" background="images/products/products_bg.gif" style="background-repeat:no-repeat; background-position:center top; padding:5px 0px 15px 0px; border-bottom:1px solid #DEDEDE;">
                        <!--ARTIKEL CONTENT-->
                        
            <table width="180" height="100%" border="0" align="center" cellpadding="0" cellspacing="10" style="background-repeat:no-repeat; background-position:center top; padding:5px 0px 15px 0px;{cycle name="rahmen2" values="border-right:1px solid #DEDEDE,border-right:1px solid #DEDEDE,"}{if $smarty.foreach.suggestList.last}border-right:0px;{/if}">
                          <tr>
                                 <td height="30" colspan="2" valign="top" style="padding:10px 0 0 0;">
                                        <div align="center" style="height:100px; overflow:hidden;">
                                            <a target="_blank" href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">
                                            {if $sArticle.image.src}
                                                        <img src="{$sArticle.image.src.2}"  border="0">
                                                        {else}
                                                        <img src="http://intranet.shopware2.de/magneto/templates/0/de/media/img/de/layout/no_picture.jpg" alt="Kein Bild vorhanden" border="0" />{/if}
                                            </a>
                                        </div>
                                        <div style="text-align:left;">
                                        <h1 style="color:#000; font-size:12px; margin-bottom:5px; line-height:12px;">{$sArticle.articleName|truncate:40:"[..]"}</h1>
                                        <span style="font-size:11px; color:#333333;">{$sArticle.description_long|truncate:50:"..."}</span>
                                        </div>
                                </td>
                        </tr>
                            <tr>
                                <td height="20" style="text-align:right;">    
                                {if $sArticle.pseudoprice}
                                <span style="font-size:11px; line-height:13px;"><s>{$sArticle.pseudoprice}</s></span><br />
                                {/if}
                                {if $sArticle.pseudoprice}
                                    <strong style="color:#ff0033; font-size:14px;">&euro; {$sArticle.price}*</strong>
                                {else}
                                    <strong style="font-size:12px;">&euro; {$sArticle.price}*</strong>
                                {/if}
                                <!--##Attribute# -->
                                </td>
							</tr>
                  </table>
                    <!--END#ARTIKEL CONTENT-->                            </td>
				{if ($key+1) % 3}{else}</tr>{/if}
            {/foreach}
            </table>
            <!--CONTENT-->
    </td>
    </tr>
</table>