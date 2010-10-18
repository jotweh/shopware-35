<table border="1" cellpadding="0" cellspacing="0" style="width:100px;background-color:#efefef;font-weight:bold;margin-left:10px;border-left:1px solid #efefef;">

{* ARTICLE PICTURE *}
	<tr >
		<td valign="top" style="height:155px;border-top:1px solid #fff;border-bottom:1px solid #efefef;">
			{* sSnippet: headline picture *}{$sConfig.sSnippets.sCompareheadlinepicture}
		</td>
	</tr>
{* /ARTICLE PICTURE *}

{* ARTICLE NAME *}
	<tr >
		<td valign="top" style="height:50px;border-bottom:1px solid #fff">
			{* sSnippet: headline name *}{$sConfig.sSnippets.sCompareheadlinename}
		</td>
	</tr>
{* ARTICLE VOTE  *}
	<tr >
		<td valign="top" style="height:50px;border-bottom:1px solid #fff">
			{* sSnippet: headline vote *}{$sConfig.sSnippets.sCompareheadlinevoting}
		</td>
	</tr>
{* ARTICLE DESCRIPTION *}
	<tr>
		<td  valign="top" style="height:110px;border-bottom:1px solid #fff;">
			{* sSnippet: headline description *}{$sConfig.sSnippets.sCompareheadlinedescription}
		</td>
	</tr>
{* /ARTICLE PRICE *}

	<tr >
		<td valign="top" style="height:38px;border-bottom:1px solid #fff;">
			{* sSnippet: headline price *}{$sConfig.sSnippets.sCompareheadlineprice}
		</td>
	</tr>


{foreach from=$sProperties item=property}
	<tr>
		<!--<td style="text-align:left; background-color: {cycle name=$sArticle.articleID values="#f0f0f0, #fff"}">-->
		<td style="height:35px;border-bottom:1px solid #fff;">
			<div style="width:100px; float:left;" name="test">{$property}:</div>
		</td>
	</tr>
{/foreach}

</table>


   
	
	
	
	

	
	
    
    

