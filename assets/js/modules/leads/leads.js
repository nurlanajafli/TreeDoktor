var Leads = function () {
	var approval = (window.location.pathname.split('/').pop() === 'for_approval') ? true : false;
    var config = {

        ui: {
            /*
            change_user_type_confirmation:'#change-user-type-confirmation',
            yearly_rate:'#txtyearlyrate',
            hourly_rate:'#txthourlyrate',
            */
			table:'#trees-table',
            files_preview_template: "#files-preview-template",
            get_edit_form: '#get-edit-form',
            get_appointment_sms_form: '#get-appointment-sms-form',
            edit_modal_id: 'lead-details-modal'
        },

        events: {
            edit_modal: '.editLeadModal',
            appointment_sms_modal: '#appointment-sms-modal',
			showAddressBlock: '.show-address-block',
			editLeadForm: '.editLeadForm',
            /*
            worker_type_change: '.wType',
            change_user_type_yes:'#change-user-type-yes',
            */
        },

		route:{
			leads: base_url + 'leads',
		},

		templates:{
			tag_container:'<span class="tag-item-container {0}" style="cursor: pointer">' +
				'<a class="btn btn-xs btn-{3} m-r-xs m-b-xs js-tag-name" data-tag-id="{1}"  data-tag-name="{2}">{2}</a>' +
				'</span>',

			more_button:'<a class="js-expand-more" data-toggle="class:show"><span class="text">...</span><span class="text-active">...</span></a>',
			preloader: '<div class="text-center"><img src="' + baseUrl + 'assets/img/preloader.gif"></div>',
		}
    }

    const _private = {
        init: function () {
			_private.init_lead_list();
			_private.initReffsSelects();
			_private.initDatepicker();
			_private.initLeadDropZone();
			_private.initEditLeadSelects();
		},

		initDatepicker: function () {
        	if ($('.datepicker').length) {
        		const dateVal = $('#php-variable').val() || 'yyyy-mm-dd';
				$('.datepicker').datepicker({ format: dateVal });
			}
		},

		initLeadDropZone: function () {
        	if ($('.dropzone-lead:not(.dz-clickable)').length) {
				window.initDropzone($('.dropzone-lead'));
			}
		},

		initEditLeadSelects: function () {
        	if ($('.editLeadForm').length && itemsForSelect2) {
        		const leadForm = $('.editLeadForm');

				if (itemsForSelect2.services) {
					initSelect2(leadForm.find("input.est_services"), itemsForSelect2.services, 'Select Services');
				}
				if(itemsForSelect2.products) {
					initSelect2(leadForm.find("input.est_products"), itemsForSelect2.products, 'Select Products');
				}
				if(itemsForSelect2.bundles) {
					initSelect2(leadForm.find("input.est_bundles"), itemsForSelect2.bundles, 'Select Bundles');
				}
			}
		},

		init_lead_list: function () {
        	if (!$('#trees-table').length && !$('#my_trees-table').length) {
        		return false;
			}

			const dataTable = $('#trees-table').DataTable({
				"processing": true,
				"serverSide": true,
				"responsive": true,
				"paging": true,
				"pageLength": 50,
				'sDom': "<'row'>t<'datatable-footer'<'col-sm-6'i><'col-sm-6'p>>",
				"createdRow": function( row, data) {
					let lead_date = moment(data.lead_date_created);
					let now_date = moment();
					let date_diff = now_date.diff(lead_date, 'days');
					let lead_priority = data.lead_priority;
					let lead_priority_display = 'success';

					if (date_diff < 7) {
						lead_priority_display = 'error';
					}
					if (lead_priority === 'Priority' || lead_priority === 'Emergency') {
						lead_priority_display = 'warning';
					}

					row.setAttribute('data-lead-id', data.lead_id);
					row.setAttribute('class', lead_priority_display);
					row.setAttribute('data-trees-di', 'class=warning');
				},
				ajax: {
					method: 'get',
					url: base_url + 'leads',
					"data": {'approval' : approval},
					dataSrc: function (response) {
						if (response.data.original.length === 0) {
							$('#leads_section').hide();
						} else {
							$('#leads_section').show();
						}
						return response.data.original;
					},
				},
				columnDefs: [
					{ "name": "lead_id", "targets": 0, "visible": false },
					{ "name": "client_name", "targets": 1 },
					{ "name": "lead_date_created", "targets": 2 },
					{ "name": "lead_created_by", "targets": 4 },
					{ "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], "orderable": false },
					{
						'targets': 0,
						'render': function (data, type, row, meta) {
							return row.lead_id;
						}
					},
					{
						'targets': 1,
						'render': function (data, type, row, meta) {
							return `<a href="${base_url + row.client_id}">${row.client_name}</a>`;
						}
					},
					{
						'targets': 2,
						'render': function (data, type, row, meta) {
							return moment(row.lead_date_created).format(MOMENT_DATE_FORMAT);
						}
					},
					{
						'targets': 3,
						'render': function (data, type, row, meta) {
							return  `${$.trim(row.lead_address) + ', ' + $.trim(row.lead_city)}`;
						}
					},
					{
						'targets': 4,
						'render': function (data, type, row, meta) {
							let result = '—';
							if (row.lead_createdBy !== null && row.lead_createdBy !== undefined) {
								result = row.lead_createdBy;
							}
							return `${result}`;
						}
					},
					{
						'targets': 5,
						'render': function (data, type, row, meta) {
							let result = '—';
							if (row.firstname != null && row.lastname != null) {
								result = row.firstname + ', ' + row.lastname;
							}
							return `${result}`;
						}
					},
					{
						'targets': 6,
						'render': function (data, type, row, meta) {
							if(row.lead_assigned_date != null && row.lead_assigned_date)
							 	return moment(row.lead_assigned_date).format(MOMENT_DATE_FORMAT);
							return row.lead_assigned_date;
						}
					},
					{
						'targets': 7,
						'render': function (data, type, row, meta) {
							return row.lead_status_name;
						}
					},
					{
						'targets': 8,
						'render': function (data, type, row, meta) {
							let date = row.lead_postpone_date !== '0000-00-00' ? moment(row.lead_postpone_date).format(MOMENT_DATE_FORMAT)  : '';
							return `
								<a href="#" data-name="lead_postpone" data-value="` + date + `"  data-placement="left" data-type="date" data-pk="` + row.lead_id + `" class="lead_postpone" title="Postpone Date" data-url="` + base_url + 'leads/ajax_postpone_lead' + `">${date}</a>
							`;
						}
					},
					{
						'targets': 9,
						'render': function (data, type, row, meta) {
							return row.lead_priority;
						}
					},
					{
						'targets': 10,
						"name": "client_id",
						'render': function (data, type, row, meta) {
							return `<div class="text-center"></div>
									<a href="#lead-details-modal" data-id="` + row.lead_id + `" role="button" class="btn btn-xs btn-default" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-eye"></i></a>
									<a href="` + base_url + row.lead_no + `" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i></a>
								  	<a href="` + base_url + `estimates/new_estimate/` + row.lead_id + `" class="btn btn-xs btn-default"><i class="fa fa-leaf"></i></a>
								</div>`;
						},
					},
				]
			});

			const myDataTable = $('#my_trees-table').DataTable({
				"processing": true,
				"serverSide": true,
				"responsive": true,
				"paging": false,
				//"pageLength": 50,
				'sDom': "<'row'>t<'datatable-footer'<'col-sm-6'i><'col-sm-6'p>>",
				"createdRow": function( row, data) {
					let lead_date = moment(data.lead_date_created);
					let now_date = moment();
					let date_diff = now_date.diff(lead_date, 'days');
					let lead_priority = data.lead_priority;
					let lead_priority_display = 'success';

					if (date_diff < 7) {
						lead_priority_display = 'error';
					}
					if (lead_priority === 'Priority' || lead_priority === 'Emergency') {
						lead_priority_display = 'warning';
					}

					row.setAttribute('data-lead-id', data.lead_id);
					row.setAttribute('class', lead_priority_display);
					row.setAttribute('data-trees-di', 'class=warning');
				},
				ajax: {
					method: 'get',
					url: base_url + 'leads?my_leads=1',
					"data": {'approval' : approval},
					dataSrc: function (response) {
						if (response.data.original.length === 0) {
							$('#my_leads_section').hide();
						} else {
							$('#my_leads_section').show();
						}
						return response.data.original;
					},
				},
				columnDefs: [
					{ "name": "lead_id", "targets": 0, "visible": false },
					{ "name": "client_name", "targets": 1 },
					{ "name": "lead_date_created", "targets": 2 },
					{ "name": "lead_created_by", "targets": 4 },
					{ "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], "orderable": false },
					{
						'targets': 0,
						'render': function (data, type, row, meta) {
							return row.lead_id;
						}
					},
					{
						'targets': 1,
						'render': function (data, type, row, meta) {
							return `<a href="${base_url + row.client_id}">${row.client_name}</a>`;
						}
					},
					{
						'targets': 2,
						'render': function (data, type, row, meta) {
							return row.lead_date_created;
						}
					},
					{
						'targets': 3,
						'render': function (data, type, row, meta) {
							return  `${$.trim(row.lead_address) + ', ' + $.trim(row.lead_city)}`;
						}
					},
					{
						'targets': 4,
						'render': function (data, type, row, meta) {
							let result = '—';
							if (row.lead_createdBy !== null && row.lead_createdBy !== undefined) {
								result = row.lead_createdBy;
							}
							return `${result}`;
						}
					},
					{
						'targets': 5,
						'render': function (data, type, row, meta) {
							return  `${row.firstname + ', ' + row.lastname}`;
						}
					},
					{
						'targets': 6,
						'render': function (data, type, row, meta) {
							return row.lead_assigned_date;
						}
					},
					{
						'targets': 7,
						'render': function (data, type, row, meta) {
							return row.lead_status_name;
						}
					},
					{
						'targets': 8,
						'render': function (data, type, row, meta) {
							let date = row.lead_postpone_date !== '0000-00-00' ? row.lead_postpone_date  : '';
							return `
								<a href="#" data-name="lead_postpone" data-value="` + date + `"  data-placement="left" data-type="date" data-pk="` + row.lead_id + `" class="lead_postpone" title="Postpone Date" data-url="` + base_url + 'leads/ajax_postpone_lead' + `">${date}</a>
							`;
						}
					},
					{
						'targets': 9,
						'render': function (data, type, row, meta) {
							return row.lead_priority;
						}
					},
					{
						'targets': 10,
						"name": "client_id",
						'render': function (data, type, row, meta) {
							return `<div class="text-center"></div>
									<a href="#lead-details-modal" data-id="` + row.lead_id + `" role="button" class="btn btn-xs btn-default" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-eye"></i></a>
									<a href="` + base_url + row.lead_no + `" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i></a>
								  	<a href="` + base_url + `estimates/new_estimate/` + row.lead_id + `" class="btn btn-xs btn-default"><i class="fa fa-leaf"></i></a>
								</div>`;
						},
					},
				]
			})

			dataTable.on( 'draw', function () {
				_private.initEditable();
			}).on( 'init.dt', function () {
				$('#trees-table').css('width', (parseInt($('#trees-table').css('width')) - 10) + 'px');
			});

			myDataTable.on( 'draw', function () {
				_private.initEditable();
			});
        },

        get_edit_modal: function (e) {
            if (e.target.id === config.ui.edit_modal_id) {
				const lead_id = e.relatedTarget.dataset.id;
                $(config.ui.get_edit_form).find('[name="id"]').val(lead_id);
                $(config.ui.get_edit_form).trigger("submit");
            }
            if (e.target.id !== '' && $("#" + e.target.id + ' #reffered').val()) {
                $("#" + e.target.id + ' #reffered').change();
            }
        },

        get_appointment_sms_modal: function (e) {
            const task_id = e.relatedTarget.dataset.id;
            $(config.ui.get_appointment_sms_form).find('[name="id"]').val(task_id);
            if (e.relatedTarget.dataset.smsId !== undefined) {
                $(config.ui.get_appointment_sms_form).find('[name="sms-id"]').remove();
                $(config.ui.get_appointment_sms_form).append('<input type="hidden" name="sms-id" value="' + e.relatedTarget.dataset.smsId + '">');
            }
            $(config.ui.get_appointment_sms_form).trigger("submit");
        },

		initEditable: function () {
        	if (!$('.lead_postpone').length) {
        		return false;
			}

			$('.lead_postpone').editable({
				savenochange: true,
				success: function(response) {
					response = $.parseJSON(response);
					if (response.status === 'ok') {
						if (response.date != null) {
							$('tr[data-lead-id="' + response.lead_id + '"]').find('.lead_postpone').text(response.date);
						} else {
							$('tr[data-lead-id="' + response.lead_id + '"]').find('.lead_postpone').text('-');
						}
						$('tr[data-lead-id="' + response.lead_id + '"]').find('.lead_postpone').attr('data-value', response.date);
						_private.initEditable();

						$(".custom-menu").hide(100);
						$('.editable-cancel').click();
						selected = [];
					}
					setTimeout(function(){
						$('.lead_postpone').editable('setValue', null);
					}, 50);
				},
				display: function(value) {
					//var obj = $(this);
					//$(this).text('Postpone Date');
				}
			});
		},

		showAddressBlock: function () {
			$(this).parent().find('.another-address-checkbox').trigger('click');
		},

		editLeadFormSubmit: function () {
			if ($.parseJSON($('[name="set_lead_status"] option:selected').attr('data-status-info')).length
					&& !$('[name="set_lead_reason_status"]').val()) {
				$('[name="set_lead_reason_status"]').parents('tr:first').addClass('has-error');
				return false;
			} else {
				return true;
			}
		},

		initReffsSelects: function () {
        	if (!$("#reff_id").length) {
        		return false;
			}

			$("#reff_id").select2({
				minimumInputLength: 3,
				multiple: true,
				placeholder: "Search refferal",
				ajax: {
					url: baseUrl + "clients/ajax_get_reff",
					params: {
						type: 'POST',
						global: false,
					},
					dataType: 'json',
					quietMillis: 500,
					data: function (term, page) {
						return {
							name: term,
							trigger: $('#reffered').val()
						};
					},
					results: function (data, page) {
						return { results: data.items };
					},
					cache: true
				},
			});

        	if (typeof refferdOption !== 'undefined') {
				$("#reff_id").select2("data", refferedOption);
			}

			$('#reff_id').select2("enable", false);

			if ($('#reffered').val()) {
				$('#reffered').change();
			}
		},
    }

    let selected_date;
    const public = {
        init: function () {
            $(document).ready(function () {
                public.events();
                _private.init();
            });
        },

        events: function () {
			$('.upload-lead-files input[name="files"]').change(function (e) {
				const container = $(this).data('container');
				let files = [];
				if (e.target.files.length) {
					files = $.map(e.target.files, function (item) {
						return {"name": item.name};
					});
				}

				const renderView = {
					template_id: config.ui.files_preview_template,
					view_container_id: container,
					data: files
				};
				Common.renderView(renderView);
			});

            $(config.events.edit_modal).on('show.bs.modal', _private.get_edit_modal);
            $(config.events.edit_modal).on('hide.bs.modal', public.set_preloader);

            $(config.events.appointment_sms_modal).on('show.bs.modal', _private.get_appointment_sms_modal);

            $(document).on('click', config.events.showAddressBlock, _private.showAddressBlock);
            $(document).on('submit', config.events.editLeadForm, _private.editLeadFormSubmit);
        },

        set_edit_modal: function (response) {
            if (response.status !== 'ok' || !itemsForSelect2) {
                alert("Lead is not valid!");
                $(config.events.edit_modal).modal('hide');
                return false;
            }

            $(config.events.edit_modal + ' .panel-heading').html(response.heading);
            $(config.events.edit_modal + ' .modal-body').html(response.html);

            $(config.events.edit_modal + ' .datepicker').datepicker({format: $('#php-variable').val()});

            _private.initEditLeadSelects();

            $.each($(config.events.edit_modal + ' .dropzone-lead'), function (key, val) {
                window.initDropzone($(val));
            });
            Common.init_autocompleate(config.events.edit_modal);
			_private.initReffsSelects();
        },

        set_appointment_sms_modal: function (response) {
            if (!response.status) {
                alert(response.message);
                $(config.events.appointment_sms_modal + ' #appointment-sms-phone').val(response.phone);
                $(config.events.appointment_sms_modal + ' #appointment-sms-text').val(response.body);
                return false;
            }
            if (response.phone) {
                $(config.events.appointment_sms_modal + ' #appointment-sms-phone').val(response.phone);
            }
            $(config.events.appointment_sms_modal + ' #appointment-sms-text').val(response.body);
            //$(config.events.appointment_sms_modal+' #appointment-sms-client-id').val(response.client_id);
        },

        appointment_sms_callback: function (response) {
            if (response.status === 'ok') {
                $(config.events.appointment_sms_modal).modal('hide');
                successMessage('Sms was sent successfully');
            } else {
				if (response.messages) {
					$.each(response.messages, function(key, message) {
						errorMessage(message || 'Unexpected error.');
					});
				} else {
					errorMessage(response.message || 'Unexpected error.');
				}
            }
        },

        set_preloader: function (e) {
            if (e.target.id === config.ui.edit_modal_id) {
                $(config.events.edit_modal + ' .modal-body').html(config.templates.preloader);
            }
        },

		reasonFunction: function (id) {
			const reasons = $('.leadForm-' + id + ' [name="set_lead_status"] option:selected').attr('data-status-info');
			if ($.parseJSON(reasons).length) {
				let select = '<select name="set_lead_reason_status" class="form-control reason-no-go" >';
				$.each($.parseJSON(reasons), function(key, val) {
					select += '<option value="'+ val.reason_id +'">'+ val.reason_name +'</option>';
				});
				select += '</select>';
				$('.leadForm-' + id + ' .reason-no-go').replaceWith(select).val(null);
				$('.leadForm-' + id + ' .reason-no-go').closest('.reason-block').show();
			} else {
				$('.leadForm-' + id + ' .reason-no-go').closest('.reason-block').hide();
				$('.leadForm-' + id + ' [name="set_lead_reason_status"]').val(null).attr('disabled', 'disabled');
			}
		},
    }

    public.init();
    return public;
}();

function remove_lead_file(lead_id, name, client_id, exists = 0) {
    if (typeof lead_id == "undefined" || typeof name == "undefined" || !confirm('Are you sure you want to delete the selected file?'))
        return false;

    $.ajax({
        url: baseUrl + 'leads/deleteFile',
        data: {lead_id: lead_id, name: name, client_id: client_id},
        method: "POST",
        global: false,
        success: function (resp) {
            if (resp.type == 'ok') {
                let currentDropzonePreviewImage = $('a[data-lead_file="' + name + '"]').closest('.dz-preview ');

                if (currentDropzonePreviewImage.length > 0) {
                    currentDropzonePreviewImage.remove();
                    return true
                }

                if (exists == 1) {
                    $('a[data-lead_file="' + name + '"]').remove();
                }
                return true;
            } else {
                alert(resp.message);
            }
            return false;
        },
        dataType: 'json'
    });
    return false;
}
