<fieldset id="groupAdminSettings" class="section">
  <h2><?php p($l->t('User Group Admin'));?></h2>
  <?php 
		$default_group_freequota = OCP\Config::getAppValue('user_group_admin', 'default_group_freequota', '');
		$sender = OCP\Config::getAppValue('user_group_admin', 'sender', '');
		$subject = OCP\Config::getAppValue('user_group_admin', 'subject', '');
 echo "
  <label for='default_freequota'>Default free quota for groups</label>
  <input type='text' name='default_group_freequota' id = 'default_group_freequota' value=\"".
  $default_group_freequota."\" original-title='' />
  <br>
	<label for='mailSender'>".$l->t('Mail Sender')."</label>
  <input type='text' name='mailsender' id = 'mailsender' value=\"".$sender."\" original-title='' />
  <br>
  <label for='mailSubject'>".$l->t('Mail Subject')."</label>
  <input type='text' name='mailsubject' id = 'mailsubject'  value=\"".$subject."\" />
  <input type='submit' value='Save' id = 'mailsubmit' original-title='' />";
?>
</fieldset>

