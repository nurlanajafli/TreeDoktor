<!DOCTYPE html>
<html class="app js no-touch no-android chrome no-firefox no-iemobile no-ie no-ie10 no-ie11 no-ios">
<head>
	<meta charset="utf-8">
	<title>Thank You - <?php echo brand_name(isset($brand_id)?$brand_id:0); ?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/animate.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css'); ?>"
	      type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/modules/brands/brands.css'); ?>">
	<script src="<?php echo base_url('assets/js/jquery.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap.js'); ?>"></script>
	<script src="<?php echo base_url('assets/js/common.js?v='.config_item('js_common')); ?>"></script>
</head>
<body class="" style="overflow: auto;">
<section id="content">
	<div class="row m-n">
		<div class="col-sm-8 col-sm-offset-1">
			<div class="m-b-lg col-6 col-lg-8 col-sm-offset-3">
<!--				<h3 class="text-badge animated fadeInDownBig" style="text-align: left">-->
					<?php if($like) : ?>
                        <?php if(isset($promoLinks) && !empty($promoLinks)) :
                            echo $promoLinks['br_like_message']?>
                    <?php endif; else : ?>
                        <?php if(isset($promoLinks) && !empty($promoLinks)) :
                            echo $promoLinks['br_dislike_message']?>
					<?php endif; endif; ?>
					<span class="block clear"></span>
					<span class="block pull-right m-t-md text-center">
                        <?php if(config_item('thank_you_page_sign')) : ?>
                            <?php echo config_item('thank_you_page_sign'); ?>
                        <?php endif; ?>
					</span>
					<span class="block clear"></span>
<!--				</h3>-->
			</div>
			<div class="col-6 col-lg-8 list-group p-n m-b-lg col-sm-8 col-sm-offset-3">
				<form method="POST" data-type="ajax" data-callback="<?php if($like) : ?>thankyou<?php else : ?>gotoblog<?php endif; ?>">
					<div class="form-group">
					  <textarea class="form-control" name="feedback" placeholder="Type Your Feedback" style="height: 150px;" data-toggle="tooltip" data-placement="top" title="" data-original-title=""></textarea>
					</div>
					<button type="submit" class="btn btn-big btn-default w-100">Submit</button>
				</form>
			</div>
			<div class="col-lg-8 list-group p-n bg-white m-b-lg col-sm-8 col-6 col-sm-offset-3">
				<a href="<?php echo brand_site_http(isset($brand_id)?$brand_id:0); ?>" class="list-group-item" style="text-decoration: none;" target="_blank">
					<i class="fa fa-chevron-right icon-muted"></i>
					<i class="fa fa-fw fa-home icon-muted"></i> Goto <?php echo brand_site(isset($brand_id)?$brand_id:0); ?>
				</a>
				<a href="mailto:<?php echo brand_email(isset($brand_id)?$brand_id:0); ?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<i class="fa fa-fw fa-question icon-muted"></i> Contact Us
				</a>
				<a href="tel:<?php echo brand_phone(isset($brand_id)?$brand_id:0, true); ?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<span class="badge"><?php echo brand_phone(isset($brand_id)?$brand_id:0); ?></span>
					<i class="fa fa-fw fa-phone icon-muted"></i> Call Us
				</a>
			</div>
		</div>
	</div>
    <?php $this->load->view('includes/partials/preloader_modal'); ?>

	<?php if($like) : ?>
	<div class="modal modal-static fade" id="thankyou-modal" role="dialog" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
                        <?php if(isset($promoLinks) && !empty($promoLinks)) : ?>
                            <?= $promoLinks['br_header'];
                            else: ?>
                                <div class="text-success h3 text-center">
						            Thank you for your review! We need your help. A lot of your neighbors don't know about us, but you can change that! Please take a moment to copy and paste your review to our Yelp or Homestars pages, linked below. Thank You!
                                </div>
                            <?php endif; ?>
					<div class="font-bold m-t-sm">Your Review:</div>
					<div id="review" class="m-t-sm h5"></div>
					<div class="m-t-sm text-center">
<!--					--><?php //$promoLinks = $this->config->item('promotion_links'); ?>
						<?php if(isset($promoLinks) && !empty($promoLinks) && !empty($promoLinks['reviews'])) : ?>
							<?php foreach($promoLinks['reviews'] as $k=>$v) : ?>
                                <?php if(!isset($v['brl_link'], $v['brl_name'])) continue; ?>
								<a target="_blank" href="<?php echo $v['brl_link']; ?>" class="btn btn-danger m-top-5" style="width: 290px;"><?php echo $v['brl_name']; ?></a>
								<?php if(isset($promoLinks[$k + 1])) : ?>
									<br><br>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</section>
<!-- footer -->
<footer id="footer">
	<div class="text-center padder clearfix">
		<p>
			<small><?php echo brand_name(isset($brand_id)?$brand_id:0, true); ?>. All Rights Reserved.<br>© <?php echo date('Y'); ?></small>
		</p>
	</div>
</footer>
<script>
	<?php if($like) : ?>
	function thankyou(response) {
		if(response.thankyou) {
			$('#review').text(response.feedback);
			$('#thankyou-modal').modal();
		}
	}
	<?php else : ?>
	function gotoblog() {
		$('#processing-modal').modal();
		location.href = '<?php echo brand_site_http(isset($brand_id)?$brand_id:0); ?>';
	}
	<?php endif; ?>
</script>
</body></html>
