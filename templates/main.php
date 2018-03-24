<div id="app-content" style="transition: all 0.3s ease 0s;">
	<div id="app-content-user_group_admin" class="viewcontainer">
		<div id="controls">
			<div class="row">
				<div class="text-right" style="margin-right: 19px;">
					<div class="actions creatable">
						<a id="join" class="btn btn-primary btn-flat" href="#"><i class="icon-users"></i>
						<?php p($l->t("Join group"));?></a>
						<div id="create" original-title="">
							<a id="create" class="btn btn-primary btn-flat" href="#"><i class="icon"></i>
							<?php p($l->t("New group"));?></a>
							<div id="importgroup" class="btn-group">
								<a id="importgroup" type="button" class="btn btn-default btn-flat"><?php p($l->t("Import"));?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="newgroup" class="apanel">
				<span class="spanpanel" >
					<input class="editgroup" type="text" placeholder="<?php p($l->t("New group name"));?>" />
					<span class="newgroup-span">
						<div id="ok" class="btn-group" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Add"));?></a>
						</div>
						<div id="cancel" class="btn-group" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Cancel"));?></a>
						</div>
					</span>
				</span>
			</div>
			<div id="joingroup" class="apanel">
				<span class="spanpanel" >
					<input class="editgroup ui-autocomplete-group" type="text" placeholder="<?php p($l->t("Search groups"));?>" />
					<span class="newgroup-span">
						<div id="join_group" class="btn-group" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Join"));?></a>
						</div>
						<div id="cancel_join" class="btn-group" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Cancel"));?></a>
						</div>
					</span>
				</span>
			</div>
			<div id="importnew" class="apanel">
				<div class="spanpanel">
					<?php p($l->t("Import group from text file"));?>:
					<span class="newimportform">
					<form  id="import_group_form" class="btn btn-default btn-flat" action="<?php echo OCP\Util::linkTo('user_group_admin', 'ajax/import.php'); ?>"  method="post" enctype="multipart/form-data">
						<span><?php p($l->t("Choose file"));?></span>
						<input id="import_group_file" type="file" name="import_group_file" />
					</form></span>
				</div>
			</div>
		</div>
	</div>

	<table id="groupstable" class="panel">
	<thead class="panel-heading" >
		<tr>
			<th id="headerName" class="column-name">
				<div id="headerName-container" class="row">
					<div class="col-xs-3 col-sm-6">
						<div class="name sort columntitle" data-sort="descr">
							<span class="text-semibold"><?php p($l->t("Group name"));?></span>
						</div>
					</div>
				</div>
			</th>
			<th id="headerDisplay" class="column-display">
				<div class="display sort columntitle" data-sort="public">
					<span><?php p($l->t("Members"));?></span>
				</div>
			</th>
			<th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
				<div class="size sort columntitle" data-sort="size">
					<span><?php p($l->t("Status"));?></span>
				</div>
			</th>
			<th></th>
		</tr>
	</thead>
	
	<tbody id='fileList'>
<?php
$ownedGroups = OC_User_Group_Admin_Util::getOwnerGroups(OC_User::getUser());
$groupmemberships = OC_User_Group_Admin_Util::getUserGroups(OC_User::getUser(), false, false, false);
foreach ($ownedGroups as $group) {
	echo "<tr role=\"owner\" group=\"".$group['gid']."\" hiddenGroup=\"".$group['hidden']."\">
	<td class='groupname'>
	<div class='row'>
		<div class='col-xs-8 filelink-wrap'><a class='name'><i class='icon-users deic_green icon'></i>
				<span class='nametext'>".$group['gid']."</span></a></div>
	</div>
	</td>";
	$members = OC_User_Group_Admin_Util::usersInGroup($group['gid']);
	$size = count($members);
	echo "<td group=\"".$group['gid']."\"><div class='nomembers'><span id='nomembers'>$size</span></div></td>";
	echo "<td>Owner</td><td><a href='#' original-title='Delete' class='delete-group action icon icon-trash-empty'></a></td>
  		</tr>";
}

$count = 0;
foreach ($groupmemberships as $groupmembership) {
	$group = (string)$groupmembership["gid"];
	if(array_search($group, array_column($ownedGroups, 'gid'))!==FALSE){
		continue;
	}
	$role = 'Member';
	if($groupmembership["verified"]==OC_User_Group_Admin_Util::$GROUP_INVITATION_OPEN){
		$role = 'Pending';
	}
	elseif($groupmembership["verified"]==OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED){
		continue;
	}
	$count++;
	echo "<tr role=\"".strtolower($role)."\" group=\"$group\" hiddenGroup=\"".$groupmembership["hidden"]."\">
	<td class='groupname'><div class='row'>
		<div class='col-xs-8 filelink-wrap'><a class='name'><i class='icon-users deic_green icon'></i>
			<span class='nametext'>$group</span></a></div>
	</div></td>";
	$members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
	$size = count($members);
	echo "<td group=\"$group\"><div class='nomemberships'><span id='nomembers' >$size</span></div></td>";
	echo "<td>".$role."</td><td>".
		($groupmembership["hidden"]==="yes"?"":"<a href='#' original-title='Leave' class='delete-group action icon icon-trash-empty'></a>").
		"</td>
			</tr>";
}

if(\OC_User::isAdminUser(\OC_User::getUser())){
	// getGroups uses the local DB, but admin user is always on master, so that should be ok.
	$users_groups = OC_User_Group_Admin_Util::getGroups();
	foreach ($users_groups as $users_group) {
		$count++;
		echo "<tr role=\"admin\" group=\"$users_group\">
						<td class='groupname'><div class='row'>
						<div class='col-xs-8 filelink-wrap'><a class='name'><i class='icon-users deic_green icon'></i>
							<span class='nametext'> $users_group</span></a></div>
						</div></td>";
			$members = OC_User_Group_Admin_Util::usersInGroup( $users_group );
			$size = count($members) + 1;
			echo "<td group=\"$users_group\"><div class='nomemberships'>
							<span id='nomembers' >$size</span>
						</div></td>";
			echo "<td>Admin</td>
						<td><a href='#' original-title='Delete' class='action icon icon-trash-empty'></a></td>
					</tr>";
	}
}
?>
		</tbody>
		<tfoot>
			<tr class="summary text-sm">
				<td>
				<span class="info"><?php
				$allGroups = count($ownedGroups)+$count;
				echo $allGroups." group".($allGroups>1?"s":""); ?></span>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

<?php
function checkGroup($group, $groupUser){
	$groupname = $group["gid"];
	$acceptCode = $group["accept"];
	$declineCode = $group["decline"];
	$verified = $group["verified"];
	if($verified==OC_User_Group_Admin_Util::$GROUP_INVITATION_OPEN &&
			($_GET['code']===$acceptCode || $_GET['code']===$declineCode)){
		\OCP\Util::writeLog('User_Group_Admin', 'GROUP: '.$group["gid"], \OCP\Util::WARN);
		if(OC_User_Group_Admin_Util::updateStatus($groupname, $groupUser,
				$_GET['code']===$acceptCode?OC_User_Group_Admin_Util::$GROUP_INVITATION_ACCEPTED:
				OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED, true, $group["invitation_email"], $_GET['code'])){
			echo "<script type='text/javascript'>var url=window.location.href.replace('code=','nocode='); ".
			"OC.dialogs.alert('Welcome to the group ".$group["gid"].
			"', 'Welcome', function(){window.location.href=url}, true);</script>";
			//echo "<script type='text/javascript'>location.reload();</script>";
			//echo "<script type='text/javascript'>var url=window.location.href.replace('code=','nocode=');window.location.href=url;</script>";
			return true;
		}
	}
	return false;
}

if(!empty($_GET['code'])){
	// First try if this is a regular invitation
	$groupUser = OC_User::getUser();
	$groups = OC_User_Group_Admin_Util::getUserGroups($groupUser);
	$ret = false;
	foreach($groups as $group){
		$ret = $ret || checkGroup($group, $groupUser);
	}
	// Then check if it is an external invitation
	$groups = OC_User_Group_Admin_Util::getUserGroups(OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER);
	foreach($groups as $group){
		$ret = $ret || checkGroup($group, OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER);
	}
	if(!$ret){
		echo "<script type='text/javascript'>OC.dialogs.alert('Invalid code. You may already have accepted or declined membership.', 'Request invalid');</script>";
	}
}

echo "<div id='dialogalert' title='Confirmation' style='display:none;' ><p>Are you sure you want to delete this group?</p></div>";
?>

