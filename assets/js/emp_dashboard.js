var latitude, longitude;
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
        url: _BASE_URL + 'employee/getdatabymonth',
        data: 'monthyear=' + $("#monthyear").val(),
        type: 'post',
        dataType: 'text',
        success: function (res) {
            $("#monthreport").html(res);
            //alert("Logged out successfully");
            //$("#container").html('');
            //$(".datepicker").hide();
            //call_login();
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

function toggletimer(f) {
    var timer_class = $(f).attr("class");
    //alert(timer_class);
    if (timer_class.indexOf("start") >= 0) {
        timer('start');
        var that = $('#entry-button');
        $('#entry-button').attr("disabled", true);
        $('#entry-button').hide();
        $('#running-button').show();
        // setTimeout(function() { enableTimer(that) },_DISABLE_TIMER_BUTTON );
    } else if (timer_class.indexOf("running") >= 0) {
        timer('stop');
        // var that = $('#entry-button');
        var that = $('#running-button');
        $('#running-button').attr("disabled", true);
        $('#entry-button').show();
        $('#running-button').hide();
        //setTimeout(function() { enableTimer(that) }, _DISABLE_TIMER_BUTTON);
    }
    //return false;
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

function timer(status) {
    $("#btnsubmit").hide();
    sloading();
    $.ajax({
        url: _BASE_URL + "employee/timer",
        data: "timer=" + status + "&login_rec_id=" + $("#login_rec_id").val() + "&lat=" + latitude + "&lon=" + longitude + "&new_rec_id=" + $("#login_rec_id").attr('data-new_table_record'),
        type: "post",
        dataType: "json",
        success: function (response) {
            if (status == "start") {
                //   stared success
                if (response.res == "SUCCESS") {
                    if (response.rec_id) {
                        $("#login_rec_id").val(response.rec_id);
                    }
                    if (response.new_rec_id) {
                        $("#login_rec_id").attr('data-new_table_record', response.new_rec_id);
                    }
                    $("#logouttime").val('00:00');
                    $("#timediff").val('00:00');
                    //$("#entry-button").removeClass("start");
                    //$("#entry-button").addClass("running");
                    $("#entry-button").find("i").show();
                    $("#logintime").html(response.login_time);
                    // $('#show_timer').removeClass('filled_blue').addClass('filled_green');
                    //$("#entry-button").find("span").html("Stop");
                    
                    
                    /* to photo need uncomment
                     * var dataUrl = takepicture();
                    uploadImage(dataUrl, response.rec_id, 'login', response.new_rec_id);
                    */
                } else {
                    addError("Error in starting timer. Please try again.");
                }
                //$("#btnsubmit").show();
            } else {
                //   stopped success
                if (response.res == "SUCCESS") {
                    var login_rec_id = $("#login_rec_id").val();
                    var new_rec_id = $("#login_rec_id").attr('data-new_table_record');
                    $("#login_rec_id").val('');
                    //$("#entry-button").removeClass("running");
                    //$("#entry-button").addClass("start");
                    $("#entry-button").find("i").hide();
                    $("#logouttime").html(response.logout_time);
                    $("#timediff").html(response.time_diff);
                    $('#time_str').html(response.time_str);
                    //$('#show_timer').removeClass('filled_green').addClass('filled_blue');
                    // $("#entry-button").find("span").html("Start");
                    
                    /* to photo need uncomment
                    var dataUrl = takepicture();
                    uploadImage(dataUrl, login_rec_id, 'logout', new_rec_id);
					*/

                } else {
                    addError("Error in stoping timer. Please try again.");
                }
                $("#btnsubmit").show();
            }
            $("#toploading").remove();
            get_monthly_report();

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

function uploadImage(file, login_rec_id, type, new_rec_id) {
    //showLoader("btnbutton");
    $.ajax({
        url: _BASE_URL + "employee/recognize",
        type: "POST",
        data: {image: file, rec_id: login_rec_id, ltype: type, new_rec_id : new_rec_id},
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

function show_employee_dashboard() {
    sloading();
    $("#container").load(_BASE_URL + "employee/dashboard", function () {
        $("#toploading").remove();
        $("#username").html($("#empname").val());
       /* to photo need uncomment
        *  if (web_cam_started == false) {

            $("#entry-button").hide();
            $("#running-button").hide();
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
							
								$("#entry-button").show();
								$("#running-button").show();
								if ($('#running-button').attr('disabled') != 'disabled') {
									$("#entry-button").hide();
								} else {
									$("#running-button").hide();
								}
								$("#cam-warning").hide();
								web_cam_started = true;
							
                        });
                    }
                    else
                    {
						$("#entry-button").show();
						$("#running-button").show();
						if ($('#running-button').attr('disabled') != 'disabled') {
							$("#entry-button").hide();
						} else {
							$("#running-button").hide();
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
        }*/
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
