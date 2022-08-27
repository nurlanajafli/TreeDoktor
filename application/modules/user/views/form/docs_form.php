
	<?php if(isset($docs) && !empty($docs)) : ?>
		<?php foreach($docs as $k=>$v) : ?>
			<?php $this->load->view('form/certificate_form', ['num' => $k + 1, 'doc' => $v]); ?>
		<?php endforeach; ?>
		<?php if(count($docs) < 3) : ?>
			<?php for($i = 1; $i <= 3 - count($docs); $i++) : ?>
				<?php $this->load->view('form/certificate_form', ['num' => count($docs) + $i, 'doc' => []]); ?>
			<?php endfor; ?>
		<?php endif; ?>
	<?php else :?>
		<div class="row">
		<?php $this->load->view('form/certificate_form', ['num' => 1]); ?>
		<?php $this->load->view('form/certificate_form', ['num' => 2]); ?>
		<?php $this->load->view('form/certificate_form', ['num' => 3]); ?>
		</div>
	<?php endif; ?>