<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}

$name = isset($_GET['group'])?$_GET['group']:'%';
$user = isset($_GET['userid'])?$_GET['userid']:'';
$owner = isset($_GET['owner'])?$_GET['owner']:OCP\USER::getUser ();
$externalUser = isset($_GET['externalUser'])?$_GET['externalUser']==='yes':false;
$activityHook =  OC_User_Group_Hooks::dbGroupJoin($name,$user,$owner, $externalUser);
OCP\JSON::encodedPrint($activityHook);

