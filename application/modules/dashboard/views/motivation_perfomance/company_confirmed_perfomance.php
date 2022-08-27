<?php $good = 100;?>
<?php if(isset($company_previous_last_year) && $company_previous_last_year) : ?>
	<div class="papers-block b-b">
		<div class="font-thin padder panel-heading m-n"><strong>Montly Review</strong></div>
		<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
			<strong><?php echo $previous_last_year_month; ?> Confirmed
                <?php echo money($company_previous_last_year); ?>
			</strong>
		</div>
		<?php //if(isset($company_previous_current) && $company_previous_current) : ?>
			<?php $perc = round($company_previous_current / ($company_previous_last_year + $company_previous_last_year/10) * 100); ?>
			<?php $percPlan = round(($company_previous_current / ($company_previous_last_year + $company_previous_last_year/10))*100); ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
				<strong class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
					<?php echo $previous_current_month; ?> Confirmed
                    <?php echo money($company_previous_current); ?>
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
<?php if(isset($company_last_year) && $company_last_year) : ?>
	<div class="papers-block b-b b-t">	
		<div class="font-thin padder panel-heading m-n"><strong><?php echo $last_year_month; ?> Confirmed
                <?php echo money($company_last_year); ?>
		</strong></div>
		<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0"><strong>Goal for <?php echo $current_month; ?> -
                <?php echo money($company_last_year + $company_last_year * 0.1); ?> + 10%
		</strong></div>
		<?php //if(isset($company_current) && $company_current) : ?>
			<?php $perc = round($company_current / ($company_last_year + $last_year/10) * 100); ?>
			<?php $percPlan = round(($company_current / ($company_last_year + $company_last_year/10))*100); ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
				<strong class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
					<?php echo $current_month; ?> Confirmed
                    <?php echo money($company_current); ?>
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
<?php if(isset($company_last_quart) && $company_last_quart) : ?>
	<div class="papers-block b-b b-t">	
		<div class="font-thin padder panel-heading m-n"><strong>Quarterly Review</strong></div>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
				<strong>
					<?php echo date('Y', strtotime($last_quart_month['start'])); ?> Last Quarter, Confirmed
                    <?php echo money($company_last_quart); ?>
				</strong>
			</div>
		<?php if(isset($company_current_quart) && $company_current_quart) : ?>
			<?php $perc = round(($company_current_quart / ($company_last_quart + $company_last_quart / 10)) * 100); ?>
			<?php $percPlan = round(($company_current_quart / ($company_last_quart + $company_last_quart / 10))*100); ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">				 
				<strong>
					Last Quarter, Confirmed
                    <?php echo money($company_current_quart); ?>
					<span class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
						<?php echo (($percPlan-100) > 0) ? '+' . ($percPlan - 100) : ($percPlan - 100); ?>% 
						<?php if($percPlan <= 99) : ?>Bad<?php elseif($percPlan > $good) : ?>Great<?php endif; ?>
					</span>
				</strong>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php if(isset($company_before_last_yearly) && $company_before_last_yearly) : ?>
	<div class="papers-block b-b b-t">	
		<div class="font-thin padder panel-heading m-n"><strong>Yearly Review</strong></div>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">
				<strong>
					<?php echo $before_last_yearly_day; ?> Yearly, Confirmed
                    <?php echo money($company_before_last_yearly); ?>
				</strong>
			</div>
		<?php if(isset($company_last_yearly) && $company_last_yearly) : ?>
			<?php $perc = round(($company_last_yearly / ($company_before_last_yearly + $company_before_last_yearly / 10)) * 100); ?>
			<?php $percPlan = round(($company_last_yearly / ($company_before_last_yearly + $company_before_last_yearly / 10))*100); ?>
			<div class="font-thin padder panel-heading m-n p-top-2 p-bottom-0">				 
				<strong>
					<?php echo $last_yearly_day; ?> Yearly, Confirmed
                    <?php echo money($company_last_yearly); ?>
					<span class="text-<?php if($percPlan <= 99) : ?>danger<?php elseif($percPlan > $good) : ?>success<?php endif; ?>">
						<?php echo (($percPlan-100) > 0) ? '+' . ($percPlan - 100) : ($percPlan - 100); ?>% 
						<?php if($percPlan <= 99) : ?>Bad<?php elseif($percPlan > $good) : ?>Great<?php endif; ?>
					</span>
				</strong>
			</div>
		<?php  endif; ?>
	</div>
<?php endif; ?>
