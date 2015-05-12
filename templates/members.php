<?php

        $group=$_['group'];
        $members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
        $size = count($members) +1;
        if ($size==1) {
                        echo "<div id=\"emptysearch\">No members yet</div>";
                }
	$owner = OC_User::getUser();
	$ownerAvatar = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner title=\"".OC_User::getDisplayName($owner)."\" ><i class=\"fa fa-user\"></i><div class='left'
>$ownerAvatar</div><span style='float:right'><i>($owner) &nbsp</i></span><br>
                
                </li><br>" ;	
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
                $name = OC_User_Group_Admin_Util::prepareUser($member);
		//echo $name;
                echo "<li data-member=$member title=\"".OC_User::getDisplayName($member)."\" ><i class=\"fa fa-user\"></i><div class='left'
>$name</div>
                <span class=\"member-actions\" style='float:right'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-cancel-circled\"></i></a>
                </span>
                <span style='float:right'><i>($member) &nbsp</i></span><br>
                <div style='float:right; padding-right: 25px;'><i>$status </i></div>
                </li><br>" ;
}  ?>
