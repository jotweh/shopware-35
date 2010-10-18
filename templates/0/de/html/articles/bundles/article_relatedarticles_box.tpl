{* PARAM $sRelatedArticles *}
{if $sRelatedArticles}
	<div id='related_box' {if $sArticle.sVariants}style="display:none;"{/if}>
	<h2>{* sSnippet: Bundle Buy this with those *}{$sConfig.sSnippets.sArticlesBundleBuy}:</h2>

		<form name="sAddToBasket" method="GET" action="{$sStart}" class="clearfix">
		<div class="box_Relatedset">
		{* SELECTED ARTICLE *}
				<div id="related_main_image">
					{if $sArticle.image.src.1}
						<a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url({$sArticle.image.src.1});"></a>
					{else}
						<a href='#' title="{$sArticle.articleName}" class="bundleImg" style="background-image: url(../../media/img/de/layout/no_picture.jpg);"></a>
					{/if}
				</div>
		{* /SELECTED ARTICLE *}
			{foreach from=$sRelatedArticles item=relatedArticle}
					<p id='{$relatedArticle.ordernumber}_related_plusicon' class="RelatedPlus">+</p>
					<div id='{$relatedArticle.ordernumber}_related_container' class="box_RelatedImg">
					{if $relatedArticle.image.src[1]}
						<a href="{$relatedArticle.linkDetails}" class="bundleImg" title="{$relatedArticle.articleName}" style="background-image: url({$relatedArticle.image.src[1]});"></a>
					{else}
						<a href="{$relatedArticle.linkDetails}" class="bundleImg" title="{$relatedArticle.articleName}" style="background-image: url(../../media/img/de/layout/no_picture.jpg);"></a>
					{/if}
					</div>
				{assign var="first" value=1}
			{/foreach}
		<div class="fixfloat"></div>
		</div>
		<div class="box_RelatedPrice">
			<div class="RelatedBasketbutton">
				<input type='hidden' name='sAddRelatedArticles' value='basket' />
				<input id='related_main_ordernumber' type="hidden" name='sAdd' value='{$sArticle.ordernumber}' />
				<input type='hidden' id='sRelatedOrdernumbers' name='sRelatedOrdernumbers' value='' />
				<input type='hidden' name='sViewport' value='basket' />
				<input class="bundleBasketButton" type="submit"  title="{$sArticle.articleName} {* sSnippet: add article to basket *}{$sConfig.sSnippets.sArticleinthebasket}" name="{* sSnippet: add to basked *}{$sConfig.sSnippets.sArticleaddtobasked}" value="{$sConfig.sSnippets.sArticleaddtobasked}" style="visibility: visible; opacity: 1;"/>
			</div>
			<p>{* sSnippet: Prices for all *}{$sConfig.sSnippets.sArticlesBundlePricesForAll}: </p>
			<p><span>{$sConfig.sCURRENCYHTML} </span><span id='price_relatedbundle'></span></p>
			<div class="fixfloat"></div>
		</div>
		</form>

	<div class="fixfloat"></div>

	<!--<input type="checkbox" style="float:left;margin-right:8px;" checked disabled/>
	<p><b>Dieser Artikel</b></p>
	<div style="clear:both;"></div>-->
<div class="relatedChecker">
	{foreach from=$sRelatedArticles item=relatedArticle}
		<input id="{$relatedArticle.ordernumber}_related_checkbox" type="checkbox" style="float:left;margin-right:8px;" checked onclick="refreshRelatedArticle();" />
		<p style="float:left;margin-right:8px;"><u><a href="{$relatedArticle.linkDetails}">{$relatedArticle.articleName}</a></u></p>
		<p style="float:left;margin-right:8px;">{if $relatedArticle.description}{$relatedArticle.description|truncate:60}{else}{$relatedArticle.description_long|truncate:60}{/if}</p>
		<div style="clear:both;"></div>

		{* JAVASCRIPT Zwischenspeicher Zubebörartikel *}
		<input class="relatedOrdernumber" type="hidden" value="{$relatedArticle.ordernumber}" />
		<input type="hidden" id="{$relatedArticle.ordernumber}_checked" />
		<input type="hidden" id="{$relatedArticle.ordernumber}_price" value="{$relatedArticle.price|replace:',':'.'}"/>
		{* /JAVASCRIPT Zwischenspeicher Zubebörartikel* *}

	{/foreach}
		<div class="fixfloat"></div>
</div>
	{* JAVASCRIPT Zwischenspeicher Ausgewählter Artikel*}
	<input type="hidden" id="selected_articel_price" value='{$sArticle.price|replace:',':'.'}' />
	{* /JAVASCRIPT Zwischenspeicher Ausgewählter Artikel*}

	<div class="fixfloat"></div>

	</div>
	{* /Varianten hide *}

{/if}
<div class="fixfloat"></div>
<!--{$relatedArticle.ordernumber}_related_container-->

{* JAVASCRIPT *}
{literal}
<script type="text/javascript">
/**
 *
 * @access public
 * @return void
 **/
function refreshRelatedArticle(){
	var relatedOrdernumbers = "";
	var totalPrice = $('selected_articel_price').value;
	$(document.body).getElements('input[class=relatedOrdernumber]').each(function(item, index, allItems){
		var tmpOrdernumber = item.value;
		var tmpContainerName = tmpOrdernumber+'_related_container';
		var tmpPlusiconName = tmpOrdernumber+'_related_plusicon';
		var tmpPreisName = tmpOrdernumber+'_price';
		var checkbox = $(tmpOrdernumber+'_related_checkbox');

		if(true == checkbox.checked)
		{
			//Container und Pluszeichen einblenden
			$(tmpContainerName).setStyle('display', 'block');
			$(tmpPlusiconName).setStyle('display', 'block');

			//Bestellnummer hinzufügen
			if("" == relatedOrdernumbers)
				relatedOrdernumbers = tmpOrdernumber;
			else
				relatedOrdernumbers+= ";"+tmpOrdernumber;

			//Preis addieren
			var tmpPrice = $(tmpPreisName).value;
			if(tmpPrice) totalPrice = eval(totalPrice)+eval(tmpPrice);

		}else{
			//Container und Pluszeichen ausblenden
			$(tmpContainerName).setStyle('display', 'none');
			$(tmpPlusiconName).setStyle('display', 'none');
		}
	});
	$('sRelatedOrdernumbers').value = relatedOrdernumbers;
	$('price_relatedbundle').innerHTML = number_format(totalPrice, 2, ',', '.');
}

/**
 *
 * @access public
 * @return void
 **/
function changeRelatedArticleState(ordernumber, active){
	var tmpContainerName = ordernumber+'_related_container';
	if(false == active)
	{
		//Container ausblenden
		$(tmpContainerName).setStyle('display', 'none');
	}else{
		//Container einblenden
		$(tmpContainerName).setStyle('display', 'block');
	}
}

//Werte setzen
refreshRelatedArticle();

function number_format (number, decimals, dec_point, thousands_sep) {
    var n = number, prec = decimals;

    var toFixedFix = function (n,prec) {
        var k = Math.pow(10,prec);
        return (Math.round(n*k)/k).toString();
    };

    n = !isFinite(+n) ? 0 : +n;
    prec = !isFinite(+prec) ? 0 : Math.abs(prec);
    var sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
    var dec = (typeof dec_point === 'undefined') ? '.' : dec_point;

    var s = (prec > 0) ? toFixedFix(n, prec) : toFixedFix(Math.round(n), prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;

    var abs = toFixedFix(Math.abs(n), prec);
    var _, i;

    if (abs >= 1000) {
        _ = abs.split(/\D/);
        i = _[0].length % 3 || 3;

        _[0] = s.slice(0,i + (n < 0)) +
              _[0].slice(i).replace(/(\d{3})/g, sep+'$1');
        s = _.join(dec);
    } else {
        s = s.replace('.', dec);
    }

    var decPos = s.indexOf(dec);
    if (prec >= 1 && decPos !== -1 && (s.length-decPos-1) < prec) {
        s += new Array(prec-(s.length-decPos-1)).join(0)+'0';
    }
    else if (prec >= 1 && decPos === -1) {
        s += dec+new Array(prec).join(0)+'0';
    }
    return s;
}
</script>
{/literal}
{* /JAVASCRIPT *}