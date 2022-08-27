var dialogs = {};

$(document).ready(function () {

});
$(document).on('submit', '#taskQueueForm', function (e) {
    e.preventDefault();
    e.stopPropagation();

    let formData = $(this).serialize();
    let taskQueueSid = $(this).find('input[name="sid"]').val();

    $.ajax({
        url: $(this).attr('action'),
        data: formData,
        type: 'POST',
        success: function (response) {
            successMessage('Saved successfully');
            if (taskQueueSid === '') {
                setTaskQueueOptions(response.taskQueue.sid, response.taskQueue.friendlyName);
                setTaskQueueIntoList(response.taskQueue.sid, response.taskQueue.friendlyName)
            } else {
                $('.list-group-sid-' + response.taskQueue.sid).find('.pull-left').text(response.taskQueue.friendlyName);
            }

            $('#taskQueueFormError').hide();
            $('#taskQueueModal').modal().hide();
        },
        error: function (err) {
            $('#taskQueueFormError p').html(err.responseJSON.error);
            $('#taskQueueFormError').show();
        }
    });
});

$(document).on('click', '.taskQueueEdit', function (e) {
    e.preventDefault();
    e.stopPropagation();

    let sid = $(this).data('sid');
    let workspaceSid = $('input[name="workspace_sid"]').val();
    $.ajax({
        url: base_url + 'settings/integrations/twilio/workspace/' + workspaceSid + '/task_queue_save/' + sid,
        type: 'GET',
        success: function (response) {
            if (response.data) {
                $('#taskQueueModal').remove();
                $('#taskQueueModalForm').html(response.data);
                $('#taskQueueModal').modal().show();
            } else {
                errorMessage('Something went wrong!');
            }
        },
        error: function (err) {
            console.log(err, 'err');
        }
    });

});

$(document).on('change', '.workspaceSelect', function(e) {
    e.preventDefault();
    e.stopPropagation();
    let workspaceSid = $(this).val();
    let queueInTaskQueuesList = $(this).parent('.queueInTaskQueuesList');

    if (workspaceSid != 0) {
        $('input[name="workspace_sid"]').val(workspaceSid);
        $.ajax({
            url: base_url + 'settings/integrations/twilio/workspace/get_data_by_sid/' + workspaceSid,
            type: 'GET',
            success: function (response) {
                if (response) {
                    $.each(response.taskQueues, function (i, t) {
                        setTaskQueueOptions(t.sid, t.friendlyName);
                        setTaskQueueIntoList(t.sid, t.friendlyName)
                    });
                    $.each(response.workflows, function (i, w) {
                        setWorkflowOptions(w.sid, w.friendlyName);
                    });
                    $('.create_taskQueue_for_workspace').removeClass('hidden');
                } else {
                    errorMessage('Something went wrong!');
                }
            },
            error: function (err) {
                console.log(err, 'err');
            }
        });
    } else {
        queueInTaskQueuesList.find('input[name="workspace_sid"]').val('');
        queueInTaskQueuesList.find('input[name="queue_name"]').val('');
        queueInTaskQueuesList.find('select[name="task_queue_sid"]').html('');
        $('.create_taskQueue_for_workspace').addClass('hidden');
    }
});

$(document).on('click', '#bottonCreateTaskQueue', function (e) {
    e.stopPropagation();
    e.preventDefault();
    let workspaceSid = $('input[name="workspace_sid"]').val();
    $.ajax({
        url: base_url + 'settings/integrations/twilio/workspace/' + workspaceSid + '/task_queue_save/0',
        type: 'GET',
        success: function (response) {
            if (response.data) {
                $('#taskQueueModal').remove();
                $('#taskQueueModalForm').html(response.data);
                $('#taskQueueModal').modal().show();
            } else {
                errorMessage('Something went wrong!');
            }},
        error: function (err) {
            console.log(err, 'err');
        }
    });
});

$(document).on('click', '.taskQueueDelete', function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (confirm('Are you sure delete this task queue?')) {
        let sid = $(this).data('sid');
        let workspaceSid = $('input[name="workspace_sid"]').val();
        $.ajax({
            url: base_url + 'settings/integrations/twilio/workspace/' + workspaceSid + '/task_queue_delete/' + sid,
            type: 'DELETE',
            success: function (response) {
                $('select[name="task_queue_sid"] option[value="'+sid+'"]').remove();
                $('.list-group-sid-' + sid).remove();
                successMessage('Removed successfully');
            },
            error: function (err) {
                alert(err.responseJSON.error);
            }
        });
    }
});

$(document).on('change', 'select[name="task_queue_sid"]', function() {
    let sid = $(this).val();
    $('input[name="queue_name"]').val(sid);
});

function setTaskQueueOptions(sid, friendlyName) {
    if (friendlyName !== undefined && sid !== undefined) {
        $('select[name="task_queue_sid"]').append('<option value="'+sid+'">'+friendlyName+'</option>');
    }
}

function setWorkflowOptions(sid, friendlyName) {
    if (friendlyName !== undefined && sid !== undefined) {
        $('select[name="workflow_sid"]').append('<option value="'+sid+'">'+friendlyName+'</option>');
    }
}

function setTaskQueueIntoList(sid, friendlyName) {
    $('ul#taskQueueList').append('<li class="list-group-item list-group-sid-'+sid+'">\n' +
        '                            <div class="media-body">\n' +
            '                            <div class="pull-right">\n' +
            '                                <button class="taskQueueEdit btn btn-xs btn-default" data-sid="'+sid+'">Edit</button>\n' +
            '                                <button class="taskQueueDelete btn btn-xs btn-danger" data-sid="'+sid+'">Delete</button>\n' +
            '                            </div>\n' +
        '                                <span class="pull-left">'+friendlyName+'</span>\n' +
        '                            </div>\n' +
        '                        </li>');
}

$(document).on('click', '.music_block .add', function(e) {
    e.preventDefault();
    e.stopPropagation();

    var music_block_wrapper = $(this).parents('.music_block_wrapper');
    var currentKey = music_block_wrapper.data('key');
    var newKey = currentKey + 1;
    var newDivContent = music_block_wrapper.html();

    let newDiv = $('<div class="music_block_wrapper" data-key="'+ newKey +'">' + newDivContent + '</div>')
        .show()
        .insertAfter(music_block_wrapper);

    $('.add', newDiv).addClass('hidden');
    $('.remove', newDiv).removeClass('hidden');

    $('input[name="music[' + currentKey + ']_say"]', newDiv).attr('name', 'music['+ newKey +']_say');
    $('input[name="music[' + currentKey + ']_play"]', newDiv).attr('name', 'music['+ newKey +']_play');
    $('input[name="music[' + currentKey + ']_mode"]', newDiv).attr('name', 'music['+ newKey +']_mode');
    $('input[name="music[' + currentKey + ']_tag"]', newDiv).attr('name', 'music['+ newKey +']_tag');
    $('input[name="music[' + currentKey + ']_caller_id"]', newDiv).attr('name', 'music['+ newKey +']_caller_id');

    $('.play_music_while_calling_repeat', newDiv).attr('name', 'repeat[' + newKey + ']');
    $('.play_music_while_calling_say', newDiv).attr('name', 'say[' + newKey + ']');
    $('.play_music_while_calling_pause', newDiv).attr('name', 'pause[' + newKey + ']');

});

$(document).on('click', '.music_block .remove', function(e) {
    e.preventDefault();
    e.stopPropagation();

    $(this).parents('.music_block_wrapper').remove();
});