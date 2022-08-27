var worker_sid = getData('worker_sid');
var workspace_sid = getData('workspace_sid');
var baseUrl = 'https://td.onlineoffice.io/';
var wsaddress = 'wss://' + baseUrl.replace(/^https:\/\//i, '').replace(/^http:\/\//i, '').replace(/\/+$/, '') + ':8895/?worker_sid=' + worker_sid + '&workspace_sid=' + workspace_sid;
var ws = {};


'use strict';

// These are all exposed to the Options and Popup pages from the BGP object

var Color = {
	Red: [255, 0, 0, 192],
	Orange: [255, 116, 0, 192], // [255, 133, 0, 192]
	Green: [0, 192, 0, 192],
	Grey: [56, 56, 56, 56],
};

var Connection = null;
var deviceRe = null;

function getData(k)
{
	return localStorage.getItem(k);
}

function setData(k, v)
{
	return localStorage.setItem(k, v);
}

var _app = 'Chrome Twilio Phone';
var l = function(x) { /*if (window.console) console.log(x);*/ };

var ctp = {

	state: 'idle',

	status: function()
	{
		return Twilio.Device.status();
	},

	worker: function()
	{
		wsc.worker.on('ready', function (worker) {
            //reservation = {};
           /* SP.active_call = false;
            SP.functions.notReady();*/
            
	        ws.send({method:'getToken', params:{contact_uri:worker.attributes.contact_uri}});
	        ws.send({method:'getCallsHistory'});
	        ws.send({method:'getQueueToken'});
	        ws.send({method:'getAgentsCounter'});
	        ctp.state = 'idle';

	        wsc.worker.activity = 'Idle';

	        /*SP.functions.updateAgentStatusText("ready", "Ready");
	        SP.functions.setIdleState();*/
	        //SP.worker.update("ActivitySid", SP.actvities.ready, function (error, worker) {});
        });

        wsc.worker.on('activity.update', function (worker) {
        	if(worker.activityName == 'Offline')
        		ctp.stat('offline', Color.Red, 'Offline');
        	if(worker.activityName == 'Idle')
        		ctp.stat('idle', Color.Green, 'Ready');

        	wsc.worker.activity = worker.activityName;
        });
        wsc.worker.on("error", function(error) {
            console.log("Websocket had an error: "+ error.response + " with message: "+error.message);
        });
        wsc.worker.on("reservation.canceled", function(task) {
        	chrome.notifications.clear('main');
            window.reservation = {};
            Twilio.Device.disconnectAll();
          	Twilio.Device.instance.soundcache.stop('incoming');
          	ctp.init();
          	ctp.state = 'idle';
        });

        wsc.worker.on("reservation.rescinded", function(task) {
            window.reservation = {};
        });
        
        wsc.worker.on("reservation.timeout", function(task) {
            chrome.notifications.clear('main');
            window.reservation = {};

          	Twilio.Device.disconnectAll();
          	Twilio.Device.instance.soundcache.stop('incoming');
          	//ctp.init();
          	ctp.state = 'idle';

            //SP.functions.updateAgentStatusText("ready", "Ready");
            //SP.functions.setIdleState();

            /*setTimeout(function(){
            	SP.worker.update("ActivitySid", SP.actvities.ready, function (error, worker) {});
            }, 300);*/
            
        });

        wsc.worker.on('reservation.created', function (task) {
            window.reservation = task;
            /*SP.active_call = false;*/
            var callerId = task.task.attributes.clientId;
            /*if(callerId && $('.site-iframe').attr('src') != baseUrl + 'iframe/clients/details/' + callerId)
                $('.site-iframe').attr('src', baseUrl + 'iframe/clients/details/' + callerId);
            else {
                if(!$('.site-iframe').attr('src'))
                    $('.site-iframe').attr('src', baseUrl + 'iframe' + location.pathname)
            }*/

        });
	},

	take: function()
	{
		this.state = 'oncall';
		Connection.accept();
	},

	call: function (d)
	{
		this.state = 'oncall';
		l('ctp.call(' + d + ')');
		Connection = Twilio.Device.connect(d);
	},

	// Loading
	init: function (token)
	{

		if ('good' != localStorage.getItem('mic-access')) {
			ctp.stat('perm', Color.Red, 'Configure A/V Permissions');
			return;
		}

		ctp.stat('init', Color.Grey, 'connecting');
		
		if(token) {
			Twilio.Device.setup(token, {
				debug: false, 
				audioConstraints: {
					optional: [ 
						{googAutoGainControl: false},
						{googHighpassFilter: false}
					]
				}
			});
		}

		var s = Twilio.Device.status();
		console.log('ctp.init status = ' + s);

		switch (s) {
		case 'ready':
			if(wsc.worker.activity == 'Offline')
        		ctp.stat('offline', Color.Red, 'Offline');
        	if(wsc.worker.activity == 'Idle')
        		ctp.stat('idle', Color.Green, 'Ready');
			break;
		default:
			ctp.stat('init', Color.Orange, s);
			break;
		};

	},

	/**

	*/
	kill: function ()
	{
		if (Connection) {
			Connection.disconnect();
			Connection = null;
		}
		Twilio.Device.disconnectAll();
		this.state = 'idle';
	},

	/**
		Change Button Status
	*/
	stat: function (n, c, t) // Note, Colour, Info Text
	{
		l('ctp.stat(' + n + ',' + c + ',' + t + ')');

		if (!t) {
			t = _app;
		}

		chrome.browserAction.setTitle({
			title: t
		});
		chrome.browserAction.setBadgeText({
			text: n
		});
		chrome.browserAction.setBadgeBackgroundColor({
			color: c
		});
	},

	/**

	*/
	logs_list: function(cb) {
		var u = 'https://' + localStorage._user_sid + ':' + localStorage._auth_tid + '@api.twilio.com/2010-04-01/Accounts/' + localStorage._user_sid + '/Notifications.json?Page=0&PageSize=10';
		console.log('ctp.post(' + u + ')');
		$.get(u,cb);
	},

	/**
		@param
	*/
	text: function(n,t,cb) {
		var u = 'https://' + localStorage._user_sid + ':' + localStorage._auth_tid + '@api.twilio.com/2010-04-01/Accounts/' + localStorage._user_sid + '/SMS/Messages.json';
		var p = {
			'From':'+12062826500', // 'client:' + localStorage._plug_did'], // From number the Callee Sees
			'To':n,
			'Body':t
		};
		console.log('ctp.post(' + u + ')');
		$.post(u,p,cb);
	},
	/**
		@param cb callback function
	*/
	text_list: function(cb) {
		var u = 'https://' + localStorage._user_sid + ':' + localStorage._auth_tid + '@api.twilio.com/2010-04-01/Accounts/' + localStorage._user_sid + '/SMS/Messages.json';
		u += '?Page=0&PageSize=10';
		window.console && console.log('ctp.text_list(' + u + ')');
		$.get(u,cb);
	}
};

/**
	Prompt for Options or the Popup if set properly
*/
/*chrome.browserAction.onClicked.addListener(function(tab) {

	//if ('good' != localStorage.getItem('mic-access')) {
	var arg = {
		url: 'options.html'
	};

	chrome.tabs.create(arg);

	//} else {
	//	chrome.browserAction.setPopup({
	//		popup: "popup.html"
	//	});
	//}

});*/


// Init my Thing
document.addEventListener("DOMContentLoaded", function () {

	console.log('BrowserPhone!DOMContentLoaded');

	ws = io.connect(wsaddress, {secure: true});

	ws.on('message', function(data){

		var method = data.payload.method + 'Callback';

		if(typeof data.result == 'object' || typeof data.result == 'boolean')
			eval('if(typeof(wsc.' + method + ') == "function") wsc.' + method + '(data.result)');
		else
			eval('if(typeof(wsc.' + method + ') == "function") wsc.' + method + "('" + data.result + "')");
	});

	if(!workspace_sid || !worker_sid) {
		ctp.stat('fail', Color.Red, 'Enter your credentials');
		return false;
	}

	ws.on('connect', function (socket) {
		ws.emit('room', workspace_sid);
		ws.send({method:'getWorkerToken'});
	});


	// Ready Handle

	Twilio.Device.offline(function (device) {
		ctp.stat('init', Color.Grey, 'connecting');
		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
	        view.SP.notReady();
	        view.SP.hideCallData();
	    }
        //ws.send({worker_sid:worker_sid, method:'getToken'});
        //ws.send(JSON.stringify({worker_sid:worker_sid, method:'getQueueToken'}));
        ws.send({method:'getWorkerToken'});
    });


	Twilio.Device.ready(function(d) {
		console.log('Twilio.Device.ready(' + d + ')');
		if(wsc.worker.activity == 'Offline')
    		ctp.stat('offline', Color.Red, 'Offline');
    	if(wsc.worker.activity == 'Idle')
    		ctp.stat('idle', Color.Green, 'Ready');
	});

	// Incoming
	Twilio.Device.incoming(function (x) {
		chrome.notifications.create('main', {
			type: "basic",
			title: "Incoming Call",
			message: "From: " + x.parameters.From + "\n\n" + 'Click the "Answer" button to start conversation',
			iconUrl: "img/icon38.png",
			buttons: [{title:'Answer', iconUrl:'img/icon_phone_small.png'}],
			priority: 20
		}, function(id){});


		ctp.state = 'ring';
		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			view.SP.currentCall = x;
	        view.SP.setRingState();
	        if (view.SP.requestedHold == true) {
	            SP.requestedHold = false;
	            //$("#action-buttons > button.answer").click();
	        }
	        var inboundnum = x.parameters.From;
	        var sid = x.parameters.CallSid
	        view.SP.call_sid = x.parameters.CallSid;
	        view.SP.action_buttons[0].mute = 'hidden';
	        view.SP.render_actions_btn();
		}
		console.log('Twilio.Device.incoming()');
		Connection = x;
		Connection.want = 0;
		ctp.stat('ring', Color.Red, 'From: ' + Connection.parameters.From);

	});

	// Connected
	Twilio.Device.connect(function (c) {
		chrome.notifications.clear('main');
		ctp.state = 'oncall';
		console.log('Twilio.Device.connect()');

		ctp.stat('talk', Color.Orange, c.parameters.From);

		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			if(Twilio.Device.activeConnection()!==undefined && Twilio.Device.activeConnection()._direction=="OUTGOING"){
	            view.SP.connection(Twilio.Device.activeConnection());
	            view.SP.action_buttons[0].mute = '';
	            view.SP.render_actions_btn();
	        }
	        else{
	            /*var call_sid = view.SP.call_sid;
	            var task_sid = '';
	            if(reservation.task != undefined) {
	                call_sid = reservation.task.attributes.call_sid;
	                task_sid = reservation.task.sid;
	            }
	            if(Twilio.Device.activeConnection()!=undefined && !isNaN(parseInt(Twilio.Device.activeConnection().options.callParameters.From)))
	                ws.send({method:'setCallSession', params:{'worker_sid':worker_sid, 'call_sid':call_sid,'task_sid':task_sid}});*/
	        }
	        view.SP.setOnCallState();
	    }

		// Open the Requested Page
		/*var url = localStorage.getItem('_open_url');
		if ((undefined !== url) && (url.length > 5)) {
			url = url.replace('{PHONE}', c.parameters.From);
			chrome.tabs.create({
				url: url,
			});
		}*/

	});

	// Disconnected
	Twilio.Device.disconnect(function (x) {
		chrome.notifications.clear('main');
		console.log('Twilio.Device.disconnect()');
		ctp.state = 'idle';
		ctp.stat('done', Color.Grey);
		Twilio.Device.instance.soundcache.stop('incoming');
		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
	        view.SP.updateAgentStatusText("ready", "Call ended");
	        ///view.SP.state.callNumber = null;

	        if(Twilio.Device.activeConnection()==undefined)
	            view.SP.active_call=false;
	        
	        view.SP.setIdleState();
	    }

		window.setTimeout(function() {
			ctp.init();
		}, 1);

	});

	// Cancel - incoming connection is canceled by the caller before it is accepted
	Twilio.Device.cancel(function(x) {
		chrome.notifications.clear('main');
		console.log('Twilio.Device.cancel()');
		ctp.kill();
		ctp.stat('drop', Color.Red);
        Twilio.Device.instance.soundcache.stop('incoming');

        var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
        	view.SP.setIdleState();
        }

		window.setTimeout(function() {
			ctp.init();
		}, 1);

	});

//	// Offline Event
//	Twilio.Device.offline(function() {
//		l('Twilio.Device.offline()');
//		ctp.kill();
//		ctp.stat('...',[56, 56, 56, 128]);
//	});

	/**
		Token has expired => Make a new one
	*/
	Twilio.Device.error(function (e) {

		console.log('Twilio.Device.error(' + e.message + ')');

		Connection = null;

		ctp.kill();
		ctp.stat('fail', Color.Red,e.message);

		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			view.SP.updateAgentStatusText("ready", error.message);
	        view.SP.hideCallData();
	    }

		window.setTimeout(function() {
			ctp.init();
		}, 2000);
	});
});

chrome.notifications.onButtonClicked.addListener(function(notificationId, buttonIndex) {
	if(notificationId == 'main') {
		ctp.take();
	}
});

chrome.runtime.onMessage.addListener(function(msg, sender, sendResponse) {
    var method = msg.method;
    var params = msg.params;
    var result = false;

	if(typeof params == 'object' || typeof params == 'boolean' || typeof params == 'undefined')
		eval('if(typeof(' + method + ') == "function") result = ' + method + '(params)');
	else
		eval('if(typeof(' + method + ') == "function") result = ' + method + "('" + params + "')");

	sendResponse(result);
});
