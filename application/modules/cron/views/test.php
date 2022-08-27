<body>
<script src="<?php echo base_url('assets/js/jquery.js'); ?>" type="text/javascript"></script>
<center>
	<div id="timer">
		<div id="hour">00</div>&nbsp;:
		<div id="minute">00</div>&nbsp;:
		<div id="second">00</div>
	</div>
</center>
<style>
#timer{
	margin: 20px auto;
	text-align: center;
	color: #333;
	font-family: fantasy;
	font-size: 100px;
	cursor: default;
}
	 
#timer div{
	display: inline;
}
</style>


<script>
	function timer(){
		var hour = document.getElementById('hour').innerHTML;
		var minute = document.getElementById('minute').innerHTML;
		var second = document.getElementById('second').innerHTML;
		var end = false;
		 
		if(second < 59) second++;
		else{
			second = 60; 
			if( minute < 59 ) minute++;
			else{
				second = 60;
				if(hour < 23 ) hour++;
				else end = true;
			}
		}
		if(end){
			clearInterval(intervalID);
			alert("Таймер сработал!");
		}else{
			document.getElementById('hour').innerHTML = hour;
			document.getElementById('minute').innerHTML = minute;
			document.getElementById('second').innerHTML = second;
		}
	}
	window.intervalID = setInterval(timer, 1000);
</script>
</body>
