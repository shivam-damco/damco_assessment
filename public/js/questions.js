jQuery(document).ready(function(){
    jQuery(".question-add .custom-check").hide();
    jQuery(".question-add #max_limit").hide();
    var formName ='#question';
    var btnSave = '#submit';
    $(btnSave).click( function() {
        $(formName).trigger('submit'); 
    });
    /*
     * Show/Hide max_limit dropdown on basis of input_type
     * 
     */
    jQuery(".question-add #input_type").on('change',function(){
        var val = jQuery("#input_type option:selected").val();
        if(val == 'textarea'){
            jQuery(".custom-check").hide();
            jQuery("#max_limit").hide();
            jQuery("#res1").show();
            
        } else if(val == 'radio'){
            jQuery("#max_limit").show();
            jQuery(".custom-check").hide();
        }else{
            jQuery(".custom-check").hide();
            jQuery("#max_limit").hide();
            jQuery("#res1").hide();
        }
    });
    /*
     * Show/Hide responses dropdown on basis of max_limit
     * 
     */
    jQuery(".question-add #max_res_limit").on('change',function(){
        var val = jQuery("#max_limit option:selected").val();
        jQuery(".custom-check").hide();
        var i = 0;
        for(i = 1; i<= val; i++){
            jQuery("#res"+i).show();
        }
    });
});