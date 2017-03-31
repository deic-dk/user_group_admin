<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$uid = isset($_GET['uid'])?$_GET['uid']:\OCP\User::getUser();
$gid = isset($_GET['gid'])?$_GET['gid']:\OCP\User::getUser();
$limit = !empty($_GET['limit'])?$_GET['limit']:null;
$offset = !empty($_GET['offset'])?$_GET['offset']:null;
$caseInsensitive = !empty($_GET['caseInsensitive'])?$_GET['caseInsensitive']=='yes':false;

$groups = OC_User_Group_Admin_Util::dbSearchGroups($gid, $uid, $limit, $offset, $caseInsensitive);
\OCP\Util::writeLog('user_group_admin', 'Returning groups '.serialize($groups), \OC_Log::DEBUG);
OCP\JSON::encodedPrint($groups);

