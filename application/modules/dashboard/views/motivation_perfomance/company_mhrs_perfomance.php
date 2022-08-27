<?php $good = 100;?>
<?php if(isset($worker_company_previous_last_year) && $worker_company_previous_last_year) : ?>
	<div class="papers-block b-b">
		<div class="font-thin padder panel-heading m-n"><strong>Montly Review</strong></div>
		<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
			<strong><?php echo $worker_previous_last_year_month; ?> 
				<?php echo round($worker_company_previous_last_year, 2);?> MHRS, AVG $ <?php echo $worker_avg_company_previous_last_year;?>
			</strong>
		</div>
		<?php //if(isset($worker_company_previous_current) && $worker_company_previous_current) : ?>
			<?php $perc = round($worker_company_previous_current / ($worker_company_previous_last_year + $worker_company_previous_last_year/10) * 100); ?>
			<?php $percPlan = round(($worker_company_previous_current / ($worker_company_previous_last_year + $worker_company_previous_last_year/10))*100); ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
				<strong class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
					<?php echo $worker_previous_current_month; ?>  
					<?php echo round($worker_company_previous_current, 2);?> MHRS, AVG $ <?php echo $worker_avg_company_previous_current;?>
					<div class="progress m-t-sm progress-striped active col-md-9 p-n">
						<div class="progress-bar progress-bar-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>" style="width: <?php echo $perc; ?>%;"></div>
					</div>
					<span class="col-md-3 m-t-sm text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
						<?php echo (($percPlan-100) > 0) ? '+' . ($percPlan - 100) : ($percPlan - 100); ?>% <?php if($percPlan <= 99) : ?>Bad<?php elseif($percPlan > $good) : ?>Great<?php endif; ?>
					</span>
				</strong>
			</div>
		<?php // endif; ?>
	</div>
<?php endif; ?>
<?php if(isset($worker_company_last_year) && $worker_company_last_year) : ?>
	<div class="papers-block b-b b-t">	
		<div class="font-thin padder panel-heading m-n"><strong><?php echo $worker_last_year_month; ?> 
			<?php echo round($worker_company_last_year, 2);?> MHRS, AVG $ <?php echo $worker_avg_company_last_year;?>
		</strong></div>
		<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0"><strong>Goal for <?php echo $worker_current_month; ?> - 
				<?php echo round($worker_company_last_year + $worker_company_last_year*0.1, 2);?> + 10%
		</strong></div>
		<?php //if(isset($worker_company_current) && $worker_company_current) : ?>
			<?php $perc = round($worker_company_current / ($worker_company_last_year + $worker_last_year/10) * 100); ?>
			<?php $percPlan = round(($worker_company_current / ($worker_company_last_year + $worker_company_last_year/10))*100); ?>
				<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
					<strong class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
						<?php echo $worker_current_month; ?> 
						<?php echo round($worker_company_current, 2);?> MHRS, AVG $ <?php echo $worker_avg_company_current;?>
						<div class="progress m-t-sm progress-striped active col-md-9 p-n">
							<div class="progress-bar progress-bar-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>" style="width: <?php echo $perc; ?>%;"></div>
						</div>
						<span class="col-md-3 m-t-sm text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
							<?php echo (($percPlan-100) > 0) ? '+' . ($percPlan - 100) : ($percPlan - 100); ?>% <?php if($percPlan <= 99) : ?>Bad<?php elseif($percPlan > $good) : ?>Great<?php endif; ?>
						</span>
					</strong>
				</div>
		<?php //endif; ?>
	</div>
<?php endif; ?>
<?php if(isset($worker_company_last_quart) && $worker_company_last_quart) : ?>
	<div class="papers-block b-b b-t">	
		<div class="font-thin padder panel-heading m-n"><strong>Quarterly Review</strong></div>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
				<strong>
					<?php echo date('Y', strtotime($worker_last_quart_month['start'])); ?> Last Quarter
					<?php echo round($worker_company_last_quart, 2);?> MHRS, AVG $ <?php echo $worker_avg_company_last_quart;?>
				</strong>
			</div>
		<?php //if(isset($worker_company_current_quart) && $worker_company_current_quart) : ?>
			<?php $perc = round(($worker_company_current_quart / ($worker_company_last_quart + $worker_company_last_quart / 10)) * 100); ?>
			<?php $percPlan = round(($worker_company_current_quart / ($worker_company_last_quart + $worker_company_last_quart / 10))*100); ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">				 
				<strong>
					Last Quarter
					<?php echo round($worker_company_current_quart, 2);?> MHRS, , AVG $ <?php echo $worker_avg_company_current_quart;?>
					<span class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
						<?php echo (($percPlan-100) > 0) ? '+' . ($percPlan - 100) : ($percPlan - 100); ?>% 
						<?php if($percPlan <= 99) : ?>Bad<?php elseif($percPlan > $good) : ?>Great<?php endif; ?>
					</span>
				</strong>
			</div>
		<?php //endif; ?>
	</div>
<?php endif; ?>
<?php if(isset($worker_company_last_yearly) && $worker_company_last_yearly) : ?>
	<div class="papers-block b-b b-t">	
		<div class="font-thin padder panel-heading m-n"><strong>Yearly Review</strong></div>
		<?php if(isset($worker_company_before_last_yearly) && $worker_company_before_last_yearly) : ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
				<strong>
					<?php echo $worker_before_last_yearly_day; ?> Yearly, 
					<?php echo round($worker_company_before_last_yearly, 2);?> MHRS, AVG $ <?php echo $worker_avg_company_before_last_yearly;?><br>
				</strong>
			</div>
		<?php endif; ?>
		<?php if(isset($worker_company_last_yearly) && $worker_company_last_yearly) : ?>
			<?php $perc = ($worker_company_before_last_yearly + $worker_company_before_last_yearly / 10) ? round(($worker_company_last_yearly / ($worker_company_before_last_yearly + $worker_company_before_last_yearly / 10)) * 100) : 0; ?>
			<?php $percPlan = ($worker_company_before_last_yearly + $worker_company_before_last_yearly / 10) ? round(($worker_company_last_yearly / ($worker_company_before_last_yearly + $worker_company_before_last_yearly / 10))*100) : 0; ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">				 
				<strong>
					<?php echo $worker_last_yearly_day; ?> Yearly, 
					<?php echo round($worker_company_last_yearly, 2);?> MHRS, AVG $ <?php echo $worker_avg_company_last_yearly;?>
					<span class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
						<?php echo (($percPlan-100) > 0) ? '+' . ($percPlan - 100) : ($percPlan - 100); ?>% 
						<?php if($percPlan <= 99) : ?>Bad<?php elseif($percPlan > $good) : ?>Great<?php endif; ?>
					</span>
				</strong>
			</div>
		
		<?php endif; ?>
	</div>
<?php endif; ?>
