<?php $this->load->view('includes/header'); ?>
<?php /*
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.css" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/theme.css" type="text/css" />
*/ ?>
    <style>
        .row:before {content:""; display: block;}
        ul.select2-choices {
            padding-right: 30px !important;
        }
        ul.select2-choices:after {
            content: "";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border-top: 5px solid #333;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
        }

        .items-div .select2-container.select2-container-multi {
            margin-bottom: -3px;
        }
        .select2-container.select2-container-multi.est_services .select2-choices {
            width: 100% !important;
        }

        .search-result {
            width: 100% !important;
            display: inline-grid !important;
            margin-top: 5px !important;
        }
        .showSearch {
            position: fixed;
            /*float: right;*/
            text-align: center;
            bottom: 20px;
            /*right: 20px;*/
            width: 50px;
            height: 50px;
            border: 3px solid #000;
            border-radius: 28px;
            cursor: pointer;
            z-index: 1001;
        }
        @keyframes glowing {
            0% { background-color: #fb6b5b; box-shadow: 0 0 3px #fb6b5b; }
            50% { background-color: #b54336; box-shadow: 0 0 8px #b54336; }
            100% { background-color: #fb6b5b; box-shadow: 0 0 3px #fb6b5b; }
        }
        .blink {
            animation: glowing 2500ms infinite;
        }
    </style>
    <!-- format fone numbers -->
    <script>
        // set default tax
        let OLD_ADDRESS;

        var contact_tpl = <?php echo $contact_tpl; ?>;

        function format(obj) {
            if (obj.value.length > 10) {
                obj.value = obj.value.replace(/(\d{3})(\d{3})(\d{4})/, '' + '$1' + '.' + '$2' + '.' + '$3' + ' Ext ');
            } else {
                obj.value = obj.value.replace(/(\d{3})(\d{3})/, '' + '$1' + '.' + '$2' + '.');
            }

        }


        $(document).ready(function(){
            $("#new_add").change(function(){
                if($(this).prop('checked')==true){
                    $("#lead-address-container input").removeAttr("disabled");
                }
                else{
                    $("#lead-address-container input").attr("disabled", "disabled");
                }

            });

            $('#new_add').trigger('change');
        });

        $(document).ready(function () {
            var client_source = $('#new_client_source').val();
            if (client_source == 'Referred by another client') {
                $('#reference_client_name_row').show();
            }
        });

        function show_reference_person_row(val) {
            if (val == 'Referred by another client') {
                $('#reference_client_name_row').show();
            } else {
                $('#reference_client_name_row').hide();
            }
        }


        function validateEmail(email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }

        function checkEmail(email, obj)
        {
            if(!email) {
                return false;
            }
            if(!validateEmail(email)) {
                return false;
            }

            $.ajax({
                url: baseUrl + 'clients/check_client_email',
                data: {email: email},
                dataType: 'json',
                global:false,
                method:'POST',
                success: function(resp){

                    if(resp.status == 'ok') {
                        $(obj).css('background-color', '#dff0d8');
                        $(obj).parent().find('.client-email-checker').val('1');
                    }
                    if(resp.status == 'invalid') {
                        $(obj).css('background-color', '#ff8181');
                        $(obj).parent().find('.client-email-checker').val('0');
                    }
                    if(resp.status == 'unverifiable') {
                        warningMessage('The Email is Unverifiable');
                        $(obj).css('background-color', '#fdff81');
                        $(obj).parent().find('.client-email-checker').val('');
                    }
                    return false;
                }
            });

            return false;
        }

        $(document).ready(function () {
            $('.client-phone').inputmask(PHONE_NUMBER_MASK);
            $('body').on('click', function (e) {
                $('[data-toggle="popover"]').each(function () {
                    //the 'is' for buttons that trigger popups
                    //the 'has' for icons within a button that triggers a popup
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });

            $('.contact-section:first').find('input').css('background-color', '#dff0d8');

            $('input[name="new_client_name"]').keyup(function(){
                $('.contact-section:first').find('input[name="client_name[]"]').val($(this).val());
            });

            $('.client-email').keyup(function(e) {
                $(this).parent().find('.client-email-checker').val('');
                $(this).css('background-color', '#fdff81');
                if(validateEmail($(this).val()))
                    $(this).next().removeAttr('disabled');
                else
                    $(this).next().attr('disabled', 'disabled');
            });

            /*----------ajax check ----------*/
            $('.client-phone').change(function(){
                checkContact($(this).parents('.profile-table:first'));
            });

            $(document).on('click', '.change-title', function(){
                var title = $(this).parent().find('#contact-title').val();
                //if()
                $(this).parents('.contact-section:first').find('.contact-title-link').html(title);
                $(this).parents('.contact-section:first').find('input.client-title-value').val(title);
            });

            $('.new_client_address, .locality').change(function(){
                setTimeout(function(){
                    checkAddress();
                }, 500);

            });


            $('.client-email').change(function(){
                checkEmail($(this).val(), $(this));
                checkContact($(this).parents('.profile-table:first'));
            });

            /*$('.client-phone').change(function(){
                format(this);
                checkContact($(this).parents('.profile-table:first'));
            });*/

            /*$('#email2').change(function(){
                checkEmail($('#email2').val(), 'email2');
                checkContact();
            });*/
            /*----------ajax check ----------*/


            function checkContact(obj){
                return false;
                var phone = $(obj).find('.client-phone').val();
                var email = $(obj).find('.client-email').val();
                $('#check-contact').parent().css({ display: 'none'});

                $.ajax({
                    url: baseUrl + 'clients/ajax_check_contact',
                    data: {phone: phone, email: email},
                    dataType: 'json',
                    global:false,
                    method:'POST',
                    success: function(resp){
                        $('#check-contact').text('');
                        var clients_ids = [];
                        if (resp.status == 'ok') {

                            if (resp.users != 'Not Found!'){
                                $('#check-contact').parent().attr('style', '');
                                $('#check-contact').parent().css({ display: 'block'});
                                $(resp.users).each(function (key, val) {
                                    clients_ids.push(val.client_id);

                                    name = val.client_name ? val.client_name : 'NO NAME';
                                    $('#check-contact').append('<a target="_blank" href="' + baseUrl + val.client_id + '">' + name + '</a><br>');
                                });
                            }
                            if(clients_ids.length){
                                var ids = clients_ids.join(',');
                                $('[name="clients_ids"]').val(ids);
                            }

                        }
                        else {
                            alert(resp.msg);
                        }
                        return false;
                    }
                });

                return false;
            }


            function checkAddress(){
                return false;
                var street = $('.new_client_address').val();
                var city = $('.locality').val();
                $('#check-address').parent().css({ display: 'none'});
                if(!street)
                    return false;

                $.ajax({
                    url: baseUrl + 'clients/ajax_check_address',
                    data: {street: street, city: city},
                    dataType: 'json',
                    global:false,
                    method:'POST',
                    success: function(resp){
                        $('#check-address').text('');
                        if (resp.status == 'ok') {


                            if (resp.users != 'Not Found!'){
                                $('#check-address').parent().attr('style', '');
                                $('#check-address').parent().css({ display: 'block'});
                                $(resp.users).each(function (key, val) {
                                    $('#check-address').append('<a target="_blank" href="' + baseUrl + val.client_id + '">' + val.client_name + '</a><br>');
                                });
                            }

                        }
                        else {
                            alert(resp.msg);
                        }
                        return false;
                    }
                });
            }

            $('#check-contact-close').click(function () {
                $('#check-contact').parent().css({display: 'none'});
                return false;
            });
            $('#check-address-close').click(function () {
                $('#check-address').parent().css({display: 'none'});
                return false;
            });
            $('#check-client-close').click(function () {
                $('#check-client').parent().closest('div').css({display: 'none'});
                return false;
            });
        });
    </script>


    <div class="alert alert-success parent-check-contact">
        <strong>Check Contact</strong>
        <button type="button" id="check-contact-close" class="alert-close">&times;</button>
        <i class="fa fa-ok-sign"></i><div id="check-contact"></div>
    </div>

    <div class="alert alert-success parent-check-address">
        <strong>Check Address</strong>
        <button type="button" id="check-address-close" class="alert-close">&times;</button>
        <i class="fa fa-ok-sign"></i><div id="check-address"></div>
    </div>

    <div class="alert alert-light parent-check-contact">
        <section class="panel panel-default p-n">
            <header class="panel-heading">Check Client
                <button type="button" id="check-client-close" class="alert-close">&times;</button>
            </header>
            <div id="check-client"></div>
        </section>
    </div>

    <input type="hidden" value='<?php echo json_encode($allTaxes); ?>' id="allTaxes">

    <section class="scrollable p-sides-15">
        <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo base_url('clients'); ?>">Clients</a></li>
            <li class="active">New Client</li>
        </ul>
        <?php echo form_open(base_url() . "clients/add_client", ['id' => 'formData']) ?>

        <div class="row" style="display: flex; flex-wrap: wrap;">
            <!--Client profile -->
            <section class="col-lg-3 col-md-6  col-sm-6 col-xs-12">
                <section class="panel panel-default p-n">
                    <!-- Client header -->
                    <header class="panel-heading" style="padding-right: 10px; padding-left: 10px">
                        <div class="pull-left" style="width: 36%">Profile</div>
                        <div class="pull-left" style="width: 64%; position: relative;">
                            <?php $this->load->view('brands/partials/client_brands_dropdown', ['brand_style'=>'position: absolute;left: 5px; right: 0;top: -8px;', 'class'=>'input-sm select2 p-n']); ?>

                            <?php /*
			<select class="input-sm select2" id="brand-select" name="client_brand_id" style="">
			<?php if(isset($brands) && $brands): ?>
				<?php foreach($brands as $brand_key => $brand): ?>
					<option data-url="<?php echo $brand->main_logo; ?>" value="<?php echo $brand->b_id; ?>">
						<?php echo $brand->b_name; ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
			</select>
			*/?>
                        </div>
                        <div class="clearfix"></div>
                    </header>

                    <table class="table m-b-none profile-table text-ellipsis" style="display: table;">
                        <tr>
				<td class="v-middle"><label class="control-label">Client Name</label></td>
                            <td>

                                <?php  if (form_error('new_client_name')) {
                                    $new_client_name_error = "inputError";
                                    $new_client_name_holder = "control-group error";
                                } else {
                                    $new_client_name_error = "inputSuccess";
                                    $new_client_name_holder = "control-group success";
                                } ?>


                                <?php  $new_client_name_options = array(
                                    'name' => 'new_client_name',
                                    'value' => $this->input->post('new_client_name'),
                                    'class' => 'form-control success',
                                    'style' => 'background-color: #dff0d8;',
                                    'id' => $new_client_name_error);
                                ?>

                                <span class="<?php echo $new_client_name_holder ?>">
                 <?php echo form_input($new_client_name_options) ?>
              </span>
                            </td>
                        </tr>

                        <tr>
				<td class="v-middle">
					Reference
                            </td>
                            <td>
                                <select name="reffered" class="form-control pull-left" id="reffered">
                                    <?php foreach($reference as $key => $val) : ?>
                                        <option data-is_user_active="<?= $val['is_user_active']; ?>" data-is_client_active="<?= $val['is_client_active']; ?>" value="<?php echo $val['id']; ?>" <?= $this->input->post('reffered') == $val['id'] ? 'selected' : '';  ?> ><?php echo $val['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input id="reff_id" name="reff_id" class="pull-right" style="width:100%; display:none">
                                <input name="other_comment" class="form-control other_comment" style="display: none;width:100%;" value="<?= $this->input->post('other_comment'); ?>">
                                <br>
                            </td>
                        </tr>

                        <!--Reference person name-->
                        <tr id="reference_client_name_row">
				<td class="w-150 v-middle"><label class="control-label">Select refrence client</label></td>
                            <td class="p-left-30">

                                <?php  $refrence_client_options = array(

                                    'name' => 'client_referred_by',
                                    'value' => set_value('client_referred_by'),
                                    'class' => 'form-control',
                                    'id' => 'client_referred_by');
                                ?>

                            </td>
                        </tr>
                        <!--Reference person name ends-->

                        <tr>
				<td class="v-middle"><label class="control-label">Client Type</label></td>
                            <td class="p-left-30">
                                <select name="new_client_type" class="form-control">
                                    <option value="1" <?php echo set_select('new_client_type', '1'); ?>>Residential</option>
                                    <option value="2" <?php echo set_select('new_client_type', '2'); ?>>Corporate</option>
                                    <option value="3" <?php echo set_select('new_client_type', '3'); ?>>Municipal</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </section>
            </section>

            <!-- client address -->
            <section class="col-lg-3 col-md-6  col-sm-6 col-xs-12">
                <section class="panel panel-default p-n" style="overflow: hidden">
                    <header class="panel-heading">
                        Client Address
                        <!-- client tax block -->
                        <div class="pull-right default-tax-div">
                            <div class="overflow-ellipsis">Tax:&nbsp;
                                <div class="inline-block pull-right popover-markup text-ul" >
                                    <span class="tax trigger" data-tax-text="<?php echo $dataTaxText; ?>" style="cursor: pointer"><?php echo $taxText; ?></span>
                                    <div class="head hide">
                                        Tax selection
                                        <button type="button" class="close pull-right" data-dismiss="popover">×</button>
                                    </div>
                                    <div class="content hide">
                                        <input class="select2 form-data select2Tax" id="tax" style="min-width: 156px;" type="text" value="<?php echo $taxText; ?>" data-href='#allTaxes'>
                                    </div>
                                </div>

                                <span class="popover-markup recommendation" hidden>
                                    <i class="fa fa-warning trigger recommendationTax" style="color: red; cursor: pointer"></i>
                                    <div class="head hide">Tax advice <button type="button" class="close pull-right" data-dismiss="popover">×</button></div>
                                    <div class="content hide">
                                        <div style="min-width: 200px;">
                                            Recommended tax (<span class="taxRecommendationTitle">0</span>%)
                                            <input type="button" class="btn btn-success btn-xs useTax" value="use">
                                        </div>
                                    </div>
                                </span>

                            </div>
                        </div>
                    </header>

                    <!-- form -->
                    <div class="">
                        <table class="table m-n profile-table">
                            <tr>
                                <td class="p-left-30">
                                    <?php echo form_input(array(
                                        'name' => 'new_client_address',
                                        'class' => 'form-control new_client_address pull-left',
                                        'style' => 'background-color: #dff0d8; width: 100%;',
                                        'data-autocompleate' => 'true',
                                        'data-part-address' => 'address',
                                        'autocomplete' => 'nope',
                                        'data-parent-selectror' => 'table',
                                        'value' => set_value('new_client_address')));
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-left-30">
                                    <?php
                                    $new_client_address_2_error = "inputSuccess";
                                    $new_client_address_2_holder = "control-group success";
                                    if (form_error('new_client_address')) {
                                        $new_client_address_2_error = "inputError";
                                        $new_client_address_2_holder = "control-group error";
                                    } ?>
                                    <span class="<?php echo $new_client_address_2_holder ?>">

						<?php
                        $new_client_city_error = "inputSuccess";
                        $new_client_city_holder = "control-group success";
                        if (form_error('new_client_city')) {
                            $new_client_city_error = "inputError";
                            $new_client_city_holder = "control-group error";
                        } ?>

                        <?php echo form_input(array(
                            'name' => 'new_client_city',
                            'data-part-address' => 'locality',
                            'style' => 'background-color: #dff0d8; width: 48%;',
                            'autocomplete' => 'nope',
                            'placeholder' => 'City',
                            'class' => 'form-control locality pull-left',
                            'value' => set_value('new_client_city')));
                        ?>
                        <?php
                        $new_client_zip_error = "inputSuccess";
                        $new_client_zip_holder = "control-group success";
                        if (form_error('new_client_zip')) {
                            $new_client_zip_error = "inputError";
                            $new_client_zip_holder = "control-group error";
                        } ?>
						</span>
                                    <span class="<?php echo $new_client_zip_holder ?> pull-right" style="width: 48%;">
						    <input type="text"  placeholder="Province/State" autocomplete="nope" name="lead_state" class="form-control locality pull-right" value="<?php echo $this->input->post('lead_state'); ?>" data-part-address="administrative_area_level_1" style="background-color: #dff0d8; ">
						</span>

                                    <input type="hidden" class="task_lat" data-part-address="lat" name="new_client_lat" value="<?php echo $this->input->post('new_client_lat') ; ?>">

                                    <input type="hidden" class="task_lon" data-part-address="lon" name="new_client_lon" value="<?php echo $this->input->post('new_client_lon') ; ?>">

                                    <input type="hidden" class="task_state" data-part-address="state" name="new_client_state" value="<?php echo $this->input->post('new_client_state') ; ?>">

                                    <input type="hidden" class="task_country" data-part-address="country" name="new_client_country" value="<?php echo $this->input->post('new_client_country') ; ?>">

                                    <input type="hidden" class="formatted_address" data-part-address="formatted_address" name="formatted_address" value="<?php echo $this->input->post('formatted_address') ; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="p-left-30">

						<span class="<?php echo $new_client_zip_holder ?>">
						<?php echo form_input(array(
                            'name' => 'new_client_zip',
                            'placeholder' => 'Zip/Postal Code',
                            'autocomplete' => 'nope',
                            'data-part-address' => 'postal_code',
                            'style' => 'background-color: #dff0d8; width: 48%;',
                            'class' => 'form-control pull-left',
                            'value' => set_value('new_client_zip')));
                        ?>

						</span>
                                    <?php
                                    $new_client_city_error = "inputSuccess";
                                    $new_client_city_holder = "control-group success";
                                    if (form_error('new_client_country')) {
                                        $new_client_city_error = "inputError";
                                        $new_client_city_holder = "control-group error";
                                    } ?>

                                    <span class="<?php echo $new_client_zip_holder ?>">
						<?php echo form_input(array(
                            'name' => 'new_client_country',
                            'data-part-address' => 'country',
                            'style' => 'background-color: #dff0d8; width: 48%;',
                            'placeholder' => 'Country',
                            'type' => "hidden",
                            'autocomplete' => 'nope',
                            'class' => 'form-control locality pull-right',
                            'value' => set_value('new_client_country')));
                        ?>
                            <input type="text" name="new_client_main_intersection" value="<?php echo $this->input->post('new_client_main_intersection'); ?>" class="form-control pull-right" placeholder="Add Info" style="background-color: #dff0d8; width: 48%;">
						</span>

                                </td>
                            </tr>

                        </table>
                    </div>
                    <!-- /form -->
                </section>
            </section>
            <!-- client address -->

<?php if(!$this->input->post('client_phone') || empty($this->input->post('client_phone'))) : ?>
	<?php $this->load->view('new_client_contact', ['number' => 1]); ?>
	<?php //$this->load->view('new_client_contact', ['number' => 2]); ?>
<?php else : ?>
	<?php $num = 1; ?>
	<?php foreach($this->input->post('client_phone') as $key => $value) : ?>
		<?php $this->load->view('new_client_contact', [
				'number' => ($num++),
				'client_print' => isset($this->input->post('client_print')[0]) ? $this->input->post('client_print')[0] : 0,
				'client_name' => $this->input->post('client_name')[$key],
				'client_phone' => $this->input->post('client_phone')[$key],
				'client_email' => $this->input->post('client_email')[$key],
				'client_email_check' => isset($this->input->post('client_email_check')[$key]) ? $this->input->post('client_email_check')[$key] : '',
		]); ?>
	<?php endforeach; ?>
<?php endif; ?>


            <section class="col-lg-3 col-md-6  col-sm-6 col-xs-12">
                <section class="panel panel-default p-n">
                    <header class="panel-heading">
                        <div class="checkbox m-n">
                            <label class="checkbox-custom text-ellipsis" style="display: block; width: calc(100% + 25px);">
                                <input type="checkbox" id="new_add" name="new_add" value="1" <?= $this->input->post('new_add') === '1' ? 'checked' : ''; ?>>
                                <i class="fa fa-fw fa-square-o"></i>
                                <small>Lead's Address (if different from client's address)</small>
                            </label>
                        </div>
                    </header>
                    <table class="table m-n profile-table" id="lead-address-container">
                        <tr>
                            <td class="p-left-30" style="width: 100%">
                                <input type="text" class="form-control" disabled="disabled" autocomplete="nope" name="new_address" value="<?php echo $this->input->post('new_address'); ?>" data-autocompleate="true" data-part-address="address" placeholder="Enter a location" autocomplete="off" data-parent-selectror="table">
                            </td>
                        </tr>
                        <tr>
                            <td class="p-left-30">
					<span class="pull-left" style="width: 48%;">
						<input type="text" disabled="disabled" data-part-address="locality" autocomplete="nope" class="form-control" name="new_city" placeholder="City" value="<?php echo $this->input->post('new_city'); ?>">
					</span>
                                <span class="pull-right" style="width: 48%;">
						<input type="text" disabled="disabled" placeholder="Province/State" autocomplete="nope" name="lead_state" class="form-control" value="<?php echo $this->input->post('lead_state'); ?>" data-part-address="administrative_area_level_1">
						<input type="hidden" disabled="disabled" class="new_lat" data-part-address="lat" name="new_lat" value="<?php echo $this->input->post('new_lat'); ?>">
						<input type="hidden" disabled="disabled" class="new_lon" data-part-address="lon" name="new_lon" value="<?php echo $this->input->post('new_lon'); ?>">
					</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-left-30">
					<span class="pull-left" style="width: 48%;">
						<input type="text" placeholder="Zip/Postal Code" autocomplete="nope" disabled="disabled" name="new_zip" class="form-control" value="<?php echo $this->input->post('new_zip'); ?>" data-part-address="postal_code">
					</span>
                                <span class="pull-right" style="width: 48%;">

                                    <input type="text" name="lead_add_info" value="<?php echo $this->input->post('lead_add_info'); ?>" class="form-control" placeholder="Add Info">
                                    <input type="hidden" placeholder="Country"  name="new_country" class="form-control" value="<?php echo $this->input->post('new_country'); ?>" data-part-address="country">
                                </span>
                            </td>
                        </tr>
                    </table>
                </section>
            </section>


        </div>


        <?php $this->load->view('clients/client_create_tags'); ?>
        <?php //$this->load->view('leads/lead_services_list'); ?>
        <?php $this->load->view('appointment/schedule_appointment_modal'); ?>
        <!-- Clients intake notes -->
        <div class="row">
            <section class="col-md-12 col-sm-12 col-xs-12">
                <section class="panel panel-default p-n">
                    <div class="panel-body panel-default p-bottom-0">
                        <div class="row" style="display: flex; flex-wrap: wrap;">
                            <div class="col-md-6 col-lg-3 col-sm-6 col-xs-12 items-div">
                                <div class="form-group p-right-5">
                                    <label class="control-label"><strong>Services</strong></label>

                                    <div class="controls pos-rlt m-b-xs">
                                        <input type="hidden" name="est_services" autocomplete="false" class="est_services w-100" value="<?php echo isset($est_services) ? $est_services : '' ;  ?>" style="overflow-y: auto"
                                               data-value="<?php  echo isset($est_services) ? $est_services : '' ; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                                    </div>
                                    <?php if(!empty($products) && !empty(json_decode($products))): ?>
                                        <label class="control-label"><strong>Products</strong></label>
                                        <div class="controls pos-rlt m-b-xs">
                                            <input type="hidden" name="est_products" autocomplete="false" class="est_products w-100" value="<?php echo isset($est_products) ? $est_products : '' ;  ?>" style="overflow-y: auto"
                                                   data-value="<?php echo isset($est_products) ? $est_products : '' ;  ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                                        </div>
                                    <?php endif; if (!empty($bundles) && !empty(json_decode($bundles))): ?>
                                        <label class="control-label"><strong>Bundles</strong></label>
                                        <div class="controls pos-rlt m-b-xs">
                                            <input type="hidden" name="est_bundles" autocomplete="false" class="est_bundles w-100" value="<?php echo isset($est_bundles) ? $est_bundles : '' ;  ?>" style="overflow-y: auto"
                                                   data-value="<?php echo isset($est_bundles) ? $est_bundles : '' ;  ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title=""/>
                                        </div>
                                    <?php endif; if (!empty($estimatorsList) && !empty(json_decode($estimatorsList))): ?>
                                        <label class="control-label"><strong>Estimators</strong></label>
                                        <div class="controls pos-rlt m-b-xs">
                                            <input type="hidden" name="estimators" autocomplete="false" class="estimators w-100" value="<?php echo isset($estimators) ? $estimators : '' ;  ?>" style="overflow-y: auto"
                                                   data-value="<?php echo isset($estimators) ? $estimators : '' ;  ?>"  data-toggle="tooltip" data-placement="top" title="" data-original-title=""/
                                            <?php if(isset($estimators) && $estimators != '' && isset($scheduled_user_id) && $scheduled_user_id != '') :?>
                                                disabled="disabled"><div class="text-danger"><strong>To enable you need to delete the appointment</strong></div>
                                            <?php else : ?>
                                                >
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3 col-sm-6 p-left-10 col-xs-12 textarea-div">
                                <textarea name="new_client_lead" cols="40" rows="6" class="form-control" style="height: calc(100% - 17px);margin: 0px -12.25px 0px 0px;width: 100%;resize: none;" placeholder="New client lead. Get as much info as possible..."><?php echo $this->input->post('new_client_lead'); ?></textarea>
                            </div>
                            <div class="col-xs-12 col-md-6 col-sm-6 col-lg-3 p-right-10 dropzone-div">
                                <div class="dropzone dropzone-lead dz-clickable pos-abt text-center p-n" style="overflow: auto;">
                                    <div class="dz-message" data-dz-message=""><span>Drop files or tap here to upload <br>(image / pdf)</span></div>
                                </div>
                            </div>
                            <br class="hidden-lg hidden-md">
                            <div class="col-md-6 col-lg-3 col-sm-6 col-xs-12 config" style="padding-left: 25px">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group" style="margin-bottom: 9px;">
                                            <label class="control-label"><strong>Priority status</strong></label>
                                            <div class="btn-group btn-group-justified" data-toggle="buttons">
                                                <label class="btn btn-sm btn-info active white-space">
                                                    <input type="radio" name="new_lead_priority" checked="checked" data-priority_status="Regular" value="Regular"><i class="fa fa-check text-active"></i> Regular
                                                </label>
                                                <label class="btn btn-sm btn-success white-space">
                                                    <input type="radio" name="new_lead_priority" data-priority_status="Priority" value="Priority"><i class="fa fa-check text-active"></i> Priority
                                                </label>
                                                <label class="btn btn-sm btn-primary white-space">
                                                    <input type="radio" name="new_lead_priority" data-priority_status="Emergency" value="Emergency"><i class="fa fa-check text-active"></i> Emergency
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 9px;">
									<label class="control-label"><strong>Timelines</strong></label>
                                            <div class="btn-group btn-group-justified" data-toggle="buttons">
                                                <label class="btn btn-sm btn-info white-space">
                                                    <input type="radio" name="new_lead_timing" value="Right Away"><i class="fa fa-check text-active"></i> Right Away
                                                </label>
                                                <label class="btn btn-sm btn-success white-space">
                                                    <input type="radio" name="new_lead_timing" value="Within a month"><i class="fa fa-check text-active"></i> Few Weeks
                                                </label>
                                                <label class="btn btn-sm btn-primary white-space">
                                                    <input type="radio" name="new_lead_timing" value="Not in a Rush"><i class="fa fa-check text-active"></i> No Rush
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-lg-6">
                                        <div class="form-group">
                                            <label class=""><strong>Estimate Size</strong></label>
                                            <div class="btn-group btn-group-justified" data-toggle="buttons">
                                                <label class="btn btn-sm btn-info ">
                                                    <input type="radio" name="preliminary_estimate" id="preliminary-estimate-small" value="small"><i class="fa fa-check text-active"></i>S</label>
                                                <label class="btn btn-sm btn-success ">
                                                    <input type="radio" name="preliminary_estimate" id="preliminary-estimate-medium" value="medium"><i class="fa fa-check text-active"></i>M</label>
                                                <label class="btn btn-sm btn-primary ">
                                                    <input type="radio" name="preliminary_estimate" id="preliminary-estimate-big" value="big"><i class="fa fa-check text-active"></i>L</label>
                                            </div>
                                        </div></div><div class="col-sm-12 col-lg-6">
                                        <div class="form-group">
                                            <label class="control-label"><strong>Call The Client</strong></label>
                                            <div class="btn-group btn-group-justified" data-toggle="buttons">
                                                <label class="btn btn-sm btn-info ">
                                                    <input type="radio" name="lead_call" value="1"><i class="fa fa-check text-active"></i> Yes
                                                </label>
                                                <label class="btn btn-sm btn-success  active">
                                                    <input type="radio" name="lead_call" checked="checked" value="0"><i class="fa fa-check text-active"></i> No
                                                </label>
                                            </div>
                                        </div>


                                        </div>
                                    </div>
                                <?php $this->load->view('clients/appointment/new_appointment_block'); ?>
                            </div>
                        </div>
                        <div class="row panel-heading">
                            <div class="col-md-6 col-lg-3 col-sm-4 hidden-md p-left-0 hidden-xs">
                                <h4> <span>Lead Details</span></h4>
                            </div>
                            <div class="col-md-6 col-lg-3 col-sm-1 p-right-10 hidden-md hidden-xs"></div>
                            <div class="col-xs-12 col-md-6 col-lg-3 col-sm-2 p-right-5 shedule-div">
                                <input type="button" class="btn btn-success scheduledLead pull-right" value="Schedule Appointment">
<!--                                <div class="btn-group pull-right" data-toggle="buttons">-->
<!--                                    <label class="btn btn-success --><?php //if(isset($lead_scheduled) && $lead_scheduled): ?><!--active--><?php //endif; ?><!--">-->
<!--                                        <input type="checkbox" class="scheduledLead" name="lead_scheduled" --><?php //if(isset($lead_scheduled) && $lead_scheduled): ?><!--checked="checked"--><?php //endif; ?><!--<i class="fa fa-check text-active"></i>-->
<!--                                    </label>-->
<!--                                </div>-->
                            </div>
                            <div class="col-md-6 col-lg-3 col-sm-5 p-right-0 p-left-30 submit-div col-xs-12">
                                <div class="col-xs-6 p-left-0">
                                    <input type="submit" name="submit" value="Create Estimate" class="btn btn-info pull-left create-estimate">
                                </div>
                                <div class="col-xs-6 p-right-0">
                                    <input type="submit" id="submit" name="submit" value="Add Client" class="btn btn-info pull-right add-client">
                                </div>
                                <?php  $hidden = array('author' => $this->session->userdata('first_name') . " " . $this->session->userdata('last_name'));?>
                                <input type="hidden" name="type" value="">
                            </div>
                        </div>
                        <a href="#" class="showSearch block hide">
                            <i class="lamp fa fa-lightbulb-o fa-2x" style="padding-top: 24%;"></i>
                        </a>
                    </div>

                    <input type="hidden" name="new_client_tax_rate" class="taxRate" value="<?php echo $addTaxRate ?? null ?>">
                    <input type="hidden" name="new_client_tax_value" class="taxValue" value="<?php echo $addTaxValue ?? null ?>">
                    <input type="hidden" name="new_client_tax_name" class="taxName" value="<?php echo $addTaxName ?? null ?>">
                    <input type="hidden" name="us_tax_recommendation" class="taxRecommendation" value="<?php echo $usTaxRecommendation ?? null ?>">

                    <?php echo form_hidden($hidden); ?>
                    <?php echo form_close() ?>

                </section>
            </section>
        </div>

        <?php $this->load->view('appointment/appointment_ajax_forms'); ?>
        <link href="<?php echo base_url('assets/vendors/notebook/js/datetimepicker/datetimepicker.css'); ?>" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">
        <script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>
        <script src="<?php echo base_url(); ?>assets/js/modules/leads/leads.js?v=1.21"></script>

        <script>

            let itemsForSelect2 = <?= getCategoriesItemsForSelect2() ?>;
            let selectTags = itemsForSelect2.services;
            let selectTagsProducts = itemsForSelect2.products;
            let selectTagsBundles = itemsForSelect2.bundles;
            let selectTagsEstimators = <?= json_encode($estimatorsList); ?>;
            const referer = {
                'id' : "<?= $this->input->post('reff_id'); ?>",
                'name': "<?= $referer_full_name ?? ''  ?>"
            }
            $(document).ready(function(){
                $('#formData').submit(function(){
                    $('[name="new_address"]').attr('disabled', false);
                    $('[name="new_city"]').attr('disabled', false);
                    $('[name="lead_state"]').attr('disabled', false);
                    $('[name="new_zip"]').attr('disabled', false);
                    $('[name="new_country"]').attr('disabled', false);
                    $("input[type='submit']", this).attr('disabled', 'disabled');

                    return true;
                });
                // initSelect2();
                if(selectTags)
                    initSelect2($('form').find("input.est_services"), selectTags, 'Select Services', true);
                if(selectTagsProducts)
                    initSelect2($('form').find("input.est_products"), selectTagsProducts, 'Select Products', true);
                if(selectTagsBundles)
                    initSelect2($('form').find("input.est_bundles"), selectTagsBundles, 'Select Bundles', true);
                if(selectTagsEstimators)
                    initSelect2($('form').find("input.estimators"), selectTagsEstimators, 'Select Estimator', false);
                $("#reff_id").select2({
                    minimumInputLength:3,
                    placeholder: "Search",

                    ajax: {
                        url: baseUrl + "clients/ajax_get_reff",
                        params:{
                            type:'POST',
                            global:false,
                        },
                        dataType: 'json',
                        quietMillis: 500,
                        data: function (term, page) {
                            return {
                                name: term,
                                trigger: $('#reffered').val()
                            };
                        },
                        results: function (data, page) {
                            $($('#reff_id').select2("container")).addClass('search-result');
                            return { results: data.items };
                        },
                        cache: true
                    }
                });
                $('#reff_id').select2("enable", false);
            });

            $(document).on('change', '#reffered', function(){
                if($('option:selected', this).data('is_user_active') == 1 || $('option:selected', this).data('is_client_active') == 1) {
                    $(this).next('div').addClass('search-result');
                } else {
                    $(this).next('div').removeClass('search-result');
                }
            });
            /*
            $(document).on('change', '#reffered', function(){
                var val = $(this).val();
                var obj = $(this);

                if(val == 'client' || val == 'user')
                {
                    $('#reff_id').select2("enable", true);
                    $('.other_comment').attr('disabled', 'disabled');
                    $('.other_comment').css('display', 'none');
                    $(this).next().css('display', 'inline-block');
                }
                else if(val == 'other')
                {
                    $('#reff_id').select2("enable", false);
                    $('.other_comment').removeAttr('disabled');
                    $('.other_comment').css('display', 'inline-block');
                    $(this).next().css('display', 'none');
                }
                else
                {
                    $('#reff_id').select2("enable", false);
                    $('.other_comment').attr('disabled', 'disabled');
                    $('.other_comment').css('display', 'none');
                    $(this).next().css('display', 'none');
                }
                return false;
            });
            */

            $(document).ready(function(){
                $('.create-estimate').on('click', function (event) {
                    <!--            --><?php //$hidden['type'] = 'estimate' ?>
//            let test = <?//= json_encode($hidden) ?>//;
//            let estimate = <?//= form_hidden($hidden); ?>
                    $('[name="type"]').val('estimate');
                    console.log($('[name="type"]').val());
                    // return;
                    // $('#submit').click();
                });
                if($('#reffered').val()){
                    $('#reffered').change();
                }

                $(document).on('click', '.delete-contact', function() {
                    if($(this).is('disabled'))
                        return false;

                    $(this).parents('.contact-section:first').remove();
                    let primary = $(this).parents('.contact-section:first').find('[name="client_print[]"]');
                    if(typeof primary != 'undefined' && primary.is(':checked')){
                        $('[name="client_print[]"]').first().prop('checked', true);
                    }
                    $.each($('.contact-section .contact-title-link'), function(key, val) {
                        if(($(val).text().indexOf('Contact #') + 1))
                            $(val).text('Contact #' + (key + 1));

                        if($(val).parents('.contact-section:first').find('.client-title-value').val().indexOf('Contact #') + 1)
                            $(val).parents('.contact-section:first').find('.client-title-value').val('Contact #' + (key + 1));
                    });

                    return false;
                });

                $(document).on('click', '.add-contact', function() {
                    if($(this).is('disabled'))
                        return false;

                    $('.contact-section:last').after(contact_tpl.html);
                    $('.contact-section:last input').val('');

                    $.each($('.contact-section .contact-title-link'), function(key, val) {

                        if(($(val).text().indexOf('Contact #') + 1))
                            $(val).text('Contact #' + (key + 1));

                        if(key + 1 == $('.contact-section .contact-title-link').length) {
                            $(val).parents('.contact-section:first').find('.client-title-value').val('Contact #' + (key + 1));
                            $('.contact-title-link[data-toggle="popover"]').popover();
                            $(val).parents('.contact-section:first').find('.client-phone').inputmask(PHONE_NUMBER_MASK);
                            $(val).parent().find('.radio-inline input').val(key + 1);
                        }
                    });
                    Common.init_autocompleate();
                    return false;
                });

                $(document).on('show.bs.popover', '.contact-title-link[data-toggle="popover"]', function(){
                    var popover_tpl = "<input type='text' class='form-control pull-left' style='width:85%;' id='contact-title' value='" + $.trim($(this).text()) + "'><a class='btn btn-xs btn-success pull-right m-t-xs change-title'><i class='fa fa-check'></i></a><div class='clear'></div>";
                    $(this).attr('data-content', popover_tpl);
                });

                window.initDropzone($(".dropzone-lead"));

                var drop_files = <?php echo isset($pre_uploaded_files) ? json_encode($pre_uploaded_files[0]) : '[]'; ?>;
                var zone = Dropzone.forElement(".dropzone-lead");

                if(drop_files.length > 0){
                    $.each(drop_files, function(key, val) {
                        var pic_name_parts = val.split('/');
                        var pic_name = pic_name_parts[pic_name_parts.length-1];
                        var pic_format_parts = pic_name.split('.');
                        var pic_format = pic_format_parts[pic_format_parts.length-1];
                        var picurl = baseUrl + val;
                        zone.emit("addedfile", { name: pic_name});
                        zone.emit("thumbnail", { name: pic_name}, picurl);
                        zone.emit("complete", { name: pic_name});
                        zone.files.push({ name: pic_name});

                        var thumb_to_set = $('.dropzone .dz-preview.dz-file-preview:last .dz-image img');
                        thumb_to_set.attr('width', 94);
                        thumb_to_set.attr('height', 94);

                        if(pic_format != 'pdf'){
                            thumb_to_set.attr('src', picurl);
                            let  imageHrefContainer = `
                                <div class="dz-image"><a href="${baseUrl + val}" data-lightbox="lightbox" data-lead_file="${val.match(/lead_no.+\d/)}">
                                   ${thumb_to_set.get(0).outerHTML}
                                </a></div>
                            `;

                            $('.dropzone .dz-preview.dz-file-preview:last .dz-image').replaceWith(imageHrefContainer);
                        } else {
                            thumb_to_set.attr('src', baseUrl + '/assets/vendors/notebook/images/pdf.png');
                        }

                        $('form#formData').append('<input type="hidden" data-lead_id="0" name="pre_uploaded_files[0][]" value="' + val +
                            '" data-uuid="" data-size="" data-url="' + picurl +'" data-type="" data-name="' +
                            pic_name + '">');
                    });
                }

                let priorityType = "<?= $this->input->post('new_lead_priority'); ?>";
                if(priorityType)
                    $('input[name="new_lead_priority"][value="' + priorityType + '"]').prop('checked', true).click();
                let leadTiming = "<?= $this->input->post('new_lead_timing'); ?>";
                if(leadTiming)
                    $('input[name="new_lead_timing"][value="' + leadTiming + '"]').prop('checked', true).click();
                let preliminaryEstimate = "<?= $this->input->post('preliminary_estimate'); ?>";
                if(preliminaryEstimate)
                    $('input[name="preliminary_estimate"][value="' + preliminaryEstimate + '"]').prop('checked', true).click();
                let leadCall = "<?= $this->input->post('lead_call'); ?>";
                if(leadCall)
                    $('input[name="lead_call"][value="' + leadCall + '"]').prop('checked', true).click();

                // init tax popovers
                $('.popover-markup > .trigger').popover({
                    html: true,
                    trigger: 'focus',
                    placement : 'bottom',
                    title: function () {
                        return $(this).parent().find('.head').html();
                    },
                    content: function () {
                        return $(this).parent().find('.content').html();
                    }
                }).click(function(e) {
                    $(this).popover('toggle');
                    $('.trigger').not(this).popover('hide');
                    e.stopPropagation();
                }).data('bs.popover').tip().addClass('tax-popover');

                $('.popover-markup').on('shown.bs.popover', '.tax',  function (e) {
                    const data_json = $('#allTaxes').val();
                    let data;
                    if (typeof data_json !== "undefined") {
                        data = JSON.parse(data_json);
                        $('.select2Tax').select2({data: data});
                        $('.select2Tax').select2( 'val', $('.tax.trigger').data('taxText'));
                    }
                    $('.select2Tax').on('change', function () {
                        $('.popover-markup > .tax.trigger').popover('hide');
                        let newVal = $(this).val();
                        $('.tax.trigger').data('taxText', newVal);

                        // find selected tax from allTaxes
                        const taxEl = $.grep(data, function(val, i) {
                            return val.id === newVal;
                        });

                        if (typeof taxEl[0] !== "undefined") {
                            // set tax from allTaxes
                            $('[name="new_client_tax_rate"]').val(taxEl[0].rate);
                            $('[name="new_client_tax_value"]').val(taxEl[0].value);
                            $('[name="new_client_tax_name"]').val(taxEl[0].name);

                            if (taxEl[0].name === 'Tax') {
                                newVal = '(' + taxEl[0].value + '%)';
                            }
                            $('.tax.trigger').text(newVal);
                        } else {
                            // set default tax
                            $('[name="new_client_tax_rate"]').val(null);
                            $('[name="new_client_tax_value"]').val(null);
                            $('[name="new_client_tax_name"]').val(null);
                            $('.tax.trigger').text('<?php echo getDefaultTax()['name'] . " (" . getDefaultTax()['value'] . "%)" ?>');
                        }
                    });
                });

            <?php if (config_item('office_country') === 'United States of America'): ?>
                // check autotax for US
                // `blur` is used, since the `change` does not work if nothing was entered in the field,
                //   and the choice was made from the autocomplete
                $('[name="new_client_address"], [name="new_address"]').blur(function() {
                    const elName = $(this).attr('name');
                    const inpEl = elName.replace('address', '');

                    // do not send request for `new_client_address` if `new_address` is active and not empty
                    if (elName === 'new_client_address'
                            && $('[name="new_address"]').prop('disabled') === false
                            && $('[name="new_address"]').val() !== '') {
                        return false;
                    }

                    setTimeout(function() {
                        const stateName = elName === 'new_address' ? 'lead_state' : 'new_client_state';
                        const address = {
                            'address': $('[name="' + inpEl + 'address"]').val(),
                            'city': $('[name="' + inpEl + 'city"]').val(),
                            'zip': $('[name="' + inpEl + 'zip"]').val(),
                            'country': $('[name="' + inpEl + 'country"]').val(),
                            'state': $('[name="' + stateName + '"]').val()
                        };

                        if (address.country !== 'United States') {
                            return false;
                        }

                        let noVal = false;
                        $.each(address, function(key, val) {
                            if (val === '') {
                                noVal = true;
                                return false;
                            }
                        });

                        if (noVal) {
                            return false;
                        }

                        if (typeof OLD_ADDRESS !== 'undefined') {
                            if (JSON.stringify(OLD_ADDRESS) === JSON.stringify(address)) {
                                return false;
                            }
                        }
                        OLD_ADDRESS = address;

                        $.ajax({
                            url: baseUrl + 'clients/ajax_get_us_autotax',
                            data: address,
                            dataType: 'json',
                            global: false,
                            method:'POST',
                            success: function(resp) {
                                if (resp.status === 'ok') {
                                    if (typeof resp.estimatedTax !== 'undefined') {
                                        const estimatedTax = resp.estimatedTax;
                                        let data = [];
                                        const data_json = $('#allTaxes').val();

                                        if (typeof data_json !== "undefined") {
                                            data = JSON.parse(data_json);
                                        }

                                        const taxExists = $.grep(data, function(val, i) {
                                            return estimatedTax.text === val.text;
                                        });

                                        // add estimated tax to allTaxes if not exist
                                        if (typeof taxExists[0] === 'undefined') {
                                            data.unshift(estimatedTax);
                                        }

                                        $('#allTaxes').val(JSON.stringify(data));

                                        $('.taxRecommendationTitle').text(estimatedTax.value);
                                        $('input.taxRecommendation').val(estimatedTax.value);
                                        const currTaxValue = $('[name="new_client_tax_value"]').val();

                                        if (currTaxValue != estimatedTax.value) {
                                            $('.recommendation').show();
                                        }
                                    }
                                } else {
                                    alert(resp.msg);
                                }

                                return false;
                            }
                        });
                    }, 1000);
                });

                $(document).on('click', '.useTax', function(e) {
                    const value = $('.taxRecommendation').val();
                    const text = 'Tax (' + value + '%)';
                    $('.tax.trigger').text('(' + value + '%)').data('taxText', text);
                    const taxRate =  value / 100 + 1;
                    $('[name="new_client_tax_rate"]').val(taxRate);
                    $('[name="new_client_tax_value"]').val(value);
                    $('[name="new_client_tax_name"]').val('Tax');

                    $('.recommendation').hide();
                });
            <?php endif; ?>
            });

            function initSelect2(input, items, placeholder, multiply = true)
            {
                // var select2Input = $('form').find("input.est_services");
                var select2Input = $(input);

                var data = [];

                $(select2Input).select2('destroy');

                // $.each($.parseJSON(items), function(key, val) {
                //
                // 	data.push({id:val.key, text:val.name});
                // });

                $(select2Input).select2({
                    placeholder: placeholder,
                    data: JSON.parse(items),
                    allowClear: true,
                    multiple: multiply,
                    dropdownCssClass: "select-statuses-dropdown",
                    separator: "|"
                });
                $(select2Input).val($(select2Input).data('value'));
                $(select2Input).trigger('change');
                //$('input.select2-input').attr('name', 'select2_name_' + parseInt(Math.random() * 100000));
                $('form').find("input.est_services").attr('type', 'text').addClass('pos-abt').css({'display':'block', 'top':'-1px', 'z-index':'-1'});
            }
        </script>

        <!-- select2 -->
        <script src="<?php echo base_url(); ?>assets/js/modules/clients/clients.js?v=<?php echo config_item('js_clients'); ?>"></script>

        <style type="text/css">
            .m-bottom-0{ margin-bottom: 0; }
            .datepicker-inline{ font-size: 21px; width: 277px; }
            .hour, .minute{ color: #333; font-weight: 600; }
            .text-overflow-ellipsis{
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .default-tax-div {
                padding-top: 1px;
                font-size: 12px;
            }
            .default-tax-div .popover {
                z-index: 10000;
            }
            .default-tax-div .popover.bottom > .arrow::after {
                border-bottom-color: #f7f7f7;
            }
            .default-tax-div .recommendation {
                display: none;
                position: absolute;
                top: 12px;
                right: 17px;
            }
        </style>
        <!-- /row -->
        <!-- /block 4 -->

        <!-- / new customer -->
<?php $this->load->view('includes/footer'); ?>
