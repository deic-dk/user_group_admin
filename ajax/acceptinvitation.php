<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


$group = $_POST['group'];
$accept = $_POST['accept'];
$user = !empty($_POST['user'])?$_POST['user']:OCP\USER::getUser();

$owner = OC_User_Group_Admin_Util::getGroupOwner($group);

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

