<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;">
	<?php echo $map['html']; ?>
	<div class="open" style="position: initial;">
		<ul class="dropdown-menu on" style="left: auto; right: 5px; height: 125px; overflow: auto; top: 0;">
			<?php foreach($statuses as $status) : ?>
				<li<?php if($status_name == $status->invoice_status_id) : ?> class="active"<?php endif; ?>>
					<a href="<?php echo base_url('invoices/invoices_mapper/' . $status->invoice_status_id); ?>"><?php echo $status->invoice_status_name; ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<script>
	$(document).ready(function(){
		$('.dropdown-menu.on').animate({
          scrollTop: $('.dropdown-menu.on').find('li.active').offset().top - 55
        }, 1);
	});
</script>
<?php $this->load->view('includes/footer'); ?>
