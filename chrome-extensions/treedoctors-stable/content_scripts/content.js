
$(document).ready(function(){
	var msg = {
		method: 'ctp.status'
	};
	chrome.runtime.sendMessage(msg, function(response) {
		if(response == 'ready') {
			$('.createCall').removeClass('disabled');
		}
	});
});

$(document).on('click', '.createCall', function(){
	if($(this).is('.disabled'))
		return false;
	var msg = {
		method: 'ctp.call',
		params: {
			To: $(this).data('number'),
		}
	};
	chrome.runtime.sendMessage(msg);
});
