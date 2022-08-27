var worker_sid = getData('worker_sid');
var workspace_sid = getData('workspace_sid');
var baseUrl = getData('base_url');
var port = getData('port');
var wsaddress = null;
var ws = {};


'use strict';

// These are all exposed to the Options and Popup pages from the BGP object

Date.prototype.timeNow = function () {
     return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
}

var Color = {
	Red: [255, 0, 0, 192],
	Orange: [255, 116, 0, 192], // [255, 133, 0, 192]
	Green: [0, 192, 0, 192],
	Grey: [56, 56, 56, 56],
};

var Connection = null;
var deviceRe = null;

function initSocket() {
	worker_sid = getData('worker_sid');
	workspace_sid = getData('workspace_sid');
	baseUrl = getData('base_url');
	port = getData('port');
	wsaddress = 'wss://' + baseUrl.replace(/^https:\/\//i, '').replace(/^http:\/\//i, '').replace(/\/+$/, '') + ':' + port + '/?worker_sid=' + worker_sid + '&workspace_sid=' + workspace_sid;
	ws = {};

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
}

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
	        ws.send({method:'getInHoldList'});
	        ws.send({method:'getOutgoingIds'});
	        ctp.state = 'idle';

	        wsc.worker.activity = 'Idle';
	        wsc.worker.contact_uri = worker.attributes.contact_uri;
	        console.log(new Date().timeNow() + ' Worker is ready!');
        });

        wsc.worker.on('activity.update', function (worker) {
        	if(worker.activityName == 'Offline') {
        		ctp.stat('offline', Color.Red, 'Offline');
        		/*try {
					worker.activities.fetch(function(error, activityList) {
						if(error) {
							console.log(error.code);
							console.log(error.message);
							return;
						}
					});
				} catch (err) {
					localStorage.removeItem('firstname');
					localStorage.removeItem('lastname');
					localStorage.removeItem('worker_sid');
					localStorage.removeItem('workspace_sid');

					setTimeout(function(){
						location.reload();
						//location.reload();
					}, 500);
				}*/
        		
			}
        	if(worker.activityName == 'Idle')
        		ctp.stat('idle', Color.Green, 'Ready');
        		
			console.log(new Date().timeNow() + ' Activity changed to ' + worker.activityName);
        	wsc.worker.activity = worker.activityName;
        });
        wsc.worker.on("error", function(error) {
            console.log(new Date().timeNow() + " Websocket had an error: "+ error.response + " with message: "+error.message);
        });
        wsc.worker.on("reservation.canceled", function(task) {
			console.log(new Date().timeNow() + ' [reservation.canceled]');
        	chrome.notifications.clear('main');
            window.reservation = {};
            //Twilio.Device.disconnectAll();
          	//Twilio.Device.instance.soundcache.stop('incoming');
          	//Twilio.Device.audio.incoming(false);
          	ctp.init();
          	ctp.state = 'idle';
        });

        wsc.worker.on("reservation.rescinded", function(task) {
            window.reservation = {};
            console.log(new Date().timeNow() + ' [reservation.rescinded]');
        });
        
        wsc.worker.on("reservation.timeout", function(task) {
            chrome.notifications.clear('main');
            Twilio.Device.activeConnection().reject();
            window.reservation = {};

          	//Twilio.Device.disconnectAll();
          	//Twilio.Device.audio.incoming(false);
          	//Twilio.Device.instance.soundcache.stop('incoming');
          	//ctp.init();
          	ctp.state = 'idle';
          	console.log(new Date().timeNow() + ' [reservation.timeout]');

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
            console.log(new Date().timeNow() + ' [reservation.created]');
            

        });
	},

	take: function()
	{
		console.log(new Date().timeNow() + ' Connection.accept()');
		this.state = 'oncall';
		Connection.accept();
	},

	call: function (d)
	{
		console.log(new Date().timeNow() + ' Twilio.Device.connect');
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
				debug: true, 
				audioConstraints: {
					optional: [ 
						{googAutoGainControl: false},
						{googHighpassFilter: false}
					]
				},
				sounds: {
					incoming: '/sounds/iphone_x.mp3',
					outgoing: '/sounds/outgoing.mp3',
					disconnect: '/sounds/disconnect.mp3',
					dtmf0: '/sounds/dtmf-0.mp3',
					dtmf1: '/sounds/dtmf-1.mp3',
					dtmf2: '/sounds/dtmf-2.mp3',
					dtmf3: '/sounds/dtmf-3.mp3',
					dtmf4: '/sounds/dtmf-4.mp3',
					dtmf5: '/sounds/dtmf-5.mp3',
					dtmf6: '/sounds/dtmf-6.mp3',
					dtmf7: '/sounds/dtmf-7.mp3',
					dtmf8: '/sounds/dtmf-8.mp3',
					dtmf9: '/sounds/dtmf-9.mp3',
				}
			});
		}

		var s = Twilio.Device.status();
		console.log(new Date().timeNow() + ' ctp.init status = ' + s);

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
	kill: function (x)
	{
		console.log(new Date().timeNow() + ' Connection.disconnect()');
		if(x != undefined) {
			if (x) {
				x.disconnect();
			}
		}
		else {
			if (Connection) {
				Connection.disconnect();
				Connection = null;
			}
			Twilio.Device.disconnectAll();
		}
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
			'From':'',
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


	if(getData('base_url') && getData('port')) {
		initSocket();
	}
	else {
		setData('base_url', 'https://treedoctors.arbostar.com/');
		setData('port', '8895');
		initSocket();
	}
	// Ready Handle

	Twilio.Device.offline(function (device) {
		console.log(new Date().timeNow() + ' Twilio.Device.offline');
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
		console.log(new Date().timeNow() + ' Twilio.Device.ready(' + d + ')');
		if(wsc.worker.activity == 'Offline')
    		ctp.stat('offline', Color.Red, 'Offline');
    	if(wsc.worker.activity == 'Idle')
    		ctp.stat('idle', Color.Green, 'Ready');
	});

	// Incoming
	Twilio.Device.incoming(function (x) {
		if(wsc.worker.activity != 'Reserved')
			wsc.worker.update("ActivitySid", wsc.activities.busy, function (error, worker) {});
		chrome.notifications.create('main', {
			type: "basic",
			title: getData('company_name') + " Incoming Call",
			message: "From: " + x.parameters.From + "\n\n" + 'Click the "Answer" button to start conversation',
			iconUrl: getData('logo') ? getData('logo') : "img/icon38.png",
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
		console.log(new Date().timeNow() + ' Twilio.Device.incoming() - SID:' + x.parameters.CallSid);
		Connection = x;
		Connection.want = 0;
		ctp.stat('ring', Color.Red, 'From: ' + Connection.parameters.From);

	});

	// Connected
	Twilio.Device.connect(function (c) {
		if(wsc.worker.activity != 'Reserved')
			wsc.worker.update("ActivitySid", wsc.activities.busy, function (error, worker) {});
			
		chrome.notifications.clear('main');
		ctp.state = 'oncall';
		console.log(new Date().timeNow() + ' Twilio.Device.connect()');

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
		wsc.worker.update("ActivitySid", wsc.activities.ready, function (error, worker) {});
		chrome.notifications.clear('main');
		console.log(new Date().timeNow() + ' Twilio.Device.disconnect()');
		ctp.state = 'idle';
		ctp.stat('done', Color.Grey);
		//Twilio.Device.audio.incoming(false);
		//Twilio.Device.instance.soundcache.stop('incoming');
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
		wsc.worker.update("ActivitySid", wsc.activities.ready, function (error, worker) {});
		chrome.notifications.clear('main');
		console.log(new Date().timeNow() + ' Twilio.Device.cancel()');
		ctp.kill(x);
		ctp.stat('drop', Color.Red);
		//Twilio.Device.audio.incoming(false);
        //Twilio.Device.instance.soundcache.stop('incoming');

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

		console.log(new Date().timeNow() + ' Twilio.Device.error(' + e.message + ')');

		Connection = null;

		//ctp.kill();
		ctp.stat('fail', Color.Red,e.message);

		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			view.SP.updateAgentStatusText("ready", e.message);
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
