<!--Profile Counter -->
<aside class="bg-white">
	<!--Counter Header-->
	<section class="vbox panel panel-default m-n">
		<header class="panel-heading font-bold form-horizontal">
			<div class="col-sm-4 control-label inline">Productivity Pulse</div>

            <div class="input-group reportrange inline" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;"
                 data-selector="#counter_table" data-url="<?php echo base_url('/dashboard/getProductivityPulse/');?>"
                 data-dates="<?php echo isset($dates) ? htmlspecialchars(json_encode($dates)) : ''; ?>">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span>
            </div>
		</header>
        <script>
            var fromDate = "<?php echo isset($from_date) ? $from_date : date(getDateFormat()); ?>";
            var toDate = "<?php echo isset($to_date) ? $to_date : date(getDateFormat()) ?>";

            Common.initDateRangePicker('.reportrange', fromDate, toDate);

            //var myPie = new Chart(document.getElementById("pie").getContext("2d")).Pie(pieData);

        </script>

		<section class="scrollable">
			<!--Counter table -->
			<?php $this->load->view('productivity_pulse'); ?>
		</section>
	</section>
</aside>
<!--Profile Counter -->
<div id="reportInfoModal" class="modal fade reportInfoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

		$(document).delegate('.send-expense-report', 'click', function(){
			$(this).closest('form').trigger('submit');
		});

		function init_report_editable(){

			$('.editable-report').editable({
				format: 'hh:mm a',
	        	viewformat: 'hh:mm a',
	        	template: "hh : mm a",
	        	escape:true,
                emptytext: "Empty",
	        	combodate:{
	        		minYear: <?php echo (int)date("Y")-10; ?>,
	    			maxYear: <?php echo (int)date("Y")+10; ?>,	
	        	},
	        	success: function(response, newValue) {
	        		var data = JSON.parse(response);
	        		if(data.container!=undefined){
	        			$('.reportInfoModal '+data.container).html(data.html);
	        		}
	        		else{
	        			$('.reportInfoModal .reports-container').html(data.html);	
	        		}
			        
			        init_report_editable();
			    },
			    display: function(value, val2) {
                    if($(this).hasClass('currency')){
			    		Common.mask_currency($(this));
			    	}
                    if(!value || value==undefined)
                        $(this).empty();
			    }
	        });
		}
        $("document").ready(function () {
		//init_report_editable();

		/*
		$(document).delegate('.reportInfo', 'click', function(){
			var team_id = $(this).data('id');
			$($(this).attr('href')).data('team_id', team_id);
			$($(this).attr('href')).modal();
			return false;
		});
		*/
		$('.reportInfoModal').find('[data-dismiss="modal"]').click(function(){
			$(this).parents('.reportInfoModal').modal('hide');
			return false;
		});

		$('.reportInfoModal').on('show.bs.modal', function (e) {
			var id = e.relatedTarget.dataset.id;
		 	$.post('/events/get_team_report', {'team_id':id}, function(response){
		 		$('#reportInfoModal .modal-body').html(response.html);
		 		setTimeout(function(){ init_report_editable(); }, 100); 
		 	}, "JSON");
		});
		/* pulse date dicker*/

		$(document).delegate('.woProfile', 'click', function(){
			var win = window.open($(this).attr('href'), '_blank');
			win.focus();
			return false;
		});
        
		$(document).delegate('.chk_payroll', 'click', function(){
	        var href = $(this).attr('href');
			window.open(href, '_blank');
	        return false;
		});
		$(document).delegate('.loc_map', 'click', function(){
	        var href = $(this).attr('href');
			window.open(href, '_blank');
	        return false;
		});

		<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) : ?>
		$(document).delegate('.confirmReport', 'click', function() {
			var obj = $(this);
			var er_id = $(this).data('er_id');
			var event_id = $(this).data('event_id');
			var leader_id = $(this).data('leader_id');
			$.post(baseUrl + 'events/confirmReport', {event_id:event_id, er_id:er_id}, function(resp){
				if(resp.status == 'ok')
				{

					$(obj).parents('.reportInfoModal').find('#one-report-' + er_id).toggle('slow');
                    $(obj).parents('.reportInfoModal').find('#one-report-' + er_id).prev().remove();
					$(obj).parents('.reportInfoModal').find('#one-report-' + er_id).remove();
					
					if($('.reportInfoModal .one-report').length == 0)
					{
						$('.reportInfoModal').modal('hide');
                        $('#reportsCounter').text(resp.countEventsReport);

						setTimeout(function(){
							$('.eventsList').find('li[data-leader_id="'+leader_id+'"]').css('background', '#C9E2B6').fadeOut('slow', function(){
							    $('.eventsList').find('li[data-leader_id="'+leader_id+'"]').remove();
							});
						}, 500);
					}
				}
				else
				{
					alert('Ooops! Error.');
				}
			}, 'json');
			return false;
		});

		$(document).delegate('.confirmEstReport', 'click', function() {
			var obj = $(this);
			var report_id = $(this).data('report_id');
			$.post(baseUrl + 'report/ajax_confirm_report', {report_id:report_id}, function(resp){
				if(resp.status == 'ok')
				{
					if($('.eventsList').length)
					{
						$(obj).parents('.reportInfoModal').modal('hide');
						setTimeout(function(){
							$('.eventsList').find('li[data-report_id="'+report_id+'"]').css('background', '#C9E2B6').fadeOut('slow', function(){
								$('.eventsList').find('li[data-report_id="'+report_id+'"]').remove();
								var counts = parseInt($('#estimatorCounter').text()) - 1;
								if(counts)
									$('#estimatorCounter').text(counts);
								else
								{
									$('#estimatorCounter').parent().parent().find('.eventsList').remove();
									$('#estimatorCounter').parent().parent().html(counts);
								}
							});
						}, 500);
					}
					else
					{
						location.reload();
					}
				}
				else
				{
					alert('Ooops! Error.');
				}
			}, 'json');
			return false;
		});
		<?php endif; ?>

	});

</script>
