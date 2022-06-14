<?php
 
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');
if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}
$owner = isset($_GET['owner'])?$_GET['owner']:'';
$user = isset($_GET['user'])?$_GET['user']:'';
$res = OC_User_Group_Admin_Util::dbOwnerIsCurator($owner, $user)?"yes":"no";
OCP\JSON::encodedPrint($res);
