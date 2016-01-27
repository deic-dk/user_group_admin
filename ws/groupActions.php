<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}
$action = isset($_GET['action'])?$_GET['action']:null;
$userid = isset($_GET['userid'])?$_GET['userid']:'';
$name = isset($_GET['name'])?$_GET['name']:null;

switch ($action) {
	case "newGroup":
		$result = OC_User_Group_Admin_Util::dbCreateGroup($name, $userid);
        	break;
        case "newMember":
		$owner = isset($_GET['owner'])?$_GET['owner']:OCP\USER::getUser ();
		$result = OC_User_Group_Admin_Util::dbAddToGroup($userid,$name,$owner);
		break;
	case "deleteGroup":
		$result = OC_User_Group_Admin_Util::dbDeleteGroup($name, $userid);
		break;
	case "leaveGroup":
		$result = OC_User_Group_Admin_Util::dbRemoveFromGroup($userid, $name);
		break;
	case "updateStatus":
		$status = isset($_GET['status'])?$_GET['status']:null;
		$result = OC_User_Group_Admin_Util::dbUpdateStatus($name, $userid, $status);
		break;
}
OCP\JSON::encodedPrint($result);

