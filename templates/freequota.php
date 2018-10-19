<span><?php p($l->t('Free quota'));?></span>
<?php
	$freequotaIsUserDefined = !empty($_['user_freequota']) &&
		array_search($_['user_freequota'], $_['quota_preset'])===false;
?>
<select id='setfreequota' group='<?php p($_['group']);?>' data-inputtitle="<?php p($l->t('Please enter free storage quota (ex: "512 MB" or "12 GB")')) ?>" data-tipsy-gravity="s">
	<option <?php if($_['user_freequota'] === 'none') print_unescaped('selected="selected"');?> value='none'>
		<?php p($l->t('None'));?>
	</option>
	<?php foreach($_['quota_preset'] as $preset):?>
		<?php if($preset !== 'default'):?>
			<option <?php if(isset($_['user_freequota']) && $_['user_freequota']==$preset) print_unescaped('selected="selected"');?> value='<?php p($preset);?>'>
				<?php p($preset);?>
			</option>
		<?php endif;?>
	<?php endforeach;?>
	<?php if($freequotaIsUserDefined):?>
		<option selected="selected" value='<?php p($_['user_freequota']);?>'>
			<?php p($_['user_freequota']);?>
		</option>
	<?php endif;?>
	<option data-new value='other'>
		<?php p($l->t('Other'));?>
		...
	</option>
</select>
<?php $showOwned = OC_User_Group_Admin_Util::getShowOwned($_['group']); ?>
<div id="show_owned" <?php print_unescaped(empty($_['user_freequota'])||$_['user_freequota']=='none'||$_['user_freequota']=='0 B'?
		'class="hidden"':'');?>>
<?php p($l->t('Show owned group folders')); ?>&nbsp;
<input id="show_owned_group_folders"
<?php p($showOwned=='yes'?'checked="checked"':'');?>
title="<?php p($l->t("Allow group owner to see group folders of members")); ?>"
 type="checkbox"/>
<label id="group_msg"></label>
</div>