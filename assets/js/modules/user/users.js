var Users = function(){
	var config = {

		ui:{
			change_user_type_confirmation:'#change-user-type-confirmation',
			yearly_rate:'#txtyearlyrate',
			hourly_rate:'#txthourlyrate',
		},
		
		events:{
			worker_type_change: '.wType',
			change_user_type_yes:'#change-user-type-yes',
		},

		route:{
			
		},
		
		templates:{
		
		}
	}
	
	var _private = {
		init:function(){
		
		},

		change_worker_type_modal:function(e){
			e.preventDefault();
			var name = $(this).attr('name');
			$(config.events.change_user_type_yes).data('name', name);
			$(config.ui.change_user_type_confirmation).modal();
			return;
		},

		change_user_type_yes:function(){
			console.log($(this).data("name"));
			var val = $('[name="'+$(this).data("name")+'"]:checked').val();
			if(val==1)
				val = 2;
			else
				val = 1;

			$('[name="'+$(this).data("name")+'"]').prop("checked", false);
			//$('[name="'+$(this).data("name")+'"]').val(val);
			console.log('[name="'+$(this).data("name")+'"][value="'+val+'"]');
			$('[name="'+$(this).data("name")+'"][value="'+val+'"]').prop("checked", true);
			$('[name="'+$(this).data("name")+'"][value="'+val+'"]').trigger('change');
			$(config.ui.change_user_type_confirmation).modal('hide');
		}
	}
	
	var selected_date;
	var public = {

		init:function(){
			$(document).ready(function(){
			  	public.events();
			  	_private.init();
			});
		},
		
		events:function(){
			
			$(config.events.worker_type_change).click(_private.change_worker_type_modal);
			$(config.events.change_user_type_yes).click(_private.change_user_type_yes);
			
			$(config.events.worker_type_change).on('change', function(){
				var id = $(this).data('id');
				var val = $(this).val();
				
				$.post(baseUrl + 'user/chage_worker_type', {id : id, val:val}, function () {
				}, 'json');
				return false;
			});

			$(document).on('click', '.close_all_other_sessions', function() {
				let url = $(this).data('url');
				$.post(url, {}, function (response) {

					if (response.error) {
						errorMessage(response.error);
					} else if(response.success) {
						successMessage(response.success);
					} else {
						errorMessage('Something went wrong!');
					}
				}, 'json');
				return false;

			});
			$('.picture-dropzone').click(function(){
				$('input[name="picture"]').trigger("click");
			});
			
			$('.picture-dropzone').on("dragover drop", function(){
				$('input[name="picture"]').trigger("click");
				return false;
			});

			$('input[name="picture"]').change(function(e){
	            var fileName = e.target.files[0].name;
	            $('.picture-dropzone .dz-message').html('<span class="fa fa-file"></span>&nbsp;'+fileName);
	        });

			
			$('#signature-modal').on('show.bs.modal', function () {
				setTimeout(function(){
					var opts = {
						container:'epiceditor',
					    basePath: '',
					    textarea: 'epiceditor-content',
					    clientSideStorage: false,
					    theme: {
					        base: '/assets/vendors/notebook/js/markdown/epiceditor.css',
					        preview: '/assets/vendors/notebook/js/markdown/bartik.css',
					        editor: '/assets/vendors/notebook/js/markdown/epic-light.css'
					    }
					}

					var editor = new EpicEditor(opts).load();	
					
					if(editor!=undefined)
						editor.preview();	
				}, 500);
			});

			$('[name="selectusertype"]').change(function(){
				value = $(this).val();
				if($('[name="worker_type"]').val()==1)
					return; 

				if(value=="admin"){
					$('.options-section').hide(200);
				}
				else{
					$('.options-section').show(200);
				}
				return;
			});
			
			/*$(config.ui.yearly_rate).keyup(function(){
				$(config.ui.hourly_rate).val(0);
			});*/
			$(config.ui.hourly_rate).keyup(function(){
				$(config.ui.yearly_rate).val(0);
			});
			
		}

		


	}

	public.init();
	return public;
}();
