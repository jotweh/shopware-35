<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="height:100%;">
<head>

<title>Newsletter</title>


{literal}

<style type="text/css">

<!--
body {
 font-family:Arial, Helvetica, sans-serif; 
 background-color:#F6F6F6;
}
#frame {

	border:1px solid #CCCCCC;

}

a:link, a:visited {
	color:#444444;
	text-decoration:none;
}

a:hover, a:active {

	color:#444444;

	text-decoration:none;

}
a:hover {

	color:#770D30;

	text-decoration:none;

}

-->

</style>

{/literal}


</head>

<body style="height:100%; font-family:Arial, Helvetica, sans-serif; background-color:#F6F6F6;">
<table border="0" align="center" cellpadding="0" cellspacing="0">
<tr>
    	<td>

<table width="612" height="100%" border="0" align="center" cellpadding="6" cellspacing="0">
  <tr>
    <td valign="top" height="100"  bgcolor="#FFFFFF" style="height:100px;">
    
    <!--HEADER-->
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr height="80">
  		  <td align="left" valign="top" style="padding-top:10px;padding-bottom:25px;">
            	<img src="images/logo.gif" /> &nbsp;      	  </td>
            <td align="center" valign="middle" style="height:25px;">
            	 <table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
					<td style="width:32px;"><img src="images/bt_meinkonto.gif" width="22" height="22" border="0" align="absmiddle" /></td>
					<td><a href="#" target="_blank" style="font-size:10px;height:25px;padding-bottom:15px;">{$sConfig.sSnippets.sIndexaccount}</a></td>
					</tr>
				</table>
            	
            	
            	&nbsp;
            	</td>
          <td width="230" valign="top">
       
			</td>
      	</tr>
		
        <tr style="background-color:#ffb588; border-bottom:1px solid #ffcaad;">
        	<td colspan="3">
            
            <div>
            	<table border="0" cellpadding="0" cellspacing="0">
  <tr height="26" style="border-right:1px solid #ffffff; font-size:11px; font-weight:bold;">
    		            <td align="center" style="padding: 0 5px 0 5px; border-right:1px solid #ffffff;">
            			    <a href="{$sBasefile}" target="_blank" title="Home" {if $_GET.sCategory==1} style="color:#FFFFFF;" {/if}>{$sConfig.sSnippets.sIndexhome}</a>						</td>
       		          	{foreach from=$sMainCategories item=sMainCategory}
                		<td align="center" style="{if $sMainCategory.flag}background-color:#0496ad; color:#FFFFFF;{/if}padding: 0 5px 0 5px; border-right:1px solid #ffffff;" >
			                <a href="{$sMainCategory.link}" target="_blank" title="{$sMainCategory.1}" {if $sMainCategory.flag} style="color:#FFFFFF;" {/if}>
            			    {$sMainCategory.description}</a>		                </td>
        		        {/foreach}                	</tr>
                </table></div>    		</td>
		</tr>
        <tr>
        	<td colspan="3" height="15" background="images/hor_gradient.gif" style="background-repeat:repeat-x;"></td>
		</tr>
    </table>
    
    <!--#HEADER-->
    </td>
  </tr>
  <tr>
    <td valign="top"  bgcolor="#FFFFFF">
    	<!--DUMMY-->
        {foreach from=$sCampaign.containers item=sCampaignContainer}
        
<!--BANNER-->

			{if $sCampaignContainer.type == "ctBanner"}
            <div style="text-align:center; margin-bottom:20px;">
                <a target="_blank" href="{$sCampaignContainer.data.link}">
				<img src="{$sCampaignContainer.data.image}" alt="Banner" width="600" border="0" align="middle" />                </a>
            </div>
			{/if}	
	
<!--BANNER-->
<!--TEXT-->
            {if $sCampaignContainer.type == "ctText"}	
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                <tr>
                	<td style=" background:#FAFAF5; border:1px solid #DEDEDE; padding:5px 5px 10px 5px;">
                <h2 style="color:#770D30; line-height:20px; font-size:16px; font-weight:bold; padding:10px 0px 0px 30px; margin:0px;">{$sCampaignContainer.description}</h2>
	                {if $sCampaignContainer.data.image}
	                	<table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
	                	<tr>
	                	{if $sCampaignContainer.data.alignment=="left"}
	                	<td>
	                	 <a target="_blank" href="{$sCampaignContainer.data.link}"><br />
						 <img src="{$sCampaignContainer.data.image}" alt="Banner" border="0" align="middle" />
						 </a>
	                	</td>
	                	{/if}
	                	<td>
	                	<div style=" padding:0 30px 20px  30px; color:#333; line-height:17px; font-size:11px;">{$sCampaignContainer.data.html} </div>
	                	</td>
	                	{if $sCampaignContainer.data.alignment=="right"}
	                	<td>
	                	 <a target="_blank" href="{$sCampaignContainer.data.link}"><br />
						 <img src="{$sCampaignContainer.data.image}" alt="Banner" border="0" align="middle" />
						 </a>
	                	</td>
	                	{/if}
	                	</tr>
					    </table>
	                
	                {else}
	                <div style=" padding:0 30px 20px  30px; color:#333; line-height:17px; font-size:11px;">{$sCampaignContainer.data.html} </div>
	                {/if}
					</td>
                </tr>
            </table>
            {/if}
 <!--TEXT-->

<!--LINKS-->
           {if $sCampaignContainer.type == "ctLinks"}
   	        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
				<tr>
				  <td style="padding:5px 5px 10px 5px;">
                        <h2 style="color:#666666; font-size:11px; font-weight:bold; margin:10px 0 10px 30px;">{$sCampaignContainer.description}</h2>
                    <ul style="font-size:13px;font-weight:bold;list-style-position:inside;list-style-type:none; list-style-image:none;">
                                    {foreach from=$sCampaignContainer.data item=sLink}
                                        <li><img src="images/blue_arrow.gif" width="14" height="8" />&nbsp;<a target="_blank" href="{$sLink.link}" style="text-decoration:none; color:#666666; font-size:11px;">{$sLink.description}
                                        </a>                                        </li>
                                    {/foreach}
                    </ul>	
				  </td>
				</tr>
            </table>
			{/if}
<!--LINKS-->


<!--PRODUKTE-->
			{if $sCampaignContainer.type == "ctSuggest"}
			<suggestions></suggestions>
			{/if}
			{if $sCampaignContainer.type == "ctArticles"}
             <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
				<tr>
				<td>            
	                    <h2 style="color:#666666; font-size:11px; font-weight:bold; margin:10px 0 10px 30px;">{$sCampaignContainer.description}</h2>
                        <!-- Anfang f&uuml;r Produktauflistung -->	 
                        <!-- Beginn Artikelauflistung | A1 | A2 | \n\r -->
                        <table width="100%" border="0" cellpadding="5" cellspacing="0"> 
                        {foreach from=$sCampaignContainer.data item=sArticle name=artikelListe}
                            {if $smarty.foreach.artikelListe.last is div by 3}
                        <!-- Marker: {$smarty.foreach.artikelListe.iteration} -->
                          {cycle values="<tr>,,"}
                            {/if}
                        <!-- ANFANG Artikel  -->
                            <td height="247" width="33%" valign="bottom" align="center" background="images/products/products_bg.gif" style="background-repeat:no-repeat; background-position:center top; padding:5px 0px 15px 0px; border-bottom:1px solid #DEDEDE;">
                                    <!--ARTIKEL CONTENT-->
                                    
                        <table width="180" height="100%" border="0" align="center" cellpadding="0" cellspacing="10" style="background-repeat:no-repeat; background-position:center top; padding:5px 0px 15px 0px;{cycle name="rahmen" values="border-right:1px solid #DEDEDE,border-right:1px solid #DEDEDE,"}{if $smarty.foreach.artikelListe.last}border-right:0px{/if}">
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
                                                <strong style="color:#ff0033; font-size:14px;">{$sConfig.sCURRENCYHTML} {$sArticle.price}</strong>
                                            {else}
                                                <strong style="font-size:12px;">{$sConfig.sCURRENCYHTML} {$sArticle.price}*</strong>
                                            {/if}
                                            <!--##Attribute# -->
                                            </td>
										</tr>
                              </table>
                                <!--END#ARTIKEL CONTENT-->                            </td>
							{if $smarty.foreach.artikelListe.iteration is div by 3}
                          {cycle values="</tr>,,"}
                            {/if}
                        {/foreach}
                        </table>
                        <!--CONTENT-->
                  </td>
                    </tr>
            </table>
                

			{/if}     
<!--PRODUKTE-->


		{/foreach}
        <!--#DUMMY-->
    </td>
  </tr>
</table>


		</td>
    </tr>
    <tr>
		<td height="15"></td>
</tr>
<tr>
	<td height="48">
<table width="612" border="0" align="center" cellpadding="6" cellspacing="0" bordercolor="#FFFFFF" bgcolor="#FFFFFF" >
	<tr>
    	<td style="font-size:10px;">
    	{$sConfig.sSnippets.sCampaignsNavigation}
</td>
	</tr>
    <tr>
    	<td>
	<div style="border-bottom:1px solid #666666; height:20px;">
	{if $sUserGroup.tax}
		<div style="float:left; width:400px; font-size:10px; text-align:left; color:#666666; padding:8px 0 0 0;">{$sConfig.sSnippets.sIndexpricesinclvat}</div>
	{else}
		<div style="float:left; width:400px; font-size:10px; text-align:left; color:#666666; padding:8px 0 0 0;">{$sConfig.sSnippets.sIndexallpricesexcludevat}</div>
	{/if}
    	<div style="float:right; width:150px; font-size:10px; text-align:right; color:#666666; padding:5px 0 0;">{* sSnippet: realized with *}{$sConfig.sSnippets.sIndexrealizedwith}<a href="http://www.shopware.ag" target="_blank" title="{* sSnippet: realized with the Webshop system of Hamann-media *}{$sConfig.sSnippets.sIndexrealizedwiththeshopsystem}"> {* sSnippet: shopware *}{$sConfig.sSnippets.sIndexshopware}</a><img src="images/shopware.gif" width="16" height="13" /></div>
	</div>
	</td>
  </tr>
  <tr>
    <td>
    <div style="float:left; width:400px; font-size:10px; text-align:left; color:#666666;">{$sConfig.sSnippets.sIndexcopyright}</div><br />
    <a href="{$sBasefile}?sViewport=newsletter" target="_blank" style="font-size:10px;">{$sConfig.sSnippets.sCampaignsUnsubscribe}</a>
    </td>
  </tr>
</table>    
        
        
        </td>
	</tr>
</table>
	</td>
  </tr>
</table>
<weblog></weblog>
</body>
</html>
