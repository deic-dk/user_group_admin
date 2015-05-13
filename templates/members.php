<?php

        $group=$_['group'];
        $members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
        $size = count($members) +1;
	$owner = OC_User::getUser();
	$ownerAvatar = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner title=\"".OC_User::getDisplayName($owner)."\" ><i class=\"fa fa-user\"></i><span class='left'
>$ownerAvatar &nbsp</span><span style=''><i>($owner) &nbsp</i></span><span style=''>Owner</span><br>
                
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
                echo "<li data-member=$member title=\"".OC_User::getDisplayName($member)."\" ><i class=\"fa fa-user\"></i><span class='left'
>$name &nbsp</span>
                <span class=\"member-actions\" style='float:right'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-cancel-circled\"></i></a>
                </span>
                <span style=''><i>($member) &nbsp</i></span><br>
                <div style=' padding-right: 25px;'><i>$status </i></div>
                </li><br>" ;
}  ?>
