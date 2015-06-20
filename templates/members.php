<?php

        $group=$_['group'];
        $members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
        $size = count($members) +1;
	$owner = OC_User::getUser();
	$ownerAvatar = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner title=\"".OC_User::getDisplayName($owner)."\" ><i class=\"fa fa-user\"></i><span class='left'
>$ownerAvatar &nbsp</span><span class='normaltext'><i>($owner) &nbsp</i></span><span class='ownertext'>Owner</span><br>
                </li><br>" ;	
        foreach ($members as $member) {
                $groupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '1');
                $notgroupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '0');
                if($groupmembers){
                         $status = '';
                } elseif ($notgroupmembers) {
                         $status = '<i style="color:#CDDC39">Pending...</i>';
                } else {
                        $status = '<i style ="color:#F44336">Member declined the invitation';
                }
                $name = OC_User_Group_Admin_Util::prepareUser($member);
                echo "<li data-member=$member title=\"".OC_User::getDisplayName($member)."\" ><span class='left'>$name </span>
		<span class='normaltext'><i>($member)</i></span>
                <span class=\"member-actions\" id='spanaction'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-cancel-circled\" style='color:#006b93; display:inline'></i></a>
                </span>
                <div style='font-size:80%; padding-right: 25px;'><strong style='padding-left:40px'>$status</strong> </div>
                </li>" ;
}  ?>
