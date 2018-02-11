<div class="signup">

	<div>You've been invited by <?php echo $_['groupowner'];?> to join the group
	<?php echo $_['groupname'];?></div>
	
	<div>The email address to which the invitation was sent was
	<?php echo $_['email'];?>.
	To decline the invitation, click the 'Decline' button below.</div>
	
	<br />
	
	<form method="post">
		<input type="hidden" name="code" value="<?php echo $_GET['code'];?>"/>
		<input type="hidden" name="declined" value="yes"/>
		<input type="submit" value="Decline" />
	</form>

</div>
