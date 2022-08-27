// Page loaded
$(function () {

    // ** Application container ** //
    window.SP = {};
    window.refreshIntervalId = 0;
    window.connection = 0;
    window.reservation = {};
        // Global state
    SP.call_sid = null;
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
    SP.actvities = {
        ready: null,
        notReady: null
    };

    // For video
    SP.endpoint = null;
    SP.videoAccessToken

    // check for WebRTC
    if (!navigator.webkitGetUserMedia && !navigator.mozGetUserMedia) {
        //alert('WebRTC is not available in your browser.');
    }

    SP.functions = {};

    // Get a Twilio Client name and register with Twilio
    SP.functions.getTwilioClientName = function (sfdcResponse) {
        //sforce.interaction.runApex('UserInfo', 'getUserName', '' , SP.functions.registerTwilioClient);
    }

    SP.functions.registerTwilioClient = function (response) {
        console.log("Registering with client name: " + response.result);
        // Twilio does not accept special characters in Client names
        var useresult = response.result;
        useresult = useresult.replace("@", "AT");
        useresult = useresult.replace(".", "DOT");
        SP.username = useresult;
        console.log("useresult = " + useresult);
    }

    // TaskRouter callbacks
    //  - ready - tells us when we are connected to TaskRouter
    //  - activity.update - tells us of worker state changes
    //  - reservation.created - tells us when our agent is assigned work (SMS in this case)
    SP.functions.registerTaskRouterCallbacks = function () {
        //console.dir("Register callbacks");
        SP.worker.on('ready', function (worker) {
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getAgentsCounter', params:{workspaceSid:SP.worker.workspaceSid}}));
            SP.active_call = false;
        });
        SP.worker.on('activity.update', function (worker) {
            SP.functions.updateStatus("", worker);
            //console.log("Worker activity changed to: " + worker.activityName);
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

            setTimeout(function(){
                SP.functions.ready();
                SP.active_call = false;
            }, 1000);
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
            
            //SP.functions.ready();
            window.reservation = task;
            SP.active_call = false;
            setTimeout(function(){
                var callerId = $('.queues-status ul li:contains(' + $('#number-entry input').val() + ') a').data('clientid');
                if(callerId && $('.site-iframe').attr('src') != baseUrl + 'iframe/clients/details/' + callerId)
                    $('.site-iframe').attr('src', baseUrl + 'iframe/clients/details/' + callerId);
            }, 300);

        });
    }


    SP.functions.startWebSocket = function (worker) {
        // ** Agent Presence Stuff ** //
        //console.log(".startWebSocket...");
        //console.log("worker is " + worker);
        var path = '/';
        var segments = window.location.pathname.split('/');

        for (i = 1; i < segments.length - 1; i++)
            path += segments[i] + '/';

        var wsaddress = 'wss://' + baseUrl.replace(/^https:\/\//i, '').replace(/^http:\/\//i, '').replace(/\/+$/, '') + ':8889?worker_sid=' + worker_sid;

        var ws = new ReconnectingWebSocket(wsaddress);
        window.ws = ws;

        ws.onopen = function () {
            //console.log('websocket opened');
            if(refreshIntervalId)
                clearInterval(refreshIntervalId);
            //ws.send(JSON.stringify({worker_sid:worker_sid, method:'setConnection'}));
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getToken'}));
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getCallsHistory'}));
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getWorkerToken'}));
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getQueueToken'}));
            //refreshIntervalId = setInterval(function(){
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getQueueCounter'}));
            ws.send(JSON.stringify({worker_sid:worker_sid, method:'getAgentsCounter', params:{workspaceSid:SP.worker.workspaceSid}}));
            //}, 1000);
        };



        ws.onclose = function () { /*send sid*/
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

        ws.newCallInHistoryCallback = function(result) {
            $('#call-history #tab1 ul').prepend(result.calls);
            $('#call-history #tab2 ul').prepend(result.my_calls);
        };

        ws.getTokenCallback = function(token) {
            Twilio.Device.setup(token, {debug: true});
        };

        ws.getQueueTokenCallback = function(token) {
            //SP.queue = Twilio.TaskRouter.TaskQueue(token);
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
                    setTimeout(function(){
                        SP.functions.ready();
                    }, 3000);
                }
            );
        };

        ws.setConnectionCallback = function(data) {
            if (data) {
                //$("#team-status .agents-num").text(data.stats.readyagents);
                //$("#team-status .queues-num").text(data.queuesize);
                /*if(data.tasks.length)
                {
                    $("#team-status .dropdown-menu").html('');
                    $.each(data.tasks, function(key, val){
                        clientLink = '';
                        if(val.client)
                            clientLink += ' - <a href="#" class="clientLink p-n text-ul" data-clientId="' + val.client.client_id + '" style="display: inline-block;">' + val.client.client_name + '</a>';
                        $("#team-status .dropdown-menu").append('<li style="white-space: nowrap;">' + (key + 1) + ') ' + val.caller + /*' - ' + val.status + *//*clientLink + '</li>');
                    });
                }
                else
                {
                    $("#team-status .dropdown-menu").html('<li>Queue is empty</li>');
                }*/
            }
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

        ws.getWorkersStatusesCallback = function(data) {
            $.each(data, function(key, worker){
                var obj = $('[data-worker_sid="' + worker.sid + '"]');
                if(worker.available)
                {
                    $(obj).removeClass('callToAgent').removeClass('disabled').addClass('callToAgent');
                    $(obj).find('i').removeClass('text-success').removeClass('text-danger').addClass('text-success');
                }
            });
        };

        ws.getQueueCounterCallback = function(data) {
            if (data.stats) {
                //$("#team-status .agents-num").text(data.stats.readyagents);
                $("#team-status .queues-num").text(data.stats.queuesize);
                if(data.tasks.length)
                {
                    $("#team-status .queues-status .dropdown-menu").html('');
                    $.each(data.tasks, function(key, val){
                        clientLink = '';
                        if(val.client)
                            clientLink += ' - <a href="#" class="clientLink p-n text-ul" data-clientId="' + val.client.client_id + '" style="display: inline-block;">' + val.client.client_name + '</a>';
                        $("#team-status .dropdown-menu").append('<li style="white-space: nowrap;">' + (key + 1) + ') ' + val.caller + /*' - ' + val.status + */clientLink + '</li>');
                    });
                }
                else
                {
                    $("#team-status .queues-status .dropdown-menu").html('<li>Queue is empty</li>');
                }
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
        }

        ws.getContactsCallback = function(data){
            var template = $.templates('#contacts-empty-tmp');
            var htmlOutput = template.render([{message:'No contacts'}]);
            if(data.length){
                var template = $.templates('#contacts-tmp');
                var htmlOutput = template.render(data);
            }
            
            $('#users-contacts').html(htmlOutput);
        }

    };

    // ** UI Widgets ** //

    // Hook up numpad to input field
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
            console.log("sending DTMF" + key);
            SP.currentCall.sendDigits(key);
        }
    }

    //called when agent is not on a call
    SP.functions.setIdleState = function () {
        if(Twilio.Device.instance!=undefined && Twilio.Device.instance && Twilio.Device.instance.hasOwnProperty('soundcache'))
            Twilio.Device.instance.soundcache.stop('incoming');
        $("#action-buttons > .call").show();
        $("#action-buttons > .send").hide();
        $("#action-buttons > .answer").hide();
        $("#action-buttons > .mute").hide();
        $("#action-buttons a.mute").parent().hide();
        $("#action-buttons > .hold").hide();
        $("#action-buttons > .unhold").hide();
        $("#action-buttons > .hangup").hide();
        $("#action-buttons > .wrapup").hide();
        $('div.agent-status').hide();
        $("#number-entry > input").val("");
        $('div.numpad-container').show();
        $('div#messages').hide();
    }

    SP.functions.setRingState = function () {
        $("#action-buttons > .answer").show();
        $("#action-buttons > .call").hide();
        $("#action-buttons > .send").hide();
        $("#action-buttons > .mute").hide();
        $("#action-buttons a.mute").parent().hide();
        $("#action-buttons > .hold").hide();
        $("#action-buttons > .unhold").hide();
        $("#action-buttons > .hangup").hide();
        $("#action-buttons > .wrapup").hide();
        $('div.numpad-container').show();
        $('div#messages').hide();
    }

    SP.functions.setOnCallState = function () {

        $("#action-buttons > .answer").hide();
        $("#action-buttons > .wrapup").hide();
        $("#action-buttons > .call").hide();
        $("#action-buttons > .mute").show();
        $("#action-buttons > .send").hide();

        //can not hold outbound calls, so disable this
        if (SP.calltype == "Inbound") {
            $("#action-buttons > .hold").show();
            $("#action-buttons a.mute").parent().show();
        }

        $("#action-buttons > .hangup").show();
        $('div.agent-status').show();
    }

    SP.functions.setSmsState = function () {
        $("#action-buttons > .answer").hide();
        $("#action-buttons > .call").hide();
        $("#action-buttons > .mute").hide();
        $("#action-buttons a.mute").parent().hide();
        $("#action-buttons > .hold").hide();
        $("#action-buttons > .unhold").hide();
        $("#action-buttons > .hangup").hide();

        $("#action-buttons > .send").show();
        $("#action-buttons > .wrapup").show();
        $('div.numpad-container').hide();
        $('div#messages').show();

    }

    SP.functions.incomingSMS = function (sms) {
        // Update agent status
        //sforce.interaction.setVisible(true);  //pop up CTI console
        SP.functions.detachSendButton();
        SP.functions.attachWrapUpButton();
        SP.functions.setSmsState();

        var inboundnum = cleanInboundTwilioNumber(sms["From"]);
        //sforce.interaction.searchAndScreenPop(inboundnum, 'con10=' + inboundnum + '&con12=' + inboundnum + '&name_firstcon2=' + name,'inbound');
        $("#number-entry > input").val(inboundnum);
        SP.functions.displayMessage("inbound", sms["Body"]);
        SP.functions.attachSendButton();
    }

    SP.functions.displayMessage = function (direction, message) {
        $("div#messages-container").append("<div class=messagecardthread-" + direction + "><span class=message-text>" + message + "</span></div>");
    }

    // Hide caller info
    SP.functions.hideCallData = function () {
        $("#call-data").hide();
    }
    SP.functions.hideCallData();
    SP.functions.setIdleState();

    // Show caller info
    SP.functions.showCallData = function (callData) {
        $("#call-data > ul").hide();
        $(".caller-name").text(callData.callerName);
        $(".caller-number").text(callData.callerNumber);
        $(".caller-queue").text(callData.callerQueue);
        $(".caller-message").text(callData.callerMessage);

        if (callData.callerName) {
            $("#call-data > ul.name").show();
        }

        if (callData.callerNumber) {
            $("#call-data > ul.phone_number").show();
        }

        if (callData.callerQueue) {
            $("#call-data > ul.queue").show();
        }

        if (callData.callerMessage) {
            $("#call-data > ul.message").show();
        }

        $("#call-data").slideDown(400);
    }


    // Attach answer button to an incoming connection object
    SP.functions.attachAnswerButton = function (conn) {
        $("#action-buttons > button.answer").click(function () {
            conn.accept();
            SP.active_call = true;
            if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
                SP.functions.connection(Twilio.Device.activeConnection());

            if(Twilio.Device.activeConnection()!=undefined && isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From)))        
                $("#action-buttons a.mute").parent().hide();

        }).removeClass('inactive').addClass("active");
    }

    SP.functions.detachAnswerButton = function () {
        Twilio.Device.instance.soundcache.stop('incoming');
        $("#action-buttons > button.answer").unbind().removeClass('active').addClass("inactive");
    }

    SP.functions.attachSendButton = function () {
        $("#action-buttons > button.send").unbind().click(function () {
            //$.post("/send_sms", { "To": $("#number-entry > input").val(), "From": $("#callerid-entry > input").val(), "Message": $("#message-entry > input").val() }, function (data) {
                SP.functions.displayMessage("outbound", $("#message-entry > input").val());
                $("#message-entry > input").val('');
            //});
        }).removeClass('inactive').addClass("active");
    }

    SP.functions.detachSendButton = function () {
        $("#action-buttons > button.send").unbind().removeClass('active').addClass("inactive");
    }

    SP.functions.attachWrapUpButton = function () {
        $("#action-buttons > button.wrapup").click(function () {
            // clear out the chat
            $("div#messages-container").empty();

            // clean out the conversation in the DB
            //$.post("/wrapup", { "task": SP.currentTask }, function (data) {
            //});

            // make ready
            SP.functions.ready();
        }).removeClass('inactive').addClass("active");
    }

    SP.functions.detachWrapUpButton = function () {
        $("#action-buttons > button.wrapup").unbind().removeClass('active').addClass("inactive");
    }

    SP.functions.attachMuteButton = function (conn) {
        $("#action-buttons > button.mute").click(function () {
            conn.mute(true);
            SP.functions.attachUnMute(conn);
        }).removeClass('inactive').addClass("active").text("Mute");
    }

    SP.functions.attachUnMute = function (conn) {
        $("#action-buttons > button.mute").click(function () {
            conn.mute(false);
            SP.functions.attachMuteButton(conn);
        }).removeClass('inactive').addClass("active").text("UnMute");
    }

    SP.functions.detachMuteButton = function () {
        $("#action-buttons > button.mute").unbind().removeClass('active').addClass("inactive");
    }

    SP.functions.attachHoldButton = function (conn) {
        $("#action-buttons > button.hold").click(function () {
            //console.dir(conn);
            SP.requestedHold = true;
            //can't hold outbound calls from Twilio client
            //$.post("/request_hold", { "from": SP.username, "callsid": conn.parameters.CallSid, "calltype": SP.calltype }, function (data) {
                //Todo: handle errors
                //Todo: change status in future
            SP.functions.attachUnHold(conn, data);

            //});

        }).removeClass('inactive').addClass("active").text("Hold");
    }

    SP.functions.attachUnHold = function (conn, holdid) {
        $("#action-buttons > button.unhold").click(function () {
            //do ajax request to hold for the conn.id
            //$.post("/request_unhold", { "from": SP.username, "callsid": holdid }, function (data) {
                //Todo: handle errors
                //Todo: change status in future
                SP.functions.attachHoldButton(conn);
            //});

        }).removeClass('inactive').addClass("active").text("UnHold").show();
    }

    SP.functions.detachHoldButtons = function () {
        $("#action-buttons > button.unhold").unbind().removeClass('active').addClass("inactive");
        $("#action-buttons > button.hold").unbind().removeClass('active').addClass("inactive");
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
            //alert("call from " + statusText);
            $('#softphone').addClass('incoming');
            $("#number-entry > input").val(statusText);
        }
    }

    // Call button will make an outbound call (click to dial) to the number entered
    $("#action-buttons > button.call").click(function () {
        SP.active_call = true;
        params = {"PhoneNumber": $("#number-entry > input").val()};
        Twilio.Device.connect(params);
    });

    $('.agents-status').on('shown.bs.dropdown', function () {
        ws.send(JSON.stringify({worker_sid:worker_sid, method:'getWorkersStatuses'}));
        $('.agents-status ul li a').removeClass('callToAgent').removeClass('disabled').addClass('disabled');
        $('.agents-status ul li a i').removeClass('text-success').removeClass('text-danger').addClass('text-danger');
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

    // Hang up button will hang up any active calls
    $("#action-buttons > button.hangup").click(function () {
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

    $("#agent-status-controls > button.userinfo").click(function () {

    });

    $(document).on('click', '.client-iframe', function(){
        $('.site-iframe').attr('src', $(this).attr('href'));
        return false;
    });

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

    /*$(window).unload(function(){ 
        if(confirm('sdfdsfsfds'))
            return false;
    });*/


    window.onbeforeunload = function(e) {
        
        //e = e || window.event;
        //if(e && SP.active_call == true){
        //    e.returnValue = 
        //}
        if(SP.active_call == true)
            return SP.connection_loss();
    };
    
    SP.connection_loss = function(){
        var call_sid = SP.call_sid;
        
        if(reservation.task != undefined)
            call_sid = reservation.task.attributes.call_sid;

        ws.send(JSON.stringify({worker_sid:worker_sid, method:'unsetConnection', params:{cid:call_sid}}));
    }

    // ** Twilio Client Stuff ** //
    // first register outside of sfdc

    if (window.self === window.top) {
        var defaultclient = {}
        defaultclient.result = SP.username;
        SP.functions.startWebSocket('test');
    }

    //this will only be called inside of salesforce

    Twilio.Device.ready(function (device) {

        //sforce.interaction.cti.enableClickToDial();
        //sforce.interaction.cti.onClickToDial(startCall);
       // SP.functions.ready();
    });

    Twilio.Device.offline(function (device) {
        SP.functions.notReady();
        SP.functions.hideCallData();
        ws.send(JSON.stringify({worker_sid:worker_sid, method:'getToken'}));
        ws.send(JSON.stringify({worker_sid:worker_sid, method:'getWorkerToken'}));
        ws.send(JSON.stringify({worker_sid:worker_sid, method:'getQueueToken'}));
    });

    /* Report any errors on the screen */
    Twilio.Device.error(function (error) {
        SP.functions.updateAgentStatusText("ready", error.message);
        SP.functions.hideCallData();
    });

    /* Log a message when a call disconnects. */
    Twilio.Device.disconnect(function (conn) {
        Twilio.Device.instance.soundcache.stop('incoming');
        SP.functions.updateAgentStatusText("ready", "Call ended");

        SP.state.callNumber = null;

        // deactivate answer button
        SP.functions.detachAnswerButton();
        SP.functions.detachMuteButton();
        SP.functions.detachHoldButtons();
        console.log('glyuk-1');
        SP.functions.setIdleState();


        if(Twilio.Device.activeConnection()==undefined)
            SP.active_call=false;

        setTimeout(function(){
            if(Twilio.Device.activeConnection()==undefined || SP.active_call==false)
            {
                SP.currentCall = null;
                // return to waiting state
                SP.functions.hideCallData();
                SP.functions.ready();
            }    
        }, 300);

        if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
            SP.functions.connection(Twilio.Device.activeConnection());
        //sforce.interaction.getPageInfo(saveLog);
    });



    /* Listen for incoming connections */
    Twilio.Device.incoming(function (conn) {
        // Update agent status
        //sforce.interaction.setVisible(true);  //pop up CTI console
        SP.currentCall = conn;
        SP.functions.updateAgentStatusText("ready", ( conn.parameters.From), true);
        // Enable answer button and attach to incoming call
        SP.functions.attachAnswerButton(conn);
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
        $('.phone-overlay').show();

        if(Twilio.Device.activeConnection()!=undefined && isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From)))        
            $("#action-buttons a.mute").parent().hide();
        
        //if(Twilio.Device.activeConnection()._direction=="OUTGOING")
        //    $("#action-buttons a.mute").parent().hide();

        //sfdc screenpop fields are specific to new contact screenpop
        //sforce.interaction.searchAndScreenPop(inboundnum, 'con10=' + inboundnum + '&con12=' + inboundnum + '&name_firstcon2=' + name,'inbound');

    });

    Twilio.Device.cancel(function (conn) {
        conn.accept();

        SP.functions.detachAnswerButton();
        SP.functions.detachMuteButton();
        SP.functions.detachHoldButtons();
        SP.functions.setIdleState();
        SP.functions.hideCallData();
        SP.functions.ready();
       
        Twilio.Device.instance.soundcache.stop('incoming');
        
        console.log('glyuk-2');
        
        if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
            SP.functions.connection(Twilio.Device.activeConnection());

    });


    $("#callerid-entry > input").change(function () {
        //$.post("/setcallerid", { "from": SP.username, "callerid": $("#callerid-entry > input").val() });
    });


    // Set server-side status to ready / not-ready
    SP.functions.notReady = function () {
        
        if(reservation.reject!=undefined)
           reservation.reject();
        Asid = 'WAb59015c8ffd0c47bb1f812a8c06c38d6';
        if(worker_sid == 'WK360ac12f67055ab09fbbec57a079069e' || worker_sid == 'WK4ecc2746fd3180d02cda96ace0335594') 
			Asid = 'WAc48b2d76d465788143043ed444694394';
        SP.worker.update("ActivitySid", Asid, function (error, worker) {
            //SP.functions.updateStatus(error, worker);
        });

    }

    SP.functions.ready = function () {

        if(reservation.reject!=undefined)
           reservation.reject();
        Asid = 'WAfc57c478bd7cea19883d8908c85b0f6b';
		if(worker_sid == 'WK360ac12f67055ab09fbbec57a079069e' || worker_sid == 'WK4ecc2746fd3180d02cda96ace0335594') 
			Asid = 'WA1d8dfb5be8a292886994d6490e6639f7';
        SP.worker.update("ActivitySid", Asid, function (error, worker) {
            //if(!error)
                //SP.functions.updateStatus(error, worker);
        });
    }


    // Check the status on the server and update the agent status dialog accordingly
    SP.functions.updateStatus = function (error, worker) {
        console.log(error);
        if (worker && worker.hasOwnProperty('activityName') && worker.activityName == "Idle") {
            console.log('glyuk-3');

            SP.functions.setIdleState();
            SP.functions.updateAgentStatusText("ready", "Ready");

            if(Twilio.Device.activeConnection()!==undefined && SP.active_call==true)
                SP.functions.connection(Twilio.Device.activeConnection());

        } else {
            SP.functions.updateAgentStatusText("notReady", "Not Ready");
        }
    }


    SP.functions.connection = function(conn){

        SP.functions.setOnCallState();
        SP.functions.detachAnswerButton();

        SP.currentCall = conn;
        SP.functions.attachMuteButton(conn);
        SP.functions.attachHoldButton(conn, SP.calltype);


        $("#action-buttons a.mute").parent().show();
    }

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
            console.log("saveLog result =" + response.result);
        } else {
            console.log("saveLog error = " + response.error);
        }
    };


    function saveLog(response) {

        console.log("saving log result, response:");
        var result = JSON.parse(response.result);

        console.log(response.result);

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

        console.log("About to parse  result..");

        var result = JSON.parse(response.result);
        var objectidsubstr = result.objectId.substr(0, 3);
        // object id 00Q means a lead.. adding this to support logging on leads as well as contacts.
        if (objectidsubstr == '003' || objectidsubstr == '00Q') {
            saveParams += '&whoId=' + result.objectId;
        } else {
            saveParams += '&whatId=' + result.objectId;
        }

        console.log("save params = " + saveParams);
        //sforce.interaction.saveLog('Task', saveParams, saveLogcallback);
    }
});
