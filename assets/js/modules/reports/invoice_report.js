let InvoiceReport = function(){
    let config = {
        ui:{
            pagination: '.pagination',
            select2:[
                {
                    selector:'#filter_estimator',
                    options:{width: '100%'},
                    onchange:false,
                    values:false,
                },
                {
                    selector:'#filter_reference',
                    options:{width: '100%'},
                    onchange:false,
                    values:false,
                }
            ],
            date_format: '#date-format',
            btn_go: '.btn-go',
            invoice_table: '.invoice_table',
            invoice_report: '.invoice-report',
            filter_container: '.filter-container',
            active_pagination: '.pagination .active > a',
            filter_estimator: '#filter_estimator',
            filter_reference: '#filter_reference',
            filter_classes: '.filter-classes',
            report_range: '.report-range',
            filter: '.filter',
            table_container: '.table-container',
            export_csv: '.export-csv'
        },
        events:{
            pagination: '.pagination a',
            filter: '.filter',
            btn_go: '.btn-go',
            report_range: '.report-range',
            export_csv: '.export-csv'
        }
    };
    let table;
    let _private = {
        init: function(){
            $('.datepicker').datepicker({format: $(config.ui.date_format).val()});
            _private.init_DataTable();
        },
        init_DataTable: function(){
            table = $(config.ui.invoice_table).DataTable( {
                "processing": true,
                responsive: true,
                searching: false,
                paging:  false,
                "bInfo" : false,
                columnDefs: [
                    { targets: [1, 2, 4], orderable: true},
                    { targets: '_all', orderable: false }
                ],
                order: [[2, 'desc']],
                // fixedHeader: true,
                colReorder: true,
                rowReorder: true,
                fixedHeader: {
                    header: true,
                    footer: false
                },
                // serverSide: true,
                // ajax: _private.ajax_set_table
                // scrollX: true
            });
            // new $.fn.dataTable.FixedHeader( table)
            // table.ajax.reload();
        },
        pagination: function (e) {
            e.preventDefault();
            let filter = {
                page : $(this).data('ci-pagination-page')
            };
            _private.ajax_set_table(filter);
        },
        filter: function () {
            if($(config.ui.filter_container).is(':visible')) {
                // $(this).find('i').removeClass('fa-angle-right').addClass('fa-angle-left');
                $(config.ui.filter_container).animate({'right': '-50%'}, 300, function () {
                    $(config.ui.filter_container).hide();
                });
            }
            else {
                // $(this).find('i').removeClass('fa-angle-left').addClass('fa-angle-right');
                $(config.ui.filter_container).show();
                $(config.ui.filter_container).animate({'right': 0}, 300);
            }
        },
        btn_go: function (ev, picker) {
            let selected = [];
            $(config.ui.filter_classes + ' input:checked').each(function() {
                selected.push($(this).attr('id'));
            });
            let filter = {
                page : 1,
                estimator: $(config.ui.filter_estimator).select2('val'),
                reference: $(config.ui.filter_reference).select2('val'),
                classes: selected
            };
            if(picker){
                let start = moment(picker.startDate).format($(config.ui.date_format).val());
                let end = moment(picker.endDate).format($(config.ui.date_format).val());
                filter.date_from = start.toString();
                filter.date_to = end.toString();
            } else {
                let start = moment($(config.ui.report_range).data('daterangepicker').startDate).format($(config.ui.date_format).val());
                let end = moment($(config.ui.report_range).data('daterangepicker').endDate).format($(config.ui.date_format).val());
                filter.date_from = start.toString();
                filter.date_to = end.toString();
                $(config.ui.filter).click()
            }
            _private.ajax_set_table(filter);
        },
        ajax_set_table: function (filter) {
            $.ajax({
                method: "POST",
                url: base_url + 'reports/getInvoiceReportTable',
                dataType: 'JSON',
                data: filter
            }).done(function (response) {
                if(response.status === 'ok') {
                    if (typeof response.table != 'undefined') {
                        table.destroy();
                        $(config.ui.table_container).replaceWith(response.table);
                        _private.init_DataTable();
                        $(config.ui.pagination).replaceWith(response.links);
                        $(config.ui.invoice_report).scrollTop(0);

                    }
                } else {
                    if (typeof response.error != 'undefined' && typeof response.error.message != 'undefined'){
                        errorMessage(response.error.message);
                    }
                }
            });
        },
        export_csv: function(){
            let titles = [];
            let data = [];

            $(config.ui.invoice_table + ' th').each(function() {
                titles.push($(this).text());
            });
            data.push(titles);

            $(config.ui.invoice_table + ' tbody tr').each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    row.push($(this).text());
                });
                data.push(row);
            });

            if(data.length > 1 && data[1] && data[1][0] !== 'No data available in table'){
                let name = 'report';
                let start = moment($(config.ui.report_range).data('daterangepicker').startDate).format($(config.ui.date_format).val());
                let end = moment($(config.ui.report_range).data('daterangepicker').endDate).format($(config.ui.date_format).val());
                if(start.toString())
                    name += '_' + start.toString();
                if(end.toString())
                    name += '_' + end.toString();

                name = name.replace(/ /g,"_");
                name += '.csv';
                _private.exportToCsv(name, data);
            } else {
                errorMessage('No data available in table');
            }
        },
        exportToCsv: function(filename, rows) {
            let processRow = function (row) {
                let finalVal = '';
                for (let j = 0; j < row.length; j++) {
                    let innerValue = row[j] === null ? '' : row[j].toString();
                    if (row[j] instanceof Date) {
                        innerValue = row[j].toLocaleString();
                    };
                    let result = innerValue.replace(/"/g, '""');
                    if (result.search(/("|,|\n)/g) >= 0)
                        result = '"' + result + '"';
                    if (j > 0)
                        finalVal += ',';
                    finalVal += result;
                }
                return finalVal + '\n';
            };

            let csvFile = '';
            for (let i = 0; i < rows.length; i++) {
                csvFile += processRow(rows[i]);
            }

            let blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
            if (navigator.msSaveBlob) { // IE 10+
                navigator.msSaveBlob(blob, filename);
            } else {
                let link = document.createElement("a");
                if (link.download !== undefined) { // feature detection
                    // Browsers that support HTML5 download attribute
                    let url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", filename);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        }
    };

    let public = {

        init: function () {
            $(document).ready(function () {
                public.events();
                Common.init_select2(config.ui.select2);
                _private.init();
                Common.initDateRangePicker(config.ui.report_range);
            });

        },
        events:function(){
            $(document).delegate(config.events.pagination, 'click', _private.pagination);
            $(document).delegate(config.events.filter, 'click', _private.filter);
            $(document).delegate(config.events.btn_go, 'click', _private.btn_go);
            $(document).delegate(config.events.export_csv, 'click', _private.export_csv);
            $(config.events.report_range).on('apply.daterangepicker', _private.btn_go);

        }
    }

    public.init();
    return public;
}();