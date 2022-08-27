UsersStatisticClass = function(){
	
	/*------------routes-------------*/
	this.routes = function(){
		this.usersStatisticUrl = '/screen/get_users_statistic';
		
		return this;
	}
	/*------------routes-------------*/

	this.init = function(){
		$this=this;
		this.events();

		loadCallback = function(response){
			if(!$this.isResponse(response))
				return false;

			$this.view(response.data);
		}
		this.load(loadCallback);
	}

	this.load = function(callback){
		if(callback==undefined)
			callback = function(){};

		$.post(this.routes().usersStatisticUrl, {}, function(response){
			var result = [];
			$.each(response.data.all_time, function(key, value){
				
				class_all = class_mnth = class_3mnth = class_6mnth = 'bg-danger';
				
				if(value.mhrs_return2 >= GREAT_MAN_HOURS_RETURN)
					class_all = 'bg-success';
				if(value.mhrs_return2 < GREAT_MAN_HOURS_RETURN && value.mhrs_return2>GOOD_MAN_HOURS_RETURN)
					class_all='bg-warning';

				if(response.data.mnth[key] != undefined && response.data.mnth[key].mhrs_return2 > GREAT_MAN_HOURS_RETURN)
					class_mnth = 'bg-success';
				if(response.data.mnth[key] != undefined && response.data.mnth[key].mhrs_return2 < GREAT_MAN_HOURS_RETURN && response.data.mnth[key].mhrs_return2 > GOOD_MAN_HOURS_RETURN)
					class_mnth = 'bg-warning';

				if(response.data.mnth3[key] != undefined && response.data.mnth3[key].mhrs_return2 > GREAT_MAN_HOURS_RETURN)
					class_3mnth = 'bg-success';
				if(response.data.mnth3[key] != undefined && response.data.mnth3[key].mhrs_return2 < GREAT_MAN_HOURS_RETURN && response.data.mnth3[key].mhrs_return2 > GOOD_MAN_HOURS_RETURN)
					class_3mnth = 'bg-warning';

				if(response.data.mnth6[key] != undefined && response.data.mnth6[key].mhrs_return2 > GREAT_MAN_HOURS_RETURN)
					class_6mnth = 'bg-success';
				if(response.data.mnth6[key] != undefined && response.data.mnth6[key].mhrs_return2 < GREAT_MAN_HOURS_RETURN && response.data.mnth6[key].mhrs_return2 > GOOD_MAN_HOURS_RETURN)
					class_6mnth = 'bg-warning';
				

				tmp = {
					'emp_name':value.emp_name,
					'mhrs_return_all_time':value.mhrs_return2==null?0:value.mhrs_return2,
					'mhrs_return_mnth':response.data.mnth[key] == undefined || response.data.mnth[key].mhrs_return2==null?0:response.data.mnth[key].mhrs_return2,
					'mhrs_return_3mnth':response.data.mnth[key] == undefined || response.data.mnth3[key].mhrs_return2==null?0:response.data.mnth3[key].mhrs_return2,
					'mhrs_return_6mnth':response.data.mnth[key] == undefined || response.data.mnth6[key].mhrs_return2==null?0:response.data.mnth6[key].mhrs_return2,
					
					'count_teams_all_time':value.count_teams,
					'count_teams_mnth':response.data.mnth[key] == undefined ? 0:response.data.mnth[key].count_teams,
					'count_teams_3mnth':response.data.mnth[key] == undefined ? 0:response.data.mnth3[key].count_teams,
					'count_teams_6mnth':response.data.mnth[key] == undefined ? 0:response.data.mnth6[key].count_teams,

					'total_mhrs_all_time':value.total_mhrs,
					'total_mhrs_mnth':response.data.mnth[key]==undefined?0:response.data.mnth[key].total_mhrs,
					'total_mhrs_3mnth':response.data.mnth[key]==undefined?0:response.data.mnth3[key].total_mhrs,
					'total_mhrs_6mnth':response.data.mnth[key]==undefined?0:response.data.mnth6[key].total_mhrs,

					'damage_sum_all_time':value.damage_sum,
					'damage_sum_mnth':response.data.mnth[key]==undefined?0:response.data.mnth[key].damage_sum,
					'damage_sum_3mnth':response.data.mnth[key]==undefined?0:response.data.mnth3[key].damage_sum,
					'damage_sum_6mnth':response.data.mnth[key]==undefined?0:response.data.mnth6[key].damage_sum,

					'complain_sum_all_time':value.complain_sum,
					'complain_sum_mnth':response.data.mnth[key]==undefined?0:response.data.mnth[key].complain_sum,
					'complain_sum_3mnth':response.data.mnth[key]==undefined?0:response.data.mnth3[key].complain_sum,
					'complain_sum_6mnth':response.data.mnth[key]==undefined?0:response.data.mnth6[key].complain_sum,

					'class_all':class_all,
					'class_mnth':class_mnth,
					'class_3mnth':class_3mnth,
					'class_6mnth':class_6mnth
				};
				result.push(tmp);
			});

			callback({'data':result, 'status':response.status});
			return true;
		}, 'JSON');
	}
	
	this.view = function(data, callback){
		if(callback==undefined)
			callback = function(){};
		
		template = $.templates('#usersStatisticIsEmpty');
		
		var htmlOutput = template.render([{message:'Statistic is not available by now ...'}]);
		
		if(data.length!=0){
			var template = $.templates('#usersStatisticTmp');
			var htmlOutput = template.render(data);
		}
		
		$('#usersStatisticView').html(htmlOutput);

		callback();
	}

	this.events = function(callback){
		if(callback==undefined)
			callback = function(){};
		

		callback();
		return true;
	}

	this.isResponse = function(response){
		if(response.status!='success' || response.data==undefined)
			return false;
		return true;
	}

}

var UsersStatistic = new UsersStatisticClass();
