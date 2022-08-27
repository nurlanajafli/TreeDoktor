//var color = Chart.helpers.color; 
var COLORS = [
	'#4dc9f6',
	'#f67019',
	'#f53794',
	'#537bc4',
	'#acc236',
	'#166a8f',
	'#00a950',
	'#58595b',
	'#8549ba'
];

var color = Chart.helpers.color;

(function(global) {

    var showZeroPlugin = {
        beforeRender  : function (chartInstance) {
            var datasets = chartInstance.config.data.datasets;

            for (var i = 0; i < datasets.length; i++) {
                var meta = datasets[i]._meta;
                // It counts up every time you change something on the chart so
                // this is a way to get the info on whichever index it's at
                var metaData = meta[Object.keys(meta)[0]];
                var bars = metaData.data;

                for (var j = 0; j < bars.length; j++) {
                    var model = bars[j]._model;

                    if (metaData.type === "horizontalBar" && model.base === model.x) {
                        model.x = model.base + 2;
                    }
                    if (model.base === model.y) {
                        model.y = model.base - 2;
                    }
                    if ((model.base - model.y)<3) {
                        model.y = model.base - 3;
                    }
                }
            }
        }
    };
    Chart.pluginService.register(showZeroPlugin);

    var COLORS = [
        '#4dc9f6',
        '#f67019',
        '#f53794',
        '#537bc4',
        '#acc236',
        '#166a8f',
        '#00a950',
        '#58595b',
        '#8549ba'
    ];

    var Samples = global.Samples || (global.Samples = {});
    var Color = global.Color;

    Samples.utils = {
        // Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
        srand: function(seed) {
            this._seed = seed;
        },

        rand: function(min, max) {
            var seed = this._seed;
            min = min === undefined ? 0 : min;
            max = max === undefined ? 1 : max;
            this._seed = (seed * 9301 + 49297) % 233280;
            return min + (this._seed / 233280) * (max - min);
        },

        numbers: function(config) {
            var cfg = config || {};
            var min = cfg.min || 0;
            var max = cfg.max || 1;
            var from = cfg.from || [];
            var count = cfg.count || 8;
            var decimals = cfg.decimals || 8;
            var continuity = cfg.continuity || 1;
            var dfactor = Math.pow(10, decimals) || 0;
            var data = [];
            var i, value;

            for (i = 0; i < count; ++i) {
                value = (from[i] || 0) + this.rand(min, max);
                if (this.rand() <= continuity) {
                    data.push(Math.round(dfactor * value) / dfactor);
                } else {
                    data.push(null);
                }
            }

            return data;
        },

        labels: function(config) {
            var cfg = config || {};
            var min = cfg.min || 0;
            var max = cfg.max || 100;
            var count = cfg.count || 8;
            var step = (max - min) / count;
            var decimals = cfg.decimals || 8;
            var dfactor = Math.pow(10, decimals) || 0;
            var prefix = cfg.prefix || '';
            var values = [];
            var i;

            for (i = min; i < max; i += step) {
                values.push(prefix + Math.round(dfactor * i) / dfactor);
            }

            return values;
        },

        months: function(config) {
            var cfg = config || {};
            var count = cfg.count || 12;
            var section = cfg.section;
            var values = [];
            var i, value;

            for (i = 0; i < count; ++i) {
                value = MONTHS[Math.ceil(i) % 12];
                values.push(value.substring(0, section));
            }

            return values;
        },

        color: function(index) {
            return COLORS[index % COLORS.length];
        },

        transparentize: function(color, opacity) {
            var alpha = opacity === undefined ? 0.5 : 1 - opacity;
            return Color(color).alpha(alpha).rgbString();
        }
    };
    window.randomScalingFactor = function() {
        return Math.round(Samples.utils.rand(0, 10050));
    };
	
    Samples.utils.srand(Date.now());
	
}(this));

var Sales = function () {
    var config = {

        ui:{
            chartCanvas: "flot-bar",
            salesRows: '#salesRows',
            loadMoreRow: '#loadMoreRow',
            loadMoreLink: '#loadMoreLink',
            salesRowsTable: '#salesRowsTable',
            searchForm: '#search',
            offsetValue: '#offsetValue',
            chartTitle: 'Sales Graph',
            countRow: '#count',
            showHideServices: '.showHideServices',
            enDisableServices: '.enDisableServices',
            exportPdf: '.exportPdf',
            pdfForm: '#pdfForm'
        },

        events:{

        },

        route:{

        },

        templates:{

        },

        chartColors: {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(201, 203, 207)'
        },
        tagsSelect2: [
            {
                selector:'.js-tags-select2',
                options:{
                    data: window.filterTags,
                    tags: true,
                    placeholder: 'Tags',
                    separator: '|',
                    onchange: function(obj) {
                        console.log(obj);
                    }
                }
            }, {
                selector:'[name="search_status[]"], [name="search_service_type[]"], [name="search_workorder_status[]"], [name="search_invoice_status[]"]',
                options: {
                    containerCssClass: "p-n b-0"
                }
            }
        ],
    };

    var _private = {
        init:function(){
            this.drawChart();
        },
        offset: 0,
        chart: {},

        getHexColor: function(number) {
            if(number.length < 7) {
                number = number * 1234567;
            }
            return "#"+((number)>>>0).toString(16).slice(-6);
        },

        drawChart: function(response) {
            if(self.chart instanceof Chart) {
                self.chart.destroy();
            }

            if(response === undefined || response.stats === undefined || !response.stats.length) {
                return false;
            }
            var stats = response.stats;
            var data = [];

            $.each(stats, function(key, val) {
                data.push({
                    label: val.service_name,
                    backgroundColor: color(_private.getHexColor(val.service_id)).alpha(0.7).rgbString(),
                    borderColor: _private.getHexColor(val.service_id),
                    borderWidth: 1,
                    data: [
                        val.total 
                    ],
                    count: val.count,
                    minHeight: 5
                });
            });

            var barChartData = {
                labels: [['Total: ' + response.count_all, "Total For Services: " + Common.money(response.sum)/*, "Total Estimates: " + Common.money(response.total_estimates)*/]],
                datasets: data
            };

            self.chart = new Chart(document.getElementById(config.ui.chartCanvas).getContext("2d"), {
                type: 'bar',
                data:barChartData,
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: config.ui.chartTitle
                    },
                    tooltips: {
                        callbacks: {
                            title: function () {
                                return;
                            },
                            label: function(tooltipItem, data) {
                                var label = ' ';
                                label += data.datasets[tooltipItem.datasetIndex].label || '';

                                if (label) {
                                    label += ': ';
                                }
                                label += Common.money(tooltipItem.yLabel) + ' (' + data.datasets[tooltipItem.datasetIndex].count + ')';
                                return label;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                callback: function(value, index, values) {
                                    return Common.money(value);
                                }
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontStyle: "bold"
                            }
                        }],
                    }
                }
            });
        },

        appendLoadMore: function() {
            $(config.ui.salesRowsTable).append(`<tr id="loadMoreRow"><td colspan="6" class="text-center"><a href="#" id="loadMoreLink">Load More</a></td></tr>`);
        },

        renderHtmlResponse: function(html, count, sum) {
            $(config.ui.salesRows).replaceWith(html);
            var rows = $('#salesRows');
            if (!rows.hasClass('resize-height')) {
                rows.addClass('resize-height');
            }
        }
    };

    var public = {
        init:function(){
            $(document).ready(function(){
                public.events();
                Common.init_select2(config.tagsSelect2);
                _private.init();
            });
        },

        isNumber: function (n) { return /^-?[\d.]+(?:e-?\d+)?$/.test(n); },

        events:function(){
            $(document).on('click', config.ui.showHideServices, function () {
				chart.options.legend.display = !chart.options.legend.display;
				chart.update();
			});
            $(document).on('click', config.ui.enDisableServices, function () {
				
				chart.data.datasets.forEach(function(ds) {
					ds.hidden = !ds.hidden;
				});
				chart.update();
			});
            $(document).on('click', config.ui.loadMoreLink, function () {
                $(config.ui.offsetValue).val(_private.offset);
                $(config.ui.searchForm).submit();
                return false;
            });
            $(document).on('click', config.ui.exportPdf, function () {
                var data = $(config.ui.searchForm).serializeArray();
                $.each($(data), function(key, val){
                   $(config.ui.pdfForm).append('<input type="hidden" name="'+ val['name'] +'" value="'+ val['value'] +'">');
                });
                $(config.ui.pdfForm).submit();
            });
        },

        render: function(response) {
            $(config.ui.loadMoreRow).remove();
            if(response.data.stats) {
                _private.drawChart(response.data);
                $('#flot-bar').css('height', '55vh');
            }
            if(response.data.offset) {
                $(config.ui.salesRowsTable).append(response.data.html);
            } else {
                _private.renderHtmlResponse(response.data.html, response.data.count_all, response.data.sum);
            }
            $(config.ui.offsetValue).val(0);
            _private.offset = response.data.offset + response.data.limit;
            if(response.data.limit === response.data.count) {
                _private.appendLoadMore();
            }
        }
    };
    public.init();
    return public;
}();
$(document).ready(function () {
    Common.initDateRangePicker();
	$(document).on('click', '.dropdown-menu.animated.fadeInDown.searchFrom', function (e) {
		e.stopPropagation();
	});

    $('#searchEst').on('click', function () {
        var canvas = $('#flot-bar');
        if (!canvas.hasClass('resize-height')) {
            canvas.addClass('resize-height');
        }
    })

	$('[name="search_client_type"]').on('change', function(){
		var value = $('#search').find('[name="search_by"]').val();
		var clientVal = $('#search').find('[name="search_client_type"]').val();
		if(!value && !clientVal)
			$('#search').find('#searchEst').attr('disabled', 'disabled');
		else
			$('#search').find('#searchEst').removeAttr('disabled');
	});
		
	$('[name="search_by"]').on('change', function(){
		var value = $(this).val();
		var clientVal = $('#search').find('[name="search_client_type"]').val();
		if(value) {
			$('#estimatorSelect').removeClass('hide');
		} else {
			$('#estimatorSelect').addClass('hide');
		}
		$('#search').find('.table:not(:first)').addClass('hide');
		$('#search').find('.table:not(:first)').find('input, select').val('');
		$('#search').find('.' + value + 'Table').removeClass('hide');
		var dates = moment().format(MOMENT_DATE_FORMAT) + ' - ' + moment().format(MOMENT_DATE_FORMAT);
		$('#search').find('.' + value + 'Table').find('.reportrange').find('span').text(dates);
		$('#search').find('.' + value + 'Table').find('.reportrange').find('input').val(dates);
		if(!value && !clientVal)
			$('#search').find('#searchEst').attr('disabled', 'disabled');
		else
			$('#search').find('#searchEst').removeAttr('disabled');
			
	});
	$('.reset').on('click', function(){
		var obj = $(this);
		$(obj).parents('td:first').find('select').val('').change();
		if($(obj).hasClass('mainReset'))
			$(obj).parents('form').find(".table:not(:first)").addClass('hide');
		
	});
	$('.resetAll').on('click', function(){
		$.each($('#search input, select, textarea'), function(key, val){
			$(val).val('');
		});
		$.each($('#search table:not(:first)'), function(key, val){
			$(val).addClass('hide');
		});
		$('#search').find('#searchEst').attr('disabled', 'disabled');
		$('#search').find('#estimatorSelect').addClass('hide');
	});
	$('.resetDate').on('click', function(){
		var obj = $(this);
		$(obj).parent().find('.reportrange').find('span').text('');
		$(obj).parent().find('.reportrange').find('input').val('');
	});
});
