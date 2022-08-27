var bgp = chrome.extension.getBackgroundPage();
var wizard = {};
navigator.webkitGetUserMedia({audio: true, video: false}, function() {
	localStorage.setItem('mic-access', 'good');
}, function(e) {
	localStorage.setItem('mic-access', 'fail');
});

function incorrectLogin() {
	$('#log_email, #log_password').parent().addClass('has-error');
	$('#errMsg').text('Inorrect Login or Password');
}

function welcome() {
	$('#myWizard').wizard('selectedItem', {step: 3});
	$('#myWizard #agentName').text(localStorage.getItem('firstname') + ' ' + localStorage.getItem('lastname'));
}

document.addEventListener("DOMContentLoaded", function () {
	$('#myWizard').wizard({disablePreviousStep:true});
	$('#myWizard').on('actionclicked.fu.wizard', function(evt, data){
		if(data.step != undefined) {
			if(data.step == 1) {
				$('#log_domain').parent().removeClass('has-error');
				var domain = 'https://' + $.trim($('#log_domain').val()) + '.arbostar.com';
				var re = new RegExp(/^(((https:\/\/|http:\/\/)(([a-zA-Z0-9])|([a-zA-Z0-9][a-zA-Z0-9\-]{0,86}[a-zA-Z0-9]))\.(([a-zA-Z0-9])|([a-zA-Z0-9][a-zA-Z0-9\-]{0,73}[a-zA-Z0-9]))\.(([a-zA-Z0-9]{2,12}\.[a-zA-Z0-9]{2,12})|([a-zA-Z0-9]{2,25})))|((([a-zA-Z0-9])|([a-zA-Z0-9][a-zA-Z0-9\-]{0,162}[a-zA-Z0-9]))\.(([a-zA-Z0-9]{2,12}\.[a-zA-Z0-9]{2,12})|([a-zA-Z0-9]{2,25}))))/);
				var match = domain.match(re);
				
    			if(match === null)
    				return false;
    			var base_url = match[0];
    			$.ajax({
    				url: base_url + "/api/settings",
    				dataType: 'json',
    				success: function(data) {
    					$.each(data, function(key, val) {
    						localStorage.setItem(key, val);
    					});
    					$('#myWizard').wizard('selectedItem', {step: 2});
    					bgp.initSocket();
    				},
    				error: function() {
    					$('#log_domain').parent().addClass('has-error');
						$('#errMsg').text('Inorrect Login or Password');
    				}
    			});
    			return false;
			}
			if(data.step == 2) {
				$('#log_email, #log_password').parent().removeClass('has-error');
				var name = $('#log_email').val();
				var pass = $('#log_password').val();
				bgp.ws.send({method:'login', params:{login:name,pass:pass}});
				return false;
			}
			if(data.step == 3) {
				localStorage.removeItem('firstname');
				localStorage.removeItem('lastname');
				localStorage.removeItem('worker_sid');
				localStorage.removeItem('workspace_sid');
				localStorage.removeItem('base_url');
				localStorage.removeItem('port');

				setTimeout(function(){
					bgp.location.reload();
					location.reload();
				}, 500);
			}
		}
	});
	$('#currYear').text(new Date().getFullYear());
	if(localStorage.getItem('port') && localStorage.getItem('base_url') && localStorage.getItem('firstname') && localStorage.getItem('lastname') && localStorage.getItem('worker_sid') && localStorage.getItem('workspace_sid')) {
		welcome();
	}
	else {		
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
	
	return false;
});
