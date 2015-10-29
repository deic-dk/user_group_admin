<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}

$owner = isset($_GET['owner'])?$_GET['owner']:\OCP\User::getUser();
$groups = OC_User_Group_Admin_Util::dbGetOwnerGroups($owner);
\OCP\Util::writeLog('user_group_admin', 'Returning groups', \OC_Log::DEBUG);
OCP\JSON::encodedPrint($groups);
