var Tasks = function () {
    var config = {
        ui: {
            section: '#tasks_section',
            table: '#tasks-table',
        },
        events: {},
        route: {
            leads: base_url + 'tasks',
        },

        templates: {
            tag_container: '<span class="tag-item-container {0}" style="cursor: pointer">' +
                '<a class="btn btn-xs btn-{3} m-r-xs m-b-xs js-tag-name" data-tag-id="{1}"  data-tag-name="{2}">{2}</a>' +
                '</span>',

            more_button: '<a class="js-expand-more" data-toggle="class:show"><span class="text">...</span><span class="text-active">...</span></a>',
        }
    };

    var _private = {
        init: function () {
            _private.init_task_list();
        },

        init_task_list: function () {
            dataTable = $(config.ui.table).DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "paging": true,
                "pageLength": 50,
                'sDom': "<'row'>t<'datatable-footer'<'col-sm-6'i><'col-sm-6'p>>",
                "createdRow": function (row, data) {
                    row.setAttribute('data-trees-di', data.task_id)
                },
                ajax: {
                    method: 'get',
                    url: base_url + 'tasks',
                    dataSrc: function (response) {
                        console.log(response);
                        if (response.data.original.length === 0) {
                            $(config.ui.section).hide();
                        } else {
                            $(config.ui.section).show();
                        }
                        return response.data.original;
                    },
                },
                columnDefs: [
                    {"name": "task_id", "targets": 0, "visible": false},
                    {"targets": [1, 2, 3, 4, 5, 6, 7, 8], "orderable": false},
                    {
                        'targets': 0,
                        'render': function (data, type, row, meta) {
                            return row.task_id;
                        }
                    },
                    {
                        'targets': 1,
                        'render': function (data, type, row, meta) {
                            let result = 'No Client';
                            if (row.client != null && row.client.client_name) {
                                result = `
                                    <a href="${base_url}client/${row.client.client_id}">${row.client.client_name}</a>
                                `;
                            }
                            return result;
                        }
                    },
                    {
                        'targets': 2,
                        'render': function (data, type, row, meta) {
                            let result = moment().format('YYYY-MM-D h:mm:ss');
                            if (row.task_start_date != null) {
                                result = row.task_start_date;
                            }
                            return `${result}`;
                        }
                    },
                    {
                        'targets': 3,
                        'render': function (data, type, row, meta) {
                            var result = (row.task_city?(row.task_city+ ', '):'') + (row.task_address?row.task_address:'');
                            if(!row.task_city && !row.task_address)
                                result = ' — ';

                            return `${result}`;
                        }
                    },
                    {
                        'targets': 4,
                        'render': function (data, type, row, meta) {
                            let result = '—';
                            if (row.owner != null && row.owner.firstname != null && row.owner.lastname != null) {
                                result = row.owner.firstname + ', ' + row.owner.lastname;
                            }
                            return `${result}`;
                        }
                    },
                    {
                        'targets': 5,
                        'render': function (data, type, row, meta) {
                            let color = '#fff';
                            let name = '';
                            if (row.category != null && row.category.category_color != null) {
                                color = row.category.category_color;
                            }
                            if (row.category != null && row.category.category_name != null) {
                                name = row.category.category_name;
                            }
                            return `
                                <span style="border: 1px solid #000;display: inline-block;width: 18px;height: 18px;background: ${color}"></span>
                                ${name}
                            `;
                        }
                    },
                    {
                        'targets': 6,
                        'render': function (data, type, row, meta) {
                            return row.statuses[row.task_status];
                        }
                    },
                    {
                        'targets': 7,
                        'render': function (data, type, row, meta) {
                            let checkUnCheck = (row.task_no_map == null || row.task_no_map == undefined) ? 'times' : 'check';
                            return `
                                <i class="fa fa-${checkUnCheck}"></i>
                            `;
                        }
                    },
                    {
                        'targets': 8,
                        'render': function (data, type, row, meta) {
                            return `<div class="text-center"></div>
									<a href="#" id="view_task_data" data-id="` + row.task_id + `" role="button" class="btn btn-xs btn-default" data-backdrop="static" data-keyboard="false"><i class="fa fa-eye"></i></a>
									<a href="tasks/edit/` + row.task_id + `" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i></a>
								</div>`;
                        },
                    },
                ]
            });

            dataTable.on('draw', function () {
            }).on('init.dt', function () {
                $(config.ui.table).css('width', (parseInt($(config.ui.table).css('width')) - 10) + 'px');
            });
        }
    }

    var selected_date;
    var public = {

        init: function () {
            $(document).ready(function () {
                _private.init();
                public.events();
            });
        },
        events: function () {

            //get task data with modal form
            $(document).on('click', '#view_task_data', function (e) {
                e.stopPropagation();
                e.preventDefault();
                let task_id = $(this).data('id');
                $.get('tasks/ajax_get_modal_form/' + task_id, function (response) {
                    $('#modal_container').html(response);
                    $('#modal_' + task_id).modal('show');
                })
            });
            //change status event
            $(document).on('change', '.task_status_change', function (e) {
                e.stopPropagation();
                e.preventDefault();
                let id = $(this).parents('.modal.fade.overflow').data('task_id');
                if ($(this).val() == 'new') {
                    $('#modal_' + id).find('.new_status_desc').css('display', 'none');

                } else {
                    $('#modal_' + id).find('.new_status_desc').css('display', 'block');
                }
                return false;
            });
            //submit form event
            $(document).on('click', '.submit', function (e) {
                e.stopPropagation();
                e.preventDefault();
                var id = $(this).parents('.modal.fade.overflow').data('task_id');

                if ($(this).text() == 'Close') {
                    $('#modal_' + id).find('.task_status_change').val('new');
                    $('#modal_' + id).find('.new_status_desc').css('display', 'none');
                    $('#modal_' + id).find('.submit').css('display', 'none');
                } else {
                    let status = $('#modal_' + id).find('.task_status_change').val();
                    let text = $('#modal_' + id).find('.new_status_desc').val();
                    if (text == '') {
                        alert('Description is required!');
                        return false;
                    } else {
                        $.post(baseUrl + 'tasks/ajax_change_status', {id: id, status: status, text: text}, function (resp) {
                            if (resp.status == 'ok')
                                location.reload();
                            else
                                alert(resp.msg);
                        }, 'json');
                    }
                }
                return false;
            });
        }
    };
    public.init();
    return public;
}();
