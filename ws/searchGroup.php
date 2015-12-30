<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}

$name = isset($_GET['name'])?$_GET['name']:'%';
$user = isset($_GET['userid'])?$_GET['userid']:\OCP\User::getUser();
$groupInfo = OC_User_Group_Admin_Util::dbSearchGroup($name,$user);
\OCP\Util::writeLog('user_group_admin', 'Returning group info '.serialize($groupInfo), \OC_Log::DEBUG);
OCP\JSON::encodedPrint($groupInfo);

