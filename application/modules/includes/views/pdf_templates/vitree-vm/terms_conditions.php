<?php 
    $default_img = base_url('assets/'.$this->config->item('company_dir').'/print/header.png');
    $brand_id = get_brand_id($estimate_data, $client_data);
    $estimate_logo = get_brand_logo($brand_id, 'main_logo_file', $default_img);
    $estimate_terms = get_estimate_terms($brand_id);
?>
<pagebreak>
	<div class="holder p_top_" style="margin-left: 342px; margin-top: 0px;"><img src="<?php echo $estimate_logo; ?>" width="482" height="85" style="<?php echo $this->config->item('company_header_pdf_small_logo_styles'); ?>"    class="p-top-20"></div>
	<div class="title m_min_50" style="margin-top: -40px;">Terms and Conditions</div>
	<br>

	<div class="title_1">INDUSTRY STANDARD FOR PRUNING OPERATIONS</div>
	<?php if(!strip_tags($estimate_terms, ['img'])): ?>
	<div class="des_1">(as condensed from the Ansi A300 Standards)<br><br>
		Pruning is to be performed by arborists who, through related training and on-the-job experience, are familiar with techniques
	 and hazards of this work including trimming, maintenance, repairing or removal, and equipment used in such operations. The use
	 of climbing spurs or irons is not approved in pruning operations on live trees. This type of work is a potentially hazardous
	 occupation and is to be undertaken only by trained personnel or under the supervision of trained personnel, all of whom are
	 covered with worker's compensation, property damage, public liability and completed operations insurance.
	</div>

	<div class="title_1">PEST MANAGEMENT PROGRAM</div>
	<div class="des_1">
		This program is designed especially for your landscape needs; primarily to protect the tree’s ability to manufacture food in the
		leaves to maintain their beauty and vigour. All treatments are applied by trained applicators. Due to the short term residual of
		available pesticides, repeat applications may b e required.
	</div>

	<div class="title_1">INSECT CONTROL</div>
	<div class="des_1">
		Inspection and treatment visits are scheduled at the proper time to achieve management of the destructive pests. Pesticides are
		applied only when pests are present or the potential exists, and only to the plants affected.
	</div>

	<div class="title_1">DISEASE CONTROL</div>
	<div class="des_1">
		Specific treatments designed to manage particular disease problems. Whether preventative or curative, the material used, the
		plant variety being treated, and the environmental conditions all dictate what treatment is needed.
	</div>

	<div class="title_1">ARBOR GREEN PRO FEEDING PROGRAM</div>
	<div class="des_1">
		In our landscaping environment it is impossible for nature’s cycle of food renewal to take place. Arbor Green PRO replenishes this
		decomposition cycle. It is hydraulically injected into the root area and the nutrients are slowly released. It doesn’t burn delicate
		feeding roots and it contains no chlorides or nitrates. Research and experience show the dramatic benefits Arbor Green PRO may
		provide: greater resistance to insect and disease attack, greater tolerance to drought, increased vigour, and denser, more
		luxuriant foliage.
	</div>

	<br><br>
	<div class="des_1">
		The work performed is fully covered by Canadian General Liability insurance and all workers are covered by WorkSafe BC. All
		work is performed in accordance with accepted industry standards. The training and supervision supplied by Vancouver Island
		Tree Service Ltd. will ensure that your property is safe from any damage.<br><br>

		Vancouver Island Tree Service Ltd. Will not be responsible for damage to the property not visible and where not specifically
		noted and marked by the customer, especially underground sprinkler systems.<br><br>

		Any concerns must be made within seven days or the invoice is assumed payable.<br><br>

		This quotation is valid for 30 days.<br><br>

		Payment for services rendered is due upon completion of the work. Overdue accounts will be charged interest at the rate of 2%
		per month, or 24% per annum.<br><br>

		Please make cheques payable to <?php echo $this->config->item('company_name_long'); ?>
	</div>
	<?php else: ?>
        <?php echo $estimate_terms; ?>
    <?php endif; ?>

    <?php if(brand_name($brand_id)): ?>
    	<div class="title_1"><?php echo brand_name($brand_id, true); ?></div>
	<?php else: ?>
		<div class="title_1"><?php echo $this->config->item('company_name_long'); ?></div>
	<?php endif; ?>
	
	<?php $brand_address = brand_address($brand_id); ?>
   	<?php if($brand_address): ?>
   		<span class="green">ADDRESS:</span>&nbsp;<?php echo $brand_address; ?>&nbsp;<span class="green">OFFICE:</span>&nbsp;<?php echo brand_phone($brand_id); ?>&nbsp;<span class="green">EMAIL:</span>&nbsp;<?php echo brand_email($brand_id); ?>
    <?php else: ?>
		<div class="address" style="position: absolute; bottom: 20px; right: 0; left: 0;">
			<?php echo $this->config->item('footer_pdf_address'); ?>
		</div>
	<?php endif; ?>