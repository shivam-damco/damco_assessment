var oldval=0;
var processResponse = function(id, qid, value, assessmentid, curaction, prog_val,element_name,langid ,preview) {
    
     oldval = prog_val;
    //$('#loadingQuestion').show();
    //$('#hideshowgraybox').show();
    //question[347]
   
    //$('input[name="question['+qid+'][]"]').prop('onclick',null);
    $chkfrm = false;
    $qidval = "";
    $qidval = jQuery('#questiontext_' + qid).val();
   //alert(qid);
   
    var is_grp_last_ques = 0;
        
        var $sel_element =$('*[name="'+element_name+'"]');
        var $sel_element_type = $sel_element.attr('type');
        
        
       // console.log(element_name + "Line 6: " +$sel_element.prop('tagName') +$sel_element_type  +"| "+$sel_element);
       
       // alert(element_name + "Line 6: " +$sel_element.prop('tagName') +$sel_element_type  );
        if($sel_element.prop('tagName') == 'INPUT' && $sel_element_type == "text")
        {
            $chkfrm = chkfromelem('question_'+qid+'_1');
        }
        else if($sel_element.prop('tagName') == 'INPUT' && $sel_element_type == "radio")
        {
            $chkfrm = chkfromelem('question_'+qid+'_1');
        }
        else if($sel_element.prop('tagName') == 'TEXTAREA' || $sel_element.prop('tagName') == 'SELECT')
        {            
            $chkfrm = chkfromelem('question_'+qid+'_1');
        }
        else if(element_name == "button")
        {
            //console.log('question_'+qid+'_1');
            $chkfrm = chkfromelem('question_'+qid+'_1');
        }
        else //if($sel_element.prop('tagName') == 'INPUT' && $sel_element_type == "checkbox")
        {
           // alert("dsf");
            $chkfrm = chkfromelem('question_'+qid+'_'+value);
        }
        if(jQuery("#dynamic_error_div").length > 0)
        {
            jQuery("#dynamic_error_div").remove();
        }
   //  alert(element_name);
    //console.log("status - "+ $chkfrm);   
    
    if ($chkfrm)
    {    

		if(preview == 'true'){
			fields =  'ID=' + id + "&questionid=" + qid + "&response=" + value + "&assessment=" + assessmentid + "&questiontext=" + $qidval + "&langid=" + langid + "&preview=" + preview;

		}
		else{ 
			preview = 'false';
			fields =  'ID=' + id + "&questionid=" + qid + "&response=" + value + "&assessment=" + assessmentid + "&questiontext=" + $qidval + "&langid=" + langid + "&preview=" + preview;
		}
      
        //console.log("I m here in if " + lang_charset);
        $.ajax({
            type: "POST",
            dataType: 'json',           
            url: BASE_URL + "/assessment/index/checkgrouplastquestion/",          
            data: fields,
          
            success: function(result) {
               
                var is_grp_last_ques = result.group_last_ques;
               // var is_participate_in_branching = result.is_grpshow;
                var progbar_stats= result.anscnt;               
				
                prog_val = result.anscnt;
                //progress_bar(result.anscnt);
				
				
				
                if(jQuery("#btnProceed").length > 0)
                {
                    jQuery("#btnProceed").remove();
                }
                if(jQuery("#btnSubmitassessment").length > 0)
                {
                    jQuery("#btnSubmitassessment").remove();
                    jQuery('#thankyoudiv').hide();
                }
                
                 show_next_ques_response(id, qid, value, assessmentid, $qidval, prog_val,id,langid,preview);
                 
                //6/30/14 1:13 PM 
              /*  if(result.is_grpshow == "showgrpnxt" )
                {
                    if(jQuery("#btnProceed").length > 0)
                    {
                        jQuery("#btnProceed").remove();
                    }
                    if(jQuery("#btnSubmitassessment").length > 0)
                    {
                        jQuery("#btnSubmitassessment").remove();
                        jQuery('#thankyoudiv').hide();
                    }
                  
                }  */
                //
                var parent_grp_dtls = result.parent_grp_id;
                // console.log(prog_val+"hello" + result.is_grpshow);
                /* if (is_grp_last_ques > 0)
                {

                   // $('#btnProceed').removeAttr('disabled');
                    jQuery('#grpval').val(result.next_group_id);

                    if (result.is_grpshow == "showgrpnxt")
                    {
                        //console.log("echo hi");
                        show_next_ques_response(id, qid, value, assessmentid, $qidval, prog_val,parent_grp_dtls,langid);
                    }
                    else if (result.is_grpshow == "shownxt")
                    {
                        notshow_next_ques_response(id, qid, value, assessmentid, $qidval, prog_val,langid);
                    }
                    else if (result.is_grpshow == "shownothing")
                    {
                        saveques_response(id, qid, value, assessmentid, $qidval, prog_val,langid);
                    }
                }
                else
                {
                    //console.log("hi");
                    if (result.is_grpshow == "showgrpnxt")
                    {
                        show_next_ques_response(id, qid, value, assessmentid, $qidval, prog_val,parent_grp_dtls,langid);
                    }
                    else if (result.is_grpshow == "shownxt")
                    {
                        notshow_next_ques_response(id, qid, value, assessmentid, $qidval, prog_val,langid);
                    }
                    else if (result.is_grpshow == "shownothing")
                    {
                        saveques_response(id, qid, value, assessmentid, $qidval, prog_val,langid);
                    }
                    //show_next_ques_response(id,qid,value,assessmentid,$qidval,prog_val)
                }*/
                
                //progress_bar(prog_val);
				
            },
            error: function()
            { 
                if(jQuery("#dynamic_error_div").length == 0)
                {
                    jQuery("#ID_pbar").addClass("panel-danger");
                    jQuery("#ID_pbar").prepend("<div id=\"dynamic_error_div\" class=\"panel-heading\">"+assessment_error_text+"</div>");
                }
               
            }

        });
    }
    else{
        //alert('m in else');
        if($sel_element.prop('tagName') == 'INPUT' && $sel_element_type == "text")
        {//alert('dipa1'+'question_'+qid+'_1');
            jQuery('#question_'+qid+'_1').text("");
        }
        else if($sel_element.prop('tagName') == 'TEXTAREA' )
        {//alert('dipa2'+'question_'+qid+'_1');
           jQuery('#question_'+qid+'_1').text("");            
        }
        else
        {//alert('dipa3=>question_'+qid+'_'+value);
            jQuery('#question_'+qid+'_'+value).attr('checked', false);
            
        } 
        
        deactivateassessmentSubmission();
    }
    //console.log(is_grp_last_ques);
  //  $showproceedbtn = jQuery('#showproceedbtn').val();
    //console.log("outside fun: "+$showproceedbtn);

};/**/
var deactivateassessmentSubmission = function() {
   // console.log("to disable");
    jQuery('#thankyoudiv').hide();
    jQuery('#btnSubmitassessment').attr('disabled', true);

};

var activateassessmentSubmission = function(postAction) {
   // console.log("here" + postAction + "sdf");
    jQuery('#task').val(postAction);
    jQuery('#thankyoudiv').addClass("alert alert-success");
    jQuery('#thankyoudiv').show();
    $("#prog_bar").css("width", "100%");
    $("#prog_bar").html("100%");
    //create button
    if(jQuery("#btnSubmitassessment").length > 0)
    {
        jQuery("#btnSubmitassessment").remove();
    }
    jQuery(".panel-footer div ul li").append("<button type='button' id='btnSubmitassessment' class='btn btn-primary' disabled='disabled'>"+bttntext+"</button>"); 
    
    jQuery('#btnSubmitassessment').removeAttr('disabled');


};


var progress_bar = function(progress_val) {

    var pw =(progress_val/multiplier)*100;//(progress_val * multiplier); 7/1/14 10:44 PM
   // var pw = (progress_val * 1);
    var prog_width = Math.floor(pw) + '%';

    $("#prog_bar").css("width", prog_width);
    $("#prog_bar").html(prog_width)
    if(pw > 100 )
    {
        $("#prog_bar").css("width", "100%");
        $("#prog_bar").html("100%")
    }
    ;
    
};

jQuery(function() {
   
    jQuery('body').on('click', 'button#btnSubmitassessment',function() {
        //console.log('test call');
        jQuery('#assessmentfrm').submit();
    });
    
    //to proceed
    jQuery('body').on('click', 'button#btnProceed',function(e) {
        e.preventDefault();
        //

        var sel_element = $("#btnProceed").attr('ctrl_lastElementName');
        var myelem = $('*[name="'+sel_element+'"]');

        var sel_element_type = myelem.attr('type');
       // alert(myelem.attr('name') +"|" + myelem.prop('tagName')+"|"+sel_element_type+"|"+myelem.attr('id'));
        //console.log(element_name + "Line 6: " +$sel_element.prop('tagName') +$sel_element_type  );
        if(myelem.prop('tagName') == 'INPUT' && sel_element_type == "text")
        {
            //alert(3 +"   "+ myelem.attr('id'));
            var textid = myelem.attr('id');
            jQuery("#"+textid).trigger('blur');
            //$chkfrm = chkfromelem('question_'+qid+'_1');
        }
        else if(myelem.prop('tagName') == 'TEXTAREA')
        {
           // alert(2 +"   "+ myelem.attr('id'));
            var textareaid = myelem.attr('id');
            //alert(textareaid);
            jQuery("#"+textareaid).focus().trigger('blur');
          //  $chkfrm = chkfromelem('question_'+qid+'_1');
        }
        else if(myelem.prop('tagName') == 'INPUT' && sel_element_type == "checkbox")
        {
            //get ok button's  onclick param
            var curElem = $('input[name="'+sel_element+'"]:last');
            var okbttnnm = curElem.parent().siblings('button').attr("onclick"); //  myelem.parent().siblings('button').prop('tagName');
           //alert(okbttnnm);
            eval( okbttnnm );
        }
        else
        { 
            //alert(3+"| "+myelem.prop('tagName'));
            eval( myelem.attr('onclick') );
            
          //  $chkfrm = chkfromelem('question_'+qid+'_'+value);
        }
        return false;
   
    });
    
    //intro page submit
    jQuery('#introsubmit').click(function() {
        
        var qryString = jQuery('#assessment').val();
        //(typeof neverDeclared == "undefined")
        if(typeof jQuery('#langid') !== undefined )
        {
            qryString += "&langid="+ jQuery('#langid').val();
        }
        jQuery("#frm-intro").attr("action", "/public/assessment/index/start/?assessment=" + qryString);
        jQuery("#frm-intro").submit();
        //jQuery('#assessmentfrm').submit();
    });
    //$(function() {
       // $(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
      // console.log("fxgfd");
       jQuery(document).ajaxStart(function(){
           //jQuery.blockUI.defaults.overlayCSS = { };
           // jQuery.blockUI({ message: jQuery('#domMessage') })          
           $.blockUI({ message: '<h1>Please Wait...</h1>'});
       })
        $(document).ajaxStop($.unblockUI);
       // .ajaxStop();/**/
   // });
});

function saveques_response(id, qid, value, assessmentid, mqidval, prog_val,langid)
{
    $.ajax({
         
        type: "POST",
        url: BASE_URL + "/assessment/index/processResponse/",
        cache: false,
        data: 'ID=' + id + "&questionid=" + qid + "&response=" + value + "&assessment=" + assessmentid + "&questiontext=" + mqidval + "&shownxtquestion=shownothing&langid=" + langid,
        /*beforeSend: function(xhr) {
            xhr.setRequestHeader("Accept-Charset", lang_charset);
            xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset="+lang_charset);
            },*/
        success: function(result) {
            jQuery("#ID_" + id + "_" + qid).after(result);
//            console.log("my val: "+prog_val);
           // progress_bar(prog_val); 7/1/14 10:52 PM
        },
        error: function()
        {
            if(jQuery("#dynamic_error_div").length == 0)
            {
                jQuery("#ID_pbar").addClass("panel-danger");
                jQuery("#ID_pbar").prepend("<div id=\"dynamic_error_div\" class=\"panel-heading\">"+assessment_error_text+"</div>");
            }

        }
        

    });
}
function notshow_next_ques_response(id, qid, value, assessmentid, mqidval, prog_val,langid)
{
    $.ajax({
        
        type: "POST",
        url: BASE_URL + "/assessment/index/processResponse/",
         cache: false,
        data: 'ID=' + id + "&questionid=" + qid + "&response=" + value + "&assessment=" + assessmentid + "&questiontext=" + mqidval + "&shownxtquestion=n&langid=" + langid,
        /*beforeSend: function(xhr) {
            xhr.setRequestHeader("Accept-Charset", lang_charset);
            xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=iso-8859-9");
            },*/
        success: function(result) {
            jQuery("#ID_" + id + "_" + qid).after(result);
//            console.log("my val: "+prog_val);
           // progress_bar(prog_val); 7/1/14 10:52 PM
        },
        error: function()
        {
            if(jQuery("#dynamic_error_div").length == 0)
            {
                jQuery("#ID_pbar").addClass("panel-danger");
                jQuery("#ID_pbar").prepend("<div id=\"dynamic_error_div\" class=\"panel-heading\">"+assessment_error_text+"</div>");
            }

        }
       

    });
}
function show_next_ques_response(id, qid, value, assessmentid, mqidval, prog_val, parent_grp_dtls,langid ,preview)
{ 
	if(preview == 'true'){
			fields =  'ID=' + id + "&questionid=" + qid + "&response=" + value + "&assessment=" + assessmentid + "&questiontext=" + mqidval + "&shownxtquestion=y&langid=" + langid + "&preview=" + preview;
		}
		else{
			preview = 'false';
			fields =  'ID=' + id + "&questionid=" + qid + "&response=" + value + "&assessment=" + assessmentid + "&questiontext=" + mqidval + "&shownxtquestion=y&langid=" + langid + "&preview=" + preview;
		}


    $.ajax({
      
        type: "POST",
        url: BASE_URL + "/assessment/index/processResponse/",
        cache: false,
        data: fields,
        success: function(result) {
           // console.log(result);
           // console.log("#ID_" + id + "_" + qid);
            if (result == 'close' || result == 'did not qualify') {
                // console.log(result);
                activateassessmentSubmission(result);
				if ( preview == 'true' ) {
					jQuery("#btnSubmitassessment").remove();
				}				
            }
            else {
                //console.log("#ID_" + id + "_" + qid);
               // jQuery("#ID_" + parent_grp_dtls.ID + "_" + parent_grp_dtls.qid).after(result);
                if(result=='Error-Access'){
                   //progress_bar(oldval);
                   $("#question_"+qid+"_"+value).attr("checked",false).attr("disabled",true).next('label').css("color","#a94442");
                   var optionValue = $.trim($("#question_"+qid+"_"+value).next('label').text());
                   jQuery("#ID_" + id + "_" + qid).addClass("panel-danger");
                   jQuery("#ID_pbar").addClass("panel-danger");
                    jQuery("#ID_" + id + "_" + qid).after("<div id=\"dynamic_error_div\" class=\"panel-heading option-error\">Nomination for this option ("+optionValue+") is filled. Please choose another option</div>");
                }else{
                   jQuery("#ID_" + id + "_" + qid).after(result);
                }
                
                //            console.log("my val: "+prog_val);
                //progress_bar(prog_val); 7/1/14 10:52 PM
            }
        },
        beforeSend: function() {         
             jQuery("#ID_" + id + "_" + qid).nextAll().each(function() {
                var id = $(this).attr('id');
                if (id != 'ID_pbar') {
                    $(this).remove();
                }

            }); 
        },
        error: function()
        {
            if(jQuery("#dynamic_error_div").length == 0)
            {
                jQuery("#ID_pbar").addClass("panel-danger");
                jQuery("#ID_pbar").prepend("<div id=\"dynamic_error_div\" class=\"panel-heading\">"+assessment_error_text+"</div>");
            }

        }

    });
		
}

function chkfromelem(elem_id)
{
    var elem_list = Array();
    var elem_name = '';
    var elem_type = '';
    var textbox_elem = '';
    var clicked_elem_name = $("#" + elem_id).attr('name');
    var elem_cnt = 0;
    var lcnt = 0;
    if (jQuery('#dynamic_alert_div').length) {
        jQuery('#dynamic_alert_div').remove();
        jQuery("#ID_pbar").removeClass("panel-danger");
    }
    jQuery('input:radio, input:checkbox, textarea, select, input:text').each(function() {
        elem_name = $(this).attr('name');
        elem_type = $(this).attr('type');
        //console.log(elem_name + "Line No: 207: "+elem_id +" | "+ elem_type);
        
        if (jQuery.inArray(elem_name, elem_list) == '-1') {
            elem_list.push(elem_name);
            elem_cnt = elem_list.length;
            //console.log("txtarestat:" + $(this).is('textarea'));
            if ($(this).is('textarea'))
            {
                textarea_elem = jQuery(this);
                
                    // console.log(textarea_elem.val());
                    if ((textarea_elem.attr("data-ctrl_needchk") != '' ) && (textarea_elem.val() == '' || typeof textarea_elem.val() == 'undefined')) {

                        var bb = jQuery(this).closest('div.panel-body').prev().find('.question_num').text();

                        jQuery(this).closest('div.panel-default').addClass("panel-danger");

                        lcnt--;
                    }
                    else
                    {
                        //elem_cnt++;
                        jQuery(this).closest('div.panel-default').removeClass("panel-danger");
                    }
                    lcnt++;
                    //console.log(clicked_elem_name+' == '+elem_name);
                    if (clicked_elem_name == elem_name)
                    {
                        //return assessmentreturn_status(lcnt , elem_cnt);
                        return false;
                    }
                /*}
                else
                {
                    return true;
                }*/
            }
            else if($(this).is('input:text'))
            {
                textbox_elem = jQuery(this);
               
                
                    if ((textbox_elem.attr("data-ctrl_needchk") != '' ) && (textbox_elem.val() == '' || typeof textbox_elem.val() == 'undefined')) {

                        var bb = jQuery(this).closest('div.panel-body').prev().find('.question_num').text();

                        jQuery(this).closest('div.panel-default').addClass("panel-danger");

                        lcnt--;
                    }
                    else
                    {
                        //elem_cnt++;
                        jQuery(this).closest('div.panel-default').removeClass("panel-danger");
                    }
                    lcnt++;
                    //console.log(clicked_elem_name+' == '+elem_name);
                    if (clicked_elem_name == elem_name)
                    {
                        //return assessmentreturn_status(lcnt , elem_cnt);
                        return false;
                    }
                
            }
            else if ($(this).is('select'))
            {
                selectbox_elem = jQuery(this);
                // console.log(selectbox_elem.val());
                if (selectbox_elem.val() == '' || selectbox_elem.val() == '0' || typeof selectbox_elem.val() == 'undefined') {

                    var bb = jQuery(this).closest('div.panel-body').prev().find('.question_num').text();

                    jQuery(this).closest('div.panel-default').addClass("panel-danger");

                    lcnt--;
                }
                else
                {
                    
                    jQuery(this).closest('div.panel-default').removeClass("panel-danger");
                }
                lcnt++;
                //console.log(clicked_elem_name+' == '+elem_name);
                if (clicked_elem_name == elem_name)
                {
                    //return assessmentreturn_status(lcnt , elem_cnt);
                    return false;
                }
            }
            else
            {
                radio_buttons = jQuery("input[name='" + elem_name + "']");
                if (radio_buttons.filter(':checked').length == 0) {

                    var bb = jQuery(this).closest('div.panel-body').prev().find('.question_num').text();

                    jQuery(this).closest('div.panel-default').addClass("panel-danger");
                    

                    lcnt--;
                }
                else
                {
                    //elem_cnt++;
                    jQuery(this).closest('div.panel-default').removeClass("panel-danger");
                }
                lcnt++;

                if (clicked_elem_name == elem_name)
                {
                    //return assessmentreturn_status(lcnt , elem_cnt);
                    return false;
                }

            }
        }
    });
    jQuery('.dateValidation').each(function() {
        elem_name = $(this).attr('name');
        elem_type = $(this).attr('type');
        //console.log(elem_name + "Line No: 207: "+elem_id +" | "+ elem_type);
        
        if (jQuery.inArray(elem_name, elem_list) == '-1') {
            elem_list.push(elem_name);
            elem_cnt = elem_list.length;
        
            textbox_elem = jQuery(this);
            if ((textbox_elem.attr("data-ctrl_needchk") != '' ) && (textbox_elem.val() == '' || typeof textbox_elem.val() == 'undefined')) {

                var bb = jQuery(this).closest('div.panel-body').prev().find('.question_num').text();

                jQuery(this).closest('div.panel-default').addClass("panel-danger");

                lcnt--;
            }
            else
            {
                //elem_cnt++;
                jQuery(this).closest('div.panel-default').removeClass("panel-danger");
            }
            lcnt++;
            //console.log(clicked_elem_name+' == '+elem_name);
            if (clicked_elem_name == elem_name)
            {
                //return assessmentreturn_status(lcnt , elem_cnt);
                return false;
            }
            
        }
    });
   //alert(lcnt +"|"+ elem_cnt+"| "+elem_type);
    return assessmentreturn_status(lcnt, elem_cnt);

}

function assessmentreturn_status(lcnt, elem_cnt)
{
    if (lcnt < elem_cnt)
    {	
		assessment_notselected_text = 'Please fill the assessment';
        jQuery("#ID_pbar").addClass("panel-danger");
        jQuery("#ID_pbar").prepend("<div id=\"dynamic_alert_div\" class=\"panel-heading\">"+assessment_notselected_text+"</div>");
        return false;
    }
    else
    {
        return true;
    }
}

function htmlEncode(value){
    if (value) {
        return jQuery('<div />').text(value).html();
    } else {
        return '';
    }
}
function htmlDecode(value) {
    if (value) {
        return $('<div />').html(value).text();
    } else {
        return '';
    }
}



