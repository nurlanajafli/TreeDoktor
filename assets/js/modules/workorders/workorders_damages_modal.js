	var DamagesModalClass = function(){
	
	//this.modalObj = null;
	this.workorder_id = 0;
	this.tmpSelector = '#theTmpl';
	this.formSelector = '#workorders_damages_form',
	this.modalJQSelector = '#workorders_damages',
	this.saveFormSelector = '#save-damage-complain',
	this.reloadAfterUpdate = true;
	this.globalCallback = false;
	this.init = function(wo_id, reload, callback){
	
		if(callback==undefined)
			callback=function(){location.reload();};
		if(reload==undefined)
			reload=true;
		
		this.workorder_id = wo_id;
		this.showModal();
		this.setEvents();

		this.globalCallback = callback;

		this.reloadAfterUpdate = reload;
	},


	this.showModal = function(){
		$this = this;
		
		callback = function(response){
			response.data = [];
			var template = $.templates($this.tmpSelector);
			var htmlOutput = template.render(response.data);
			
			$($this.modalJQSelector).modal();

			$($this.formSelector).html(htmlOutput);
			
			if(!response.data.length){
				$($this.modalJQSelector).modal('hide');
				if($this.globalCallback)
					$this.globalCallback();
				else
					location.reload();
			}
		}

		this.getWorkOrderTeams(callback);
	},


	this.getWorkOrderTeams = function(callback){
		$this = this;
		$.post('/workorders/get_workorder_teams', {'id':$this.workorder_id}, function(data){
			callback(data);
		}, 'json');
	},
	
	this.setWorkorderDamages = function(callback){
		form = $(this.formSelector).serialize();
		$.post('/workorders/set_workorder_damages', form, function(data){
			callback(data);
		}, 'json');
	},

	this.setEvents = function(){
		$this = this;
		$(this.saveFormSelector).on('click', function(){
			callback = function(response){
				if(response.status=='success' && $this.reloadAfterUpdate)
					location.reload();
				else
					$($this.modalJQSelector).modal('hide');

				$this.globalCallback();
			}
			$this.setWorkorderDamages(callback);
		});
	}

}

var DamagesModal = new DamagesModalClass();
