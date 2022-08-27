<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/business_intelligence/emails_stat/emails_stat.css'); ?>">
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.tooltip.min.js'); ?>"></script>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Emails Statistics</li>
	</ul>
    <section class="panel panel-default">
        <header class="panel-heading">Emails Statistics
            <div class="pull-right" style="margin-top:-0px;">
                <form id="dates" method="post" action="<?php echo base_url('business_intelligence/emails_stat'); ?>" class="input-append m-t-xs">
                    <label>
                        <input name="from" class="datepicker form-control date-input-client from" type="text" readonly
                               value="<?php if ($from): echo getDateTimeWithDate($from, 'Y-m-d');
                               else: echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
                    </label>
                    —
                    <label>
                        <input name="to" class="datepicker form-control date-input-client to" type="text" readonly
                               value="<?php if ($to): echo getDateTimeWithDate($to, 'Y-m-d');
                               else: echo date(getDateFormat()); endif; ?>">
                    </label>
                    <input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
                </form>
            </div>
            <div class="clear"></div>
        </header>
		<div class="row m-md">
			<div style="height:300px; display:table;" class="text-center col-md-5 col-sm-4 col-xs-12">
				<?php if(!isset($data) || !$data): ?>
				    <div style="display:table-cell; vertical-align: middle; color: #000; font-size: 50px;">NO DATA</div>
				<?php else: ?>
						
                    <div style="display: table-row;">
                        <table class="table m-b-none b-a" >
                            <thead>
                                <tr>
                                    <th class="bg-light b-r b-b text-center">Status</th>
                                    <th class="bg-light b-r b-b text-center">Count Emails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($statuses as $k => $v) :?>
                                    <tr>
                                        <td class="b-r b-b">
                                            <?php echo ucfirst(str_replace('_', ' ', $v)); ?>
                                        </td>
                                        <td class="b-r b-b">
                                            <div class="btn-group clear" style="display: inline-block;overflow: visible;">
                                                <?php echo $data[$v]['count']; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
<!--                                    <tr>-->
<!--                                        <td class="b-r b-b"><strong>Total:</strong></td>-->
<!--                                        <td class="b-r b-b"><strong>--><?php //echo $all; ?><!--</strong></td>-->
<!--                                    </tr>-->
                            </tbody>
                        </table>
                    </div>
					
				<?php endif; ?>
			</div>
			<div class="col-md-7 col-sm-8 col-xs-12">
				<div class="plots text-center">
					<div id="flotcontainer" class="plotContainer"></div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="row m-md emails">
			<?php foreach($statuses as $k => $v): ?>
				<?php if($data[$v]['letters'] && !empty($data[$v]['letters'])): ?>
				<div class="col-sm-6 col-lg-4 col-xs-12 p-top-30 p-bottom-10px <?php echo $v; ?>">
                    <div class="emails-block-heading b-a r text-center l-h-2x"
                         style="<?php if(isset($color[$k])): ?>background:<?php echo $color[$k]; ?>; color:#fff;<?php else: ?>background:#f1f1f1; color:#000;<?php endif; ?>"
                    >
                        <?php echo ucfirst(str_replace('_', ' ', $v)); ?> (<?php echo $data[$v]['count_actual']; ?>)
                    </div>
                    <div class="emails-block-content b-l b-r">
                        <div class="panel-group accordion-emails" id="accordionEmails_<?php echo $v; ?>" role="tablist" aria-multiselectable="true">
                            <?php $this->load->view('email_row', ['data' => $data, 'v' => $v]); ?>
                        </div>
                    </div>
                    <div class="emails-block-footer text-center b-a r l-h-2x">
                        <?php if($data[$v]['more']) : ?>
                            <a href="#" class="getMore getMore<?php echo $v; ?>"
                               data-num="<?php echo $limit; ?>"
                               data-limit="<?php echo $limit; ?>"
                               data-type="<?php echo $v; ?>"
                            >
                                Show More
                            </a>
                        <?php endif; ?>
                    </div>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
    </section>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat(); ?>" />
</section>
<script>
	const letters = <?php echo json_encode($data); ?>;
	const statuses = <?php echo json_encode($statuses); ?>;
	const colors = <?php echo json_encode($color); ?>;
	const data = [];
	const ticks = [];
	const all = <?php echo $all; ?>;

	$(document).ready(function () {
        $('body').tooltip({
            selector: '[data-toggle="tooltip"]'
        });

		// $('.datepicker').datepicker({format: 'yyyy-mm-dd'});
        $('.datepicker').datepicker({format: $('#php-variable').val()});
		$.each(statuses, function (key, val) {
			let labelTitle =  val[0].toUpperCase() + val.slice(1);
            labelTitle = labelTitle.replace('_', ' ');

			data.push({
                label: labelTitle  + ' (' + letters[val]['count'] + ')',
                data: [[key, letters[val]['count']]],
                color: colors[key],
                bars: {
                    show: true,
                    fill: true,
                    lineWidth: 1,
                    fillColor: colors[key]
                },
			});

			ticks.push([key, labelTitle]);
		});

		const options = {
            series: {
                shadowSize: 1,
            },
            bars: {
                align: "center",
                barWidth: 0.8
            },
            legend: {
                show: true,
            },
            grid: {
                hoverable: true,
                clickable: false,
                borderWidth: 0
            },

            xaxis: {
                ticks: ticks,
                align: "center",
            },
            yaxis: {
                tickFormatter: function (v, axis) {
                    return parseInt(v);
                }
            },
            tooltip: true,
            tooltipOpts: {
                content: "%s",
                defaultTheme: false,
                shifts: {
                    x: 0,
                    y: 20
                }
            },
        };

		// $.plot($("#flotcontainer"), data1, options);
		$.plot($("#flotcontainer"), data, options);

		$(".emails").on("click", ".getMore", function() {
			const num = $(this).data('num');
			const limit = $(this).data('limit');
			const type = $(this).data('type');
			const from = $('.from').val();
			const to = $('.to').val();

			$.ajax({
				global: false,
				method: "POST",
				data: { num: num, limit: limit, type: type, from: from, to: to },
				url: base_url + "business_intelligence/ajax_more_emails",
				dataType: 'json',
				success: function (response){
					if (response.status !== 'ok') {
						errorMessage('Sorry. No more emails');
						$('.' + type).find('.getMore' + type).remove();
					} else {
						$('.' + type).find('.accordion-emails').append(response.blocks);
						if (response.more) {
                            $('.' + type).find('.getMore' + type).data('num', response.offset);
                        } else {
                            $('.' + type).find('.getMore' + type).remove();
                        }
					}
				}
			});

			return false;
		});
	});
</script>
<?php $this->load->view('includes/footer'); ?>
