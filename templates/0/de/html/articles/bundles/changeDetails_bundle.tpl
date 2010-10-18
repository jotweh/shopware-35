/*
 +++ RELATED BUNDLELOOK - START
 */
 {if $sArticle.sRelatedArticles && $sArticle.crossbundlelook}
 {literal}

 	//Box freischalten
 	try {
		$('related_box').setStyle('display', 'block');
	}catch(e){}

	//Bestellnummer und Preis hinterlegen
 	try {
		$('related_main_ordernumber').value = ordernumber;
		$('selected_articel_price').value = $('price_'+ordernumber).value;
		refreshRelatedArticle();
	}catch(e){}

	try{
		var tmpBundleImg = $('related_main_image');
		var tmpSourceImg = $('img_1_'+ordernumber);
		if(tmpSourceImg != null)
		{
			tmpBundleImg.setHTML(tmpSourceImg.innerHTML);
			//alert(tmpSourceImg.innerHTML);
		}
	}catch(e){	}


 {/literal}
 {/if}
 {literal}
/*
 +++ RELATED BUNDLELOOK - END
 */

/*
 +++ BUNDLE - START
 */

{/literal}
{if $sArticle.sBundles}
{literal}

	//Variantenbestellnummer als aktiven Artikel
	//hinterlegen
	try {
		$(document.body).getElements('input[name=sAddBundle]').each(function(item, index, allItems){
			item.value = ordernumber;
		});
	}catch(e){}

	//BundleBox ausblenden
	try {
		$('bundle_box').setStyle('display','none');
	}catch(e){}

	{/literal}
	//Bundle Display Flag
	//Wird auf true gesetzt, wenn mindestens
	//ein Bundleartikel aktiv ist
	var showBundleBox = false;

	{foreach from=$sArticle.sBundles item=bundle}

		{if $sArticle.sVariants && $sConfig.sSHOWBUNDLEMAINARTICLE}
			//Bild des Hauptartikels bei Varianten austauschen
			{literal}
				try{
					var tmpBundleImg = $('bundleImg_{/literal}{$bundle.id}{literal}');
					var tmpSourceImg = $('img_1_'+ordernumber);
					if(tmpBundleImg != null)
					{
						if(tmpSourceImg != null)
						{
							tmpBundleImg.setHTML(tmpSourceImg.innerHTML);
						}
					}
				}catch(e){	}
			{/literal}
		{/if}

		//Preise und Rabatte
		{literal}
			//GESAMTPREIS BERECHNEN
			try{
				//Ausgabe Span
				var price_rab_span = $('price_rabAbs_{/literal}{$bundle.id}{literal}');
				//Artikelpreis
				var articlePrice = $('price_'+ordernumber).value;
				//Bundleartikelgesamtpreis
				var bundleArticlesTotalPrice = {/literal}{$bundle.sBundleArticlesTotalPrice.display}{literal}
			}catch(e){}
			if(price_rab_span!=null && articlePrice!=null && bundleArticlesTotalPrice!=null)
			{
				var rabTotal = eval(articlePrice)+eval(bundleArticlesTotalPrice);
				//var total = "x";
				price_rab_span.innerHTML = number_format(rabTotal, 2, ",", ".");
			}

			//BUNDLEPREIS ERMITTELN
			try{
				//Ausgabe Span
				var price_bundle_span = $('price_bundle_{/literal}{$bundle.id}{literal}');
			}catch(e){}
			if(price_bundle_span!=null)
			{
				{/literal}
				//Absoluter Rabatt
				{if "abs"==$bundle.rab_type}
					var bundle_price = {$bundle.sBundlePrices.display};
					price_bundle_span.innerHTML = number_format(bundle_price, 2, ",", ".");
				{else}
					//Prozentualen Rabatt berechnen
					var percentage = {$bundle.sBundlePrices.percentage};
					var tmpRabatt = eval(rabTotal)/100*eval(percentage);
					var bundle_price = eval(rabTotal)-eval(tmpRabatt);
					price_bundle_span.innerHTML = number_format(bundle_price, 2, ",", ".");
				{/if}
				{literal}

			}

			//Rabatt in Prozent
			try{
				//Ausgabe Span
				var price_rabPro_span = $('price_rabPro_{/literal}{$bundle.id}{literal}');
				var rabPro = 100-(eval(bundle_price)*100/eval(rabTotal));
			}catch(e){}
			if(price_rabPro_span!=null && rabPro!=null)
			{
				price_rabPro_span.innerHTML = number_format(rabPro,2);
			}

		{/literal}


		//Überprüfung, ob eine Artikeleinschränkung vorliegt
		{if $bundle.sBundleStints}
			//Bundle Box zunächst ausblenden
			//im weiteren Verlauf wird Sie dann für
			//berechtigte Artikelnummern wieder freigeschaltet
			{literal} try { {/literal}
				var tmpBundlesetName = 'bundleset_{$bundle.id}';
				$(tmpBundlesetName).setStyle('display','none');
			{literal} }catch(e){} {/literal}

			//Bestellnummern durchlaufen, bei denen der Bundle
			//angezeigt werden soll
			{foreach from=$bundle.sBundleStints item=stints}
				var ordernumberToUpper = ordernumber.toUpperCase();
				var stints = '{$stints}';
				{literal}
				if(ordernumberToUpper == stints)
				{
					showBundleBox=true;

					//BundleBox anzeigen
					try {
					{/literal}
						var tmpBundlesetName = 'bundleset_{$bundle.id}';
						$(tmpBundlesetName).setStyle('display','block');
					{literal}
					}catch(e){}
				}
				{/literal}
			{/foreach}
		{else}
			{literal}
			try {
				$('bundle_box').setStyle('display','block');
			}catch(e){}
			{/literal}
		{/if}
	{/foreach}

	{literal}
	//BundleBox anzeigen, wenn noch mindestens ein Bundleartikel
	//aktiv ist
	if(showBundleBox)
	{
		try {
			$('bundle_box').setStyle('display','block');
		}catch(e){}
	}

	//Lagerbestandprüfung für Variantenartikel
	{/literal}
	{if $sArticle.sVariants}
		{if 1 == $sArticle.laststock}
			//Lagerbestand der Variante ermitteln
			{literal}
				try {
					var instock = $('instock_'+ordernumber).value;
				}catch(e){}

				if(instock > 0)
				{
					if(showBundleBox)
					{
						try {
							$('bundle_box').setStyle('display','block');
						}catch(e){}
					}
				}else{
					try {
						$('bundle_box').setStyle('display','none');
					}catch(e){}
				}
			{/literal}
		{else}
			//BundleBox anzeigen, wenn noch mindestens ein Bundleartikel
			//aktiv ist
			{literal}
				if(showBundleBox)
				{
					try {
						$('bundle_box').setStyle('display','block');
					}catch(e){}
				}
			{/literal}
		{/if}
	{/if}
	{literal}

{/literal}
{/if}
/*
 +++ BUNDLE - END
 */