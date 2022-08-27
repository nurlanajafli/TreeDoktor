<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Trees & Pests</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Trees & Pests
			<div class="col-sm-3 pull-right" style="margin-top: -6px;">
				<div id="container">
					<div style="text-align: center;">
						<form method="post" action="<?php echo base_url('info/search'); ?>">
							<div class="input-group">
								<input type="text" name="search" id="search_box" class='search_box input-sm form-control'>
							<span class="input-group-btn">
								<button class="btn btn-sm btn-default search_button" type="submit" value="Go">Go</button>
							</span>
							</div>
						</form>
					</div>
				</div>
			</div>
		<!--
			<a href="#add_tree" class="btn btn-xs btn-success btn-mini pull-right" role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
			
		-->
		</header>
		<div class="table-responsive" id="tableTrees">
			<?php $this->load->view('table_trees'); ?>
		</div>
	</section>
</section>
 <script type="text/javascript">

$(function() {

	$(".search_button").click(function() {
		// получаем строку, которую ввел пользователь
		var searchString    = $("#search_box").val();
		// формируем поисковый запрос
		var data = 'search='+ searchString;
		// если введенная информация не пуста
		// вызов ajax
		$.ajax({
			type: "POST",
			global:false,
			dataType: 'json',
			url: baseUrl + 'info/search',
			data: data,
			beforeSend: function(html) { // действие перед отправлением
				$("#tableTrees").html('');
				$(".word").html(searchString);
		   },
		   success: function(html){ // действие после получения ответа
				console.log(html.html);
				$("#tableTrees").append(html.html);
		  }
		});
		return false;
	});
});
</script>
<?php $this->load->view('add_tree_modal'); ?>
<?php $this->load->view('includes/footer'); ?>
