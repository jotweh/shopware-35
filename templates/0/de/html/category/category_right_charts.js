var Site = {

	start: function(){
		
		if ($('topseller')) Site.parseKwicks();
	},
	
	parseKwicks: function(){
		var f = 50;
		var d = 90;
		var kwicks = $$('#topseller .toprule');
		var kwicks2 = $$('#topseller .topruleimg');
		var fx = new Fx.Elements(kwicks, {wait: false, duration: 500, transition: Fx.Transitions.quadOut});
		var fx2 = new Fx.Elements(kwicks2, {wait: false, duration: 500, transition: Fx.Transitions.quadOut});
		kwicks.each(function(kwick, i){
			kwick.addEvent('mouseenter', function(e){
				e = new Event(e);
				var obj = {};
				var obj2 = {};
				obj[i] = {
						'top': [kwick.getStyle('top').toInt(), i*f+39]
				};
				obj2[i] = {
						'top': [kwick.getFirst().getStyle('top').toInt(), 10]
				};
				kwick.removeClass('out').addClass('over');
				kwicks.each(function(other, j){
					if (other != kwick){
						var w = other.getStyle('top').toInt();
						if (w != j*f-d){
							if (i > j){
								obj[j] = {
									'top': [w, j*f-d+39]
								};
							}
							else
							{
								obj[j] = {
									'top': [w, j*f+39]
								};
							}
							obj2[j] = {
								'top': [other.getFirst().getStyle('top').toInt(), 100]
							};
							other.removeClass('over').addClass('out');
						}
					}
				});
				fx.start(obj);
				fx2.start(obj2);
				e.stop();
			});
		});
		
		$('topseller').addEvent('mouseleave', function(e){
			var obj = {};
			var obj2 = {};
			kwicks.each(function(other, j){
				if (j == 0){
					obj[j] = {
						'top': [other.getStyle('top').toInt(), 39]
					};
					obj2[j] = {
						'top': [other.getFirst().getStyle('top').toInt(), 10]
					};
					other.removeClass('out').addClass('over');
				}
				else
				{
					obj[j] = {
						'top': [other.getStyle('top').toInt(), j*f+39]
					};
					obj2[j] = {
						'top': [other.getFirst().getStyle('top').toInt(), 100]
					};
					other.removeClass('over').addClass('out');
				}
			});
			fx.start(obj);//42//101//59
			fx2.start(obj2);
		});
	}	
};
//window.addEvent('load', Site.start);
Site.start();