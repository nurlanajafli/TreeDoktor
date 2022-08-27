<?php $this->load->view('includes/header'); ?>

<!--<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/schedule/schedule.css'); ?>">-->
<link rel="stylesheet" href="<?php echo base_url('assets/css/colpick.css'); ?>"/>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/js/modules/leads/leads.js?v=1.21"></script>
<style>
    .ui-autocomplete{padding: 1px 6px;}
	.dhx_save_btn_set, .dhx_cancel_btn_set, .dhx_delete_btn_set{text-shadow: none!important;} .dhx_email_btn_set{text-shadow: none!important;}
    .dhx_event_resize{
        position: absolute;
        bottom: 0px;
    }
    .dhx_cal_event .dhx_title {
        text-align: left!important;
    }
    .dhx_delete_btn_set, .dhx_email_btn_set
    {
        color: #fff !important;
        background-color: #fb6b5b!important;
        border-color: #fb6b5b!important;
        border: 1px solid #fb6b5b!important;
    }
    .dhx_cal_event {
        box-shadow: 0px 0px 1px 1px #fff;
        border-radius: 5px;
    }
    .dhx_cal_event .dhx_footer {
        width: auto!important;
    }
    .dhx_cal_event.dhx_cal_select_menu {
        height:0px!important;
    }
    .routes {
        left: auto!important;
        right: 220px!important;
    }
    .icon_email.open, .icon_sms.open{
        position: absolute;
    }
    .dhx_menu_head {
        background-image: none!important;
    }

    .dhx_cal_select_menu .dhx_event_move.dhx_title {
        height: 0px;
    }
    .dhx_menu_icon.icon_email, .dhx_menu_icon.icon_sms {
        background-image: none;
        color: #fff;
        font-size: 15px;
        display: inline-block;
        font-family: FontAwesome;
        font-style: normal;
        font-weight: normal;
        line-height: 1;
    }
    @media (max-width: 768px) {
        html {
            overflow: hidden!important;
        }
        .app .vbox > section {
            position: absolute;
            top: 0;
            bottom: 0;
        }
        .app .hbox.stretch {
            display: table;
            table-layout: fixed;
            width: 100%;
            height: 100%;
        }
        .hbox > section {
            height: 100%;
        }
        .vbox {
            border-spacing: 0;
            height: 100%;
        }
        .dhx_cal_light.dhx_cal_light_wide {
            left: 0!important;
            right: 0!important;
            margin: 0 auto!important;
            top: 100px!important;
            max-width: 99%!important;
        }
        .dhx_cal_larea {
            margin-left: 0!important;
            max-width: 99%!important;
        }
        .dhx_cal_light_wide .dhx_wrap_section {
            max-width: 99%!important;
        }
        .free-members-label>div {
            top: -7px!important;
        }
    }

    .dayoff-toggle {
        top: 0px !important;
        left: 10px !important;
        font-family: 'Open Sans';
        width: 105px !important;
        height: 26px !important;
    }

    .toggle-group {
        top: 0px !important;
    }

    .toggle-group label{
        padding-top: 6px;
    }

    .dhx_cal_event.dhx_cal_select_menu.no_menu {
        display:none;
    }

    .hide_dayoff {
        display: none;
    }

    .show_dayoff {
        display: block;
    }
</style>
<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.js'); ?>" type="text/javascript"
        charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_units.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_editors.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_collision.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/vendors/notebook/js/codebase/ext/dhtmlxscheduler_tooltip.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<script src="<?php echo base_url('assets/js/bootstrap-select.js'); ?>"
        type="text/javascript" charset="utf-8"></script>

<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.css'); ?>"
      type="text/css" media="screen" title="no title" charset="utf-8">
<script src="<?php echo base_url('assets/js/colpick.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/label.js'); ?>"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
  var uPressed = false;
  var OFFICE_SCHEDULER_STARTS_FROM = <?php echo config_item('office_schedule_start') ?? 7; ?>;
  var OFFICE_SCHEDULER_ENDS_AT = <?php echo config_item('office_schedule_end') ?? 20; ?>;
  var clients = {};
  var sticker = <?php echo json_encode($sticker); ?>;
  var clients_tpl = <?php echo json_encode($clients_tpl); ?>;
  var address_tpl = <?php echo json_encode($address_tpl); ?>;
  var emails_tpl = <?php echo json_encode($emails_tpl); ?>;
  var sms_tpl = <?php echo json_encode($sms_tpl); ?>;
  var tags = [];
  var sections = [];
  var categories = [];
  var allStickers = {};
  var mapRoutes, routes, directionsRenderer = [];
  var office_events = {};
  var MESSENGER = <?php echo (int) $this->config->item('messenger'); ?>;

  function init() {
      sections = [
          <?php foreach($users as $user) : ?>
          {
              key:<?php echo $user->id; ?>,
              label: "<?php echo $user->firstname . ' ' . $user->lastname; ?>",
              color: "<?php echo $user->color; ?>"
          },
          <?php endforeach; ?>
      ];

      categories = [
          <?php foreach($task_categories as $cat) : ?>
          <?php if ($cat['category_id'] >= 0): ?>
          {
              key:<?php echo $cat['category_id']; ?>,
              label: "<?php echo $cat['category_name']; ?>",
              color: "<?php echo $cat['category_color']; ?>"
          },
          <?php endif; ?>
          <?php endforeach; ?>
      ];

      statuses = [
          {key: 'new', label: "New"},
          {key: 'canceled', label: "Canceled"},
          {key: 'done', label: "Done"},
      ];


      scheduler.locale.labels.section_is_busy = "<label class='is-busy-label'>Busy</label>";
      scheduler.locale.labels.icon_email = "Send Email";
      scheduler.locale.labels.icon_sms = "Send SMS";
      scheduler.locale.labels.section_assign = "Assign to";
      scheduler.locale.labels.section_status = "Status";
      //scheduler.locale.labels.section_description="Details";
      scheduler.locale.labels.section_category = "Task Type";
      scheduler.locale.labels.section_client = '<br class="office-add-padding">Client<br><a href="" target="_blank" id="taskClientUrl"></a>';
      scheduler.locale.labels.section_address = "Address";
      scheduler.config.first_hour = OFFICE_SCHEDULER_STARTS_FROM;
      scheduler.config.last_hour = OFFICE_SCHEDULER_ENDS_AT;
        scheduler.locale.labels.section_leads = "Leads";

		scheduler.config.time_step  = 15;
		scheduler.config.collision_limit = 100;
		scheduler.config.multi_day = true;
		scheduler.config.details_on_create=true;
		scheduler.config.details_on_dblclick=true;
		scheduler.config.xml_date = "%Y-%m-%d %H:%i";
		scheduler.config.default_date = "%l, %j %F %Y";
		scheduler.config.icons_select=["icon_email"];
        if(typeof(MESSENGER) != 'undefined' && MESSENGER) {
            scheduler.config.icons_select.push("icon_sms");
        }
        scheduler.config.icons_select.push("icon_delete");
		<?php if(config_item('time') == 12) :?>
		    scheduler.config.hour_date="%h:%i %a";
        <?php endif; ?>
        <?php if(config_item('time') == 24) :?>
        scheduler.config.hour_date="%H:%i";
        <?php endif; ?>

      scheduler.templates.event_class = function (s, e, ev) {
          if (ev.Categories == -1) {
              return "no_menu";
          }
          return ev.custom ? "custom" : "";
      };
      scheduler.templates.event_text = function (start, end, ev) {
          return ev.text;
      };

      var tooltip_event_date_format = scheduler.date.date_to_str("%Y-%m-%d %H:%i");
      scheduler.templates.tooltip_text = function(start,end,event) {

          var country = (event.task_country === null || event.task_country === undefined) ? '' : event.task_country;
          var city = (event.task_city === null || event.task_city === undefined) ? '' : event.task_city;
          var address = (event.task_address === null || event.task_address === undefined) ? '' : event.task_address;
          var description = (event.description === ''|| event.description === null || event.description === undefined) ? '' : "<br/><b>Description:</b> " + event.description;
          var dateTimeFormat = 'h:mm';
          if(timeFormat === "12") {
              dateTimeFormat = 'h:mm a';
          }
          var phone = '';
          if(event.cc_phone_view)
            phone = '<br/><b>Phone:&nbsp;&nbsp;</b><span class="text-info"><i class="fa fa-phone-square"></i>&nbsp;<b>'+event.cc_phone_view+'</b></span> ';

          var client_name_header = (event.client_name!=undefined && event.client_name)?event.client_name:'Office';
          return '<b class="text-info">'+ client_name_header+'</b>'+ (address ? ' - ' : '') + address + (city ? ", " + city : '') +
              "<br/><b>Assigned to:</b> "+ event.ass_firstname + " " + event.ass_lastname +
              "<br/><b>Start date:</b> "+ moment(event.start_date).format(dateFormatJS.toUpperCase() + " " + dateTimeFormat) +
              "<br/><b>End date:</b> "+ moment(event.end_date).format(dateFormatJS.toUpperCase() + " " + dateTimeFormat) +
              "<br/><b>Address:</b> " + (address ? ' - ' : '') + address + (city ? ", " + city : '') +
              "<br/><b>Status:</b> " + event.task_status + phone + description;
      };

      scheduler.config.lightbox.sections = [
          {name: "is_busy", map_to: "is_busy_template", type: "template"},
          {name: "description", height: 50, map_to: "description", type: "textarea", focus: true},
          {name: "status", height: 23, type: "select", options: statuses, map_to: "task_status"},
          {name: "category", height: 23, type: "select", options: categories, map_to: "category_id"},
          {name: "assign", height: 23, type: "select", options: sections, map_to: "section_id"},
          {name: "client", height: 53, type: "template", map_to: "my_template"},
          {name: "leads", height: 23, type: "template", options: [], map_to: "leads_template"},
          {name: "address", height: 28, type: "template", map_to: "address_template"}
      ];

      scheduler.config.touch = true;
      scheduler.config.touch_drag = 750;

      scheduler.attachEvent("onBeforeLightbox", function (id) {
          let event = scheduler.getEvent(id);
          if (event.Categories == -1) {
              scheduler.getEvent(id).readonly = true;
              return false;
          }

          var ev = scheduler.getEvent(id);
          ev.my_template = clients_tpl.tpl;
          ev.address_template = address_tpl.tpl;
          ev.is_busy_template = '<label class="switch"><input name="is_busy" type="checkbox"><span></span></label>';
          ev.leads_template = '<select style="width:100%;" id="leads_select" disabled><option value="0">-</option></select>';
          return true;
      });

      scheduler.createUnitsView("unit", "section_id", sections);

      scheduler.init('scheduler_here', new Date(), "week");

      min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
      max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);
      scheduler.load(baseUrl + "schedule/office/data?from=" + min_date + "&to=" + max_date, "json", function () {
          $('#processing-modal').modal('hide');
      });

      dp = new dataProcessor(baseUrl + "schedule/office/data");
      dp.init(scheduler);
      dp.setTransactionMode("POST", true);

      scheduler.attachEvent('onLightbox', function (id) {

          var addressAuto = [];
          var event = scheduler.getEvent(id);

          if(event.client_id == -1)
              event.client_id = undefined;

          if (event.client_id != undefined) {
              $('#taskClientUrl').attr('href', baseUrl + event.client_id);
              $('#taskClientUrl').text('#' + event.client_id);
          } else {
              $('#taskClientUrl').attr('href', '#');
              $('#taskClientUrl').text('');
              $('.office_add').prop('checked', true).change();
          }

          if (event.task_address) {
              $('.client_id').val(event.client_id);

              if (!event.client_id) {
                  $('.office_add').prop('checked', true).change();
              }
              $('.clients').val(event.client_title);
              $('.task_address').removeAttr('disabled').val(event.task_address && event.task_address !== 'null' ? event.task_address : '');
              $('.task_city').removeAttr('disabled').val(event.task_city && event.task_city !== 'null' ? event.task_city : '');
              $('.task_state').removeAttr('disabled').val(event.task_state && event.task_state !== 'null' ? event.task_state : '');
              $('.task_zip').removeAttr('disabled').val(event.task_zip && event.task_zip !== 'null' ? event.task_zip : '');
          }
          if(event.lead_id && event.leads && event.leads.length > 0){
              $.each(event.leads, function (key, value) {
                  $('#leads_select')
                      .append($("<option></option>")
                          .attr("value", value['lead_id'])
                          .attr('data-address', value['lead_address'])
                          .attr('data-city', value['lead_city'])
                          .attr('data-state', value['lead_state'])
                          .attr('data-zip', value['lead_zip'])
                          .text(value['lead_no']));
              });
              $('#leads_select').removeAttr('disabled').val(event.lead_id);
          }
          $('.clients').autocomplete({
              source: function (request, response) {
                  let DTO = { "term": request.term };
                  $.ajax({
                      data: DTO,
                      global: false,
                      type: 'GET',
                      url: baseUrl + 'clients/tasksSearch',
                      success: function ($result) {
                          return response($result.data);
                      }
                  });
              },
              appendTo: '#ul',
              minLength: 3,
              select: function (e, ui) {
                  $('.office_add').prop('checked', false);

                  event.cc_name = ui.item.cc_name;
                  event.client_name = ui.item.cc_name;
                  event.client_id = ui.item.id;
                  event.cc_client_id = ui.item.id;
                  event.task_client_id = ui.item.id;
                  event.cc_phone_view = ui.item.cc_phone_view;

                  scheduler.updateEvent(event.id);
                  $('.client_id').val(ui.item.id);
                  $('.task_address').removeAttr('disabled').val(ui.item.address && ui.item.address !== 'null' ? ui.item.address : '');
                  $('.task_city').removeAttr('disabled').val(ui.item.city && ui.item.city !== 'null' ? ui.item.city : '');
                  $('.task_state').removeAttr('disabled').val(ui.item.state && ui.item.state !== 'null' ? ui.item.state : '');
                  $('.task_zip').removeAttr('disabled').val(ui.item.zip && ui.item.zip !== 'null' ? ui.item.zip : '');
                  $('#leads_select').find('option').remove().end()
                      .append('<option value="0">-</option>').val('0');

                  if(ui.item.leads !== undefined && ui.item.leads.length > 0) {
                      $.each(ui.item.leads, function (key, value) {
                          $('#leads_select')
                              .append($("<option></option>")
                                  .attr("value", value['lead_id'])
                                  .attr('data-address', value['lead_address'])
                                  .attr('data-city', value['lead_city'])
                                  .attr('data-state', value['lead_state'])
                                  .attr('data-zip', value['lead_zip'])
                                  .text(value['lead_no']));
                      });
                      $('#leads_select').removeAttr('disabled');
                  } else {
                      $('#leads_select').attr('disabled', 'disabled');
                  }
              }
          });
          $('.clients').blur(function () {
              if (!$('.clients').val()) {
                  $('.task_address').attr('disabled', 'disabled').val('');
                  $('.task_city').attr('disabled', 'disabled').val('');
                  $('.task_state').attr('disabled', 'disabled').val('');
                  $('.task_zip').attr('disabled', 'disabled').val('');
                  $('.task_lat').val('');
                  $('.task_lon').val('');
                  $('.client_id').val('');
                  $('#leads_select').attr('disabled', 'disabled');
                  $('#leads_select option:first').prop('selected', true);
              }
          });
          $('#leads_select').on('change', function(){
              if($('#leads_select option:selected').data('address')) {
                  $('.task_address').val($('#leads_select option:selected').data('address'));
                  $('.task_city').val($('#leads_select option:selected').data('city'));
                  $('.task_state').val($('#leads_select option:selected').data('state'));
                  $('.task_zip').val($('#leads_select option:selected').data('zip'));
              }
          });
          $('head').append('<style>.ui-autocomplete{width: ' + $('.clients.ui-autocomplete-input').css('width') + '!important;}</style>');
          scheduler.formSection('category').setValue(event.category_id);
          scheduler.formSection('assign').setValue(event.assign);
          scheduler.formSection('description').setValue(event.description);
          scheduler.formSection('status').setValue(event.task_status);
          $('.clients').val(event.client_title);
          $('.client_id').val(event.client_id);

          if (event.task_lead_id) {
              $('.office_add').parent().hide();
              $('.office-add-padding').hide();
              $('.task_address, .task_city, .task_state, .task_zip, .task_lat, .task_lon, .clients').attr('readonly', 'readonly');
              return true;
          }

			$.each($('[data-autocompleate]'), function(key, val){

				addressAuto.push(new google.maps.places.Autocomplete(
					($(val)[0]),
					{ types: ['geocode'] , componentRestrictions: {country: AUTOCOMPLETE_RESTRICTION} }
				));
				let key_address = addressAuto.length - 1;
                google.maps.event.clearListeners(addressAuto[key_address], 'place_changed');
				google.maps.event.addListener(addressAuto[key_address], 'place_changed', function () {
					fillInAutocompleteAddress(addressAuto[key_address], val, 'form');
                });


			});


          return true;
      });

      scheduler.attachEvent("onViewChange", function (new_mode, new_date) {
          $('#processing-modal').modal();
          min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
          max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);
          $('.choosenUser li.active').removeClass('active');
          $('.choosenUser li:first').addClass('active');
          showTaskPdf(false, false, false);
          $('.choosenUser').parent().find('.dropdown-label').text('Choose User');
          scheduler.clearAll();
          scheduler.load(baseUrl + 'schedule/office/data?from=' + min_date + '&to=' + max_date, "json", function () {
              $('#processing-modal').modal('hide');
          });
      });

      scheduler.config.buttons_right = ["dhx_save_btn", "dhx_cancel_btn"];

      scheduler.config.buttons_left = ["dhx_delete_btn"];

      scheduler.attachEvent("onEventSave", function (id, ev, is_new) {

          ev.client_title = $('.clients').val();
          ev.client_id = $('.client_id').val();
          ev.task_address = $('.task_address').val();
          ev.task_city = $('.task_city').val();
          ev.task_state = $('.task_state').val();
          ev.task_zip = $('.task_zip').val();
          ev.task_lat = $('.task_lat').val();
          ev.task_lon = $('.task_lon').val();
          ev.lead_id = $('#leads_select').val();

          ev.category = ev.category_id;
          ev.assign = ev.section_id;
          var categoryName = '';
          var stickerColor = '';
          $.each(categories, function (num, val) {
              if (val.key == ev.category) {
                  categoryName = val.label;
                  stickerColor = val.color;
              }
          });
          var assignName = '';
          $.each(sections, function (num, val) {
              if (val.key == ev.assign) {
                  assignName = val.label;
                  assignColor = val.color;
              }
          });

          ev.ass_firstname = assignName.split(' ')[0];
          ev.ass_lastname = assignName.split(' ')[1];
          ev.color = assignColor;
          ev.text = sticker.tpl;
          ev.text = $(ev.text).find('.category-name').text(categoryName).parents('.sticker:first')[0].outerHTML;
          ev.text = $(ev.text).find('.client').text(ev.client_title).parents('.sticker:first')[0].outerHTML;
          ev.text = $(ev.text).find('.assigned-to').text(assignName).parents('.sticker:first')[0].outerHTML;
          ev.text = $(ev.text).find('.details').text(ev.description).parents('.sticker:first')[0].outerHTML;
          if(ev.lead_id.length > 0 && ev.lead_id > 0)
            ev.text = $(ev.text).find('.lead-no').text(ev.lead_id + '-L').parents('.sticker:first')[0].outerHTML;

          scheduler.updateEvent(ev.id);


          //$(".office_add").trigger('click');
          $(".office_add").parent().show();

          scheduler.formSection('category').setValue('1');
          $(scheduler.formSection('category').header).parent().show();

          //scheduler.formSection('assign').setValue(user_id);
          scheduler.formSection('description').setValue("");
          $(scheduler.formSection('description').header).parent().show();

          scheduler.formSection('status').setValue('new');
          $(scheduler.formSection('status').header).parent().show();

          $(".client_id").parent().parent().show();
          $(".task_address").parent().parent().parent().show();

          setTimeout(() => {
              scheduler.load(baseUrl + 'schedule/office/data?from=' + min_date + '&to=' + max_date, "json", function () {
                  $('#processing-modal').modal('hide');
              });
          }, 100)
          return true;
      });
      scheduler.attachEvent("onEmptyClick", function (date) {
          if (scheduler.getState().mode == 'month')
              scheduler.init('scheduler_here', new Date(date), "day");
          return true;
      });

      scheduler.attachEvent("onEventCancel", function (id, flag) {
          if ($('input[name="is_busy"]').prop('checked') == true) {
              $('input[name="is_busy"]').click();
          }
      });

      scheduler.renderEvent = function (container, ev) {

          var container_width = container.style.width; // e.g. "105px"
          var container_height = parseInt(container.style.height); // e.g. "105px"

          // move section
          var html = "<div class='dhx_event_move dhx_header' style='width: " + container_width + "'></div>";
          var status = '';

          if (ev.task_status == 'done')
              status = '<i class="fa fa-check pull-right m-r-xs"></i>';
          if (ev.task_status == 'canceled')
              status = '<i class="fa fa-times pull-right m-r-xs"></i>';


          var client_name_header = (ev.client_name!=undefined && ev.client_name)?('<a style="color:#fff!important" target="_blank" href="/'+ev.task_client_id+'">'+ev.client_name+'</a>'):'Office';
          // container for event's content
          ev.ass_firstname = typeof (ev.ass_firstname) == 'undefined' ? '' : ev.ass_firstname;
          ev.ass_lastname = typeof (ev.ass_lastname) == 'undefined' ? '' : ev.ass_lastname;
          html += '<div class="dhx_event_move dhx_title" style="background:' + ev.color + ';">';
          html += '&nbsp; ' + client_name_header + "</div>";
          //two options here:show only start date for short events or start+end for long
          html += '<div class="dhx_body" style="height: ' + (container_height - 28) + 'px; width:' + (parseInt(container_width) - 10) + 'px;background:' + ev.color + ';">';
          if ((ev.end_date - ev.start_date) / 60000 > 40) {//if event is longer than 40 minutes
              html += ' ' + scheduler.templates.event_header(ev.start_date, ev.end_date, ev);
              html += status + '<br>';
          } else {
              html += scheduler.templates.event_date(ev.start_date) + status + '<br>';
          }
          // displaying event's text

          html += ev.text + '</div>';
          html += '<div class="dhx_event_resize dhx_footer" style=" width:166px;background:' + ev.color + ';"></div>';

          container.innerHTML = html;
          return true; //required, true - display a custom form, false - the default form
      };

      scheduler._click.buttons.email = function (id) {
          var ev = scheduler.getEvent(id);
          var dropdownContent = emails_tpl.tpl.replace(/\[ADDRESS\]/g, ev.task_address);
          var dropdownContent = emails_tpl.tpl.replace(/data-task_id=""/g, 'data-task_id="' + id + '"');
          $('[event_id="' + id + '"]').find('.icon_email').html(dropdownContent);
          if ($('[event_id="' + id + '"]').find('.icon_email').is('.open'))
              $('[event_id="' + id + '"]').find('.icon_email').removeClass('open');
          else
              $('[event_id="' + id + '"]').find('.icon_email').addClass('open');
          return true;
      };
      scheduler._click.buttons.sms = function (id) {
          var ev = scheduler.getEvent(id);
          var dropdownContent = sms_tpl.tpl.replace(/\[ADDRESS\]/g, ev.task_address);
          var dropdownContent = sms_tpl.tpl.replace(/data-id=""/g, 'data-id="' + id + '"');
          $('[event_id="' + id + '"]').find('.icon_sms').html(dropdownContent);
          if ($('[event_id="' + id + '"]').find('.icon_sms').is('.open'))
              $('[event_id="' + id + '"]').find('.icon_sms').removeClass('open');
          else
              $('[event_id="' + id + '"]').find('.icon_sms').addClass('open');
          return true;
      };
      scheduler.attachEvent("onClick", function (id, e) {
          if (uPressed)
              return false;

          if (!$(e.target).closest('.dropdown-menu').length && !$(e.target).is('.icon_email'))
              $('.icon_email.open').removeClass('open');
          if (!$(e.target).closest('.dropdown-menu').length && !$(e.target).is('.icon_sms'))
              $('.icon_sms.open').removeClass('open');

          return true;
      });
  }
  $(document).ready(function(){

    $('#processing-modal').modal();
    init();
        //Common.initTinyMCE('.textMsg');

        $('#email-template-modal').on('hidden.bs.modal', function () {
            $('.icon_email.open').removeClass('open');
        });

        $('#appointment-sms-modal').on('hidden.bs.modal', function () {
            $('.icon_sms.open').removeClass('open');
        });

    $(document).on('change', 'input[name="is_busy"]', function(){
      if($(this).prop('checked')==true){

        $(".office_add").trigger('click');
        $(".office_add").parent().hide();

        scheduler.formSection('category').setValue('0');
        $(scheduler.formSection('category').header).parent().hide();

        //scheduler.formSection('assign').setValue(user_id);
        scheduler.formSection('description').setValue("Busy time");
        $(scheduler.formSection('description').header).parent().hide();

        scheduler.formSection('status').setValue('new');
        $(scheduler.formSection('status').header).parent().hide();

        $(".client_id").parent().parent().hide();
        $(".task_address").parent().parent().parent().hide();
      }
      else
      {
        $(".office_add").trigger('click');
        $(".office_add").parent().show();

        scheduler.formSection('category').setValue('1');
        $(scheduler.formSection('category').header).parent().show();

        //scheduler.formSection('assign').setValue(user_id);
        scheduler.formSection('description').setValue("");
        $(scheduler.formSection('description').header).parent().show();

        scheduler.formSection('status').setValue('new');
        $(scheduler.formSection('status').header).parent().show();

        $(".client_id").parent().parent().show();
        $(".task_address").parent().parent().parent().show();
      }
    });

    $(document).on('click', '.dhx_scheduler_week .dhx_scale_bar', function(){
      var date = $(this).text() + ' ' + (scheduler.getState().date.getYear() + 1900);
      scheduler.init('scheduler_here', new Date(date), 'day');
      return false;
    });
    $(document).on('change', '.office_add', function(){
      var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
      if($(this).prop('checked') == true)
      {
          ev.client_name = undefined;
          ev.cc_name = undefined;
          ev.client_name = undefined;
          ev.client_id = -1;
          ev.task_client_id = '';
          scheduler.updateEvent(ev.id);
          $('.client_id').val(-1);

          $('#leads_select').find('option').remove().end()
              .append('<option value="0">-</option>').val('0').attr('disabled', 'disabled');
        $('.clients').val('Office ' + <?php echo json_encode($office_address);?> + ', '+ <?php echo json_encode($office_city);?> + ', ' + <?php echo json_encode($office_state);?> + ', ' + <?php echo json_encode($office_zip);?>);
        $('.task_address').removeAttr('disabled').val(<?php echo json_encode($office_address);?>);
        $('.task_city').removeAttr('disabled').val(<?php echo json_encode($office_city);?>);
        $('.task_state').removeAttr('disabled').val(<?php echo json_encode($office_state);?>);
        $('.task_zip').removeAttr('disabled').val(<?php echo json_encode($office_zip);?>);
        $('.task_lat').removeAttr('disabled').val(OFFICE_LAT);
        $('.task_lon').removeAttr('disabled').val(OFFICE_LON);
      }
      else
      {
        $('.clients').val(ev.client_title);
        $('.client_id').val(ev.client_id);
        $('.task_address').val(ev.task_address);
        $('.task_city').val(ev.task_city);
        $('.task_state').val(ev.task_state);
        $('.task_zip').val(ev.task_zip);
        $('.task_lat').val(ev.task_lat);
        $('.task_lon').val(ev.task_lon);
      }
      return false;

    });
    $(document).on('click', '.choosenUser a', function(){
      var obj = $(this);
      var user_id = $(obj).data('user-id');
      var text = $(obj).text();
      $('#processing-modal').modal();
      min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
      max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);
      scheduler.clearAll();
      scheduler.load(baseUrl + "schedule/office/data?from=" + min_date + "&to=" + max_date + "&user_id=" + user_id + "&filter=" + true, "json", function(){$('#processing-modal').modal('hide');});
      $('.choosenUser li.active').removeClass('active');
      $(obj).parent().parent().parent().find('.dropdown-label').text(text);
      showTaskPdf(user_id, min_date, scheduler.getState().mode == 'day' ? '' : max_date);
      //$(obj).parent().parent().find('.active').removeClass("active");
			//return false;
      return true;
    });
    $(document).on('change', '.office_my_task', function(){
      var my_task = $(this).prop('checked');
      $('#processing-modal').modal();
      min_date = (scheduler.getState().min_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().min_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().min_date.getDate(), 2);
      max_date = (scheduler.getState().max_date.getYear() + 1900) + '-' + leadZero(scheduler.getState().max_date.getMonth() + 1, 2) + '-' + leadZero(scheduler.getState().max_date.getDate(), 2);
      scheduler.clearAll();
      $.post(baseUrl + 'schedule/ajax_my_task', {my_task : my_task}, function (resp) {
        if(my_task == true)
          scheduler.load(baseUrl + "schedule/office/data?from=" + min_date + "&to=" + max_date + "&user_id=" + resp.id, "json", function(){$('#processing-modal').modal('hide');});
        else
          scheduler.load(baseUrl + "schedule/office/data?from=" + min_date + "&to=" + max_date, "json", function(){$('#processing-modal').modal('hide');});

        showTaskPdf(my_task, min_date, scheduler.getState().mode == 'day' ? '' : max_date);
        return false;
      }, 'json');

      return true;
    });
    $('#teams-routes-list').on('change', function(){

      var choosenId = $(this).val();
      var addressSet =[];

      $.each($('#teams-routes-list option'), function(key, val){
        id = $(val).val();
        $(val).removeAttr('selected');
        if(directionsRenderer[routes[id]] !== undefined)
        {
          directionsRenderer[routes[id]].setMap(null);
        }
      });

      color = '#1796b0';

      if(allStickers[choosenId][0].color != '')
        color = allStickers[choosenId][0].color;
      $.each(allStickers[choosenId], function(k, v){
          if(v.task_address != null && v.task_address != 'null')
            addressSet.push(v.task_address + '+' + v.task_city + '+' + v.task_state + '+<?php echo config_item('office_country'); ?>');
      });

      $('#teams-routes-list [value="'+ choosenId +'"]').prop('selected', true);
      if(directionsRenderer[routes[choosenId]] == undefined)
        requestDirections('<?php echo addslashes(config_item('office_address')); ?>, <?php echo addslashes(config_item('office_city')); ?>, <?php echo addslashes(config_item('office_state')); ?>, <?php echo addslashes(config_item('office_country')); ?>', addressSet[addressSet.length - 1],{ strokeColor:color },addressSet, choosenId);
      if(directionsRenderer[routes[choosenId]] !== undefined)
        directionsRenderer[routes[choosenId]].setMap(mapRoutes);
    });
  });

  function leadZero(number, length) {
    while(number.toString().length < length){
      number = '0' + number;
    }
    return number;
  }
  function showTaskPdf(user_id = false, min_date = false, max_date = false)
  {
    var obj = $('#scheduler_here').find('.task_list_pdf');
    if(!user_id)
    {
      $(obj).addClass('hide');
      $(obj).removeAttr('href');
    }
    else
    {
      $(obj).removeClass('hide');
      $(obj).attr('href', base_url + 'schedule/tasks_list/' + user_id + '/' + min_date + '/' + max_date);
    }
  }

  function initializeRoutes() {
    directionsRenderer = [];
    routes = [];

    $('#routesMap').css('opacity', 0);
    $('#teams-routes-list').html('');
    setTimeout(function() {
      var mapOptions = {
        zoom: 10,
        center: new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON),
        mapTypeId: google.maps.MapTypeId.ROADMAP
      };
      mapRoutes = new google.maps.Map(document.getElementById('routesMap'), mapOptions);
      directionsService = new google.maps.DirectionsService();
      var addresses = [];
      var end = [];
      var blocks = $('.dhx_cal_data [event_id]');
      var today = $('.dhx_scale_holder_now > [event_id]');
      var allBlocks = {};

      $.each($(today), function(key, val){
        blocks.push(val);
      });
        console.log(blocks);
      $.each(blocks , function(key, val){
        blockData = scheduler.getEvent($(val).attr('event_id'));

        date = blockData['start_date'].getUTCFullYear() + (blockData['start_date'].getUTCMonth() + 1 < 10 ? '0':'') + blockData['start_date'].getDate();
        jkey = parseInt(date + blockData['assign']);
        if(allBlocks[jkey] == undefined)
          allBlocks[jkey] = $(blockData);
        else
          allBlocks[jkey].push(blockData);

      });
      var size = Object.keys(allBlocks).length;

      var step = 1;
      allStickers =  allBlocks;
      $.each(allBlocks, function(key, val){
        addresses = [];
        color = '#1796b0';

        if(val[0].color != '')
          color = val[0].color;
        teamId = key;
        $.each(val, function(k, v){
            if(v.task_address != null && v.task_address != 'null')
                addresses.push(v.task_address + '+' + v.task_city + '+' + v.task_state + '+<?php echo config_item('office_country'); ?>');
        });
        requestDirections('<?php echo addslashes(config_item('office_address')); ?>, <?php echo addslashes(config_item('office_city')); ?>, <?php echo addslashes(config_item('office_state')); ?>, <?php echo addslashes(config_item('office_country')); ?>', addresses[addresses.length - 1],{ strokeColor:color },addresses, teamId);

        //if(step == size) {
        $('#routesMap').animate({'opacity':1}, 'slow');

        setTimeout(function() {
          google.maps.event.trigger(mapRoutes, "resize");
          mapRoutes.setCenter(new google.maps.LatLng(MAP_CENTER_LAT, MAP_CENTER_LON));
          mapRoutes.setZoom(10);
        }, 500);
        return false;
      });

      $.each(allBlocks, function(key, val){
        selected = ''
        date = val[0].start_date.getHours() + ':' + (val[0].start_date.getMinutes()<10?'0':'') + val[0].start_date.getMinutes() + '-' + val[0].end_date.getHours() + ':' + (val[0].end_date.getMinutes()<10?'0':'') + val[0].end_date.getMinutes()  + ' ' + val[0].start_date.getUTCFullYear() + '-' + (val[0].start_date.getUTCMonth() + 1 < 10 ? '0':'') + (val[0].start_date.getUTCMonth() + 1) + '-' + val[0].start_date.getDate();
        teamName = val[0].ass_firstname + ' ' + val[0].ass_lastname + '<p class="h6">(' + date + ')</p>';
        if(teamId == key)
          selected = 'selected="selected"';
        $('#teams-routes-list').append('<option  '+selected+' style="m-n" value="' + key + '">' + teamName + '</option>');
        if(teamId == key)
        {
          //$('#teams-routes-list [value="'+ teamId +'"]').prop('selected', true);
        }
      });
    }, 200);
  }


  function requestDirections(start, end, polylineOpts, points, teamId) {

    var waypoints = [];
    $.each(points, function(key, val){
      if(key != points.length - 1)
        waypoints.push({location:val,stopover:true});
      if(waypoints.length == 8)
        return false;
    });
    directionsService.route({
      origin: start,
      destination: end,
      waypoints: waypoints,
      region: teamId.toString(),
      travelMode: google.maps.DirectionsTravelMode.DRIVING,
      avoidTolls: true

    }, function(result, status) {
      renderDirections(result, polylineOpts, mapRoutes);
    });
    // return false;
  }
  function renderDirections(result, polylineOpts) {

    var key = directionsRenderer.length;
    directionsRenderer[key] = new google.maps.DirectionsRenderer();
    directionsRenderer[key].setMap(mapRoutes);

    if(polylineOpts) {
      directionsRenderer[key].setOptions({
        polylineOptions: polylineOpts
      });
    }

    directionsRenderer[key].setDirections(result);
    routes[directionsRenderer[key].directions.request.region] = key;

  }

  $('[data-toggle="class:nav-xs"]').click(function(){
      setTimeout(function(){
          scheduler.set_sizes();
          scheduler.update_view()
      }, 100);
  });

</script>

<div id="scheduler_here" class="dhx_cal_container" style='width:100%; height:100%;'>
    <div class="dhx_cal_navline">
        <div class="dhx_cal_prev_button">&nbsp;</div>
        <div class="dhx_cal_next_button">&nbsp;</div>
        <div class="dhx_cal_today_button"></div>
        <div class="dhx_cal_date"></div>
        <div class="dhx_cal_tab" name="day_tab" style="right:204px;"></div>
        <div class="dhx_cal_tab" name="week_tab" style="right:140px;"></div>
        <div class="dhx_cal_tab" name="month_tab" style="right:76px;"></div>

        <div class="btn-group dhx_cal_tab_standalone no-shadow" name="users_tab">
            <button data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle">
                <span class="dropdown-label">Choose User</span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-select choosenUser" style="height: 300px; overflow-y: scroll;">
                <li class="active">
                    <a href="#" data-user-id="0">
                        <input type="radio" name="d-s-r" checked="checked">Choose User
                    </a>
                </li>
                <?php foreach($users as $key=>$user) : ?>
                    <li class="">
                        <a href="#" data-user-id="<?php echo $user->id;?>">
                            <input type="radio" name="d-s-r" checked="">
                            <span style="border: 1px solid #000;display: inline-block;width: 17px;height: 17px;background: <?php echo $user->color;?>">&nbsp;</span>
                            <?php echo $user->firstname;?> <?php echo $user->lastname;?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-xs btn-default btn-mini block hide task_list_pdf" target="_blank" title="Task List PDF" style="margin: 4px 0px 0px 13px;"><i class="fa fa-file"></i></a>
            <label class="checkbox pull-left hide" style="margin-left: 15px;">
                <input type="checkbox" name="office_my_task" class="office_my_task" <?php if(isset($user_task) && $user_task) : ?>checked<?php endif; ?>>My Tasks Only
            </label>
        </div>
        <div class="btn btn-default no-shadow routes" style="padding: 5px 5px; right: 220px;" name="routes_tab" onclick="initializeRoutes();" href="#crewsRoutes" type="button" role="button" data-toggle="modal" data-backdrop="true" data-keyboard="true">Show Map</div>
    </div>
    <div class="dhx_cal_header">
    </div>
    <div class="dhx_cal_data">
    </div>
</div>
<?php $this->load->view('clients/letters/client_letters_modal'); ?>
<?php $this->load->view('clients/partials/send_appointment_sms_modal'); ?>
<div id="crewsRoutes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 1000px;">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Routes</header>
            <div class="modal-body p-n">
                <div class="affix" style="top: 50px; right: 55px; z-index: 1;">

                    <select   class="nav navbar-nav navbar-right m-n hidden-xs nav-user form-control" id="teams-routes-list" style="right: -70px;  height: 40px; max-height: 500px;">

                    </select>
                </div>
                <div id="routesMap" style="height: 500px;">
                </div>
            </div>
        </div>
    </div>
</div>


<?php $this->load->view('includes/footer'); ?>
