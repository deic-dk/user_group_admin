<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}

$name = isset($_GET['group'])?$_GET['group']:'%';
$user = isset($_GET['userid'])?$_GET['userid']:'';
$accept = isset($_GET['accept'])?$_GET['accept']:'';
$decline = isset($_GET['decline'])?$_GET['decline']:'';
$owner = isset($_GET['owner'])?$_GET['owner']:OCP\USER::getUser ();
$memberRequest = !empty($_GET['memberRequest'])?$_GET['memberRequest']=='yes':false;
OC_User_Group_Hooks::dbGroupShare($name, $user, $owner, $memberRequest, $accept, $decline);
OCP\JSON::success();

