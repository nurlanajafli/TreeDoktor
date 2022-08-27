chatBaseUrl = baseUrl; //'https://treedoctors.arbostar.com/';
var slideIndex = 1;
var chatGallery = {}; // keep toUserId: [<gallery>]
if(baseUrl != 'https://crmold.loc/' && baseUrl != 'http://crmold.loc/' && baseUrl != 'https://crm.loc/' && baseUrl != 'http://crm.loc/')
	var wsaddress = 'wss://' + chatBaseUrl.replace(/^https:\/\//i, '').replace(/^http:\/\//i, '').replace(/\/+$/, '') + ':'+ EXTERNAL_WS_PORT +'/?chat=1&user_id=' + user_id;
else
	var wsaddress = 'wss://demo.arbostar.com:8895';
ws = io.connect(wsaddress, {secure: true});

ws.on('message', function(data){

	var method = data.payload.method + 'Callback';

	if(typeof data.result == 'object' || typeof data.result == 'boolean')
		eval('if(typeof(callback.' + method + ') == "function") callback.' + method + '(data.result)');
	else
		eval('if(typeof(callback.' + method + ') == "function") callback.' + method + "('" + data.result + "')");
});

ws.on('connect', function (socket) {
	ws.emit('room', 'chat-' + user_id);
	ws.emit('room', 'chat');
	if(isSupport != "0") {
		ws.emit('room', 'sms_support');
	}
	ws.emit('room', 'sms');
	ws.send({method:'getChatOnline'});
	ws.send({method:'getUnread'});
});

var callback = {
	sendMessageCallback: function(data){
		var chatWith = data.user_id;
		var message = data.message.message;

		// get if there is `Today` before send today's messages
		let todayTime = $("#chatbox_"+chatWith).find(".chat-date:contains('Today')");

		// if started today's messages check is there today date
		if (!todayTime.length) {
			addTimeToChatBox(chatWith);
		}

		if ($("#chatbox_"+chatWith).length <= 0) {
			$('#chat-with-' + chatWith).click();
		} else {
			if ($("#chatbox_"+chatWith).css('display') == 'none') {
				$("#chatbox_"+chatWith).css('display','block');
				restructureChatBoxes();
			}

			var item = data.message;
			item.sent = item.sent.replace(/-/g, '/');
			var messageBlock = document.createElement('div');

			if (item.to == user_id) {
				$(messageBlock).append(from_message_tpl);
			} else {
				$(messageBlock).append(to_message_tpl);
			}

			if (item.type !== 'text') {
				let name = message.split('/');
				name = name[name.length - 1];

				let encodedName = encodeURIComponent(name);
				let encodedUrl = (item.message).replace(name, encodedName);

				chatGallery[chatWith].push(item.message);

				if (item.type === 'image') {
					$('#chatGallery_'+chatWith).append(`<div class="attachmentSlides"><img src="${encodedUrl}"></div>`);
				}
				message = attachment_tpl(encodedUrl, name, item.type);
			} else {
				message = Common.text_decorate(message);
			}

			$(messageBlock).find('.message').html(message);

			var messageTime = getMessageTimeWithFormat(item.sent);
			$(messageBlock).find('.message-time').text(messageTime);

			$("#chatbox_"+chatWith+" .messages-wrapper").append($(messageBlock).html());
			$("#chatbox_"+chatWith+" .messages-wrapper").scrollTop($("#chatbox_"+chatWith+" .messages-wrapper")[0].scrollHeight);
		}

		if (data.message.to == user_id) {
			blinkTitle(document_title, "***NEW MESSAGE***", 1000, true);
		}
	},

	chatHistoryCallback:function(data){
		slideIndex = 1;
		var chatWithId = data.user_id, date;
		// for each chat block init `to` messages
		chatGallery[chatWithId] = [];
		var dateCounter = '';
		$.each(data.rows, function(i, item){
			item.sent = item.sent.replace(/-/g, '/');
			var ymd = item.sent.split(' ')[0];
			if(ymd != dateCounter) {
				dateCounter = ymd;

				if(new Date(item.sent).setHours(0,0,0,0) == new Date().setHours(0,0,0,0)) 
					date = 'Today';
				else {
					date = weekdays[new Date(item.sent).getDay()] + ', ' + new Date(item.sent).getDate() + ' ' + months[new Date(item.sent).getMonth()];
					if(new Date(item.sent).getFullYear() != new Date().getFullYear())
						date += ' ' + new Date(item.sent).getFullYear();
				}
				addTimeToChatBox(chatWithId, date);
			}

			var messageBlock = document.createElement('div');

			if(item.to == user_id) {
				$(messageBlock).append(from_message_tpl);
			} else {
				$(messageBlock).append(to_message_tpl);
			}

			if (item.type !== 'text'){
				let name = item.message.split('/');
				name = name[name.length - 1];
				let msg = attachment_tpl(item.message, name, item.type);
				$(messageBlock).find('.message').html(msg);

				let encodedName = encodeURIComponent(name);
				let encodedUrl = (item.message).replace(name, encodedName);

				//add images to gallery
				if (item.type === 'image') {
					chatGallery[chatWithId].push(encodedUrl);
				}
			} else {
				//item.message = urlify(item.message);
				$(messageBlock).find('.message').html(Common.text_decorate(item.message));
			}

			var messageTime = getMessageTimeWithFormat(item.sent);

			$(messageBlock).find('.message-time').text(messageTime);

			$("#chatbox_"+chatWithId+" .messages-wrapper").append($(messageBlock).html());
			$("#chatbox_"+chatWithId+" .messages-wrapper").scrollTop($("#chatbox_"+chatWithId+" .messages-wrapper")[0].scrollHeight);
		});

		//append chat gallery
		// $('.chat-gallery-tpl').remove();
		$('.chat-gallery-tpl').css('display', 'none');
		if (!$('#chatGallery_' + chatWithId).length) {
			$('body').append(chatGallery_tpl(chatGallery[chatWithId], chatWithId));
		} else {
			$('#chatGallery_' + chatWithId).css('display', 'block');
		}

	},

	getChatOnlineCallback:function(data){
		$.each(data, function(i,user){
			$('.userStatus[data-user_id="' + i + '"]').removeClass('text-danger').addClass('text-success');
            $('.userStatus[data-user_id="' + i + '"]').parent().attr('title', 'Online');
		});
	},

	toOfflineCallback:function(user_id){
		$('.userStatus[data-user_id="' + user_id + '"]').removeClass('text-success').addClass('text-danger');
        $('.userStatus[data-user_id="' + user_id + '"]').parent().attr('title', 'Offline');
	},

	toOnlineCallback:function(user_id){
		$('.userStatus[data-user_id="' + user_id + '"]').removeClass('text-danger').addClass('text-success');
        $('.userStatus[data-user_id="' + user_id + '"]').parent().attr('title', 'Online');
	},

	getUnreadCallback:function(data){
		$('.countUnread').text(data.rows.length?data.rows.length:'');
		$('.unreadIcon').parent().css('font-weight', 'normal');
		$('.unreadIcon').remove();
		blinkTitleStop();

		$.each(data.rows, function(key, val) {
			$('#chat-with-' + val.from).css('font-weight', 'bold');
			$('#chat-with-' + val.from).append('<i class="fa fa-envelope-o pull-right unreadIcon pos-abt" style="font-size: 8px;left: 0; top: 10px;"></i>');
		});
		if(data.rows.length)
			blinkTitle(document_title, "***NEW MESSAGE***", 1000, true);
	}, 

	closeChatCallback:function(data){
		closeChatBox(data);
	},

	toggleChatCallback:function(data){
		toggleChatBoxGrowth(data);
	},

	createChatCallback:function(data){
		createChatBox(data, $.trim($('#chat-with-' + data).text()));
	},


	openMessengerCallback:function(data) {

	},

};


var sendingChat = false;
var windowFocus = true;
var blinkedTitle = false;
var username;
var from_username;
var chatHeartbeatCount = 0;
var minChatHeartbeat = 2000;
var maxChatHeartbeat = 33000;
var chatHeartbeatTime = minChatHeartbeat;
var originalTitle;
var blinkOrder = 0;
var hold = "";
var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
var weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

var chatboxFocus = new Array();
var newMessages = new Array();
var newMessagesWin = new Array();
var chatBoxes = new Array();
var chat_with = false;
var document_title = $(document).find('title').text();

var chatbox_tpl = '<div class="chat body col-lg-3 col-md-4 col-sm-6 col-xs-12 p-n"><header><h2 class="username"></h2><a href="#" class="pull-right text-muted pos-abt chat-close"><i class="fa fa-times"></i></a><a href="#" class="pull-right text-muted pos-abt chat-minimize"><i class="fa fa-minus"></i></a></header><div class="messages-wrapper"></div></div>';
var textarea_tpl = '<div class="chat-footer p-bottom-10 p-top-10 b-b b-l b-r"><div class="pull-left col-md-2"><a href="#" class="btn btn-default btn-rounded open-chat-attachment"><i class="fa fa-paperclip"></i></button></div><div class="pull-left col-md-8 col-sm-8 col-xs-8"><textarea class="form-control rounded message-field" rows="1"></textarea></div><div class="pull-left col-md-2 col-sm-2 send-chat-block"><a href="#" class="btn btn-info btn-rounded btn-send-chat pos-abt"><i class="fa fa-arrow-up"></i></a></div><div class="clear"></div></div>';
var from_message_tpl = '<div class="message-row"><div class="message-block"><div class="message from"></div></div><div class="message-time from"></div></div>';
var to_message_tpl = '<div class="message-row"><div class="message-time to"></div><div class="message-block"><div class="message to"></div></div></div>';
var chatdate_tpl = '<div class="chat-date"></div>';
var attachment_tpl = function(url, name, type = 'image') {
	let encodedName = encodeURIComponent(name);
	let encodedUrl = url.replace(name, encodedName);
	console.log(encodedUrl, url, name)
	if (type === 'image') {
		return `<img class='chat-image-item' src='${encodedUrl}' title='${name}' width="120" height="85">`;
	} else {
		let typesWithIcons = /((xlsx|xls|xlsm)|(pdf)|(docx?))$/ig;
		let attach = 'attach';

		// by default set attachment  icon
		if (typesWithIcons.exec(type)) {
			attach = 'chat-icons/'+type;
		}

		return `<a href="${encodedUrl}" target="_blank" class="attachment" style="color: inherit;" ${type !== 'pdf' ? 'download' : ''}><span><img src="/assets/img/${attach}.png" width="30" style="margin-right: 10px;"></span>${name}</a>`;
	}
}
var attachment_modal_tpl = (url) => `
	<div id="attachment-modal" class="modal-chat">
		<span class="close">&times;</span>
		<img class="modal-content" id="img01attachment-img">
	</div>
`

/***** START CHAT GALLERY SLIDE *****/
var chatGalleryLoader_tpl = '<div class="chat-gallery-loader" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #00000090; display: flex;    align-items: center; justify-content: center;"><img src="/assets/img/loading.gif"></div>';
var chatGallery_tpl = (images, user_id) => `<div class="container chat-gallery-tpl" id="chatGallery_${user_id}">
		<span class="close">&times;</span>
		${images.map(item => `<div class="attachmentSlides">
				<img src="${item}">
			</div>`).join('')}

		<!-- Next and previous buttons -->
		<a class="prev" onclick="plusSlides(-1, ${user_id})">&#10094;</a>
		<a class="next" onclick="plusSlides(1, ${user_id})">&#10095;</a>
	</div>`;

var urlify = function (text) {
	var urlRegex = /(https?:\/\/[^\s]+)/g;
	return text.replace(urlRegex, '<a href="$1" target="_blank" style="color: inherit;">$1</a>');
}
// setup slide
function plusSlides(n, chatWith) {
	showSlides(slideIndex += n, chatWith);
}

function showSlides(n, chatWith) {
	if (!$('#chatGallery_' + chatWith).length)  {
		$('body').append(chatGallery_tpl(chatGallery[chatWith], chatWith));
	} else {
		$('.chat-gallery-tpl').css('display', 'none');
	}

	var slides = $('#chatGallery_' + chatWith).find(".attachmentSlides");

	if (slides.length) {
		$('#chatGallery_' + chatWith).css('display', 'block');

		if (n >= slides.length) {slideIndex = 1}
		else if (n < 0) {slideIndex = slides.length - 1}
		else {slideIndex = n}

		//last elem
		if(n == slides.length) {slideIndex = 0}
		for (let i = 0; i < slides.length; i++) {
			slides[i].style.display = "none";
		}

		slides[slideIndex].style.display = "flex";
	}
}
/***** END CHAT GALLERY SLIDE *****/
$(document).ready(function(){
	originalTitle = document.title;

	$([window, document]).blur(function(){
		windowFocus = false;
	}).focus(function(){
		windowFocus = true;
		document.title = originalTitle;
	});

	restoreBoxes = $.parseJSON($.cookie('chatboxes'));

	if(restoreBoxes) {
		$.each(restoreBoxes, function(key, val){
			if($('#chat-with-' + val).length)
				$('#chat-with-' + val).click();
		});	
	}

	$(document).on('keydown', '.message-field', function(e) {
		checkChatBoxInputKey(e, $(this), $(this).parents('.chat.body').data('id'));
	});
	$(document).on('blur', '.message-field', function(e) {
		//$(this).parents('.chat.body').find(".messages-wrapper").css('height','auto');
	});
	$(document).on('click', '.btn-send-chat', function(e) {
		checkChatBoxInputKey(e, $(this).parents('.chat.body').find('.message-field'), $(this).parents('.chat.body').data('id'));
	});
	$(document).on('click', '.chat-close', function(e) {
		ws.send({method:'closeChat', params:{'chatbox':$(this).parents('.chat.body').data('id')}});
	});
	$(document).on('click', '.chat-minimize', function(e) {
		ws.send({method:'toggleChat', params:{'chatbox':$(this).parents('.chat.body').data('id')}});
	});
	$(document).on('click', '.createChat', function(e) {
		ws.send({method:'createChat', params:{'chatbox':$(this).data('user-id')}});
	});

});

function closeChatBox(chatboxtitle) {
	$('#chatbox_'+chatboxtitle).css('display','none');
	delete chatBoxes[chatBoxes.indexOf(chatboxtitle.toString())];
	$.cookie('chatboxes', JSON.stringify(chatBoxes), {path:'/', expires:365});
	restructureChatBoxes();
}

function createChatBox(chatboxtitle, chatuserName, minimizeChatBox) {
	if ($("#chatbox_"+chatboxtitle).length > 0) {
		if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
			$("#chatbox_"+chatboxtitle).css('display','block');
			restructureChatBoxes();
		}
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
		return;
	}

	var chatboxDiv = document.createElement('div');
	$(chatboxDiv).append(chatbox_tpl);
	$(chatboxDiv).find('.chat.body').attr("id", "chatbox_"+chatboxtitle).attr('data-id', chatboxtitle).append(textarea_tpl);
	$(chatboxDiv).find('.username').text(getTitle(chatuserName));
	
	$("body").append($(chatboxDiv).html());

	autosize(document.getElementsByClassName('message-field'));
	$('.message-field').on('autosize:resized', function(){
		//console.log('textarea height updated');
	});
	
	chatBoxeslength = 0;

	for (x in chatBoxes) {
		if(isNaN(parseInt(x)))
			continue;
		if ($("#chatbox_"+chatBoxes[x]).css('display') != 'none') {
			chatBoxeslength++;
		}
	}

	/*if (chatBoxeslength == 0) {
		$("#chatbox_"+chatboxtitle).css('right', '20px');
	} else {
		width = (chatBoxeslength)*(400+7)+20;
		$("#chatbox_"+chatboxtitle).css('right', width+'px');
	}*/
	
	chatBoxes.push(chatboxtitle);

	minimizeChatBox = 0;
	if($.cookie('chatbox_minimized'))
		minimizeChatBox = $.cookie('chatbox_minimized').split(/\|/).indexOf(chatboxtitle.toString()) + 1;

	if (minimizeChatBox) {
		minimizedChatBoxes = new Array();

		if ($.cookie('chatbox_minimized')) {
			minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		}
		minimize = 0;
		for (j=0;j<minimizedChatBoxes.length;j++) {
			if (minimizedChatBoxes[j] == chatboxtitle) {
				minimize = 1;
			}
		}

		if (minimize == 1) {
			$('#chatbox_'+chatboxtitle+' .messages-wrapper').css('display','none');
			$('#chatbox_'+chatboxtitle+' .chat-footer').css('display','none');
		}
	}

	chatboxFocus[chatboxtitle] = false;

	$("#chatbox_"+chatboxtitle).click(function() {
		if ($('#chatbox_'+chatboxtitle+' .messages-wrapper').css('display') != 'none') {
			ws.send({method:'readChat', params:{'user_id':chatboxtitle}});
		}
	});


	ws.send({method:'chatHistory', params:{'user_id':chatboxtitle}});
	$("#chatbox_"+chatboxtitle).show();
	itemsfound = 0;

	$.cookie('chatboxes', JSON.stringify(chatBoxes), {path:'/', expires:365});
}

function checkChatBoxInputKey(event, chatboxtextarea, chatboxtitle) {
	message = $(chatboxtextarea).val();
	message = message.replace(/^\s+|\s+$/g,"");

	if((event.keyCode == 13 && !event.ctrlKey && !event.shiftKey) || event.type == 'click')  {
		sendingChat = true;
		$(chatboxtextarea).val('');
		//$(chatboxtextarea).find('.message-field')[0].value = "";
		autosize.destroy(document.getElementsByClassName('message-field'));
		//autosize.update($(chatboxtextarea).find('.message-field')[0]);
		$(chatboxtextarea).focus();
		$(chatboxtextarea).css('height','34px');
		
		if (message != '') {
			ws.send({method:'sendMessage', params:{'user_id':chatboxtitle, 'message':message}});
		}
		chatHeartbeatTime = minChatHeartbeat;
		chatHeartbeatCount = 1;

		//autosize($(chatboxtextarea).find('.message-field')[0]);
		event.preventDefault();
		autosize(document.getElementsByClassName('message-field'));
		return false;
	} 
	/*if(event.keyCode == 13 && (event.ctrlKey || event.shiftKey)) {
		//$(chatboxtextarea).find('.message-field')[0] += "\n";
		autosize.update($(chatboxtextarea).find('.message-field')[0]);
		return false;
	}*/

	var adjustedHeight = chatboxtextarea.clientHeight;
	var maxHeight = 94;

	if (maxHeight > adjustedHeight) {
		adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
		if (maxHeight)
			adjustedHeight = Math.min(maxHeight, adjustedHeight);
		if (adjustedHeight > chatboxtextarea.clientHeight)
			$(chatboxtextarea).css('height',adjustedHeight+8 +'px');
	} else {
		$(chatboxtextarea).css('overflow','auto');
	}
	 
}



function restructureChatBoxes() {
	align = 0;
	for (x in chatBoxes) {

		if(isNaN(parseInt(x)))
			continue;

		chatboxtitle = chatBoxes[x];

		if ($("#chatbox_"+chatboxtitle).css('display') != 'none') {
			if (align == 0) {
				$("#chatbox_"+chatboxtitle).css('right', '20px');
			} else {
				width = (align)*(400+7)+20;
				$("#chatbox_"+chatboxtitle).css('right', width+'px');
			}
			align++;
		}
	}
	var newCookie = '';
	$.each($('.chat.body'), function(key, val){
		if($(val).find('.messages-wrapper').is(':hidden') && $(val).is(':visible'))
			newCookie += $(val).data('id') + '|';
	});
	newCookie = newCookie.substring(0, newCookie.length - 1);

	$.cookie('chatbox_minimized', newCookie, {path:'/', expires:365});
}

function getTitle( title ){
	try{
		return title.replace('-', ' ');

	}catch(ex){
		return title;		
	}
}

function chatWith(chatuser, chatuserName) {
	chat_with = true;
	createChatBox(chatuser, chatuserName); 
	$("#chatbox_"+chatuser).find(".message-field").focus();
	event.preventDefault();
	
	/*setTimeout(function(){
			
			$("#chatbox_"+chatuser).find(".messages-wrapper").css('height','50%');
			//console.log($("#chatbox_"+chatuser+' .messages-wrapper') );
			//alert($("#chatbox_"+chatuser+" .message-wrapper").style.height);
	}, 100);*/
	
}


function toggleChatBoxGrowth(chatboxtitle) {
	//ws.send({method:'toggleChat', params:{'chatbox':chatboxtitle}});
	if ($('#chatbox_'+chatboxtitle+' .messages-wrapper').is(':hidden')) {
		$('#chatbox_'+chatboxtitle+' .messages-wrapper').css('display','block');
		$('#chatbox_'+chatboxtitle+' .chat-footer').css('display','block');
		$("#chatbox_"+chatboxtitle+" .messages-wrapper").scrollTop($("#chatbox_"+chatboxtitle+" .messages-wrapper")[0].scrollHeight);
	} else {
		$('#chatbox_'+chatboxtitle+' .messages-wrapper').css('display','none');
		$('#chatbox_'+chatboxtitle+' .chat-footer').css('display','none');
	}
	var newCookie = '';
	$.each($('.chat.body'), function(key, val){
		if($(val).find('.messages-wrapper').is(':hidden'))
			newCookie += $(val).data('id') + '|';
	});
	newCookie = newCookie.substring(0, newCookie.length - 1);

	$.cookie('chatbox_minimized', newCookie, {path:'/', expires:365});
}

function blinkTitle(msg1, msg2, delay, isFocus, timeout) {
	if(blinkedTitle)
		return false;

	blinkedTitle = true;
    if(hold)
        return false;
    if (isFocus == null) {
        isFocus = false;
    }
    if (timeout == null) {
        timeout = false;
    }
    if(timeout){
        setTimeout(blinkTitleStop, timeout);
    }
    document.title = msg1;
    
    hold = window.setInterval(function() {
        if (document.title == msg1) {
            document.title = msg2;
        } else {
            document.title = msg1;
        }
    }, delay);
}

function blinkTitleStop() {
	blinkedTitle = false;
	$(document).find('title').text(document_title);
    clearInterval(hold);
    hold = "";
}

$(document).on('click', '.open-chat-attachment', function(e) {
	$('#send-chat-attachment').remove();
	let parentId = $(this).parents('.chat.body').data('id');
	let input = $(`<input type="file" id="send-chat-attachment" name="chatAttachment" class="hidden" data-id="${parentId}" multiple/>`);

	//append the created file input
	$(this).parents('.chat.body').append(input);

	$('#send-chat-attachment').trigger('click');
});

// send attachment
$(document).on('change', '#send-chat-attachment', function(e) {
	// allow only following types VALIDATION
	let attachments = e.originalEvent.target.files;
	var formData = new FormData();

	$.each(attachments, function(i, attachment) {
			formData.append('chatAttachment[]', attachment);
	});


	let to = $(this).data('id');
	$('.chat-gallery-loader').remove();
	$(`#chatbox_${to}`).append(chatGalleryLoader_tpl)
	formData.append('to', to);

	$.ajax({
		type: 'post',
		url: baseUrl + 'chat/attachment',
		data: formData,
		mimeType: "multipart/form-data",
		contentType: false,
		dataType: 'json',
		cache: false,
		global:false,
		processData: false,
		success: function (data) {
			$('.chat-gallery-loader').remove();
			try {
				data = JSON.parse(data);
			} catch (e) {}

			if (data.status) {
				(data.data).forEach(msg => {
					ws.send({method:'sendMessage', params:{user_id: msg.to, message: msg.message, type: msg.type}});
				})
				$('#send-chat-attachment').remove();
			}
			else {
				alert(data.message.replace(/<[^>]*>?/gm, ''));
			}
		},
		error: function (error, exception) {
			$('.chat-gallery-loader').remove();
			let errorText = $(error.responseText).find('h1');
			if (errorText.length) {
				errorText = errorText.text();
				alert(errorText.replace(/<[^>]*>?/gm, ''));
			} else {
				alert(error.responseText.replace(/<[^>]*>?/gm, ''));
			}
		}
	})
});

$(document).on('click', '.chat-image-item', function(e) {
	let imageSrc = $(this).attr('src');

	let chatWith = +$(this).parents('.chat.body').data('id')
	// let chatWith = +$('[id^="chatbox_"]:visible').attr('id').replace(/\D/ig, '')
	let chatGalleryItemIndex = chatGallery[chatWith].indexOf(imageSrc);
	showSlides(chatGalleryItemIndex, chatWith);
});

$(document).on('click', '.close', function(e) {
	$('.chat-gallery-tpl').css('display', 'none');
})

// add TimeBlock into chatBox
function addTimeToChatBox (chatWithId, time = 'Today') {
	var dateBlock = document.createElement('div');
	$(dateBlock).append(chatdate_tpl);
	$(dateBlock).find('.chat-date').text(time);
	$("#chatbox_"+chatWithId+" .messages-wrapper").append($(dateBlock).html());
}

function getMessageTimeWithFormat(time)
{
	var messageTime = '';
	if (timeFormat == 12) {
		messageTime = new Date(time).toLocaleTimeString().
			replace(/([\d]+:[\d]{2})(:[\d]{2})(.*)/, "$1$3");
		if (messageTime[1] == ':') {
			messageTime = '0' + messageTime;
		}
	} else {
		messageTime = new Date(time).getHours().toString().length == 1 ? '0' +
			new Date(time).getHours().toString() : new Date(
			time).getHours().toString();
		messageTime += ':';
		messageTime += new Date(time).getMinutes().toString().length == 1
			? '0' + new Date(time).getMinutes().toString()
			: new Date(time).getMinutes().toString();
	}

	return messageTime;
}
