<?php

$group=$_['group'];
$owner = OC_User_Group_Admin_Util::getGroupOwner($group);
$members = OC_User_Group_Admin_Util::usersInGroup($group);
$numMembers = count($members);

echo "<div class='owner'>Owner</div>";

$name = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner><span class='left'>$name </span></li>";

echo "<div class='memberscount' members='".$numMembers."'>".$numMembers." member".($numMembers>1?"s":"")."</div>";

foreach($members as $member){
	$uid = $member["uid"];
	$status = $member["verified"];
	$statusStr = "";
	if($status==OC_User_Group_Admin_Util::$GROUP_INVITATION_OPEN){
		$statusStr = '<i class="group_pending">Pending...</i>';
	}
	elseif ($status===OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED){
		$statusStr = '<i class="group_declined">Member declined the invitation';
	}
	$name = OC_User_Group_Admin_Util::prepareUser($uid);
	
	echo "<li data-member=$uid><span class='left'>$name </span>
		<span class='normaltext'><i>($uid)</i></span>
		<span class=\"member-actions\" id='spanaction'>
		<a href=# class='removemember' original-title=" . $l->t('Remove') . ">
		<i class=\"icon icon-cancel-circled\"></i></a></span>
		<span class='group_status'>$statusStr</span>
		</li>" ;
}

