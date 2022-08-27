<div class="modal modal-static fade" id="autologoutModal" role="dialog" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog" style="width:250px;margin:10% auto;">
		<div class="modal-content">
			<div class="modal-body">
				<div class="text-center">
					<h4>You were inactive for <?php echo (ACTIVITY_TIMEOUT / 60); ?> mins and will be logged out now <span id="logoutTimer"></span> sec.</h4>
					<button class="btn btn-default btn-warning" id="autoLogout">Stay logged in</button>
				</div>
			</div>
		</div>
	</div>
</div>
