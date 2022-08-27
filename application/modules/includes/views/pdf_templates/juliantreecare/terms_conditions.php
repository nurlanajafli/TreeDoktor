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
	<div class="title_1">I. PERFORMANCE BY <?php echo $this->config->item('company_name_long'); ?></div>
	<div class="des_1"><?php echo $this->config->item('company_name_long'); ?> shall perform the work described on the preceding page (the “Work”) in a professional manner by qualified personnel using appropriate tools, equipment and techniques to complete the job properly and safely. All Work will be supervised by an  arborist.
	</div>

	<div class="title_1">II. TIMING</div>
	<div class="des_1">Work crews will arrive at the job site within a 4 hour time frame on the date scheduled. <?php echo $this->config->item('company_name_long'); ?> staff shall use reasonable efforts to meet all performance dates, but shall not be liable for delays due to inclement weather or other causes beyond their control. An estimate of the number of days to complete the Work and an expected start date are provided as a courtesy only. Any delay in beginning or completing the Work shall not alter or invalidate any part of this contract, nor will they entitle Customer to additional rights or remedies.
	</div>

	<div class="title_1">III. OWNERSHIP & PERMITTING</div>
	<div class="des_1">Customer represents and warrants that all trees, plant materials and property upon which Work is to be performed are either owned by Customer or, if not owned by Customer, that the owner of such trees, plant materials and property has authorized Customer to have the Work performed. Customer hereby grants <?php echo $this->config->item('company_name_long'); ?> a license to access and use such trees, plant materials and property in order to perform the Work. Customer shall be responsible for securing any permits or authorizations required to perform the Work and shall provide copies of the same to <?php echo $this->config->item('company_name_long'); ?>.
	</div>

	<div class="title_1">IV. CONTRACT PRICE</div>
	<div class="des_1">The contract price shall be as set out on the preceding page. Any prices specified are valid for up to 3 months from the date of the estimate. If  <?php echo $this->config->item('company_name_long'); ?> is required to perform Work in circumstances other than those expressly or reasonably assumed and normally pertaining to work of a similar nature, including any site conditions that were not known to <?php echo $this->config->item('company_name_long'); ?> at the time the quotation was provided, or if there is a change in the scope, timing or complexity of the Work, <?php echo $this->config->item('company_name_long'); ?> shall promptly notify Owner and the parties shall agree on an equitable adjustment to the contract price.
	</div>

	<div class="title_1">V. TERMS OF PAYMENT</div>
	<div class="des_1">The invoice is due upon receipt and interest will begin to accrue on any unpaid balance 30 days after the invoice date, at a rate of 2% per month. If Customer has provided a credit card number to <?php echo $this->config->item('company_name_long'); ?>, Customer authorizes <?php echo $this->config->item('company_name_long'); ?> to charge unpaid amounts to such a credit card when due. Payment of any amount shall not be construed as acceptance by Customer of defective Work, and Customer agrees not to withhold any amount invoiced in accordance with this contract for any reason whatsoever. Failure by Customer to pay any amount within 30 days after the invoice date shall constitute a breach of this contract, and in addition to paying <?php echo $this->config->item('company_name_long'); ?> all amounts due hereunder, Customer shall indemnify <?php echo $this->config->item('company_name_long'); ?> for all costs incurred in collecting such unpaid amounts.
	</div>

	<div class="title_1">VI. TERMINATION</div>
	<div class="des_1">This contract may be cancelled by Customer by mailing written notice to <?php echo $this->config->item('company_name_long'); ?> no later than 14 days before Work is scheduled to commence. In the event of any such termination, Customer shall forfeit the deposit. Customer may only terminate the Work less than 14 days before the Work is scheduled to commence by paying <?php echo $this->config->item('company_name_long'); ?> for any Work already performed and for any expenses incurred in preparing to perform the Work.
	</div>

	<div class="title_1">VII. MINOR DAMAGES</div>
	<div class="des_1"><?php echo $this->config->item('company_name_long'); ?> will always use reasonable care in the performance of the Work but shall not be liable for the damages to lawns, plants, ground covers and dilapidated structures, when performing tree services on large and dangerous trees. <?php echo $this->config->item('company_name_long'); ?> will not be liable for damages to underground fixtures when doing stump grinding and similar services. It is the client's responsibility to obtain mapping for underground infrastructure . If for any reason Customer is not 100% satisfied with the Work, they have 10 business days after completion to raise their concerns. No complaints will be accepted after that.</div>

	<div class="title_1">VIII. INSURANCE AND LIABILITY</div>
	<div class="des_1">In no event shall <?php echo $this->config->item('company_name_long'); ?> be liable to Customer, irrespective of whether alleged to be by way of a result of breach of contract, tort, (including negligence and strict liability), or any other legal theory, and whether arising before or after the Work, for, and Customer hereby waives any right to, damages that constitute incidental, indirect, exemplary or consequential damages of any nature whatsoever. Customer shall indemnify, defend and hold <?php echo $this->config->item('company_name_long'); ?> harmless from and against any losses (including legal fees on a full indemnity basis), attributable to or arising out of any breach by Customer of its obligations in this contract. This paragraph shall survive the expiry or any termination of this contract.
	</div>

	<div class="title_1">IX. MISCELLANEOUS</div>
	<div class="des_1">This contract contains the entire understanding of <?php echo $this->config->item('company_name_long'); ?> and Customer with respect to the Work and reflects the prior agreements and commitments with respect thereto. There are no other oral understandings, terms or conditions and neither Party has relied upon any representation, express or implied, not contained in this contract. This contract may only be amended by a written amending agreement signed by both <?php echo $this->config->item('company_name_long'); ?> and the Customer. No waiver by either party of any breach or provision of this contract will be binding unless made in writing and any such waiver will extend only to the specific breach or provision waived and not to any future breach. Indemnities against, releases from and limitations and exclusions on liability expressed in this contract will apply even in the case of the fault, negligence or strict liability of the party indemnified or released or whose liability is limited, and will extend to the benefit the officers, directors, employees, agents, representatives, subcontractors and affiliates of such parties.
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
