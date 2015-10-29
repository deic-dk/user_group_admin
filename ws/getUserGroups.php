<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$user = isset($_GET['userid'])?$_GET['userid']:\OCP\User::getUser();
$groups = OC_User_Group_Admin_Util::dbGetUserGroups($user);
\OCP\Util::writeLog('user_group_admin', 'Returning groups '.serialize($groups), \OC_Log::DEBUG);
OCP\JSON::encodedPrint($groups);
