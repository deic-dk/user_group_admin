<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


$group = $_POST['group'];
$code = $_POST['code'];
$accept = $_POST['accept'];

if($accept==='yes'){
	$result = OC_User_Group_Admin_Util::updateStatus($group, OCP\USER::getUser(),
		OC_User_Group_Admin_Util::$GROUP_INVITATION_ACCEPTED);
}
elseif($accept==='no'){
	$result = OC_User_Group_Admin_Util::updateStatus($group, OCP\USER::getUser(),
		OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED);
}

return $result;

