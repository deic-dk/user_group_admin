<!--This applies to the new version of My Groups-->
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
  <div id="newgroup" class="panel-heading apanel">
     <span>
	  <input class="editgroup" id="newgroup" type="text" placeholder="New group name..."> 
	  
	    <span class="newgroupform">	
		  <div id="ok" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Ok</a>
          </div>
          <div id="cancel" class="btn-group" original-title="">
		    <a class="btn btn-default btn-flat" href="#">Cancel</a>
          </div>
	    </span>
      </span>
  </div>
 <div id="importnew" class="panel-heading apanel">
    <span>
	Import group from text file:
	<span class="newimportform" style="margin-left:30px; margin-bottom:20px; position:absolute">
	<form  id="import_group_form" action="<?php echo OCP\Util::linkTo('user_group_admin', 'ajax/import.php'); ?>"  method="post" enctype="multipart/form-data">
	<input id="import_group_file" type="file" name="import_group_file"> 
        </form></span>
    </span>
</div>

 </div> 
</div>
<table id="groupstable" class="panel" style="width=20px;">
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
		echo "<tr id='owner' class=\"$group\"><td id=\"$group\" class='groupsname' name=\"$group\" data-group=\"$group\" style='height:34px' ><div class='row'><div class='col-xs-1 text-right '></div>
		<div class='col-xs-8 filelink-wrap' style='padding-left:4px;'><a class='name'><i class='icon-users     deic_green icon'></i>
		       <span class='nametext'>$group</span></a></div>
			<div class='col-xs-3 fileactions-wrap text-right'>
			<div class='btn-group btn-group-xs fileactions'>
				<a  class='btn btn-flat btn-default action-primary action action-edit' id='removegroup' href='#'>Delete</a>
				<a id='dropdownbtn' class='btn btn-flat btn-default dropdown-toggle' data-toggle='dropdown' href='#' aria-expanded='true'>
					<i class='icon-angle-down'></i>
		 	        </a>	
				<ul class='dropdown-menu' style='display:none;'>
					<li><a id='exportgroup' data-action='Export' href='#'>
					<i class='icon-export-alt'>&nbsp</i>	<span>Export</span>
					</a></li>
				</ul>
			</div>

		</div></div></div>
		</td>";
		$members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
                $size = count($members) + 1;
                echo "<td id='members' class=\"$group\"><div class='nomembers'><span id='nomembers'>$size</span>
                </div></td>";
            echo "<td>Owner</td></tr>";
	}
	foreach ($groupmemberships as $groupmembership) {
	         $ingroup = OC_User_Group_Admin_Util::searchUser ( $groupmembership, OC_User::getUser (), '1' );	
		if ($ingroup) {
	         echo "<tr id='member' class=\"$group\"><td  id=\"$groupmembership\" class='groupsname' data-group=\"$groupmembership\" style='height:34px;' ><div class='row'><div class='col-xs-4 col-sm-1'></div>
		<div class='col-xs-8 filelink-wrap' style='padding-left:4px;'><a class='name'><i class='icon-users     deic_green icon'></i> 
                <span class='nametext'>	$groupmembership</span></a></div>
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
	        $size = count($members) + 1;	
		echo "<td id='memberships' class=\"$groupmembership\"><div class='nomemberships'><span id='nomembers' >$size</span>
 	        </div></td>";
	
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
if (isset($_GET ['code']))  { 
	$groups = OC_User_Group_Admin_Util::getUserGroups ( OC_User::getUser () );
	foreach ( $groups as $group ) {
		$verified = OC_User_Group_Admin_Util::acceptedUser ( $group, OC_User::getUser (), '0', $_GET ['code']);
		$declined = OC_User_Group_Admin_Util::declinedUser ( $group, OC_User::getUser (), '0', $_GET ['code']);
		$checkagain = OC_User_Group_Admin_Util::acceptedUser ( $group, OC_User::getUser (), '2', $_GET ['code']);
	
		if ($verified || $checkagain) {
			echo "<script type='text/javascript'>
					location.reload();
				</script>";
			$result = OC_User_Group_Admin_Util::acceptInvitation ( $group, OCP\USER::getUser () );
		} elseif ($declined) {
			 echo "<script type='text/javascript'>
                                        location.reload();
                                </script>";
			$result = OC_User_Group_Admin_Util::declineInvitation ( OCP\USER::getUser (), $group );
	        }
	}
}
echo "<div id='dialogalert' title='Delete Confirmation' style='display:none;' ><p>Are you sure you want to delete this group?</p></div>";
?>



