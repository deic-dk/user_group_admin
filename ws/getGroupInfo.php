<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$group = $_GET['gid'];
$info = OC_User_Group_Admin_Util::dbGetGroupInfo($group);
OCP\JSON::encodedPrint($info);
