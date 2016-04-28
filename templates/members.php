<?php

$group=$_['group'];
if(\OC_User::isAdminUser(\OC_User::getUser())){
	$owner = OC_User_Group_Admin_Util::getGroupOwner($group);
}
else{
	$owner = \OC_User::getUser();
}
$members = OC_User_Group_Admin_Util::usersInGroup($group);
$numMembers = count($members);
$ownerAvatar = OC_User_Group_Admin_Util::prepareUser($owner);

echo "<div class='owner'>Owner</div>";

$name = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner><span class='left'>$name </span></li>";

echo "<div class='memberscount' members='".$numMembers."'>".$numMembers." member".($numMembers>1?"s":"")."</div>";

foreach($members as $member){
	$uid = $member["uid"];
	$status = $member["verified"];
	if($status == 1){
		$status = '';
	}
	elseif ($status == 0){
		$status = '<i class="group_pending">Pending...</i>';
	}
	else{
		$status = '<i class="group_declined">Member declined the invitation';
	}
	$name = OC_User_Group_Admin_Util::prepareUser($uid);
	
	echo "<li data-member=$uid><span class='left'>$name </span>
		<span class='normaltext'><i>($uid)</i></span>
		<span class=\"member-actions\" id='spanaction'>
		<a href=# class='removemember' original-title=" . $l->t('Remove') . ">
		<i class=\"icon icon-cancel-circled\"></i></a></span>
		<span class='group_status'>$status</span>
		</li>" ;
}

