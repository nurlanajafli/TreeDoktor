
<div class="panel-group p-5 m-b-none" style="height: 550px; overflow: auto;" id="recomendations-result">
	<!--<div class="slim-scroll"  data-height="250px" id="recomendations-result">
	</div>-->
</div>
<input type="hidden" >
<script id="recomendations-template-empty" type="text/x-jsrender">
	<h2 class="text-muted text-center m-top-25 p-top-20">No recommended estimators</h2>
</script>
<script id="recomendations-template" type="text/x-jsrender">
    {{if gDate}}
        <h4 class="text-center">{{:gDate}}</h4>
    {{/if}}
	<div class="panel panel-default" data-total="{{:total}}" data-filter_estimator_id="{{:estimator.id}}">
	  	<div class="panel-heading estimator-appointment p-n" data-estimator="{{:estimator.id}}" data-estimator-name="{{:estimator.firstname}}&nbsp;{{:estimator.lastname}}">

		    <a class="accordion-toggle block p-10 w-85 pull-left" data-toggle="collapse" data-parent="#recomendations-result" href="#collapse{{:estimator.id}}-{{:current.start}}-{{:current.end}}">
		    	<span class="pull-left">
		    		<img width="39px" src="{{:estimator.photo}}" alt="{{:estimator.firstname}}&nbsp;{{:estimator.lastname}}" class="img-circle">&nbsp;
		    	</span>
		    	<span class="pull-left p-top-10">
		    	&nbsp;&nbsp;{{:estimator.firstname}}&nbsp;{{:estimator.lastname}} 
		    	</span>
		    	<span class="pull-right">
		    		<span class="text-danger"><i class="fa fa-calendar"></i>&nbsp;Date: {{:date}}</span>
		    		<br>
		    		<span class="text-info">
		    		<i class="fa fa-clock-o"></i>&nbsp;{{:current_formated.start_time}}/{{:current_formated.end_time}}
		    		</span>
		    	</span>
		    	<span class="clearfix"></span>
		    </a>
		    <button type="button" class="btn btn-rounded btn-sm btn-icon btn-default pull-right m-top-20 m-right-5 information-table" data-toggle="popover" data-html="true" data-placement="top" data-content="<table class='table table-striped' style='zoom: 0.8;'>
		      		<thead>
		      		<tr>
		      			<th>EMERGENCY CLOSEST AVAILABLE TIME X 20</th>
		      			<th>IF THE CLIENT IS PRIORITY THAN ADD 5 POINTS  TO THE CLOSEST DATE</th>
		      			<th>PREVIOUS ESTIMATOR</th>
		      			<th>CLOSEST APPOINTMENT- THE SOONER YOU COME THE MORE CHANCES TO GET A JOB</th>
		      			<th>DISTANCE- LESS DISTANCE LESS TRAVEL TIME- MORE APPOINTMENTS PER DAY</th>
		      			<th>HOW BIG IS THE JOB LARGE MEDIUM X2 SMALL X1</th>
		      			<th>DAY FILLING- PRIORITISE FILLING THE DAY</th>
		      			<th>WHICH ESTIMATOR DO LESS LEADS ON AVERAGE</th>
		      			<th>CONFIRMATION RATES ESTIMATOR WITH HIGHEST RATE</th>
		      			<th>LEAST ESTIMATES BOOKED IN THE WEEK</th>
		      			<th>Total</th>
		      		</tr>
		      		</thead>
		      		<tbody>
		      		<tr>
		      			<td>{{:emergency}}</td>
		      			<td>{{:priority}}</td>
		      			<td>{{:previus_estimator_coeff}}</td>
		      			<td>{{:closest_available_time}}</td>
		      			<td>{{:distance_coeff}}</td>
		      			<td>{{:preliminary_estimate_coeff}}</td>
		      			<td>{{:day_filling_coeff}}</td>
		      			<td>{{:total_filling_coeff}}</td>
		      			<td></td>
		      			<td></td>
		      			<td>{{:total}}</td>
		      		</tr>
		      		</tbody>
		      	</table>" title="" data-original-title='<button type="button" class="close pull-right" data-dismiss="popover">&times;</button>Rating Details'><i class="fa fa-info get-information-table"></i>
		    </button>
		    <div class="clearfix"></div>
	  	</div>
	  	<div id="collapse{{:estimator.id}}-{{:current.start}}-{{:current.end}}" class="panel-collapse collapse">
		    <div class="panel-body text-sm">
		    <div class="m-b-sm">                
  			<div class="btn-group" data-toggle="buttons">
			    {{for current.small_intervals}}
			    
			    <label class="btn btn-sm btn-success m-right-5 m-bottom-5 time-interval-label">
					<input type="radio" name="time_interval" data-date="{{>date}}"><i class="fa fa fa-clock-o text-active"></i>{{>start}}&nbsp;-&nbsp;{{>end}}
				</label>

				<div class="schedule-duration-value hidden">

					<input data-date="{{:date}}" data-date-startdate="{{>start}}" data-date-enddate="{{>end}}" type="hidden" data-format="hh:mm" data-current="{{>start}}" value="{{>start}}" name="schedule_interval_start" disabled="disabled">

					<input data-date="{{:date}}" data-date-startdate="{{>start}}" data-date-enddate="{{>end}}" type="hidden" data-format="hh:mm" data-current="{{>end}}" value="{{>end}}" name="schedule_interval_end" disabled="disabled">

				</div>

			    {{/for}}
			    
			    {{if low_priority}}
			    <div class="clear"></div>
			    <blockquote>
			    	<p>Low Priority</p>
				    {{props low_priority}}
				    	{{for prop.current.small_intervals}}
					    	<label class="btn btn-sm btn-success m-right-5 m-bottom-5 time-interval-label">
								<input style="display: none;" type="radio" name="time_interval" data-date="{{>date}}"><i class="fa fa fa-clock-o text-active"></i>{{>start}}&nbsp;-&nbsp;{{>end}}
							</label>

							<div class="schedule-duration-value hidden">

								<input data-date-startdate="{{>start}}" data-date-enddate="{{>end}}" type="hidden" data-format="hh:mm" data-current="{{>start}}" value="{{>start}}" name="schedule_interval_start" disabled="disabled">

								<input data-date-startdate="{{>start}}" data-date-enddate="{{>end}}" type="hidden" data-format="hh:mm" data-current="{{>end}}" value="{{>end}}" name="schedule_interval_end" disabled="disabled">

							</div>
					    {{/for}}
					{{/props}}
				</blockquote>
				{{/if}}
		    </div>
		    </div>
		    </div>
	  	</div>
	</div>
</script>

<script>
  window.gDate = null;
</script>
