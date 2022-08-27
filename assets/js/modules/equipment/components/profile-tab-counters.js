/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 */
"use strict";

exports.scripts = [];

exports.modules = [
    '/assets/vendors/diez/js/modules/datepicker.js',
    '/assets/vendors/diez/js/modules/table.js',
    '/assets/vendors/diez/js/modules/form.js',
    '/assets/vendors/diez/js/modules/filter.js',
    //'/assets/vendors/diez/js/modules/fileinput.js'
];

exports.App = {
    routes: {
        list: '/equipment/counters/ajax_get_counters',
        row: '/equipment/counters/ajax_get_counter',
        create: '/equipment/counters/ajax_create_counter',
        edit: '/equipment/counters/ajax_update_counter',
        delete: '/equipment/counters/ajax_delete_counter'
    },
    init: function (app) {
        this.app = app;
        if ($(this.app).data('equipmentId')) {
            this.where = {eq_id: $(this.app).data('equipmentId')};
        } else {
            this.where = {};
        }
        this.idField = 'counter_id';
        this.editFormSelector = '#edit';
        this.noPaginate = false;
        if ($(this.app).data('noPaginate')) {
            this.noPaginate = true;
        }
        this.rowTemplate = Handlebars.compile($('#profile-tab-counter-row-template').html());
        this.editTemplate = Handlebars.compile($('#profile-tab-counter-edit-template').html());
        this.paginatorTemplate = Handlebars.compile($('#paginator-template').html());
        this.defaultSort = ['counter_date', 'desc'];
        this.filter = D.helper.url.getQuery('filter');
        this.query = D.helper.url.getQuery('query');
        //this.bindEvents();
        this.getList();
    },
    refresh: function (app) {
        this.getList();
    },
    bindEvents: function () {
        let self = this;
        // $.initialize('[data-toggle="tooltip"]', function () {
        //     $(this).tooltip();
        // }, {target: this.app});
        // $.initialize('.action-refresh', function () {
        //     $(this).on('click', self.refresh.bind(self));
        // }, {target: this.app});
        // $.initialize('.action-create', function () {
        //     $(this).on('click', self.eventCreate.bind(self));
        // }, {target: this.app});
        // $.initialize('.action-edit', function () {
        //     $(this).on('click', self.eventEdit.bind(self));
        // }, {target: this.app});
        // $.initialize('.action-delete', function () {
        //     $(this).on('click', self.eventDelete.bind(self));
        // }, {target: this.app});
        // $.initialize('input#filter', function () {
        //     $(this).on('keyup', function (event) {
        //         if (event.keyCode === 13) {
        //             event.preventDefault();
        //             $(self.app).find('.action-filter').click();
        //         }
        //     });
        // }, {target: this.app});
        // $.initialize('.action-filter', function () {
        //     $(this).on('click', self.eventFilter.bind(self));
        // }, {target: this.app});
        // $.initialize('a.page-link', function () {
        //     $(this).on('click', self.eventPage.bind(self));
        // }, {target: this.app});
        // $.initialize('#edit', function () {
        //     $(this).on('show.bs.modal', function (event) {
        //         //
        //     });
        //     $(this).on('hide.bs.modal', function (event) {
        //         if (event.target == this) {
        //             $(this).find('form').removeClass('edit-form create-form');
        //             $(this).find('.modal-body').empty()
        //         }
        //     });
        // }, {target: this.app});
        // $.initialize('form.edit-form', function () {
        //     $(this).unbind('submit').on('submit', self.eventSubmitEdit.bind(self));
        // }, {target: this.app});
        // $.initialize('form.create-form', function () {
        //     $(this).unbind('submit').on('submit', self.eventSubmitCreate.bind(self));
        // }, {target: this.app});
        // $.initialize('.table th.sortable', function () {
        //     $(this).unbind('click').on('click', self.eventSort.bind(self));
        // }, {target: this.app});
        // $.initialize('.datepicker', function () {
        //     if (!$(this).val()) {
        //         var now = new Date();
        //         $(this).val(now.format(DATE_FORMAT));
        //     }
        //     $(this).datepicker({
        //         format: DATE_FORMAT,
        //         todayBtn: true,
        //         todayHighlight: true
        //     });
        // }, {target: this.app});
    },
    afterEventOnHideModal: function (prev, event, el) {
        if (event.target == el) {
            if (typeof EquipmentProfileApp !== "undefined") {
                EquipmentProfileApp.refresh();
            }
        }
    },
    // eventPage: function (event, page = null) {
    //     if (event !== null) {
    //         event.preventDefault();
    //         page = event.currentTarget.dataset.pageNum;
    //     }
    //     if (page === null)
    //         return;
    //     var newUrl = D.helper.url.setPage(page);
    //     if (history.pushState) {
    //         window.history.pushState("Page " + page, document.title, newUrl);
    //     } else {
    //         document.location.href = newUrl;
    //     }
    //     this.getList();
    // },
    // eventEdit: function (event) {
    //     let id = event.currentTarget.dataset.id;
    //     let name = event.currentTarget.dataset.name;
    //     this.getRow(id, this.renderEdit.bind(this));
    //     let editEl = $(this.app).find('#edit');
    //     $(editEl).find('form').addClass('edit-form');
    //     $(editEl).find('.panel-heading').text('Edit "' + name + '"');
    //     $(editEl).modal('show');
    // },
    // eventSubmitEdit: function (event) {
    //     event.preventDefault();
    //     let id = $(event.currentTarget).find('input[name=' + this.idField + ']').val();
    //     let modal = $(event.currentTarget).parents('.modal-dialog').first();
    //     D.helper.ajax(
    //         D.helper.url.route(this.routes.edit),
    //         $(event.currentTarget).serialize(),
    //         ['edit', id, event.currentTarget],
    //         this.callbackSubmitSuccess.bind(this),
    //         this.callbackSubmitError.bind(this),
    //         "POST",
    //         modal
    //     );
    // },
    // eventCreate: function (event) {
    //     this.renderEdit(this.where);
    //     let editEl = $(this.app).find('#edit');
    //     $(editEl).find('form').addClass('create-form');
    //     $(editEl).find('.panel-heading').text('Create');
    //     $(editEl).modal('show');
    // },
    // eventSubmitCreate: function (event) {
    //     event.preventDefault();
    //     let modal = $(event.currentTarget).parents('.modal-dialog').first();
    //     D.helper.ajax(
    //         D.helper.url.route(this.routes.create),
    //         $(event.currentTarget).serialize(),
    //         ['create', null, event.currentTarget],
    //         this.callbackSubmitSuccess.bind(this),
    //         this.callbackSubmitError.bind(this),
    //         "POST",
    //         modal
    //     );
    // },
    // eventDelete: function (event) {
    //     let id = event.currentTarget.dataset.id;
    //     let name = event.currentTarget.dataset.name;
    //     if (confirm('Are you sure delete "' + name + '"?')) {
    //         D.helper.ajax(
    //             D.helper.url.route(this.routes.delete),
    //             {[this.idField]: id},
    //             ['delete', id, event.currentTarget],
    //             this.callbackSubmitSuccess.bind(this),
    //             this.callbackSubmitError.bind(this),
    //             "POST",
    //             this.app
    //         );
    //     }
    // },
    // eventSort: function (event) {
    //     let col = event.currentTarget.dataset.sort;
    //     let orderEl = $(event.currentTarget).find('i');
    //     let newOrder = 'asc';
    //     if (orderEl.length !== 0) {
    //         newOrder = $(orderEl).hasClass('fa-caret-up') ? 'desc' : 'asc';
    //     }
    //     this.sort = [col, newOrder];
    //     $(this.app).find('.table th.sortable i').remove();
    //     $(event.currentTarget).append('<i class="fa ' + (newOrder === "asc" ? 'fa-caret-up' : 'fa-caret-down') + '"></i>');
    //     var newUrl = D.helper.url.setQuery({sort: col, order: newOrder});
    //     if (history.pushState) {
    //         window.history.pushState("order " + col, document.title, newUrl);
    //         this.eventPage(null, 1);
    //     } else {
    //         newUrl = D.helper.url.setPage(1, newUrl);
    //         document.location.href = newUrl;
    //     }
    // },
    // eventFilter: function (event, filter = null) {
    //     if (event !== null) {
    //         event.preventDefault();
    //         filter = $(this.app).find('input#filter').val();
    //         this.filter = filter;
    //     }
    //     var newUrl = D.helper.url.setQuery({filter: filter});
    //     if (history.pushState) {
    //         window.history.pushState("Filter " + filter, document.title, newUrl);
    //         this.eventPage(null, 1);
    //     } else {
    //         newUrl = D.helper.url.setPage(1, newUrl);
    //         document.location.href = newUrl;
    //     }
    // },
    // callbackSubmitSuccess: function (response, action, id, form) {
    //     if (action === "delete") {
    //         $(this.app).find('.data-list #row_' + id).remove();
    //         //this.eventPage(null, 1);
    //         return;
    //     }
    //     if (action === 'create') {
    //         this.eventPage(null, 1);
    //     } else {
    //         this.refresh();
    //     }
    //     //this.renderRow(response.group, id);
    //     $(this.app).find('#edit').modal('hide');
    // },
    // callbackSubmitError: function (error, errors, action, id, form) {
    //     if (action === "delete") {
    //         return;
    //     }
    //     if (error !== null) {
    //         $(form).find('.feedback')
    //             .addClass('has-error')
    //             .find('.help-block')
    //             .text("Error: " + error);
    //     }
    //     if (errors !== null) {
    //         $.each(errors, function (field, value) {
    //             $(form).find('#' + field + '-error')
    //                 .text(value)
    //                 .parent()
    //                 .addClass('has-error');
    //         });
    //     }
    // },
    // getList: function (successCallback = null) {
    //     if (successCallback === null)
    //         successCallback = this.renderRows.bind(this);
    //     this.getOrdering();
    //     D.helper.ajax(
    //         D.helper.url.route(this.routes.list),
    //         {
    //             filter: this.filter,
    //             page: D.helper.url.getPage(),
    //             sort: this.sort,
    //             where: this.where
    //         },
    //         [],
    //         successCallback,
    //         false,
    //         "POST",
    //         this.app
    //     )
    // },
    // getRow: function (id, successCallback = null) {
    //     if (successCallback === null)
    //         successCallback = this.renderRow.bind(this);
    //     D.helper.ajax(
    //         D.helper.url.route(this.routes.row),
    //         {id: id},
    //         [id],
    //         successCallback,
    //         false,
    //         "POST",
    //         this.app
    //     )
    // },
    // getOrdering: function () {
    //     var sort = D.helper.url.getQuery('sort');
    //     if (sort === undefined)
    //         sort = this.defaultSort[0];
    //     var order = D.helper.url.getQuery('order');
    //     if (order === undefined)
    //         order = this.defaultSort[1];
    //     this.sort = [sort, order];
    //     $(this.app).find('.table th.sortable i').remove();
    //     $(this.app).find('.table th.sortable[data-sort=' + sort + ']').append('<i class="fa ' + (order === "asc" ? 'fa-caret-up' : 'fa-caret-down') + '"></i>');
    // },
    // renderRows: function (response) {
    //     $(this.app).find('.data-list').empty();
    //     if (response.data !== false) {
    //         var self = this;
    //         $.each(response.data, function (key, row) {
    //             self.renderRow(row);
    //         });
    //     }
    //     this.renderPaginator(response);
    // },
    // renderRow: function (row, id = null) {
    //     if (id !== null) {
    //         $(this.app).find('.data-list #row_' + id).replaceWith(this.rowTemplate(row));
    //     } else {
    //         $(this.app).find('.data-list').append(this.rowTemplate(row));
    //     }
    // },
    // renderPaginator: function (paginator) {
    //     $(this.app).find('.paginator').html(this.paginatorTemplate(paginator));
    // },
    // renderEdit: function (response) {
    //     $(this.app).find('#edit .modal-body').html(this.editTemplate(response))
    // }
};