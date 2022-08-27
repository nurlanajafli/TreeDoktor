
var wsc = {
	
	voicemailCounter: 0,
	myVoicemailCounter: 0,
	
	worker: {

	},

	activities: {
		ready: null,
		notReady: null,
		busy: null,
		reserved: null
	},

	updateHistoryCallback: function(result) {
		ws.send({method:'getCallsHistory'});
	},

	getCallsHistoryCallback: function(result) {
		wsd.vc = 0;
		wsd.mvc = 0;
		$.each(result.calls, function(key, val){
			wsd.allHistory[val.call_id] = val;
		});
		$.each(result.my_calls, function(key, val){
			wsd.allHistory[val.call_id] = val;
		});
		$.each(result.voices, function(key, val){
			wsd.allHistory[val.call_id] = val;
			wsd.vc = val.call_new_voicemail ? wsd.vc + 1 : wsd.vc;
		});
		$.each(result.my_voices, function(key, val){
			wsd.allHistory[val.call_id] = val;
			wsd.mvc = val.call_new_voicemail ? wsd.mvc + 1 : wsd.mvc;
		});
		wsd.history = {
			calls: result.calls,
			my_calls: result.my_calls,
			voices: result.voices,
			my_voices: result.my_voices,
		};
		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			view.SP.render_call_history('#all-calls-history', wsd.history.calls);
    		view.SP.render_call_history('#my-calls-history', wsd.history.my_calls);
        	view.SP.render_call_history('#voices-history', wsd.history.voices);
        	view.SP.render_call_history('#my-voices-history', wsd.history.my_voices);
        	view.SP.render_voicemails_counters();
    		view.soundManager.reboot();
		}
	},
	
	recordIsListenCallback: function(result) {
		var views = chrome.extension.getViews({type:'popup'});
		if(wsd.allHistory[result] != undefined && wsd.allHistory[result] && wsd.allHistory[result].call_new_voicemail != undefined)
			wsd.allHistory[result].call_new_voicemail = 0;
		wsd.vc = 0;
		wsd.mvc = 0;
		$.each(wsd.history.calls, function(key, val){
			wsd.history.calls[key].call_new_voicemail = result == val.call_id ? 0 : val.call_new_voicemail;
		});
		$.each(wsd.history.my_calls, function(key, val){
			wsd.history.my_calls[key].call_new_voicemail = result == val.call_id ? 0 : val.call_new_voicemail;
		});
		$.each(wsd.history.voices, function(key, val){
			wsd.history.voices[key].call_new_voicemail = result == val.call_id ? 0 : val.call_new_voicemail;
			wsd.vc = wsd.history.voices[key].call_new_voicemail ? wsd.vc + 1 : wsd.vc;
		});
		$.each(wsd.history.my_voices, function(key, val){
			wsd.history.my_voices[key].call_new_voicemail = result == val.call_id ? 0 : val.call_new_voicemail;
			wsd.mvc = wsd.history.my_voices[key].call_new_voicemail ? wsd.mvc + 1 : wsd.mvc;
		});
		
		/*if(views.length) {
			view = views[0];
			view.SP.render_call_history('#all-calls-history', wsd.history.calls);
    		view.SP.render_call_history('#my-calls-history', wsd.history.my_calls);
        	view.SP.render_call_history('#voices-history', wsd.history.voices);
        	view.SP.render_call_history('#my-voices-history', wsd.history.my_voices);
        	view.SP.render_voicemails_counters();
    		view.soundManager.reboot();
		}*/
		if(views.length) {
			view = views[0];
			view.SP.delete_new_label(result);
		}
	},

	updateInHoldListCallback: function(result) {
		wsd.onhold = result.rows;
		wsd.serverTz = result.serverTz;
		var timeDiff = (wsd.serverTz - new Date().getTimezoneOffset() * 60);
		/*console.log(timeDiff);
		if(timeDiff) {
			$.each(wsd.onhold, function(key, val){
				val.ch_onhold_time = val.ch_onhold_time - timeDiff;
			});
		}*/

		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			view.SP.render_onhold('#on-hold-list', wsd.onhold);
		}
	},

	searchInHistoryCallback: function(result) {
		wsd.history.search = result.search;
		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			view.SP.render_call_history('#search-history', wsd.history.search);
    		view.soundManager.reboot();
		}
	},

	getWorkerTokenCallback: function(data) {

		this.worker = new Twilio.TaskRouter.Worker(data.token, true);//, data.onlineActivitySid, data.offlineActivitySid, true);
		var activitySids = {};
		ctp.worker();
		wsc.worker.activities.fetch(function(error, activityList) {
			if(error)
				return;
			
			var data = activityList.data;
			
			$.each(data, function(k, v){
				if(v.friendlyName == 'Idle')
					wsc.activities.ready = v.sid;
				if(v.friendlyName == 'Offline')
					wsc.activities.notReady = v.sid;
				if(v.friendlyName == 'Busy')
					wsc.activities.busy = v.sid;
				if(v.friendlyName == 'Reserved')
					wsc.activities.reserved = v.sid;
			});
			
			var views = chrome.extension.getViews({type:'popup'});
			if(views.length) {
				view = views[0];
				view.SP.registerTaskRouterCallbacks();
			}
		});
	},

	getTokenCallback: function(token) {
		if(!token)
		   return false;
		
		ctp.init(token);
	},

	getAgentsCounterCallback: function(data) {
		wsd.agentsCounter = data.count;
		wsd.agentsData = data;

		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
        	tpl = {
				template_id:'#agents-num-tmp', view_container_id:'#agents-num',
				data:[{agents_count:data.count}]
			};
			view.SP.renderView(tpl);
			data.linkClass = 'callToAgent';
			if(data.workers.length)
				view.SP.render_workers(data);	
        }
	},

	getQueueCounterCallback: function(data) {
		wsd.queuesize = data.queuesize;
		wsd.tasks = data.tasks;


		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			var template = view.$.templates('');
			var htmlOutput = template.render([{message:'Queue is empty'}]);

			var response = [];
			if(data.tasks) {
				$.each(data.tasks, function(i, v){ response.push(v); });
			}
			
			tpl = {
				template_id:'#in-queues-list-tmp',
				view_container_id:'#in-queues-list',
				data:response,
				empty_template_id:'#in-queues-list-empty-tmp',
				empty_message:'Queue is empty'
			};
			view.SP.renderView(tpl);            

			tpl2 = {
				template_id:'#queues-count-tmp',
				view_container_id:'#queues-count-result',
				data:[{"queuesize":data.queuesize}],
			};
			view.SP.renderView(tpl2);
		}
	},

	updateQueueCounterCallback: function(data){
		ws.send({method:'getQueueCounter'});
	},

	updateAgentsCountersCallback: function(data) {
		ws.send({method:'getAgentsCounter'});
	},
	
	getOutgoingIdsCallback: function(data) {
		wsd.outgoingCallerIds = data;
	},

	getAvailableAgentsCallback: function(data) {
		wsd.availableAgents = data;
		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			if(view.$('.agents-status').is('.open'))
				linkClass = 'callToAgent';
			else
				linkClass = 'forward-in-support';
			view.SP.render_workers({'workers':data, linkClass:linkClass});
		}
	},
	getQueueTokenCallback: function(token) {
		//SP.queue = Twilio.TaskRouter.TaskQueue(token);
	},

	getForwardContactsCallback: function(data){
		wsd.forwardContacts = data;
		var views = chrome.extension.getViews({type:'popup'});
		if(views.length) {
			view = views[0];
			tpl = {
				template_id:'#contacts-tmp',
				view_container_id:'#users-contacts',
				data:data,
				empty_template_id:'#contacts-empty-tmp',
				empty_message:'No contacts'
			};
			view.SP.renderView(tpl);
		}
	},
	loginCallback: function(data) {
		var views = chrome.extension.getViews({type:'tab'});
		if(data.length) {
			data = data[0];
			setData('workspace_sid', data.twilio_workspace_id);
			setData('worker_sid', data.twilio_worker_id);
			setData('firstname', data.firstname);
			setData('lastname', data.lastname);
			if(views.length) {
				views[0].welcome();
				location.reload();
			}
		}
		else {
			if(views.length) {
				views[0].incorrectLogin();
			}
		}
	}
}
