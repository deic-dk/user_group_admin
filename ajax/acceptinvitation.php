<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


$group = $_POST['group'];
$accept = $_POST['accept'];

$owner = OC_User_Group_Admin_Util::getGroupOwner($group);

$user = OCP\USER::getUser();

if($accept==='yes'){
	$result = OC_User_Group_Admin_Util::updateStatus($group, $user,
		OC_User_Group_Admin_Util::$GROUP_INVITATION_ACCEPTED, true);
	OC_User_Group_Hooks::groupJoin($group, $user, $owner);
}
elseif($accept==='no'){
	$result = OC_User_Group_Admin_Util::updateStatus($group, $user,
		OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED, true);
	OC_User_Group_Hooks::groupLeave($group, $user, $owner);
}

return $result;

