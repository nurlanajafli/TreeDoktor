function funonload() {
    var text = $("#accessToken").text();
    if (text.trim() === "\"No Access Token Generated Yet\"") {
        $("#export").addClass("disabled");
        $("#import").addClass("disabled");
    }
};

function importData() {
    $("#import").addClass("disabled");
    $.ajax({
        type: "GET",
        url: "import"
    });
}

function exportData() {
    $("#export").addClass("disabled");
    $.ajax({
        type: "GET",
        url: "export"
    });
}

function exportDatav2(obj) {
    $("#sync").toggleClass("height");
    $.ajax({
        type: "POST",
        url: "exportV2",
        data: "module="+obj.id
    });
}
function importDatav2(obj) {
    $("#sync").toggleClass("height");
    $.ajax({
        type: "POST",
        url: "importV2",
        data: "module="+obj.id
    });
}


var apiCall = function () {
    this.getCompanyInfo = function () {
        /*
        AJAX Request to retrieve getCompanyInfo
         */
        return $.ajax({
            type: "GET",
            url: "makeAPICall"
        }).done(function (msg) {
            $('#apiCall').html(msg);
        });
    }

    this.refreshToken = function () {
        $.ajax({
            type: "POST",
            url: "refreshToken.php",
        }).done(function (msg) {

        });
    }
}


var apiCall = new apiCall();
window.onload = funonload;