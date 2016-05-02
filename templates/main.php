<div id="app-content" style="transition: all 0.3s ease 0s;">
<div id="app-content-user_group_admin" class="viewcontainer">
<div id="controls">
  <div class="row">
    <div class="text-right" style="margin-right: 19px;">
      <div class="actions creatable">
        <div id="create" original-title="">
		  <a id="create" class="btn btn-primary btn-flat" href="#"><i class="icon-users"></i>
               New group
          </a>
	<div id="importgroup" class="btn-group">
		<a id="importgroup" type="button" class="btn btn-default btn-flat">Import</a>
	</div>
     </div>
    </div>
      </div>
  </div>
  <div id="newgroup" class="apanel">
     <span class="spanpanel" >
	  <input class="editgroup" id="newgroup" type="text" placeholder="New group name">

	    <span class="newgroup-span">
		  <div id="ok" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Add</a>
          </div>
          <div id="cancel" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Cancel</a>
          </div>
	    </span>
      </span>
  </div>
 <div id="importnew" class="apanel">
    <div class="spanpanel">
	Import group from text file:
	<span class="newimportform">
	<form  id="import_group_form" class="btn btn-default btn-flat" action="<?php echo OCP\Util::linkTo('user_group_admin', 'ajax/import.php'); ?>"  method="post" enctype="multipart/form-data">
	<span>Choose File</span><input id="import_group_file" type="file" name="import_group_file" />
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
		  <span class="text-semibold">Group name</span>
        </div>
      </div>
    </div>
  </th>
  <th id="headerDisplay" class="column-display">
    <div class="display sort columntitle" data-sort="public">
      <span>Members</span>
    </div>
  </th>
  <th id="headerDisplay" class="column-display" style="padding-right:3%; width:1%">
    <div class="size sort columntitle" data-sort="size">
      <span>Status</span>
    </div>
  </th>
  <th></th>
</tr>
</thead>
<tbody id='fileList'>
<?php
	$ownedGroups = OC_User_Group_Admin_Util::getOwnerGroups(OC_User::getUser());
	$groupmemberships = OC_User_Group_Admin_Util::getUserGroups (OC_User::getUser());
	foreach ($ownedGroups as $group) {
		echo "<tr role=\"owner\" group=\"$group\">
		<td class='groupname'>
		<div class='row'>
			<div class='col-xs-8 filelink-wrap'><a class='name'><i class='icon-users deic_green icon'></i>
					<span class='nametext'>$group</span></a></div>
		</div>
		</td>";
		$members = OC_User_Group_Admin_Util::usersInGroup( $group );
		$size = count($members);
		echo "<td group=\"$group\"><div class='nomembers'><span id='nomembers'>$size</span></div></td>";
		echo "<td>Owner</td><td><a href='#' original-title='Delete' class='delete-group action icon icon-trash-empty'></a></td>
	  		</tr>";
	}

	$count = 0;
	foreach ($groupmemberships as $groupmembership) {
		$group = (string)$groupmembership["gid"];
		if($groupmembership["verified"]!=1 || in_array($group, $ownedGroups)){
			continue;
		}
		$count++;
		echo "<tr role=\"member\" group=\"$group\">
		<td class='groupname'><div class='row'>
			<div class='col-xs-8 filelink-wrap'><a class='name'><i class='icon-users deic_green icon'></i>
				<span class='nametext'>$group</span></a></div>
		</div></td>";
		$members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
		$size = count($members);
		echo "<td group=\"$group\"><div class='nomemberships'><span id='nomembers' >$size</span></div></td>";
		echo "<td>Member</td><td><a href='#' original-title='Leave' class='delete-group action icon icon-trash-empty'></a></td>
				</tr>";
	}

	if(\OC_User::isAdminUser(\OC_User::getUser())){
		$users_groups = OC_User_Group_Admin_Util::getGroupsForAdmin();
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
</div>

<?php
if(!empty($_GET['code'])){
	$groups = OC_User_Group_Admin_Util::getUserGroups (OC_User::getUser ());
	foreach($groups as $group){
		$groupname = $group["gid"];
		$acceptCode = $group["accept"];
		$declineCode = $group["decline"];
		if($_GET['code']==$acceptCode &&
			OC_User_Group_Admin_Util::updateStatus($groupname, OCP\USER::getUser (),
				OC_User_Group_Admin_Util::$GROUP_INVITATION_ACCEPTED, true)){
			echo "<script type='text/javascript'>location.reload();</script>";
			break;
		}
		elseif($declineCode==$_GET['code'] &&
			OC_User_Group_Admin_Util::updateStatus($groupname, OCP\USER::getUser (),
				OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED, true)){
			echo "<script type='text/javascript'>location.reload();</script>";
			break;
		}
	}
}

echo "<div id='dialogalert' title='Delete Confirmation' style='display:none;' ><p>Are you sure you want to delete this group?</p></div>";
?>

