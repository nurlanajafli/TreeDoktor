<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" >
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" >
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
	
html
{
  height:100%;
  width:100%;
}
body{    
  background:url('../assets/img/jurassic-coast-1089035_1920.jpg') no-repeat;
  background-size: cover;
  height:100%;

}

.form-top{
  margin-top: 30px;
}
.panel{
  box-shadow: 0 1px 6px 0 rgba(0,0,0,.12), 0 1px 6px 0 rgba(0,0,0,.12);
  border-radius: 6px;
    border: 0;
}
@-moz-document url-prefix() {
    .form-control{
      height: auto;
    }
}
.panel-primary{
  background-color: #2c3e50;
  color: #fff;
}
.panel-primary>.panel-heading {
    color: #fff;
    font-size: 20px;
    background-color: #2c3e50;
    border-color: #2c3e50;
}
.btn-warning{
  background-color: transparent;
  border-color: #bdc3c7;
}


.mce-tinymce{
	border-width: 2px;
}

	
	</style>
<div class="container form-top">
		<div class="row">
			<div class="col-md-6 col-md-offset-3 col-sm-12 col-xs-12">
				<div class="panel panel-danger">
					<div class="panel-body">
						<form id="reused_form" action="<?php echo base_url('cron/sendEmail'); ?>" method="post" enctype="multipart/form-data">
						 	<div class="form-group">
						 		<label><i class="fa fa-user" aria-hidden="true"></i> Subject</label>
						 		<input type="text" name="name" class="form-control" placeholder="Enter Name">
						 	</div>
						 	<div class="form-group">
						 		<label><i class="fa fa-envelope" aria-hidden="true"></i> To</label>
						 		<input type="email" name="email" class="form-control" placeholder="Enter Email">
						 	</div>
						 	<div class="form-group">
						 		<label><i class="fa fa-comment" aria-hidden="true"></i> Message</label>
						 		<textarea rows="3" name="message" class="form-control template_text" placeholder="Type Your Message"></textarea>
						 	</div>

													 	<div class="form-group">
						 		<label><i class="fa fa-upload" aria-hidden="true"></i>Upload Your Files</label>
						 		<input type="file" name="image[]" class="form-control" multiple="" >
						 	</div>

						 	<div class="form-group">
						 		<button class="btn btn-raised btn-block btn-danger">Send →</button>
						 	</div>
						</form>
						<div id="error_message" style="width:100%; height:100%; display:none; ">
						<h4>Error</h4>
						Sorry there was an error sending your form.
						</div>
						<div id="success_message" style="width:100%; height:100%; display:none; ">
						<h2>Success! Your Message was Sent Successfully.</h2>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<!-- Form Ended -->
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
<script>
    Common.initTinyMCE('.template_text');
</script>
