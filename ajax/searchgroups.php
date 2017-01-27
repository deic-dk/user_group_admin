<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');

$search = isset($_GET['search'])?(strlen($_GET['search'])>1?'%':'').$_GET['search'].'%':'%';
$userid = !empty($_GET['userid'])?$_GET['userid']:\OCP\User::getUser();

$groups = OC_User_Group_Admin_Util::searchGroups($search, $userid);

foreach($groups as &$group){
	$group['members'] = count(OC_User_Group_Admin_Util::usersInGroup($group['gid']));
	$group['ownerDisplayName'] = OCP\User::getDisplayName($group['owner']);
}

OCP\JSON::encodedPrint($groups);

