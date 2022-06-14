<?php

OCP\App::checkAppEnabled('user_group_admin');
OCP\App::checkAppEnabled('files_sharding');

// Not necessary.
/*if(!\OCA\FilesSharding\Lib::isMaster()){
	$master = \OCA\FilesSharding\Lib::getMasterURL();
	\OC_Response::redirect($master);
}*/

$group = $_POST['group'];
$owner = OC_User_Group_Admin_Util::getGroupOwner($group);
$user = OCP\User::getUser();
$member = $_POST['member']

$result = false;

if(!empty($_POST['member']) && $_POST['member'] != OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER &&
		(checkOwner($user, $owner) || $_POST['member']==$user)){
	$result = OC_User_Group_Admin_Util::removeFromGroup($_POST['member'], $group);
	if(!empty($_POST['disable']) &&
			OC_User_Group_Admin_Util:: groupIsHidden($group) &&
			OC_User_Group_Admin_Util::ownerIsCurator($owner, $user)){
		$result = $result && OC_User_Group_Admin_Util::disableUser($owner, $group, $user);
	}
}

if($result){
	OCP\JSON::success();
}
else{
	OCP\JSON::error();
}