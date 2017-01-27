<fieldset id="groupAdminSettings" class="section">
  <h2><?php p($l->t('User Group Admin'));?></h2>
  <?php 
		$sender = OCP\Config::getAppValue('user_group_admin', 'sender', '');
		$subject = OCP\Config::getAppValue('user_group_admin', 'subject', '');
 echo "
  <label for='mailSender'>Mail Sender </label>
  <input type='text' name='mailsender' id = 'mailsender' value=\"".$sender."\" original-title='' />
  <br>
  <label for='mailSubject'>Mail Subject </label>
  <input type='text' name='mailsubject' id = 'mailsubject'  value=\"".$subject."\" />
  <input type='submit' value='Save' id = 'mailsubmit' original-title='' />";
?>
</fieldset>

