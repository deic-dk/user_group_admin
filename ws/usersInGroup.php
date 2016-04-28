<?php
 
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');
if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}
$group = isset($_GET['gid'])?$_GET['gid']:'';
$users = OC_User_Group_Admin_Util::dbUsersInGroup($group);
\OCP\Util::writeLog('user_group_admin', 'Returning users '.serialize($users), \OC_Log::DEBUG);
OCP\JSON::encodedPrint($users);

