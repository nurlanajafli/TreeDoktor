
var ScheduleTimelineCore = function(){
    let config = {
        marked_types: {

            default: {
                css:"",
                type: "says",
                html: ""
            },

            holiday: {
                css:"holiday",
                type: "dhx_time_block",
                html: "Day Off"
            },

            busy: {
                css:  "busy-in-team",
                type: "dhx_time_block",
                html: "On other crew",
            },

            no_team: {
                css:   "no-team",
                type:  "says",
                html: "Create a crew",
            },

            blocked: {
                css: "fat_lines_section",
                type: "dhx_time_block",
                html: ""
            },
        }
    };

    let public = {
        init: function(){

        },

        add_time_marker:  function(section_id, from, to, typeKey){

            type = (config.marked_types[typeKey]!=undefined)?config.marked_types[typeKey]:config.marked_types['default'];
            scheduler.addMarkedTimespan({
                css:  type.css,
                type: type.type,
                html: type.html,
                start_date: moment(from)._d,
                end_date: moment(to)._d,
                sections:{
                    timeline: parseInt(section_id),
                }
            });
        },

        delete_time_marker: function(timeline_id, from, to, type){
            if(!type || type==undefined)
                type = '';
            scheduler.deleteMarkedTimespan({
                start_date:moment(from)._d,
                end_date:moment(to)._d,
                type:type,
                sections:{
                    timeline: parseInt(timeline_id)
                }
            });
        },

        clear_all_timespan:function(){
            scheduler.deleteMarkedTimespan();
            scheduler.updateView();
        },

        dates: {
            scheduler_start_date: function () {
                return moment(scheduler.getState().min_date).format("YYYY-MM-DD");
            },

            scheduler_end_date: function () {
                return moment(scheduler.getState().max_date).format("YYYY-MM-DD");
            },

            scheduler_start: function () {
                return moment(scheduler.getState().min_date).format("YYYY-MM-D")+' '+scheduler.config.first_hour+":00";
            },

            scheduler_end: function () {
                return moment(scheduler.getState().max_date).format("YYYY-MM-D")+' '+scheduler.config.last_hour+":00";
            },

            date_in_interval:function (date, interval) {

                if(moment(interval.from).unix() <= moment(date).unix() && moment(interval.to).unix() >= moment(date).unix())
                    return true;

                return false;
            },

            interval_size:function(from, to){
                interval = moment(to).unix()-moment(from).unix();
                nights = moment(moment(to).format("YYYY-MM-D")).diff(moment(from).format("YYYY-MM-D"), 'days');
                if(nights > 0){
                    night_size = (24-parseFloat(SCHEDULER_ENDS_AT))+parseFloat(SCHEDULER_STARTS_FROM);
                    interval = interval-(night_size*nights*3600);
                }
                return interval;
            },

            dayStart: function (date) {
                return date+' '+scheduler.config.first_hour+":00";
            },

            dayEnd: function (date) {
                return date+' '+scheduler.config.last_hour+":00";
            },

            dayStartUnix: function (date) {
                return moment(public.dates.dayStart(date)).unix();
            },

            dayEndUnix: function (date) {
                return moment(public.dates.dayEnd(date)).unix();
            }
        }
    };

    public.init();
    return public;
}();