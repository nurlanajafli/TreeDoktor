<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/js/jquery.tablesorter.min.js'); ?>"></script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('estimates'); ?>">Estimates</a></li>
		<li class="active">Own Estimates</li>
	</ul>

	<?php $this->load->view('index_sale_block.php'); ?>
	<?php $this->load->view('estimates/estimates_personal_files'); ?>
</section>
<script>
	//return false;
	$(document).ready(function(){
		$.tablesorter.addParser({
			// set a unique id
			id: 'grades',
			is: function(s, table, cell, $cell) {
			// return false so this parser is not auto detected
			return false;
			},
			format: function(s, table, cell, cellIndex) {
			// format your data for normalization
			return $(cell).attr('data-status');
		  },
		  // set type, either numeric or text
		  type: 'numeric'
		});
		
			$("table").tablesorter({ 
				headers: { 
					0: { sorter: false },
					1: { sorter: false },
					2: { sorter: false },
					3: { sorter: false },
					5: { sorter: false },
					6: { 
						sorter:'grades' 
					},
					7: { sorter: false }
				}
				 
			});   
	});

</script>
<?php $this->load->view('includes/footer'); ?>
