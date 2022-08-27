<div class="row">
	<?php foreach($blocks as $key => $block) : ?>
		<section class="col-md-3 col-sm-6 col-xs-12">
			<section class="panel panel-default">
				<header class="panel-heading"><?php echo strtoupper($block['name']); ?></header>
					<?php foreach($block['list'] as $item) : ?>
						<div class="checkbox p-left-30">
							<label>
								<?php
								if(isset($row) && isset($row[$item['name']]))
									$checked_status = $row[$item['name']] == 'yes' ? TRUE : FALSE;
								else
									$checked_status = $this->input->post($item['name']) == 'yes' ? TRUE : FALSE;
								$data = array(
									'name' => $item['name'],
									'id' => $item['name'],
									'value' => 'yes',
									'checked' => $checked_status);

								echo form_checkbox($data) . $item['label']; ?>
							</label>
						</div>
						
					<?php endforeach; ?>
			</section>
		</section>
	<?php endforeach; ?>
</div>
