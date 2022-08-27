<?php if(isset($event) && $event) : ?>
<section class="media-body panel panel-default p-n">
	<header class="panel-heading">Event Notes</header>
	<table class="table table-striped b-t bg-white m-n p-n">
		<tr>
			<td class="m-n p-n">
				<textarea id="eventNotes" class="form-control no-shadow" style="background: #fff;border:0px!important;height: 100px;"><?php echo $event['event_note']; ?></textarea>
			</td>
		</tr>
	</table>
</section>
<?php endif; ?>