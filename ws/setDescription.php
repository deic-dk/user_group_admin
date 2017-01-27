<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
        http_response_code(401);
        exit;
}

$name = isset($_GET['group'])?$_GET['group']:'%';
$description = isset($_GET['description'])?$_GET['description']:'';

$ret =  OC_User_Group_Admin_Util::dbsetDescription($description, $name);

if($ret){
	OCP\JSON::success();
}
else{
	OCP\JSON::error();
}

