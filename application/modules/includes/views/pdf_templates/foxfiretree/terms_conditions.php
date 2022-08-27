<?php 
    $default_img = base_url('assets/'.$this->config->item('company_dir').'/print/header.png');
    $brand_id = get_brand_id($estimate_data, $client_data);
    $estimate_logo = get_brand_logo($brand_id, 'main_logo_file', $default_img);
    $estimate_terms = get_estimate_terms($brand_id);
?>
<pagebreak>
	<div class="holder p_top_" style="margin-left: 342px; margin-top: 0px;"><img src="<?php echo $estimate_logo; ?>" height="85"
	                       style="<?php echo $this->config->item('company_header_pdf_small_logo_styles'); ?>"    class="p-top-20"></div>
	<div class="title m_min_50" style="margin-top: -40px;">Terms and Conditions</div>
	<br>
	<?php if(!strip_tags($estimate_terms, ['img'])): ?>
	<div class="title_1"><strong>It is agreed by and between <?php echo $this->config->item('company_name_long'); ?> and the authorizing party (customer and/or customer’s agent) that the following provisions are made as part of this contract:</strong></div>

	<div class="title_1">Insurance by Contractor:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?> warrants that it is insured for liability resulting from injury to person(s) or property and that all employees are covered by Workers’ Compensation as required by law.  Certificates of coverage are available upon request.
	</div>

	<div class="title_1">Completion of Contract:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?> agrees to do its best to meet any agreed upon performance dates, but shall not be liable in damages or otherwise for delays because of inclement weather, labor, or any other cause beyond its control; nor shall the customer be relieved of completion for delays.
	</div>

	<div class="title_1">Tree Ownership and Permitting:</div>
	<div class="des_1">
		The authorizing party warrants that all trees listed are located on the customer’s property, and, if not, that the authorizing party has received full permission from the owner to allow <?php echo $this->config->item('company_name_long'); ?> to perform the specified work.  In addition, the authorizing party warrants that any permits necessary for tree removal have been acquired.  Should any tree be mistakenly identified as to ownership, the customer agrees to indemnify <?php echo $this->config->item('company_name_long'); ?> for any damages or costs incurred from the result thereof.
	</div>

	<div class="title_1">Safety:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?> warrants that all arboricultural operations will follow the latest version of the ANSI Z133.1 industry safety standards.  The authorizing party agrees to not enter the work area during arboricultural operations unless authorized by the crew leader on-site.
	</div>

	<div class="title_1">Stump Removal:</div>
	<div class="des_1">
		Unless specified in the proposal, stump removal is not included in the price quoted.  Grindings from stump removal are not hauled unless specified in this proposal.  Surface and subsurface roots beyond the stump are not removed unless specified in this proposal.
	</div>

	<div class="title_1">Concealed Contingencies:</div>
	<div class="des_1">
		Any additional work or equipment required to complete the work, caused by the authorizing party’s failure to make known or caused by previously unknown foreign material in the trunk, the branches, underground, or any other condition not apparent in estimating the work specified, shall be paid for by the customer on a time and material basis.  <?php echo $this->config->item('company_name_long'); ?> is not responsible for damages to septic systems, underground sprinklers, drain lines, invisible fences or underground cables unless the system(s) are adequately and accurately mapped by the authorizing party and a copy is presented before or at the time the work is performed.
	</div>

	<div class="title_1">Clean-up:</div>
	<div class="des_1">
		Clean-up shall include removing wood, brush, and clippings, and raking of the entire area affected by the specified work, unless noted otherwise on this proposal.
	</div>

	<div class="title_1">Lawn Repair:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?> will attempt to minimize all disturbances to the customer’s lawn.  Lawn repairs are not included in the contract price, unless noted otherwise on this proposal.
	</div>

	<div class="title_1">Terms of Payment:</div>
	<div class="des_1">
		Unless otherwise noted in this proposal, the customer agrees to pay the account in full within 15 days of work completion.  Failure to remit full payment within the payment term will result in a finance charge of 1.5% per month.
	</div>

	<?php else: ?>
        <?php echo $estimate_terms; ?>
    <?php endif; ?>
    
	<?php $brand_address = brand_address($brand_id); ?>
   	<?php if($brand_address): ?>
   		<span class="green">ADDRESS:</span>&nbsp;<?php echo $brand_address; ?>&nbsp;<span class="green">OFFICE:</span>&nbsp;<?php echo brand_phone($brand_id); ?>&nbsp;<span class="green">EMAIL:</span>&nbsp;<?php echo brand_email($brand_id); ?>
    <?php else: ?>
		<div class="address" style="position: absolute; bottom: 20px; right: 0; left: 0;">
			<?php echo $this->config->item('footer_pdf_address'); ?>
		</div>
	<?php endif; ?>