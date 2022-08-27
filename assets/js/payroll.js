$(window).on('load', function () {
    $('#intime-timepicker').datetimepicker({
        pickDate: false
    });

    $('#outtime-timepicker').datetimepicker({
        pickDate: false
    });

    $('.selectpicker').selectpicker();
    // $('.selectpicker').selectpicker('hide');

    $("#btn_generate_pdf").click(function () {
        $("#generate_pdf").val(1);
        $("#btnsubmit").click();
    });
    var blockid = 0;
    $("input[name='btn-edit']").click(function () {
        var id = $(this).attr("id");
        console.log("id");
        if (id.indexOf("-") >= 0) {
            var idarr = id.split("-");
            if (idarr[3]) {
                blockid = idarr[3];
                $("#div-edit-time-" + blockid).show();
                $("#inp-intime-" + blockid).mask("99:99");
                $("#inp-outtime-" + blockid).mask("99:99");
            }
        }
    });

    $("#pre-week").click(function () {
        $("#generate_pdf").val(0);
        $("#get_data_by_date").val($("#pre-week-val").val());
        $("#this-week").removeClass("disabled");
        $("#next-week").removeClass("disabled");
        $(this).addClass("disabled");
        $("#which_week").val($(this).attr("id"));
        $("#searcEmployee").submit();
    });

    $("#next-week").click(function () {
        $("#generate_pdf").val(0);
        $("#get_data_by_date").val($("#next-week-val").val());
        $("#this-week").removeClass("disabled");
        $("#pre-week").removeClass("disabled");
        $(this).addClass("disabled");
        $("#which_week").val($(this).attr("id"));
        $("#searcEmployee").submit();
    });

    $("#this-week").click(function () {
        $("#generate_pdf").val(0);
        $("#get_data_by_date").val($("#this-week-val").val());
        $("#next-week").removeClass("disabled");
        $("#pre-week").removeClass("disabled");
        $(this).addClass("disabled");
        $("#which_week").val($(this).attr("id"));
        $("#searcEmployee").submit();
    });

    /*$(".edit-time").each(function() {
     $("#generate_pdf").val(0);
     $(this).click(function() {
     var timetoedit = $(this).attr("id").split("-")[2];
     var grower = $("#emp_time_details_"+timetoedit);
     $(grower).fadeIn('fast').animate({'left':'25%', 'top': "20%", 'margin-top': +($('#targets').height()/-2)}, {duration: 'slow', queue: false}, function() {
     // Animation complete.
     });

     return false;
     });
     });*/

    $(".close-emp-details").each(function () {
        $("#generate_pdf").val(0);
        $(this).click(function () {
            var divtoclose = $(this).attr("id").split("-")[3];
            $("#emp_time_details_" + divtoclose).hide();
        });
    });

    $(".delete-emp-details").each(function () {
        $("#generate_pdf").val(0);
        $(this).click(function () {
            var conf = window.confirm("Are you sure to Delete this record?");
            if (conf == true) {
                var divtodelete = $(this).attr("id");
                $.ajax({
                    url: _BASE_URL + 'employees/deletetime_ajax',
                    data: 'inpid=' + divtodelete,
                    type: 'post',
                    success: function (res) {
                        if (res == 1) {
                            alert('Record Deleted Successfully');
                            $("#searcEmployee").submit();
                        }
                        return false;
                    },
                    error: function (err) {
                        alert("Error in deleting Login details: " + err.responseText);
                    }
                });
            }
        });
    });

    $(".add-emp-details").each(function () {
        $("#generate_pdf").val(0);
        $(this).click(function () {
            var divtoadd = $(this).attr("id");
            $(".login_date").val(divtoadd);
            $(".add-time-detials").find("h3").html("Add Login Details - " + divtoadd);
            $("#inp-login-time").mask("99:99");
            $("#inp-logout-time").mask("99:99");
            $("#inp-login-date").val(divtoadd);
            $(".add-time-detials").show();
        });
    });

    $(".edit-emp-details").each(function () {
        $("#generate_pdf").val(0);
        $(this).click(function () {
            var darr = $(this).attr("id").split("||");
            $(".login_date").val(darr[0]);
            $(".add-time-detials").find("h3").html("Edit Login Details - " + darr[0]);
            $("#inp-login-time").mask("99:99");
            $("#inp-logout-time").mask("99:99");
            $("#inp-login-date").val(darr[0]);
            $("#inp-id").val(darr[1]);
            //$("#inp-login-time").val($.trim($("#in-time-"+darr[1]).html().replace(/&nbsp;/gi,"")));
            //$("#inp-logout-time").val($.trim($("#out-time-"+darr[1]).html().replace(/&nbsp;/gi,"")));
            $("#inp-login-time").val($.trim($("#in-time-" + darr[1]).attr("title")));
            $("#inp-logout-time").val($.trim($("#out-time-" + darr[1]).attr("title")));
            $(".add-time-detials").show();
        });
    });

    $(".save_login_time").click(function () {
        var inpid = $(this).attr('id');
        var indate = $("#inp-login-date-" + inpid).val();
        var intime = $("#inp-login-time-" + inpid).val();
        var outtime = $("#inp-logout-time-" + inpid).val();
        var empid = $("#inp-emp-id-" + inpid).val();
        var hourlyrate = 0;
        if($("#inp-hourly-rate-" + inpid).length && $("#inp-hourly-rate-" + inpid).val())
			var hourlyrate = $("#inp-hourly-rate-" + inpid).val();
        if (inpid == "") {
            //add
            var url = 'addtime_ajax';
            var text1 = 'inserted';
            var text2 = 'inserting';
        } else {
            // edit
            var url = 'edittime_ajax';
            var text1 = 'updated';
            var text2 = 'updating';
        }

        $.ajax({
            url: _BASE_URL + 'employees/' + url,
            data: 'hourlyrate=' + hourlyrate + '&empid=' + empid + '&indate=' + indate + '&intime=' + intime + "&outtime=" + outtime + "&inpid=" + inpid,
            type: 'post',
            dataType: 'text',
            success: function (res) {
				console.log(res);
                if (res > 0) {
                    alert("Time details " + text1 + " successfully.");
                    $("#searcEmployee").submit();
                }
                /*else if (res.indexOf("error: greater") >= 0) {
                    alert("In time should be smaller than Out time. Please try again.");
                } Add new working day without Out time*/ 
                else {
                    alert("Error in " + text2 + " time details. Please try again!!");
                }
            },
            error: function (err) {
                alert("Error in " + text2 + " time details: " + err.responseText);

            }
        });
    });


    $("#close_login_time").click(function () {
        $(".add-time-detials").hide();
    });


    $(".fancybox1").each(function () {
        $(this).click(function () {
            $("#fancbox").remove();
            var img = $(this).find("img");
            var div1 = $("<div></div>");
            $(div1).addClass("fancybox-overlay fancybox-overlay-fixed");

            var div3 = $("<div></div>");
            $(div3).addClass("fancybox-skin");

            var div4 = $("<div></div>");
            $(div4).addClass("fancybox-inner");

            var fimg = $("<img></img>");
            $(fimg).addClass("fancybox-image");
            $(fimg).attr("src", $(img).attr("src"));
            var fclose = $("<a></a>");
            $(fclose).addClass("fancybox-item fancybox-close");
            $(fclose).click(function () {
                $("#fancbox").remove();
            });

            var fwrapp = $("<div></div>");
            $(fwrapp).css("width", "300px");
            $(fwrapp).css("height", "auto");
            $(fwrapp).css("border", "10px solid orange");
            $(fwrapp).css("z-index", "9000");
            $(fwrapp).attr("id", "fancbox");
            $(fwrapp).append($(div1));
            $(fwrapp).append($(div3));
            $(div4).append($(fimg));
            $(fwrapp).append($(div4));
            $(fwrapp).append($(fclose));

            $("body").append($(fwrapp));
            $("#fancbox").centerToWindow();
        });
    });
    $('.addEmpRow').click(function () {
        var date = $(this).data('date');
        var emp_id = $(this).data('emp_id');
		
        $('#addAmpClose').click();
        var obj = $(this).parent().parent().find('.emp_data');
        var row = '<tr id="addEmpTr"><td><input type="time" class="span2" id="logIn"></td><td><input type="time" class="span2" id="logOut"></td>';
        
        if($(obj).parent().find('thead th').length == 6)
			row += '<td></td>';
        row += '<td></td><td></td><td width="80px"><span class="btn btn-xs btn-info ampRowSave" data-date="' + date + '" data-emp_id="' + emp_id + '"><i class="fa fa-check"></i></span><span class="btn btn-xs btn-danger" id="addAmpClose" style="margin-left:3px;"><i class="fa fa-minus icon-white"></i></span></td></tr>';
        $(obj).prepend(row);
        return false;
    });
    $(document).on('click', '#addAmpClose', function () {
        $('#addEmpTr').remove();
        return false;
    });
    $(document).on('click', '.ampRowSave', function () {
        var logIn = $(this).parent().parent().find('#logIn').val();
        var logOut = $(this).parent().parent().find('#logOut').val();
        var date = $(this).data('date');
        var emp_id = $(this).data('emp_id');
        $.post(baseUrl + 'employees/addtime_ajax', {intime: logIn, outtime: logOut, empid: emp_id, indate: date}, function (resp) {
            if (resp > 0) {
                alert("Time details added successfully.");
                $("#searcEmployee").submit();
            } //else if (resp.indexOf("error: greater") >= 0) {
                //alert("In time should be smaller than Out time. Please try again.");
            //}
            else {
                alert("Error in inserting time details. Please try again!!");
            }
        });

    });

});

function show_image(pic) {
}

$.fn.centerToWindow = function () {
    var obj = $(this);
    var obj_width = $(this).outerWidth(true);
    var obj_height = $(this).outerHeight(true);
    var window_width = window.innerWidth ? window.innerWidth : $(window).width();
    var window_height = window.innerHeight ? window.innerHeight : $(window).height();
    obj.css({
        "position": "fixed",
        "top": ((window_height / 2) - (obj_height / 2)) + "px",
        "left": ((window_width / 2) - (obj_width / 2)) + "px"
    });
}


$.fn.drags = function (opt) {

    opt = $.extend({handle: "", cursor: "move"}, opt);

    if (opt.handle === "") {
        var $el = this;
    } else {
        var $el = this.find(opt.handle);
    }

    return $el.css('cursor', opt.cursor).on("mousedown",function (e) {
        if (opt.handle === "") {
            var $drag = $(this).addClass('draggable');
        } else {
            var $drag = $(this).addClass('active-handle').parent().addClass('draggable');
        }
        var z_idx = $drag.css('z-index'),
            drg_h = $drag.outerHeight(),
            drg_w = $drag.outerWidth(),
            pos_y = $drag.offset().top + drg_h - e.pageY,
            pos_x = $drag.offset().left + drg_w - e.pageX;
        $drag.css('z-index', 1000).parents().on("mousemove", function (e) {
            $('.draggable').offset({
                top: e.pageY + pos_y - drg_h,
                left: e.pageX + pos_x - drg_w
            }).on("mouseup", function () {
                $(this).removeClass('draggable').css('z-index', z_idx);
            });
        });
        e.preventDefault(); // disable selection
    }).on("mouseup", function () {
        if (opt.handle === "") {
            $(this).removeClass('draggable');
        } else {
            $(this).removeClass('active-handle').parent().removeClass('draggable');
        }
    });

}
