
<style type="text/css">
    .error-heading{
        margin-top: 120px;
        font-size: 80px;
        font-weight: 300;
        text-shadow: 0 1px 0 #d9d9d9, 0 2px 0 #d0d0d0, 0 5px 10px rgba(0, 0, 0, 0.125), 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .error-message{
        color: #828282;
        text-shadow: 0 1px 0 #d9d9d9, 0 2px 0 #d0d0d0, 0 5px 10px rgba(0, 0, 0, 0.125), 0 10px 20px rgba(0, 0, 0, 0.2);
    }
</style>
<section id="content">
    <div class="row m-n">
        <div class="col-sm-6 col-sm-offset-3">
            <div class="text-center m-b-lg">
                <h1 class="h1 text-white animated fadeInDownBig error-heading"><?php echo $heading; ?></h1>
                <br>
                <br>
                <h2 class="h1 animated fadeInRightBig error-message"><span class="glyphicon glyphicon-tree-deciduous" style="margin-right:20px; color: #81ba53;"></span><span style="display: inline-block; border-bottom: 1px solid #81ba53;"><?php echo $message; ?></span></h2>
            </div>

            <?php /*
            <div class="list-group m-b-sm bg-white m-b-lg">
              <a href="index.html" class="list-group-item">
                <i class="fa fa-chevron-right icon-muted"></i>
                <i class="fa fa-fw fa-home icon-muted"></i> Goto homepage
              </a>
              <a href="#" class="list-group-item">
                <i class="fa fa-chevron-right icon-muted"></i>
                <i class="fa fa-fw fa-question icon-muted"></i> Send us a tip
              </a>
              <a href="#" class="list-group-item">
                <i class="fa fa-chevron-right icon-muted"></i>
                <span class="badge">021-215-584</span>
                <i class="fa fa-fw fa-phone icon-muted"></i> Call us
              </a>
            </div>
            */ ?>
        </div>
    </div>
  </section>

<?php /*
<div id="container">

    <h1 class="text-center"><?php echo $heading; ?></h1>
    <p class="text-center" style="font-size: 18px"><?php echo $message; ?></p>
</div>
*/ ?>