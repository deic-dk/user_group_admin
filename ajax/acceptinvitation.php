<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


$group = $_POST['group'];
$accept = $_POST['accept'];

if($accept==='yes'){
	$result = OC_User_Group_Admin_Util::updateStatus($group, OCP\USER::getUser(),
		OC_User_Group_Admin_Util::$GROUP_INVITATION_ACCEPTED, true);
}
elseif($accept==='no'){
	$result = OC_User_Group_Admin_Util::updateStatus($group, OCP\USER::getUser(),
		OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED, true);
}

return $result;

