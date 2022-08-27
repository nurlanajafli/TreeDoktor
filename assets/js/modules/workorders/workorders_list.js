var WorkordersList = function(){
    var config = {
        ui:{
            table:'#trees-table',
            tags_select:'.js-tags-select2'
        },

        events:{

        },
        route:{
            clients: base_url + 'workorders',
        },
        templates:{
            tag_container:'<span class="tag-item-container {0}" style="cursor: pointer">' +
                '<a class="btn btn-xs btn-{3} m-r-xs m-b-xs js-tag-name" data-tag-id="{1}"  data-tag-name="{2}">{2}</a>' +
                '</span>',
            //more_button:'<div class="text-right"><span class="js-expand-more" style="text-decoration: underline; cursor: pointer">more</span></div>',
            more_button:'<a class="js-expand-more" data-toggle="class:show"><span class="text">...</span><span class="text-active">...</span></a>',
            row_tags_container:'<div class="form-group p-left-0 p-right-0 tags-container m-n">{0}{1}</div>',
        },

    }


    var _private = {
        init:function(){
            _private.init_wo_list();
        },
        tags_tamplate: function (row) {

            if (row.client.tags.length !== 0) {
                var active_tag = $.map($(config.ui.tags_select).select2('val'), function (i) {
                    return parseInt(i);
                });
                var tags_length = 0;
                row.client.tags = row.client.tags.sort(function (a, b) {
                    return active_tag.indexOf(b.tag_id) - active_tag.indexOf(a.tag_id)
                });
                var tagsListTpl = row.client.tags.map((tag, index) => {

                    tags_length+=tag.name.length;
                    var class_name = (tags_length > tagsExpandLimit && index!=0) ? 'd-none js-hidden-before-expand' : 'd-inline-flex';

                    return config.templates.tag_container.sprintf(class_name, tag.tag_id, tag.name, (active_tag.indexOf(tag.tag_id)!=-1)?'warning active':'success').trim();
                }).join('');

                var more_button = tags_length > tagsExpandLimit ? config.templates.more_button: '';
                return config.templates.row_tags_container.sprintf(tagsListTpl, more_button);
            }

            return '';
        },
        init_wo_list:function () {
            dataTable = $('#trees-table').DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "paging": true,
                "pageLength": 50,
                'sDom': "<'row'>t<'datatable-footer'<'col-sm-6'i><'col-sm-6'p>>",
                "createdRow": function( row, data) {
                    row.setAttribute('data-trees-di', data.trees_id)
                },
                "order": [[3, "desc"]],
                ajax: {
                    method: 'get',
                    url: base_url + 'workorders',
                    "data": function(d) {
                        if (typeof clientFilter === 'object' && clientFilter !== null) {
                            Object.assign(d, clientFilter);
                        }
                    },

                    dataSrc: function (response) {
                        data = response.data.original;

                        var text = response.total;
                        if(response.active_status.workorders_count!=undefined)
                            text = response.active_status.workorders_count;

                        if(text != response.recordsFiltered)
                            text += ' (' + response.recordsFiltered + ')';

                        var dr_status_name = (response.active_status.wo_status_name!=undefined)?response.active_status.wo_status_name:'All';
                        var dr_status_id = (response.active_status.wo_status_id!=undefined)?response.active_status.wo_status_id:'-1';

                        $('.filter-container').find('.count-wo').text(text);
                        $('.wo_status_filter #statusMapper').attr('href', baseUrl + 'workorders/workorders_mapper/' + dr_status_id);
                        $('.wo_status_filter .wo_status_btn').html(dr_status_name + ' '+ text + "<span class='caret' style='margin-left:5px;'></span>");


                        $('.wo_status_filter #wo_status').html('');
                        var obj = $('.wo_status_filter #wo_status');

                        $.each($(response.statuses), function(key, val){
                            var activeClass = '';
                            if(val.wo_status_id == dr_status_id)
                                activeClass = "active";
                            $(obj).append('<li class="'+ activeClass +'"><a href="#tab'+ val.wo_status_id +'" data-toggle="tab" data-statusname="'+ val.wo_status_id +'" style="padding-right: 6px;padding-left: 6px;">'+ val.wo_status_name +' <span class="badge bg-info">'+ val.workorders_count +'</span></a></li>');
                        });
                        return data;
                    },
                },
                columnDefs: [
                    { "name": "client_name",   "targets": 0 },
                    { "name": "tags",   "targets": 1, "orderable": false },
                    { "name": "workorder_no",   "targets": 2 },
                    { "name": "date_created",   "targets": 3 },
                    { "name": "firstname",   "targets": 6 },
                    { "name": "total_with_tax",   "targets": 7 },

                    {
                        "targets": [ 4, 5, 8, 9],
                        "orderable": false
                    },

                    {
                        'targets': 0,
                        'render': function (data, type, row, meta) {
                            return `
											<a href="${base_url + row.client_id}">${row.client_name.trim()}</a>
									`;
                        }
                    },

                    {
                        'targets': 1,
                        'render': function (data, type, row, meta) {
                            return _private.tags_tamplate(row);
                        },
                        "order": false
                    },
                    {
                        'targets': 2,
                        'render': function (data, type, row, meta) {
                            return row.workorder_no;
                        }
                    },

                    {
                        'targets': 3,
                        'render': function (data, type, row, meta) {
                            return row.date_created;
                        }
                    },

                    {
                        'targets': 4,
                        'render': function (data, type, row, meta) {
                            return `${row.lead_address + ', ' + row.lead_city}`;
                        },
                        "order": false
                    },
                    {
                        'targets': 5,
                        'render': function (data, type, row, meta) {
                            let ccPhoneHtml = '';
                            if (row.cc_phone) {
                                ccPhoneHtml = `
										<a href="#" class="${row.cc_phone_config_status ? 'text-danger' : 'createCall'}" data-client-id="${row.client_id}" data-number="${row.cc_phone.substr(0, 10)}">
												${row.cc_phone_view}
										</a>
								`;
                            }

                            return ccPhoneHtml;
                        },
                        "order": false
                    },
                    {
                        'targets': 6,
                        'render': function (data, type, row, meta) {
                            return row.user_firstname + ' ' + row.user_lastname;
                        }
                    },
                    {
                        'targets': 7,
                        "name": "total_estimate_price",
                        'render': function (data, type, row, meta) {
                            return row.hasOwnProperty('total_with_tax') ? Common.money(row.total_with_tax) : '';
                        },
                    },
                    {
                        'targets': 8,
                        'render': function (data, type, row, meta) {
                            var text = '';
                            if(row.wo_deposit_taken_by !== null)
                                text = row.wo_deposit_taken_by;
                            return `<td width="580px"><textarea class="form-control wo-note w-100" data-wo_id="` + row.id +`" placeholder="Ctrl+Enter" name="wo_office_notes">` + row.wo_office_notes + `</textarea></td>`;
                            /*return row.hasOwnProperty('wo_deposit_taken_by') ? `
							   <td width="580px"><textarea class="form-control wo-note" data-wo_id="` + row.id +`" placeholder="Ctrl+Enter">` + text + `</textarea></td>
							` : '';*/
                        },
                        "order": false
                    },
                    {
                        'targets': 9,
                        'render': function (data, type, row, meta) {
                            return  `<td width="70" class="print-btns">
										<a href="${base_url + row.workorder_no}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i></a>
							   		</td>
								`;
                        },
                        "order": false
                    }

                ]
            })

            dataTable.on( 'draw', function () {
                /*$('.progress>[data-toggle="tooltip"]').tooltip();*/
            }).on( 'init.dt', function () {
                $('#trees-table').css('width', (parseInt($('#trees-table').css('width')) - 10) + 'px');
            })




        },
    };

    var public = {

        init:function(){
            $(document).ready(function(){
                public.events();
                _private.init();
            });
        },
        events:function(){
            $(document).on('click', '.js-expand-more', function() {
                $(this).closest('td').find('.js-hidden-before-expand').toggleClass(function(){
                    return $(this).is('.d-none') ? 'd-inline-flex' : 'd-none';
                });
            });

        },
    };

    public.init();
    return public;
}();
