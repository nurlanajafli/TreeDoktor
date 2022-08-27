<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<style>.sortable-placeholder {
		min-height: 54px;
	}</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Products</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Products
			<a href="#product-modal-new" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false">New Product <i class="fa fa-plus"></i></a>
            <a style="margin-right: 10px;" href="#category-modal" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
               data-backdrop="static" data-keyboard="false">New Category <i class="fa fa-plus"></i></a>
		</header>

		<div class="table-responsive">
            <?php 
            /*
            foreach ($products as $product) : ?>
                <?php $this->load->view('partials/service_modal', ['service' => $product]); ?>
            <?php endforeach;
            */
            ?>
		<ul class="list-group gutter list-group-lg list-group-sp sortable">
			<?php if(!isset($products) || !count($products)): ?>
				<li class="clear list-group-item">
					<div class="col-md-12 text-center">Products list is empty<br><a href="#product-modal-new" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false">Create product</a></div>
				</li>
        </ul>
			<?php else: ?>
                <?php $this->load->view('categories/category');
                endif;  ?>

		</div>
	</section>
</section>

<div id="product-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<?php $this->load->view('products/product_form', ['product' => []]); ?>
		</div>
	</div>
</div>
<div id="product-modal-new" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <?php $this->load->view('products/product_form', ['product' => []]); ?>
        </div>
    </div>
</div>

<div id="category-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <?php $this->load->view('categories/partials/category_form', ['category' => []]); ?>
        </div>
    </div>
</div>
<script>
	
	window.edit_modal = function(response){
		$('#product-modal .modal-content').html(response.html);
		$('#product-modal').modal();
		Common.mask_currency();
	};

	$(document).ready(function (){
        $(document).on('click', '.favourite-checkbox', function () {
            let item_id = $(this).data('item_id');
            if(item_id === ''){
                let modal = $(this).closest('form');
                let item_name = modal.find('.service_name').val();
                if(item_name.trim() !== ''){
                    let first_letters = '';
                    let item_name_array = item_name.trim().split(' ');
                    if(item_name_array[0] && item_name_array[1])
                        first_letters = item_name_array[0].substr(0,1).toUpperCase() + item_name_array[1].substr(0,1).toUpperCase();
                    else if(item_name_array[0])
                        first_letters = item_name_array[0].substr(0,2).toUpperCase();
                    modal.find('.first-letters').text(first_letters);
                    modal.find('.first-letters').closest('label').show();
                } else {
                    modal.find("input[value='tree-removal']").prop('checked', true);
                    modal.find('.first-letters').closest('label').hide();
                }
            }
            $('.favourite-icons-' + item_id).toggle();
        });
		Common.mask_currency();
		
		$('.deleteService').click(function(){
			if (confirm('Are you sure?')) {	
				var id = $(this).data("href");
				$(id).trigger("submit");	
			}
			return false;
		});

		$('.sortable').sortable().bind('sortupdate', function () {
			var arr = [];
			$.each($('.sortable').children(), function (key, val) {
				priority = key + 1;
				arr[$(val).data('id')] = priority;
			});
			$.post(baseUrl + 'estimates/ajax_estimate_priority_service', {data: arr}, function (resp) {
				if (resp.status == 'error')
					alert('Ooops! Error...');
				return false;
			}, 'json');
			return false;
		});
        initQbLogPopover();
	});
	
</script>
<?php $this->load->view('includes/footer'); ?>
