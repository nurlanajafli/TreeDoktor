<?php $this->load->view('includes/account/header'); ?>
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/codebase/dhtmlxscheduler.css'); ?>"
          type="text/css" media="screen" title="no title" charset="utf-8">
    <section>
        <section class="hbox stretch">
            <!-- .aside -->
            <?php $this->load->view('includes/account/sidebar'); ?>
            <!-- /.aside -->
            <section id="content">
                <section class="vbox">
                    <header class="header b-b b-light padder-v text-center">
                        <?php $this->load->view('schedule/client_form'); ?>
                    </header>
                    <section class="scrollable padder m-t-md">


                        <?php $this->load->view('schedule/calendar'); ?>

                    </section>
                </section>
            </section>
        </section>
    </section>
    <script>
        let dp = {};

        dp = new dataProcessor(baseUrl + "account/schedule/data");

        $(document).ready(function () {
            $('.phone').inputmask(PHONE_NUMBER_MASK);
            $('.no').inputmask("99999");

            $('#getSchedule').on('submit', function() {
                let phone = $(this).find('.phone').inputmask('unmaskedvalue');
                let no = $(this).find('.no').val();
                scheduler.load(baseUrl + 'account/schedule/data?phone=' + phone + '&no=' + no, "json", function(){

                });
                return false;
            });

            scheduler.config.first_hour = 7;
            scheduler.config.last_hour = 19;
            scheduler.config.touch = true;
            scheduler.xy.scale_height = 20;
            scheduler.config.drag_create = false;
            scheduler.config.readonly = true;
            scheduler.config.hour_size_px = 60;
            //let format = $('#timeFormat').val();
            let timeFormat = "%h:%i %a";//"%H:%i";
            /*if(format == 12){
                timeFormat = "%h:%i %a";
            }*/
            //scheduler.config.hour_date = timeFormat;
            scheduler.config.xml_date = "%Y-%m-%d %H:%i";
            scheduler.config.default_date = "%l, %j %F %Y";

            scheduler.templates.hour_scale = function(date) {
                let hour = date.getHours();
                let top = '00';
                let bottom = '30';
                hour = date.getHours();
                if(hour==0)
                    top = 'AM';
                if(hour==12)
                    top = 'PM';
                hour =  ((date.getHours()+11)%12)+1;
                let html = '';
                let section_width = Math.floor(scheduler.xy.scale_width/2);
                let minute_height = Math.floor(scheduler.config.hour_size_px/2);
                html += "<div class='dhx_scale_hour_main' style='width: "+section_width+"px; height:"+(minute_height*2)+"px;'>"+hour+"</div><div class='dhx_scale_hour_minute_cont' style='width: "+section_width+"px;'>";
                html += "<div class='dhx_scale_hour_minute_top' style='height:"+minute_height+"px; line-height:"+minute_height+"px;'>"+top+"</div><div class='dhx_scale_hour_minute_bottom' style='height:"+minute_height+"px; line-height:"+minute_height+"px;'>"+bottom+"</div>";
                html += "<div class='dhx_scale_hour_sep'></div></div>";
                return html;
            };

            scheduler.init('scheduler_here', new Date(), 'month');

            dp = new dataProcessor(baseUrl + "account/schedule/data");
            dp.init(scheduler);
            dp.setTransactionMode("POST", true);
        });

        function show_minical() {
            if (scheduler.isCalendarVisible()) {
                scheduler.destroyCalendar();
            } else {
                scheduler.renderCalendar({
                    position:"dhx_minical_icon",
                    date:scheduler._date,
                    navigation:true,
                    handler:function(date,calendar){
                        var mode = scheduler.getState().mode;
                        scheduler.setCurrentView(date, mode);
                        scheduler.destroyCalendar()
                    }
                });
            }
        }
    </script>
    <style>
        .dhx_cal_event_cont_selected{
            background-color: #9cc1db;
            color: white;
        }
        .dhx_scale_hour_main {
            float: left;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            padding-top: 9px;
        }
        .dhx_scale_hour_minute_cont {
            float: left;
            position: relative;
            text-align: right;
        }
        .dhx_scale_hour_minute_top, .dhx_scale_hour_minute_bottom {
            font-size: 10px;
            padding-right: 5px;
        }
        .dhx_scale_hour_sep {
            position: absolute;
            height: 1px;
            background-color: #8C929A;
            right: 0;
            top: 30px;
            width: 20px;
        }
        .dhx_scale_holder {
            background-image: url("/assets/img/databg.png")!important;
        }
        .dhx_scale_holder_now {
            background-image: url("/assets/img/databg_now.png")!important;
        }
        .client_calendar div.dhx_minical_icon {
            left: 211px!important;
        }

        .dhx_cal_event .dhx_body, .dhx_cal_event .dhx_footer, .dhx_cal_event .dhx_header, .dhx_cal_event .dhx_title {
            color: #0E64A0;
        }
    </style>
    <script src="<?php echo base_url('assets/js/jquery.inputmask.bundle.js'); ?>"></script>
<?php $this->load->view('includes/account/footer'); ?>
