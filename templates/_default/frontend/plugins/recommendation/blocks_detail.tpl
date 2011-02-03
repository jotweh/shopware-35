{block name='frontend_index_header_javascript' append}
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($) {
		$('.slider').ajaxSlider('ajax', {
			'url': unescape('{"{url controller=recommendation action=bought article=$sArticle.articleID}"|escape:url}'),
			'title': '{s name="DetailBoughtArticlesSlider"}Kunden kauften auch:{/s}',
			'headline': true,
			'navigation': false,
			'scrollSpeed': 800,
			'rotateSpeed': 3000,
			'rotate': false,
			'containerCSS': {
				'marginBottom': '20px'
			}
		});
		$('.slider2').ajaxSlider('ajax', {
			'url': unescape('{"{url controller=recommendation action=viewed article=$sArticle.articleID}"|escape:url}'),
			'title': '{s name="DetailViewedArticlesSlider"}Kunden haben sich ebenfalls angesehen:{/s}',
			'headline': true,
			'navigation': false,
			'scrollSpeed': 800,
			'rotateSpeed': 3000,
			'rotate': false,
			'containerCSS': {
				'marginBottom': '20px'
			}
		});
	});
	//]]>
	</script>
{/block}
{block name="frontend_detail_index_tabs" append}{/block}