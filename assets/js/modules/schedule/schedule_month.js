var ScheduleMonth = function(){
    var config = {

        ui:{
            
        },
        
        events:{
            
        },

        route:{
            
        },
        
        templates:{
        
        }
    }
    
    var _private = {
        init:function(){
        
        },

        resetTeams: function(team_id){

            if(!$('.modal-backdrop.fade.in').is(':visible'))
                $('#processing-modal').modal();

            scheduler.config.drag_create = false;
            scheduler.config.readonly = true;
            scheduler.templates.event_class = function(start, end, ev){
                return "";
            };
            processUpdateSections = true;
            _private.menuElements();
            getScheduleData(function(){
                changeFonts();
                $('#processing-modal').modal('hide');
            });
            processUpdateSections = false;

            if($('.popover').length)
                $('.popover').hide();
        },

        crewsList:function(members, sections){
            $('#crewsList').html('');
        },

        menuElements:function(){
            $('.week-member-filter-view').hide();
            $(".crews-list-container").hide();

            $('.free-members-label').hide();
            //$('.crewsList').hide();
            $('.day-off-btn').hide();

            $(".schedule-stats").hide();

        },
    }

    var selected_date;
    let public = {
        
        init:function(){
            
            $(document).ready(function(){
                //public.events();
                
                if($.cookie('scheduler_mode') && $.cookie('scheduler_mode')=="month"){
                    _private.menuElements();
                    _private.resetTeams();
                }
                
            });
        },
        events:function(){

        },

        init_month:function(){
            _private.resetTeams();
        },

        onViewChange:function(new_mode , new_date){

            _private.menuElements();
            
            /*---------------------- Notes block ---------------------*/
            $('.day-note').css('height', '');
            $('.day-note').show();

            $('.saveNote').css('height', '');
            $('.saveNote').show();
                
            $('.day-note').attr('style', $('.day-note').attr('style') + ';height:' + (parseInt($('.day-note').css('height')) + 1) + 'px!important');
            $('.saveNote').attr('style', $('.saveNote').attr('style') + ';height:' + (parseInt($('.saveNote').css('height')) + 1) + 'px!important');
            /*---------------------- Notes block ---------------------*/

            scheduler.config.drag_create = false;
            scheduler.config.readonly = true;
            scheduler.xy.scale_height = -60;

            recalcSizes();

            if(processUpdateSections)
            {
                $('#processing-modal').modal();
                return true;
            }

            $('#crewsList').parent().hide();

            _private.resetTeams();

            $('.dhx_cal_data').css('height', ($('#content').height() - ($('.dhx_cal_navline').height() + $('.dhx_cal_header').height() + $('#crewsList').height())) + 'px');
            return true;
        },


        resetTeams:function(team_id){
            _private.resetTeams(team_id);
        }
    }

    public.init();
    return public;
}();
