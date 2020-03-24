<div class="signup">

	<div>You've been invited by <?php echo $_['groupowner'];?> to join the group
	<?php echo $_['groupname'];?>.</div>
	
	<div>For this, you need an account here. Notice that the preferred way to obtain
	this is for you to simply <a href="/">sign in via your home institution</a>.</div>
	
	<div>If this is not possible, you may apply for an external collaborator account.
	To do so, fill in the form below, then click 'Proceed'.
	Your username will be the email address to which the invitation was sent:
	<b><?php echo $_['email'];?></b></div>
	
	<form method="post" id="external_signup">
		<input type="hidden" name="code" value="<?php echo $_GET['code'];?>"/>
		<br />
		<?php
		if(!empty($_['password_error'])){
			echo "<div class='msg error fadeout'>".$_['password_error']."</div>";
		}
		?>
		<!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
		<input style="display:none" type="text" name="fakeusernameremembered"/>
		<input style="display:none" type="password" name="fakepasswordremembered"/>
		<input class="password numeric-password"
		autocomplete="off" type="text" name="password" 
			<?php
		\OCP\Util::writeLog('User_Group_Admin', 'Missing: '.serialize($_['missingfields']), \OCP\Util::WARN);
			if(in_array('password', $_['missingfields'])){
				echo "class='highlight'";
			}
			if(!empty($_['password_error'])){
				//echo "placeholder='".$_['password_error']."'";
				echo "placeholder='Password not accepted'";
			}
			else{
				echo 'placeholder="Password"';
				if(!empty($_['password'])){
					echo "value='".$_['password']."'";
				}
			}
			?>
			/>
		<br />
		<input type="text" name="fullname" placeholder="Full name" 
			<?php
			if(in_array('fullname', $_['missingfields'])){
				echo "class='highlight'";
			}
			if(!empty($_['fullname'])){
				echo "value='".$_['fullname']."'";
			}
			?>
			/>
		<br />
		<textarea name="address" placeholder="Full postal address" <?php
		if(in_array('address', $_['missingfields'])){
			echo "class='highlight'";
		}
		?>><?php
			if(!empty($_['address'])){
				echo $_['address'];
			}
			?></textarea>
		<br />
		<textarea name="affiliation" placeholder="Affiliation"><?php
		if(!empty($_['affiliation'])){
			echo $_['affiliation'];
		}
		?></textarea>
		<br />
		<input type="submit" value="Proceed" />
	</form>

</div>
