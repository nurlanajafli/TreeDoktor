<?php 
    $default_img = base_url('assets/'.$this->config->item('company_dir').'/print/header.png');
    $brand_id = get_brand_id($estimate_data, $client_data);
    $estimate_logo = get_brand_logo($brand_id, 'main_logo_file', $default_img);
    $estimate_terms = get_estimate_terms($brand_id);
?>
<pagebreak>
	<div class="holder p_top_" style="margin-left: 342px; margin-top: 0px;"><img src="<?php echo $estimate_logo; ?>" width="482" height="85"
	                       style="<?php echo $this->config->item('company_header_pdf_small_logo_styles'); ?>"    class="p-top-20"></div>
	<div class="title m_min_50" style="margin-top: -40px;">Terms and Conditions</div>
	<br>
	<?php if(!strip_tags($estimate_terms, ['img'])): ?>
	<div class="title_1"><strong>By signing the contract, this an agreement for work to be completed and understanding of terms, conditions and specifications listed below:</strong></div>

	<div class="title_1">  Insurance by Contractor:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?> is insured for liability resulting from injury to person(s) or property and all employees are covered by Workers’ Compensation as required by law. Certificates of coverage are available upon request.
	</div>

	<div class="title_1">Scheduling / Cancelation Fee:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?>. kindly requests that the authorizing party provide at least 24 hours advance notice of any full or partial work cancellation for jobs that have been scheduled in advance. If a crew has been dispatched to the job site for scheduled work, and customer cancels the job, the customer will be assessed a mobilization fee of $150 for incurred expenses.
	</div>

	<div class="title_1">Completion of Contract:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?> agrees to do its best to meet any agreed upon performance dates but shall not be liable in damages or otherwise for delays because of inclement weather, labor, or any other cause beyond its control.
	</div>

	<div class="title_1">Ownership:</div>
	<div class="des_1">
		The authorizing party warrants that all trees listed are located on the customer’s property, or that the authorizing party has received full permission from the owner to allow <?php echo $this->config->item('company_name_long'); ?>. to perform the specified work. Should any tree be mistakenly identified as to ownership, the customer agrees to indemnify <?php echo $this->config->item('company_name_long'); ?>. for any damages or costs incurred from the result thereof.
	</div>

	<div class="title_1">Safety:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?>. warrants that all arboricultural operations will follow the latest version of the ANSI Z133.1 industry safety standards. The authorizing party agrees to not enter the work area during arboricultural operations unless authorized by the crew leader on-site.
	</div>

	<div class="title_1">Stump Removal:</div>
	<div class="des_1">
		Stumps can be removed by utilizing a commercial stump grinder to remove stump to depth of 4-6 inches below ground unless otherwise stated in the proposal. Stump removal is only included if requested. Grindings (mulch) from stump removal can be cleaned up and removed to ground level for an additional fee.
	</div>

	<div class="title_1">Tree Decline or Failure:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?>. will make customer aware of any problems found with trees while on property. It is impossible to know or identify all problems and predict all failures. We will give our best opinion of structural stability and tree health based on visual inspection. Customer acknowledges <?php echo $this->config->item('company_name_long'); ?>. is not be responsible for any decline in tree health or failure of tree or damage caused by failure of any or all of tree after our work is completed.
	</div>

	<div class="title_1">Debris Removal:</div>
	<div class="des_1">
		All debris from tree trimming and tree removal operations shall be reasonably cleaned up each day before the work crew leaves the site, unless otherwise coordinated by the client and crew leader. All lawn area shall be raked, streets and sidewalks shall be swept, and all brush, branches, and logs shall be removed from the site.
	</div>

	<div class="title_1">Concealed Contingencies:</div>
	<div class="des_1">
		Any additional work or equipment required to complete the work, caused by previously unknown foreign material in the trunk, the branches, underground, or any other condition not apparent in estimating the work specified, shall be paid for by the customer on a time and material basis. <?php echo $this->config->item('company_name_long'); ?>. is not responsible for damages to underground sprinklers, drain lines, invisible fences or underground cables unless the system(s) are adequately and accurately mapped by the authorizing party and a copy is presented before or at the time the work is performed. 
		Driveways & Sidewalks:
		<?php echo $this->config->item('company_name_long'); ?>. will attempt to minimize any damage to driveways and sidewalks. If there is a problem that is unknown such as a void area under concrete, or thin spots which cause a failure under the load of our equipment we are not responsible for this damage. If any damage is caused from falling debris as a result of our work, we will take responsibility and correct the damage to the customers satisfaction.
	</div>

	<div class="title_1">Lawn & Surface Repair:</div>
	<div class="des_1">
		<?php echo $this->config->item('company_name_long'); ?>. will attempt to minimize all disturbances to the customer’s lawn and surfaces. It is normal to have some dents in the yard during tree work as a result of falling wood. This is especially true during removals or wet conditions. It is also normal to have temporary damage to turf grasses from cleanup activities. If the customer finds this unacceptable, we can take measures to ensure this damage will not occur. This will add labor costs to the job and must be specified in the estimate or it will be billed in addition. If there is any root damage to turf or perennial beds or any ruts are made in the lawn from our equipment, we will repair damage to the customers satisfaction.
	</div>

	<div class="title_1">Terms of Payment:</div>
	<div class="des_1">
		Unless otherwise noted in this proposal, the customer agrees to pay the account in full upon completion of the work. Failure to remit full payment will result in a finance charge of 1.5% per month and up to 18.99% per year. We understand that not all tree work is voluntary and will work with our customers to develop a finance plan to suit their needs.
	</div>

	<div class="title_1">Types of Payment Accepted:</div>
	<div class="des_1">
		Checks, Cashier’s Check, Credit Cards Including: Visa, MasterCard, Discover & American Express are all accepted. All credit card purchases will be assessed a 5% convenience fee. For our Home Advisor Clients, we gladly accept being paid through the app. Venmo & Cash app are also accepted.
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