<?php
use Carbon\Carbon;
?>
<link href="<?php echo base_url(); ?>/assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet">

        <section class="panel panel-default p-n">

            <!-- Workorder Details Header -->
            <header class="panel-heading">Workorder Events</header>
            <!-- Data Display -->
            <div class="table-responsive">
                <table class="table table-striped small">
                    <thead>
                    <tr>
                        <th style="width: 90px;">Date</th>
                        <th style="width: 155px;">Crew</th>
                        <th>Note</th>
                        <th style="min-width: 100px;">Amount</th>
                        <th class="text-center">AMH</th>
                        <th class="text-center">PMH</th>
                        <th class="text-center" style="width: 120px;">Crew Time<br>(On Site / On Travel)</th>
                        <th class="text-center" style="min-width: 100px;">Act. <?php echo get_currency(); ?></th>
                        <th class="text-center" style="min-width: 100px;">Plan <?php echo get_currency(); ?></th>
                        <th class="text-center" style="min-width: 100px;">Expenses</th>
                        <th class="text-center" style="min-width: 100px;">CM</th>
                        <th class="text-center" style="min-width: 100px;">DM</th>
                        <th class="text-center" width="300px">Comment</th>
                        <th >Close Team</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($events && !empty($events)) : ?>
                        <?php
                            $full_time  = 0;
                            $total_time_summ = 0;
                            $total_mnrs_summ = 0;
                            $total_expenses_summ = 0;
                            $planned_mnrs_price_summ = 0;
                        ?>
                 
                        <?php $totalForEvents = 0; ?>
                        <?php $this->load->view('workorders/profile_modal_event_info'); ?>
                        <?php foreach($events as $event) : ?>
                            <?php
                                $service_style = '';
                                if(isset($team_id) && $team_id && $team_id == $event['team_id'])
                                    $service_style = 'background-color: rgba(180, 193, 76, 0.35)!important;';
                                $teamAmount = 0;
                                if($event['team_man_hours'])
                                    $teamAmount = round(((($event['team_amount'] - $event['event_damage']) / $event['team_man_hours'])*100)/100, 2);
                             ?>
                        <tr data-id="<?php echo $event['id'];?>">
                            <td style="<?php echo $service_style; ?>">
                                <?php $event['full_time'] = $full_time;?>
                                <?php /*
                                <a href="#eventInfo-report-modal" class="btn btn-default btn-xs" data-id="<?php echo $event['id']; ?>" data-team_id="<?php echo $event['team_id']; ?>" data-wo_id="<?php echo $event['wo_id']; ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                                    <?php echo getDateTimeWithTimestamp($event['event_start']) . '<br>' . getTimeWithTimestamp($event['event_start'], true) . ' - ' . getTimeWithTimestamp($event['event_end'], true); ?>
                                </a> */

                                $from = new Carbon(date("Y-m-d", $event['event_start']));
                                $difference = $from->diff(date("Y-m-d", $event['event_end']))->days;
                                ?>
                                <div class="btn-group">
                                    <button class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown">
                                        <?php echo getDateTimeWithTimestamp($event['event_start']); ?> - <?php echo getDateTimeWithTimestamp($event['event_end']); ?>
                                        <br><?php echo getTimeWithTimestamp($event['event_start'], true); ?> - <?php echo getTimeWithTimestamp($event['event_end'], true); ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php for ($i = 0; $i <= $difference; $i++): ?>
                                        <?php
                                            $from = new Carbon(date("Y-m-d", $event['event_start']));
                                            $current = $from->addDays($i);
                                        ?>
                                        <li>
                                            <a href="#eventInfo-report-modal" data-date="<?php echo $current->format("Y-m-d"); ?>" data-id="<?php echo $event['id']; ?>" data-team_id="<?php echo $event['team_id']; ?>" data-wo_id="<?php echo $event['wo_id']; ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                                                <?php echo getDateTimeWithTimestamp($current->timestamp); ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                    </ul>
                                </div>

                                <?php

                                /*if($event['event_report']) : ?>

                                    <?php $event['full_time'] = $full_time;?>
                                    <a href="#eventInfo-<?php echo $event['id']; ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                                        <?php echo date('<\b><\u>'.getDateFormat().'</\u></\b>', $event['event_start']) . '<br>' . date(getPHPTimeFormatWithOutSeconds(), $event['event_start']) . '-' . date(getPHPTimeFormatWithOutSeconds(), $event['event_end']); ?>
                                    </a>
                                    <?php $this->load->view('workorders/profile_modal_event_info', $event); ?>

                                <?php else : ?>

                                    <?php echo date('<\b><\u>Y-m-d</\u></\b>', $event['event_start']) . '<br>' . date('H:i', $event['event_start']) . '-' . date('H:i', $event['event_end']); ?>

                                <?php endif; ?>
                                <br>
                                <?php if ($event['team_closed']) : ?>
                                    <h5 class="text-center text-success font-bold">L</h5>
                                <?php else : ?>
                                    <h5 class="text-center text-danger font-bold">U</h5>
                                <?php endif;*/
                                ?>
                            </td>
                            <td style="<?php echo $service_style; ?>">
                                <?php echo $event['crew_name']; $crewMembers = NULL; ?>
                                <?php /*
                                <div style="display: inline-block;background: <?php echo $event['team_color']; ?>;height: 15px; width: 15px; border: 1px solid #000;"></div>
                                */ ?>
                                <?php foreach($members[$event['id']] as $member) : ?>
                                    <?php $crewMembers .= $member['emp_name'] . '<br>'; ?>
                                <?php endforeach; ?>
                                <?php $crewMembers = "<br>" . rtrim($crewMembers, ', '); ?>

                                <?php foreach($items[$event['id']] as $item) : ?>
                                    <?php $crewMembers .= '<u>' . $item['eq_code'] . '</u><br>'; ?>
                                <?php endforeach; ?>
                                <?php echo rtrim($crewMembers, ', '); ?>
                            </td>
                            <td style="<?php echo $service_style; ?>"><?php echo $event['event_note']; ?></td>
                            <td style="<?php echo $service_style; ?>">
                                <a href="#" class="team-amount" data-toggle="popover" data-event-id="<?php echo $event['id']; ?>" data-html="1" data-original-title="Event Price" data-content='<div class="form-group m-b-none" style="width: 140px;"><input style="width: 100px;" class="form-control inline eventPrice-<?php echo $event['id']; ?>" type="text" name="event_price" value="<?php echo $event['event_price']; ?>"><button class="btn btn-xs btn-success m-l-sm changeEventPrice" data-id="<?php echo $event['id']; ?>"><i class="fa fa-check"></i></button></div>'>
                                    <?php echo money($event['event_price']); ?>
                                    <?php $totalForEvents += $event['event_price']; ?>
                                </a>
                            </td>


                            <td class="text-center" style="<?php echo $service_style; ?>">
                            <?php
                                echo round($event['event_man_hours'], 2);
                                $total_time_summ+=$event['event_man_hours'];
                            ?>
                             </td>
                            <td class="text-center" style="<?php echo $service_style; ?>"><?php echo round($event['planned_workorder_time'], 2); ?></td>
							<td class="text-center" style="<?php echo $service_style; ?>; width: 90px;">
								<?php echo ($event['er_on_site_time']) ? round($event['er_on_site_time'] / 3600, 2) : 0; ?> / <?php echo ($event['er_travel_time']) ? round($event['er_travel_time'] / 3600, 2) : 0; ?>
							</td>
                            <td class="text-center" style="<?php echo $service_style; ?>">
                                <?php

                                    $total_mnrs_price = ((float)$event['event_man_hours'])?round((float)($event['event_total']-$event['expense_amount_sum'])/(float)$event['event_man_hours'], 2):0;
                                    $total_mnrs_summ+=$total_mnrs_price;
                                echo money(round($total_mnrs_price, 2));
                                ?>
                            </td>

                            <td class="text-center" style="<?php echo $service_style; ?>"><?php
                                    if(floatval($event['planned_workorder_time'])>0):
                                        echo money(round(floatval($event['total']) / floatval($event['planned_workorder_time']),
                                            2));
                                        $planned_mnrs_price_summ+=$event['total'];
                                    else:
                                        echo money(0);
                                    endif;

                            ?></td>
                            <td class="text-center" style="<?php echo $service_style; ?>">
                                <a href="#add_expense" id="addExpense-<?php echo $event['id']; ?>" data-pk="<?php echo $event['id']; ?>" data-name="event_expenses" class="reportInfo" data-toggle="modal" data-backdrop="static" data-keyboard="false" >
                                    <?php echo $event['expense_amount_sum'] ? money($event['expense_amount_sum']) : '—';
                                    $total_expenses_summ += $event['expense_amount_sum']; ?>
                                </a>
                            </td>
                            <td class="text-center" style="<?php echo $service_style; ?>">
                                <a href="#" data-name="event_complain" data-value="<?php echo $event['event_complain']; ?>" data-placement="top" data-type="text" data-pk="<?php echo $event['id']; ?>" class="event_complain editable editable-click editable-empty" title="" data-url="<?php echo base_url('workorders/changeDC'); ?>" data-original-title="Complaint">
                                    <?php echo $event['event_complain'] ? money($event['event_complain']) : '—'; ?>
                                </a>

                            </td>
                            <td class="text-center" style="<?php echo $service_style; ?>">
                                <a href="#" data-name="event_damage" data-value="<?php echo $event['event_complain']; ?>" data-placement="top" data-type="text" data-pk="<?php echo $event['id']; ?>" class="event_damage editable editable-click editable-empty" title="" data-url="<?php echo base_url('workorders/changeDC'); ?>" data-original-title="Damage">
                                    <?php echo $event['event_damage'] ? money($event['event_damage']) : '—'; ?>
                                </a>
                            </td>

                            <td class="text-center p-n" style="<?php echo $service_style; ?>">
                                <a href="#" data-name="event_compliment" data-placement="right" data-type="text"  data-pk="<?php echo $event['id']; ?>" class="type event_compliment" title="Comment" data-url="<?php echo base_url('workorders/changeDC'); ?>">

                                    <?php echo $event['event_compliment']; ?>
                                </a>


                            </td>
                            <td style="vertical-align: middle;<?php echo $service_style; ?>">
                                <label class="switch-mini">
                                    <input data-id="<?php echo $event['team_id']; ?>" data-amount="<?php echo $teamAmount; ?>" name="closed" class="closed" type="checkbox" <?php if($event['team_closed']) : ?>checked="checked"<?php endif; ?> <?php if(!$event['team_man_hours'] || $event['count_team_events'] != 1) : ?>disabled="disabled"<?php endif; ?>>
                                    <span ></span>
                                </label>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class=" totalForEvents"><?php echo money(round($totalForEvents, 2)); ?></td>

                            <td class="text-center"><?php echo round(floatval($total_time_summ), 2); ?></td>
                            <td class="text-center"><?php echo round(floatval($events_total_summ), 2); ?></td>
                            <td></td>
                            <td class="text-center"><?php echo money(round(($totalForEvents - $total_expenses_summ) / ($total_time_summ?:1), 2));//echo money(round(floatval($total_mnrs_summ) / floatval(count($events)), 2)); ?></td>
                            <td class="text-center">
                            <?php
                                if(floatval($events_total_summ) > 0) :
                                    echo money(round(floatval($planned_price) / floatval($events_total_summ), 2));
                                else:
                                    echo money(0);
                                endif;
                            ?>

                            </td>
                            <td class="text-center">
                                <?php echo money(round($total_expenses_summ, 2)); ?>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php else : ?>
                        <tr>
                            <td colspan="13" style="color:#FF0000;">No record found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

<script type="text/javascript">
	function initEditable(selector) {
		if(!selector)
			selector = '';
		
		$(selector + '.event_compliment').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			
		});


	}
	$(document).ready(function(){
		initEditable();
		$('body').on('click', function (e) {
		    if ($(e.target).data('toggle') !== 'popover'
		        && $(e.target).parents('.popover.in').length === 0) {
		    	$('[data-toggle="popover"]').popover('hide');
		    }
		});

		$('.team-amount').on('click', function (e) {
			var eventId = $(this).data('event-id');
			if (!$('.team-amount[data-event-id="' + eventId + '"]').find('.popover.in').length) { 
		        $(this).popover('show');
		    }
		    else {
		    	$('[data-toggle="popover"]').popover('hide');
		    }
		});

		$(document).on('click', '.changeEventPrice', function() {
			var eventId = $(this).data('id');
			var obj = $('.team-amount[data-event-id="' + eventId + '"]');
			var eventPrice = parseFloat($(this).parent().find('.eventPrice-' + eventId).val().replace(',', '.'));
			$(this).parent().find('.eventPrice-' + eventId).val(eventPrice);
			$.ajax({
				type: 'POST',
				url: baseUrl + 'schedule/ajax_change_event_price',
				data: {id:eventId, event_price:eventPrice},
				global: false,
				success: function(resp){
					if(resp.status == 'ok') {
						$(obj).text(resp.total_for_services);
						$(obj).parents('table:first').find('.totalForEvents').text(resp.wo_amount);
						$('body').click();
						var content = $(obj).attr('data-content');
						var tempDiv = document.createElement('div');
						tempDiv.innerHTML = content;
						$(tempDiv).find('.eventPrice-' + eventId).attr('value', eventPrice);
						$(obj).attr('data-content', tempDiv.innerHTML);
					}
					return false;
				},
				dataType: 'json'
			});
		});

		$('.event_complain,.event_damage,.event_expenses').editable({
			success: function(response, newValue) {
				$(this).editable('setValue', newValue);
				newValue = parseFloat(newValue);
				if(newValue) {
					newValue = Common.money(newValue);
				} else {
					newValue = '—';
				}
				var text = $(this).text();
				$(this).text(newValue);
				$('.editable-cancel').click();
				return false;
			}
		});
		$('.closed').on('change', function(){
			var name = $(this).attr("name")
			var team_id = $(this).data('id');
			var check = $(this).prop('checked')?1:0;
			var amount = $(this).data('amount');
			 
			$.post(baseUrl + 'schedule/ajax_close_team', {team_id:team_id,check:check,amount:amount}, function () {
			}, 'json');
			return false;
		});
		/*
		$('.event_compliment').hover(function() {
			var obj = $(this).find('i');
			if($(obj).is('.fa-heart-o'))
				$(obj).removeClass('fa-heart-o').addClass('fa-heart');
			else
				$(obj).removeClass('fa-heart').addClass('fa-heart-o');
		});

		$('.event_compliment').click(function() {
			var obj = $(this).find('i');
			$.post(baseUrl + 'workorders/changeDC', {pk:$(this).data('pk'), value:$(obj).is('.fa-heart-o')?0:1, name:'event_compliment'}, function(){
				if($(obj).is('.fa-heart-o'))
					$(obj).removeClass('fa-heart-o').addClass('fa-heart');
				else
					$(obj).removeClass('fa-heart').addClass('fa-heart-o');
			});
		});
		*/
	});
</script>
<?php $this->load->view('dashboard/expenses/add_expense_modal'); ?>
