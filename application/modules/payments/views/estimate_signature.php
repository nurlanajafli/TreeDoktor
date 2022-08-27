<?php //echo(json_encode($estimate)); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <?php if (!empty($estimate)) : ?>
        <title>Online <?php echo config_item('estimate_label') ?: 'Estimate'; ?> Signature</title>
    <?php endif; ?>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/css/bootstrap.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/css/app.css?v=<?php echo config_item('app.css'); ?>">
    <link rel="stylesheet"
          href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/estimate_payment.css"
          type="text/css">
    <script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/libs/currency.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/vendors/notebook/js/bootstrap.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/jquery.inputmask.bundle.js"></script>

</head>
<?php
$default_img = 'assets/'.$this->config->item('company_dir').'/print/header.png';
$brand_id = get_brand_id(isset($estimate)?$estimate:[], isset($client_data)?$client_data:[]);
$payment_logo = get_brand_logo($brand_id, 'payment_logo_file', $default_img);
?>
<body class="col-md-12" style="overflow-y:auto;">

<div class="col-lg-4"></div>
<div class="m-t-sm filled_white overflow col-lg-4 payment-conteiner"
     style="margin-top: 4%!important; margin-bottom: 4%!important; box-shadow: 0 0 10px rgba(0,0,0,0.5);">


    <div class="col-sm-12 holder text-center"><img
                src="<?php echo $payment_logo; ?>"
                width="100%" style="<?php echo $this->config->item('payment_logo_styles'); ?>;width: 70%; float: none;"></div>
    <div class="clear"></div>
    <div class="estimate_number">

        <div class="h3 text-center">
            <?php if (isset($estimate) && !empty($estimate)) : ?>
                <div class="estimate_number_1"><?php echo config_item('estimate_label') ?: 'Estimate'; ?> #</div>
                <div class="estimate_number_2">
                    <a target="_blank" class="text-ul"
                       href="<?php echo base_url('payments/estimate/' . md5($estimate->estimate_no . $estimate->client_id)); ?>"><?php echo $estimate->estimate_no; ?></a>
                </div>
            <?php endif; ?>
            <div class="clear"></div>
        </div>
    </div>


    <div class="title">Client Information</div>
    <div class="col-md-12 p-n">
        <div class="data">
            <table cellpadding="0" cellspacing="0" border="0" class="client_table">
                <tr>
                    <td>Client:</td>
                    <td><?php echo $client_data->client_name; ?></td>
                </tr>
                <tr>
                    <td>Client Address:</td>
                    <td><?php echo $client_data->client_address . ", " . $client_data->client_city . " " . $client_data->client_state . " " . $client_data->client_zip; ?></td>
                </tr>
                <tr>
                    <td>Client Phone:</td>
                    <td><?php echo numberTo($client_contact['cc_phone']); ?></td>
                </tr>
                <?php if ($client_contact['cc_email']) : ?>
                    <tr>
                        <td>Client Email:</td>
                        <td><?php echo $client_contact['cc_email']; ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($client_data->client_address != $lead_data->lead_address) : ?>
                    <tr>
                        <td>Job Site Location:</td>
                        <td><?php echo $lead_data->lead_address . " " . $lead_data->lead_city; ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($client_data->client_name != $client_contact['cc_name']) : ?>
                    <tr>
                        <td>Job Site Contact:</td>
                        <td><?php echo $client_contact['cc_name']; ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <div class="col-md-12 p-n">
        <div class="title">Signature</div>

        <?php if($estimate->status == 6): ?>
            <div class="h1 text-center text-success m-left-50">
                <strong><?php echo config_item('estimate_label') ?: 'Estimate'; ?> Сonfirmed</strong>
            </div>
        <?php else: ?>
            <div class="canvas">
                <div class=" text-center m-left-50">
                    <canvas id="myCanvas" style=" border-radius: 30px; border: 2px solid #ebebe7">
                                    Your browser does not support Canvas
                    </canvas>
                </div>
                <div class="text-center m-left-50 m-bottom-20 m-top-10">
                    <input type="button" class="btn btn-warning" value="Clear" id="clear">
                    <input type="button" class="btn btn-success" value="Confirm <?php echo config_item('estimate_label') ?: 'Estimate'; ?>" id="confirmed" disabled>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if($estimate->status == 6): ?>
    <div class="col-md-12 text-center">
        <h3 class="m-left-50">Thank you for your business. We will contact you shortly</h3>
    </div>
    <?php endif; ?>
    <input type="hidden" class="estimate_id" value="<?= !empty($hash) ? $hash : null ?>" />
    <div class="clear"></div>
</div>

<script>
    let canvas = document.getElementById("myCanvas"),
        context = canvas.getContext("2d");

    let mouse = { x:0, y:0};
    let draw = false;
    let clear = document.getElementById("clear");
    let confirmed = document.getElementById("confirmed");
    let baseUrl = '<?php echo base_url();?>';

    setCanvasBackground();
    let mouseDown = function(e){
/*        context.font = '25px Arial';
        context.textAlign = 'center';
        context. textBaseline = 'middle';
        context.fillStyle = '#ebebe7';  // a color name or by using rgb/rgba/hex values
        context.fillText('Sign Here', canvas.width/2, canvas.height/2); // text and position*/
        /*if(1 == 1){
            context.clearRect(0, 0, canvas.width, canvas.height);
            setCanvasBackground();
        }*/
       /* e.preventDefault();
        if(confirmed.getAttribute('disabled') !== null) {
            context.clearRect(0, 0, canvas.width, canvas.height);
            setCanvasBackground();
        }*/

        mouse.x = e.offsetX;
        mouse.y = e.offsetY;
        draw = true;

        context.beginPath();
        context.moveTo(mouse.x, mouse.y);
    };

    let mouseMove = function(e){
        e.preventDefault();
        e.stopPropagation();
        if(draw==true){
            if(confirmed.getAttribute('disabled') !== null) {
                confirmed.removeAttribute('disabled');
                context.clearRect(0, 0, canvas.width, canvas.height);
                setCanvasBackground();
            }
            mouse.x = e.offsetX;
            mouse.y = e.offsetY;
            context.lineTo(mouse.x, mouse.y);
            context.stroke();
        }
    };

    let mouseUp = function(e){
        mouse.x = e.offsetX;
        mouse.y = e.offsetY;
        context.lineTo(mouse.x, mouse.y);
        context.stroke();
        context.closePath();
        draw = false;
    };
    canvas.addEventListener("mousedown", mouseDown);
    canvas.addEventListener("mousemove", mouseMove);
    canvas.addEventListener("mouseup", mouseUp);

    canvas.addEventListener('touchstart', mouseDown, false);
    canvas.addEventListener('touchmove', mouseMove, false);
    canvas.addEventListener('touchend', mouseUp, false);

    canvas.addEventListener("touchmove", function (e) {
        let touch = e.changedTouches[0];
        let mouseEvent = new MouseEvent("mousemove", {
            'view': e.target.ownerDocument.defaultView,
            'bubbles': true,
            'cancelable': true,
            'screenX':touch.screenX,  // get the touch coords
            'screenY':touch.screenY,  // and add them to the
            'clientX':touch.clientX,  // mouse event
            'clientY':touch.clientY,
        });
        canvas.dispatchEvent(mouseEvent);
        touch.target.dispatchEvent(mouseEvent);
    }, false);

    clear.addEventListener('click', function () {
        confirmed.setAttribute('disabled', 'disabled');
        context.clearRect(0, 0, canvas.width, canvas.height);
        setCanvasBackground();
    });

    confirmed.addEventListener('click', function () {
        confirmed.setAttribute('disabled', 'disabled');
        clear.setAttribute('disabled', 'disabled');
        let signature = canvas.toDataURL();
        let estimate_id = $('.estimate_id').val();
        $.ajax({
            url: baseUrl + 'payments/sign_estimate',
            data: {signature: signature, estimate_id: estimate_id, is_web: true},
            method: "POST",
            success: function(resp){
                if (resp.status === true) {
                    location.reload();
                } else {
                    alert(resp.message);
                }
                return false;
            },
            dataType: 'json'
        });
    });

    function setCanvasBackground() {
        // change non-opaque pixels
        let imgData=context.getImageData(0,0,canvas.width,canvas.height);
        let data=imgData.data;
        for(let i=0;i<data.length;i+=4){
            if(data[i+3]<255){
                data[i]=235;
                data[i+1]=235;
                data[i+2]=231;
                data[i+3]=255;
            }
        }
        context.putImageData(imgData,0,0);

       // var canvas = document.getElementById('myCanvas');
        if(confirmed.getAttribute('disabled') !== null){
            context.font = '48px Arial';
            context.textAlign = 'center';
            context. textBaseline = 'middle';
            context.fillStyle = 'green';  // a color name or by using rgb/rgba/hex values
            context.fillText('Sign Here', canvas.width/2, canvas.height/2); // text and position
        }
    }
</script>
</html>
