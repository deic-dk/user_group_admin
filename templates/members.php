<?php

        $group=$_['group'];
        $members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
        $size = count($members) +1;
	$owner = OC_User::getUser();
	$ownerAvatar = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner title=\"".OC_User::getDisplayName($owner)."\" ><i class=\"fa fa-user\"></i><span class='left'
>$ownerAvatar &nbsp</span><span style='font-size:80%'><i>($owner) &nbsp</i></span><span style='font-size:80%; color:#FF8C00'>Owner</span><br>
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
		//echo $name;
                echo "<li data-member=$member title=\"".OC_User::getDisplayName($member)."\" ><span class='left'>$name </span>
		<span style='font-size:80%'><i>($member)</i></span>
                <span class=\"member-actions\" style='display:inline-block; position:relative; padding-top:4px; float:right;'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-cancel-circled\" style='color:#006b93; display:inline'></i></a>
                </span>
                <div style='font-size:80%; padding-right: 25px;'><strong style='padding-left:40px'>$status</strong> </div>
                </li>" ;
}  ?>
