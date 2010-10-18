{block name='frontend_index_header_javascript_inline' prepend}

	jQuery(document).ready(function($) {
		$('.slider').ajaxSlider('ajax', {
			'url': '{url controller=recommendation action=bought article=$sArticle.articleID}',
			'title': 'Kunden kauften auch:',
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
			'url': '{url controller=recommendation action=viewed article=$sArticle.articleID}',
			'title': 'Kunden haben sich ebenfalls angesehen:',
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
{/block}


{block name="frontend_detail_index_tabs" append}


{/block}