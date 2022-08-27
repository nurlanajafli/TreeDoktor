<?php 
    $default_img = base_url('assets/'.$this->config->item('company_dir').'/print/header.png');
    $brand_id = get_brand_id($estimate_data, $client_data);
    $estimate_logo = get_brand_logo($brand_id, 'main_logo_file', $default_img);
    $estimate_terms = get_estimate_terms($brand_id);
?>
<pagebreak>
	<div class="holder p_top_" style="margin-left: 342px; margin-top: 0px;"><img src="<?php echo $estimate_logo; ?>" width="482" height="85"
	                       style="<?php echo $this->config->item('company_header_pdf_small_logo_styles'); ?>"    class="p-top-20"></div>
  	<div class="title m_min_50" style="margin-top: -40px;">Tree Care Performance Contract</div>
	<?php if(!strip_tags($$estimate_terms)): ?>
	<br>
	<div class="title_1">
		<strong>Pruning Definitions</strong><br><br>
	</div>
	<div class="des_1">
		<b>Crown Thinning</b> consists of the selective removal of up to 25% of live foliage to increase light penetration, air movement, and reduce weight, resulting in a natural and authentic appearance.<br /><br />
		<b>Crown Raising</b> consists of the removal of the lower branches of a tree to provide pedestrian, vehicular and signage clearance.<br /><br />
		<b>Crown Reduction</b> decreases the height and/or spread of a tree by reducing limbs back to lateral branches that are at least 1/3 the diameter of the parent limb.<br /><br />
		<b>Crown Shaping</b> reduces in length branches that are growing aggressively beyond the canopy of the tree. Finished appearance should be uniform and well groomed.<br /><br />
		<b>Crown Restoration</b> improves the structure, form and appearance of trees that have been severely pruned, vandalized or storm damaged.<br /><br />
		<b>Pollarding</b> is the annual pruning of a tree back to the originally designed heading cuts. The multiple sprouts that originate at these heading cuts will be removed at each pruning.<br /><br />
		<b>Vista Pruning</b> is the selective thinning of framework limbs or specific areas of the crown to allow a view of an object from a predetermined point.<br /><br />
		<b>Training Pruning</b> is performed on juvenile trees and establishes a framework of branches that is structurally sound and characteristic of the species, by removing or correcting inferior limbs.<br /><br />
		<b>Maintenance Pruning</b> is Palm tree pruning consisting of the removal of brown and yellowing fronds, flowers and dates. Certain species have specific pruning parameters as outlined below:

		<ul>
			<li>Canary Island Palms-Only brown and yellowing fronds, flowers and dates will be removed. Weed plants will be removed from the “pineapple” directly below the canopy. Canopy will retain a cascading appearance, as raising the canopy beyond this point may be detrimental to the palm. Chainsaws will not be used, and handsaws will be sterilized in 50/50 bleach and water solution.</li>
			<li>Date Palms, Mexican Fan Palms-Brown and yellowing fronds, flowers and dates will be removed, and the canopy will be raised to the horizon line, or perpendicular to the trunk.</li>
			<li>King, Queen, Royal, Kentia, Fishtail palms-Only brown and yellowing fronds, flowers and dates will be removed. Canopy will retain a natural appearance.</li>
			<li>Pineappling-If pineppling is requested, old “pineapple” base will be raised up to a height where the remaining tissue is firmly attached (Canary Island Palms, Date Palms)</li>
		</ul>

	</div>
	<br>
	<div class="title_1">
		<strong>Terms and Conditions</strong><br><br>
	</div>
		<div class="des_1">
		PCA Arborists & Consultants, Inc., (PCA) was requested to provide an estimate for tree pruning work. No risk assessment is implied or provided as any or part of our scope of work or proposal. Any observations of any tree(s) or work on any tree(s) does not constitute a tree risk assessment. Any observations of any tree(s) or work on any tree(s) does not constitute notice to the client(s) or owner(s). PCA does not provide any category or level of tree risk assessment. Any tree risk assessment must be contracted separate from this scope of work by a qualified tree risk assessor. It is highly recommended that a third-party tree risk assessment, evaluation, inspection or analysis be performed annually, or as recommended, by an ISA tree risk assessor qualified. Any observations of any tree(s) or work on any tree(s) does not constitute that PCA provides notice of defect or abnormality of the tree(s). This proposal is not intended as, and does not represent, legal advice and should not be relied upon to take the place of such advice. <br><br>

		Client/Owner and its agents shall provide access to all parts of the jobsite and all utilities necessary to perform the work. Client/Owner agrees that Contractor will suffer damages if access to the job site and utilities are not provided, including delay damages, demobilization and remobilization costs, extended field and home office overhead, lost profits and loss of use of funds. <br><br>

		Contractor makes no warranties, express or implied, as to the merchantability or fitness for a particular purpose of any trees or shrubs pruned or planted. Contractor shall not be liable to Client/Owner or any other party for loss of property, loss of use, loss of profits, special damages, incidental damages, consequential damages, or damages arising from the failure of any tree, plant or shrub materials or irrigation, including indirect or other similar damages arising from breach of warranty, breach of contract, negligence, or any other legal theory, even if contractor or its agent has been advised of the possibility of such loss and/or damages. In no event shall Contractor's liability exceed the contract price paid to Contractor.<br><br>

		Contractor shall not be liable for its failure to perform due to strikes or other labor difficulties or shortages, judicial action, fire, flood, war, sabotage, terror attack, riot, delays in or unavailability of materials, or any other cause beyond Contractor's control. If Contractor, in its discretion, determines that its performance would result in Contractor’s incurring a loss because of causes beyond Contractor's control, Contractor may terminate this Agreement, without penalty or obligation to Client/Owner. <br><br>

		Contractor reserves the right to hire qualified subcontractors to perform any portion of the work herein, including but not limited to specialized functions or work requiring specialized equipment. <br><br>

		Title of any trees and bushes Contractor plants shall be retained by Contractor until payment in full is received. If payment is not made when due, Contractor may, at its option and without notice, enter the premises and remove the trees and bushes. This reservation of title in Contractor and the right to repossess shall be in addition to any and all other remedies Contractor may have under law or equity. Any and all of the above mentioned remedies may be used at the same time and the use of any of these shall not constitute a waiver of the right to use any other available remedies. <br><br>

		All transactions are to be construed and interpreted in accordance with the laws of the State of California, without regard to conflicts of law. Place of performance is the county where the job site is located. <br><br>

		Client/Owner agree to pay within Thirty (30) Days of date of Invoice. Any delinquent account will bear 1.5 % interest per month, or 18% per year. Client/Owner agree to pay all reasonable collection costs and attorneys' fees incurred in collection of this account. <br><br>

		This Proposal may be accepted only on the Terms and Conditions set out herein, which supersede all previous communications, representations or agreements, whether oral or written, between the parties with respect to the subject matter hereof. Any modification to these Terms and Conditions is unacceptable to Contractor, and is expressly rejected by Contractor, and shall not become a part hereof. <br><br>

		This Proposal shall not be interpreted by any prior course of dealing between the parties.If any term herein is held partially or wholly invalid or unenforceable for any reason, such holding shall not affect, alter, modify or impair any of the other terms, or the remaining portion of any term, held to be partially invalid or unenforceable. <br><br>

		If a dispute arises between the parties that exceeds small claims court jurisdiction, the parties agree to first try in good faith to mediate the dispute with the American Arbitration Association, in Orange County, California. Thirty (30) days before the mediation, the party requesting mediation shall serve on the other a detailed description of the dispute, the facts supporting the dispute, and the amount sought. A party commencing an action without first mediating, or refusing to participate in mediation requested by the other, shall not be entitled to recover attorney fees, even if otherwise available to that party in any such action. If mediation fails to resolve the dispute, the parties agree that any controversy, dispute or claim arising from or relating to this Proposal shall be settled by arbitration in Orange County, California, in accordance with the then existing rules of the American Arbitration Association. Claimant must serve on Respondent with the Demand: a detailed description of the facts and law supporting each claim, a copy of all documents that support each claim, and the names and last known addresses of each witness supporting each claim. Within 45 days of receipt of the above, Respondent must serve on Claimant: a detailed description of each defense known to Respondent at that time, the facts and law upon which each defense is based, a copy of all documents that support each defense, and the names and last known addresses of each witness supporting each defense. The parties agree to keep the following confidential: the fact of the arbitration, all documents exchanged pursuant to the arbitration, the content of any testimony or briefing submitted, and the arbitration award. The arbitrator must provide a written opinion setting forth findings of fact, legal analysis, mathematical calculations and the award, and must follow the applicable California law. Any award contrary to California law may be appealed in a court of competent jurisdiction by any party to the arbitration. The parties agree that the prevailing party, as determined by the arbitrator, will be entitled to recover its reasonable legal fees and costs, including the arbitrator's fees.
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