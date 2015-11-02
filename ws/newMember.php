<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$userid = isset($_GET['userid'])?$_GET['userid']:'';
$name = isset($_GET['name'])?$_GET['name']:'';
$owner = isset($_GET['owner'])?$_GET['owner']:OCP\USER::getUser ();
$user = OC_User_Group_Admin_Util::dbAddToGroup($userid,$name,$owner);

\OCP\Util::writeLog('user_group_admin', 'Add user '.$user, \OC_Log::WARN);
OCP\JSON::encodedPrint($user);

