MembersClass = function(){
	
	this.addMember = function(data, callback){
		if(callback==undefined)
			callback = function(){};

		$.ajax({
			global: false,
			method: "POST",
			data: data,
			url: base_url + "schedule/ajax_add_member",
			dataType:'json',
			success: function(response){
				if (response.status == 'error') {
					callback(response);
					alert(response.errMsg);
				}
				else {
					callback(response);
					lastUpdateId = response.update.update_id;
				}
			}
		});


	}
}

EquipmentClass = function(){

	this.addEquipment = function(data, callback){
		if(callback==undefined)
			callback = function(){};
		
		$.ajax({
			global: false,
			method: "POST",
			data: data,
			url: base_url + "schedule/ajax_add_equipment",
			dataType:'json',
			success: function(response){
				if (response.status == 'error') {
					callback(response);
					alert(response.errMsg);
				}
				else {
					callback(response);
					lastUpdateId = response.update.update_id;
				}
			}
		});

	}

	this.deleteEquipment = function(data, callback){
		removeFromItemList(data.item_id);
		var obj = $('li.label[data-eq_id="' + data.item_id + '"]');
		var item_name = $.trim($(obj).text().replace(' x', ''));
		var group_id = $(obj).attr('data-eq_group_id');
		var origin_color = $(obj).attr('data-origin-color');
		var item_code = $(obj).attr('data-item_code');
		$(obj).remove();
		var tpl = `
			<li class="label bg-danger ui-draggable addItem b-a" style="text-shadow: 1px 1px #626262;background: ` + origin_color + `;" data-item_id="` + data.item_id + `" data-item_group_id="` + group_id + `" data-origin-color="` + origin_color + `" data-item_code="` + item_code + `">` +
				item_name + `
			</li>
		`;
		$('.emp-dropdown').find('.line.line-dashed.line-lg.line-items').after(tpl);

		tpl = `
			<li class="label bg-danger ui-draggable b-a ui-sortable-handle" style="border-color: #000;text-shadow: 1px 1px #626262;background: ` + origin_color + `;" data-item_id="` + data.item_id + `" data-item_group_id="` + group_id + `" data-origin-color="` + origin_color + `" data-item_code="` + item_code + `">` + 
				item_name + `
			</li>
		`;
		$('.freeItems').append(tpl);

		if(callback==undefined)
			callback = function(){};

		$.ajax({
			global: false,
			method: "POST",
			data: data,
			url: base_url + "schedule/ajax_delete_equipment",
			dataType:'json',
			success: function(response){
				callback(response);
				lastUpdateId = response.update.update_id;
			}
		});
	}

	this.buildEquipment = function(data){
		if(data.response.status == 'error') {
			$(data.item).appendTo('.freeItems.sortable');
			$(data.item).css('display', 'inline-block');
			return false;
		}
		//add equipment to team from free
		if(data.old_team_id==-1 || data.old_team_id==undefined)
			this.setEquipmentToTeam(data.item, data.new_team_id);

		//remove equipment from team to free
		if(data.new_team_id == -1 || data.new_team_id==undefined)
			this.setEquipmentToFree(data);

		if(data.new_team_id > 0 && data.old_team_id > 0){
			item_name = data.item.html();
			this.setEquipmentToTeam(data.item, data.new_team_id);
		}
	}

	this.setEquipmentToTeam = function(selector, new_team_id){
		var data = {};
		data.item = selector;
		data.itemId = selector.attr('data-item_id');
		if(data.itemId == undefined)
			data.itemId = selector.attr('data-item-id');

		var tpl = `
			<a href="#" data-driver_id="" data-toggle="popover" data-html="true" data-container="body" data-placement="top" data-content="TEST">
				(N/A)
			</a>
		`;
		origin_color = data.item.attr('data-origin-color');
		item_code = data.item.attr('data-item_code');
		data.new_team_id = new_team_id ? new_team_id : data.item.parents('div.dhx_scale_bar:first').find('a[data-crew_id]').attr('data-crew_id');
		data.item.data('origin-color', origin_color);
		data.item.attr('style','color:#000; display: block;');
		data.item.attr('data-item-id', data.itemId);

		if(data.item.is('.addItem'))
			data.item.appendTo('#crewsList .crew_' + data.new_team_id);
		else {
			data.item.find('a[data-toggle="popover"]').remove();
		}
		data.item.attr('class', 'label it_'+data.itemId+' ui-sortable-handle team-equipment-item');

		data.item.removeAttr('data-item_id');
		data.item.find('.popover').remove();
		item_name = data.item.text();
		group_id = data.item.attr('data-item_group_id');
		data.item.html($(data.item).html() + tpl);

		$('li.label.it_' + data.itemId + ' a[data-toggle="popover"]').popover().on('show.bs.popover', function() {
			var teamId = $(this).parents('ul[data-bonus-team-id]').attr('data-bonus-team-id');
			var itemId = $(this).parents('li[data-item-id]').attr('data-item-id');
			var driverId = $(this).attr('data-driver_id');
			var content = `
				<select class="form-control changeDriver" data-equipment_team_id="` + teamId + `" data-equipment_id="` + itemId + `">
			`;
			content += `<option value="">N/A</option>`;

			$.each($('.crew_' + teamId).find('li.label[data-emp_id]'), function(key, val){
				var el = document.createElement('div');
				el.innerHTML = $(val).html();
				$(el).find('.teamLeader').remove();
				$(el).find('.driverFor').remove();
				name = $(el).text();
				selected = driverId && driverId==$(val).attr('data-emp_id') ? ' selected="selected"' : '';
				content += `<option data-emailid="` + $(val).attr('data-emailid') + `" ` + selected + ` value="` + $(val).attr('data-emp_id') + `">` + name + `</option>`;
			});
			content += `
				</select>
			`;
			$(this).attr('data-content', content);
		});

		//$('.emp-dropdown').find('li.label[data-eq_id="' + data.itemId + '"]').remove();
		//$('.addItem[data-item_id="' + data.itemId + '"]').remove();
		//$('.freeItems li[data-item_id="' + data.itemId + '"]').remove();
		//$('[data-crew_id="'+data.new_team_id+'"]').parent().find('.emp-dropdown .eqInfo').after('<li class="label bg-warning" data-eq_id="' + data.itemId + '" data-origin-color="' + origin_color + '" data-eq_group_id="' + group_id + '" data-item_code="' + item_code + '">' + item_name + ' <a href="#" class="moveItemFromCrew">x</a></li>');
	}

	this.setEquipmentToFree = function(data){

		origin_color = data.item.attr('data-origin-color');
		item_code = data.item.attr('data-item_code');
		data.item.attr('style','text-shadow: 1px 1px #626262;background: '+origin_color);
		data.item.attr('data-item_id', data.itemId);	
		data.item.attr('data-item_code', item_code);	
		data.item.attr('class', 'label bg-danger ui-draggable b-a ui-sortable-handle');
		data.item.find('a[data-toggle="popover"]').remove();
		data.item.removeAttr('data-item-id');

		item_name = data.item.html();
		var obj = $('li.label[data-eq_id="' + data.item_id + '"]');
		var group_id = $(obj).attr('data-eq_group_id');
		
		//var origin_color = $(obj).attr('data-origin-color');
		$(obj).remove();

		$('[data-crew_id="'+data.old_team_id+'"]').parent().find('.emp-dropdown [data-eq_id="' + data.itemId + '"]').remove();

		/*
		var tpl = `
			<li class="label bg-danger ui-draggable addItem b-a" style="text-shadow: 1px 1px #626262;background: ` + origin_color + `;" data-item_id="` + data.item_id + `" data-item_group_id="` + group_id + `" data-origin-color="` + origin_color + `" data-item_code="` + item_code + `">` +
				item_name + `
			</li>
		`;
		$('.emp-dropdown').find('.line.line-dashed.line-lg.line-items').after(tpl);
		*/
		//$('.emp-dropdown').find('.line.line-dashed.line-lg.line-items').after('<li class="label bg-danger ui-draggable addItem b-a" data-item_id="' + data.itemId + '">' + item_name + '</li>');
	}

}

Equipment = new EquipmentClass();
Members = new MembersClass();
