<?php
    $default_img = base_url('assets/'.$this->config->item('company_dir').'/print/header.png');
    $brand_id = get_brand_id($estimate_data, $client_data);
    $estimate_logo = get_brand_logo($brand_id, 'main_logo_file', $default_img);
?>
<pagebreak>

	<div class="title m_min_50" style="margin-top: -40px;">Terms and Conditions</div>
	<br />
	<?php if(!$brand_id): ?>
	<div class="title_1">
		<strong>
			This agreement is between Gordon Pro Tree Service, LLC herein known as GPTS, and the potential customer, herein known as customer. By customer signing this contract, customer has read, understood and accepted the terms, specifications, conditions, guarantees, and idemnifications of this contract as set forth herein.
		</strong>
	</div>
	<br />
	<div class="title_1">
		Insurance by Contractor:
	</div>
	<div class="des_1">
		GPTS warrants that it is insured for liability resulting from injury to person(s) or property and that all employees are covered by Workers’ Compensation as required by law. Certificates of coverage are available upon request.
	</div>

	<div class="title_1">
		Completion of Contract:
	</div>
	<div class="des_1">
		GPTS agrees to do its best to meet any agreed upon performance dates, but shall not be liable in damages or otherwise for delays because of inclement weather, labor, or any other cause beyond its control; nor shall the customer be relieved of completion for delays.
	</div>

	<div class="title_1">
		Tree Ownership:
	</div>
	<div class="des_1">
		The customer warrants that all trees listed are located on the customer’s property, and, if not, that the customer has received full permission from the owner to allow GPTS to perform the specified work. Should any tree be mistakenly identified as to ownership, the customer agrees to indemnify GPTS for any damages or costs incurred from the result thereof.
	</div>

	<div class="title_1">
		Safety:
	</div>
	<div class="des_1">
		Customer and/or customer's agent will provide GPTS serviceable access to the project area in order to safetly perform agreed upon services. The customer agrees to not enter the work area during arboricultural operations unless authorized by the crew leader on-site. GPTS warrants that all arboricultural operations will follow the latest version of the ANSI Z133.1 industry safety standards.
	</div>

	<div class="title_1">
		Stump Removal:
	</div>
	<div class="des_1">
		GPTS is not responsible for any flying debris damage which may occur during stump grinding. Unless specified in this proposal, stump removal is not included in the price. Surface and subsurface roots beyond the stump are not removed and grindings from the stump removal process are not hauled off unless specified in this proposal.
	</div>

	<div class="title_1">
		Concealed Contingencies:
	</div>
	<div class="des_1">
		Any additional work or equipment required to complete the work, caused by the customer's failure to make known or caused by previously unknown foreign material in the trunk, the branches, underground, or any other condition not apparent in estimating the work specified, shall be paid for by the customer on a time and material basis. GPTS is not responsible for damages to underground sprinklers, drain lines, invisible fences or underground cables unless the system(s) are adequately and accurately mapped by the customer and a copy is presented before or at the time the work is performed.
	</div>


	<div class="title_1">
		Clean-up:
	</div>
	<div class="des_1">
		Clean-up shall include removing wood, brush, and clippings, and raking of the entire area affected by the specified work, unless noted otherwise on this proposal.
	</div>


	<div class="title_1">
		Lawn Repair:
	</div>
	<div class="des_1">
		GPTS will attempt to minimize all disturbances to the customer’s lawn. Lawn repairs are not included in the contract price, unless noted otherwise on this proposal.
	</div>


	<div class="title_1">
		Terms of Payment:
	</div>
	<div class="des_1">
		Unless otherwise noted in this proposal, the customer agrees to pay the account in full upon completion of work. Failure to remit full payment within the payment term will result in a $25.00 late fee. We accept cash, checks, VISA, and MasterCard. There will be a 3% surcharge for payments made by credit card.
		<br /><br />
		If the balance of the Contract Agreement is not paid within sixty (60) days of completion, we reserve the right to file a Mechanics' or Materialmen's Lien. In the event of nonpayment, you agree to pay all collection costs, including reasonable attorney's fees, in the amount of fifteen percent (15%) of the principal and interest due.
	</div>


	<div class="title_1">
		Returned Check Fee:
	</div>
	<div class="des_1">
		There will be a $30.00 fee charged for all checks returned to our office for non-sufficient funds.
	</div>
	<br />
	<div class="title_1">
		<b>ANSI A300 Tree Care Standard Definitions:</b>
	</div>
	<div class="des_1">
		The following definitions apply to specifications detailed in this proposal.<br />

		<b>clean:</b> Selective pruning to remove one or more of the following parts: dead, diseased, and/or broken branches. Unless noted otherwise on this proposal, all cleaning will be of branches 1inch diameter or greater throughout the entire crown.<br />

		<b>crown:</b> The leaves and branches of a tree measured from the lowest branch on the trunk to the top of the tree.<br />

		<b>leader:</b> A dominant or co-dominant, upright stem.<br />

		<b>raise:</b> Selective pruning to provide vertical clearance.<br />

		<b>reduce:</b> Selective pruning to decrease height and/or spread by removing specified branches.<br />

		<b>restore:</b> Selective pruning to improve the structure, form, and appearance of trees that have been severely headed, vandalized, or damaged.<br />

		<b>thin:</b> Selective pruning to reduce density of live branches, usually by removing entire branches.<br />

		<b>vista pruning:</b> Selective pruning to allow a specific view, usually by creating view “windows” through the tree’s crown.
	</div>
	<?php else: ?>
        <?php echo get_estimate_terms($brand_id); ?>
    <?php endif; ?>

	<?php $brand_address = brand_address($brand_id); ?>
   	<?php if($brand_address): ?>
   		<span class="green">ADDRESS:</span>&nbsp;<?php echo $brand_address; ?>&nbsp;<span class="green">OFFICE:</span>&nbsp;<?php echo brand_phone($brand_id); ?>&nbsp;<span class="green">EMAIL:</span>&nbsp;<?php echo brand_email($brand_id); ?>
    <?php else: ?>
		<div class="address" style="position: absolute; bottom: 20px; right: 0; left: 0;">
			<?php echo $this->config->item('footer_pdf_address'); ?>
		</div>
	<?php endif; ?>
