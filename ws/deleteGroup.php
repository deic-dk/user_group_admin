<?php

OCP\JSON::checkAppEnabled('files_accounting');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}
$gid = isset($_GET['name'])?$_GET['name']:null;
$userid = isset($_GET['userid'])?$_GET['userid']:null;
$ret = OC_User_Group_Admin_Util::dbDeleteGroup($gid, $userid);
\OCP\Util::writeLog('user_group_admin', 'Deleted group '.$ret, \OC_Log::WARN);
OCP\JSON::encodedPrint($ret);

