/**
 * Get email from Gmail account
 * moved from client_billing_details.php
 * not used now!
 *
 *      <a href="#getEmails" id="clientEmails" class="btn btn-block btn-primary" data-toggle="modal" data-backdrop="true" data-keyboard="true" style="margin-top: 10px;overflow: hidden;text-overflow: ellipsis;">
 *          Emails(from/to) <?php echo $client_data->client_name; ?>
 *      </a>
 *
 *      <script src="https://apis.google.com/js/client.js"></script>
 */
var inMessages = {};
var outMessages = {};

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

var extractField = function(json, fieldName) {
    //if(typeof(json.payload.headers) == undefined)
    //return false;
    return json.payload.headers.filter(function(header) {
        return header.name === fieldName;
    })[0].value;
};

/*$('#clientEmails').on("click", function (){
    inMessages = {};
    outMessages = {};
    fromQ = '<?php //echo $fromQ; ?>';
    toQ = '<?php //echo $toQ; ?>';
    if(!fromQ)
        $('#inbox').html('<p class="text-center m-t-sm"><em class="h4 text-mute">Incorrect Email Address</em></p>');
    if(!toQ)
        $('#outbox').html('<p class="text-center m-t-sm"><em class="h4 text-mute">Incorrect Email Address</em></p>');
    if(!fromQ && !toQ)
    {
        var showed = '#inbox';
        if(location.hash == '#outbox')
            showed = location.hash;
        $(showed).fadeIn('slow');
    }
    if(fromQ || toQ)
    {
        $('#inbox').css('display', 'none').html('');
        $('#outbox').css('display', 'none').html('');
        $.ajax({
            type: 'POST',
            url: baseUrl + 'clients/ajax_access_token',
            global: false,
            success: function(response){
                setTimeout(function(){
                    $('#processing-modal').modal();
                    gapi.auth.setToken(response);
                    gapi.client.load('gmail', 'v1').then(function(){
                        getList(fromQ, $('#inbox'));
                        getList(toQ, $('#outbox'));
                    });
                }, 200);
            },
            dataType: 'json'
        });
    }
});*/

function getList(q, selector) {
    var request = gapi.client.gmail.users.messages.list({
        'userId': 'me',
        'q': q
    });

    request.execute(function(resp) {
        if(!resp.resultSizeEstimate)
        {
            $(selector).html('<p class="text-center m-t-sm"><em class="h4 text-mute">No Recors Found</em></p>');
            var showed = '#inbox';
            if(location.hash == '#outbox')
                showed = location.hash;
            $(showed).fadeIn('slow');
            $('#processing-modal').modal('hide');
        }
        else
        {
            $.each(resp.messages, function(key, val){
                getMessage(val.id, selector, resp.messages);
            });
        }
    });
}
function getMessage(messageId, selector, msgList) {
    var request = gapi.client.gmail.users.messages.get({
        'userId': 'me',
        'id': messageId,
        'format': 'full',
    });

    request.execute(function(resp) {
        var parts;
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        var html = '';
        var snippet = '';
        var part = {};
        var attach = getAttachments('me', resp.payload.parts);
        var date = extractField(resp, "Date");
        var subject = extractField(resp, "Subject");
        if(resp.payload.parts)
            var part = resp.payload.parts.filter(function(part) {
                return part.mimeType == 'text/html';
            });
        if(typeof(resp.snippet) != 'undefined')
            snippet = resp.snippet;
        if(typeof(part[0]) != 'undefined')
            html = decodeURIComponent(escape((atob(part[0].body.data.replace(/\-/g, '+').replace(/\_/g, '/')))));
        else
        {
            if(typeof(resp.payload.parts) == 'undefined')
                html = decodeURIComponent(escape((atob(resp.payload.body.data.replace(/\-/g, '+').replace(/\_/g, '/')))));
            else
            {
                var part = resp.payload.parts[0].parts.filter(function(part) {
                    return part.mimeType == 'text/html';
                });
                if(typeof(part[0]) != 'undefined')
                    html = decodeURIComponent(escape((atob(part[0].body.data.replace(/\-/g, '+').replace(/\_/g, '/')))));
            }
        }

        html = html.replace(/<img.*>/g, '');

        var message = {body:html, date:date, subject:subject, snippet:snippet, attach:null};

        if(attach.length)
            message.attach = attach;

        if($(selector).is('#inbox'))
            inMessages[messageId] = message;
        else
            outMessages[messageId] = message;

        html = '<div class="panel panel-default" data-date="' + new Date(message.date).getTime() + '">';
        html += '<div class="panel-heading">';
        html += '<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#inbox" href="#collapse-' + messageId + '">';
        html += '<article class="media"><div class="media-body">';
        html += '<div class="pull-right media-xs text-center text-muted"><strong class="h4">' + new Date(message.date).getDate() + '</strong><br>';
        html += '<small class="label bg-success" style="  display: inline-block;line-height: 15px;">' + monthNames[new Date(message.date).getMonth()];
        html += '<br>' + (new Date(message.date).getYear() + 1900) + '</small></div>';
        html += '<span class="h4 text-success">' + message.subject + '</span>';
        html += '<small class="block m-t-sm">' + message.snippet + '</small>';
        html += '</div></article>';
        html += '</a></div>';
        html += '<div id="collapse-' + messageId + '" class="panel-collapse collapse" style="height: 0px;"><div class="panel-body text-sm">';
        html += message.body;
        if(message.attach)
        {
            $.each(message.attach, function(key, val){
                html += '<div class="label bg-primary ui-draggable m-b-xs" style="display: inline-block; height: 20px; line-height: 15px;"><a target="_blank" title="Download File" href="' + baseUrl + 'clients/attach_download/' + val.attachId + '/' + messageId + '/' + btoa(unescape(encodeURIComponent(val.attachName))).replace(/\+/g, '-').replace(/\//g, '_') +  '">'+ val.attachName +'</a></div><br>';
            });
        }
        html += '</div></div></div>';

        selector.append(html);

        if($('#inbox').find(".panel.panel-default").length == Object.size(inMessages) && $('#outbox').find(".panel.panel-default").length == Object.size(outMessages))
        {
            $('#inbox').find('style').remove();
            $('#outbox').find('style').remove();
            var myArray = $('#inbox').find(".panel.panel-default");
            myArray.sort(function (a, b) {
                a = parseInt($(a).data("date"), 10);
                b = parseInt($(b).data("date"), 10);
                if(a < b) {
                    return 1;
                } else if(a > b) {
                    return -1;
                } else {
                    return 0;
                }
            });
            $('#inbox').append(myArray);
            var myArray = $('#outbox').find(".panel.panel-default");
            myArray.sort(function (a, b) {
                a = parseInt($(a).data("date"), 10);
                b = parseInt($(b).data("date"), 10);
                if(a < b) {
                    return 1;
                } else if(a > b) {
                    return -1;
                } else {
                    return 0;
                }
            });
            $('#outbox').append(myArray);
            var showed = '#inbox';
            if(location.hash == '#outbox')
                showed = location.hash;
            $(showed).fadeIn('slow');
            $('#processing-modal').modal('hide');
        }
    });
}

function getAttachments(userId, message, callback) {
    var parts = message;
    var attachments = [];

    if(message)
    {
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i];
            if (part.filename && part.filename.length > 0) {
                attachments.push({attachName:part.filename,attachId:part.body.attachmentId});
            }
        }
    }
    return attachments;
}

var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
