<!--This applies to the new version of My Groups-->

<div id="app-content" style="transition: all 0.3s ease 0s;">
<div id="app-content-meta_data" class="viewcontainer">
<div id="controls">
  <div class="row">
    <div class="col-sm-12 text-right">
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
  <div id="newgroup" class="panel-heading" style="border:solid 1px #e4e4e4; margin-bottom:20px; height:55px; display:none">
     <span>
	  <input class="editgroup" id="newgroup" type="text" placeholder="New group name..."> 
	  
	    <span style="margin-left:20px; margin-bottom:20px; position:absolute">	
		  <div id="ok" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Ok</a>
          </div>
          <div id="cancel" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Cancel</a>
          </div>
	    </span>
      </span>
  </div>
 <div id="importnew" class="panel-heading" style="border:solid 1px #e4e4e4; margin-bottom:20px; height:55px; display:none">
    <span>
	Import group from text file:
	<span style="margin-left:30px; margin-bottom:20px; position:absolute">
	<form  id="import_group_form" action="<?php echo OCP\Util::linkTo('user_group_admin', 'ajax/import.php'); ?>"  method="post" enctype="multipart/form-data">
        <input id="import_group_file" type="file" name="import_group_file" />
        </form></span>
    </span>
</div>

 </div> 
</div>

<table id="filestable" class="panel" style="width=20px;">
<thead class="panel-heading">
<tr>
  <th id="headerName" class="column-name">
    <div id="headerName-container" class="row">
      <div class="col-xs-4 col-sm-1"></div>
      <div class="col-xs-3 col-sm-6">	  
        <div class="name sort columntitle" data-sort="descr">
		  <span class="text-semibold">Group name</span>         
          <span class="sort-indicator hidden icon-triangle-n"></span>
	    </div>
      </div>
    </div>
  </th>
  <th id="headerDisplay" class="column-display">
    <div class="display sort columntitle" data-sort="public">
      <span>Members</span>
      <span class="sort-indicator hidden icon-triangle-n"></span>
    </div>
  </th>
  <th id="headerDisplay" class="column-display">
    <div class="size sort columntitle" data-sort="size">
      <span>Status</span>
      <span class="sort-indicator hidden icon-triangle-n"></span>
    </div>
  </th>
  
</tr>
</thead>
<tbody id='fileList'>
<?php
	$groups = $_['groups'] ;	
	$groupmemberships = OC_User_Group_Admin_Util::getUserGroups ( OC_User::getUser () );
	foreach ($groups as $group) {
		echo "<tr id='owner'><td id=\"$group\" class='groupsname' data-group=\"$group\"  style='height:34px;' ><div class='row'><div class='col-xs-1 text-right '></div>
		<div class='col-xs-8 filelink-wrap'><i class='icon-users     deic_green icon'>&nbsp</i>
		<span class='nametext'>$group</span></div>
			<div class='col-xs-3 fileactions-wrap text-right'>
			<div class='btn-group btn-group-xs fileactions'>
				<a id='invite' class='btn btn-flat btn-default action-primary action action-edit' href='#'>Invite</a>
				<a id='dropdownbtn' class='btn btn-flat btn-default dropdown-toggle' data-toggle='dropdown' href='#' aria-expanded='true'>
					<i class='icon-angle-down'></i>
		 	        </a>	
				<ul class='dropdown-menu' style='display:none;'>
					<li><a id='exportgroup' data-action='Export' href='#'>
					<i class='icon-export-alt'>&nbsp</i>	<span>Export</span>
					</a></li>
					<li class='removegroup' ><a id='removegroup' data-action='Invite' href='#'>
					<i class='icon-trash'>&nbsp</i>	<span>Delete</span>

					</a></li>
				</ul>
			</div>
			<div id='dropdown' class=\"$group\" data-item-type='folder' style='display:none;' >
                        <input id='mkgroup' type='text' placeholder='Invite user ...' class='ui-autocomplete-input' autocomplete='off'>
                        <span role='status' aria-live='polite' class='ui-helper-hidden-accessible'></span>
                        <div id='invitation' style='display:none;'>An invitation was sent to the user.</div>
                </div>

		</div></div></div>
		</td>";
		$members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
                $size = count($members);
                echo "<td id='members' class=\"$group\"><div class='nomembers'><a id='nomembers' href='#'>$size</a>
                     <div id='dropdown' class='drop' data-item-type='folder' style='overflow-y: scroll; display:none;' >
                        <span role='status' aria-live='polite' class='ui-helper-hidden-accessible'></span>
                <strong style='float:left;'>Members</strong><br>";
                if ($size==0) {
                        echo "<div style='float:left;'><i>No members yet</i></div>";
                }
            foreach ($members as $member) {
                $groupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '1');
                $notgroupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '0');
                if($groupmembers){
                         $status = '';
               } elseif ($notgroupmembers) {
                         $status = 'Pending...';
               } else {
                        $status = 'User declined the invitation';
                }
                $name = OC_User::getDisplayName($member) ;
                echo "<li data-member=$member title=\"".OC_User::getDisplayName($member)."\" ><i class=\"fa fa-user\"></i><div style='float:left;'>$name</div>
                <span class=\"member-actions\" style='float:right'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-cancel-circled\"></i></a>
                </span>
                <span style='float:right'><i>($member) &nbsp</i></span><br>
                <div style='float:right; padding-right: 25px;'><i>$status </i></div>
                </li><br>" ;
            }

            echo "</div></div></td>";
            echo "<td>Owner</td></tr>";
	}
	foreach ($groupmemberships as $groupmembership) {
	         $ingroup = OC_User_Group_Admin_Util::searchUser ( $groupmembership, OC_User::getUser (), '1' );	
		if ($ingroup) {
	         echo "<tr id='member'><td  id=\"$groupmembership\" class='groupsname' data-group=\"$groupmembership\" style='height:34px;' ><div class='row'><div class='col-xs-4 col-sm-1'></div>
		<div class='col-xs-8 filelink-wrap'><i class='icon-users     deic_green icon'>&nbsp</i> 

	$groupmembership</div>
			<div class='col-xs-3 fileactions-wrap text-right'>
			<div id='dropdownbtn' class='btn-group btn-group-xs fileactions'>
                                <a id='removegroup' class='btn btn-flat btn-default action-primary action action-edit' href='#'>Delete</a>
                                <a id='dropdownbtn' class='btn btn-flat btn-default dropdown-toggle' data-toggle='dropdown' href='#' aria-expanded='true'>
                                        <i class='icon-angle-down'></i>
                                </a>
                                <ul class='dropdown-menu' style='display:none;'>
                                        <li><a id='exportgroup' data-action='Export' href='#'>
                                         <i class='icon-export-alt'>&nbsp</i>       <span>Export</span>
                                        </a></li>
                                </ul>
                        </div></div>
		</div></div>	
		</td>";
		$members = OC_User_Group_Admin_Util::usersInGroup( $groupmembership ) ;
	        $size = count($members);	
		echo "<td id='memberships'><div class='nomembers'><a id='nomembers' href='#'>$size</a>
                     <div id='dropdown' class='drop' data-item-type='folder' style='overflow-y: scroll; display:none;' >
                        <span role='status' aria-live='polite' class='ui-helper-hidden-accessible'></span>
		<div ><strong style='float:left;' >Owner</strong></div><br>";
      		$stmt = OC_DB::prepare( "SELECT `owner` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?" ); 
      		$result = $stmt->execute( array($groupmembership));
	  	$owners = array();                                                                                                           
      		while ($row = $result->fetchRow()) {                                                                                         
        		$owners[] = $row['owner'];                                                                                                 
      		}   
      		foreach ($owners as $member) {
			$name = OC_User::getDisplayName($member) ;
        	echo "<li data-member=$member><i class=\"fa fa-user\"></i><div style='float:left;'> $name</div>
			<span  style='float:right;'><i>($member)</i></span>
              	</li><br>" ;
      		}
		////////////////////////
		echo "<strong style='float:left;'>Members</strong>";
            	foreach ($members as $member) {
                	$name = OC_User::getDisplayName($member) ;
                	echo "<br><li data-member=$member title=\"".OC_User::getDisplayName($member)."\"><i class=\"fa fa-user\"></i><div style='float:left;'>$name</div>
                	<span  style='float:right;'><i>($member)</i></span>
                	</li>" ;
            	}  

 	        echo "</div></div></td>";
	
		echo "<td>Member</td></tr>";
		}}
	
       ?>
</tbody> 
<tfoot>
	<tr class="summary text-sm">
		<td>
			
		       <span class="info"><?php
			$memberships=array();
		        foreach ($groupmemberships as $groupmembership) {
				$ingroup = OC_User_Group_Admin_Util::searchUser ( $groupmembership, OC_User::getUser (), '1' );
				if ($ingroup) {
					array_push($memberships, $groupmembership);
				}
			}	
 			echo count($groups)+count($memberships)." groups"; ?></span>
		</td>
	</tr>
    
</tfoot>


</table>

</div>
</div>

<?php
$groups = OC_User_Group_Admin_Util::getUserGroups ( OC_User::getUser () );
// ioanna
foreach ( $groups as $group ) {
	$ingroup = OC_User_Group_Admin_Util::searchUser ( $group, OC_User::getUser (), '1' );
	$notification = OC_User_Group_Admin_Util::isNotified ( $group, OC_User::getUser (), '0', '0' );
	$verified = OC_User_Group_Admin_Util::acceptedUser ( $group, OC_User::getUser (), '0', $_GET ['code'], '0' );
	$declined = OC_User_Group_Admin_Util::declinedUser ( $group, OC_User::getUser (), '0', $_GET ['code'], '0' );
	$checkagain = OC_User_Group_Admin_Util::acceptedUser ( $group, OC_User::getUser (), '2', $_GET ['code'], '0' );
        $owner = OC_User_Group_Admin_Util::groupOwner($group);
	
	
	if ($group != 'dtu.dk') {
		if ($verified || $checkagain) {
			echo "<script type='text/javascript'>
					window.alert(\"You have accepted the invitation to the following group:  $group\");
					location.reload();
				</script>";
			$result = OC_User_Group_Admin_Util::acceptInvitation ( $group, OCP\USER::getUser () );
			$status = OC_User_Group_Admin_Util::Notification ( OCP\USER::getUser (), $group, $_GET ['code'] );
		} elseif ($declined) {
			echo "<script type='text/javascript'>
                                	window.alert(\"You have declined the invitation to the following group:  $group\");
					location.reload();
                        	</script>";
			$result = OC_User_Group_Admin_Util::declineInvitation ( OCP\USER::getUser (), $group );
			$status = OC_User_Group_Admin_Util::Notification ( OCP\USER::getUser (), $group, $_GET ['code'] );
	        }elseif ($notification == true && isset ( $_GET ['code'] ) == false) {
			echo "<div id='dialog' title='Group Invitation'>
  <p>You have been invited to the following group: <div id = 'group1' value = \"$group\"> $group</div> by <b>$owner</b> . Press Accept to accept the invitation or Decline to reject it</p>
</div>";
			echo "<script type='text/javascript'>
                                         $( '#dialog' ).dialog({ buttons: [ { id:'test','data-test':'data test', text: 'Accept', click: function() {
                                        $.ajax({
                                        url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                                        type: 'post',
                                        data: { 'group': $('#group1').attr('value'), 'action': 'acceptinvitation'},
                                        success: function(data, status) {
						location.reload();		
                                        }
                                        });
                                         $(this).dialog( 'close' ); } },
                                        { id:'test2','data-test':'data test', text: 'Decline', click: function() {
                                        $.ajax({
                                        url:OC.linkTo('user_group_admin', 'ajax/notification.php'),
                                        type: 'post',
                                        data: {'group': $('#group1').attr('value'), 'action': 'declineinvitation'},
                                        success: function(data, status) {
						location.reload();
                                        }
                                        });
                                        $(this).dialog( 'close' ); } } ] });
                                </script>";
			break;
		}
	}
}


?>



<div class="hidden" id="deleteConfirm" title="<?php p($l->t('Delete tag')) ?>"> 
    <div>
        <span id="deleteType"></span>
        <?php p($l->t('Are you sure you want to delete the tag:')) ?><br />
        <div style="width: 100%; text-align: center; padding: 5px 0px 15px 0px; font-weight: bold;" id="tagToDelete"></div>
        <?php p($l->t('This operation cannot be undone.')) ?><br />
    </div>
    <input type="hidden" name="deleteID" id="deleteID" value="-1" />
</div>
