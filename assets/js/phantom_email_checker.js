var system = require('system');
var args = system.args;
var page = require('webpage').create();
var email = args[1];
var url = 'https://tools.verifyemailaddress.io/';
page.open(url, function (status) {
	page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js", function() {
		var result = -1;
		
		var checks = page.evaluate(function() {
			return (20 - parseInt($('#ctl00_PageMainContent_pnlClientUsageInfo').html().match(/<p>.*?<strong>.*?<\/strong> (.*?) <strong>.*?<\/strong>.*?<\/p>/i)[1]));
		});
		if(!checks)
		{
			page.close();
			console.log(result);
			phantom.exit();
		}
		
		var isForm = page.evaluate(function() {
			return $('#ctl00_PageMainContent_txtEmail').length;
		});
		if(!isForm)
		{
			page.close();
			console.log(result);
			phantom.exit();
		}
		
		var ua = page.evaluate(function(email) {
			$('#ctl00_PageMainContent_txtEmail').val(email);
			$('#ctl00_PageMainContent_cmdSubmit').click();
			return false;
		}, email);
		
		setTimeout(function(){
			var result = page.evaluate(function(email) {
				return $('[title="' + email + '"]').parent().next(':contains("Ok")').length;
			}, email);
			page.close();
			console.log(result);
			phantom.exit();
		}, 3000);
	});
});
 
