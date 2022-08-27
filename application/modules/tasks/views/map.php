<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15 p-n mapper" style="top: 9px;">
	<?php echo $map['html']; ?>
</section>
<script>
	var checked = true;
	$(document).ready(function () {
		$(document).on('change', '.showPoint', function () {
			var crewId = $(this).val();
			var visible = false;
			if ($(this).is(':checked'))
				visible = true;
			$.each(markers, function (key, val) {
				var currCrew = $(val.content).data('user');
				if (currCrew == crewId)
					markers[key].setVisible(visible);
			});
		});
		$(document).on('change', '.showAll', function () {
			$.each($('.showPoint'), function (key, val) {
				if (checked)
					$(val).prop('checked', false);
				else
					$(val).prop('checked', true);
				$(val).change();
			});
			checked = checked ? false : true;
		});
		$(document).on('change', '.task_status_change', function () {
		//$('.task_status_change').change(function(){
			var id = $(this).parents('.marker').attr('id');
			if($(this).val() == 'new')
			{
				
				$('#'+ id).find('.new_status_desc').css('display', 'none');
				$('#'+ id).find('.submit').css('display', 'none');
				$('#'+ id).find('form:first').css('padding-bottom', '0px');
				
			}
			else
			{
				$('#'+ id).find('.new_status_desc').css('display', 'block');
				$('#'+ id).find('.submit').css('display', 'inline-block');
				$('#'+ id).find('form:first').css('padding-bottom', '40px');
			}
			return false;
		});
		$(document).on('click', '.submit', function () {
		//$('.submit').click(function(){
			var id = $(this).parents('.marker').attr('id');
			if($(this).text() == 'Close')
			{
				$('#'+ id).find('.task_status_change').val('new');
				$('#'+ id).find('.new_status_desc').css('display', 'none');
				$('#'+ id).find('.submit').css('display', 'none');
				$('#'+ id).find('form:first').css('padding-bottom', '0px');
			}
			else
			{
				status = $('#'+ id).find('.task_status_change').val();
				text = $('#'+ id).find('.new_status_desc').val();
				/*if(text == '')
				{
					alert('Description is required!');	
					return false;
				}
				else
				{*/
					$.post(baseUrl + 'tasks/ajax_change_status', {id : id, status : status, text : text}, function (resp) {
						if(resp.status == 'ok')
							location.reload();
						else
							alert(resp.msg);
					}, 'json');
				//}
			}
			
			return false;
		});
	});
</script>

<?php $this->load->view('includes/footer'); ?>
