/*

Copyright (c) 2009 Anant Garg (anantgarg.com | inscripts.com)

This script may be used for non-commercial purposes only. For any
commercial purposes, please contact the author at 
anant.garg@inscripts.com

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

*/

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

var chatboxFocus = new Array();
var newMessages = new Array();
var newMessagesWin = new Array();
var chatBoxes = new Array();
var chat_with = false;
var document_title = $(document).find('title').text();
$(document).ready(function(){
	originalTitle = document.title;
	startChatSession();

	$([window, document]).blur(function(){
		windowFocus = false;
	}).focus(function(){
		windowFocus = true;
		document.title = originalTitle;
	});
});

function restructureChatBoxes() {
	align = 0;
	for (x in chatBoxes) {
		chatboxtitle = chatBoxes[x];

		if ($("#chatbox_"+chatboxtitle).css('display') != 'none') {
			if (align == 0) {
				$("#chatbox_"+chatboxtitle).css('right', '20px');
			} else {
				width = (align)*(225+7)+20;
				$("#chatbox_"+chatboxtitle).css('right', width+'px');
			}
			align++;
		}
	}
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
	$("#chatbox_"+chatuser+" .chatboxtextarea").focus();

}

function createChatBox(chatboxtitle,chatuserName, minimizeChatBox) {
	if ($("#chatbox_"+chatboxtitle).length > 0) {
		if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
			$("#chatbox_"+chatboxtitle).css('display','block');
			restructureChatBoxes();
		}
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
		return;
	}

	$(" <div />" ).attr("id","chatbox_"+chatboxtitle)
	.addClass("chatbox")
	.html('<div class="chatboxhead"><div class="chatboxtitle">'+getTitle( chatuserName )+'</div><div class="chatboxoptions"><a href="javascript:void(0)" onclick="javascript:toggleChatBoxGrowth(\''+chatboxtitle+'\')">-</a> <a href="javascript:void(0)" onclick="javascript:closeChatBox(\''+chatboxtitle+'\')">X</a></div><br clear="all"/></div><div class="chatboxcontent"></div><div class="chatboxinput"><textarea autocompleate="off" spellcheck="false" autocorrect="off" autocapitalize="off" class="chatboxtextarea" onkeydown="javascript:return checkChatBoxInputKey(event,this,\''+chatboxtitle+'\',\''+chatuserName+'\');"></textarea></div>')
	.appendTo($( "body" ));
			   
	$("#chatbox_"+chatboxtitle).css('bottom', '0px');
	
	chatBoxeslength = 0;

	for (x in chatBoxes) {
		if ($("#chatbox_"+chatBoxes[x]).css('display') != 'none') {
			chatBoxeslength++;
		}
	}

	if (chatBoxeslength == 0) {
		$("#chatbox_"+chatboxtitle).css('right', '20px');
	} else {
		width = (chatBoxeslength)*(225+7)+20;
		$("#chatbox_"+chatboxtitle).css('right', width+'px');
	}
	
	chatBoxes.push(chatboxtitle);

	if (minimizeChatBox == 1) {
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
			$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
			$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
		}
	}

	chatboxFocus[chatboxtitle] = false;

	$("#chatbox_"+chatboxtitle+" .chatboxtextarea").blur(function(){
		chatboxFocus[chatboxtitle] = false;
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").removeClass('chatboxtextareaselected');
	}).focus(function(){
		$.ajax({
			url: baseUrl + "chat?action=readchat",
			global: false,
			cache: false,
			data: 'user_id='+chatboxtitle,
			dataType: "json",
			success: function(data) {
				if(data.unread == 0)
					blinkTitleStop();
			}
		});

		chatboxFocus[chatboxtitle] = true;
		newMessages[chatboxtitle] = false;
		
		/***not messages***/
		$('#chatbox_'+chatboxtitle+' .chatboxhead').removeClass('chatboxblink');
		$("#chatbox_"+chatboxtitle+" .chatboxtextarea").addClass('chatboxtextareaselected');
	});

	$("#chatbox_"+chatboxtitle).click(function() {
		if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') != 'none') {
			//$("#chatbox_"+chatboxtitle+" .chatboxtextarea").focus();
		}
	});

	$("#chatbox_"+chatboxtitle).show();
	itemsfound = 0;
	//call
	$.ajax({
	  url: baseUrl + "chat?action=chathistory",
	  global: false,
	  cache: false,
	  data: 'to='+chatboxtitle,
	  dataType: "json",
	  success: function(data) {
        if(data.url)
        {
            location.href = data.url;
            return false;
        }
		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug
				messid = item.id != undefined ? ' mess-'+item.id : '';
				if (item.s == 1) {
					item.f = from_username;
				}
				if (item.s == 2) {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxinfo">'+item.m+'</span></div>');
				} else {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxmessagefrom">'+ getTitle( item.fname )+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
				}

				$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
				itemsfound += 1;
			}
		});
	
	}}).fail(function(e,t){
		console.log(e,t);
	});

}


function chatHeartbeat(){
	if(sendingChat)
		return false;
	var itemsfound = 0;
	/*if (windowFocus == false) {
 
		var blinkNumber = 0;
		var titleChanged = 0;
		for (x in newMessagesWin) {
			if (newMessagesWin[x] == true) {
				++blinkNumber;
				if (blinkNumber >= blinkOrder) {
					//document.title = x+' says...';
					document.title = ' New message...';
					titleChanged = 1;
					break;	
				}
			}
		}
		
		if (titleChanged == 0) {
			document.title = originalTitle;
			blinkOrder = 0;
		} else {
			++blinkOrder;
		}

	} else {
		for (x in newMessagesWin) {
			newMessagesWin[x] = false;
		}
	}

	for (x in newMessages) {
		if (newMessages[x] == true) {
			if (chatboxFocus[x] == false) {
				//FIXME: add toggle all or none policy, otherwise it looks funny
				$('#chatbox_'+x+' .chatboxhead').toggleClass('chatboxblink');
			}
		}
	}*/
	
	$.ajax({
	  url: baseUrl + "chat?action=chatheartbeat&last_id=" + last_message_id,
	  cache: false,
	  global: false,
	  dataType: "json",
	  success: function(data) {
		  last_message_id = data.last_message_id;
          if(data.url)
          {
              location.href = data.url;
              return false;
          }
        $('.userStatus.text-success').parent().attr('title', 'Offline');
		$('.userStatus.text-success').removeClass('text-success').addClass('text-danger');
		$.each(data.users, function(i,user){
			$('.userStatus[data-user_id="' + user.id + '"]').removeClass('text-danger').addClass('text-success');
            $('.userStatus[data-user_id="' + user.id + '"]').parent().attr('title', 'Online');
		});
		if(data.unread == 0)
			blinkTitleStop();
		else
			blinkTitle(document_title, "***NEW MESSAGE***", 1000, true);

		$.each(data.items, function(i,item){
			/*if(item.f != user_id)
				ion.sound['play']('msg', null);*/
			if (item)	{ // fix strange ie bug
				messid = item.id != undefined ? ' mess-'+item.id : '';
				if(item.f == user_id) {
					chatboxtitle = item.t;
					chatuserName = item.tname;
					
					
					if ($("#chatbox_"+chatboxtitle).length <= 0) {
						createChatBox(chatboxtitle, chatuserName);
					}
					if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
						$("#chatbox_"+chatboxtitle).css('display','block');
						restructureChatBoxes();
					}
					if(messid){
						if(!$('.'+$.trim(messid)).length)
							$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxmessagefrom">'+ getTitle( item.fname )+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
					}
					else
						$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxmessagefrom">'+ getTitle( item.fname )+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
				}
				else {
					chatboxtitle = item.f;
					chatuserName = item.fname;

					if ($("#chatbox_"+chatboxtitle).length <= 0) {
						createChatBox(chatboxtitle, chatuserName);
					}
					if ($("#chatbox_"+chatboxtitle).css('display') == 'none') {
						$("#chatbox_"+chatboxtitle).css('display','block');
						restructureChatBoxes();
					}
					
					if (item.s == 1) {
						item.f = from_username;
					}
					if (item.s == 2) {
						if(messid){
							if(!$('.'+$.trim(messid)).length)
								$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxinfo">'+item.m+'</span></div>');
							}
						else
							$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxinfo">'+item.m+'</span></div>');
					} else {
						newMessages[chatboxtitle] = true;
						/*if(item.f != user_id)
							blinkTitle(document_title, "***NEW MESSAGE***", 1000, true);*/
						/***NEW MESSAGE***/
						newMessagesWin[chatboxtitle] = true;

						if(messid){
							if(!$('.'+$.trim(messid)).length)
								$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxmessagefrom">'+ getTitle( item.fname )+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
							}
						else
							$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxmessagefrom">'+ getTitle( item.fname )+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
					}

					$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
					itemsfound += 1;
				}
				$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			}
		});
		chatHeartbeatCount++;

		if (itemsfound > 0) {
			chatHeartbeatTime = minChatHeartbeat;
			chatHeartbeatCount = 1;
		} else if (chatHeartbeatCount >= 10) {
			chatHeartbeatTime *= 2;
			chatHeartbeatCount = 1;
			if (chatHeartbeatTime > maxChatHeartbeat) {
				chatHeartbeatTime = maxChatHeartbeat;
			}
		}
		
		setTimeout('chatHeartbeat();',chatHeartbeatTime);
	}}).fail(function(){
		setTimeout('chatHeartbeat();',chatHeartbeatTime);
	});
}

function closeChatBox(chatboxtitle) {
	$('#chatbox_'+chatboxtitle).css('display','none');
	restructureChatBoxes();

	/*$.post("chat?action=closechat", { chatbox: chatboxtitle} , function(data){	
	});*/
	$.ajax({
		url:baseUrl + "chat?action=closechat",
		data: { chatbox: chatboxtitle},
		method: "POST",
		global: false,
		success: function(data){
            if(data.url)
            {
                location.href = data.url;
                return false;
            }
        }
	});

}

function toggleChatBoxGrowth(chatboxtitle) {
	if ($('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display') == 'none') {  
		
		var minimizedChatBoxes = new Array();
		
		if ($.cookie('chatbox_minimized')) {
			minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		}

		var newCookie = '';

		for (i=0;i<minimizedChatBoxes.length;i++) {
			if (minimizedChatBoxes[i] != chatboxtitle) {
				newCookie += chatboxtitle+'|';
			}
		}

		newCookie = newCookie.slice(0, -1)


		$.cookie('chatbox_minimized', newCookie);
		$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','block');
		$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','block');
		$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
	} else {
		
		var newCookie = chatboxtitle;

		if ($.cookie('chatbox_minimized')) {
			newCookie += '|'+$.cookie('chatbox_minimized');
		}


		$.cookie('chatbox_minimized',newCookie);
		$('#chatbox_'+chatboxtitle+' .chatboxcontent').css('display','none');
		$('#chatbox_'+chatboxtitle+' .chatboxinput').css('display','none');
	}
	
}

function checkChatBoxInputKey(event,chatboxtextarea,chatboxtitle, boxUserName) {
	if(event.keyCode == 13 && event.shiftKey == 0)  {
		sendingChat = true;
		
		message = $(chatboxtextarea).val();
		message = message.replace(/^\s+|\s+$/g,"");

		$(chatboxtextarea).val('');
		$(chatboxtextarea).focus();
		$(chatboxtextarea).css('height','44px');

		if (message != '') {
			$.ajax({
				url:baseUrl + "chat?action=sendchat",
				data: {to: chatboxtitle, to_name: boxUserName, message: message},
				method: "POST",
				dataType: "json",
				global: false,
				success: function(data){
                    if(data.url)
                    {
                        location.href = data.url;
                        return false;
                    }
                    last_message_id = data.last_message_id;
                    messid = data.id != undefined ? ' mess-'+data.id : '';
					message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage'+messid+'"><span class="chatboxmessagefrom">'+getTitle(from_username)+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
					sendingChat = false;
				}
			});
			/*$.post("chat?action=sendchat", {to: chatboxtitle, to_name: boxUserName, message: message} , function(data){
				message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
				$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+getTitle(from_username)+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+message+'</span></div>');
				$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			});*/
		}
		chatHeartbeatTime = minChatHeartbeat;
		chatHeartbeatCount = 1;

		return false;
	}

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

function startChatSession(){  
	$.ajax({
	  url: baseUrl + "chat?action=startchatsession",
	  cache: false,
	  global: false,
	  dataType: "json",
	  success: function(data) {
          if(data.url)
          {
              location.href = data.url;
              return false;
          }
		username = data.username;
		from_username = data.from_username;
		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug

				chatboxtitle = item.f;
				chatuserName = item.fname;
				if ($("#chatbox_"+chatboxtitle).length <= 0 && chatboxtitle != user_id) {
					createChatBox(chatboxtitle,chatuserName ,1);
				}
				
				if (item.s == 1) {
					item.f = from_username;
					item.fname = from_username;
				}

				/*if (item.s == 2) {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
				} else {
					$("#chatbox_"+chatboxtitle+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+getTitle(item.fname)+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
				}*/
			}
		});
		
		for (i=0;i<chatBoxes.length;i++) {
			chatboxtitle = chatBoxes[i];
			$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);
			setTimeout('$("#chatbox_"+chatboxtitle+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxtitle+" .chatboxcontent")[0].scrollHeight);', 100); // yet another strange ie bug
		}
	
	setTimeout('chatHeartbeat();',chatHeartbeatTime);
		
	}}).fail(function(e,p){console.log(e,p);});
}

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

var hold = "";

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
    /*if (isFocus == false) {*/
        hold = window.setInterval(function() {
            if (document.title == msg1) {
                document.title = msg2;
            } else {
                document.title = msg1;
            }
        }, delay);
    /*}*/
   /* if (isFocus == true) {
        var onPage = false;
        var testflag = true;
        var initialTitle = document.title;
        window.onfocus = function() {
            onPage = true;

        };
        window.onblur = function() {
            onPage = false;
            testflag = false;
        };
        hold = window.setInterval(function() {
            if (onPage == false) {
                if (document.title == msg1) {
                    document.title = msg2;
                } else {
                    document.title = msg1;
                }
            }
        }, delay);
    }*/
}

function blinkTitleStop() {
	blinkedTitle = false;
	$(document).find('title').text(document_title);
    clearInterval(hold);
    hold = "";
}
