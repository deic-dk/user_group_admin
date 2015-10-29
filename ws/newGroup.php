<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$userid = isset($_GET['userid'])?$_GET['userid']:'';
$name = isset($_GET['name'])?$_GET['name']:'';
$group = OC_User_Group_Admin_Util::dbCreateGroup($name, $userid);

\OCP\Util::writeLog('user_group_admin', 'New group '.$group, \OC_Log::WARN);
OCP\JSON::encodedPrint($group);

