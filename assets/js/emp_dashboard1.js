var latitude, longitude;
//entry-button - start
//running-button - stop
function dologin() {
    $("#txtUsername").parent().addClass("control-group success");
    $("#txtPassword").parent().addClass("control-group success");

    if ($("#txtUsername").val() == "") {
        $("#txtUsername").parent().removeClass();
        $("#txtUsername").parent().addClass("control-group error");
        $("#txtUsername").focus();
        return false;
    }

    if ($("#txtUsername").val().match(/^[A-Za-z0-9]+$/) == null) {
        alert("Username should be Alphanumeric");
        $("#txtUsername").parent().removeClass();
        $("#txtUsername").parent().addClass("control-group error");
        $("#txtUsername").focus();
        return false;
    }

    if ($("#txtPassword").val() == "") {
        $("#txtPassword").parent().removeClass();
        $("#txtPassword").parent().addClass("control-group error");
        $("#txtPassword").focus();
        return false;
    }
    sloading();
    $.ajax({
        url: _BASE_PATH + 'employee/check_login',
        data: "r=" + encrypt($("#txtUsername").val() + "---" + $("#txtPassword").val()),
        type: 'POST',
        dataType: 'text',
        success: function (res) {
            $("#toploading").remove();
            if (res.indexOf("Incorrect Username or Password") >= 0) {
                alert(res);
            } else if (res == "login done") {
                location.reload();
                //show_employee_dashboard();
            } else {
                alert("Can not login.");
            }
        },
        error: function (err) {
            alert(err.responseText);
        }
    });
}

function logout() {
    sloading();
    $.ajax({
        url: _BASE_URL + 'employee/logout',
        data: '',
        type: 'post',
        dataType: 'text',
        success: function (res) {
            alert("Logged out successfully");
            location.reload();
            //$("#container").html('');
            $(".datepicker").hide();
            location.href = baseUrl + 'employee';//call_login(); CHANGED 30.01.2015 BY GLEBA RUSLAN
            $("#toploading").remove();
        },
        error: function (err) {
            alert("Error in logout: " + err.responseText);
            $("#toploading").remove();
        }
    });
}


function get_monthly_report() {
    sloading();
    $.ajax({
        url: _BASE_URL + 'employee/getdatabymonth1',
        data: 'monthyear=' + $("#monthyear").val(),
        type: 'post',
        dataType: 'text',
        success: function (res) {
            $("#monthreport").html(res);
            $("#toploading").remove();
            $('#show_timer').show();
            $('#show_timer1').show();
        },
        error: function (err) {
            alert("Error in logout: " + err.responseText);
            $("#toploading").remove();
        }
    });
}
/* this  enableTimer - for setting time of start / stop button*/
var enableTimer = function (ele) {
    $(ele).removeAttr("disabled");
}

function get_report() {
    showLoader("btngetreport");
    $.ajax({
        url: _BASE_URL + "employee/dashboard",
        data: $("#frmgetmonth").serialize(),
        type: 'POST',
        dataType: 'html',
        success: function (res) {
            //$("#container").html('');
            $("#container").html(res);
        },
        error: function (err) {
            alert("Error in getting Report: " + err.responseText);
        }
    });
}

function takepicture() {
    canvas.width = videoElement.width;
    canvas.height = videoElement.height;
    canvas.getContext('2d').drawImage(videoElement, 0, 0, videoElement.width, videoElement.height);
    var data = canvas.toDataURL();
    //videoElement.stop();
    return data;
}

function start() {
    $("#btnsubmit").hide();
    sloading(); 
    $.ajax({
        url: _BASE_URL + "employee/start",
        data: "login_rec_id=" + $("#login_rec_id").val() + "&lat=" + latitude + "&lon=" + longitude,
        type: "post",
        dataType: "json",
        success: function (response) {
			//   stared success
			if (response.res == "SUCCESS") {
				if (response.rec_id) {
					$("#login_rec_id").val(response.rec_id);
				}
				$("#logouttime").val('00:00');
				$("#timediff").val('00:00');
				//$("#entry-button").removeClass("start");
				//$("#entry-button").addClass("running");
				$("#stop").find("i").show();
				$("#logintime").html(response.login_time);
				// $('#show_timer').removeClass('filled_blue').addClass('filled_green');
				//$("#entry-button").find("span").html("Stop");
				var dataUrl = takepicture();
				uploadImage(dataUrl, response.rec_id, 'login');
			} else {
				addError("Error in starting timer. Please try again.");
			}
			//$("#btnsubmit").show(); 
            $("#toploading").remove();
            get_monthly_report();
			$('#start').attr("disabled", true);
			$('#start').hide();
			$('#stop').show();
			$('#stop').attr("disabled", false);

        },
        error: function (e) {
            console.log("Error in starting timer - " + e.responseText);
            $("#toploading").remove();
        }
    });
}

function stop() { 
    $("#btnsubmit").hide();
    sloading(); 
    $.ajax({
        url: _BASE_URL + "employee/stop",
        data: "login_rec_id=" + $("#login_rec_id").val() + "&lat=" + latitude + "&lon=" + longitude,
        type: "post",
        dataType: "json",
        success: function (response) {
			//   stopped success
			if (response.res == "SUCCESS") {
				var login_rec_id = $("#login_rec_id").val();
				$("#login_rec_id").val('');
				$("#start").find("i").hide();
				$("#logouttime").html(response.logout_time);
				$("#timediff").html(response.time_diff);
				$('#time_str').html(response.time_str);
				var dataUrl = takepicture();
				uploadImage(dataUrl, login_rec_id, 'logout');
			} else {
				addError("Error in stoping timer. Please try again.");
			}
			$("#btnsubmit").show();
			$('#stop').attr("disabled", true);
			$('#stop').hide();
			$('#start').show();
			$('#start').attr("disabled", false);

        },
        error: function (e) {
            console.log("Error in starting timer - " + e.responseText);
            $("#toploading").remove();
        }
    });
}

function addError(msg) {
    $(".alert-text").html(msg);
    $("#error").show();
}

function uploadImage(file, login_rec_id, type) {
    //showLoader("btnbutton");
    $.ajax({
        url: _BASE_URL + "employee/recognize",
        type: "POST",
        data: {image: file, rec_id: login_rec_id, ltype: type},
        success: function (rd) {
            // window.location.href = '';
        }
    });
}


function call_login() {
    sloading();
    $("#container").load(_BASE_URL + "employee/login", function () {
        $("#toploading").remove();
    });
}

function show_employee_dashboard() { // THIS EDIT
    sloading(); 
    $("#container").load(_BASE_URL + "employee/dashboard1", function () {
        $("#toploading").remove();
        $("#username").html($("#empname").val());
        if (web_cam_started == false) {

            $("#start").hide();
            $("#stop").hide();
            navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;

            navigator.getUserMedia({
                video: true,
                audio: false
            }, function (stream) {
                //videoElement.src = window.URL.createObjectURL(localMediaStream);
                if (navigator.mozGetUserMedia) {
                    videoElement.mozSrcObject = stream;
                } else {
                    var vendorURL = window.URL || window.webkitURL;
                    videoElement.src = vendorURL.createObjectURL(stream);
                }
                videoElement.play();
                videoElement.addEventListener('loadedmetadata', function (e) {
                    //videoElement.onloadedmetadata = function(e) {

                    if(navigator.geolocation && emp_estimator == '1') {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            
								latitude = position.coords.latitude;
								longitude = position.coords.longitude;
							
								$("#start").show();
								$("#stop").show();
								if ($('#start').attr('disabled') != 'disabled') {
									$("#stop").hide();
								} else {
									$("#start").hide();
								}
								$("#cam-warning").hide();
								web_cam_started = true;
							
                        });
                    }
                    else
                    {
						$("#stop").show();
						$("#start").show();
						if ($('#start').attr('disabled') != 'disabled') {
							$("#stop").hide();
						} else {
							$("#start").hide();
						}
						$("#cam-warning").hide();
						web_cam_started = true;
					}
                });
            }, function (e) {
                web_cam_started = true;
                if (e.code === 1) {
                    console.log('User declined permissions.');
                }
            });
        }
        get_monthly_report();
    });
}

function sloading() {

    var loading = $("<div></div>");
    $(loading).attr("id", "toploading");
    $(loading).css("position", "absolute");
    $(loading).css("border", "4px ridge #DADADA");
    $(loading).css("border-radius", "5px");
    $(loading).css("background-color", "#D1F0FF");
    $(loading).css("width", "200px");
    $(loading).css("padding", "15px");
    $(loading).css("top", "30%");
    $(loading).css("left", "40%");
    $(loading).css("text-align", "center");
    $(loading).css("color", "brown");
    $(loading).css("font-weight", "bold");
    $(loading).html("<img src='" + _BASE_URL + "assets/img/loading4.gif'/>");
    $("body").append($(loading));
}

