<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');

$userid = isset($_GET['userid'])?$_GET['userid']:\OCP\User::getUser();
$onlyOwned = !empty($_GET['onlyOwned'])?$_GET['onlyOwned']=='yes':false;

$groups = $onlyOwned?OC_User_Group_Admin_Util::getOwnerGroups($userid, true):
	OC_User_Group_Admin_Util::getUserGroups($userid, true, false, true, false);

OCP\JSON::encodedPrint($groups);

