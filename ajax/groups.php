<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');

$userid = isset($_GET['userid'])?$_GET['userid']:\OCP\User::getUser();

$groups = OC_User_Group_Admin_Util::getUserGroups($userid, true, false, true);

OCP\JSON::encodedPrint($groups);

