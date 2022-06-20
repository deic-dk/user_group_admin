<?php

OCP\App::checkAppEnabled('user_group_admin');
OCP\App::checkAppEnabled('files_sharding');

// Not necessary.
/*if(!\OCA\FilesSharding\Lib::isMaster()){
	$master = \OCA\FilesSharding\Lib::getMasterURL();
	\OC_Response::redirect($master);
}*/

$group = $_GET['group'];
$owner = OC_User_Group_Admin_Util::getGroupOwner($group);
$user = OCP\User::getUser();
$member = $_GET['member'];

$result = false;

if(!empty($member) && $member != OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER &&
		(OC_User::isAdminUser($user) || !empty($owner) && $user===$owner || $member==$user)){
			$result = OC_User_Group_Admin_Util::removeFromGroup($member, $group);
			if(!empty($_GET['disable']) &&
				//OC_User_Group_Admin_Util:: groupIsHidden($group) &&
					OC_User_Group_Admin_Util::ownerIsCurator($owner, $member)){
				$result = $result && OC_User_Group_Admin_Util::disableUser($owner, $group, $member);
	}
}

if($result){
	OCP\JSON::success();
}
else{
	OCP\JSON::error();
}