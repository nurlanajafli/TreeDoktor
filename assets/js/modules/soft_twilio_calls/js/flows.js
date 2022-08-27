/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/

 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/

var dialogs = {};
var activeAnchor;


$(document).on('submit', 'form[name="modalDeleteNotify"]', function(e) {
    e.preventDefault();
    var currentModal = $(this);
    $.ajax({
        url: currentModal.attr('action'),
        data: {},
        success: function (data) {
            if (!data.error) {
                currentModal.find('.error').addClass('hidden');
                document.location = data.url;
                window.location.reload();
                return true;
            } else {
                currentModal.find('.error').html(data.error);
                currentModal.find('.error').removeClass('hidden');
            }
        },
        type: 'POST',
        dataType: 'json'
    });
});
$(document).on('submit',
    'form[name="modalFlowForm"], ' +
    'form[name="modalActivenumbersForm"], ' +
    'form[name="modalWorkspaceForm"], ' +
    'form[name="modalApplicationForm"]', function(e) {
    e.preventDefault();
    var currentModal = $(this);
    let data = currentModal.serialize();
    $.ajax({
        url: currentModal.attr('action'),
        data: data,
        success: function (data) {
            if (!data.error) {
                currentModal.find('.error').addClass('hidden');
                document.location = data.url;
                window.location.reload();
                return true;
            } else {
                currentModal.find('.error').html(data.error);
                currentModal.find('.error').removeClass('hidden');
            }
        },
        type: 'POST',
        dataType: 'json'
    });
});

$(document).on('submit', 'form[name="modalMessagingServicesForm"]', function(e) {
        e.preventDefault();
        var currentModal = $(this);
        let data = currentModal.serialize();
        $.ajax({
            url: currentModal.attr('action'),
            data: data,
            success: function (response) {
                if (response.data.errors) {
                    if (typeof response.data.errors == 'object') {
                        console.log(response.data.errors, 'response.data.errors');
                        Object.keys(response.data.errors).map(( v, i ) => {
                            $('.' + v).html(response.data.errors[v][0]);
                        });
                    }
                    return false;
                } else {
                    window.location.reload();
                }
            },
            type: 'POST',
            dataType: 'json'
        });
    });

$(document).ready(function () {

    $('.add-flow').click(function (event) {
        event.preventDefault();
        $.get('/settings/integrations/twilio/get_flow_modal_form', function (response) {
            $('#flow-modal').html(response);
            $('#flowCreateModal').modal('show');
        });
    });


    $(document).on('click', '.edit-activenumbers', function(e) {
        e.preventDefault();
        $.get($(this).attr('href'), function (response) {
            $('#active-numbers-modal').html(response);
            $('#activenumbersCreateModal').modal('show');
        });
    });

    $(document).on('click', '.create-workspace', function(e) {
        e.preventDefault();
        $.get($(this).attr('href'), function (response) {
            $('#workspace-modal').html(response);
            $('#workspaceCreateModal').modal('show');
        });
    });

    $(document).on('click', '.create-application, .edit-application', function(e) {
        e.preventDefault();
        $.get($(this).attr('href'), function (response) {
            $('#application-modal').html(response);
            $('#applicationModalAction').modal('show');
        });
    });
    $(document).on('click', '.edit-messaging_service, .create-messaging_service', function(e) {
        e.preventDefault();
        $.get($(this).attr('href'), function (response) {
            $('#messaging-services-modal').html(response.data);
            $('#messagingServicesModal').modal('show');
        });
    });


    $('a.deleteFlow, a.delete-workspace, a.delete-application, a.delete-messaging_service, a.uninstall-sms').click(function (event) {
        event.preventDefault();
        $('form[name="modalDeleteNotify"]').attr('action', $(this).attr('href'));
        $('#deleteModal').modal('show')
    });

    // edit flow name
    $(document).on('click', '.flow-name-display', function (event) {
        event.stopPropagation();
        $(this).hide().siblings('.flow-name-edit').show();
    });
    $(document).on('click', '.flow-name-edit-cancel', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var $this = $(this);
        var $inp = $this.siblings('input[name="flow_name"]');
        $inp.val($inp.attr('data-orig-value'))
            .closest('span').hide()
            .siblings('.flow-name-display').show();
    });
    $(document).on('click', '.flow-name-edit button.submit-button', function (event) {
        event.stopPropagation();
        event.preventDefault();
        var $this = $(this);
        $this.prop('disabled', true);
        var _name = $this.siblings('input[name="flow_name"]').val();
        var application_id = $('#dAddFlow select[name="application_id"]').val();
        $this.addClass('disabled');
        $.post($this.attr('data-action'), {
                name: _name,
                application_id: application_id
            }, function (data) {
                $this.removeClass('disabled');
                if (!data.error) {
                    $.notify('Flow name has been updated.');
                    $('tr#flow-' + data.flow_id).find('input[name="flow_name"]').attr('data-orig-value', _name)
                        .closest('span').hide()
                        .siblings('.flow-name-display').text(_name).show();
                } else {
                    $.notify('There was an error updating the Flow: ' + data.message);
                }
                $this.prop('disabled', false);
            },
            'json'
        );
    });
});
$(document).on('click', '.edit-flow', function(e) {
    $('#processing-modal').modal();
});

$(document).on('change', '#sms_messenger', function() {
    let checked = ($(this).is(':checked') === true) ? 1 : 0;

    $.ajax({
        url : '/settings/saveByKeyName',
        type : 'post',
        data: {'data': {messenger : checked}},
        async : false,
        dataType : 'json',
        success : function(response) {
            if (checked === 1) {
                $.notify('Successfully messenger turned on');
            } else {
                $.notify('Successfully messenger turned off');
            }

        },
        error : function(XHR, textStatus, errorThrown) {
        }
    });
});
$(document).on('click', '.sms_numbers_block_duplicate', function(e) {
    e.stopPropagation();
    e.preventDefault();
    let newItem = $('.sms_numbers_item_block').first();
    let newItemClone = newItem.clone();
    let primary_number_total = $('.primary_number').length + 1;
    let sms_service_twilio_number_total = $('.sms_service_twilio_number').length + 1;

    newItemClone.find('.sms_numbers_block_duplicate')
        .removeClass('sms_numbers_block_duplicate fa-plus-circle')
        .addClass('sms_numbers_block_duplicate_remove fa-minus-circle');


    newItemClone.find('.primary_number').attr({
        'name': 'sms_twilio_primary_number[' + primary_number_total + ']',
        'checked': false
    });
    newItemClone.find('.sms_service_twilio_number_total').attr('name', 'twilioNumber[' + sms_service_twilio_number_total + ']');

    $('.sms_numbers_block').append(newItemClone);
});

$(document).on('click', '.sms_numbers_block_duplicate_remove', function(e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).parents('.sms_numbers_item_block').remove();
});