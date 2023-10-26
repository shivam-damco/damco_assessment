/**
 * Javascript file to handle TAE assessment operations
 * @author  Harpreet Singh
 * @date    30th April, 2019
 * @version 1.0
 */

$(function(){
    $('.assessment-ques-answ').on('click', '.process-question', function(){
        var qid = $(this).attr('id').replace(/(question_)/, '').replace(/(_[0-9]+)$/, '');
        var value = $.trim($(this).val());
        var divID = ( $(this).attr('data-group-last-ques') == 1 )
                    ? $(this).closest('div.panel').attr('id') : '';
        if ( $(this).prop('type') === 'checkbox' ) {
            if ( $(this).is(':checked') ) {
                $('#' + $(this).attr('id').replace(/(question_)/, 'textarea_')).parent().slideDown('slow');
            }
            else {
                $('#' + $(this).attr('id').replace(/(question_)/, 'textarea_')).val('').parent().slideUp('slow');
            }
            return;
        }

        if ( $(this).prop('type') === 'button' && $(this).hasClass('checkbox-button') ) {
            var selectedCheckboxes = [];
            $.each($('input[name="question['+ qid +'][]"]:checked'), function(){            
                selectedCheckboxes.push($(this).val());
            });

            $(this).closest('.panel-default').removeClass('panel-danger');
            if ( selectedCheckboxes.length === 0 ) {
                $(this).closest('.panel-default').addClass('panel-danger');
                return ;
            }
            value = selectedCheckboxes.join(",");
        }
        saveAnswerAndShowNext(qid, value, divID, $(this).attr('id'));
    });
});

function saveAnswerAndShowNext(qid, value, divID, objectID) {
    $('#dynamic_error_div').remove();
    $('#ID_pbar').removeClass('panel-danger');
    
    if ( checkValidations( objectID ) === false ) {
        jQuery("#btnSubmitassessment").remove();
        jQuery('#thankyoudiv').hide();
    }
    else {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: BASE_URL + "/assessment/tae/processResponse/",          
            data: 'questionid=' + qid + "&response=" + value + "&assessment=" + $('#assessment').val() + "&langid=" + $('#langid').val() + "&questiontext=" + jQuery('#questiontext_' + qid).val() + "&shownxtquestion=y",
            success: function(result) {
                if (result.status === 'close' || result.status === 'did not qualify') {
                    jQuery('#ID_pbar').removeClass('hide').show();
                    $('#thankyoudiv').addClass("alert alert-success");
                    jQuery('#thankyoudiv').show();
                    $("#prog_bar").css("width", "100%");
                    $("#prog_bar").html("100%");
                    if ( jQuery("#btnSubmitassessment").length > 0 ){
                        jQuery("#btnSubmitassessment").remove();
                    }
                    jQuery(".panel-footer div ul li")
                            .append("<button type='button' id='btnSubmitassessment' class='btn btn-primary' disabled='disabled'>Submit</button>"); 
                    jQuery('#btnSubmitassessment').removeAttr('disabled');
                    jQuery('#task').val(result.status);
                }
                else {
                    jQuery('#task').val('');
                    jQuery("#btnProceed").remove();
                    jQuery("#ID_" + result.ID + "_" + result.question_id).after(result.question_string);
                    jQuery('#ID_pbar').removeClass('hide').show();
                    if ( result.status === 'next' ) {
                        fillProgressBar(result.answer_count, result.questions_count);
                        jQuery('#thankyoudiv').hide();
                        jQuery("#btnSubmitassessment").remove();
                    }
                }
            },
            beforeSend: function() {
                if ( divID !== '' ) {
                    jQuery("#btnSubmitassessment").remove();
                    jQuery('#thankyoudiv').hide();
                    $('#'+divID).nextAll().each(function() {
                        var id = $(this).attr('id');
                        if (id !== 'ID_pbar') {
                            $(this).remove();
                        }
                    });
                }
            },
            error: function() {
                if ( $('#dynamic_error_div').length === 0 ) {
                    $('#ID_pbar').addClass('panel-danger');
                    $('#ID_pbar').prepend('<div id="dynamic_error_div" class="panel-heading">' + assessment_error_text + '</div>');
                }
            }
        });
    }
}

function checkValidations ( objectID ) {
    $('.panel-danger').removeClass('panel-danger');
    var count = 0;

    // Self check
    $('#' + objectID).closest('.panel-default').each(function(){
        count = validateField ( this, count )
    });

    // Check previous questions
    $('#' + objectID).closest('.panel-default').prevAll('.panel-default')
            .each(function(){
        count = validateField ( this, count )
    });

    var checkAttribute = $('#' + objectID).attr('data-group-last-ques');
    var isBranchingQuestion = ( typeof checkAttribute !== typeof undefined 
                                && checkAttribute !== false) ? checkAttribute : '0';

    if ( count > 0 ) {
        if ( ( $('#' + objectID).prop('type') === 'radio'
               && !$('#' + objectID).hasClass('sub-question') )
             || ( $('#' + objectID).prop('type') === 'radio'
                  && $('#' + objectID).hasClass('sub-question')
                  && isBranchingQuestion == 1 ) ) {
            $('#' + objectID).prop('checked', false);
            
            if ( $('#' + objectID).prop('type') === 'radio'
                 && $('#' + objectID).hasClass('sub-question')
                 && isBranchingQuestion == 1 ) {
                $('#' + objectID).parents('.panel-default').addClass('panel-danger');
            }
            
            return false;
        }
        else if ( $('#' + objectID).prop('type') === 'select'
                  || $('#' + objectID).prop('type') === 'text'
                  || $('#' + objectID).prop('type') === 'textarea' ) {
            $('#' + objectID).val('');
            return false;
        }
        return true;
    }
    else {
        return true;
    }
}

function validateField ( object, count ) {
        var names = {};
        $('#' + $(object).attr('id') + ' input:radio').each(function() {
            names[$(this).attr('name')] = 'radio';
        });
        $('#' + $(object).attr('id') + ' select').each(function() {
            names[$(this).attr('name')] = 'select';
        });
        $('#' + $(object).attr('id') + ' input:text').each(function() {
            if ( $(this).attr('name') !== undefined ) {
                names[$(this).attr('name')] = 'text';
            }
        });
        $('#' + $(object).attr('id') + ' textarea').each(function() {
            if ( $(this).hasClass('mandatory') ) {
                names[$(this).attr('name')] = 'textarea';
            }
        });

        $.each(names, function(key, value) {
            switch ( value ) {
                case 'radio':
                    if ( !$('input[name="'+key+'"]').is(':checked')) {
                        count++;
                        if ( !$('input[name="'+key+'"]').hasClass('sub-question') ) {
                            $('input[name="'+key+'"]').parents('.panel-default')
                                .addClass('panel-danger');
                        }
                    }
                    break;
                case 'select':
                    if ( $('select[name="'+key+'"]').val() === '' ) {
                        count++;
                        $('select[name="'+key+'"]').parents('.panel-default')
                                .addClass('panel-danger');
                    }
                    break;
                case 'text':
                    if ( $.trim($('input[name="'+key+'"]').val()) === '' ) {
                        count++;
                        $('input[name="'+key+'"]').parents('.panel-default')
                                .addClass('panel-danger');
                    }
                    break;
                case 'textarea':
                    if ( $.trim($('textarea[name="'+key+'"]').val()) === '' ) {
                        count++;
                        $('textarea[name="'+key+'"]').parents('.panel-default')
                                .addClass('panel-danger');
                    }
                    break;
            }
        });
        return count;
    }

function fillProgressBar( answerCount, totalQuestions ) {
    var pw = ( answerCount/totalQuestions ) * 100;
    var prog_width = Math.floor(pw) + '%';

    $("#prog_bar").css("width", prog_width).html(prog_width)
    if ( pw > 100 ) {
        $("#prog_bar").css("width", "100%").html("100%")
    }
}