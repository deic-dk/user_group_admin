<?php
$group=$_['group'];
$members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
$size = count($members); ?>
		<div class='groupname' id=<?php echo $group;?>><strong><?php echo $group;?></strong></div> 
                <strong class='left'>Members</strong><br>
<?php 
	$group=$_['group'];
	$members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
	$size = count($members);
	if ($size==0) {
                        echo "<div class='left'><i>No members yet</i></div>";
                }   
	foreach ($members as $member) {
                $groupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '1');
                $notgroupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '0');
                if($groupmembers){
                         $status = '';
                } elseif ($notgroupmembers) {
                         $status = 'Pending...';
                } else {
                        $status = 'Member declined the invitation';
                }
                $name = OC_User::getDisplayName($member) ;
		echo "<li data-member=$member title=\"".OC_User::getDisplayName($member)."\" ><i class=\"fa fa-user\"></i><div class='left' 
>$name</div>
                <span class=\"member-actions\" style='float:right'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-cancel-circled\"></i></a>
                </span>
                <span style='float:right'><i>($member) &nbsp</i></span><br>
                <div style='float:right; padding-right: 25px;'><i>$status </i></div>
                </li><br>" ;
}  ?>




