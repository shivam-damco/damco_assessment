/* =Modernizr feature detection tests */
Modernizr.load([
		{
			load:"http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js",
			complete: function(){
					if(!window.jQuery){
						console.clear();
						console.warn("'http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js' link not working or check your internet connection")
						Modernizr.load('js/jquery.min.js');
					};
				}
		},
		{
			load:['js/jquery.screwdefaultbuttonsV2.js', 'js/jquery.dataTables.min.js', 'js/bootstrap-select.js', 'js/bootstrap.min.js', 'js/custom.js'],
			complete:function(){
						$('.selectpicker').selectpicker();
						$('#example').dataTable();
					}
			
		},
		{
			test: Modernizr.input.placeholder,
			nope: 'js/placeholders.min.js'
		},
		{
			test: !Modernizr.mq('only all'),
			nope: 'js/respond.min.js'
		}
]);
