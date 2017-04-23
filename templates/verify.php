<div class="signup">

	<div>Your invitation of the external user <b><?php echo $_['user'];?></b> to join the group
	<b><?php echo $_['group'];?></b> has been accepted and a new account has been created for
	this user.</div>
	
	<div>It is your responsibility to guarantee that the contact details provided below are correct.</div>
	
	<div>If the information is correct and you accept the responsibility, you don't have
	to do anything and you can simply click 'Home' to return to your files.</div>
		
	<?php if($_['verify_pending']=='yes'){?>
	<div>If you cannot or will not accept the responsibilisty or if the information below
	is not correct, please click "Revoke invitation". Notice that you must do this now
	- you cannot return here and do this later.</div>
	<div class=spanpanel>
	<a id="revokeinvitation" class="btn btn-primary btn-flat" href="#" group="<?php echo $_['group'];?>"
	user="<?php echo $_['user'];?>">
		<i class="icon"></i>Revoke invitation
	</a>
	<a href="<?php echo OC::$WEBROOT ?>/">Home</a>
	</div>
	<?php }
	else{
	$fromEmail = \OCP\Config::getSystemValue('fromemail', '');
	?>
	<div>
	You can no longer revoke your invitation. If the information below is not correct,
	please <a href="mailto:<?php echo $fromEmail;?>">contact us</a>.
	</div>
	<div class="verify_info">
	<a href="<?php echo OC::$WEBROOT ?>/">Home</a>
	</div>
	<?php }?>
	<div class="verify_info">
	<table class="table table-striped">
	<tbody>
		<tr>
			<td>Username</td>
			<td><?php echo $_['user'];?></td>
		</tr>
		<tr>
			<td>Full name</td>
			<td><?php echo $_['displayname'];?></td>
		</tr>
		<tr>
			<td>Email</td>
			<td><?php echo $_['email'];?></td>
		</tr>
		<tr>
			<td>Address</td>
			<td><?php echo $_['address'];?></td>
		</tr>
		<tr>
			<td>Affiliation</td>
			<td><?php echo $_['affiliation'];?></td>
		</tr>
	</tbody>
	</table>
	</div>
	
</div>
