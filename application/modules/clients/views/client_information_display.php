<!-- Client Information Display-->
<?php if(isset($client_data) && !empty((array)$client_data)): ?>

<?php $this->load->view('clients/client_create_tags'); ?>

<link href="<?php echo base_url('assets/css/star-rating.min.css'); ?>" media="all" rel="stylesheet" type="text/css" />

<input type="hidden" name="client_address" value="<?php echo $client_data->client_address; ?>">
<input type="hidden" name="client_city" value="<?php echo $client_data->client_city; ?>">
<input type="hidden" name="client_state" value="<?php echo $client_data->client_state; ?>">			
<input type="hidden" name="client_zip" value="<?php echo $client_data->client_zip; ?>">
<input type="hidden" name="client_country" value="<?php echo $client_data->client_country; ?>">
<input type="hidden" name="client_lat" value="<?php echo $client_data->client_lat; ?>">
<input type="hidden" name="client_lon" value="<?php echo $client_data->client_lng; ?>">

<div class="row pos-rlt">
	<div class="col-md-9 col-sm-12 col-xs-12">
		<section class="panel panel-default p-n poser" style="min-height: 310px; overflow: hidden;">
			<header class="panel-heading" style="position: relative;">
                <a href="#mapModal" class="btn btn-default btn-mini pull-left btn-xs hidden-md hidden-lg m-r-sm" data-toggle="modal">
                    <i class="fa fa-map-marker"></i>
                </a>

                Client Profile

				<a href="#" class="important-note-indicator m-l-xs text-danger" style="display: none;">
					<i class="fa fa-warning" title="Has Important Notes" style="font-size: 17px;"></i>
				</a>

				<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('CL') == 1) { ?>
					<a href="#clientUpdateModal" role="button" class="btn btn-default btn-xs btn-mini pull-right"
					   data-toggle="modal"><i class="fa fa-pencil"></i></a>
				<?php } ?>
				<?php if (isAdmin()) : ?>
					<a href="#deleteClient" role="button" class="btn btn-xs btn-mini btn-danger pull-right m-r-xxxxl"
					   data-toggle="modal" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-trash-o"></i>
                    </a>
				<?php endif; ?>
                <?php $this->load->view('qb/partials/qb_logs', ['lastQbTimeLog' => $client_data->client_last_qb_time_log, 'lastQbSyncResult' => $client_data->client_last_qb_sync_result, 'module' => 'client', 'entityId' => $client_data->client_id, 'entityQbId' => $client_data->client_qb_id, 'class' => 'pull-right m-right-10']); ?>

                <?php if (isset($reffered_leads) && $reffered_leads) : ?>
					<a href="#reffList" role="button" class="btn btn-xs btn-mini btn-info pull-right m-r"
					   data-toggle="modal" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-list-alt"></i>
                    </a>
				<?php endif; ?>

                <div class="client-tax-text pull-right m-r" style="color: #888; cursor: default;">
                    <?php $clientTax = get_client_tax_text($client_data); ?>
                    Tax: <?php echo $clientTax['taxText'] ?>
                </div>

				<div class="<?php if ($client_data->client_type == 1) {
					echo "icon_residential";
				} ?> <?php if ($client_data->client_type == 2) {
					echo "icon_corp";
				} ?> <?php if ($client_data->client_type == 3) {
					echo "icon_municipal";
				} ?>"></div>
			</header>
            <!-- Client body -->
			<div class="pos-rlt">
				<div class="pull-left p-xl client_address_block col-md-9 col-lg-9 col-sm-12 col-xs-12" style="display:inline-block;height: 268px;">
                    <h3 class="clientName">
						<?php echo anchor( $client_data->client_id, $client_data->client_name); ?>
						<div class="rating-stars" data-content="★★★★★★★★★★" style="width: 0%;display: inline-block;"></div>
						<input id="input-stars" class="rating" data-min="0" data-max="10" data-step="1" data-stars=10 data-size="xs" data-glyphicon="false">
						<?php if ($client_data->client_unsubscribe) : ?>
							<div class="stamp_css">Unsubscribed</div>
						<?php endif; ?>
					</h3>
                    
					<?php /*if ($client_data->client_contact) { ?><span class="muted">
						<strong>Attn:&nbsp;<?php echo $client_data->client_contact; ?></strong></span><?php }*/ ?>
				<div style="display: flex; line-height: 20px; overflow-y: auto; height: 145px; position: absolute; overflow-x: auto; left: 20px; top: 80px; right: 20px;" class="client-info m-t-md">
					<table class="m-r-lg m-t-xs">
						<tr>
							<td class="text-left">
								<strong>Client's Address</strong>
							</td>
						</tr>
						<tr>
							
							<td valign="top" class="client_address_block_<?php echo $client_data->client_id; ?>">
                                <?php $addressFields = [
                                        'stump_address' => $client_data->client_address,
                                        'stump_city' => $client_data->client_city,
                                        'stump_state' => $client_data->client_state,
                                        'stump_zip' => $client_data->client_zip,
                                        'stump_country' => $client_data->client_country,
                                        'stump_lat' => $client_data->client_lat,
                                        'stump_lon' => $client_data->client_lng,
                                        'stump_main_intersection' => $client_data->client_main_intersection,
                                ]; ?>
								<a href="#" data-name="client_address" data-value='<?php echo json_encode($addressFields, JSON_HEX_APOS|JSON_HEX_QUOT); ?>' data-placement="right" data-type="address" data-pk="<?php echo $client_data->client_id; ?>" class="stump_address" title="Client Address" data-url="<?php echo base_url('clients/ajax_change_address'); ?>">
									<?php echo $client_data->client_address; ?><br>
									<?php echo $client_data->client_city; ?>&#44;&nbsp;<?php echo $client_data->client_state; ?>
									<br>
									<?php echo $client_data->client_country; ?>&nbsp;<?php echo $client_data->client_zip; ?>
                                    <br>
                                    <?php echo $client_data->client_main_intersection; ?>
								</a>
                                <?php if(((isset($estimate_data->lead_add_info) && $estimate_data->lead_add_info != '') ||
                                    (isset($row['lead_add_info']) && $row['lead_add_info'] != '')) &&
                                    ($this->uri->segment(2) != 'new_estimate' && $this->uri->segment(2) != 'edit')) : ?>
                                <small>
                                    <strong>
                                        <?php echo $estimate_data->lead_add_info ?? $row['lead_add_info']; ?>
                                    </strong>
                                </small>
                                <?php endif; ?>
							</td>
						</tr>
					</table>

					<?php if ((isset($lead->lead_address) && ($this->uri->segment(2) == 'new_estimate' || ($this->uri->segment(2) == 'edit' && $this->router->fetch_class()  == 'estimates'))) || (isset($lead->lead_address) && $lead->lead_address != $client_data->client_address) || (isset($estimate_data->lead_address) && $estimate_data->lead_address != $client_data->client_address) || isset($row['lead_address'])) : ?>
						<table class="m-r-lg m-t-xs">
							<tr>
								<td class="text-left">
									<strong>Job Site Location</strong>
								</td>
							</tr>
							<tr>
                                <?php if (isset($lead->lead_address) /*&& $lead->lead_address != $client_data->client_address*/) { ?>
                                    <td valign="top" class="lead_address_block_<?php echo $lead->lead_id; ?>">
                                        <?php $addressFields = [
                                            'stump_address' => $lead->lead_address,
                                            'stump_city' => $lead->lead_city,
                                            'stump_state' => $lead->lead_state,
                                            'stump_zip' => $lead->lead_zip ?? $client_data->client_zip,
                                            'stump_country' => $client_data->client_country,
                                            'stump_lat' => $lead->latitude,
                                            'stump_lon' => $lead->longitude,
                                            'stump_add_info' => $lead->lead_add_info
                                        ]; ?>
                                        <a href="#" data-name="lead_address" data-value='<?php echo json_encode($addressFields, JSON_HEX_APOS|JSON_HEX_QUOT); ?>' data-placement="right" data-type="address" data-pk="<?php echo $lead->lead_id; ?>" class="stump_address" title="Client Address" data-url="<?php echo base_url('clients/ajax_change_address'); ?>">
                                            <?php echo $lead->lead_address; ?><br><?php echo $lead->lead_city; ?>&#44;&nbsp;
                                            <?php //echo $lead->lead_state; ?><br>
                                            <?php echo $client_data->client_country; ?><br>
                                            <?php echo $lead->lead_zip ?? $client_data->client_zip; ?><br>
                                            <?php echo $lead->lead_add_info ?? $client_data->client_main_intersection; ?>
                                        </a>
										</td>
                                <?php }  elseif (isset($estimate_data->lead_address) && $estimate_data->lead_address != $client_data->client_address) { ?>
										<td valign="top" class="lead_address_block_<?php echo $estimate_data->lead_id; ?>">
                                            <?php $addressFields = [
                                                'stump_address' => $estimate_data->lead_address,
                                                'stump_city' => $estimate_data->lead_city,
                                                'stump_state' => $estimate_data->lead_state,
                                                'stump_zip' => $estimate_data->lead_zip ?? $client_data->client_zip,
                                                'stump_country' => $client_data->client_country,
                                                'stump_lat' => $estimate_data->lat,
                                                'stump_lon' => $estimate_data->lon,
                                                'stump_add_info' => $estimate_data->lead_add_info
                                            ]; ?>
                                            <a href="#" data-name="lead_address" data-value='<?php echo json_encode($addressFields, JSON_HEX_APOS|JSON_HEX_QUOT); ?>' data-placement="right" data-type="address" data-pk="<?php echo $estimate_data->lead_id; ?>" class="stump_address" title="Client Address" data-url="<?php echo base_url('clients/ajax_change_address'); ?>">
                                                <?php echo $estimate_data->lead_address; ?><br><?php echo $estimate_data->lead_city; ?>&#44;&nbsp;
                                                <?php echo $estimate_data->lead_state; ?><br>
                                                <?php echo $client_data->client_country; ?>
                                                <?php echo isset($estimate_data->lead_zip) ? $estimate_data->lead_zip : $client_data->client_zip;  ?><br>
                                                <?php echo isset($estimate_data->lead_add_info) ? $estimate_data->lead_add_info : $client_data->client_main_intersection;  ?>
                                        </a>
                                    </td>
                                <?php } elseif (isset($row['lead_address'])) { ?>
                                    <td valign="top" class="lead_address_block_<?php echo $row['lead_id']; ?>">
                                        <?php $addressFields = [
                                            'stump_address' => $row['lead_address'],
                                            'stump_city' => $row['lead_city'],
                                            'stump_state' => $row['lead_state'],
                                            'stump_zip' => $row['lead_zip'] ?? $client_data->client_zip,
                                            'stump_country' => $client_data->client_country,
                                            'stump_lat' => $row['latitude'],
                                            'stump_lon' => $row['longitude'],
                                            'stump_add_info' => $row['lead_add_info']
                                        ]; ?>
                                        <a href="#" data-name="lead_address" data-value='<?php echo json_encode($addressFields, JSON_HEX_APOS|JSON_HEX_QUOT); ?>' data-placement="right" data-type="address" data-pk="<?php echo $row['lead_id']; ?>" class="stump_address" title="Client Address" data-url="<?php echo base_url('clients/ajax_change_address'); ?>">
                                            <?php echo $row['lead_address']; ?><br><?php echo $row['lead_city']; ?>&#44;&nbsp;
                                            <?php echo $row['lead_state']; ?><br>
                                            <?php echo $client_data->client_country; ?>
                                            <?php echo isset($row['lead_zip']) ? $row['lead_zip'] : $client_data->client_zip;  ?><br>
                                            <?php echo isset($row['lead_add_info']) ? $row['lead_add_info'] : $client_data->client_main_intersection;  ?>
                                        </a>
                                    </td>
                                <?php } ?>
							</tr>
						</table>
					<?php endif; ?>
					<?php $this->load->view('client_information_display_contact'); //create form ?>
					<div class="m-l-sm m-t-lg">
						<a href="#" data-name="client_cont" data-cc_client_id="<?php echo $client_data->client_id; ?>" data-placement="right" data-type="contact" data-pk="" class="btn btn-success btn-xs client_contact" title="Client Contact" data-url="<?php echo base_url('clients/ajax_save_client_contact'); ?>">
							<i class="fa fa-plus"></i>
						</a>
					</div>
				</div>
				</div>
				<div class="pull-right pos-abt b-l hidden-xs hidden-sm" style="width: 25%;right: 0px; top: 0; bottom: 0;">
					<div class="papers-block b-b" style="height: 55% !important;">
						<?php if(isset($client_papers) && !empty($client_papers)) : ?>
							<?php foreach($client_papers as $k=>$v) : ?>
								<div class="client-paper p-5">
									<?php if ($this->session->userdata('user_type') == "admin") : ?>
										<i class="fa fa-times text-danger pull-right deletePaper"
                                           data-paper_id="<?php echo $v['cp_id']; ?>" style="cursor:pointer;"></i>
									<?php endif; ?>
									<span style="color: #000;"><?php echo $v['cp_text']; ?>, </span>									
									<small class="text-muted"><?php echo $v['emailid']; ?>, <?php echo getDateTimeWithDate($v['cp_date'], 'Y-m-d'); ?></small>
									<div class="line"></div>
								</div>
								
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<textarea class="form-control paperText" data-client_id="<?php echo $client_data->client_id; ?>" placeholder="To save note press Ctrl+Enter" style="height: 45% !important; overflow-y: auto;"></textarea>
				</div>
				<div class="clear"></div>
			</div>
		</section>
	</div>
    <div class="col-md-3 pos-abt hidden-sm hidden-xs client-profile-map">
        <!--map-->
        <?php if($map): ?>
            <a href="#mapModal" class="initClientMap" data-toggle="modal">
        <?php endif; ?>
            <?php $mapKey = '&key=' . $this->config->item('gmaps_key'); ?>
            <?php if (((isset($invoice_data) || isset($workorder_data)) && !isset($estimate_contacts)) || isset($estimate_data)): ?>
                <?php $tags = str_replace(array(' ', '#'), array('+', ''), $estimate_data->lead_address); ?>
                <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $tags; ?>,<?php echo $estimate_data->lead_city; ?>,<?php echo $estimate_data->lead_state; ?>,<?php echo $client_data->client_country; ?>,<?php echo str_replace(' ', '+', $estimate_data->lead_zip); ?>&zoom=11&size=500x500&markers=<?php echo $tags; ?>,<?php echo $estimate_data->lead_city; ?>,<?php echo $estimate_data->lead_state; ?>,<?php echo $client_data->client_country; ?>,<?php echo str_replace(' ', '+', $estimate_data->lead_zip); ?><?php echo $mapKey; ?>"
                     alt="Google Map" width="100%">
            <?php elseif (isset($lead)): ?>
                <?php $tags = str_replace(array(' ', '#'), array('+', ''), $lead->lead_address); ?>
                <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $tags; ?>,<?php echo $lead->lead_city; ?>,<?php echo $lead->lead_state; ?>,<?php echo $client_data->client_country; ?>,<?php echo str_replace(' ', '+', $lead->lead_zip); ?>&zoom=11&size=500x500&markers=<?php echo $tags; ?>,<?php echo $lead->lead_city; ?>,<?php echo $lead->lead_state; ?>,<?php echo $client_data->client_country; ?>,<?php echo str_replace(' ', '+', $lead->lead_zip); ?><?php echo $mapKey; ?>"
                     alt="Google Map" width="100%">
            <?php else: ?>
                <?php $tags = str_replace(array(' ', '#'), array('+', ''), $client_data->client_address); ?>
                <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $tags; ?>,<?php echo $client_data->client_city; ?>,<?php echo $client_data->client_state; ?>,<?php echo $client_data->client_country; ?>,<?php echo str_replace(' ', '+', $client_data->client_zip); ?>&zoom=11&size=500x500&markers=<?php echo $tags; ?>,<?php echo $client_data->client_city; ?>,<?php echo $client_data->client_state; ?>,<?php echo $client_data->client_country; ?>,<?php echo str_replace(' ', '+', $client_data->client_zip); ?><?php echo $mapKey; ?>"
                     alt="Google Map" width="100%">
            <?php endif; ?>
        <?php if ($map): ?>
            </a>
        <?php endif; ?>
    </div>
</div>
<!-- /Client information display-->
	<div id="mapModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<div class="modal-body form-horizontal"  style="height:500px;">
					<?php if(isset($map['html'])): ?>
                        <div class="mapper">
                            <?php echo $map['html']; ?>
                        </div>
					<?php else: ?>
                        <h2 class="text-center">MAP NOT FOUND</h2>
					<?php endif; ?>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
<?php if (isAdmin()) : ?>
	<div id="deleteClient" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Confirm Delete Client "<?php echo $client_data->client_name; ?>"</header>
				<div class="modal-body form-horizontal">
					<div class="control-group">
						<label class="control-label">Your Password</label>

						<div class="controls">
							<input id="yourPassword" class="form-control" type="password" placeholder="Your Password"
							       value="">
							<span class="help-inline"></span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					<button class="btn btn-danger" id="confirmDelete" data-dismiss="modal" aria-hidden="true"
					        data-client_id="<?php echo $client_data->client_id; ?>">Delete
					</button>
				</div>
			</div>
		</div>
	</div>
	<div id="reffList" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Reffered Leads by <?php echo $client_data->client_name; ?></header>
				<div class="modal-body form-horizontal">
					<?php if(isset($reffered_leads) && $reffered_leads) :?>
						<table class="table"> 
							<thead>
								<tr>
									<th>Lead Id</th>
									<th>Client Name</th>
									<!--<th>Discount</th>-->
								</tr>
							</thead>
							<?php foreach($reffered_leads as $key=>$val) : ?>
								<tr>
									<td><?php echo $val->lead_no; ?></td>
									<td><?php echo $val->client_name; ?></td>
									<!--<td>
										<input class="form-control" type="text" placeholder="Discount" value="">
									</td>-->
								</tr>
									
							<?php endforeach; ?>
						</table>
					<?php endif; ?>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<script>
    var isAdmin = <?php echo isAdmin() ? 'true' : 'false'; ?>;
    let clientRating = <?php echo json_encode($client_data->client_rating); ?>;
    const clientId = <?php echo json_encode($client_data->client_id); ?>;
    const mapAddress = '<?php echo addslashes($tags); ?>,<?php echo addslashes($client_data->client_city); ?>,<?php echo addslashes($client_data->client_state); ?>,<?php echo addslashes($client_data->client_country); ?>';
    const officeLocation = '<?php echo config_item('office_location'); ?>';
    const ccTpls = {
        tpl_<?php echo $client_data->client_id; ?>: `<?php $this->load->view('clients/client_cc_edit', ['client_data' => $client_data]); ?>`
    };
</script>

<script src="<?php echo base_url('assets/js/star-rating.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/tinymce/tinymce.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/clients/client_information_display.js?v=1.02'); ?>"></script>

<?php endif ;?>
