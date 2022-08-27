$(function () {

	window.SP = {};
	window.reservation = {};

	SP.functions = {};
	SP.active_call = false;
	SP.state = {};
    SP.state.callNumber = null;
    SP.state.calltype = "";
    SP.username = $('#client_name').text();
    SP.currentCall = null;  //instance variable for tracking current connection
    SP.currentTask = null;
    SP.requestedHold = false; //set if agent requested hold button
    SP.worker = null;
    SP.queue = null;
    SP.actvities = { ready: null, notReady: null };
	SP.action_buttons = [];

	$.views.helpers({
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
	
	/*--------------------SP Helpers -------------------*/
	SP.functions.action_buttons_reset = function(){
		SP.action_buttons = [{
        	call:'hidden', send:'hidden', answer:'hidden', hangup:'hidden',
        	wrapup:'hidden', mute:'hidden', unhold:'hidden', forward:'hidden'
        }];
	}
	SP.functions.render_actions_btn = function(){
		view = {
			template_id:'#action-buttons-tmp',
			view_container_id:'#action-buttons',
			data:SP.action_buttons
		}
		SP.functions.renderView(view);	
	}
	
	/* template_id view_container_id data
	empty_template_id empty_message render_method */
    SP.functions.renderView = function(params){
    	
    	if(params.empty_template_id!=undefined){
    		var template = $.templates(params.empty_template_id);
        	var htmlOutput = template.render([{message:params.empty_message}]);	
    	}
        
        if(params.data.length){
            var template = $.templates(params.template_id);
            var htmlOutput = template.render(params.data);
        }

        if(params.render_method==undefined)
        	$(params.view_container_id).html(htmlOutput);
        if(params.render_method=='append')
        	$(params.view_container_id).append(htmlOutput);
    }

    SP.functions.render_workers = function(data){
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
		SP.functions.renderView(view2);

		view3 = view2;
		view3.view_container_id = "#online-workers";
		SP.functions.renderView(view3);
	}

	SP.functions.render_call_history = function(container, data){
		view = {
			template_id:'#calls-history-list-tmp',
			view_container_id:container,
			data:data,
			empty_template_id:'#calls-history-empty-tmp',
			empty_message:'Not found'
		};
		SP.functions.renderView(view);
	}
    /*--------------------SP Helpers -------------------*/



	SP.functions.registerTaskRouterCallbacks = function () {
        SP.worker.on('ready', function (worker) {
            //reservation = {};
            SP.active_call = false;
            SP.functions.notReady();
            
	        ws.send({method:'getToken', params:{contact_uri:worker.attributes.contact_uri}});
	        ws.send({method:'getCallsHistory'});
	        ws.send({method:'getQueueToken'});
	        ws.send({method:'getAgentsCounter'});

	        SP.functions.updateAgentStatusText("ready", "Ready");
	        SP.functions.setIdleState();
	        //SP.worker.update("ActivitySid", SP.actvities.ready, function (error, worker) {});
        });

        SP.worker.on('activity.update', function (worker) {
            SP.functions.updateStatus('', worker);
        });
        SP.worker.on("error", function(error) {
            console.log("Websocket had an error: "+ error.response + " with message: "+error.message);
        });
        SP.worker.on("reservation.canceled", function(task) {
            window.reservation = {};
            SP.functions.setIdleState();
        });

        SP.worker.on("reservation.rescinded", function(task) {
            window.reservation = {};
        });
        
        SP.worker.on("reservation.timeout", function(task) {
            
            window.reservation = {};

          	Twilio.Device.disconnectAll();
          	Twilio.Device.instance.soundcache.stop('incoming');

            //SP.functions.updateAgentStatusText("ready", "Ready");
            SP.functions.setIdleState();

            /*setTimeout(function(){
            	SP.worker.update("ActivitySid", SP.actvities.ready, function (error, worker) {});
            }, 300);*/
            
        });
        
        /*SP.worker.on("reservation.accepted", function(task) {
            window.reservation = task;
            SP.active_call = true;
        });
        
        SP.worker.on("reservation.rejected", function(task) {
            window.reservation = {};
            SP.active_call = false;
        });*/

        SP.worker.on('reservation.created', function (task) {
            window.reservation = task;
            SP.active_call = false;
            var callerId = task.task.attributes.clientId;
            if(callerId && $('.site-iframe').attr('src') != baseUrl + 'iframe/clients/details/' + callerId)
                $('.site-iframe').attr('src', baseUrl + 'iframe/clients/details/' + callerId);
            else {
                if(!$('.site-iframe').attr('src'))
                    $('.site-iframe').attr('src', baseUrl + 'iframe' + location.pathname)
            }

        });
    }

	SP.functions.startWebSocket = function(){
        if(baseUrl.indexOf('.dev/')>0)
            return false;
		var path = '/';
	    var segments = window.location.pathname.split('/');

	    for (i = 1; i < segments.length - 1; i++)
	        path += segments[i] + '/';

	    var wsaddress = baseUrl.replace(/^https:\/\//i, '').replace(/^http:\/\//i, '').replace(/\/+$/, '') + ':8895/?worker_sid=' + worker_sid + '&workspace_sid=' + workspace_sid;
	    
	    ws = io.connect(wsaddress, {secure: true});
	    window.ws = ws;

	    ws.on('connect', function (socket) {
	    	ws.emit('room', workspace_sid);
	    	ws.send({method:'getWorkerToken'});
	    });

		ws.on('message', function(data){
		
	        var method = data.payload.method + 'Callback';

	        if(typeof data.result == 'object' || typeof data.result == 'boolean')
	            eval('if(typeof(ws.' + method + ') == "function") ws.' + method + '(data.result)');
	        else
	            eval('if(typeof(ws.' + method + ') == "function") ws.' + method + "('" + data.result + "')");
		});

		ws.updateHistoryCallback = function(result) {
            ws.send({method:'getCallsHistory'});
        }

        ws.getCallsHistoryCallback = function(result) {
        	SP.functions.render_call_history('#all-calls-history', result.calls);
        	SP.functions.render_call_history('#my-calls-history', result.my_calls);
            SP.functions.render_call_history('#voices-history', result.voices);
        	soundManager.reboot();
        }

        ws.searchInHistoryCallback = function(result) {
            SP.functions.render_call_history('#search-history', result.search);
            soundManager.reboot();
        }

		ws.getWorkerTokenCallback = function(data) {

	        SP.worker = new Twilio.TaskRouter.Worker(data.token, true);//, data.onlineActivitySid, data.offlineActivitySid, true);
	        var activitySids = {};
	        SP.worker.activities.fetch(function(error, activityList) {
                if(error)
                    return;

                var data = activityList.data;
                for(i=0; i< data.length; i++) {
                    if (data[i].available)
                        SP.actvities.ready = data[i].sid
                    else
                        SP.actvities.notReady = data[i].sid
                }
                
                SP.functions.registerTaskRouterCallbacks();
            });
	    };

	    ws.getTokenCallback = function(token) {
            if(!token)
               return false;
            
            Twilio.Device.setup(token, {
                debug: false, 
                audioConstraints: {
                    optional: [ 
                        {googAutoGainControl: false},
                        {googHighpassFilter: false}
                    ]
                }
            });
        };

        ws.getAgentsCounterCallback = function(data) {

        	view = {
        		template_id:'#agents-num-tmp', view_container_id:'#agents-num',
				data:[{agents_count:data.count}]
			};
			SP.functions.renderView(view);
            data.linkClass = 'callToAgent';
			if(data.workers.length)
				SP.functions.render_workers(data);
        };

        ws.getQueueCounterCallback = function(data) {
            
            var template = $.templates('');
            var htmlOutput = template.render([{message:'Queue is empty'}]);

            var response = [];
            if(data.tasks){    
                $.each(data.tasks, function(i, v){ response.push(v); });
            }
            
            view = {
				template_id:'#in-queues-list-tmp',
				view_container_id:'#in-queues-list',
				data:response,
				empty_template_id:'#in-queues-list-empty-tmp',
				empty_message:'Queue is empty'
			};
			SP.functions.renderView(view);            

			view2 = {
				template_id:'#queues-count-tmp',
				view_container_id:'#queues-count-result',
				data:[{"queuesize":data.queuesize}],
			};
			SP.functions.renderView(view2);
        };

        ws.updateQueueCounterCallback = function(data){
        	
            ws.send({method:'getQueueCounter'});
        }

        ws.updateAgentsCountersCallback = function(data) {
            ws.send({method:'getAgentsCounter'});
        };

        ws.getAvailableAgentsCallback = function(data) {
            if($('.agents-status').is('.open'))
                linkClass = 'callToAgent';
            else
                linkClass = 'forward-in-support';
            SP.functions.render_workers({'workers':data, linkClass:linkClass});
        };

        ws.getQueueTokenCallback = function(token) {
            //SP.queue = Twilio.TaskRouter.TaskQueue(token);
        };

        ws.getForwardContactsCallback = function(data){
        	view = {
				template_id:'#contacts-tmp',
				view_container_id:'#users-contacts',
				data:data,
				empty_template_id:'#contacts-empty-tmp',
				empty_message:'No contacts'
			};
			SP.functions.renderView(view);
        }

	}

	
	// Set server-side status to ready / not-ready
    SP.functions.notReady = function () {
        /*if(reservation.reject!=undefined)
           reservation.reject();*/

        Asid = SP.actvities.notReady;
        /*if(reservation.reservationStatus!='pending')
            SP.worker.update("ActivitySid", Asid, function (error, worker) {});*/

    }

    SP.functions.ready = function () {
        Asid = SP.actvities.ready; 
        //SP.worker.update("ActivitySid", Asid, function (error, worker) {});
    }

    SP.functions.updateStatus = function (error, worker) {
        if (worker && worker.hasOwnProperty('activityName') && worker.activityName == "Idle") {

            //SP.functions.setIdleState();
            SP.functions.updateAgentStatusText("ready", "Ready");

            //if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
                //SP.functions.connection(Twilio.Device.activeConnection());

        } else {
            SP.functions.updateAgentStatusText("notReady", "Not Ready");
        }
    }

    SP.functions.updateAgentStatusText = function (statusCategory, statusText, inboundCall) {

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

        if (inboundCall == true) {
            $('#softphone').addClass('incoming');
            $("#number-entry > input").val(statusText);
        }
    }

    SP.functions.setIdleState = function () {
        if(Twilio.Device.instance!=undefined && Twilio.Device.instance && Twilio.Device.instance.hasOwnProperty('soundcache'))
            Twilio.Device.instance.soundcache.stop('incoming');
        SP.functions.updateAgentStatusText('ready');

        if(Twilio.Device.activeConnection()!=undefined)
        	Twilio.Device.activeConnection().reject();

        SP.functions.action_buttons_reset();
        SP.action_buttons[0].call = '';
        SP.functions.render_actions_btn();
        
        SP.functions.ready();

        $('div.agent-status').hide();
        $("#number-entry > input").val("");
        $('div.numpad-container').show();
        $('div#messages').hide();

        var queryParams = {"ReservationStatus":"pending"};

        SP.worker.fetchReservations(
            function(error, reservations) {
                if(reservations.length)
                    return false;
                //SP.worker.update("ActivitySid", SP.actvities.ready, function (error, worker) {});
            },
            queryParams
        );
    }

    SP.functions.setRingState = function () {
    	
    	SP.functions.action_buttons_reset();
        SP.action_buttons[0].answer = '';
        SP.functions.render_actions_btn();

        $('div.numpad-container').show();
        $('div#messages').hide();
    }

    SP.functions.setOnCallState = function () {

    	SP.functions.action_buttons_reset();
    	//SP.action_buttons[0].mute = '';
        //can not hold outbound calls, so disable this
        if (Twilio.Device.activeConnection()._direction == "INCOMING") {
        	SP.action_buttons[0].hold = '';
        	SP.action_buttons[0].forward = '';
        }
        SP.action_buttons[0].hangup = '';
		SP.functions.render_actions_btn();

        $('div.agent-status').show();
    }

    SP.functions.hideCallData = function () {
        $("#call-data").hide();
    }

    SP.functions.hideCallData();


	if (window.self === window.top) {
	    //var defaultclient = {}
	    //defaultclient.result = SP.username;
	    SP.functions.startWebSocket();
	    SP.functions.action_buttons_reset();
	}

	SP.functions.connection = function(conn){
        SP.functions.setOnCallState();
        //SP.functions.detachAnswerButton();
        SP.currentCall = conn;
    }
    SP.functions.handleKeyEntry = function (key) {
        if (Twilio.Device.activeConnection() != null && Twilio.Device.activeConnection() != undefined) {
            Twilio.Device.activeConnection().sendDigits(key);
        }
    }



	/*------------------------Twilio Device Events-----------------*/
	Twilio.Device.ready(function (device) {
        $('.createCall.disabled').removeClass('disabled');
    });

	Twilio.Device.offline(function (device) {
        SP.functions.notReady();
        SP.functions.hideCallData();
        //ws.send({worker_sid:worker_sid, method:'getToken'});
        //ws.send(JSON.stringify({worker_sid:worker_sid, method:'getQueueToken'}));
        ws.send({method:'getWorkerToken'});
    });

	Twilio.Device.error(function (error) {
        SP.functions.updateAgentStatusText("ready", error.message);
        SP.functions.hideCallData();
    });


	Twilio.Device.disconnect(function (conn) {
        Twilio.Device.instance.soundcache.stop('incoming');
        SP.functions.updateAgentStatusText("ready", "Call ended");

        SP.state.callNumber = null;

        if(Twilio.Device.activeConnection()==undefined)
            SP.active_call=false;
        
        SP.functions.setIdleState();

    });

	Twilio.Device.connect(function (conn){
        if(Twilio.Device.activeConnection()!==undefined && Twilio.Device.activeConnection()._direction=="OUTGOING"){
            SP.functions.connection(Twilio.Device.activeConnection());
            SP.action_buttons[0].mute = 'hidden';
            SP.functions.render_actions_btn();
        }
        else{
            var call_sid = SP.call_sid;
            var task_sid = '';
            if(reservation.task != undefined) {
                call_sid = reservation.task.attributes.call_sid;
                task_sid = reservation.task.sid;
            }
            if(Twilio.Device.activeConnection()!=undefined && !isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From)))
                ws.send({method:'setCallSession', params:{'worker_sid':worker_sid, 'call_sid':call_sid,'task_sid':task_sid}});
        }
    });

	Twilio.Device.incoming(function (conn) {
        // Update agent status
        SP.currentCall = conn;
        SP.functions.updateAgentStatusText("ready", ( conn.parameters.From), true);
        // Enable answer button and attach to incoming call

        //SP.functions.attachAnswerButton(conn);
        SP.functions.setRingState();
        if (SP.requestedHold == true) {
            //auto answer
            SP.requestedHold = false;
            $("#action-buttons > button.answer").click();
        }

        var inboundnum = cleanInboundTwilioNumber(conn.parameters.From);
        var sid = conn.parameters.CallSid
        SP.call_sid = conn.parameters.CallSid;
        var result = "";

        if(!$('.site-iframe').attr('src'))
            $('.site-iframe').attr('src', baseUrl + 'iframe' + location.pathname)
        $('.phone-overlay').show();

        if(Twilio.Device.activeConnection()!=undefined && isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From))){
            SP.action_buttons[0].mute = 'hidden';
            SP.functions.render_actions_btn();
        }
        
    });

	Twilio.Device.cancel(function (conn) {
        conn.reject();
       
        Twilio.Device.instance.soundcache.stop('incoming');
        SP.functions.setIdleState();
    });

	/*------------------------Twilio Device Events-----------------*/

	
	/*------------------------jQuery Events------------------------*/
	

	$(document).on('click', "#action-buttons > button.answer", function () {
        SP.active_call = true;
        var conn = Twilio.Device.activeConnection();
        conn.accept();
        SP.functions.setOnCallState();

        /*if(Twilio.Device.activeConnection()!=undefined && isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From))){
            SP.action_buttons[0].mute = 'hidden';
            SP.functions.render_actions_btn();
        }*/
    });

    $(document).on('click', "#action-buttons > button.call", function () {
        SP.active_call = true;
        params = {"PhoneNumber": $("#number-entry > input").val()};
        Twilio.Device.connect(params);
    });

    $('.agents-status').on('shown.bs.dropdown', function () {
        ws.send({method:'getAvailableAgents'});
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
        ws.send({method:'searchInHistory', params:{query:query}});
        return false;
    });

    $(document).on('click', '.callToAgent', function(){
        SP.active_call = true;
        params = {"agent": $(this).data('contact_uri')};
        Twilio.Device.connect(params);
        
        if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
            SP.functions.connection(Twilio.Device.activeConnection());

        if(Twilio.Device.activeConnection()._direction=="OUTGOING"){
        	SP.action_buttons[0].mute = 'hidden';
            SP.functions.render_actions_btn();
        }

    });

    $(document).on('click', 'a.outgoing-call', function () {
        SP.active_call = true;
        $('#number-entry input').val($(this).data('phone'));
        params = {"PhoneNumber": $.trim($(this).data('phone'))};
        Twilio.Device.connect(params);
    });

    // Hang up button will hang up any active calls
    $(document).on('click', "#action-buttons > button.hangup", function () {
        ws.send({method:'unsetCallSession'});
        SP.active_call = false;
        Twilio.Device.disconnectAll();
        reservation = {};
    });

    // Wire the ready / not ready buttons up to the server-side status change functions
    $("#agent-status-controls > button.ready").click(function () {
        $("#agent-status-controls > button.ready").prop("disabled", true);
        SP.functions.ready();
    });

    $("#agent-status-controls > button.not-ready").click(function () {
        $("#agent-status-controls > button.not-ready").prop("disabled", true);
        SP.functions.notReady();
    });

    $(document).on('click', '.client-iframe', function(){
        $('.site-iframe').attr('src', $(this).attr('href'));
        return false;
    });

    $(document).on('click', '#forward-call-in-worker', function(){
        ws.send({method:'getAvailableAgents', params:{worker_sid:worker_sid}});
        return false;
    });

    $(document).on('click', '#forward-call-in-phone', function(){
        ws.send({method:'getForwardContacts', params:{worker_sid:worker_sid}});
        return false;
    });

    $(document).on('click', '.forward-in-support', function(){
        var call_sid = SP.call_sid;
        if(reservation.task != undefined)
            call_sid = reservation.task.attributes.call_sid;
        
        var params = {
			contact_uri:$(this).data('contact_uri'),
            call_sid:call_sid,
            worker_sid:worker_sid
        };

        SP.active_call = false;

        ws.send({method:'forwardCallToAgent', params:params});
        return false;
    });

    $(document).on('click', '.forward-in-number', function(){

        var call_sid = SP.call_sid;
        if(reservation.task != undefined)
            call_sid = reservation.task.attributes.call_sid;

        var params = {
            number:$(this).data('number'),
            call_sid:call_sid,
            worker_sid:worker_sid
        };

        SP.active_call = false;

        ws.send({method:'forwardCallToNumber', params:params});
        return false;
    });

    $("div.number").bind('click', function () {
        $("#number-entry > input").val($("#number-entry > input").val() + $(this).attr('Value'));
        //pass key without conn to a function
        SP.functions.handleKeyEntry($(this).attr('Value'));
    });

    window.onbeforeunload = function(e) {
        if(Twilio.Device.activeConnection() != undefined && 
           Twilio.Device.activeConnection()._direction == 'INCOMING' && 
           Twilio.Device.activeConnection()._status == 'open' && 
           !isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From)))
            return SP.connection_loss();
        else {
            if(Twilio.Device.activeConnection())
                return true;
        }
        return null;
    };
    
    SP.connection_loss = function(){
     
        Twilio.Device.activeConnection()._onHangup(Twilio.Device.activeConnection());
        ws.send({method:'restoreCall'});
    }
    /*------------------------jQuery Events------------------------*/

	/******** GENERAL FUNCTIONS for SFDC  ***********************/

    function cleanInboundTwilioNumber(number) {
        //twilio inabound calls are passed with +1 (number). SFDC only stores
        return number.replace('+1', '');
    }

    function cleanFormatting(number) {
        //changes a SFDC formatted US number, which would be 415-555-1212
        return number.replace(' ', '').replace('-', '').replace('(', '').replace(')', '').replace('+', '');
    }


    function startCall(response) {

        //called onClick2dial
        //sforce.interaction.setVisible(true);  //pop up CTI console
        var result = JSON.parse(response.result);
        var cleanednumber = cleanFormatting(result.number);

        params = {"PhoneNumber": cleanednumber, "CallerId": $("#callerid-entry > input").val()};
        connection = Twilio.Device.connect(params);

    }

    var saveLogcallback = function (response) {
        if (response.result) {
            //console.log("saveLog result =" + response.result);
        } else {
            //console.log("saveLog error = " + response.error);
        }
    };


    function saveLog(response) {

        //console.log("saving log result, response:");
        var result = JSON.parse(response.result);

        //console.log(response.result);

        var timeStamp = new Date().toString();
        timeStamp = timeStamp.substring(0, timeStamp.lastIndexOf(':') + 3);
        var currentDate = new Date();
        var currentDay = currentDate.getDate();
        var currentMonth = currentDate.getMonth() + 1;
        var currentYear = currentDate.getFullYear();
        var dueDate = currentYear + '-' + currentMonth + '-' + currentDay;
        var saveParams = 'Subject=' + SP.calltype + ' Call on ' + timeStamp;

        saveParams += '&Status=completed';
        saveParams += '&CallType=' + SP.calltype;  //should change this to reflect actual inbound or outbound
        saveParams += '&Activitydate=' + dueDate;
        saveParams += '&Phone=' + SP.state.callNumber;  //we need to get this from.. somewhere
        saveParams += '&Description=' + "test description";

        //console.log("About to parse  result..");

        var result = JSON.parse(response.result);
        var objectidsubstr = result.objectId.substr(0, 3);
        // object id 00Q means a lead.. adding this to support logging on leads as well as contacts.
        if (objectidsubstr == '003' || objectidsubstr == '00Q') {
            saveParams += '&whoId=' + result.objectId;
        } else {
            saveParams += '&whatId=' + result.objectId;
        }

        //console.log("save params = " + saveParams);
        //sforce.interaction.saveLog('Task', saveParams, saveLogcallback);
    }
});
