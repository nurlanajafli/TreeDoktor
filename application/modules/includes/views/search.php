<style>
    .select2-container.select2-container-multi {
        padding-top: 2px !important;
        padding-bottom: 2px !important;
    }

    .select2-container-multi .select2-choices {
        padding-top: 0px !important;
    }
</style>
<form name="search" id="search" method="post" action="<?= current_url(); ?>" class="form-inline">
	<div class=form-group style="width: 300px">
        <input name="search_tags" class="input-sm w-100  js-tags-select2" style="padding: 0px;" value="<?= isset($search_tags) ? $search_tags : '' ?>">
	</div>

    <div class="form-group">
		<input name="search_keyword"  type="text" class="input-sm form-control"
		       placeholder="<?php if (!empty($placeholder)) : echo $placeholder;
		       else : ?>Name, Phone number, address...<?php endif; ?>"
		       value="<?php if (isset($search_keyword)) echo $search_keyword; ?>" style="width: 300px">
		<?php if(isset($search_param) && $search_param) { ?>
		<span class="form-group-btn" id="clear_search">
			<button type="button" class="btn btn-xs btn-link"><i class="fa fa-times"></i></button>
		</span>
		<?php } ?>
		<span class="form-group-btn">
			<button class="btn btn-sm btn-default" type="submit" id="search">Go!</button>
		</span>
	</div>
</form>

<script>
    let data = <?= isset($select2Tags)?$select2Tags:json_encode([]); ?>;

  $(document).ready(function(){
      $('#clear_search button').click(function () {
          //todo bug  after click when name and  some parameters exist
          if ($('form#search #search_tags').val() != '') {
              $('form#search #search_tags').val('');
              window.location.href = window.location.href.split('page')[0] + 'page/';
          }
      });

      selected_tags = $('input[name="search_tags"]').val();
      Common.init_select2([
          {
              'selector':'.js-tags-select2',
              options:{
                  'data': data,
                  'tags': true,
                  'placeholder': 'Tags',
                  'separator': '|'
              }
          }
      ]);

      $('.js-tags-select2').val(selected_tags).trigger('change');

    /*
    $('.js-tags-select2').select2({
      'data': JSON.parse(data),
      'tags': true,
      'placeholder': 'Tags',
      'separator': '|'
    });

     */
  });
</script>
