var config = require('./config.json');
var SocketIOFileUpload = require('socketio-file-upload');

if (typeof process.env.APPNAME !== 'undefined') {
	console.log('rvicing app: ' + process.env.APPNAME );
	config.app.name = process.env.APPNAME;
} else {
	console.log( 'using defaults' );
}

if (typeof process.env.APPDOMAIN !== 'undefined') {
	config.app.backendDomain = 'https://' + process.env.APPDOMAIN + '/';
}
if (typeof process.env.DBHOST !== 'undefined') {
	config.db.host = process.env.DBHOST;
}
if (typeof process.env.DBUSER !== 'undefined') {
	config.db.user = process.env.DBUSER;
}
if (typeof process.env.DBPASS !== 'undefined') {
	config.db.password = process.env.DBPASS;
}
if (typeof process.env.DBSCHEMA !== 'undefined') {
	config.db.database = process.env.DBSCHEMA;
}

var fs =    require('fs'),
	twilio = require('twilio');

var mysql      = require('mysql');
var querystring = require('querystring');

if(!config.app.ssl) {
	var http = require('http');
	var app = http.createServer();
}
else {
	var https = require('https');
	var options = {};
	if(config.sslFiles.key)
		options['key'] = fs.readFileSync(config.sslFiles.key);
	if(config.sslFiles.cert)
		options['cert'] = fs.readFileSync(config.sslFiles.cert);
	if(config.sslFiles.ca)
		options['ca'] = fs.readFileSync(config.sslFiles.ca);
	var app = https.createServer(options);
}

var connection;
var worker_user = {};
var call_session = {};
var task_session = {};

io = require('socket.io').listen(app);
app.listen(config.app.wsport, "0.0.0.0");

io.set('heartbeat timeout', 10000);
io.set('heartbeat interval', 5000);

//offline pins update
/*setInterval(function(){
	var query = "SELECT ut.ut_user_id, ut.ut_id, ut.ut_date, ut.ut_lat, ut.ut_lng, us.firstname, us.lastname, us.color, el.login_id " +
	"FROM users_tracking ut " +
	"JOIN users us ON ut.ut_user_id = us.id " +
	"LEFT JOIN emp_login el ON ut.ut_user_id = el.login_user_id AND el.login_date = CURDATE() AND el.logout IS NULL " +
	"JOIN (select ut_user_id, max(ut_date) as max_date from users_tracking group by ut_user_id) utj ON " + 
	"utj.ut_user_id = ut.ut_user_id and ut.ut_date = utj.max_date " +
	"WHERE el.login_id IS NULL " + //get pins with no record with current date's login, that has no logout time
	"OR ut.ut_date < (NOW() - INTERVAL 30 MINUTE) " + //or rows with the last tracked time being older than 30 minutes ago
	"GROUP BY ut.ut_user_id";

	connection.query(query, function (error, results, fields) {
		io.sockets.in('chat').emit('message', {
			payload:{
				method:'getOfflinePinsForAll'
			},
			result:results
		});		
	});
}, config.app.interval !== undefined ? config.app.interval : 300000); //5 mins*/

io.on('connection', function(socket){
	var worker_sid = socket.handshake.query.worker_sid == undefined ? '' : socket.handshake.query.worker_sid;
	var chat = socket.handshake.query.chat == undefined ? false : true;
	var user_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
	ws._setTwilioSettings(socket);
		
	io.sockets.in('chat').emit('message', {payload:{method:'connection'}, result:{
		user_id:user_id,
		id:socket.id
	}});
	if(worker_sid) {
		if(ws.agents[config.app.workspaceSid] == undefined)
			ws.agents[config.app.workspaceSid] = {};
		if(ws.agents[config.app.workspaceSid][worker_sid] == undefined)
			ws.agents[config.app.workspaceSid][worker_sid] = 0;

		ws.agents[config.app.workspaceSid][worker_sid] += 1;
		
		if(ws.agents[config.app.workspaceSid][worker_sid] == 1) {
			ws._setAgentStatus(socket, config.app.onlineActivitySid);
		}
		if(worker_user[worker_sid] == undefined) {
			var query = "SELECT * FROM users WHERE twilio_worker_id = '" + worker_sid + "'";
			connection.query(query, function (error, results, fields) {
				worker_user[worker_sid] = results && results.length ? results[0] : {};
			});
		}
	}
	if(chat && user_id) {
		if(ws.chatOnline[user_id] == undefined) {
			ws.chatOnline[user_id] = [];
			ws.hibernate[user_id] = [];
			var query = "SELECT * FROM users WHERE id = '" + parseInt(user_id) + "'";
			connection.query(query, function (error, results, fields) {
				if(results && results.length) {
					ws.users[user_id] = results[0];
				}
			});

		}
		if(!ws.chatOnline[user_id].length)
			io.sockets.in('chat').emit('message', {payload:{method:'toOnline'}, result:user_id});

		if(ws.chatOnline[user_id].indexOf(socket.id) == -1)
			ws.chatOnline[user_id].push(socket.id);
	}

	socket.on('room', function(data) {
		var room = data;
		if(typeof(data) == 'object' && data[0] != undefined)
			room = data[0];
		socket.join(room);
	});

	socket.on('message', function (msg) {
		ws._setTwilioSettings(socket);

		method = msg.method == undefined ? '' : msg.method;
		params = msg.params == undefined ? '' : msg.params;

		if(!method && socket.handshake.query.method != undefined)
			method = socket.handshake.query.method;
		if(!params && socket.handshake.query.params != undefined)
			params = socket.handshake.query.params;
		if(typeof ws[method] === "function")
		{
			var result = ws[method](params, socket);
			if(result !== null) {
				var message = {};
				message.result = result;
				message.payload = msg;
				socket.send(message);
			}
		} else {
			socket.send({error: 'Undefined event: "' + method + '"'});
		}

	});

	socket.on('broadcast', function (message) {
		socket.broadcast.emit('broadcast', message);
	});

	socket.on('messaging', function (message) {
		socket.broadcast.emit('messaging', message);
	});

	socket.on('disconnect', function () {
		var worker_sid = socket.handshake.query.worker_sid == undefined ? '' : socket.handshake.query.worker_sid;
		var chat = socket.handshake.query.chat == undefined ? false : true;
		var user_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		ws._setTwilioSettings(socket);
		var query = "INSERT INTO ws_disconnects (wsd_id, wsd_worker, wsd_date) VALUES (NULL, '" + worker_sid + "', CURRENT_TIMESTAMP)";
		connection.query(query, function (error, results, fields) {
		});
		if (worker_sid) {
			if (ws.agents[config.app.workspaceSid] !== undefined && ws.agents[config.app.workspaceSid][worker_sid] != undefined)
			{
				ws.agents[config.app.workspaceSid][worker_sid]--;
				if(ws.agents[config.app.workspaceSid][worker_sid] == 0)
				{
					delete ws.agents[config.app.workspaceSid][worker_sid];
					ws._setAgentStatus(socket, config.app.offlineActivitySid);
				}
			}
		}

		if(chat && user_id) {
			io.sockets.in('chat').emit('message', {payload:{method:'disconnect'}, result:{
				id:socket.id
			}});
			if(ws.chatOnline[user_id] !== undefined) {
				if(ws.chatOnline[user_id].indexOf(socket.id) >= 0)
					ws.chatOnline[user_id].splice(ws.chatOnline[user_id].indexOf(socket.id), 1);
				if(ws.chatOnline[user_id].length == 0) {
					delete ws.chatOnline[user_id];
					delete ws.users[user_id];
					if(ws.hibernate[user_id].indexOf(socket.id) >= 0)
						ws.hibernate[user_id].splice(ws.hibernate[user_id].indexOf(socket.id), 1);
					io.sockets.in('chat').emit('message', {payload:{method:'toOffline'}, result:user_id});
				}
			}
		}
	});
});


var ws = {
	agents:{},
	users:{},
	chatOnline:{},
	hibernate:{},

	setConfigData: async () => {
		var query = "SELECT settings.stt_key_name, settings.stt_key_value FROM settings " +
			"WHERE settings.stt_key_name = 'voice_twilio_account_sid' OR settings.stt_key_name = 'voice_twilio_auth_token_sid' OR settings.stt_key_name = 'twilioNumber'";

		await connection.query(query, function (error, results, fields) {
			results.map( (v, k) => {
				if (v.stt_key_name === 'voice_twilio_account_sid') {
					config.app.accountSid = v.stt_key_value;
				}
				if (v.stt_key_name === 'voice_twilio_auth_token_sid') {
					config.app.authToken = v.stt_key_value;
				}
				if (v.stt_key_name === 'twilioNumber') {
					config.app.number = v.stt_key_value;
				}
			})
		});

		var query = "SELECT soft_twilio_workspaces.sid as workspaceSid, soft_twilio_workflows.sid as workflowSid, soft_twilio_task_queues.sid as taskQueueSid, soft_twilio_activities.sid as activity, soft_twilio_activities.friendlyName as activity_name " +
			"FROM soft_twilio_workspaces " +
			"INNER JOIN soft_twilio_workflows on soft_twilio_workflows.workspace_id = soft_twilio_workspaces.id " +
			"INNER JOIN soft_twilio_task_queues on soft_twilio_task_queues.workspace_id = soft_twilio_workspaces.id " +
			"INNER JOIN soft_twilio_activities on soft_twilio_activities.workspace_id = soft_twilio_workspaces.id " +
			"WHERE soft_twilio_activities.friendlyName = 'Idle' OR soft_twilio_activities.friendlyName = 'Offline' " +
			"GROUP BY soft_twilio_activities.sid";

		await connection.query(query, function (error, results, fields) {
			results.map( (v, k) => {
				if (v.workspaceSid) {
					if (v.activity_name === 'Idle') {
						config.app.onlineActivitySid = v.activity;
					}
					if (v.activity_name === 'Offline') {
						config.app.offlineActivitySid = v.activity;
					}
					config.app.workspaceSid = v.workspaceSid;
					config.app.workflowSid = v.workflowSid;
					config.app.taskQueueSid = v.taskQueueSid;
				}
			});
		});

		var query = "SELECT soft_twilio_applications.sid as appSid " +
			"FROM soft_twilio_applications " +
			"WHERE soft_twilio_applications.voiceUrl IS NOT NULL " +
			"AND soft_twilio_applications.voiceUrl <>''";

		await connection.query(query, function (error, results, fields) {
			results.map( (v, k) => {
				if (v.appSid) {
					config.app.appSid = v.appSid;
				}
			});
			console.log(config.app, 'config.app');
		});

		return null;
	},

	getChatOnline:function(){
		return ws.chatOnline;
	},
	getAgents:function(){
		return ws.agents;
	},
	getHibernate:function(){
		return ws.hibernate;
	},

	getOutgoingIds:function(params, socket){
		if(config.app.workspaceSid == 'WSd5ddf64bb22aa165abac6c6434764dec') {
			var client = require('twilio')(config.app.accountSid, config.app.authToken);
			if(client !== undefined && client.outgoingCallerIds !== undefined) {
				client.outgoingCallerIds.list((err, data) => {
					var result = [];
					if(data !== undefined && data.outgoingCallerIds !== undefined && data.outgoingCallerIds.length) {
						for (i = 0; i < data.outgoingCallerIds.length; i++) {
							if (data.outgoingCallerIds[i].phone_number != '+14168366877' && data.outgoingCallerIds[i].phone_number != '+16477403466')
								result.push(data.outgoingCallerIds[i]);
						}
						socket.send({
							payload: {method: 'getOutgoingIds'},
							result: result
						});
					} else {
						socket.send({
							payload: {method:'getOutgoingIds'},
							result: [{phoneNumber:config.app.number, friendlyName:formatPhoneNumber(config.app.number)}]
						});
					}
				});
			} else {
				socket.send({
					payload: {method:'getOutgoingIds'},
					result: [{phoneNumber:config.app.number, friendlyName:formatPhoneNumber(config.app.number)}]
				});
			}
		} else {
			if(config.app.workspaceSid != 'WSd5ddf64bb22aa165abac6c6434764dec') {
				var client = require('twilio')(config.app.accountSid, config.app.authToken);
				if(client !== undefined && client.outgoingCallerIds !== undefined) {
					client.outgoingCallerIds.list((err, data) => {
						var result = [];
						if(data !== undefined && data.outgoingCallerIds !== undefined && data.outgoingCallerIds.length) {
							for (i = 0; i < data.outgoingCallerIds.length; i++) {
								result.push(data.outgoingCallerIds[i]);
							}
							socket.send({
								payload: {method: 'getOutgoingIds'},
								result: result
							});
						} else {
							socket.send({
								payload: {method:'getOutgoingIds'},
								result: [{phoneNumber:config.app.number, friendlyName:formatPhoneNumber(config.app.number)}]
							});
						}
					});
				} else {
					socket.send({
						payload: {method:'getOutgoingIds'},
						result: [{phoneNumber:config.app.number, friendlyName:formatPhoneNumber(config.app.number)}]
					});
				}
			} else {
				socket.send({
					payload: {method:'getOutgoingIds'},
					result: [{phoneNumber:config.app.number, friendlyName:formatPhoneNumber(config.app.number)}]
				});
			}
		}
		return null;
	},

	updateInHoldList: function(params, socket){
		var query = "SELECT client_calls_on_hold.*, UNIX_TIMESTAMP(ch_date) as ch_onhold_time, clients.client_id, clients_contacts.cc_name, " +
					"users.id, users.emailid, users.firstname, users.lastname " +
					"FROM client_calls_on_hold " +
					"LEFT JOIN clients ON clients.client_id = client_calls_on_hold.ch_client_id " +
					"LEFT JOIN clients_contacts ON clients.client_id = clients_contacts.cc_client_id AND cc_print = 1 " +
					"LEFT JOIN users ON users.id = client_calls_on_hold.ch_user_id " +
					"ORDER BY ch_date DESC LIMIT 200";

		connection.query(query, function (error, results, fields) {
			var room = ws._find_workspace_room(socket);
			var sender = room ? socket.broadcast.to(room) : socket.broadcast;
			var response = {
				payload: {method:'updateInHoldList'},
				result: {
					rows:results,
					serverTz:new Date().getTimezoneOffset() * 60
				}
			};
			sender.emit('message', response);
		});
		return null;
	},

	getInHoldList: function(params, socket){
		var query = "SELECT client_calls_on_hold.*, UNIX_TIMESTAMP(ch_date) as ch_onhold_time, clients.client_id, clients_contacts.cc_name, " +
					"users.id, users.emailid, users.firstname, users.lastname " +
					"FROM client_calls_on_hold " +
					"LEFT JOIN clients ON clients.client_id = client_calls_on_hold.ch_client_id " +
					"LEFT JOIN clients_contacts ON clients.client_id = clients_contacts.cc_client_id AND cc_print = 1 " +
					"LEFT JOIN users ON users.id = client_calls_on_hold.ch_user_id " +
					"ORDER BY ch_date DESC LIMIT 200";

		connection.query(query, function (error, results, fields) {
			socket.send({
				payload:{method:'updateInHoldList'},
				result:{
					rows:results,
					serverTz:new Date().getTimezoneOffset() * 60
				}
			});
		});
		return null;
	},

	updateToHold:function(params, socket){
		var data = params;
		var worker_sid = socket.handshake.query.worker_sid;
		var client = require('twilio')(config.app.accountSid, config.app.authToken);
		client.calls(params.call_sid).get(function(err, call) {
			var call_sid = data.call_sid;

			client.calls(call.parentCallSid).update({
				url: config.app.backendDomain + 'client_calls/hold_call/' + call_sid + '/' + worker_sid,
				method: "POST"
			});
		});
		return null;
	},

	updateFromHold:function(params, socket){
		var data = params;
		var client = require('twilio')(config.app.accountSid, config.app.authToken);
		client.calls(params.call_sid).get(function(err, call) {
			var call_sid = data.call_sid;
			var contact_uri = data.contact_uri;

			client.calls(call.parentCallSid).update({
				url: config.app.backendDomain + 'client_calls/dial_from_hold_call/' + call_sid + '/' + contact_uri,
				method: "POST"
			});
		});
		return null;
	},

	/*********************TOKENS**************************/
	getToken:function(params, socket) {
		var worker_sid = socket.handshake.query.worker_sid;
		var capability = new twilio.Capability(config.app.accountSid, config.app.authToken);
		capability.allowClientIncoming(params.contact_uri);
		capability.allowClientOutgoing(config.app.appSid);
		return capability.generate(28800);
	},

	getWorkerToken:function(params, socket) {
		var worker_sid = socket.handshake.query.worker_sid;
		var capability = new twilio.TaskRouterWorkerCapability(config.app.accountSid, config.app.authToken, config.app.workspaceSid, worker_sid);
		capability.allowActivityUpdates();
		capability.allowReservationUpdates();
		return {
			token:capability.generate(28800),
			onlineActivitySid:config.app.onlineActivitySid,
			offlineActivitySid:config.app.offlineActivitySid
		}
	},

	getQueueToken:function(params, socket) {
		var capability = new twilio.TaskRouterTaskQueueCapability(config.app.accountSid, config.app.authToken, config.app.workspaceSid, config.app.taskQueueSid);
		capability.allowFetchSubresources();
		capability.allowUpdates();
		return capability.generate(28800);
	},
	/*******************END TOKENS***********************/



    /****************CALLS HISTORY********************/
    getCallsHistory:function(params, socket) {
		var worker_sid = socket.handshake.query.worker_sid != undefined ? socket.handshake.query.worker_sid : false;
		var workspace_sid = socket.handshake.query.workspace_sid != undefined ? socket.handshake.query.workspace_sid : false;
		var result = {};

		if(workspace_sid) {
			var query = "SELECT clients_calls.call_id, clients_calls.call_from, clients_calls.call_to, clients_calls.call_route, " +
				"clients_calls.call_route, clients_calls.call_duration, clients_calls.call_voice, clients_calls.call_date, clients_calls.call_new_voicemail, " +
				"clients.client_id, clients.client_name, users.firstname, users.lastname " +
				"FROM clients_calls LEFT JOIN users ON users.id = clients_calls.call_user_id " +
				"LEFT JOIN clients ON clients.client_id = clients_calls.call_client_id " +
				"WHERE call_to <> '' AND call_disabled <> 1 AND call_workspace_sid = '" + workspace_sid + "' ORDER BY call_date desc LIMIT 200";
			connection.query(query, function (error, results, fields) {
				result.calls = results;

				if(worker_user[worker_sid] != undefined && worker_user[worker_sid].id != undefined) {
						var query = "SELECT clients_calls.call_id, clients_calls.call_from, clients_calls.call_to, clients_calls.call_route, " +
						"clients_calls.call_route, clients_calls.call_duration, clients_calls.call_voice, clients_calls.call_date, clients_calls.call_new_voicemail, " +
						"clients.client_id, clients.client_name, users.firstname, users.lastname " +
						"FROM clients_calls LEFT JOIN users ON users.id = clients_calls.call_user_id " +
						"LEFT JOIN clients ON clients.client_id = clients_calls.call_client_id " +
						"WHERE call_to <> '' AND call_disabled <> 1 AND call_workspace_sid = '" + workspace_sid + "' AND call_user_id = '" + worker_user[worker_sid].id + "' ORDER BY call_date desc LIMIT 200";
					connection.query(query, function (error, results, fields) {
						result.my_calls = results;

						var query = "SELECT clients_calls.call_id, clients_calls.call_from, clients_calls.call_to, clients_calls.call_route, " +
							"clients_calls.call_route, clients_calls.call_duration, clients_calls.call_voice, clients_calls.call_date, clients_calls.call_new_voicemail, " +
							"clients.client_id, clients.client_name, users.firstname, users.lastname " +
							"FROM clients_calls LEFT JOIN users ON users.id = clients_calls.call_user_id " +
							"LEFT JOIN clients ON clients.client_id = clients_calls.call_client_id " +
							"WHERE call_to <> '' AND call_disabled <> 1 AND call_workspace_sid = '" + workspace_sid + "' AND call_duration = 0 AND call_route = 1/* AND call_voice IS NOT NULL */ORDER BY call_date desc LIMIT 200";
						connection.query(query, function (error, results, fields) {
							result.voices = results;

							var query = "SELECT clients_calls.call_id, clients_calls.call_from, clients_calls.call_to, clients_calls.call_route, " +
							"clients_calls.call_route, clients_calls.call_duration, clients_calls.call_voice, clients_calls.call_date, clients_calls.call_new_voicemail, " +
							"clients.client_id, clients.client_name, users.firstname, users.lastname " +
							"FROM clients_calls LEFT JOIN users ON users.id = clients_calls.call_user_id " +
							"LEFT JOIN clients ON clients.client_id = clients_calls.call_client_id " +
							"WHERE call_to <> '' AND call_disabled <> 1 AND call_workspace_sid = '" + workspace_sid + "' AND call_duration = 0 AND call_route = 1/* AND call_voice IS NOT NULL */AND call_user_id = '" + worker_user[worker_sid].id + "' ORDER BY call_date desc LIMIT 200";
							connection.query(query, function (error, results, fields) {
								result.my_voices = results;
								socket.send({payload:{method:'getCallsHistory'}, result:result});
							});
						});
					});
				}
				else {
					result.my_calls = [];
					var query = "SELECT clients_calls.call_from, clients_calls.call_to, clients_calls.call_route, " +
							"clients_calls.call_route, clients_calls.call_duration, clients_calls.call_voice, clients_calls.call_date, " +
							"clients.client_id, clients.client_name, users.firstname, users.lastname " +
							"FROM clients_calls LEFT JOIN users ON users.id = clients_calls.call_user_id " +
							"LEFT JOIN clients ON clients.client_id = clients_calls.call_client_id " +
							"WHERE call_to <> '' AND call_workspace_sid = '" + workspace_sid + "' AND call_duration = 0 AND call_route = 1 AND call_voice IS NOT NULL ORDER BY call_date desc LIMIT 200";
					connection.query(query, function (error, results, fields) {
						result.voices = results;
						socket.send({payload:{method:'getCallsHistory'}, result:result});
					});
				}
			});
    	}
		return null;
	},

	recordIsListen:function(params, socket) {
		var room = ws._find_workspace_room(socket);
		var sender = room ? socket.broadcast.to(room) : socket.broadcast;
		var workspace_sid = socket.handshake.query.workspace_sid;

		var call_id = params.call_id == undefined ? 0 : parseInt(params.call_id);
		var query = "UPDATE clients_calls SET call_new_voicemail = 0 WHERE call_id = '" + call_id + "'";
		connection.query(query, function (error, results, fields) {
			io.sockets.in(workspace_sid).emit('message', {
				payload:{
					method:'recordIsListen'
				},
				result:call_id
			});
		});
		return null;
	},

    updateHistory:function(params, socket) {
    	var worker_sid = socket.handshake.query.worker_sid;
		var room = ws._find_workspace_room(socket);
		var sender = room ? socket.broadcast.to(room) : socket.broadcast;
		var response = {
			payload: {method:'updateHistory'},
			result: true
		};
		sender.emit('message', response);
	},

	searchInHistory:function(params, socket) {
		var query = params.query.replace(/'/g, "\\'");
		var result = {};
		var workspace_sid = socket.handshake.query.workspace_sid != undefined ? socket.handshake.query.workspace_sid : false;
		var query = "SELECT clients_calls.call_from, clients_calls.call_to, clients_calls.call_route, " +
			"clients_calls.call_route, clients_calls.call_duration, clients_calls.call_voice, clients_calls.call_date, " +
			"clients.client_id, clients.client_name, users.firstname, users.lastname " +
			"FROM clients_calls LEFT JOIN users ON users.id = clients_calls.call_user_id " +
			"LEFT JOIN clients ON clients.client_id = clients_calls.call_client_id " +
			"WHERE call_workspace_sid = '" + workspace_sid + "' AND (call_from LIKE '%" + query + "%' OR call_to LIKE '%" + query + "%') " +
			"ORDER BY call_date desc LIMIT 200";
		connection.query(query, function (error, results, fields) {
			result.search = results;
			socket.send({payload:{method:'searchInHistory'}, result:result});
		});
		return null;
	},
	/***************END CALLS HISTORY*****************/



	/******************COUNTERS FUNCTIONS***************/
	getAgentsCounter:function(params, socket) {
		var workers = [];
		var worker_sid = socket.handshake.query.worker_sid;
		var workspace_sid = socket.handshake.query.workspace_sid;
		var client = new twilio.TaskRouterClient(config.app.accountSid, config.app.authToken, config.app.workspaceSid);

		client.workspace.workers.list(function(err, data) {
			var i = 0;
			var j = 0;
			var workersSids = '';
			if(data.workers != undefined) {
				data.workers.forEach(function(worker) {
					if (worker_sid != worker.sid) {
						workers[i] = {
							friendlyName:worker.friendly_name,
							attributes:worker.attributes,
							available:worker.available,
							sid:worker.sid,
						};
						i++;
						workersSids += "'"+worker.sid+"', ";
					}
				});
			}
			workersSids = workersSids.slice(0, -2);

			var query = "SELECT users.* FROM users" + " WHERE twilio_worker_id IN (" + workersSids + ") and active_status='yes'";
			connection.query(query, function (error, results, fields) {

				var workersResult = [];
				if (results && results.length) {
					results.forEach(function(workerDb) {
						workers.map((v, index) => {
							if (v.sid === workerDb.twilio_worker_id) {
								workersResult.push(v);
							}
						});
						j++;
					});
				}
				var result = {
					count: ws.agents[workspace_sid] != undefined ? Object.keys(ws.agents[workspace_sid]).length : 0,
					workers: ws._agents_sorter(workersResult)
				};
				socket.send({payload:{method:'getAgentsCounter'}, result:result});

			});
		});

		return null;
	},

	getQueueCounter:function(params, socket) {
		var client = new twilio.TaskRouterClient(config.app.accountSid, config.app.authToken, config.app.workspaceSid);
		var inQueue = 0;

		client.workspace.statistics.get({}, function(err, responseData) {
			if(!err) {
				inQueue = responseData.realtime.tasks_by_status.pending + responseData.realtime.tasks_by_status.reserved;
			}
			if(client !== undefined && client.workspace !== undefined && client.workspace.tasks !== undefined) {
				client.workspace.tasks.list(function (err, data) {
					var tasks = [];
					if(data !== undefined && data.tasks !== undefined) {
						data.tasks.forEach(function (task) {
							if (task.assignmentStatus == 'pending' || task.assignmentStatus == 'reserved') {
								var client_data = {};
								var attributes = JSON.parse(task.attributes);
								var like = '%';
								if (attributes.caller === undefined) {
									return;
								}
								attributes.caller.match(/\d/g).forEach(function (digit) {
									like += digit + '%'
								});
								var query = "SELECT * FROM clients JOIN clients_contacts ON clients.client_id = clients_contacts.cc_client_id WHERE cc_phone LIKE '" + like + "'";
								connection.query(query, function (error, results, fields) {
									client_data = results && results.length ? results[0] : {};
									var task_data = {
										caller: attributes.caller,
										status: task.assignmentStatus,
										client: client_data
									};
									tasks.push(task_data);
								});
							}
						});
					}
					ws._sendQueueCounter(tasks, inQueue, socket, 0);
				});
			} else {
				var tasks = [];
				ws._sendQueueCounter(tasks, inQueue, socket, 0);
			}
		});
		return null;
	},

	_sendQueueCounter:function(tasks, inQueue, socket, num) {
		if(tasks.length != inQueue) {
			if(num >= 500) {
				setTimeout(function() {
					ws._sendQueueCounter(tasks, inQueue, socket, num+1);
				}, 0);
			}
			else {
				ws._sendQueueCounter(tasks, inQueue, socket, num+1);
			}
		}
		else {
			var data = {
				queuesize: inQueue,
				tasks: tasks
			}
			socket.send({payload:{method:'getQueueCounter'}, result:data});
		}
	},

	updateQueueCounter:function(params, socket) {
		var worker_sid = socket.handshake.query.worker_sid;
		var room = ws._find_workspace_room(socket);
		var sender = room ? socket.broadcast.to(room) : socket.broadcast;
		var response = {
			payload: {method:'updateQueueCounter'},
			result: true
		};
		sender.emit('message', response);
	},
	/*****************END COUNTERS FUNCTIONS*************/



	/********************FORWARD FUNCTIONS**************/
	forwardCallToAgent:function(params, socket) {
		var data = params;
		var client = require('twilio')(config.app.accountSid, config.app.authToken);
		client.calls(params.call_sid).get(function(err, call) {
			if(call.parentCallSid != undefined && call.parentCallSid) {
				client.calls(call.parentCallSid).update({
					url: config.app.backendDomain + 'client_calls/forwardToAgent/' + data.contact_uri + '/0/' + data.forwarder,
					method: "POST"
				});
			}
			else {
				client.calls(data.call_sid).update({
					url: config.app.backendDomain + 'client_calls/forwardToAgent/' + data.contact_uri + '/0/' + data.forwarder,
					method: "POST"
				});
			}
		});
		return null;
	},

	getAvailableAgents:function(params, socket) {
		var worker_sid = socket.handshake.query.worker_sid;
		var workers = [];
		var client = new twilio.TaskRouterClient(config.app.accountSid, config.app.authToken, config.app.workspaceSid);

		client.workspace.workers.list(function(err, data) {
			var i = 0;
			data.workers.forEach(function(worker) {
				if(worker_sid != worker.sid) {
					workers[i] = {
						friendlyName:worker.friendly_name,
						attributes:worker.attributes,
						available:worker.available,
						sid:worker.sid,
					};
					i++;
				}
			});
			socket.send({payload:{method:'getAvailableAgents'}, result:workers});
		});

		return null;
	},

	forwardCallToNumber:function(params, socket) {
		var data = params;
		var client = require('twilio')(config.app.accountSid, config.app.authToken);
		client.calls(params.call_sid).get(function(err, call) {
			if(call.parentCallSid != undefined && call.parentCallSid) {
				client.calls(call.parentCallSid).update({
					url: config.app.backendDomain + 'client_calls/forwardToNumber/' + data.number + '/0/0/' + data.forwarder,
					method: "POST"
				});
			}
			else {
				client.calls(data.call_sid).update({
					url: config.app.backendDomain + 'client_calls/forwardToNumber/' + data.number + '/0/0/' + data.forwarder,
					method: "POST"
				});
			}
		});
		return null;
	},

	getForwardContacts:function(params, socket) {
		var query = "SELECT users.id as contact_id, CONCAT(users.firstname, ' ', users.lastname) as name, REPLACE(employees.`emp_phone`, '.', '') as number " +
					"FROM users " +
					"LEFT JOIN employees ON users.id = employees.emp_user_id " +
					"WHERE active_status = 'yes' AND twilio_user_list = 1 AND employees.emp_phone IS NOT NULL AND employees.emp_phone != '' " +
					"GROUP BY users.id " +
					"ORDER BY firstname";
		connection.query(query, function (error, results, fields) {
			socket.send({payload:{method:'getForwardContacts'}, result:results});
		});
		return null;
	},
	/*****************END FORWARD FUNCTIONS**************/



	/******************RESTORE CALL*********************/
	restoreCall:function(params, socket) {
		if(call_session[socket.id] != undefined && call_session[socket.id])
		{
			var client = require('twilio')(config.app.accountSid, config.app.authToken);
			client.calls(call_session[socket.id]).update({
				url: config.app.backendDomain + 'client_calls/connection_loss',
				method: "POST"
			}, function(err, call) {
				console.log(err);
			});
			call_session[socket.id] = null;
		}
		return null;
	},

	setCallSession:function(params, socket) {
    	call_session[socket.id] = params.call_sid;
    	if(params.task_sid != undefined)
    		task_session[socket.id] = params.task_sid;
    },

    getCallSession:function(params, socket) {
    	if(call_session[socket.id] != undefined && call_session[socket.id])
    		return call_session[socket.id];

    	return false;
    },

    unsetCallSession:function(params, socket) {
    	if(call_session[socket.id] != undefined)
    		call_session[socket.id] = null;
    	if(task_session[socket.id] != undefined)
    		task_session[socket.id] = null;
    },
    /****************END RESTORE CALL********************/

    updateReservation:function(params, socket) {
    	var client = new twilio.TaskRouterClient(config.app.accountSid, config.app.authToken, config.app.workspaceSid);

		client.workspace.tasks(params.task_sid).reservations(params.reservation_sid).update({
			Instruction: 'Redirect',
			RedirectCallSid: params.call_sid,
			RedirectUrl: config.app.backendDomain + 'client_calls/assignment'
		}, function(err, reservation) {
			console.log(reservation.reservation_status);
			console.log(reservation.worker_name);
		});
    },



	/****************APP FUNCTIONS********************/
	login:function(params, socket) {
		var login = params.login;
		var pass = md5(params.pass);
		var query = "SELECT CONCAT(LOWER(firstname), REPLACE(lastname,' ','')) as contact_uri, twilio_worker_id, twilio_workspace_id, firstname, lastname " +
					"FROM users " +
					"WHERE active_status = 'yes' AND twilio_worker_id IS NOT NULL AND twilio_workspace_id IS NOT NULL AND " +
					"emailid = '" + login + "' AND password = '" + pass + "'";
		connection.query(query, function (error, results, fields) {
			socket.send({payload:{method:'login'}, result:results});
		});
		return null;
	},

	jobSafetyPdfSign:function(params, socket) {
		let response = {
			payload: {method:'jobSafetyPdfSign'},
			result: params
		};
		io.sockets.in('chat').emit('message', response);
	},


	/*****************PRIVATE FUNCTIONS*****************/
	_setTwilioSettings:function(socket) {

	},

	_getAgentStatus:function() {
		var client = new twilio.TaskRouterClient(config.app.accountSid, config.app.authToken, config.app.workspaceSid);
		var worker_sid = socket.handshake.query.worker_sid;
		var room = ws._find_workspace_room(socket);
		client.workspace.workers(worker_sid).get((err, worker) => {
			console.log(worker.activitySid);
		});
	},

	_setAgentStatus:function(socket, status) {
		var worker_sid = socket.handshake.query.worker_sid;
		var room = ws._find_workspace_room(socket);
		var sender = room ? socket.broadcast.to(room) : socket.broadcast;
		var client = new twilio.TaskRouterClient(config.app.accountSid, config.app.authToken, config.app.workspaceSid);
		client.workspace.workers(worker_sid).update({
				activitySid: status
			}, function(err, worker) {

		});
		var response = {
			payload: {method:'updateAgentsCounters'},
			result: true
		};
		sender.emit('message', response);
	},

	_find_workspace_room:function(socket) {
		var workspace_sid = socket.handshake.query.workspace_sid != undefined ? socket.handshake.query.workspace_sid : false;
		var room = '';
		for (var key in socket.rooms) {
			if(socket.id != socket.rooms[key]) {
				room = socket.rooms[key];
				break;
			}
		}
		if(!room && workspace_sid) {
			room = workspace_sid;
		}
		return room;
	},

	_agents_sorter:function(workers) {
		function compare(a, b) {
			if (a.friendlyName < b.friendlyName)
				return -1;
			if (a.friendlyName > b.friendlyName)
				return 1;
			return 0;
		}
		return workers.sort(compare);
	},

	_pushJob: function (driver, payload) {
		if (!driver || !payload) {
			return false;
		}

		const datetime = date('Y-m-d H:i:s', new Date().getTime() / 1000);
		const query = "INSERT INTO jobs (job_id, job_driver, job_payload, job_attempts, job_is_completed, job_available_at, job_reserved_at, job_created_at) VALUES " +
			"(NULL, '" + driver + "', '" + JSON.stringify(payload) + "', '0', '0', '" + new Date().getTime() / 1000 + "', '0', '" + datetime + "')";

		try {
			connection.query(query, function (error, results, fields) {
				if (error) {
					console.log('=> pushJob error: ', error.message);
				}

				return !(!results || !results.insertId);
			});
		}
		catch (error) {
			console.log('=> pushJob error: ', error.message);
		}
	},
	/****************END PRIVATE FUNCTIONS***************/



	/**********************CHAT***************************/
	sendMessage:function(params, socket) {
		const sender_id = socket.handshake.query.user_id === undefined ? false : socket.handshake.query.user_id;
		const type = params.type ? params.type : 'text';
		const message = type !== 'text' ? params.message : stringEscape(escapeHtml(params.message));
		const datetime = date('Y-m-d H:i:s', new Date().getTime() / 1000);
		const query = "INSERT INTO chat (id, chat.from, chat.to, message, sent, recd, chat.type) VALUES " +
					"(NULL, '" + parseInt(sender_id) + "', '" + parseInt(params.user_id) + "', '" + message + "', '" + datetime + "', '0', '" + type + "')";

		connection.query(query, function (error, results, fields) {
			const query = "SELECT chat.*, CONCAT(from_user.firstname, ' ', from_user.lastname) as from_name, " +
				"CONCAT(to_user.firstname, ' ', to_user.lastname) as to_name, ud.device_id FROM chat " +
					"JOIN users from_user ON chat.from = from_user.id " +
					"JOIN users to_user ON chat.to = to_user.id " +
					"LEFT JOIN user_devices ud ON ud.device_id = (SELECT device_id FROM user_devices WHERE device_user_id = chat.to " +
						"AND device_token_expiration >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND firebase_token <> '' " +
						"AND firebase_token IS NOT NULL LIMIT 1) " +
					"WHERE chat.id = " + results.insertId;

			connection.query(query, function (error, results, fields) {
				if (results && results.length && results[0].device_id) {
					const post = results[0];

					const payload = {
						user_id: post.to,
						action: 'Chat/message',
						params: {
							from: post.from,
							to: post.to,
							sent: post.sent,
							type: post.type,
						},
						title: post.from_name,
					};

					if (post.type === 'text') {
						const message = post.message
							.replace(/(?:\*)([^*]+)(?:\*)/gm, "$1")
							.replace(/(?:_)([^_]+)(?:_)/gm, "$1")
							.replace(/(?:~)([^~]+)(?:~)/gm, "$1");
						payload.body = stringEscape(escapeHtml(message));
					} else {
						if (post.type === 'image') {
							payload.body = 'Image sent';
                   			payload.image_url = post.message;
						}
						else if (post.type === 'pdf') {
							payload.body = 'Pdf sent';
						}
						else if (post.type === 'doc') {
							payload.body = 'Doc sent';
						}
						else if (post.type === 'excel') {
							payload.body = 'Excel sent';
						}
						else if (post.type === 'file') {
							payload.body = 'File sent';
						}
					}

					ws._pushJob('notifications/send', payload);
				}

				io.sockets.in('chat-' + params.user_id).emit(
					'message',
					{
						payload: {
							method: 'sendMessage'
						},
						result: {
							message: results && results.length ? results[0] : null,
							user_id: sender_id,
						}
					}
				);
				io.sockets.in('chat-' + sender_id).emit(
					'message',
					{
						payload: {
							method: 'sendMessage'
						},
						result: {
							message: results && results.length ? results[0] : null,
							user_id: params.user_id,
						}
					}
				);
			});
		});

		return null;
	},

	chatHistory:function(params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		var query = "SELECT chat.*, CONCAT(from_user.firstname, ' ', from_user.lastname) as from_name, CONCAT(to_user.firstname, ' ', to_user.lastname) as to_name FROM chat " +
					"JOIN users from_user ON chat.from = from_user.id " +
					"JOIN users to_user ON chat.to = to_user.id " +
					"WHERE (chat.from = " + parseInt(sender_id) + " AND chat.to = " + parseInt(params.user_id) + ") OR " +
					"(chat.from = " + parseInt(params.user_id) + " AND chat.to = " + parseInt(sender_id) + ") ORDER BY sent DESC, id DESC LIMIT 100";
		connection.query(query, function (error, results, fields) {
			socket.send({payload:{method:'chatHistory'}, result:{rows:results?results.reverse():[], user_id:params.user_id}});
		});
		return null;
	},

	getUnread:function(params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		var query = "SELECT chat.*, CONCAT(from_user.firstname, ' ', from_user.lastname) as from_name, CONCAT(to_user.firstname, ' ', to_user.lastname) as to_name, last.count_unread FROM chat " +
					"JOIN users from_user ON chat.from = from_user.id " +
					"JOIN users to_user ON chat.to = to_user.id " +
					"JOIN (" +
						"SELECT MAX(chat.id) as id, COUNT(chat.id) as count_unread, IF(`from` = " + parseInt(sender_id) + ", `to`, `from`) as chat_with " +
						"FROM chat " +
						"WHERE (`from` = " + parseInt(sender_id) + " OR `to` = " + parseInt(sender_id) + ") AND chat.recd = 0 " +
						"GROUP BY chat_with " +
						"HAVING ABS(chat_with) > 0 " +
					") as last ON last.id = chat.id " +
					"WHERE (chat.to = " + parseInt(sender_id) + " AND chat.recd = 0 AND from_user.system_user = 0 AND from_user.active_status = 'yes') GROUP BY chat.from ORDER BY sent DESC";
		connection.query(query, function (error, results, fields) {
			socket.send({payload:{method:'getUnread'}, result:{rows:results?results.reverse():[]}});
		});
		return null;
	},

	readChat:function(params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		var chatWith = params.user_id;
		var query = "UPDATE chat SET recd = 1 " +
					"WHERE (chat.to = " + parseInt(sender_id) + " AND chat.from = " + parseInt(chatWith) + " AND chat.recd = 0)";
		connection.query(query, function (error, results, fields) {
			var query = "SELECT chat.*, CONCAT(from_user.firstname, ' ', from_user.lastname) as from_name, CONCAT(to_user.firstname, ' ', to_user.lastname) as to_name FROM chat " +
					"JOIN users from_user ON chat.from = from_user.id " +
					"JOIN users to_user ON chat.to = to_user.id " +
					"WHERE (chat.to = " + parseInt(sender_id) + " AND chat.recd = 0 AND from_user.system_user = 0 AND from_user.active_status = 'yes') GROUP BY chat.from ORDER BY sent DESC";
			connection.query(query, function (error, results, fields) {
				io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'getUnread'}, result:{rows:results?results.reverse():[]}});
			});
		});
	},

	toggleChat:function(params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'toggleChat'}, result:params.chatbox});
		return null;
	},


	closeChat:function(params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'closeChat'}, result:params.chatbox});
		return null;
	},

	createChat:function(params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'createChat'}, result:params.chatbox});
		return null;
	},

	/*************************GPS*************************/


	getTrackPins:function(params, socket) {
		//has today's date and also did not log out - means is online, else login_id will be null, which means - offline
		var query = "SELECT ut.ut_user_id, ut.ut_id, ut.ut_date, ut.ut_lat, ut.ut_lng, us.firstname, us.lastname, us.color, el.login_id, " +
			"TIMESTAMPDIFF(MINUTE, ut.ut_date, NOW()) as minutes_diff " +
			"FROM users_tracking ut " +
			"JOIN users us ON ut.ut_user_id = us.id " +
			"LEFT JOIN emp_login el ON ut.ut_user_id = el.login_user_id AND el.login_date = CURDATE() AND el.logout IS NULL " +
			"JOIN (select ut_user_id, max(ut_date) as max_date from users_tracking group by ut_user_id) utj ON " +
			"utj.ut_user_id = ut.ut_user_id and ut.ut_date = utj.max_date " +
			"WHERE ut_date >= CURDATE() " +
			"GROUP BY ut.ut_user_id";

		connection.query(query, function (error, results, fields) {
			socket.send({
				payload:{
					method:'getTrackPins'
				},
				result:{
					rows:results
				}
			});
		});

		return null;
	},

	newTrackingData: function (params, socket) {

		io.sockets.in('chat').emit('message', {payload:{method:'newTrackingData'}, result:params});

		return null;
	},

	getUserTrackPins:function(params, socket) {

		var ldate = params.date + ' 00:00:00';
		var rdate = params.date + ' 23:59:59';

		var query = "SELECT ut_user_id, ut_id, ut_date, ut_lat, ut_lng " +
			"FROM users_tracking " +
			"WHERE ut_user_id = " + parseInt(params.user) +
			" AND ut_date >= '" + ldate + "' AND ut_date <= '" + rdate + "'";

		connection.query(query, function (error, results, fields) {
			socket.send({
				payload:{
					method:'getUserTrackPins'
				},
				result:{
					rows:results,
					query: query,
					error: error,
					params: params
				}
			});
		});

		return null;
	},


	/*************************SMS*************************/

	openMessenger:function(params, socket) {
		var result = {};
		var offset = params.offset == undefined ? 0 : parseInt(params.offset);
		var mode = params.mode == undefined ? '' : params.mode;
		var query = "SELECT sms_messages.sms_id, sms_messages.sms_number, sms_messages.sms_body, sms_messages.sms_date, sms_messages.sms_status, sms_messages.sms_readed, GROUP_CONCAT(cc_name SEPARATOR '|') as cc_name, /*GROUP_CONCAT(cc_client_id SEPARATOR '|') as */cc_client_id, users.firstname, users.lastname, employees.emp_phone, client_name, last_incoming_message.sms_support as last_incoming_sms_support FROM sms_messages " +
					"RIGHT JOIN (SELECT DISTINCT (sms_number), MAX(sms_id) as sms_id, SUM(sms_auto) as sum_auto, count(sms_id) as count_all FROM sms_messages GROUP BY sms_number ORDER BY sms_date DESC) last_message ON last_message.sms_id = sms_messages.sms_id " +
					"LEFT JOIN (SELECT MAX(sms_id) as last_incoming_message_id, sms_number as last_incoming_sms_number FROM sms_messages WHERE sms_incoming = 1 GROUP BY sms_number ORDER BY sms_date DESC) last_incoming_message_max_id ON last_incoming_message_max_id.last_incoming_sms_number = sms_messages.sms_number " +
					"LEFT JOIN sms_messages last_incoming_message ON last_incoming_message_max_id.last_incoming_message_id = last_incoming_message.sms_id " +
					"LEFT JOIN clients_contacts ON clients_contacts.cc_phone_clean = sms_messages.sms_number " +
					"LEFT JOIN clients ON clients_contacts.cc_client_id = clients.client_id " +
					"LEFT JOIN employees ON employees.emp_phone = sms_messages.sms_number " +
					"LEFT JOIN users ON users.id = employees.emp_user_id ";
		query += "WHERE (count_all - sum_auto) > 0 ";
		if(mode == 'users') {
			query += "AND employee_id IS NOT NULL ";
		}
		if(mode == 'clients') {
			query += "AND employee_id IS NULL ";
		}
		if(mode == 'supportchat') {
			query += "AND last_incoming_message.sms_support = 1 ";
		}

		query += "GROUP BY sms_messages.sms_number ORDER BY sms_date DESC " +
					"LIMIT " + offset + ", 100";
		connection.query(query, function (error, results, fields) {

			socket.send({
				payload:{
					method:'openMessenger'
				},
				result:{
					rows:results,
					mode:mode,
					offset:offset
				}
			});
		});
		return null;
	},

	getSmsUsersList:function(params, socket) {
		var query = "SELECT emp_phone as id, CONCAT(firstname, ' ', lastname) as text FROM employees " +
			"LEFT JOIN users ON users.id = employees.emp_user_id " +
			"WHERE emp_phone IS NOT NULL AND emp_phone <> '' AND LENGTH(emp_phone) >= 10 AND active_status = 'yes'" +
			"GROUP BY emp_phone, CONCAT(firstname, ' ', lastname) ORDER BY text";

		connection.query(query, function (error, results, fields) {
			socket.send({
				payload:{
					method:'getSmsUsersList'
				},
				result:{
					rows:results
				}
			});
		});
		return null;
	},

	searchSms:function(params, socket) {
		var offset = params.offset == undefined ? 0 : parseInt(params.offset);
		var search = addslashes(params.search);
		var query = "SELECT sms_messages.sms_id, sms_messages.sms_number, sms_body, sms_date, sms_status, sms_readed, GROUP_CONCAT(cc_name SEPARATOR '|') as cc_name, /*GROUP_CONCAT(cc_client_id SEPARATOR '|') as */cc_client_id, users.firstname, users.lastname, employees.emp_phone, client_name, 'search' as block FROM sms_messages " +
					"LEFT JOIN clients_contacts ON clients_contacts.cc_phone_clean = sms_messages.sms_number " +
					"LEFT JOIN clients ON clients_contacts.cc_client_id = clients.client_id " +
					"LEFT JOIN employees ON employees.emp_phone = sms_messages.sms_number " +
					"LEFT JOIN users ON users.id = employees.emp_user_id " +
					"RIGHT JOIN ( " +
					    "SELECT DISTINCT (sms_number), MAX(sms_id) as sms_id FROM sms_messages " +
						"LEFT JOIN clients_contacts ON clients_contacts.cc_phone_clean = sms_messages.sms_number " +
						"LEFT JOIN clients ON clients_contacts.cc_client_id = clients.client_id " +
						"LEFT JOIN employees ON employees.emp_phone = sms_messages.sms_number " +
						"LEFT JOIN users ON users.id = employees.emp_user_id " +
						"WHERE cc_name LIKE '%" + search + "%' OR sms_body LIKE '%" + search + "%' OR sms_number LIKE '%" + search + "%' OR client_name LIKE '%" + search + "%' OR CONCAT(firstname, ' ', lastname) LIKE '%" + search + "%' " +
						"GROUP BY sms_number ORDER BY sms_date DESC LIMIT 100 " +
					") last_message ON last_message.sms_id = sms_messages.sms_id " +
					"GROUP BY sms_messages.sms_number ORDER BY sms_date DESC " +
					"LIMIT " + offset + ", 100";
		connection.query(query, function (error, results, fields) {
			socket.send({
				payload:{
					method:'searchSms'
				},
				result:{
					rows:results,
					offset:offset
				}
			});
		});
		return null;
	},

	searchRecipient:function(params, socket) {
		var search = params.search == undefined ? '' : addslashes(params.search);
		var mode = params.mode == undefined ? '' : params.mode;
		var query = "";

		if(mode == 'clients' || mode == 'supportchat' || mode == '') {
			query += "SELECT cc_phone as id, cc_name as text FROM clients_contacts WHERE cc_phone IS NOT NULL AND cc_phone <> '' AND LENGTH(cc_phone) >= 10 AND cc_name IS NOT NULL AND cc_name <> '' AND " +
			"(cc_name LIKE '%" + search + "%' OR cc_phone LIKE '%" + search + "%') " +
			"GROUP BY cc_phone, cc_name ";
		}

		if(mode == '')
			query += "UNION ";

		if(mode == 'users' || mode == '') {
			query += "SELECT emp_phone as id, CONCAT(firstname, ' ', lastname) as text FROM employees " +
			"LEFT JOIN users ON users.id = employees.emp_user_id " +
			"WHERE emp_phone IS NOT NULL AND emp_phone <> '' AND LENGTH(emp_phone) >= 10 AND " +
			"(CONCAT(firstname, ' ', lastname) LIKE '%" + search + "%' OR emp_phone LIKE '%" + search + "%') " +
			"GROUP BY emp_phone, CONCAT(firstname, ' ', lastname) ";
		}

		query += "ORDER BY text";

		connection.query(query, function (error, results, fields) {
			socket.send({
				payload:{
					method:'searchRecipient'
				},
				result:{
					rows:results,
					search:search
				}
			});
		});
		return null;
	},

	getSmsHistory:function(params, socket) {
		var offset = params.offset == undefined ? 0 : parseInt(params.offset);
		var suffix = params.suffix == undefined ? '' : params.suffix;
		var number = params.number == undefined ? 0 : parseInt(params.number);
		var query = "SELECT * FROM sms_messages WHERE sms_number = '" + number + "' OR sms_number = '+1" + number + "' " +
					"ORDER BY sms_date DESC LIMIT " + offset + ", 100";

		connection.query(query, function (error, results, fields) {

			socket.send({
				payload:{
					method:'getSmsHistory'
				},
				result:{
					rows:results?results.reverse():[],
					suffix:suffix,
					number:number,
					offset:offset
				}
			});

			ws.smsChatWasRead(params, socket);
		});
		return null;
	},

	sendSms:function(params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		var senderName = ws.users[sender_id] == undefined ? '' : "\n" + ws.users[sender_id].firstname + ' ' + ws.users[sender_id].lastname;
		var senderFirstname = ws.users[sender_id] == undefined ? '' : ws.users[sender_id].firstname;
		var message = params.message == undefined ? 0 : params.message;
		var clientId = params.clientId == undefined ? 'NULL' : params.clientId;
		var number = params.number == undefined ? 0 : parseInt(params.number);

		var messageBody = addslashes(escapeHtml(message)) + senderName;

		if(io.sockets.adapter.rooms != undefined && io.sockets.adapter.rooms['supportchat_' + number] != undefined) {
			messageBody = addslashes(escapeHtml(message));
		}

		var messageRow = {
			sms_body:messageBody,
			sms_client_id:null,
			sms_date:date('Y-m-d H:i:s', new Date().getTime() / 1000),
			sms_error:clientId,
			sms_incoming:0,
			sms_number:number,
			sms_readed:1,
			sms_status:"queued",
			sms_support:0,
			sms_user_id:sender_id,
		}

		var query = "INSERT INTO sms_messages (sms_id, sms_sid, sms_number, sms_body, sms_date, sms_support, sms_readed, sms_client_id, sms_user_id, sms_incoming, sms_status, sms_error) VALUES " +
					"(NULL, NULL, '" + number + "', '" + messageBody + "', '" + messageRow.sms_date + "', '0', '1', '" + clientId + "', '" + sender_id + "', '0', 'queued', NULL)";

		connection.query(query, function (error, results, fields) {

			var sms_id = results.insertId;
			messageRow.sms_id = sms_id;

			if(io.sockets.adapter.rooms != undefined && io.sockets.adapter.rooms['supportchat_' + number] != undefined) {

				var query = "UPDATE sms_messages SET sms_status = 'delivered' WHERE sms_id = " + sms_id;
				messageRow.sms_status = 'delivered';
				messageRow.firstname = senderFirstname;
				connection.query(query, function (error, results, fields) {});

				io.sockets.in('supportchat_' + number).emit('message', {
					payload:{
						method:'sendSupportMessage'
					},
					result:messageRow
				});
			}
			else {
				var client = require('twilio')(config.app.accountSid, config.app.authToken);
				client.messages.create({
					to: '+1' + number,
					from: config.app.number,
					body: message + senderName,
					messagingServiceSid: config.app.messagingServiceSid,
					statusCallback: config.app.backendDomain + 'client_calls/send_sms/' + results.insertId
				},(err, message) => {
					if(message != undefined && message.sid != undefined) {
						var query = "UPDATE sms_messages SET sms_sid = '" + message.sid + "' WHERE sms_id = " + sms_id;
						connection.query(query, function (error, results, fields) {});
					}
				});
			}

			var query = "SELECT sms_messages.sms_id, sms_messages.sms_number, sms_messages.sms_body, sms_messages.sms_date, sms_messages.sms_status, sms_messages.sms_readed, GROUP_CONCAT(cc_name SEPARATOR '|') as cc_name, /*GROUP_CONCAT(cc_client_id SEPARATOR '|') as */cc_client_id, users.firstname, users.lastname, employees.emp_phone, client_name, last_incoming_message.sms_support as last_incoming_sms_support FROM sms_messages " +
					"RIGHT JOIN (SELECT DISTINCT (sms_number), MAX(sms_id) as sms_id, SUM(sms_auto) as sum_auto, count(sms_id) as count_all FROM sms_messages WHERE sms_number = '" + number + "' GROUP BY sms_number ORDER BY sms_date DESC) last_message ON last_message.sms_id = sms_messages.sms_id " +
					"LEFT JOIN (SELECT MAX(sms_id) as last_incoming_message_id, sms_number as last_incoming_sms_number FROM sms_messages WHERE sms_incoming = 1 GROUP BY sms_number ORDER BY sms_date DESC) last_incoming_message_max_id ON last_incoming_message_max_id.last_incoming_sms_number = sms_messages.sms_number " +
					"LEFT JOIN sms_messages last_incoming_message ON last_incoming_message_max_id.last_incoming_message_id = last_incoming_message.sms_id " +
					"LEFT JOIN clients_contacts ON clients_contacts.cc_phone_clean = sms_messages.sms_number " +
					"LEFT JOIN clients ON clients_contacts.cc_client_id = clients.client_id " +
					"LEFT JOIN employees ON employees.emp_phone = sms_messages.sms_number " +
					"LEFT JOIN users ON users.id = employees.emp_user_id " +
					"WHERE (count_all - sum_auto) > 0 AND sms_messages.sms_number = '" + number + "' GROUP BY sms_messages.sms_number ORDER BY sms_date DESC";

			connection.query(query, function (error, results, fields) {
				io.sockets.in('sms').emit('message', {
					payload:{
						method:'sendSms'
					},
					result:{
						number:number,
						rows:[messageRow],
						smschatbox:results && results.length ? results[0] : null
					}
				});
			});
		});
		return null;
	},

	newSmsMessage:function(params, socket) {
		var number = params.sms_number == undefined ? 0 : parseInt(params.sms_number);

		var query = "SELECT sms_messages.sms_id, sms_messages.sms_number, sms_messages.sms_body, sms_messages.sms_date, sms_messages.sms_status, sms_messages.sms_readed, GROUP_CONCAT(cc_name SEPARATOR '|') as cc_name, /*GROUP_CONCAT(cc_client_id SEPARATOR '|') as */cc_client_id, users.firstname, users.lastname, employees.emp_phone, client_name, last_incoming_message.sms_support as last_incoming_sms_support FROM sms_messages " +
					"RIGHT JOIN (SELECT DISTINCT (sms_number), MAX(sms_id) as sms_id, SUM(sms_auto) as sum_auto, count(sms_id) as count_all FROM sms_messages WHERE sms_number = '" + number + "' GROUP BY sms_number ORDER BY sms_date DESC) last_message ON last_message.sms_id = sms_messages.sms_id " +
					"LEFT JOIN (SELECT MAX(sms_id) as last_incoming_message_id, sms_number as last_incoming_sms_number FROM sms_messages WHERE sms_incoming = 1 GROUP BY sms_number ORDER BY sms_date DESC) last_incoming_message_max_id ON last_incoming_message_max_id.last_incoming_sms_number = sms_messages.sms_number " +
					"LEFT JOIN sms_messages last_incoming_message ON last_incoming_message_max_id.last_incoming_message_id = last_incoming_message.sms_id " +
					"LEFT JOIN clients_contacts ON clients_contacts.cc_phone_clean = sms_messages.sms_number " +
					"LEFT JOIN clients ON clients_contacts.cc_client_id = clients.client_id " +
					"LEFT JOIN employees ON employees.emp_phone = sms_messages.sms_number " +
					"LEFT JOIN users ON users.id = employees.emp_user_id " +
					"WHERE (count_all - sum_auto) > 0 AND sms_messages.sms_number = '" + number + "' GROUP BY sms_messages.sms_number ORDER BY sms_date DESC";

		connection.query(query, function (error, results, fields) {
			io.sockets.in('sms').emit('message', {
				payload:{
					method:'sendSms'
				},
				result:{
					number:number,
					rows:[params],
					smschatbox:results && results.length ? results[0] : null
				}
			});
		});
		return null;
	},

	getSmsCounter:function(params, socket) {
		var query = "SELECT COUNT(sms_number) as counter FROM sms_messages WHERE sms_readed = 0";

		connection.query(query, function (error, results, fields) {
			io.sockets.in('sms').emit('message', {
				payload:{
					method:'getSmsCounter'
				},
				result:results && results.length ? results[0].counter : 0
			});
		});
		return null;
	},

	/***REF***/

	refreshChatboxes: function(params, socket) {
		io.sockets.in('sms').emit('message', {
			payload: {
				method:'refreshChatboxes'
			},
			result: {
				incoming: params.incoming !== undefined,
				user_sms_limit: params.user_sms_limit || null,
				count_unreaded: params.count_unreaded !== undefined ? params.count_unreaded : null,
				count_unreaded_only: params.count_unreaded_only || false,
				number: params.number || null
			}
		});
		return null;
	},

	updateSmsStatus:function(params, socket) {
		io.sockets.in('sms').emit('message', {
			payload:{
				method:'updateSmsStatus'
			},
			result:params
		});
	},

	/******************SUPPORT CHAT******************/

	supportChatNow: function(params, socket) {

		var phoneNumber = socket.handshake.query.phoneNumber == undefined ? false : parseInt(socket.handshake.query.phoneNumber);
		var message = params.message == undefined ? '' : params.message;
		var name = params.name == undefined ? '' : params.name;
		var todayDateStart = date('Y-m-d 00:00:00', new Date().getTime() / 1000);
		var nowDateTime = date('Y-m-d H:i:s', new Date().getTime() / 1000);

		if(message && message.length) {

			var query = "SELECT * FROM clients_contacts WHERE cc_phone = '" + phoneNumber + "' AND cc_name = '" + addslashes(name) + "'";
			connection.query(query, function (error, results, fields) {
				if(!results || !results.length) {
					var query = "INSERT INTO clients_contacts (cc_title, cc_name, cc_phone, cc_phone_clean) VALUES ('Chat Contact', '" + addslashes(name) + "', '" + phoneNumber + "', '" + phoneNumber + "')";
					connection.query(query, function (error, results, fields) {});
				}
			});

			var query = "INSERT INTO sms_messages (sms_id, sms_sid, sms_number, sms_body, sms_date, sms_support, sms_readed, sms_client_id, sms_user_id, sms_incoming, sms_status, sms_error) VALUES " +
					"(NULL, NULL, '" + phoneNumber + "', '" + addslashes(escapeHtml(message)) + "', '" + nowDateTime + "', '1', '0', NULL, NULL, '1', 'delivered', NULL)";

			var row = {
				sms_sid: 0,
				sms_number: phoneNumber,
				sms_body: addslashes(escapeHtml(message)),
				sms_date: nowDateTime,
				sms_support: 1,
				sms_readed: 0,
				sms_user_id: '',
				sms_incoming: 1,
				sms_status: 'delivered',
				sms_error: '',
			};

			connection.query(query, function (error, results, fields) {
				var sms_id = results.insertId;
				row.sms_id = sms_id;
				var query = "SELECT sms_messages.*, users.firstname FROM sms_messages LEFT JOIN users ON sms_user_id = users.id WHERE (sms_number = '" + phoneNumber + "' OR sms_number = '+1" + phoneNumber + "') " +
						"AND sms_date >=  '" + todayDateStart + "' " +
						"ORDER BY sms_date ASC LIMIT 0, 100";
				connection.query(query, function (error, results, fields) {
					socket.send({
						payload:{
							method:'supportChatNow'
						},
						result:results
					});

					ws.newSmsMessage(row);
				});
			});
		}
		else {
			var query = "SELECT sms_messages.*, users.firstname FROM sms_messages LEFT JOIN users ON sms_user_id = users.id WHERE (sms_number = '" + phoneNumber + "' OR sms_number = '+1" + phoneNumber + "') " +
						"AND sms_date >=  '" + todayDateStart + "' " +
						"ORDER BY sms_date ASC LIMIT 0, 100";
			connection.query(query, function (error, results, fields) {
				socket.send({
					payload:{
						method:'supportChatNow'
					},
					result:results
				});
			});
		}

		return null;
	},

	chatLeaveMessage: function(params, socket) {
		var phoneNumber = params.phone == undefined ? '' : parseInt(params.phone);
		var message = params.message == undefined ? '' : params.message;
		var name = params.name == undefined ? '' : params.name;
		var todayDateStart = date('Y-m-d 00:00:00', new Date().getTime() / 1000);
		var nowDateTime = date('Y-m-d H:i:s', new Date().getTime() / 1000);

		if(message && message.length && phoneNumber) {

			var query = "SELECT * FROM clients_contacts WHERE cc_phone = '" + phoneNumber + "' AND cc_name = '" + addslashes(name) + "'";
			connection.query(query, function (error, results, fields) {
				if(!results || !results.length) {
					var query = "INSERT INTO clients_contacts (cc_title, cc_name, cc_phone, cc_phone_clean) VALUES ('Chat Contact', '" + addslashes(name) + "', '" + phoneNumber + "', '" + phoneNumber + "')";
					connection.query(query, function (error, results, fields) {});
				}
			});

			var query = "INSERT INTO sms_messages (sms_id, sms_sid, sms_number, sms_body, sms_date, sms_support, sms_readed, sms_client_id, sms_user_id, sms_incoming, sms_status, sms_error) VALUES " +
					"(NULL, NULL, '" + phoneNumber + "', '" + addslashes(escapeHtml(message)) + "', '" + nowDateTime + "', '1', '0', NULL, NULL, '1', 'delivered', NULL)";

			var row = {
				sms_sid: 0,
				sms_number: phoneNumber,
				sms_body: addslashes(escapeHtml(message)),
				sms_date: nowDateTime,
				sms_support: 1,
				sms_readed: 0,
				sms_user_id: '',
				sms_incoming: 1,
				sms_status: 'delivered',
				sms_error: '',
			};

			connection.query(query, function (error, results, fields) {
				var sms_id = results.insertId;
				row.sms_id = sms_id;
				socket.send({
					payload:{
						method:'chatLeaveMessage'
					},
					result:row
				});
				ws.newSmsMessage(row);
			});
		}
		return null;
	},

	sendSupportMessage: function(params, socket) {
		var phoneNumber = socket.handshake.query.phoneNumber == undefined ? false : parseInt(socket.handshake.query.phoneNumber);
		var message = params.message == undefined ? '' : params.message;
		var todayDateStart = date('Y-m-d 00:00:00', new Date().getTime() / 1000);
		var nowDateTime = date('Y-m-d H:i:s', new Date().getTime() / 1000);

		if(!message || message == '')
			return false;

		var query = "INSERT INTO sms_messages (sms_id, sms_sid, sms_number, sms_body, sms_date, sms_support, sms_readed, sms_client_id, sms_user_id, sms_incoming, sms_status, sms_error) VALUES " +
					"(NULL, NULL, '" + phoneNumber + "', '" + addslashes(escapeHtml(message)) + "', '" + nowDateTime + "', '1', '0', NULL, NULL, '1', 'delivered', NULL)";

		var row = {
			sms_sid: 0,
			sms_number: phoneNumber,
			sms_body: addslashes(escapeHtml(message)),
			sms_date: nowDateTime,
			sms_support: 1,
			sms_readed: 0,
			sms_user_id: '',
			sms_incoming: 1,
			sms_status: 'delivered',
			sms_error: '',
		};

		connection.query(query, function (error, results, fields) {
			row.sms_id = results.insertId;
			socket.send({
				payload:{
					method:'sendSupportMessage'
				},
				result:row
			});

			ws.newSmsMessage(row);
		});

		return null;
	},

	supportsAvailable: function(params, socket) {
		var date = new Date();
		if(date.getHours() > 18 || date.getHours() < 7 || date.getDay() == 0 || date.getDay() == 6)
			return 0;
		if(io.sockets.adapter.rooms != undefined && io.sockets.adapter.rooms['sms_support'] != undefined) {
			return Object.keys(io.sockets.adapter.rooms['sms_support'].sockets).length;
		}
		return 0;
	},

	roomsLog: function(params, socket) {
		return true;
	},


	/******************SUPPORT CHAT******************/
	syncJobFailed: function (params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'syncJobFailed'}, result:params});
		return null;
	},

	syncJobSuccess: function (params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'syncJobSuccess'}, result:params});
		return null;
	},

	trackerStarted: function (params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'trackerStarted'}, result:params});
		return null;
	},

	trackerStopped: function (params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'trackerStopped'}, result:params});
		io.sockets.in('chat').emit('message', {payload:{method:'userStopped'}, result:sender_id});
		return null;
	},

	trackerHistoryChanged: function (params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		//setTimeout(function(){
			io.sockets.in('chat-' + sender_id).emit('message', {payload:{method:'trackerHistoryChanged'}, result:params});
		//}, 3000);
		return null;
	},

	hibernateOn: function (params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		if(ws.hibernate[sender_id] != undefined && ws.hibernate[sender_id].indexOf(socket.id) == -1)
			ws.hibernate[sender_id].push(socket.id);
		io.sockets.in('chat').emit('message', {payload: {method: 'hibernateOn'}, result: sender_id});
	},

	hibernateOff: function (params, socket) {
		var sender_id = socket.handshake.query.user_id == undefined ? false : socket.handshake.query.user_id;
		if (ws.hibernate[sender_id] != undefined && ws.hibernate[sender_id].indexOf(socket.id) >= 0)
			ws.hibernate[sender_id].splice(ws.hibernate[sender_id].indexOf(socket.id), 1);
		io.sockets.in('chat').emit('message', {payload: {method: 'hibernateOff'}, result: sender_id});
	},

	// php: function (params, socket) {
	// 	io.sockets.in('chat').emit('message', {payload:{method:'php'}, result:sender_id});
	// },

}

function push(user_id, data) {
	if(ws.chatOnline[user_id] === undefined || !ws.chatOnline[user_id] || (ws.chatOnline[user_id] && ws.hibernate[user_id] !== undefined && ws.chatOnline[user_id].length === ws.hibernate[user_id].length)) {
		data.user_id = user_id;
		// sendPost('/chat/sendPush', data);
	}
}

function sendPost(path, data) {
	// Build the post string from an object
	var post_data = JSON.stringify(data);

	// An object of options to indicate where to post to
	var post_options = {
		// rejectUnauthorized: false,
		host: config.app.backendDomain.replace('https:', '').replace('http:').split('/').join(''),
		port: config.app.ssl ? 443 : 80,
		path: path,
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Content-Length': Buffer.byteLength(post_data)
		}
	};

	// Set up the request
	if(config.app.ssl) {
		var post_req = https.request(post_options, function(res) {
			res.setEncoding('utf8');
			res.on('data', function (chunk) {});
		});
	} else {
		var post_req = http.request(post_options, function(res) {
			res.setEncoding('utf8');
			res.on('data', function (chunk) {});
		});
	}

	// post the data
	post_req.write(post_data);
	post_req.end();
}

function handleDisconnect() {
  	connection = mysql.createConnection(config.db);
  	connection.connect(function(err) {
		if(err) {
			setTimeout(handleDisconnect, 2000);
		}
  	});

	connection.on('error', function(err) {
		if(err.code === 'PROTOCOL_CONNECTION_LOST') {
			handleDisconnect();
    	}
	});
}

function escapeHtml(text) {
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

function addslashes(str) {
	if(str == undefined)
		return false;
	return str.replace('/(["\'\])/g', "\\$1").replace('/\0/g', "\\0");
}

var HtmlEntities = function() {};

function stringEscape(s) {
	return s ? s.replace(/\\/g,'\\\\').replace(/\n/g,'\\n').replace(/\t/g,'\\t').replace(/\v/g,'\\v').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/[\x00-\x1F\x80-\x9F]/g,hex) : s;
	function hex(c) { var v = '0'+c.charCodeAt(0).toString(16); return '\\x'+v.substr(v.length-2); }
}

HtmlEntities.map = {
	"'": "&apos;", "<": "&lt;", ">": "&gt;", " ": "&nbsp;", "¡": "&iexcl;", "¢": "&cent;", "£": "&pound;", "¤": "&curren;", "¥": "&yen;", "¦": "&brvbar;", "§": "&sect;", "¨": "&uml;", "©": "&copy;", "ª": "&ordf;", "«": "&laquo;", "¬": "&not;", "®": "&reg;", "¯": "&macr;", "°": "&deg;", "±": "&plusmn;", "²": "&sup2;", "³": "&sup3;", "´": "&acute;", "µ": "&micro;", "¶": "&para;", "·": "&middot;", "¸": "&cedil;", "¹": "&sup1;", "º": "&ordm;", "»": "&raquo;", "¼": "&frac14;", "½": "&frac12;", "¾": "&frac34;", "¿": "&iquest;", "À": "&Agrave;", "Á": "&Aacute;", "Â": "&Acirc;", "Ã": "&Atilde;", "Ä": "&Auml;", "Å": "&Aring;", "Æ": "&AElig;", "Ç": "&Ccedil;", "È": "&Egrave;", "É": "&Eacute;", "Ê": "&Ecirc;", "Ë": "&Euml;", "Ì": "&Igrave;", "Í": "&Iacute;", "Î": "&Icirc;", "Ï": "&Iuml;", "Ð": "&ETH;", "Ñ": "&Ntilde;", "Ò": "&Ograve;", "Ó": "&Oacute;", "Ô": "&Ocirc;", "Õ": "&Otilde;", "Ö": "&Ouml;", "×": "&times;", "Ø": "&Oslash;", "Ù": "&Ugrave;", "Ú": "&Uacute;", "Û": "&Ucirc;", "Ü": "&Uuml;", "Ý": "&Yacute;", "Þ": "&THORN;", "ß": "&szlig;", "à": "&agrave;", "á": "&aacute;", "â": "&acirc;", "ã": "&atilde;", "ä": "&auml;", "å": "&aring;", "æ": "&aelig;", "ç": "&ccedil;", "è": "&egrave;", "é": "&eacute;", "ê": "&ecirc;", "ë": "&euml;", "ì": "&igrave;", "í": "&iacute;", "î": "&icirc;", "ï": "&iuml;", "ð": "&eth;", "ñ": "&ntilde;", "ò": "&ograve;", "ó": "&oacute;", "ô": "&ocirc;", "õ": "&otilde;", "ö": "&ouml;", "÷": "&divide;", "ø": "&oslash;", "ù": "&ugrave;", "ú": "&uacute;", "û": "&ucirc;", "ü": "&uuml;", "ý": "&yacute;", "þ": "&thorn;", "ÿ": "&yuml;", "Œ": "&OElig;", "œ": "&oelig;", "Š": "&Scaron;", "š": "&scaron;", "Ÿ": "&Yuml;", "ƒ": "&fnof;", "ˆ": "&circ;", "˜": "&tilde;", "Α": "&Alpha;", "Β": "&Beta;", "Γ": "&Gamma;", "Δ": "&Delta;", "Ε": "&Epsilon;", "Ζ": "&Zeta;", "Η": "&Eta;", "Θ": "&Theta;", "Ι": "&Iota;", "Κ": "&Kappa;", "Λ": "&Lambda;", "Μ": "&Mu;", "Ν": "&Nu;", "Ξ": "&Xi;", "Ο": "&Omicron;", "Π": "&Pi;", "Ρ": "&Rho;", "Σ": "&Sigma;", "Τ": "&Tau;", "Υ": "&Upsilon;", "Φ": "&Phi;", "Χ": "&Chi;", "Ψ": "&Psi;", "Ω": "&Omega;", "α": "&alpha;", "β": "&beta;", "γ": "&gamma;", "δ": "&delta;", "ε": "&epsilon;", "ζ": "&zeta;", "η": "&eta;", "θ": "&theta;", "ι": "&iota;", "κ": "&kappa;", "λ": "&lambda;", "μ": "&mu;", "ν": "&nu;", "ξ": "&xi;", "ο": "&omicron;", "π": "&pi;", "ρ": "&rho;", "ς": "&sigmaf;", "σ": "&sigma;", "τ": "&tau;", "υ": "&upsilon;", "φ": "&phi;", "χ": "&chi;", "ψ": "&psi;", "ω": "&omega;", "ϑ": "&thetasym;", "ϒ": "&Upsih;", "ϖ": "&piv;", "–": "&ndash;", "—": "&mdash;", "‘": "&lsquo;", "’": "&rsquo;", "‚": "&sbquo;", "“": "&ldquo;", "”": "&rdquo;", "„": "&bdquo;", "†": "&dagger;", "‡": "&Dagger;", "•": "&bull;", "…": "&hellip;", "‰": "&permil;", "′": "&prime;", "″": "&Prime;", "‹": "&lsaquo;", "›": "&rsaquo;", "‾": "&oline;", "⁄": "&frasl;", "€": "&euro;", "ℑ": "&image;", "℘": "&weierp;", "ℜ": "&real;", "™": "&trade;", "ℵ": "&alefsym;", "←": "&larr;", "↑": "&uarr;", "→": "&rarr;", "↓": "&darr;", "↔": "&harr;", "↵": "&crarr;", "⇐": "&lArr;", "⇑": "&UArr;", "⇒": "&rArr;", "⇓": "&dArr;", "⇔": "&hArr;", "∀": "&forall;", "∂": "&part;", "∃": "&exist;", "∅": "&empty;", "∇": "&nabla;", "∈": "&isin;", "∉": "&notin;", "∋": "&ni;", "∏": "&prod;", "∑": "&sum;", "−": "&minus;", "∗": "&lowast;", "√": "&radic;", "∝": "&prop;", "∞": "&infin;", "∠": "&ang;", "∧": "&and;", "∨": "&or;", "∩": "&cap;", "∪": "&cup;", "∫": "&int;", "∴": "&there4;", "∼": "&sim;", "≅": "&cong;", "≈": "&asymp;", "≠": "&ne;", "≡": "&equiv;", "≤": "&le;", "≥": "&ge;", "⊂": "&sub;", "⊃": "&sup;", "⊄": "&nsub;", "⊆": "&sube;", "⊇": "&supe;", "⊕": "&oplus;", "⊗": "&otimes;", "⊥": "&perp;", "⋅": "&sdot;", "⌈": "&lceil;", "⌉": "&rceil;", "⌊": "&lfloor;", "⌋": "&rfloor;", "⟨": "&lang;", "⟩": "&rang;", "◊": "&loz;", "♠": "&spades;", "♣": "&clubs;", "♥": "&hearts;", "♦": "&diams;"
};

HtmlEntities.decode = function(string) {
	var entityMap = HtmlEntities.map;
	for (var key in entityMap) {
		var entity = entityMap[key];
		var regex = new RegExp(entity, 'g');
		string = string.replace(regex, key);
	}
	string = string.replace(/&quot;/g, '"');
	string = string.replace(/&amp;/g, '&');
	return string;
}

HtmlEntities.encode = function(string) {
	var entityMap = HtmlEntities.map;
	string = string.replace(/&/g, '&amp;');
	string = string.replace(/"/g, '&quot;');
	for (var key in entityMap) {
		var entity = entityMap[key];
		var regex = new RegExp(key, 'g');
		string = string.replace(regex, entity);
	}
	return string;
}

function md5(s){function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,d,F,H,x;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);d=(k&1073741824);x=(G&1073741823)+(k&1073741823);if(I&d){return(x^2147483648^F^H)}if(I|d){if(x&1073741824){return(x^3221225472^F^H)}else{return(x^1073741824^F^H)}}else{return(x^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]| (G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]|(128<<d);aa[I-2]=F<<3;aa[I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k=k+F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/rn/g,"n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=D(Y,X,W,V,C[P+9],o,3654602809);V=D(V,Y,X,W,C[P+12],m,3873151461);W=D(W,V,Y,X,C[P+15],l,530742520);X=D(X,W,V,Y,C[P+2],j,3299628645);Y=t(Y,X,W,V,C[P+0],U,4096336452);V=t(V,Y,X,W,C[P+7],T,1126891415);W=t(W,V,Y,X,C[P+14],R,2878612391);X=t(X,W,V,Y,C[P+5],O,4237533241);Y=t(Y,X,W,V,C[P+12],U,1700485571);V=t(V,Y,X,W,C[P+3],T,2399980690);W=t(W,V,Y,X,C[P+10],R,4293915773);X=t(X,W,V,Y,C[P+1],O,2240044497);Y=t(Y,X,W,V,C[P+8],U,1873313359);V=t(V,Y,X,W,C[P+15],T,4264355552);W=t(W,V,Y,X,C[P+6],R,2734768916);X=t(X,W,V,Y,C[P+13],O,1309151649);Y=t(Y,X,W,V,C[P+4],U,4149444226);V=t(V,Y,X,W,C[P+11],T,3174756917);W=t(W,V,Y,X,C[P+2],R,718787259);X=t(X,W,V,Y,C[P+9],O,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}var i=B(Y)+B(X)+B(W)+B(V);return i.toLowerCase()};

function formatPhoneNumber(str) {
	let cleaned = ('' + str).replace(/\D/g, '');
	let match = cleaned.match(/(\d{3})(\d{3})(\d{4})$/);
	if (match) {
		return '(' + match[1] + ') ' + match[2] + '-' + match[3]
	};
	return null
};

function date (format, timestamp) {
	var a, jsdate = new Date(timestamp ? timestamp * 1000 : null);
	var pad = function(n, c){
		if( (n = n + "").length < c ) {
			return new Array(++c - n.length).join("0") + n;
		} else {
			return n;
		}
	};
	var txt_weekdays = ["Sunday","Monday","Tuesday","Wednesday",
		"Thursday","Friday","Saturday"];
	var txt_ordin = {1:"st",2:"nd",3:"rd",21:"st",22:"nd",23:"rd",31:"st"};
	var txt_months =  ["", "January", "February", "March", "April",
		"May", "June", "July", "August", "September", "October", "November",
		"December"];

	var f = {
			d: function(){
				return pad(f.j(), 2);
			},
			D: function(){
				t = f.l(); return t.substr(0,3);
			},
			j: function(){
				return jsdate.getDate();
			},
			l: function(){
				return txt_weekdays[f.w()];
			},
			N: function(){
				return f.w() + 1;
			},
			S: function(){
				return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th';
			},
			w: function(){
				return jsdate.getDay();
			},
			z: function(){
				return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0;
			},
			W: function(){
				var a = f.z(), b = 364 + f.L() - a;
				var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1;

				if(b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b){
					return 1;
				} else{

					if(a <= 2 && nd >= 4 && a >= (6 - nd)){
						nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31");
						return date("W", Math.round(nd2.getTime()/1000));
					} else{
						return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0);
					}
				}
			},
			F: function(){
				return txt_months[f.n()];
			},
			m: function(){
				return pad(f.n(), 2);
			},
			M: function(){
				t = f.F(); return t.substr(0,3);
			},
			n: function(){
				return jsdate.getMonth() + 1;
			},
			t: function(){
				var n;
				if( (n = jsdate.getMonth() + 1) == 2 ){
					return 28 + f.L();
				} else{
					if( n & 1 && n < 8 || !(n & 1) && n > 7 ){
						return 31;
					} else{
						return 30;
					}
				}
			},
			L: function(){
				var y = f.Y();
				return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0;
			},
			Y: function(){
				return jsdate.getFullYear();
			},
			y: function(){
				return (jsdate.getFullYear() + "").slice(2);
			},
			a: function(){
				return jsdate.getHours() > 11 ? "pm" : "am";
			},
			A: function(){
				return f.a().toUpperCase();
			},
			B: function(){
				var off = (jsdate.getTimezoneOffset() + 60)*60;
				var theSeconds = (jsdate.getHours() * 3600) +
								 (jsdate.getMinutes() * 60) +
								  jsdate.getSeconds() + off;
				var beat = Math.floor(theSeconds/86.4);
				if (beat > 1000) beat -= 1000;
				if (beat < 0) beat += 1000;
				if ((String(beat)).length == 1) beat = "00"+beat;
				if ((String(beat)).length == 2) beat = "0"+beat;
				return beat;
			},
			g: function(){
				return jsdate.getHours() % 12 || 12;
			},
			G: function(){
				return jsdate.getHours();
			},
			h: function(){
				return pad(f.g(), 2);
			},
			H: function(){
				return pad(jsdate.getHours(), 2);
			},
			i: function(){
				return pad(jsdate.getMinutes(), 2);
			},
			s: function(){
				return pad(jsdate.getSeconds(), 2);
			},
			O: function(){
			   var t = pad(Math.abs(jsdate.getTimezoneOffset()/60*100), 4);
			   if (jsdate.getTimezoneOffset() > 0) t = "-" + t; else t = "+" + t;
			   return t;
			},
			P: function(){
				var O = f.O();
				return (O.substr(0, 3) + ":" + O.substr(3, 2));
			},
			c: function(){
				return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P();
			},
			U: function(){
				return Math.round(jsdate.getTime()/1000);
			}
	};

	return format.replace(/[\\]?([a-zA-Z])/g, function(t, s){
		if( t!=s ){
			ret = s;
		} else if( f[s] ){
			ret = f[s]();
		} else{
			ret = s;
		}
		return ret;
	});
}

handleDisconnect();
