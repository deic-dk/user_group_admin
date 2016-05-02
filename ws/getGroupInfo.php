<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}

$group = isset($_GET['gid'])?$_GET['gid']:\OCP\User::getUser();
$info = OC_User_Group_Admin_Util::dbGetGroupInfo($group);
OCP\JSON::encodedPrint($owner);
