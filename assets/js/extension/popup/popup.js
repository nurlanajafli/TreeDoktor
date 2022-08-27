var bgp = chrome.extension.getBackgroundPage();
var Twilio = bgp.Twilio;
var worker_sid = bgp.getData('worker_sid');
var workspace_sid = bgp.getData('workspace_sid');
var baseUrl = 'https://td.onlineoffice.io/';

if(typeof(soundManager) != "undefined") {
	soundManager.setup({
		url: baseUrl + 'assets/js/soundmanager/swf'
	});
}


var SP = {
    actvities: {
        ready: bgp.wsc.activities.ready,
        notReady: bgp.wsc.activities.notReady
    },
    active_call: false,
    renderView: function(params){
        if(params.empty_template_id!=undefined){
            var template = $.templates(params.empty_template_id);
            var htmlOutput = template.render([{message:params.empty_message}]); 
        }
        
        if(params && params.data != undefined && params.data.length){
            var template = $.templates(params.template_id);
            var htmlOutput = template.render(params.data);
        }

        if(params.render_method==undefined)
            $(params.view_container_id).html(htmlOutput);
        if(params.render_method=='append')
            $(params.view_container_id).append(htmlOutput);
    },
    action_buttons_reset: function(){
        SP.action_buttons = [{
            call:'hidden', send:'hidden', answer:'hidden', hangup:'hidden',
            wrapup:'hidden', mute:'hidden', unhold:'hidden', forward:'hidden'
        }];
    },
    render_actions_btn: function(){
        view = {
            template_id:'#action-buttons-tmp',
            view_container_id:'#action-buttons',
            data:SP.action_buttons
        }
        SP.renderView(view);  
    },
    render_workers: function(data){
        $('.agents-status .dropdown-menu li, #online-workers li').remove();
        var workers_res = [];
        $.each(data.workers, function(i, v){
            attributes = jQuery.parseJSON(v.attributes);
            tmp = {
                contact_uri:attributes.contact_uri,
                available:v.available,
                friendlyName:v.friendlyName,
                linkClass:data.linkClass,
                sid:v.sid
            }
            workers_res.push(tmp);
        });

        view2 = {
            template_id:'#agents-list-tmp',
            view_container_id:'#agents-list-container',
            data:workers_res,
            empty_template_id:'#agents-list-empty-tmp',
            empty_message:'Empty',
            render_method:'append'
        };
        SP.renderView(view2);

        view3 = view2;
        view3.view_container_id = "#online-workers";
        SP.renderView(view3);
    },
    render_call_history: function(container, data){
        view = {
            template_id:'#calls-history-list-tmp',
            view_container_id:container,
            data:data,
            empty_template_id:'#calls-history-empty-tmp',
            empty_message:'Not found'
        };
        SP.renderView(view);
    },

    registerTaskRouterCallbacks: function () {

        view = {
            template_id:'#agents-num-tmp', view_container_id:'#agents-num',
            data:[{agents_count:bgp.wsd.agentsCounter}]
        };
        SP.renderView(view);
        bgp.wsd.agentsData.linkClass = 'callToAgent';
        if(bgp.wsd.agentsData.workers.length)
            SP.render_workers(bgp.wsd.agentsData);

        SP.render_call_history('#all-calls-history', bgp.wsd.history.calls);
        SP.render_call_history('#my-calls-history', bgp.wsd.history.my_calls);
        SP.render_call_history('#voices-history', bgp.wsd.history.voices);
        soundManager.reboot();


        var template = $.templates('');
        var htmlOutput = template.render([{message:'Queue is empty'}]);

        var response = [];
        if(bgp.wsd.tasks) {
            $.each(bgp.wsd.tasks, function(i, v){ response.push(v); });
        }
        
        tpl = {
            template_id:'#in-queues-list-tmp',
            view_container_id:'#in-queues-list',
            data:response,
            empty_template_id:'#in-queues-list-empty-tmp',
            empty_message:'Queue is empty'
        };
        SP.renderView(tpl);            

        tpl2 = {
            template_id:'#queues-count-tmp',
            view_container_id:'#queues-count-result',
            data:[{"queuesize":bgp.wsd.queuesize}],
        };
        SP.renderView(tpl2);

        if(bgp.Twilio.Device.status() == 'ready')
            $('.createCall.disabled').removeClass('disabled');

        switch(bgp.ctp.state) {
            case 'idle':
                SP.setIdleState();
                break;
            case 'oncall':
                SP.setOnCallState();
                break;
            case 'ring':
                SP.setRingState();
                break;
            default:
                SP.setIdleState();
                break;
        }
    },

    notReady: function () {
        if(bgp.wsc.worker.activity == 'Idle') {
            bgp.wsc.worker.update("ActivitySid", SP.actvities.notReady, function (error, worker) {});
            $("#agent-status-controls > button.ready").prop("disabled", false);
            $("#agent-status-controls > button.not-ready").prop("disabled", true);
        }
    },

    ready: function () {
        if(bgp.wsc.worker.activity == 'Offline') {
            bgp.wsc.worker.update("ActivitySid", SP.actvities.ready, function (error, worker) {});
            $("#agent-status-controls > button.ready").prop("disabled", true);
            $("#agent-status-controls > button.not-ready").prop("disabled", false);
        }
    },

    updateStatus: function (error, worker) {
        if (bgp.wsc.worker && bgp.wsc.worker.hasOwnProperty('activityName') && bgp.wsc.worker.activity == "Idle") {
            SP.updateAgentStatusText("ready", "Ready");
            if(bgp.Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
                SP.connection(bgp.Twilio.Device.activeConnection());
        } else {
            SP.updateAgentStatusText("notReady", "Not Ready");
        }
    },

    updateAgentStatusText: function (statusCategory, statusText, inboundCall, outboundCall) {

        if (statusCategory == "ready") {

            $("#agent-status-controls > button.ready").prop("disabled", true);
            $("#agent-status-controls > button.not-ready").prop("disabled", false);
            $("#agent-status").removeClass();
            $("#agent-status").addClass("ready");
            $('#softphone').removeClass('incoming');
        }

        if (statusCategory == "notReady") {
            $("#agent-status-controls > button.ready").prop("disabled", false);
            $("#agent-status-controls > button.not-ready").prop("disabled", true);
            $("#agent-status").removeClass();
            $("#agent-status").addClass("not-ready");
            $('#softphone').removeClass('incoming');
        }

        if (statusCategory == "onCall") {
            $("#agent-status-controls > button.ready").prop("disabled", true);
            $("#agent-status-controls > button.not-ready").prop("disabled", true);
            $("#agent-status").removeClass();
            $("#agent-status").addClass("on-call");
            $('#softphone').removeClass('incoming');
        }

        if(bgp.wsc.worker.activity == 'Idle') {
            $("#agent-status-controls > button.not-ready").prop("disabled", false);
            $("#agent-status-controls > button.ready").prop("disabled", true);
        }
        if(bgp.wsc.worker.activity == 'Offline') {
            $("#agent-status-controls > button.not-ready").prop("disabled", true);
            $("#agent-status-controls > button.ready").prop("disabled", false);
        }

        if (inboundCall == true) {
            $('#softphone').addClass('incoming');
        }
        if(outboundCall == true || inboundCall == true)
            $("#number-entry > input").val(statusText);
    },

    setIdleState: function () {
        if(bgp.Twilio.Device.instance!=undefined && bgp.Twilio.Device.instance && bgp.Twilio.Device.instance.hasOwnProperty('soundcache'))
            bgp.Twilio.Device.instance.soundcache.stop('incoming');
        SP.updateAgentStatusText('ready');

        if(bgp.Twilio.Device.activeConnection()!=undefined)
            bgp.Twilio.Device.activeConnection().reject();

        SP.action_buttons_reset();
        SP.action_buttons[0].call = '';
        SP.render_actions_btn();

        $('div.agent-status').hide();
        $("#number-entry > input").val("");
        $('div.numpad-container').show();
        $('div#messages').hide();
    },

    setRingState: function () {
        $('div.agent-status').show();
        SP.updateAgentStatusText("ready", (bgp.Twilio.Device.activeConnection().parameters.From), true);
        SP.action_buttons_reset();
        SP.action_buttons[0].answer = '';
        SP.render_actions_btn();

        $('div.numpad-container').show();
        $('div#messages').hide();
    },

    setOnCallState: function () {
        SP.action_buttons_reset();
        SP.action_buttons[0].mute = '';
        $('button.mute').text('Mute');

        //can not hold outbound calls, so disable this
        if (bgp.Twilio.Device.activeConnection() && bgp.Twilio.Device.activeConnection()._direction == "INCOMING") {
            SP.updateAgentStatusText("onCall", (bgp.Twilio.Device.activeConnection().parameters.From), true);
            SP.action_buttons[0].hold = '';
            SP.action_buttons[0].forward = '';
        }
        if(bgp.Twilio.Device.activeConnection() && bgp.Twilio.Device.activeConnection()._direction == "OUTGOING") {
            var to = bgp.Twilio.Device.activeConnection().message.To ? bgp.Twilio.Device.activeConnection().message.To : bgp.Twilio.Device.activeConnection().message.agent;
            SP.updateAgentStatusText("onCall", to, false, true);
        }
        SP.action_buttons[0].hangup = '';
        SP.render_actions_btn();

        console.log(bgp.Twilio.Device.activeConnection().isMuted(), bgp.Twilio.Device.activeConnection());

        if(bgp.Twilio.Device.activeConnection() && bgp.Twilio.Device.activeConnection().isMuted()) {
            $('button.mute').text('UnMute');
        }

        $('div.agent-status').show();
    },

    hideCallData: function () {
        $("#call-data").hide();
    },

    connection: function(conn){
        SP.setOnCallState();
        SP.currentCall = conn;
    },

    handleKeyEntry: function (key) {
        if (bgp.Twilio.Device.activeConnection() != null && bgp.Twilio.Device.activeConnection() != undefined) {
            bgp.Twilio.Device.activeConnection().sendDigits(key);
        }
    },

    mute: function() {
        var muted = bgp.Twilio.Device.activeConnection().isMuted();

        if(muted) {
            Twilio.Device.activeConnection().mute(false);
            $('button.mute').text('Mute');
        }
        else {
            Twilio.Device.activeConnection().mute(true);
            $('button.mute').text('UnMute');
        }
    }


};

$.views.helpers({
    baseUrl: baseUrl,
    call_dates: {
        is_today: function(val) {
            date = new Date(val)
            
            date_date = date.getDate();
            date_month = date.getMonth() + 1;
            date_year = date.getFullYear();
            result_date = date_year + "-" + date_month + "-" + date_date;

            d = new Date();
            curr_date = d.getDate();
            curr_month = d.getMonth() + 1;
            curr_year = d.getFullYear();
            curr_full = curr_year + "-" + curr_month + "-" + curr_date;

            if(result_date==curr_full)
                return true;
            return false;
        },
        callDateFormat: function(val) {
            date = new Date(val);
            
            monthName = date.toLocaleString('en-us', { month: "short" });
            dayNumber = ('0' + date.getDate()).slice(-2);
            return monthName + ' ' + dayNumber;
        },
        callTime: function(val) {
            date = new Date(val);
      
            hours = ('0' + date.getHours()).slice(-2);
            minutes = ('0' + date.getMinutes()).slice(-2);
            return hours + ':' + minutes;
        },
        callDuration: function(val) {
            minutes = parseInt(val / 60);
            seconds = parseInt(val % 60);
            return minutes + 'm. ' + seconds + 's.';
        },
        callUnixtime: function() {
            time = + new Date();
            return time;
        }
    }
});

$(document).ready(function() {
    setTimeout(function(){
        $("#number-entry > input").focus();
    }, 1000);
});

$(document).on('click', "#action-buttons > button.answer", function () {
    SP.active_call = true;
    bgp.ctp.take();
    SP.setOnCallState();

    if(bgp.Twilio.Device.activeConnection()!=undefined && isNaN(parseInt(bgp.Twilio.Device.activeConnection().options.callParameters.From))){
        //SP.action_buttons[0].mute = 'hidden';
        SP.render_actions_btn();
    }
});

$(document).on('click', "#action-buttons > button.call", function () {
    SP.active_call = true;
    params = {"To":$("#number-entry > input").val()};
    bgp.ctp.call(params);
});

$('.agents-status').on('shown.bs.dropdown', function () {
    if($('.agents-status').is('.open'))
        linkClass = 'callToAgent';
    else
        linkClass = 'forward-in-support';
    SP.render_workers({'workers':bgp.wsd.agentsData.workers, linkClass:linkClass});
});

$(document).on('submit', '#searchInHistoryForm', function() {
    var query = $('.searchInHistoryValue').val();
    $(this).parents('.form-group:first').removeClass('has-error');
    $('.searchInHistoryValue').tooltip('destroy');
    if(query.length < 3) {
        $(this).parents('.form-group:first').addClass('has-error');
        $('.searchInHistoryValue').tooltip('show');
        return false;
    }
    bgp.ws.send({method:'searchInHistory', params:{query:query}});
    return false;
});

$(document).on('click', '.callToAgent', function(){
    SP.active_call = true;
    params = {"agent": $(this).data('contact_uri')};
    bgp.ctp.call(params);
    
    if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
        SP.connection(Twilio.Device.activeConnection());

    if(Twilio.Device.activeConnection()._direction=="OUTGOING"){
        SP.action_buttons[0].mute = 'hidden';
        SP.render_actions_btn();
    }

});

$(document).on('click', 'a.outgoing-call', function () {
    SP.active_call = true;
    $('#number-entry input').val($(this).data('phone'));
    params = {"PhoneNumber": $.trim($(this).data('phone'))};
    bgp.ctp.call(params);
});

// Hang up button will hang up any active calls
$(document).on('click', "#action-buttons > button.hangup", function () {
    //ws.send({method:'unsetCallSession'});
    SP.active_call = false;
    bgp.ctp.kill();
    bgp.reservation = {};
    SP.setIdleState();
});

// Wire the ready / not ready buttons up to the server-side status change functions
$("#agent-status-controls > button.ready").click(function () {
    SP.ready();
});

$("#agent-status-controls > button.not-ready").click(function () {
    SP.notReady();
});

$(document).on('click', '#forward-call-in-worker', function(){
    bgp.ws.send({method:'getAvailableAgents', params:{worker_sid:worker_sid}});
    return false;
});

$(document).on('click', '#forward-call-in-phone', function(){
    bgp.ws.send({method:'getForwardContacts', params:{worker_sid:worker_sid}});
    return false;
});

$(document).on('click', '.forward-in-support', function(){
    var call_sid = bgp.Twilio.Device.activeConnection().parameters.CallSid;
    if(bgp.reservation != undefined && bgp.reservation.task != undefined)
        call_sid = bgp.reservation.task.attributes.call_sid;
    
    var params = {
        contact_uri:$(this).data('contact_uri'),
        call_sid:call_sid,
        worker_sid:worker_sid
    };

    SP.active_call = false;

    bgp.ws.send({method:'forwardCallToAgent', params:params});
    return false;
});

$(document).on('click', '.forward-in-number', function(){//testik

    var call_sid = bgp.Twilio.Device.activeConnection().parameters.CallSid;
    if(bgp.reservation != undefined && bgp.reservation.task != undefined)
        call_sid = bgp.reservation.task.attributes.call_sid;

    var params = {
        number:$(this).data('number'),
        call_sid:call_sid,
        worker_sid:worker_sid
    };

    SP.active_call = false;

    bgp.ws.send({method:'forwardCallToNumber', params:params});
    return false;
});

$(document).on('keyup', '#number-entry input', function(e){
    if(e.keyCode == 13) {
        $('#action-buttons > button.call').click();
    }
});

$(document).on('click', 'button.mute', function(){
    SP.mute();
});

$("div.number").bind('click', function () {
    $("#number-entry > input").val($("#number-entry > input").val() + $(this).attr('Value'));
    //pass key without conn to a function
    SP.handleKeyEntry($(this).attr('Value'));
});

window.onbeforeunload = function(e) {
    /*if(Twilio.Device.activeConnection() != undefined && 
       Twilio.Device.activeConnection()._direction == 'INCOMING' && 
       Twilio.Device.activeConnection()._status == 'open' && 
       !isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From)))
        return SP.connection_loss();
    else {
        if(Twilio.Device.activeConnection())
            return true;
    }*/
    return null;
};

SP.hideCallData();
if (window.self === window.top) {
    SP.action_buttons_reset();
}


$(function () {

    if(!workspace_sid || !worker_sid) {
        $('#login').show();
        $('#softphone').hide();
        return false;
    }
    else {
        $('#login').hide();
        $('#softphone').show();
    }

    SP.registerTaskRouterCallbacks();

    return false;
});
