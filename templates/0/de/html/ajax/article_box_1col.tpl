<table border="1" cellpadding="0" cellspacing="0" width="200">
{* ARTICLE PICTURE *}
<tr>
	<td valign="top" style="height:155px;border-top:1px solid #D9DDE3;border-bottom:1px solid #D9DDE3;">
		<div style="height:150px;">
			<a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|replace:"&":"&"}" {if $sArticle.image.src} style="display:block;height:150px;background: url({$sArticle.image.src.2}) no-repeat center 20px;"{/if}>
				{if !$sArticle.image.src}
					
				{/if}
			</a>
		</div>
	</td>
</tr>
{* /ARTICLE PICTURE *}

{* ARTICLE NAME *}
<tr>
	<td valign="top" style="height:50px;border-bottom:1px solid #D9DDE3;">
		<h1 style="padding: 0; text-align:left;"><a href="{$sArticle.linkDetails}" title="{$sArticle.articleName|wordwrap:39:"":true}">{$sArticle.articleName|truncate:47}</a></h1>
	</td>
</tr>
{* /ARTICLE NAME *}

{* ARTICLE RATING *}
<tr >
	{if $sArticle.sVoteAverange.averange!="0.00"}
		<td  valign="top" style="height:50px;border-bottom:1px solid #D9DDE3;">
			<p class="stat">
			    {if $sArticle.sVoteAverange.averange < 0.5}
			    <img src="{$sTemplate}/media/img/default/stars/star_0.gif" alt="0 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 0.5 && $sArticle.sVoteAverange.averange < 1}
    			<img src={$sTemplate}/media/img/default/stars/star_01.gif" alt="1 Punkt" />
    			{elseif $sArticle.sVoteAverange.averange >= 1.0 && $sArticle.sVoteAverange.averange < 1.5}
    			<img src="{$sTemplate}/media/img/default/stars/star_02.gif" alt="2 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 1.5 && $sArticle.sVoteAverange.averange < 2}
    			<img src="{$sTemplate}/media/img/default/stars/star_03.gif" alt="3 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 2.0 && $sArticle.sVoteAverange.averange < 2.5}
    			<img src="{$sTemplate}/media/img/default/stars/star_04.gif" alt="4 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 2.5 && $sArticle.sVoteAverange.averange < 3}
    			<img src="{$sTemplate}/media/img/default/stars/star_05.gif" alt="5 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 3.0 && $sArticle.sVoteAverange.averange < 3.5}
	    		<img src="{$sTemplate}/media/img/default/stars/star_06.gif" alt="6 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 3.5 && $sArticle.sVoteAverange.averange < 4}
    			<img src="{$sTemplate}/media/img/default/stars/star_07.gif" alt="7 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 4.0 && $sArticle.sVoteAverange.averange < 4.5}
    			<img src="{$sTemplate}/media/img/default/stars/star_08.gif" alt="8 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 4.5 && $sArticle.sVoteAverange.averange < 5}
    			<img src="{$sTemplate}/media/img/default/stars/star_09.gif" alt="9 Punkte" />
    			{elseif $sArticle.sVoteAverange.averange >= 5.0}
			    <img src="{$sTemplate}/media/img/default/stars/star_10.gif" alt="10 Punkte" />
    			{/if}
			</p>	
		</td>
	{else}
	<td  valign="top" style="height:50px;border-bottom:1px solid #D9DDE3;"></td>
	{/if}
</tr>
{* /ARTICLE RATING *}

{* ARTICLE DESCRIPTION *}
<tr>
	<td  valign="top" style="height:110px;border-bottom:1px solid #D9DDE3;">

		<div class="article-description" style="height:100px; text-align:left;">
			<p>
    			{$sArticle.description_long|replace:"<b>":""|replace:"<strong>":""|replace:"</strong>":""|replace:"</b>":""|replace:"<B>":""|replace:"<STRONG>":""|replace:"</STRONG>":""|replace:"</B>":""|replace:"&":"&"|truncate:150|wordwrap:19:"\n":true}
    		</p>	
		</div>

	</td>
</tr>
{* /ARTICLE DESCRIPTION *}

<tr >
	<td valign="top" style="height:38px;border-bottom:1px solid #D9DDE3;">
{* ARTICLE PRICE *}
    	<p {if $sArticle.pseudoprice} class="article-price2" style="font-size:16px; text-align: right; margin-right: 5px;"{else} class="article-price" style="font-size:16px; text-align: right; margin-right: 5px;"{/if}>
    		{if $sArticle.pseudoprice}<s>{$sConfig.sCURRENCYHTML} {$sArticle.pseudoprice}</s><br />{/if}
    		<strong>{if $sArticle.priceStartingFrom}ab {/if}{$sConfig.sCURRENCYHTML} {$sArticle.price}</strong>*
    	</p>
{* /ARTICLE PRICE *}
	</td>
</tr>
{foreach from=$sProperties item=property key=key}
<tr>
	<td style="text-align:left; height:35px; background-color: {cycle name=$sArticle.articleID values="#f0f0f0, #fff"}">
	
		<div style="width:70px; float:left;" name="test2">{if $sArticle.sPropertiesData.$key}{$sArticle.sPropertiesData.$key}{else}-{/if}</div>
		
	</td>
</tr>
{/foreach}

</table>


   
	
	
	
	

	
	
    
    

