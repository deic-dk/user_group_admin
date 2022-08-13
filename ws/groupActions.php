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
$quota = isset($_GET['quota'])?$_GET['quota']:'';
$usage = isset($_GET['usage'])?$_GET['usage']:0;
$memberRequest = !empty($_GET['memberRequest'])?$_GET['memberRequest']=='yes':false;
$invitationEmail = !empty($_GET['email'])?$_GET['email']:'';
$accept = isset($_GET['accept'])?$_GET['accept']:'';
$decline = isset($_GET['decline'])?$_GET['decline']:'';
$code = isset($_GET['code'])?$_GET['code']:'';
$description = isset($_GET['description'])?$_GET['description']:'';
$verified = empty($_GET['verified'])?false:$_GET['verified']=='yes';
$private = empty($_GET['privateGroup'])?true:$_GET['privateGroup']!='no';
$open = isset($_GET['openGroup'])?$_GET['openGroup']=='yes':false;
$invitation_email = isset($_GET['invitation_email'])?$_GET['invitation_email']:'';

switch ($action) {
	case "newGroup":
		$result = OC_User_Group_Admin_Util::dbCreateGroup($name, $userid);
		break;
	case "newMember":
		$result = OC_User_Group_Admin_Util::dbAddToGroup($userid, $name, $accept, $decline, $memberRequest,
		$invitationEmail, $verified);
		break;
	case "deleteGroup":
		$result = OC_User_Group_Admin_Util::dbDeleteGroup($name);
		break;
	case "leaveGroup":
		$result = OC_User_Group_Admin_Util::dbRemoveFromGroup($userid, $name, $invitation_email);
		break;
	case "updateStatus":
		$status = isset($_GET['status'])?$_GET['status']:null;
		$checkOpen = isset($_GET['checkOpen'])?$_GET['checkOpen']==='yes':false;
		$result = OC_User_Group_Admin_Util::dbUpdateStatus($name, $userid, $status, $checkOpen, $invitationEmail, $code);
		break;
	case "setUserFreeQuota":
		$result = OC_User_Group_Admin_Util::dbSetUserFreeQuota($name, $quota);
		break;
	case "getGroupUsage":
		$result = OC_User_Group_Admin_Util::dbGetGroupUsage($name, $userid);
		break;
	case "updateGroupUsage":
		$result = OC_User_Group_Admin_Util::dbUpdateGroupUsage($userid, $name, $usage);
		break;
	case "getGroupUsageCharge":
		$result = OC_User_Group_Admin_Util::dbGetGroupUsageCharge($name);
		break;
	case "setDescription":
		$result = OC_User_Group_Admin_Util::setDescription($description, $name);
		break;
	case "setPrivate":
		$result = OC_User_Group_Admin_Util::setPrivate($private, $name);
		break;
	case "setOpen":
		$result = OC_User_Group_Admin_Util::setOpen($open, $name);
		break;
	case "toggleShowOwned":
		$result = OC_User_Group_Admin_Util::dbToggleShowOwned($name);
		break;
	case "getShowOwned":
		$result = OC_User_Group_Admin_Util::dbGetShowOwned($name);
		break;
}
OCP\JSON::encodedPrint($result);

