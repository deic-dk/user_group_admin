<?php
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');
if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}
$gid = isset($_GET['name'])?$_GET['name']:null;
$userid = isset($_GET['userid'])?$_GET['userid']:null;
$status = isset($_GET['status'])?$_GET['status']:null;

$ret = OC_User_Group_Admin_Util::dbUpdateStatus($gid, $userid, $status);

\OCP\Util::writeLog('user_group_admin', 'Updated status to '.$ret, \OC_Log::WARN);
OCP\JSON::encodedPrint($ret);

