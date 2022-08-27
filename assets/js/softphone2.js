$(function () {
	
	view = {};
	view.components = {
		'ready':'',
		'answered':'hidden',
		'noanswered':'hidden'
	};

	var template = $.templates('#phone-template');
    var htmlOutput = template.render([view.components]);
	$('#phone-template-view').html(htmlOutput);

	/*
	ca
	view.components = {
		'ready':'',
		'answered':'hidden',
		'noanswered':'hidden'
	};	
	var htmlOutput = template.render([view.components]);
	$('#phone-template-view').html(htmlOutput);
	*/



	/*window.reservation = {};
	window.SP = {};
	SP.functions = {};
	SP.actvities = {};

	SP.actvities = {
        ready: null,
        notReady: null
    };

	SP.functions.startWebSocket = function (worker) {

        var path = '/';
        var segments = window.location.pathname.split('/');

        for (i = 1; i < segments.length - 1; i++)
            path += segments[i] + '/';

        var wsaddress = 'wss://' + baseUrl.replace(/^https:\/\//i, '').replace(/^http:\/\//i, '').replace(/\/+$/, '') + ':8889?worker_sid=' + worker_sid;

        var ws = new ReconnectingWebSocket(wsaddress);
        window.ws = ws;

        ws.onopen = function () {
            
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getToken'}));
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getWorkerToken'}));
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getCallsHistory'}));
 
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getAgentsCounter'}));
        };

        ws.onclose = function () { 
            ///console.log('websocket closed');
        };

        ws.onmessage = function (m) {

            var result = JSON.parse(m.data);

            if(result.payload == 'OFF')
            {
                console.log('OFF command receive');
                return false;
            }

            var payload = JSON.parse(result.payload);
            var method = payload.method + 'Callback';

            if(typeof result.result == 'object')
                eval('if(typeof(ws.' + method + ') == "function") ws.' + method + '(result.result)');
            else
                eval('if(typeof(ws.' + method + ') == "function") ws.' + method + "('" + result.result + "')");
        };

        ws.updateHistoryCallback = function(result) {
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getCallsHistory'}));
        }

        ws.getCallsHistoryCallback = function(result) {
            $('#call-history').replaceWith(result);
            soundManager.reboot();
        };

        ws.getTokenCallback = function(token) {
            Twilio.Device.setup(token, {debug: true});
        };

        ws.getWorkerTokenCallback = function(token) {
            SP.worker = new Twilio.TaskRouter.Worker(token, {debug: false});
            var activitySids = {};

            SP.worker.activities.fetch(
                function(error, activityList) {
                    if(error) {
                        console.log(error.code);
                        console.log(error.message);
                        return;
                    }
                    var data = activityList.data;
                    for(i=0; i<data.length; i++) {
                        if (data[i].available){
                            SP.actvities.ready = data[i].sid
                        } else {
                            SP.actvities.notReady = data[i].sid
                        }
                    }
                    SP.functions.registerTaskRouterCallbacks();
                
                }
            );
        };


        ws.getAgentsCounterCallback = function(data) {
            $("#team-status .agents-num").text(data.count);

            if(data.workers.length)
            {
                $("#team-status .agents-status .dropdown-menu").html('<span class="arrow top"></span>');
                $.each(data.workers, function(key, val){
                    var agentAttributes = $.parseJSON(val.attributes);
                    var agentIconClass = val.available ? 'success' : 'danger';
                    var agentClass = val.available ? 'callToAgent' : 'disabled';
                    var link = `<li>
                        <a href="#" class="` + agentClass + `" data-worker_sid="` + val.sid + `" data-contact_uri="` + agentAttributes.contact_uri + `">
                            ` + val.friendlyName + `
                            <i class="fa fa-circle text-` + agentIconClass + ` text-xs pull-right m-t-xs"></i>
                        </a>
                    </li>`;
                    $("#team-status .agents-status .dropdown-menu").append(link);
                });
            }
            else
            {
                $("#team-status .agents-status .dropdown-menu").html('<span class="arrow top"></span><li>Empty</li>');
            }
        };

        ws.getOnlineWorkersCallback = function(data){
            $('#online-workers').html('');
            var template = $.templates('#online-workers-empty-tmp');
            var htmlOutput = template.render([{message:'No free workers by now'}]);
            if(data.length){
                
                var response = [];
                $.each(data, function(i, v){
                    attributes = JSON.parse(v.attributes);
                    response.push({friendlyName:v.friendlyName, contact_uri:attributes.contact_uri, available:v.available});
                });

                var template = $.templates('#online-workers-tmp');
                var htmlOutput = template.render(response);
            }
            $('#online-workers').html(htmlOutput);
            $("#online-workers li a.disabled").click(function(event) {
                event.preventDefault()
                $("#online-workers").dropdown('toggle');
            })
        };

        ws.getContactsCallback = function(data){
            var template = $.templates('#contacts-empty-tmp');
            var htmlOutput = template.render([{message:'No contacts'}]);
            if(data.length){
                var template = $.templates('#contacts-tmp');
                var htmlOutput = template.render(data);
            }
            
            $('#users-contacts').html(htmlOutput);
        };

    };


    SP.functions.registerTaskRouterCallbacks = function () {
        
        SP.worker.on('ready', function (worker) {
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getAgentsCounter', params:{workspaceSid:SP.worker.workspaceSid}}));
            SP.active_call = false;
        });
        
        SP.worker.on("reservation.canceled", function(task) {
            window.reservation = {};
            SP.active_call = false;
        });

        SP.worker.on("reservation.rescinded", function(task) {
            window.reservation = {};
            SP.active_call = false;
        });

        SP.worker.on("reservation.timeout", function(task) {
            window.reservation = {};
            setTimeout(function(){
                Twilio.Device.instance.soundcache.stop('incoming');
            }, 100);


        });
        
        SP.worker.on("reservation.accepted", function(task) {
            window.reservation = task;
            SP.active_call = true;
        });
        
        SP.worker.on("reservation.rejected", function(task) {
            window.reservation = {};
            SP.active_call = false;
        });

        SP.worker.on('reservation.created', function (task) {
            window.reservation = task;
            SP.active_call = false;
            setTimeout(function(){
                var callerId = $('.queues-status ul li:contains(' + $('#number-entry input').val() + ') a').data('clientid');
                if(callerId && $('.site-iframe').attr('src') != baseUrl + 'iframe/clients/details/' + callerId)
                    $('.site-iframe').attr('src', baseUrl + 'iframe/clients/details/' + callerId);
            }, 300);
        });
    }


    if (window.self === window.top) {
        var defaultclient = {}
        defaultclient.result = SP.username;
        SP.functions.startWebSocket('test');
    }

    

    $("div.number").bind('click', function () {
        $("#number-entry > input").val($("#number-entry > input").val() + $(this).attr('Value'));
        //pass key without conn to a function
        SP.functions.handleKeyEntry($(this).attr('Value'));

    });

    $(document).on('click', '.clientLink', function(){
        if($('.site-iframe').attr('src') != baseUrl + 'iframe/clients/details/' + $(this).data('clientid'))
            $('.site-iframe').attr('src', baseUrl + 'iframe/clients/details/' + $(this).data('clientid'));
        return false;
    });

    SP.functions.handleKeyEntry = function (key) {
        if (SP.currentCall != null) {
            SP.currentCall.sendDigits(key);
        }
    };

    $("#action-buttons > button.call").click(function () {
        SP.active_call = true;
        params = {"PhoneNumber": $("#number-entry > input").val()};
        Twilio.Device.connect(params);
    });

    $(document).on('click', '.callToAgent', function(){
        SP.active_call = true;
        params = {"agent": $(this).data('contact_uri')};
        Twilio.Device.connect(params);
        
        if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
            SP.functions.connection(Twilio.Device.activeConnection());

        if(Twilio.Device.activeConnection()._direction=="OUTGOING")
            $("#action-buttons a.mute").parent().hide();
    });

    $(document).on('click', 'a.outgoing-call', function () {
        SP.active_call = true;
        $('#number-entry input').val($(this).data('phone'));
        params = {"PhoneNumber": $(this).data('phone')};
        Twilio.Device.connect(params);
    });

    $("#action-buttons > button.hangup").click(function () {
        SP.active_call = false;
        Twilio.Device.disconnectAll();
        reservation = {};
    });

    $(document).on('click', '.client-iframe', function(){
        $('.site-iframe').attr('src', $(this).attr('href'));
        return false;
    });

    /*
    $(document).on('click', '#forward-call-in-worker', function(){
        ws.send(JSON.stringify({worker_sid:worker_sid, method:'getOnlineWorkers'}));
        return false;
    });

    $(document).on('click', '#forward-call-in-phone', function(){
        ws.send(JSON.stringify({worker_sid:worker_sid, method:'getContacts'}));
        return false;
    });

   	
    $(document).on('click', '.forward-in-support', function(){
        var call_sid = SP.call_sid;
        if(reservation.task != undefined)
            call_sid = reservation.task.attributes.call_sid;
        
        var params = {
            contact_uri:$(this).data('contact_uri'),
            cid:call_sid
        };

        SP.active_call = false;

        ws.send(JSON.stringify({worker_sid:worker_sid, method:'forwardCallToAgent', params:params}));
        return false;
    });

    $(document).on('click', '.forward-in-number', function(){

        var call_sid = SP.call_sid;
        if(reservation.task != undefined)
            call_sid = reservation.task.attributes.call_sid;

        var params = {
            number:$(this).data('number'),
            cid:call_sid
        };

        SP.active_call = false;

        ws.send(JSON.stringify({worker_sid:worker_sid, method:'forwardCallToNumber', params:params}));
        return false;
    });
	
	

    
    Twilio.Device.incoming(function (conn) {
        // Update agent status
        //sforce.interaction.setVisible(true);  //pop up CTI console
        SP.currentCall = conn;
        SP.functions.updateAgentStatusText("ready", ( conn.parameters.From), true);
        // Enable answer button and attach to incoming call
        SP.functions.attachAnswerButton(conn);
        SP.functions.setRingState();
       

        var inboundnum = cleanInboundTwilioNumber(conn.parameters.From);
        var sid = conn.parameters.CallSid
        SP.call_sid = conn.parameters.CallSid;
        var result = "";
        $('.phone-overlay').show();


    });

    

    SP.functions.ready = function () {

        if(reservation.reject!=undefined)
           reservation.reject();

        SP.worker.update("ActivitySid", 'WAfc57c478bd7cea19883d8908c85b0f6b', function (error, worker) {
          
        });
    }*/


});