<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}

$owner = isset($_GET['owner'])?$_GET['owner']:\OCP\User::getUser();
$with_freequota = isset($_GET['with_freequota'])?$_GET['with_freequota']=='yes':false;
$search = isset($_GET['search'])?$_GET['search']:'%';
$caseInsensitive = isset($_GET['caseInsensitive'])?$_GET['caseInsensitive']=='yes':false;

$groups = OC_User_Group_Admin_Util::dbGetOwnerGroups($owner, $with_freequota, $search, $caseInsensitive);
\OCP\Util::writeLog('user_group_admin', 'Returning groups', \OC_Log::DEBUG);
OCP\JSON::encodedPrint($groups);
