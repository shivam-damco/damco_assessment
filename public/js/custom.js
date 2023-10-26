(function($, window, document, undefined){
		  
	// Custom Radio Button
	$('input:radio').screwDefaultButtons({
		image: 'url("/images/radio-input-bg.png")',
		width: 18,
		height: 18
	});
	
	// Custom Checkbox Button
	$('input:checkbox').screwDefaultButtons({
		image: 'url("../images/checkbox-input-bg.png")',
		width: 17,
		height: 17
	});
	
	//Show/Hide show by month form
	//$('.show-report input[type="radio"]').on('change', function(){
	//	($(this).attr('value') == 'by_period')
	//	? $('#optdiv').show()
	//	: $('#optdiv').hide();
	//})
	

}(jQuery, window, document));

$(function () {
    
    if( /iPhone|iPad|iPod/i.test(navigator.userAgent) ) 
    {
        
        var footer = $('#footer');
        $(document).on('focusin', 'input, textarea', function() 
        {
            footer.addClass('unfixed');
        })
        .on('focusout', 'input, textarea', function () {
            footer.removeClass('unfixed');
        });
    }
    $(window).keydown(function(event){
        if(event.keyCode == 13) {
         // event.preventDefault();
         // return false;
        }
    });
});

$( document ).ajaxComplete(function() {
  $('[data-toggle="tooltip"]').tooltip();
});