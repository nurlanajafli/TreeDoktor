var bgp = chrome.extension.getBackgroundPage();

navigator.webkitGetUserMedia({audio: true, video: false}, function() {
	localStorage.setItem('mic-access', 'good');
}, function(e) {
	localStorage.setItem('mic-access', 'fail');
});

function incorrectLogin() {
	$('#log_email, #log_password').parent().addClass('has-error');
	$('#errMsg').text('Inorrect Login or Password');
}


document.addEventListener("DOMContentLoaded", function () {
	$('#currYear').text(new Date().getFullYear());
	if(localStorage.getItem('firstname') && localStorage.getItem('lastname') && localStorage.getItem('worker_sid') && localStorage.getItem('workspace_sid')) {
		$('.logout').css('display', 'block');
		$('.login').css('display', 'none');
		$('.logout header strong').text('Hello ' + localStorage.getItem('firstname') + ' ' + localStorage.getItem('lastname'));
	}
	else {
		$('.login').css('display', 'block');
		$('.logout').css('display', 'none');
		
		if(localStorage.getItem('mic-access') != 'good') {
			var left = '130px';
			var top = '150px';
			/*var windowWidth = $(window).width();
			if(windowWidth < 1356) {
				left = '110px';
				top = '140px';
			}
			if(windowWidth > 1366 && windowWidth <= 1518) {
				left = '160px';
				top = '165px';
			}
			if(windowWidth > 1518 && windowWidth <= 1708) {
				left = '200px';
				top = '180px';
			}
			if(windowWidth > 1708 && windowWidth <= 1821) {
				left = '220px';
				top = '190px';
			}
			if(windowWidth > 1821 && windowWidth <= 2050) {
				left = '270px';
				top = '210px';
			}
			if(windowWidth > 2050) {
				left = '390px';
				top = '270px';
			}
			$('.arrow_box').css({left:left}).show().animate({top:top}, 1500);*/
		}
	}
	
});
$(document).on("click", "#closePopup", function () {
	$(this).parent().fadeOut();
});
if(!$("#loginForm").length) {
	$(document).on("click", "#login", function () {
		$('#log_email, #log_password').parent().removeClass('has-error');
		$('#errMsg').text('');
		var name = $('#log_email').val();
		var pass = $('#log_password').val();
		bgp.ws.send({method:'login', params:{login:name,pass:pass}});
	});
}
$(document).on("submit", "#loginForm", function () {
	$('#log_email, #log_password').parent().removeClass('has-error');
	$('#errMsg').text('');
	var name = $('#log_email').val();
	var pass = $('#log_password').val();
	bgp.ws.send({method:'login', params:{login:name,pass:pass}});
	return false;
});
$(document).on("click", "#logout", function () {
	localStorage.removeItem('firstname');
	localStorage.removeItem('lastname');
	localStorage.removeItem('worker_sid');
	localStorage.removeItem('workspace_sid');

	setTimeout(function(){
		bgp.location.reload();
		location.reload();
	}, 500);
});
