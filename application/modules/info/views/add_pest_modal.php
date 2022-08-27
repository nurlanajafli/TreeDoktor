<div id="add_pest" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form name="add_tree_modal" action="<?php echo base_url('info/add_pest'); ?>" method="POST">
				<div class="modal-body">
					<h5 class="p-bottom-20">Add New Pest</h5>
					<table class="table table-striped b-a b-light m-t-n-xxs m-b-none tableAddPest">
						<tr>
							<td class="w-200">
								<label class="control-label">Pest Name(Eng)</label>
							</td>
							<td class="p-left-30">
								<input id="name_eng" class="name_eng" name="name_eng" style="width:260px">
								
							</td>
						</tr>
						<tr>
							<td class="w-200">
								<label class="control-label">Pest Name(Lat)</label>
							</td>
							<td class="p-left-30">
								<input id="name_lat" class="name_lat" name="name_lat" style="width:260px">
							</td>
						</tr>
						<tr class="affecting-0">
							<td class="w-200">
								<label class="control-label">Pest Affecting:</label>
							</td>
							<td class="p-left-30">
								<select name="affecting[]" class="input-sm form-control affecting" >
									<option value="diseases">Diseases</option>
									<option value="insects">Insects</option>
								</select>
							</td>
						</tr>
						<tr class="notes-0">
							<td class="w-200">
								<label class="control-label">Pest Notes:</label>
							</td>
							<td class="p-left-30">
								<textarea name="pests[]" class="input-sm form-control" ></textarea>
							</td>
						</tr>
					</table>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					<?php echo form_submit('submit', 'Add Pest', 'class="btn btn-info update__client"'); ?>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$('#name_lat').select2("enable", true);
		$('#name_eng').select2("enable", true);
		$('#add_pest').on('hide.bs.modal', function () {
			$("#name_lat").select2('close');
			$("#name_eng").select2('close');
		});
		
		$("#name_lat").select2({
			tags: true,
			createSearchChoice: function(term, data) {
				if ($(data).filter(function() {
					return this.text.localeCompare(term) === 0;
				}).length === 0) {
					return {
						id: term,
						text: term
					};
				}
			},
			minimumInputLength:3,
			multiple: true,
			placeholder: "Add Pest(Lat)",
			ajax: { 
				url: baseUrl + "info/ajax_by_name",
				params:{
					type:'POST',
					global:false,
				},
				dataType: 'json',
				quietMillis: 500,
				data: function (term, page) {
					return {
						name: term,
						trigger: 'name_lat'
					};
				},
				results: function (data, page) {
					return { results: data.items };
				},
				cache: true
			},
		});
	
		$("#name_eng").select2({
			tags: true,
			createSearchChoice: function(term, data) {
				if ($(data).filter(function() {		return this.text.localeCompare(term) === 0;}).length === 0) {
					return {
						id: term,
						text: term
					};
				}
			},
			minimumInputLength:3,
			multiple: true,
			
			placeholder: "Add Pest(Eng)",
			ajax: { 
				url: baseUrl + "info/ajax_by_name",
				params:{
					type:'POST',
					global:false,
				},
				dataType: 'json',
				quietMillis: 500,
				data: function (term, page) {
					return {
						name: term,
						trigger: 'eng'
					};
				},
				results: function (data, page) {
					return { results: data.items };
				},
				cache: true
			},
		});
		//$('#name_eng').on("select2-selecting", function (e) { console.log( e.object.id); });
		$('#name_eng').on('change', function (e) {
			if(e.added !== undefined)
			{
				res = $('#name_lat').select2('data');
				//Object.keys(e.added).length
				newTextarea($('#name_eng').select2('data').length, e.added.id);
				if(e.added.pest_lat_name !==undefined)
				{
					res.push({id:e.added.id, text:e.added.pest_lat_name});
					$('#name_lat').select2('data', res);
				}
				
			}
			else if(e.removed !== undefined)
			{
				items = $('#name_lat').select2('data');
				$.each(items, function (key, val) {
					
					if(val.id === e.removed.id)
					{
						items.splice(key, 1);
						return false;
					}
					//else
					//	console.log($('#name_eng').select2('data'));
				});
				
				newTextarea($('#name_eng').select2('data').length, e.removed.id, true);
				$('#name_lat').select2('data', items);
			}
		});
		$('#name_lat').on('change', function (e) {
			if(e.added !== undefined)
			{
				res = $('#name_eng').select2('data');
				
				//newTextarea($('#name_eng').select2('data').length, e.added.id);
				if(e.added.pest_eng_name !==undefined)
				{
					res.push({id:e.added.id, text:e.added.pest_eng_name});
					$('#name_eng').select2('data', res);
				}
			}
			else if(e.removed !== undefined)
			{
				items = $('#name_eng').select2('data');
				$.each(items, function (key, val) {
					
					if(val.id === e.removed.id)
					{
						items.splice(key, 1); return false;
					}
				});
				//newTextarea($('#name_eng').select2('data').length, e.added.id, true);
				$('#name_eng').select2('data', items);
			}
		});
		
	});
	
	function newTextarea(count, id, num = false)
	{
		console.log(count, num, id);
		
		if(num === false)
		{
			if(count == 1)
			{
				$('.tableAddPest tbody textarea').parent().parent().attr('class', 'notes-' + id);
				$('.tableAddPest tbody .affecting').parent().parent().attr('class', 'affecting-' + id);
			}
			else if(count > 1)
			{
				$('.tableAddPest tbody').append('<tr class="notes-'+ id +'"><td class="w-200"><label class="control-label">Pest Notes:</label></td><td class="p-left-30"><textarea name="pests[]" class="input-sm form-control" ></textarea></td></tr>');
				$('.affecting:last').parent().parent().after('<tr class="affecting-'+ id +'"><td class="w-200"><label class="control-label">Pest Affecting:</label></td><td class="p-left-30"><select name="affecting[]" class="input-sm form-control affecting" ><option value="diseases">Diseases</option><option value="insects">Insects</option></select></td></tr>');
			}
		}
		else
		{
			if(count == 0)
				$('.tableAddPest tbody textarea').val('');
			else
			{
				$('.tableAddPest tbody .notes-' + id).remove();
				$('.tableAddPest tbody .affecting-'+ id).remove();
			}
		}
		
		/*
		if(count > 1 && num === false && $('.tableAddPest tbody textarea').length !== count)
			
		else if(count == 0 && !num)
		{
			$('.tableAddPest tbody textarea').val('');
			$('.tableAddPest tbody textarea').parent().parent().attr('class', 'notes-' + id);
		}
		else if(count != 0)
			$('.tableAddPest tbody .notes-' + id).remove();
		*/
		/*tables = $('.tableAddPest tbody textarea').parent().parent();
		$.each(tables, function (key, val){
			$(val).attr('class', 'notes-' + key);
		});*/
	}
	
</script>
