<?php
        $group=$_['group'];
        $members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
	$owner = OC_User::getUser();
	$ownerAvatar = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner title=\"".OC_User::getDisplayName($owner)."\" ><i class=\"fa fa-user\"></i><span class='left'
>$ownerAvatar &nbsp</span><span class='normaltext'><i>($owner) &nbsp</i></span><span class='ownertext'>Owner</span><br>
                </li><br>" ;	
        foreach ($members as $member) {
		$uid = $member["uid"];
		$status = $member["status"];
                if($status == 1){
                         $status = '';
                } elseif ($status == 0) {
                         $status = '<i style="color:#CDDC39">Pending...</i>';
                } else {
                        $status = '<i style ="color:#F44336">Member declined the invitation';
                }
                $name = OC_User_Group_Admin_Util::prepareUser($uid);
                echo "<li data-member=$uid title=\"".OC_User::getDisplayName($uid)."\" ><span class='left'>$name </span>
		<span class='normaltext'><i>($uid)</i></span>
                <span class=\"member-actions\" id='spanaction'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-cancel-circled\" style='color:#006b93; display:inline'></i></a>
                </span>
                <div style='font-size:80%; padding-right: 25px;'><strong style='padding-left:40px'>$status</strong> </div>
                </li>" ;
}  ?>

