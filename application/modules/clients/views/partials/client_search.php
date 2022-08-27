<form name="search" id="client-search-name-form" method="post" action="<?= current_url(); ?>" class="form-inline">
    <div class="form-group">
        <input name="search_keyword" id="search_tags" type="text" class="input-sm form-control"
               placeholder="Name, Phone number, address..." value="" style="width: 400px">
        <span class="form-group-btn">
			<button class="btn btn-sm btn-default" type="submit" id="search">Search</button>
		</span>
    </div>
</form>