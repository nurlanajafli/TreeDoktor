var ClientsList = function(){
	var config = {
		ui:{
			table:'#trees-table',
			tags_select:'.js-tags-select2'
		},

		events:{

		},
		route:{
			clients: base_url + 'clients',
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

		dataTable: {},

		init:function(){
			_private.init_clients_list();
		},

		init_clients_list:function () {
			_private.dataTable = $('#trees-table').DataTable({
				"processing": true,
				"serverSide": true,
				"responsive": true,
				"paging": true,
				"pageLength": 50,
				'sDom': "<'row'>t<'datatable-footer'<'col-sm-6'i><'col-sm-6'p>>",
				"createdRow": function( row, data) {
					row.setAttribute('data-trees-di', data.trees_id)
				},
				"order": [[0, "desc"]],
				ajax: {
					method: 'get',
					url: base_url + 'clients',
					"data": function(d) {
						if (typeof clientFilter === 'object' && clientFilter !== null) {
							Object.assign(d, clientFilter);
						}
					},
					dataSrc: function (response) {
						data = response.data.original;

						return data;
					},
				},
				columnDefs: [
					{ "name": "client_id",   "targets": 0, "visible": false },
					{ "name": "client_type",   "targets": 1 },
					{ "name": "client_name",   "targets": 2 },

					{
						"targets": [ 3, 4, 5, 6, 7],
						"orderable": false
					},

					{
						'targets': 0,
						'render': function (data, type, row, meta) {
							return row.client_id;
						}
					},

					{
						'targets': 1,
						'render': function (data, type, row, meta) {
							let icon = '';
							switch (row.client_type) {
								case '1':
									icon = 'icon_residential.png';
									break;
								case '2':
									icon = 'icon_corp.png';
									break;
								default:
									icon = 'icon_municipal.png';
							}

							return `
											<img height="17px" src="${base_url + 'assets/vendors/notebook/images/' + icon}">
									`;
						}
					},

					{
						'targets': 2,
						'render': function (data, type, row, meta) {
							return `
											<a href="${base_url + row.client_id}">${row.client_name.trim()}</a>
									`;
						}
					},

					{
						'targets': 3,
						'render': function (data, type, row, meta) {
							return _private.tags_tamplate(row);
						},
						"order": false
					},
					{
						'targets': 4,
						'render': function (data, type, row, meta) {
							let ccPhoneHtml = '';
							if (row.cc_phone) {
								ccPhoneHtml = `
													<a href="#" class="${row.cc_phone_config_status === 1 ? 'text-danger' : 'createCall'}" data-client-id="${row.client_id}" data-number="${row.cc_phone.substr(0, 10)}">
															${row.cc_phone_masked}
													</a>
											`;
							}

							return ccPhoneHtml;
						}
					},
					{
						'targets': 5,
						'render': function (data, type, row, meta) {
							return `${row.client_address + ', ' + row.client_city}`;
						}
					},
					{
						'targets': 6,
						"name": "total_estimate_price",
						'render': function (data, type, row, meta) {
							return row.hasOwnProperty('total_estimate_price') ? `
							   <div class="text-center">` + Common.money(row.total_estimate_price) + `</div>
									<div class="progress progress-sm progress-striped m-b-none active">
									<div class="progress-bar progress-bar-success" data-html="true" data-placement="left" data-toggle="tooltip" data-original-title="Confirmed<br>` + Common.money(row.total_confirmed_estimates_amount) + ` (` + Math.round(row.total_confirmed_estimates_amount / row.total_estimate_price * 100) + `%)" style="cursor: pointer; width: ` + (row.total_confirmed_estimates_amount / row.total_estimate_price * 100).toFixed(2) + `%;"></div>
								  	<div class="progress-bar progress-bar-warning" data-html="true" data-placement="left" data-toggle="tooltip" data-original-title="Pending<br>` + Common.money(row.total_pending_estimates_amount) + ` (` + Math.round(row.total_pending_estimates_amount / row.total_estimate_price * 100) + `%)" style="cursor: pointer; width: ` + (row.total_pending_estimates_amount / row.total_estimate_price * 100).toFixed(2) + `%;"></div>
								  	<div class="progress-bar progress-bar-danger" data-html="true" data-placement="left" data-toggle="tooltip" data-original-title="Declined<br>` + Common.money(row.total_declined_estimates_amount) + ` (` + Math.round(row.total_declined_estimates_amount / row.total_estimate_price * 100) + `%)" style="cursor: pointer; width: ` + (row.total_declined_estimates_amount / row.total_estimate_price * 100).toFixed(2) + `%;"></div>
								</div>
							` : '';
						},
					},
					{
						'targets': 7,
						'data': 'qb_html'
					}

				]
			})

			_private.dataTable.on( 'draw', function () {
				initQbLogPopover();
				$('.progress>[data-toggle="tooltip"]').tooltip();
			}).on( 'init.dt', function () {
				$('#trees-table').css('width', (parseInt($('#trees-table').css('width')) - 10) + 'px');
			})

			$(document).on('click', '.js-tag-name', function() {
				$('#client-search-form').get(0).reset();

				let tagId = $(this).attr('data-tag-id');
				let tagName = $(this).attr('data-tag-name');

				if($(this).hasClass('active')){
					window.clientFilter = {};
					$(config.ui.tags_select).select2('val', 0);
				}
				else{
					window.clientFilter = {
						'search_tags': tagId
					}
					$(config.ui.tags_select).select2('data', {
						id: tagId,
						text: tagName
					});
				}

				_private.dataTable.ajax.reload();
			});


		},

		tags_tamplate: function (row) {

			if (row.tags.length !== 0) {
				var active_tag = $.map($(config.ui.tags_select).select2('val'), function (i) {
					return parseInt(i);
				});
				var tags_length = 0;
				row.tags = row.tags.sort(function (a, b) {
					return active_tag.indexOf(b.tag_id) - active_tag.indexOf(a.tag_id)
				});
				var tagsListTpl = row.tags.map((tag, index) => {
					tags_length+=tag.name.length;
					var class_name = (tags_length > tagsExpandLimit && index!=0) ? 'd-none js-hidden-before-expand' : 'd-inline-flex';
					return config.templates.tag_container.sprintf(class_name, tag.tag_id, tag.name, (active_tag.indexOf(tag.tag_id)!=-1)?'warning active':'success').trim();
				}).join('');

				var more_button = tags_length > tagsExpandLimit ? config.templates.more_button: '';
				return config.templates.row_tags_container.sprintf(tagsListTpl, more_button);
			}

			return '';
		}
	};

	var public = {

		init:function(){
			$(document).ready(function(){
				public.events();
				_private.init();
			});
		},

		serialize: function(obj, prefix) {
			var str = [],
				p;
			for (p in obj) {
				if (obj.hasOwnProperty(p)) {
					var k = prefix ? prefix + "[" + p + "]" : p,
						v = obj[p];
					str.push((v !== null && typeof v === "object") ?
						public.serialize(v, k) :
						encodeURIComponent(k) + "=" + encodeURIComponent(v));
				}
			}
			return str.join("&");
		},

		events:function(){
			$(document).on('click', '.js-expand-more', function() {
				$(this).closest('td').find('.js-hidden-before-expand').toggleClass(function(){
					return $(this).is('.d-none') ? 'd-inline-flex' : 'd-none';
				});
			});
			$(document).on('click', '#csvExport', function() {
				window.location = base_url + 'clients?' + public.serialize(public.dataTable().ajax.params()) + '&csv=1';
			});
		},

		dataTable: function() {
			return _private.dataTable;
		}
	};

	public.init();
	return public;
}();


/*
$(document).ready(function() {
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
		"order": [[0, "asc"]],
		ajax: {
			method: 'get',
			url: base_url + 'clients',
			"data": function(d) {
				if (typeof clientFilter === 'object' && clientFilter !== null) {
					Object.assign(d, clientFilter);
				}
			},
			dataSrc: function (response) {
				data = response.data.original;
				// $('.js-total-trees').text(response.totalTrees);

				return data;
			},
		},
		columnDefs: [
			{ "name": "client_type",   "targets": 0 },
			{ "name": "client_name",   "targets": 1 },
			{
				"targets": [ 2, 3, 4, 5, 6, 7, 8],
				"orderable": false
			},
			{
				'targets': 0,
				'render': function (data, type, row, meta) {
					let icon = '';
					switch (row.client_type) {
						case '1':
							icon = 'icon_residential.png';
							break;
						case '2':
							icon = 'icon_corp.png';
							break;
						default:
							icon = 'icon_municipal.png';
					}

					return `
											<img height="17px" src="${base_url + 'assets/vendors/notebook/images/' + icon}">
									`;
				}
			},

			{
				'targets': 1,
				'render': function (data, type, row, meta) {
					return `
											<a href="${base_url + row.client_id}">${row.client_name.trim()}</a>
									`;
				}
			},

			{
				'targets': 2,
				'render': function (data, type, row, meta) {
					let tagsHtml = '';
					if (row.tags.length !== 0) {
						tagsHtml = `
													<div class="form-group p-left-0 p-right-0 tags-container">
															${row.tags.map((tag, index) => {
							return `
																				 <span class="tag-item-container ${index + 1 > tagsExpandLimit ? 'd-none js-hidden-before-expand' : 'd-inline-flex'}" style="cursor: pointer">
																							<a class="tag tag-link js-tag-name" data-tag-id=${tag.tag_id}  data-tag-name=${tag.name}>${tag.name}</a>
																					</span>
																	`.trim()
						}).join('')}
	
															${row.tags.length > tagsExpandLimit ? `
																	<div class="text-right">
																			<span class="js-expand-more" style="text-decoration: underline; cursor: pointer">
																					more
																			</span>
																	</div>
															 ` : ''}
													</div>
											`;
					}

					return tagsHtml;
				}
			},
			{
				'targets': 3,
				'render': function (data, type, row, meta) {
					let ccPhoneHtml = '';
					if (row.cc_phone) {
						ccPhoneHtml = `
													<a href="#" class="${row.cc_phone_config_status === 1 ? 'text-danger' : 'createCall'}" data-client-id="${row.client_id}" data-number="${row.cc_phone.substr(0, 10)}">
															${row.cc_phone}
													</a>
											`;
					}

					return ccPhoneHtml;
				}
			},
			{
				'targets': 4,
				'render': function (data, type, row, meta) {
					return `${row.client_address + ', ' + row.client_city}`;
				}
			},
			{
				'targets': 5,
				'data': 'client_main_intersection'
			},
			{
				'targets': 6,
				'render': function (data, type, row, meta) {
					return row.hasOwnProperty('estimator') ? row.estimator : '---';
				}
			},
			{
				'targets': 7,
				'render': function (data, type, row, meta) {
						return row.hasOwnProperty('total_estimate_price') ? '$'+ parseFloat(+ row.total_estimate_price).toFixed(2) : '';
				}
			},
			{
				'targets': 8,
				'data': 'qb_html'
			},
		]
	})

	dataTable.on( 'draw', function () {
		initQbLogPopover();
	} );


	$(document).on('click', '.js-tag-name', function() {
		$('#client-search-form').get(0).reset();

		let tagId = $(this).attr('data-tag-id');
		let tagName = $(this).attr('data-tag-name');

		window.clientFilter = {
			'search_tags': tagId
		}

		$('.js-tags-select2').select2('data', {
			id: tagId,
			text: tagName
		});

		dataTable.ajax.reload();
	})

	$(document).on('click', '.js-expand-more', function() {
		$(this).closest('td').find('.js-hidden-before-expand').toggleClass(function(){
			return $(this).is('.d-none') ? 'd-inline-flex' : 'd-none';
		});
	});
});*/
