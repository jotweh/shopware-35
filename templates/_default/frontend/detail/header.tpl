{extends file='frontend/index/header.tpl'}

{* Javascript *}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
//<![CDATA[

	try {
		jQuery(document).ready(function($) {
			$.tabNavi = $('#tabs').tabs();
			{if $sAction == 'ratingAction'}
				$.tabNavi.tabs('select', 1);
			{/if}
			
			if(window.location.hash == '#bewertung') {
				$.tabNavi.tabs('select', 1);
			}
			
			$('.write_comment').click(function(e) {
				e.preventDefault();
				$.tabNavi.tabs('select', 1);
			});
		});
	} catch(err) { if(debug) console.log(err) };

	var snippedChoose = "{s name='DetailChooseFirst'}{/s}";
	var isVariant = {if !$sArticle.sVariants}false{else}true{/if};
	var ordernumber = '{$sArticle.ordernumber}';
	var useZoom = '{$this->config("UseZoomPlus")}';
	
	jQuery.ordernumber = '{$sArticle.ordernumber}';		
//]]>
</script>
{/block}

{* Keywords *}
{block name="frontend_index_header_meta_keywords"}{if $sArticle.keywords}{$sArticle.keywords}{elseif $sArticle.sDescriptionKeywords}{$sArticle.sDescriptionKeywords}{/if}{/block}

{* Description *}
{block name="frontend_index_header_meta_description"}{if $sArticle.description}{$sArticle.description|replace:'"':"'"}{else}{$sArticle.description_long|strip_tags|replace:'"':"'"}{/if}{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}
<link rel="canonical" href="{$sArticle.linkDetailsRewrited}" title="{$sArticle.articleName}" />
{/block}