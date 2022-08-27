<?php
    $default_img = base_url('assets/'.$this->config->item('company_dir').'/print/header.png');
    $brand_id = get_brand_id($estimate_data, $client_data);
    $estimate_logo = get_brand_logo($brand_id, 'main_logo_file', $default_img);
    $estimate_terms = get_estimate_terms($brand_id);
?>
<pagebreak>
	<div class="holder p_top_" style="margin-left: 342px; margin-top: 0px;"><img src="<?php echo $estimate_logo; ?>" height="55"
	                       style="<?php echo $this->config->item('company_header_pdf_small_logo_styles'); ?>"    class="p-top-20"></div>
	<div class="title m_min_50" style="margin-top: -40px;">Terms &amp; Conditions</div>
	<br>
	<?php if(!$estimate_terms): ?>
	<div class="title_1">PAYMENT TERMS:</div>
	<div class="des_1">
		<p>Payment is due upon the date of completion of TreeNewal, LLC services.</p>
		<p>Unpaid balances will incur a monthly finance charge up to 6% until balance is paid in full. Finance charges will incur after 10 days of unpaid balance, retroactive to the date of invoice.</p>
		<p>All services are subject to the appropriate sales tax. If tax exempt, please email either a signed Tax Exemption Certificate or a Resale Certificate to <a href="mailto:<?php echo $this->config->item('account_email_address'); ?>" target="_blank"><?php echo $this->config->item('account_email_address'); ?></a> <b>at time of acceptance of estimated work</b>.</p>
		<p>A 50% down payment <b>may</b> be required prior to the start of any job that requires the purchase of job specific materials.</p>
		<p>Due to our guarantee of services performed, it is specifically understood and agreed upon that no warranty work will be performed until all outstanding amounts owed to TreeNewal, LLC have been paid in full by the customer. (See EXCLUSIONS and LIMITED WARRANTIES below)</p>
	</div>

	<div class="title_1">FORMS OF PAYMENT (CHECK BOX BELOW):</div>
	<div class="des_1">
		<p>
			<input type="checkbox">  ACH Bank Transfer online<br>
			<input type="checkbox">  Credit Card online (If paying by credit or debit card, there is a 3.75% processing charge applied to the total invoice for all services.)<br>
			<input type="checkbox">  Check  (Payable to: TreeNewal, LLC. Please give the check to the Lead Crew Member on-site.)<br>
			<input type="checkbox">  Cash  (Please give the cash to the Lead Crew Member on-site and Call, Text or Email advising of Cash payment to: Kimberly Hill at 817-946-0389 or k.hill@treenewal.com)
		</p>
	</div>

	<div class="title_1">PROPERTY ACCESS:</div>
	<div class="des_1">
		<p>Customer is responsible for ensuring TreeNewal, LLC’s full access to customer property prior to scheduled work or service commencing. This includes, but not limited to providing correct gate codes and/or prior clearance with on-site security personnel. Failure to provide TreeNewal, LLC full access to to customer property prior to scheduled work or services will  result in a trip fee charge for each occurrence.</p>
		<p>If there are trees and/or shrubs overhanging neighboring properties, TreeNewal, LLC will need access to your neighboring properties in order to perform the work and remove created debris. Customer’s assistance in obtaining permission to access neighboring properties will be needed prior to work and services commencing.</p>
	</div>

	<div class="title_1">CUSTOMER OUTDOOR BELONGINGS, VEHICLES AND PETS:</div>
	<div class="des_1">
		<p>Customer is responsible for clearance of the work area and surroundings areas of all outdoor items, furniture and accessories. TreeNewal, LLC is not responsible for damage to said items not removed. While our team members are observant and attentive of obstacles in the work area, we cannot be responsible for moving these items prior to commencing work.</p>
		<p>Customer is responsible for removing all vehicles from the work area and surrounding areas prior to work or services commencing.</p>
		<p>Customer is responsible for securing all pets away from the work area and surroundings areas prior to work or services commencing.  It is best to secure pets indoors.</p>
	</div>

	<div class="title_1">CLEAN UP AND DEBRIS REMOVAL:</div>
	<div class="des_1"><p>All created debris will be removed from the job site. Permission from your neighbors to access their property may be needed to remove debris, in these cases your assistance in obtaining said permission will be needed prior to work and services commencing. Created debris will be removed from pools and spas, however a more thorough cleaning of the system may be required by a pool service provider at customer’s expense.</p>
	</div>

	<div class="title_1">EXCLUSIONS:</div>
	<div class="des_1"><p>TreeNewal, LLC cannot be held responsible for the following:</p>
		<div style="padding-left:15px;"><p>A. Underground utilities: Electrical, Plumbing, Cable and/or Gas, Irrigation System and Wiring, rocks or any other non-visible obstruction encountered below ground or contained within trees (foam or concrete in cavities, metal objects embedded in the tree trunk and/or branches) during the performance of our services.</p>
			<p>TreeNewal, LLC will call Texas 811 for Line Locate to reduce chances of hitting underground public utility lines.</p>
			<p>B. <b>Private Lines:</b>  Texas 811 Line Locate will NOT mark private lines.  Private lines include: Irrigation Systems, Gas, Electric and Cable lines to spas, outdoor living areas, BBQ Grills, workshops, etc.  <font style="color: red;"><b>It is the customer’s responsibility to have these lines clearly marked prior to work or services commencing.</b></font></p>
			<p>C. Disconnection of power supply to the home, landscape, and/or security lighting systems.</p>
			<p>D. Improperly installed or hanging utility lines.</p>
		</div>

		<p>Damage to vehicles. Residential Customers must remove all vehicles from the work area and surrounding areas prior to work or services commencing. Commercial Business or Multi-Family Communities are responsible for the movement of employee, customer or tenant owned vehicles prior to the work commencing.  In the event vehicles are not moved, an additional charge for wait time or rescheduling will be assessed.</p>
		<p>Information contained within this Estimate and/or Report is limited to only those trees/plants that were examined and reflects the condition of those trees/plants at the time of inspection.</p>
		<p>The inspection is limited to visual examination from the ground of accessible trees/plants without probing, coring, or excavation.</p>
		<p>There is no guarantee or warranty, expressed or implied, that issues or failure of the trees/plants serviced in this Estimate may not develop in the future.</p>
	</div>

	<div class="title_1">LIMITED WARRANTIES:</div>
	<div class="des_1"><p>TreeNewal, LLC does not warrant damage from causes such as insects, improper watering, improper drainage, any Act of God, falling objects, vehicle or other mechanical damage, or vandalism.</p></div>

	<div class="title_1">PLANTED MATERIALS:</div>
	<div class="des_1"><p>NO WARRANTY.</p>
	</div>

	<div class="title_1">CABLING:</div>
	<div class="des_1">
		<p>At no charge to the customer, any support cabling installed in a tree that should fail (other than Acts of God) during the first 12 months from installation date will be replaced.  Cables must be inspected every 12 months from installation date for structural integrity. lt is the customer's responsibility to contact TreeNewal, LLC to schedule inspection.</p>
		<p>TreeNewal, LLC honors any service warranty request that is relevant to the original scope of work and falls within the terms of Limited Warranties. A warranty request that exceeds the original scope of work, is cost prohibitive, and/or does not fall within terms of Limited Warranties will require a new Estimate for approval and payment of services.</p>
	</div>
	<div class="title_1">ACCEPTANCE OF ESTIMATE:</div>
	<div class="des_1">
		<p>I agree to the work and services described and to the TERMS & CONDITIONS within this Estimate. Any changes or alterations to, or deviation from the detailed work and services described in this Estimate which is agreed upon, and involves extra cost of material and/or additional labor; will become an extra charge over and above this Estimate.</p>
		<p>All services are invoiced upon completion with appropriate sales tax, and is due upon completion of services. All invoices are considered past due after 10 days and will incur a monthly finance charge up to 6% of balance until balance is paid in full. Fiance charges will incur after 10 days of unpaid balance, retroactive to the date of invoice.</p>
		<p>This Estimate may be withdrawn by TreeNewal, LLC if not accepted within 30 days.</p>
	</div>
	<div class="title_1">CUSTOMER SATISFACTION:</div>
	<div class="des_1"><p>TreeNewal, LLC values you as our customer. A positive experience with TreeNewal, LLC is our first priority.  Please share any concerns with us promptly so that we can address immediately.  We require notice of any concerns by calling 817-329-2450 or emailing <a href="mailto:<?php echo $this->config->item('account_email_address'); ?>" target="_blank"><?php echo $this->config->item('account_email_address'); ?></a> within one week of completion date of the work and services described within this Estimate.</p>
	</div>
	<div class="title_1">WE LOVE REFERRALS!</div>
	<div class="des_1"><p>Please refer your family and friends in need of a Knowledgeable Sustainable Tree Care Company.</p>
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
