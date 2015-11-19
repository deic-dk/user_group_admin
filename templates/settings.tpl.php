<fieldset id="groupAdminSettings" class="section">

  <h2><?php p($l->t('User Group Admin'));?></h2>
  <?php  
	$subject = OCP\Config::getAppValue('user_group_admin', 'subject', '');
 	echo "     
  		<label for='mailSubject'>Mail Subject </label>
  		<input type='text' name='mailsubject' id = 'mailsubject'  value=\"".$subject."\"  >
  		<br>

  		<input type='submit' value='Save' id = 'mailsubmit' original-title=''>";
  ?>
	
</fieldset>

