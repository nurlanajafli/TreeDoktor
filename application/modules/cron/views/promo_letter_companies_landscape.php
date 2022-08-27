<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
<!-- Web Font / @font-face : BEGIN -->
    <!-- NOTE: If web fonts are not required, lines 9 - 26 can be safely removed. -->

    <!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
    <!--[if mso]>
        <style>
            * {
                font-family: sans-serif !important;
            }
        </style>
    <![endif]-->

    <!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
    <!--[if !mso]><!-->
        <!-- insert web font reference, eg: <link href='http://loyegor.com/work/mail-template/css/fonts.css' rel='stylesheet' type='text/css'> -->
    <link href='http://loyegor.com/work/mail-template/css/fonts.css' rel='stylesheet' type='text/css'>
    <!--<![endif]-->

  <!-- Web Font / @font-face : END -->

    <!--[if (gte mso 9)|(IE)]>
    <style type="text/css">
        table {border-collapse: collapse !important;}
    </style>
    <![endif]-->
    <link href='http://loyegor.com/work/mail-template/css/styles.css' rel='stylesheet' type='text/css'>
    <style type="text/css">
        @media screen and (max-width: 400px) {
            .two-column .column {
                max-width: 100%!important;
            }
            .five-column .column {
                max-width: 50%!important;
            }
            .header .left{
                max-width: 100%!important;
            }
            .header .right{
                height: auto!important;
                max-width: 100%!important;
            }
            .footer span{
                display: block;
                padding-left: 0 !important;
            }
        }

        @media screen and (min-width: 401px) and (max-width: 620px) {
            .two-column .column {
                max-width: 49%!important;
            }
            .header .left{
                max-width: 100%!important;
            }
            .header .right{
                height: auto!important;
                max-width: 100%!important;
            }
        }
         body{
            width: 100%;
            background-color: #f0f2f5;
            margin:0;
            padding:0;
            -webkit-font-smoothing: antialiased;
            mso-margin-top-alt:0px; mso-margin-bottom-alt:0px; mso-padding-alt: 0px 0px 0px 0px;
        }

        p,h1,h2,h3,h4{
            margin-top:0;
            margin-bottom:0;
            padding-top:0;
            padding-bottom:0;
        }

        p{padding-bottom: 15px;}
        span.preheader{display: none; font-size: 1px;}

        html{
            width: 100%;
        }

        table{
            font-size: 14px;
            border: 0;
        }

        /* ----------- responsivity ----------- */
        @media only screen and (max-width: 640px){
            /*------ top header ------ */
            body[yahoo] .show{display: block !important;}
            body[yahoo] .hide{display: none !important;}

            /*----- main image -------*/
            body[yahoo] .main-image img{width: 440px !important; height: auto !important;}

            /* ====== divider ====== */
            body[yahoo] .divider img{width: 440px !important;}

            /*--------- banner ----------*/
            body[yahoo] .banner img{width: 440px !important; height: auto !important;}
            /*-------- container --------*/
            body[yahoo] .container590{width: 440px !important;}
            body[yahoo] .container580{width: 400px !important;}
            body[yahoo] .container1{width: 420px !important;}
            body[yahoo] .container2{width: 400px !important;}
            body[yahoo] .container3{width: 380px !important;}

            /*-------- secions ----------*/
            body[yahoo] .section-item{width: 440px !important;}
            body[yahoo] .section-img img{width: 440px !important; height: auto !important;}
        }

        @media only screen and (max-width: 479px){
            /*------ top header ------ */
            body[yahoo] .main-header{font-size: 24px !important;}
            body[yahoo] .resize-text{font-size: 20px !important;line-height: 20px!important;}

            /*----- main image -------*/
            body[yahoo] .main-image img{width: 280px !important; height: auto !important;}

            /* ====== divider ====== */
            body[yahoo] .divider img{width: 280px !important;}
            body[yahoo] .align-center{text-align: center !important;}


            /*-------- container --------*/
            body[yahoo] .container590{width: 280px !important;padding: 0 30px;}
            body[yahoo] .container580{width: 450px !important;}

            body[yahoo] .section-img img{width: 280px !important; height: auto !important;}

            /*------- CTA -------------*/
            body[yahoo] .cta-button{width: 200px !important;}
            body[yahoo] .cta-text{font-size: 15px !important;}
        }
    </style>
</head>
<body style="Margin:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;min-width:100%;background-color:#ffffff;" >
    <style type="text/css">
        @media screen and (max-width: 400px) {
            .two-column .column {
                max-width: 100%!important;
            }
            .five-column .column {
                max-width: 50%!important;
            }
            .header .left{
                max-width: 100%!important;
            }
            .header .right{
                height: auto!important;
                max-width: 100%!important;
            }
            .footer span{
                display: block;
                padding-left: 0 !important;
            }
        }

        @media screen and (min-width: 401px) and (max-width: 620px) {
            .two-column .column {
                max-width: 49%!important;
            }
            .header .left{
                max-width: 100%!important;
            }
            .header .right{
                height: auto!important;
                max-width: 100%!important;
            }
        }
    </style>

	<p style="font-size: 16px;">
		<?php echo $company; ?>,
	</p>

	<p style="font-size: 16px;">
		My name is Derek Lebow and I’m an Arborist Estimator and Project Coordinator at Tree Doctors.  I’m reaching out to you in hopes of building a referral partnership between our companies. We currently have thousands of clients in GTA from residential to commercial and we are very often asked to do landscaping services that we do not provide. We would like to find a landscaping company we can send these leads to and in return, help the landscaping company with large tree services that they currently can’t do.
	</p>

    <p style="font-size: 16px;">
        Here’s a list of the landscaping services we would like to refer to you:
    </p>
	<ul style="font-size: 16px; margin-top: 0;">
		<li>Tree and shrub planting</li>
		<li>Annual flower planting and design</li>
		<li>Landscape design, construction and installation</li>
		<li>Grass cutting, top-dressing and over-seeding, sodding</li>
		<li>Aerating and de-thatching of lawn areas</li>
        <li>Irrigation</li>
        <li>Complete Spring and Fall clean-up</li>
    </ul>
    
    <p style="font-size: 16px;">
        In return, Tree Doctors can offer you and your clients the following:
    </p>
    <ul style="font-size: 16px; margin-top: 0;">
        <li>Prune and remove trees too large for your landscaping crews</li>
        <li>Perform cabling and bracing for tree preservation</li>
        <li>Grind and remove tree stumps</li>
        <li>Provide condition and tree risk assessments for problem trees</li>
        <li>Arborist reports for landscape and construction</li>
        <li>Consultation services and plant health care management plans</li>
	</ul>

	<p style="font-size: 16px;">
		Tree Doctors can help you extend your landscape service offerings to include more comprehensive tree care, while helping you develop new business leads through our existing clients. Please let us know if this is something you would be interested in. We are happy to work with you to prioritize tree work so your clients get the most for their money, keeping trees safe and healthy for residents.
	</p>

	
	<div class="wrapper" style="width:100%;table-layout:fixed;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;" >
        <div class="webkit" style="max-width:600px;" >
            <!--[if (gte mso 9)|(IE)]>
            <table width="600" align="center" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
            <tr>
            <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
            <![endif]-->
            <table class="outer" align="center" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;Margin:0 auto;width:100%;max-width:600px;" >
                <tr>
                    <td class="header" dir="rtl" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;font-size:0;" >
                        <!--[if (gte mso 9)|(IE)]>
                        <table width="100%"  dir="ltr" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                        <tr>
                        <td width="420" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <table class="column right"  dir="ltr" style="border-spacing:0;font-family:AvenirNext-DemiBold, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;width:100%;vertical-align:middle;background-color:#5d863b;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;height:140px;display:inline-table; max-width: 180px; background: #5d863b; height: 140px; display: inline-table;" >
                            <tr>
                                <td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:center;font-family:AvenirNext-DemiBold, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#fff;font-size:20px;" >
                                    <p style="Margin:0;font-family:AvenirNext-DemiBold, Arial, 'Helvetica Neue', Helvetica, sans-serif" class="tenoff" ><span class="percent" style="font-size:42px;" >10%</span>&nbsp;<span class="sub" style="vertical-align:top;font-size:24px;" >OFF</span><br />
                                    ANY WINTER TREE SERVICE</p>
                                </td>
                            </tr>
                        </table>
                        <!--[if (gte mso 9)|(IE)]>
                        </td><td width="180" dir="ltr" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <table class="column left"  dir="ltr" style="border-spacing:0;font-family:AvenirNext-DemiBold, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;width:100%;display:inline-block;vertical-align:middle;max-width:420px;background-color:#5d863b;background-image:url('http://loyegor.com/work/mail-template/img/header-bg.jpg?ver=1.0.3');background-repeat:no-repeat;background-position:right top;background-attachment:scroll;height:140px; max-width: 420px; height: 140px;" >
                            <tr>
                                <td class="inner contents" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:AvenirNext-DemiBold, Arial, 'Helvetica Neue', Helvetica, sans-serif;font-size:25px;text-align:left;" >
                                    <p class="h1" style="Margin:0;Margin-left:41px;Margin-top:105px;Margin-bottom:0px;font-size:25px;font-weight:normal;" >WINTER</p>
                                </td>
                            </tr>
                        </table>
                        <!--[if (gte mso 9)|(IE)]>
                        </td>
                        </tr>
                        </table>
                        <![endif]-->
                    </td>
                </tr>
                <tr>
                    <td class="one-column sub-header" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <table width="100%" style="border-spacing:0;font-family:AvenirNext-DemiBold, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                            <tr>
                                <td class="inner contents" style="padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:left;font-family:AvenirNext-DemiBold, Arial, 'Helvetica Neue', Helvetica, sans-serif;padding-top:3px;" >
                                    <p class="h1" style="Margin:0;Margin-bottom:10px;Margin-left:30px;font-weight:normal;font-size:18px;" >IS A GREAT TIME FOR TREE SERVICES</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="two-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;font-size:18px;font-family:AvenirNext-Medium, Arial, 'Helvetica Neue', Helvetica, sans-serif;" >
                        <!--[if (gte mso 9)|(IE)]>
                        <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                        <tr>
                        <td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <div class="column" style="width:100%;max-width:290px;display:inline-block;vertical-align:top;" >
                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                <tr>
                                    <td class="icon-text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <!--[if (gte mso 9)|(IE)]>
                                        <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                        <tr>
                                        <td width="25" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn icon" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:25px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:0px;padding-left:0px;" >
                                                         <img src="http://loyegor.com/work/mail-template/img/ico-1.png?ver=1.0.1" class="ico" alt="" style="border-width:0;width:20px;height:20px;" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td><td width="255" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn text" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:255px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:left;" >
                                                        <p style="Margin:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;" >Cheaper Rates</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td>
                                        </tr>
                                        </table>
                                        <![endif]-->
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if (gte mso 9)|(IE)]>
                        </td><td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <div class="column" style="width:100%;max-width:290px;display:inline-block;vertical-align:top;" >
                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                <tr>
                                    <td class="icon-text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <!--[if (gte mso 9)|(IE)]>
                                        <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                        <tr>
                                        <td width="25" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn icon" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:25px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:0px;padding-left:0px;" >
                                                         <img src="http://loyegor.com/work/mail-template/img/ico-4.png?ver=1.0.1" class="ico" alt="" style="border-width:0;width:20px;height:20px;" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td><td width="255" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn text" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:255px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:left;" >
                                                        <p style="Margin:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;" >Faster scheduling times</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td>
                                        </tr>
                                        </table>
                                        <![endif]-->
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if (gte mso 9)|(IE)]>
                        </td><td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <div class="column" style="width:100%;max-width:290px;display:inline-block;vertical-align:top;" >
                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                <tr>
                                    <td class="icon-text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <!--[if (gte mso 9)|(IE)]>
                                        <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                        <tr>
                                        <td width="25" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn icon" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:25px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:0px;padding-left:0px;" >
                                                         <img src="http://loyegor.com/work/mail-template/img/ico-2.png?ver=1.0.1" class="ico" alt="" style="border-width:0;width:20px;height:20px;" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td><td width="255" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn text" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:255px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:left;" >
                                                        <p style="Margin:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;" >Plants are dormant. This means pruning
                                                            wounds won’t be exposed to insects or
                                                            disease, allowing for optimal tree health</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td>
                                        </tr>
                                        </table>
                                        <![endif]-->
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if (gte mso 9)|(IE)]>
                        </td><td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <div class="column" style="width:100%;max-width:290px;display:inline-block;vertical-align:top;" >
                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                <tr>
                                    <td class="icon-text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <!--[if (gte mso 9)|(IE)]>
                                        <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                        <tr>
                                        <td width="25" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn icon" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:25px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:0px;padding-left:0px;" >
                                                         <img src="http://loyegor.com/work/mail-template/img/ico-5.png?ver=1.0.1" class="ico" alt="" style="border-width:0;width:20px;height:20px;" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td><td width="255" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn text" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:255px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:left;" >
                                                        <p style="Margin:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;" >Easier to assess tree structure and
                                                            identify dead and dangerous branches
                                                            avoiding winter damage</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td>
                                        </tr>
                                        </table>
                                        <![endif]-->
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if (gte mso 9)|(IE)]>
                        </td><td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <div class="column" style="width:100%;max-width:290px;display:inline-block;vertical-align:top;" >
                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                <tr>
                                    <td class="icon-text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <!--[if (gte mso 9)|(IE)]>
                                        <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                        <tr>
                                        <td width="25" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn icon" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:25px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:0px;padding-left:0px;" >
                                                         <img src="http://loyegor.com/work/mail-template/img/ico-3.png?ver=1.0.1" class="ico" alt="" style="border-width:0;width:20px;height:20px;" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td><td width="255" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn text" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:255px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:left;" >
                                                        <p style="Margin:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;" >Minimal damage to lawns and
                                                            plants, better access to backyards</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td>
                                        </tr>
                                        </table>
                                        <![endif]-->
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if (gte mso 9)|(IE)]>
                        </td><td width="50%" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                        <![endif]-->
                        <div class="column" style="width:100%;max-width:290px;display:inline-block;vertical-align:top;" >
                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                <tr>
                                    <td class="icon-text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <!--[if (gte mso 9)|(IE)]>
                                        <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                        <tr>
                                        <td width="25" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn icon" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:25px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:0px;padding-left:0px;" >
                                                        <img src="http://loyegor.com/work/mail-template/img/ico-6.png?ver=1.0.1" class="ico" alt="" style="border-width:0;width:20px;height:20px;" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td><td width="255" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                        <![endif]-->
                                        <div class="icolumn text" style="text-align:center;font-size:18px;display:inline-block;vertical-align:top;width:100%;max-width:255px;" >
                                            <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                                <tr>
                                                    <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;text-align:left;" >
                                                        <p style="Margin:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;" >Better crew availability</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!--[if (gte mso 9)|(IE)]>
                                        </td>
                                        </tr>
                                        </table>
                                        <![endif]-->
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if (gte mso 9)|(IE)]>
                        </td>
                        </tr>
                        </table>
                        <![endif]-->
                    </td>
                </tr>
                <tr>
                <td class="five-column" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;font-size:18px;" >
                    <!--[if (gte mso 9)|(IE)]>
                    <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                    <tr>
                    <td width="110" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                    <![endif]-->
                    <table class="column" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;width:110px;display:inline-block;vertical-align:top;" >
                        <tr>
                            <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
                                <table class="contents" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                    <tr>
                                        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/houzz.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/5-stars.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                    </td><td width="110" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                    <![endif]-->
                    <table class="column" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;width:110px;display:inline-block;vertical-align:top;" >
                        <tr>
                            <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
                                <table class="contents" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                    <tr>
                                        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/homestars.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/4-5-stars.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                    </td><td width="110" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                    <![endif]-->
                    <table class="column" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;width:110px;display:inline-block;vertical-align:top;" >
                        <tr>
                            <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
                                <table class="contents" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                    <tr>
                                        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/bbb.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/5-stars.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                    </td><td width="110" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                    <![endif]-->
                    <table class="column" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;width:110px;display:inline-block;vertical-align:top;" >
                        <tr>
                            <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
                                <table class="contents" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                    <tr>
                                        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/yp.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/5-stars.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                    </td><td width="110" valign="top" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                    <![endif]-->
                    <table class="column" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;width:110px;display:inline-block;vertical-align:top;" >
                        <tr>
                            <td class="inner" style="padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >
                                <table class="contents" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                                    <tr>
                                        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/google.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;" >
                                            <img src="http://loyegor.com/work/mail-template/img/4-5-stars.png?ver=1.0.1" alt="" style="border-width:0;width:110px;" />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                    </td>
                    </tr>
                    </table>
                    <![endif]-->
                </td>
            </tr>
            <tr>
                <td class="one-column footer" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;background-color:#5d863b;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;height:50px;" >
                    <table width="100%" style="border-spacing:0;font-family:AvenirNextLTPro-Regular, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#474a5d;" >
                        <tr>
                            <td class="inner contents" style="padding-top:10px;padding-bottom:10px;padding-right:30px;padding-left:30px;font-family:AvenirNext-Medium, Arial, 'Helvetica Neue', Helvetica, sans-serif;color:#fff;text-align:left;" >
                                <span style="color:#fff;padding-right:20px;display:inline-block;" ><a style="color:#fff;" href="https://maps.google.com/?q=343+Olivewood+Rd+Toronto+ON+M8Z+2Z6" target="_blank">343 Olivewood Rd. Toronto M8Z 2Z6</a></span><span style="padding-right:20px;display:inline-block;" ><a style="color:#fff;" href="tel:+14162018000">(416) 201-8000</a></span><span style="padding-right:20px;display:inline-block;color:#fff;" ><a style="color:#fff;" href="mailto:info@treedoctors.ca" target="_blank">info@treedoctors.ca</a></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="one-column signature" style="padding-top:20px;padding-bottom:0;padding-right:0;padding-left:0;background-color:#fff;background-image:none;background-repeat:repeat;background-position:top left;background-attachment:scroll;" >
                    <?php echo $signature; ?>
                </td>
            </tr>
           

            </table>
            <!--[if (gte mso 9)|(IE)]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </div>
    </div>
</body>
</html>
