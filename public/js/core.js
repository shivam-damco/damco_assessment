/**
 * Javascript file for all core operations for Triumph
 * 
 * @author Harpreet Singh
 * @date   29 May, 2014
 * @version 1.0
 */
function goBack() {
    window.history.back();
}
function validateFormQuestion(){
    var submit_flag = 0;
    if(jQuery("#input_type").val() == ''){
        alert("Please select question type");
        submit_flag = 1;
    }
    if(jQuery("#max_limit").css('display') != 'none'){
        if(jQuery("#max_res_limit").val() == ''){
            alert("Please select number of responses for the question");
            submit_flag = 1;
        }
    }
    jQuery(".custom-check").each(function(i){
        i+=1;
        if(jQuery("#res"+i).css('display') != 'none'){
            if(jQuery("#response"+i).val() == ''){
                alert('Please fill response '+i);
                submit_flag = 1;
            }
        } 
    });
    if(submit_flag){
        return false;
    } else{
        return true;
    }
}   
$(function(){
    $('input:radio[name="period"]').on('click', function(){
        var val = $('input[name="period"]:checked').val();
        switch ( val ) {
            case 'YTD':
                $('#optdiv, .by_month_selection').hide();
                break;
            case 'by_month':
                $('#optdiv').hide();
                $('.by_month_selection').show();
                fillYears('by_month');
                break;
            case 'by_period':
                $('#optdiv').hide();
                $('.by_month_selection').hide();
                fillYears('by_period');
                break;
            default:
                $('#optdiv, .by_month_selection').hide();
                break;
        }
    });
    
    if ( $('input[name="period"]:checked').val() != undefined ) {
        $('input:radio[value="'+($('input[name="period"]:checked').val())+'"]').trigger('click');
    }
    else {
        $('#optdiv, .by_month_selection').hide();
    }
    //Added By dipa for user activityreport 11/4/14 12:56 PM
    $('select#user_role').on('change', function(){
    // var selRole = $('select#user_role').val();
    var selRole = ($('select#user_role').val().length != 0) ? $('select#user_role').val() : 0;

    //alert(selRole);
    if(selRole == 0)
    {
        $('select#branchlist').prop("disabled", false).removeClass('form-control');
        $('select#dealerlist').prop("disabled", false).removeClass('form-control');
    }
    else
    {
        if(selRole < 4)
        {
             if(selRole < 2) //for corporate disable all opt
             {
                 //$('select#branchlist').hide();
                 $('select#branchlist').prop("disabled",true).prop('selectedIndex',0).addClass('form-control');
                 fillDealerList( );
                 $('select#dealerlist').prop("disabled",true).prop('selectedIndex',0).addClass('form-control');
             }
             else //for branch & ASM disable dealer only
             {
                $('select#dealerlist').prop("disabled",true).prop('selectedIndex',0).addClass('form-control');
                $('select#branchlist').prop("disabled", false).removeClass('form-control');
             }

        }
        else
        {
          // $('select#branchlist').show();
           $('select#branchlist').prop("disabled", false).removeClass('form-control');
           $('select#dealerlist').prop("disabled", false).removeClass('form-control');
        }
    }
    });
    //Added By dipa for user activityreport 11/4/14 12:56 PM
    /*if ( $('input[name="period"]:checked').val() == undefined ) {
        $('input:radio[value="YTD"]').trigger('click');
    }
    $('input:radio[value="'+($('input[name="period"]:checked').val())+'"]').trigger('click'); */
    
    $('select#branch').on('change', function(){
        fillMarkets( );
    });
    
    $('select#branchlist').on('change', function(){
    	fillDealerList( );
    });
    //Added by Dipa for Question analysis report 9/30/14 5:36 PM
    /* $('select#EventTypeID').on('change', function(){
        fillQuestions( );
    }); 1/4/16 12:40 PM commented by sachin and added below code */ 
    //Added by sachin for list assessment in question analysis 
    $('select#assessmentID').on('change', function(){
        fillQuestions();
        //displayInGrid(); 
    });
    
    $('select#assessmentCategoryID').on('change', function(){
        fillEventType();
        fillassessment();
        fillQuestions();
    });
    
    $('select#EventTypeID').on('change', function(){
        fillassessment();
        fillQuestions();
    });
    //EOC Added by sachin for list assessment in question analysis
    
    $('select#market').on('change', function(){
//        fillASMs( );
        fillSalesRegions( );
    });
    
    $('select#asm').on('change', function(){
        fillDealers( );
    });
    
    $('select#sales_region').on('change', function(){
        fillDealers( );
    });
    
    $('#df_submit').click(function(e){
        e.preventDefault();
        $('.alert').remove();
        validateForm( );
    });
    
    $('#date_range_field').on('change', function(){
        if ( $('input[name="period"]:checked').val() != undefined ) {
            $('input:radio[value="'+($('input[name="period"]:checked').val())+'"]').trigger('click');
        }
    });
});

function fillYears(caseValue) {
    var dateRangeField = ( $('#date_range_field').get(0) != undefined )
                      ? $('#date_range_field').val() :'assessment_submission_date';
    $.ajax({
        url : BASE_URL+'/assessment/ajax/fillyears',
        type: 'POST',
        dataType: 'JSON',
        data: 'date_range_field='+dateRangeField,
        success: function (data) {
            if (data['error_code'] == '0') {
                if (caseValue == 'by_period') {
                    $('#optdiv').show();
                    var range = data.range.from + ':' + data.range.to;
                    $('#fromPeriod, #toPeriod').datepicker({
                        dateFormat: "dd/mm/yy",
                        yearRange: range,
                        maxDate: 0,
                    });
                }
                $('#year').html(data.years_dd);
                if ($('input[name="sel_year"]').val() != '') {
                    $('#year').val($('input[name="sel_year"]').val());
                }
                $('.select-preloader').remove();
           }
           else {
               alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
        $('.select-preloader').remove();
            if (caseValue == 'by_period') {
                $('#optdiv').before('<div style="left:50px;top:55px" class="select-preloader">Loading...</div>');
            } else {
                $('#from_year, #to_year, #year')
                    .after('<div class="select-preloader">Loading...</div>');
            }
       }
    });
}

function fillMarkets( ) {
    $.ajax({
       url : BASE_URL+'/assessment/ajax/fillmarkets',
       type: 'POST',
       dataType: 'JSON',
       data: 'branch='+$('select#branch').val(),
       success: function (data) {
           if ( data['error_code'] == '0' ) {
               $('select#market').html(data.markets_dd);
               $('select#sales_region').html(data.sales_region_dd);
               $('select#dealer').html(data.dealers_dd);
               
               $('.select-preloader').remove();
           }
           else {
               alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#market, select#sales_region, select#dealer')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
}
function fillDealerList( ) {
    $.ajax({
       url : BASE_URL+'/assessment/ajax/fillmarkets',
       type: 'POST',
       dataType: 'JSON',
       data: 'branch='+$('select#branchlist').val(),
       success: function (data) {
           if ( data['error_code'] == '0' ) {
               $('select#dealerlist').html(data.dealers_dd);
               
               $('.select-preloader').remove();
           }
           else {
               alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#dealerlist')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
}
function fillQuestions( ) {
    $.ajax({
       url : BASE_URL+'/assessment/ajax/fillquestions',
       type: 'POST',
       dataType: 'JSON',
       data: 'event_typeid='+$('select#EventTypeID').val(),
       success: function (data) {
           if ( data['error_code'] == '0' ) {
               $('select#questionid').html(data.question_dd);            
               $('.select-preloader').remove();
           }
           else {
               alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#questionid')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
}
function fillassessment( ) {
    $.ajax({
       url : BASE_URL+'/assessment/ajax/fillassessment',
       type: 'POST',
       dataType: 'JSON',
       async: false,
       data: 'event_typeid='+$('select#EventTypeID').val(),
       success: function (data) {
           $('select#assessmentID').html('');       
           if ( data['error_code'] == '0' ) {
               $('select#assessmentID').html(data.assessment_dd);            
               $('.select-preloader').remove();
           }
           else {
                $('select#assessmentID').html(data.assessment_dd);  
                $('.select-preloader').remove();
               //alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#assessmentID')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
    
   // displayInGrid();
}

function fillASMs( ) {
    var branch = ($('select#branch').get(0) != undefined)
                 ?$('select#branch').val():'';
    $.ajax({
       url : BASE_URL+'/assessment/ajax/fillasms',
       type: 'POST',
       dataType: 'JSON',
       data: 'market='+$('select#market').val()+'&branch='+branch,
       success: function (data) {
           if ( data['error_code'] == '0' ) {
               $('select#asm').html(data.asm_dd);
               $('select#dealer').html(data.dealers_dd);
               $('.select-preloader').remove();
           }
           else {
               alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#asm, select#dealer')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
}

function fillSalesRegions( ) {
    var branch = ($('select#branch').get(0) != undefined)
                 ?$('select#branch').val():'';
    $.ajax({
       url : BASE_URL+'/assessment/ajax/fillsalesregions',
       type: 'POST',
       dataType: 'JSON',
       data: 'market='+$('select#market').val()+'&branch='+branch,
       success: function (data) {
           if ( data['error_code'] == '0' ) {
               $('select#sales_region').html(data.sales_regions_dd);
               $('select#dealer').html(data.dealers_dd);
               $('.select-preloader').remove();
           }
           else {
               alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#sales_region, select#dealer')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
}

function fillDealers( ) {
    var branch = ($('select#branch').get(0) != undefined)
                 ?$('select#branch').val():'';
    var market = ($('select#market').get(0) != undefined)
                 ?$('select#market').val():'';
    $.ajax({
       url : BASE_URL+'/assessment/ajax/filldealers',
       type: 'POST',
       dataType: 'JSON',
       data: 'sales_region='+$('select#sales_region').val()+'&branch='+branch+'&market='+market,
       success: function (data) {
           if ( data['error_code'] == '0' ) {
               $('select#dealer').html(data.dealers_dd);
               $('.select-preloader').remove();
           }
           else {
               alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#dealer')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
}

function validateForm( ) {
    var errors = new Array();
    if ( $('input[name="period"]:checked').val() != undefined
         && $('#date_range_field').get(0) != undefined ) {
        if ( $('#date_range_field').val() == '' ) {
            errors.push(messages['select_date_range_field']);
        } 
    }
    
    if ( $('input[name="period"]:checked').val() != undefined
         && $('input[name="period"]:checked').val() == 'by_period' ) {
        var fromDateObj = $( "#fromPeriod" ).datepicker( "getDate");
        var toDateObj = $( "#toPeriod" ).datepicker( "getDate" );
        if (fromDateObj === null) {
            errors.push(messages["from_date_can_not_be_empty"]);
        } else if(toDateObj === null) {
            errors.push(messages["to_date_can_not_be_empty"]);
        } else if( fromDateObj > toDateObj) {
            errors.push(messages['from_date_greater_than_to_date']);
        } 
    }
    //Added by Dipa 8/21/14 1:05 PM
    if(($('#frmdashboard').attr("action") == BASE_URL +'/report/dashboard/'
        || $('#frmesr').attr("action") == BASE_URL +'/report/esr/'
        || $('#frmqa').attr("action") == BASE_URL +'/report/questionanalysis/'
        ) && $('input[name="period"]:checked').val() != undefined
         && $('input[name="period"]:checked').val() == 'by_period')
    {
        var fromDateObj = $( "#fromPeriod" ).datepicker( "getDate");
        var selectedFromDate = $( "#fromPeriod" ).datepicker( "getDate");
        var toDateObj = $( "#toPeriod" ).datepicker( "getDate" );
        if (selectedFromDate != null && selectedFromDate != '') {
            selectedFromDate.setFullYear(selectedFromDate.getFullYear()+1);
            var todayDate = new Date();
            if (fromDateObj > todayDate || toDateObj > todayDate) {
                errors.push(messages['no_future_month']);
            } else if(toDateObj >= selectedFromDate) {
                errors.push(messages['period_diff_12_months']);
            }
        }
    }
   
    //Added by Dipa
    if ( $("#report").val() == 'mdr') {
            var currentMonth = (new Date).getMonth()+1<9?'0'+(new Date).getMonth()+1:
            (new Date).getMonth()+1;
            var currentYear = (new Date).getFullYear();
            var user_role=$( "#user_role" ).val();
            var dealer_id=$( "#dealer option:selected" ).val();
            var branch=$( "#branch option:selected" ).val();
            var user_role=$( "#user_role" ).val(); 
            if($("#EventTypeID").val()=='0'){
                errors.push(messages['select_event_type']);
                $("#EventTypeID").focus();
                
            }
            else if(user_role=='corporate' && branch==''){
                errors.push(messages['select_branch']);
                $("#branch").focus();
                
            }else if(user_role=='corporate' && dealer_id==''){
                errors.push(messages['select_dealer']);
                $("#dealer").focus();
                
            }else if(user_role=='branch' && dealer_id==''){
                errors.push(messages['select_dealer']);
                $("#dealer").focus();
                
            }
            else if(user_role=='asm' && dealer_id==''){
                 errors.push(messages['select_dealer']);
                $("#dealer").focus();
                
            }
            else if(($("#month" ).val()==currentMonth && currentYear==$("#year" ).val())
                 || ($("#month" ).val()>currentMonth && currentYear==$("#year" ).val())){
                 errors.push(messages['select_month_greater_equal_current']);
                $("#month").focus();
                
            }
    }
    
    if ( errors.length > 0 ) {
        $('#filt-opts').append('<div class="alert alert-danger margintop20">'+
                errors.join('<br />')+'</div>');
        
    }
    else {
        $('#df_submit').parents().find('form').get(0).submit(true);
    }
}

function changeLanguage( lang_id ) {
    jQuery("#frm-intro").submit();
    var url = window.location.href;
   // window.location.href = url.replace( /(&langid=[\w\d\s]+)/g, "" ) + '&langid=' + lang_id;
    var existingurl =  url.replace( /(&langid=[\w\d\s]+)/g, "" );
    var newurl =  addParameter(existingurl, "langid", lang_id);
    window.location.href = newurl;
}


function addParameter(url, param, value) {
    // Using a positive lookahead (?=\=) to find the
    // given parameter, preceded by a ? or &, and followed
    // by a = with a value after than (using a non-greedy selector)
    // and then followed by a & or the end of the string
    var val = new RegExp('(\\?|\\&)' + param + '=.*?(?=(&|$))'),
        qstring = /\?.+$/;
    
    // Check if the parameter exists
    if (val.test(url))
    {
        // if it does, replace it, using the captured group
        // to determine & or ? at the beginning
        return url.replace(val, '$1' + param + '=' + value);
    }
    else if (qstring.test(url))
    {
        // otherwise, if there is a query string at all
        // add the param to the end of it
        return url + '&' + param + '=' + value;
    }
    else
    {
        // if there's no query string, add one
        return url + '?' + param + '=' + value;
    }
}

/**
 * Extended DataTable Plugin for self use.
 * 
 * Add options in options.
 */
(function($) {
    var dataTableGrid = $.fn.dataTable;
    $.fn.dataTable = function(options) {
        if(typeof options === "object") {
            options = $.extend(true, options, {
                language: { "url": LANGUAGE_FILE },
                "fnDrawCallback": function( oSettings ) {
                    $('.btn-export').show();
                    //dipa 11/5/14 2:29 PM
                    var rows = this.fnGetData();
                    if ( rows.length === 0 ) {
                       $('.btn-export').hide();
                    }
                  //dipa
                },
                "pagingType" : PAGINATION
            });
        }
        var args = Array.prototype.slice.call(arguments,0);
        return dataTableGrid.apply(this, args);
    };
})(jQuery);

$.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
    //redraw to account for filtering and sorting
    // concept here is that (for client side) there is a row got inserted at the end (for an add)
    // or when a record was modified it could be in the middle of the table
    // that is probably not supposed to be there - due to filtering / sorting
    // so we need to re process filtering and sorting
    // BUT - if it is server side - then this should be handled by the server - so skip this step
    if(oSettings.oFeatures.bServerSide === false){
        var before = oSettings._iDisplayStart;
        oSettings.oApi._fnReDraw(oSettings);
        //iDisplayStart has been reset to zero - so lets change it back
        oSettings._iDisplayStart = before;
        oSettings.oApi._fnCalculateEnd(oSettings);
    }
      
    //draw the 'current' page
    oSettings.oApi._fnDraw(oSettings);
};

function stripslashes(str) {
  //       discuss at: http://phpjs.org/functions/stripslashes/
  //      original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      improved by: Ates Goral (http://magnetiq.com)
  //      improved by: marrtins
  //      improved by: rezna
  //         fixed by: Mick@el
  //      bugfixed by: Onno Marsman
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //         input by: Rick Waldron
  //         input by: Brant Messenger (http://www.brantmessenger.com/)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //        example 1: stripslashes('Kevin\'s code');
  //        returns 1: "Kevin's code"
  //        example 2: stripslashes('Kevin\\\'s code');
  //        returns 2: "Kevin\'s code"

  return (str + '')
    .replace(/\\(.?)/g, function(s, n1) {
      switch (n1) {
        case '\\':
          return '\\';
        case '0':
          return '\u0000';
        case '':
          return '';
        default:
          return n1;
      }
    });
}

function raiseException( ) {
    $('#filt-opts').append('<div class="alert alert-danger margintop20">'+
                messages['exception_error']+'</div>');
}

function print(){
	$( "#print").printElement({
        printBodyOptions:
        {
        styleToAdd:'background-color:#f5f5f5!important;'
        },
	    overrideElementCSS:[
    		'/css/printElement.css',
    		{ href:'/css/printElement.css',media:'print'}]
        });
};	

//added by rahul
function setDapartmentValue(){
	var deptValue = $('#dept_select option:selected').text();
        $('#dept_value').val(deptValue);
};	

function deleteassessment(id)
{
    var r = confirm("Do you want to delete this assessment instance");
    if (r == true) {
        window.location.href = BASE_URL+'/assessment/assessments/delete/id/'+id;
    }
    else 
    {
        return false;
    }
    
}

function deleteassessmentCategory(id)
{
    var r = confirm("Do you want to delete this assessment category?");
    if (r == true) {
        window.location.href = BASE_URL+'/assessment/assessmentcategories/delete/id/'+id;
    }
    else 
    {
        return false;
    }
    
}

function deleteEventTypes(id)
{
    var r = confirm("Do you want to delete this assessment");
    if (r == true) {
        window.location.href = BASE_URL+'/assessment/eventtype/delete/id/'+id;
    }
    else 
    {
        return false;
    }
    
}
/*
 * To delete a question
 * @author sachin
 * @param {type} url
 * @returns {Boolean}
 * 
 */
function deleteQuestion(id)
{
    var r = confirm("Do you want to delete this question");
    if (r == true) {
        var eventtypeid = CURRENT_URL.split("/").pop();
        //var event_id = temp_array.pop();
        //alert(temp_array);
        window.location.href = BASE_URL+'/assessment/question/delete/eventtypeid/'+ eventtypeid +'/id/'+id;
    }
    else 
    {
        return false;
    }
    
}

function selectorAjaxRequest(value)
{
    value.parents('.select-div').nextAll('.select-div').find('.multi-select-div').empty();
    value.parents('.select-div').nextAll('.select-div').find('.checkbox-div').attr('checked', false);
    var requiredValueDetails =  value.val().replace(' ','');
    //value.parents('.select-div').find('.multi-select-div').html('asd').show();
    var divName = '.div-'+ value.val().replace(' ','');
    var selectorKeyName = value.val();
    
    var dataArray = {};
    
    if(value.attr("checked"))
    {
        $('.select-div').each(function(){
            var formAjaxValue = new Array();
            var formTextValue = '';
            
            var i = 0;
            $(this).find(".form_ajax option:selected").each(function(){
                formAjaxValue[i] = $(this).val();
                formTextValue += $(this).text()+',';
                i++;
            });
            //console.log(formAjaxValue);
            //alert(formAjaxValue);
            if(formAjaxValue.length>0)
            {
                //formAjaxValue = formAjaxValue.slice(0,-1);
                formTextValue = formTextValue.slice(0,-1);

                var selector = $(this).find('.checkbox-div').val().replace(' ','');
                //dataArray[selector+'IdsList'] = formAjaxValue;
                dataArray[selector+'IdsList'] = formAjaxValue;
                
                
                
            }
            else
            {
                var selector = $(this).find('.checkbox-div').val().replace(' ','');
                //dataArray[selector+'IdsList'] = new Array('0');
                dataArray[selector+'IdsList'] = new Array('0');
            }
           
        });
        
        dataArray['key'] = selectorKeyName;
        $.ajax({
            url : BASE_URL+'/assessment/assessments/getselector',
            type: 'POST',
            dataType: 'JSON',
            data:{data:dataArray},
            success: function (data) 
            {
                $(divName).html('');
                //var jsonData = [{"id":"1","name":"IT"},{"id":"2","name":"Admin"},{"id":"3","name":"Account"},{"id":"4","name":"HR"}];
                var html = '<br/><div class="form-group">';
                //html +=    '<label for="email_address" class="col-md-3 col-sm-4">'+selector+'</label>';
                html +=    '<div class="col-sm-1 selector-radio"></div>';
                html +=    '<div class="col-sm-10 selector-radio">';
                html +=    '<select id="'+divName+'" class="form-control form_ajax" placeholder="" name="form_ajax[]" multiple onChange="formAjaxRequest($(this))">';
                if(data == '')
                {
                    html += '<option value="">No Records Found</option>';
                }
                else
                {
                    $.each(data, function(index, element) 
                    {
                        html += '<option value="'+element.Key+'">'+element.Value+' ('+element.TotalResourceCount+')</option>';
                    });
                }

                html += '</select></div></div>';

                $(divName).html(html).show();
                $(document).ajaxStop($.unblockUI);
            },
            error: function () 
            {
                raiseException( );
            },
            beforeSend: function() 
            {
                $.blockUI({ message: '<h1>Please Wait...</h1>'});
            }
        });
        
    }
    else
    {
        $(divName).html('');
    }
    
    
    
    /*
    if(value.attr("checked"))
    {
        $('.select-div').each(function(){
            //alert($(this).find('.checkbox-div').val());

            var formAjaxValue = '';
            var formTextValue = '';

            $(this).find(".form_ajax option:selected").each(function(){
                formAjaxValue += $(this).val()+',';
                formTextValue += $(this).text()+',';
            });

            if(formAjaxValue != '')
            {
                formAjaxValue = formAjaxValue.slice(0,-1);
                formTextValue = formTextValue.slice(0,-1);

                var selector = $(this).find('.checkbox-div').val().replace(' ','');
                dataArray[selector] = formAjaxValue;
            }
        });
        //console.log(dataArray);
        
        
        $.ajax({
            url : BASE_URL+'/assessment/assessments/getselector',
            type: 'POST',
            dataType: 'JSON',
            data:{selector:value.val().replace(' ','')},
            success: function (data) 
            {
                $(divName).html('');
                //var jsonData = [{"id":"1","name":"IT"},{"id":"2","name":"Admin"},{"id":"3","name":"Account"},{"id":"4","name":"HR"}];
                var html = '<br/><div class="form-group">';
                //html +=    '<label for="email_address" class="col-md-3 col-sm-4">'+selector+'</label>';
                html +=    '<div class="col-sm-1 selector-radio"></div>';
                html +=    '<div class="col-sm-10 selector-radio">';
                html +=    '<select id="'+divName+'" class="form-control form_ajax" placeholder="" name="form_ajax[]" multiple onChange="formAjaxRequest($(this))">';
                if(data == '')
                {
                    html += '<option value="">No Records Found</option>';
                }
                else
                {
                    $.each(data, function(index, element) 
                    {
                        html += '<option value="'+element.Key+'">'+element.Value+' ('+element.TotalResourceCount+')</option>';
                    });
                }

                html += '</select></div></div>';

                $(divName).html(html).show();
                $(document).ajaxStop($.unblockUI);
            },
            error: function () 
            {
                raiseException( );
            },
            beforeSend: function() 
            {
                //$.blockUI({ message: '<h1>Please Wait...</h1>'});
            }
        });
    }
    else
    {
        $(divName).html('');
    } */
    
    
    /*$('input[name="selector"]').each(function(){
        
        var divName = '.div-'+ $(this).val().replace(' ','');
        
        if($(this).prop("checked"))
        {
            if($(divName).is(':empty'))
            {
                $.ajax({
                    url : BASE_URL+'/assessment/assessments/getselector',
                    type: 'POST',
                    dataType: 'JSON',
                    data:{selector:$(this).val().replace(' ','')},
                    success: function (data) {
                        $(divName).html('');

                        //var jsonData = [{"id":"1","name":"IT"},{"id":"2","name":"Admin"},{"id":"3","name":"Account"},{"id":"4","name":"HR"}];
                        var html = '<br/><div class="form-group">';
                        //html +=    '<label for="email_address" class="col-md-3 col-sm-4">'+selector+'</label>';
                        html +=    '<div class="col-sm-1 selector-radio"></div>';
                        html +=    '<div class="col-sm-10 selector-radio">';
                        html +=    '<select id="'+divName+'" class="form-control form_ajax" placeholder="" name="form_ajax[]" multiple onChange="formAjaxRequest()">';
                        if(data == '')
                        {
                            html += '<option value="">No Records Found</option>';
                        }
                        else
                        {
                            $.each(data, function(index, element) 
                            {
                               html += '<option value="'+element.Key+'">'+element.Value+' ('+element.TotalResourceCount+')</option>';
                            });
                        }

                        html += '</select></div></div>';

                        $(divName).html(html);
                         $(document).ajaxStop($.unblockUI);
                    },
                   error: function () {
                       raiseException( );
                   },
                   beforeSend: function() {
                      $.blockUI({ message: '<h1>Please Wait...</h1>'});
                   }
                });
            }
            
        }
        else
        {
            //$(divName).html('');
            
        }
    }); */
    formAjaxRequest(value);
    /*var selector = $('input[name="selector"]:checked').val();
    selector = selector.replace(' ','');
    $('.formAjaxEmployee').html('');
    $('.formAjax').html('');
    
    $.ajax({
        url : BASE_URL+'/assessment/assessments/getselector',
        type: 'POST',
        dataType: 'JSON',
        data:{selector:selector},
        success: function (data) {
            $('.formAjax').html('');
            //var jsonData = [{"id":"1","name":"IT"},{"id":"2","name":"Admin"},{"id":"3","name":"Account"},{"id":"4","name":"HR"}];
            var html = '<div class="form-group">';
            html +=    '<label for="email_address" class="col-md-3 col-sm-4">'+selector+'</label>';
            html +=    '<div class="col-sm-5 selector-radio">';
            html +=    '<select id="form_ajax" class="form-control" placeholder="" name="form_ajax[]" multiple onChange="formAjaxRequest()">';
            if(data == '')
            {
                html += '<option value="">No Records Found</option>';
            }
            else
            {
                $.each(data, function(index, element) 
                {
                   html += '<option value="'+element.Key+'">'+element.Value+' ('+element.TotalResourceCount+')</option>';
                });
            }
            
            html += '</select></div></div>';
            
            $('.formAjax').html(html);
             $(document).ajaxStop($.unblockUI);
        },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
          $.blockUI({ message: '<h1>Please Wait...</h1>'});
       }
    }); */
}

function formAjaxRequest(value)
{
    value.parents('.select-div').nextAll('.select-div').find('.multi-select-div').empty();
    value.parents('.select-div').nextAll('.select-div').find('.checkbox-div').attr('checked', false);
    
    var dataArray = {};
    var isAnyDropdownIsSelected = 0;
    $('.select-div').each(function(){
       var formAjaxValue = new Array();
       var i = 0;
       
       $(this).find(".form_ajax option:selected").each(function()
       {
                formAjaxValue[i] = $(this).val();
                isAnyDropdownIsSelected  = 1;
                i++;
       });
       
       
       if(formAjaxValue.length>0)
        {
            var selector = $(this).find('.checkbox-div').val().replace(' ','');
            dataArray[selector+'IdsList'] = formAjaxValue;
        }
        else
        {
            var selector = $(this).find('.checkbox-div').val().replace(' ','');
            dataArray[selector+'IdsList'] = new Array('0');
        }
    });
    
    
    if($.isEmptyObject(dataArray) || isAnyDropdownIsSelected == 0)
    {
        $('.formAjaxEmployee').html('');
    }
    else
    {
        dataArray['key'] = 'employee';
        //console.log(dataArray);
        $.ajax({
            url : BASE_URL+'/assessment/assessments/getemployeedata',
            type: 'POST',
            dataType: 'JSON',
            data:{data:dataArray},
            success: function (data) {
                $('.formAjaxEmployee').html('');
                var jsonData = [{"id":"1","name":"Rahul","department":"IT","email":"rahul@example.com"},{"id":"2","name":"Sachin","department":"HR","email":"sachin@example.com"},{"id":"3","name":"Harpreet","department":"Admin","email":"harpreet@example.com"},{"id":"4","name":"Dipa","department":"IT","email":"dipa@example.com"}];

                var html = '<div class="form-group selector-radio-form">';
                html +=    '<label class="col-md-3 col-sm-4" for="email_address"></label>';
                html +=     '<div class="col-sm-5 selector-radio">';
                html +=     '<label>';
                html +=     '<input id="select-all-employee" type="checkbox" value="1" name="select-all-employee" onChange="selectAllEmployee()">';
                html +=     '&nbsp;&nbsp;&nbsp;Select All';
                html +=     '</label>';
                html +=     '<br></div></div>';



                html += '<div class="form-group">';
                html +=    '<label for="email_address" class="col-md-3 col-sm-4"> Employees <span class="required" style="color:red;">*</span></label>';
                html +=    '<div class="col-sm-5 selector-radio">';
                html +=    '<select id="form_ajax_employee" class="form-control" placeholder="" name="form_ajax_employee[]" multiple>';

                if(data == '')
                {
                     html += '<option value="">No Records Found</option>';
                }
                else
                {
                    $.each(data, function(index, element) 
                    {
                       html += '<option value="'+element.EmpCode+'<==>'+element.Resource+'<==>'+element.Designation+'<==>'+element.Email+'">'+element.Resource+' ('+element.EmpCode+' )</option>';
                    });
                }



                html += '</select></div></div>';


                $('.formAjaxEmployee').html(html);
                 $(document).ajaxStop($.unblockUI);
            },
           error: function () {
               raiseException( );
           },
           beforeSend: function() {
            $.blockUI({message: '<h1>Please Wait...</h1>'});
           }
        });
        
    }   
    
    
    
    
    return false;
  /*  $('.select-div').each(function(){
        //alert($(this).find('.checkbox-div').val());
        
        var formAjaxValue = '';
        var formTextValue = '';
        
        $(this).find(".form_ajax option:selected").each(function(){
            formAjaxValue += $(this).val()+',';
            formTextValue += $(this).text()+',';
        });
        
        if(formAjaxValue != '')
        {
            formAjaxValue = formAjaxValue.slice(0,-1);
            formTextValue = formTextValue.slice(0,-1);
            
            var selector = $(this).find('.checkbox-div').val().replace(' ','');
            dataArray[selector] = formAjaxValue;
        }
    });
    
    if($.isEmptyObject(dataArray))
    {
        $('.formAjaxEmployee').html('');
    }
    else
    {
        $.ajax({
            url : BASE_URL+'/assessment/assessments/getemployeedata',
            type: 'POST',
            dataType: 'JSON',
            data:{data:dataArray},
            success: function (data) {
                $('.formAjaxEmployee').html('');
                var jsonData = [{"id":"1","name":"Rahul","department":"IT","email":"rahul@example.com"},{"id":"2","name":"Sachin","department":"HR","email":"sachin@example.com"},{"id":"3","name":"Harpreet","department":"Admin","email":"harpreet@example.com"},{"id":"4","name":"Dipa","department":"IT","email":"dipa@example.com"}];

                var html = '<div class="form-group selector-radio-form">';
                html +=    '<label class="col-md-3 col-sm-4" for="email_address"></label>';
                html +=     '<div class="col-sm-5 selector-radio">';
                html +=     '<label>';
                html +=     '<input id="select-all-employee" type="checkbox" value="1" name="select-all-employee" onChange="selectAllEmployee()">';
                html +=     '&nbsp;&nbsp;&nbsp;Select All';
                html +=     '</label>';
                html +=     '<br></div></div>';



                html += '<div class="form-group">';
                html +=    '<label for="email_address" class="col-md-3 col-sm-4"> Employees <span class="required" style="color:red;">*</span></label>';
                html +=    '<div class="col-sm-5 selector-radio">';
                html +=    '<select id="form_ajax_employee" class="form-control" placeholder="" name="form_ajax_employee[]" multiple>';

                if(data == '')
                {
                     html += '<option value="">No Records Found</option>';
                }
                else
                {
                    $.each(data, function(index, element) 
                    {
                       html += '<option value="'+element.EmpCode+'<==>'+element.Resource+'<==>'+element.Designation+'<==>'+element.Email+'">'+element.Resource+' ('+element.EmpCode+' )</option>';
                    });
                }



                html += '</select></div></div>';


                $('.formAjaxEmployee').html(html);
                 $(document).ajaxStop($.unblockUI);
            },
           error: function () {
               raiseException( );
           },
           beforeSend: function() {
            $.blockUI({message: '<h1>Please Wait...</h1>'});
           }
        });
    } */
    //console.log(dataArray);
    //alert(dataArray);
    
    
    
    
//    $('.formAjaxEmployee').html('');
//    var formAjaxValue = '';
//    var formTextValue = '';
//    var employeeArray = [];
//    var selector = '';
    /*$(".form_ajax option:selected").each(function()
    {
        //alert($(this).parents('.select-div').find('.checkbox1').val());
        if(selector == '')
        {
           selector = $(this).parents('.select-div').find('.checkbox-div').val();
        }
        else
        {
           if(selector == $(this).parents('.select-div').find('.checkbox-div').val())
           {
               formAjaxValue += $(this).val()+',';
           }
           else
           {
               formAjaxValue = formAjaxValue.slice(0,-1);
               alert(formAjaxValue);
               selector = $(this).parents('.select-div').find('.checkbox-div').val();
               formAjaxValue = '';
               
           }
        }
    });
    
    return false; */
//    $(".form_ajax option:selected").each(function()
//    {
//        
//        
//        formAjaxValue += $(this).val()+',';
//        formTextValue += $(this).text()+',';
//    });
//    
//    formAjaxValue = formAjaxValue.slice(0,-1);
//    formTextValue = formTextValue.slice(0,-1);
//    var selector = $('input[name="selector"]:checked').val();
//    
//    
//    $.ajax({
//        url : BASE_URL+'/assessment/assessments/getemployeedata',
//        type: 'POST',
//        dataType: 'JSON',
//        data:{key:formAjaxValue,selector:selector},
//        success: function (data) {
//            $('.formAjaxEmployee').html('');
//            var jsonData = [{"id":"1","name":"Rahul","department":"IT","email":"rahul@example.com"},{"id":"2","name":"Sachin","department":"HR","email":"sachin@example.com"},{"id":"3","name":"Harpreet","department":"Admin","email":"harpreet@example.com"},{"id":"4","name":"Dipa","department":"IT","email":"dipa@example.com"}];
//            
//            var html = '<div class="form-group selector-radio-form">';
//            html +=    '<label class="col-md-3 col-sm-4" for="email_address"></label>';
//            html +=     '<div class="col-sm-5 selector-radio">';
//            html +=     '<label>';
//            html +=     '<input id="select-all-employee" type="checkbox" value="1" name="select-all-employee" onChange="selectAllEmployee()">';
//            html +=     '&nbsp;&nbsp;&nbsp;Select All';
//            html +=     '</label>';
//            html +=     '<br></div></div>';
//            
//            
//            
//            html += '<div class="form-group">';
//            html +=    '<label for="email_address" class="col-md-3 col-sm-4"> Employees</label>';
//            html +=    '<div class="col-sm-5 selector-radio">';
//            html +=    '<select id="form_ajax_employee" class="form-control" placeholder="" name="form_ajax_employee[]" multiple>';
//            
//            if(data == '')
//            {
//                 html += '<option value="">No Records Found</option>';
//            }
//            else
//            {
//                $.each(data, function(index, element) 
//                {
//                   html += '<option value="'+element.EmpCode+'<==>'+element.Resource+'<==>'+element.Designation+'<==>'+element.Email+'">'+element.Resource+' ('+element.EmpCode+' )</option>';
//                });
//            }
//            
//            
//            
//            html += '</select></div></div>';
//            
//            
//            $('.formAjaxEmployee').html(html);
//             $(document).ajaxStop($.unblockUI);
//        },
//       error: function () {
//           raiseException( );
//       },
//       beforeSend: function() {
//        $.blockUI({message: '<h1>Please Wait...</h1>'});
//       }
//    });
    

}


function selectAllEmployee()
{
    if($('#select-all-employee:checked').length > 0)
    {
        $('#form_ajax_employee option').prop('selected', true);
    }
    else
    {
        $('#form_ajax_employee option').prop('selected', false);
    }
    
    
}

function exportTOExcel(){
    $('.alert-danger').remove();
    var assessmentCategoryID = $('#assessmentCategoryID').val();
    var eventID = $('#EventTypeID').val();
    var assessmentID = $('#assessmentID').val();
    
    
    
    var questionID = $('#questionid').val();
    
    var period = $('[name="period"]:checked').val();
    
    
    var month = $('#month').val();
    var year = $('#year').val();
    var fromPeriod = $('#fromPeriod').val();
    var toPeriod = $('#toPeriod').val();
    
    if(period != null && period != '')
    {
        if(fromPeriod == '')
        {
            $('#filt-opts').append("<div class='alert alert-danger margintop20'>'From Date' must not be blank</div>");
            return false;
        }
        else
        {
            var parts = fromPeriod.split('/');
            var fromPeriod = parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        
        if(toPeriod == '')
        {
            $('#filt-opts').append("<div class='alert alert-danger margintop20'>'To Date' must not be blank</div>");
            return false;
        }
        else
        {
            var parts = toPeriod.split('/');
            var toPeriod = parts[2] + '-' + parts[1] + '-' + parts[0];
        }
    }
    else
    {
        period = '';
    }
    
    window.location = BASE_URL + "/report/questionanalysis/exporttoexcel?event_type="+eventID+"&assessment_id="+assessmentID+"&questionid="+questionID+'&assessment_category='+assessmentCategoryID+"&period="+period+"&month="+month+"&year="+year+"&fromDate="+fromPeriod+"&toDate="+toPeriod;
    
}

function exportTOExcelConsolidated()
{
    $('.alert-danger').remove();
    var assessmentCategoryID = $('#assessmentCategoryID').val();
    var eventID = $('#EventTypeID').val();
    var assessmentID = $('#assessmentID').val();
    
    var startDate = $('#fromPeriod').val();
    var endDate = $('#toPeriod').val();
    var period = $('[name="period"]:checked').val();
    
    if(period != null)
    {
        if(startDate == '')
        {
            $('#filt-opts').append("<div class='alert alert-danger margintop20'>'From Date' must not be blank</div>");
            return false;
        }
        
        if(endDate == '')
        {
            $('#filt-opts').append("<div class='alert alert-danger margintop20'>'To Date' must not be blank</div>");
            return false;
        }
        
    }
    else
    {
        period = '';
    }
        
    if(startDate != '')
    {
        var parts = startDate.split('/');
        var startDate = parts[2] + '-' + parts[1] + '-' + parts[0];
    }
        
    if(endDate != '')
    {
        var parts = endDate.split('/');
        var endDate = parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    
    
    window.location = BASE_URL + "/report/questionanalysis/exportexcelquestion?eventID="+eventID+"&assessmentID="+assessmentID+"&period="+period+'&start_date='+startDate+'&end_date='+endDate+'&assessment_category='+assessmentCategoryID;
    
}



function exportTOConsolidateReport(assessmentID){
    $('.alert-danger').remove();
    var assessmentCategoryID = $('#assessmentCategoryID').val();
    var eventID = $('#EventTypeID').val();
    //var assessmentID = $('#assessmentID').val();
	var startDate = $('#startDate').val();
    var endDate = $('#endDate').val();
    var period = '';
   /* var startDate = $('#fromPeriod').val();
    var endDate = $('#toPeriod').val();
    var period = $('[name="period"]:checked').val();
    
    if(period != null)
    {
        if(startDate == '')
        {
            $('#filt-opts').append("<div class='alert alert-danger margintop20'>'From Date' must not be blank</div>");
            return false;
        }
        
        if(endDate == '')
        {
            $('#filt-opts').append("<div class='alert alert-danger margintop20'>'To Date' must not be blank</div>");
            return false;
        }
        
    }
    else
    {
        period = '';
    }
        
    if(startDate != '')
    {
        var parts = startDate.split('/');
        var startDate = parts[2] + '-' + parts[1] + '-' + parts[0];
    }
        
    if(endDate != '')
    {
        var parts = endDate.split('/');
        var endDate = parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    */

    window.location = BASE_URL + "/report/questionanalysis/exportexcelconsolidate?eventID="+eventID+"&assessmentID="+assessmentID+"&period="+period+'&start_date='+startDate+'&end_date='+endDate+'&assessment_category='+assessmentCategoryID;
    
    
}


function displayInGrid()
{
    if (CURRENT_URL.indexOf("consolidate") >= 0)
    {
        $('.alert-danger').remove();
        
        var startDate = $('#fromPeriod').val();
        var endDate = $('#toPeriod').val();
        var period = $('[name="period"]:checked').val();
        
        if(period != null)
        {
            if(startDate == '')
            {
                $('#filt-opts').append("<div class='alert alert-danger margintop20'>'From Date' must not be blank</div>");
                return false;
            }

            if(endDate == '')
            {
                $('#filt-opts').append("<div class='alert alert-danger margintop20'>'To Date' must not be blank</div>");
                return false;
            }
        
        }
        
        if(startDate != '')
        {
            var parts = startDate.split('/');
            var startDate = parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        
        if(endDate != '')
        {
            var parts = endDate.split('/');
            var endDate = parts[2] + '-' + parts[1] + '-' + parts[0];
        }
    
        
        
        $('#employee-grid').dataTable().fnClearTable();
        //$('#employee-grid').DataTable().destroy();
        $('#employee-grid').DataTable().ajax.url('/report/questionanalysis/consolidate?assessmentID='+$('#assessmentID').val()+'&start_date='+startDate+'&end_date='+endDate).load();
        //$('.ajax').show();
        return false;
    }
    
}

function displayInGridassessmentStatus()
{
        $('.alert-danger').remove();
        
        //var startDate = $('#fromPeriod').val();
        //var endDate = $('#toPeriod').val();
        var period = $('[name="period"]:checked').val();
        
        if(period != null)
        {
            if(startDate == '')
            {
                $('#filt-opts').append("<div class='alert alert-danger margintop20'>'From Date' must not be blank</div>");
                return false;
            }

            if(endDate == '')
            {
                $('#filt-opts').append("<div class='alert alert-danger margintop20'>'To Date' must not be blank</div>");
                return false;
            }
        
        }
        
        /*if(startDate != '')
        {
            var parts = startDate.split('/');
            var startDate = parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        
        if(endDate != '')
        {
            var parts = endDate.split('/');
            var endDate = parts[2] + '-' + parts[1] + '-' + parts[0];
        }*/
    
        
        
        $('#assessment-status-grid').dataTable().fnClearTable();
        //$('#employee-grid').DataTable().destroy();
        $('#assessment-status-grid').DataTable().ajax.url('/report/questionanalysis/assessmentstatus?EventTypeID='+$('#EventTypeID').val()).load();
        //$('.ajax').show();
        return false;
}

function exportTOExcelassessmentStatus()
{
    $('.alert-danger').remove();
    var eventTypeID = $('#EventTypeID').val();
    var assessmentCategoryID = $('#assessmentCategoryID').val();
    
    window.location = BASE_URL + "/report/questionanalysis/exportexcelassessmentstatus?eventTypeID="+eventTypeID+'&assessment_category='+assessmentCategoryID;
    
}

function getEventTypeQuestions()
{
    var eventTypeID = $('#event-type-select').val();
    
    if(eventTypeID != '')
    {
        $.ajax({
            url : BASE_URL+'/assessment/ajax/getquestiondetailsbyeventtype',
            type: 'POST',
            dataType: 'JSON',
            data: 'event-type-id='+eventTypeID,
            success: function (data) {
                var html = '';
                if(data == 'No Records Found')
                {
                    html += '<div class="form-group">';
                    html +=       '<label class="col-md-3 col-sm-4">'
                    html +=           '<dt id="input_type-label">';
                    html +=             '<label class="required" for="input_type">Questions</label>'
                    html +=                '</dt>';
                    html +=             '</label>';
                    html +=            '<div class="col-sm-6">';
                    html +=                   '<div class="checkbox">';
                    html +=                         '<label>';
                    html +=                             'No Records Found'
                    html +=                         '</label>';
                    html +=                     '</div>';
                    html +=             '</div>';
                    html += '</div>';
                }
                else
                {
                    html  += '<div class="form-group selector-radio-form">';
                    html +=    '<label class="col-md-3 col-sm-4" for="email_address"></label>';
                    html +=     '<div class="col-sm-5 selector-radio">';
                    html +=     '<label>';
                    html +=     '<input id="select-all-employee" type="checkbox" value="1" name="select-all-employee" onChange="selectAllassessments()">';
                    html +=     '&nbsp;&nbsp;&nbsp;Select All';
                    html +=     '</label>';
                    html +=     '<br></div></div>';
                    $.each(data, function(index, element) 
                    {
                        
                        var inputType='';
                        if(element.input_type == '')
                            inputType = 'Label';
                        else
                            inputType = ucwords(element.input_type);
                        
                            html += '<div class="form-group">';
                            html +=       '<label class="col-md-3 col-sm-4">'
                            html +=           '<dt id="input_type-label">';
                            if(index== 0)
                                html +=             '<label class="required" for="input_type">Questions</label>';
                            else
                                html +=             '<label class="" for="input_type"></label>';
                            html +=                '</dt>';
                            html +=             '</label>';
                            html +=            '<div class="col-sm-6">';
                            html +=                   '<div class="checkbox">';
                            html +=                         '<label>';
                            html +=                             '<input type="checkbox" id="question-details" name="question-details[]" value="'+element.questionid+'"> ('+inputType+') '+element.question ;
                            html +=                         '</label>';
                            html +=                     '</div>';
                            html +=             '</div>';
                            html += '</div>';
                        
                    });
                }
                $('.ajax-row').html(html);
                 $(document).ajaxStop($.unblockUI);
           },
           error: function () {
               raiseException( );
           },
           beforeSend: function() {
              $.blockUI({ message: '<h1>Please Wait...</h1>'});
           }
        });
    
    }
    else
    {
        $('.ajax-row').html('');
    }
    
}

function ucwords (str) {
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}
function getassessmentName(eventTypeId)
{
    $('.error-messages').html('');
    var assessmentCategoryID = $('#assessment-category-select').val();
     $('.ajax-row').html('');
    if(assessmentCategoryID != '')
    {
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();
        $.ajax({
            url : BASE_URL+'/assessment/ajax/getassessmentnamesbycategory',
            type: 'POST',
            dataType: 'JSON',
            data: 'assessment-category-id='+assessmentCategoryID+'&fromDate='+fromDate+'&toDate='+toDate+'&eventtypeid='+eventTypeId,
            success: function (data) {
                var html = '';

                if(data == 'No Records Found')
                {
                    html += '<option value="">No Records Found</option>';
                }
                else
                {
                    //isRecordExist = 1;
                    html+='<option value="">Please Select</option>';
                    $.each(data, function(index, element) 
                    {
                        html += '<option value="'+element.event_typeid+'">'+element.event_type+'</option>';
                    });
                }
            
                $('#event-type-select').html(html);
                 $(document).ajaxStop($.unblockUI);
            
            },
            error: function () {
               raiseException( );
           },
           beforeSend: function() {
              $.blockUI({ message: '<h1>Please Wait...</h1>'});
           }
		});
	}
	else
        {
            $('#event-type-select').html('<option value="">No Records Found</option>');
            
            var errorHtml = '<div class=""><div class="alert alert-danger"><a class="close" data-dismiss="alert">×</a><strong>Error! </strong><br/>';
                    
            errorHtml += 'Please select assessment category' + "<br>";
                    

                errorHtml +=  '</div</div </div></div></div>';

                $('.error-messages').html(errorHtml);
	}
}	

function clearDateFilter()
{
    $('#fromDate').val('');
    $('#toDate').val('');
    $('#assessment-category-select').val('');
    $('.ajax-row').html('');
    $('#event-type-select').html('<option value="">No Records Found</option>');
}

function selectAllassessments()
{
    if($('#select-all-employee:checked').length > 0)
    {
        $("input[name='question-details[]']").prop('checked', true);
    }
    else
    {
        $("input[name='question-details[]']").prop('checked', false);
    }
    
    
}

function fillEventType( ) {
    $.ajax({
       url : BASE_URL+'/assessment/ajax/filleventtype',
       type: 'POST',
       dataType: 'JSON',
       async: false,
       data: 'category_id='+$('select#assessmentCategoryID').val(),
       success: function (data) {
           
           $('select#EventTypeID').html('');       
           if ( data['error_code'] == '0' ) {
               $('select#EventTypeID').html(data.assessment_dd);            
               $('.select-preloader').remove();
           }
           else {
                $('select#EventTypeID').html(data.assessment_dd);  
                $('.select-preloader').remove();
               //alert(data.error_msg);
           }
       },
       error: function () {
           raiseException( );
       },
       beforeSend: function() {
           $('select#EventTypeID')
               .after('<div class="select-preloader">Loading...</div>');
       }
    });
    
   // displayInGrid();
}