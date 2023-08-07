<?php

OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::checkAppEnabled('files_sharding');

$user = OCP\USER::getUser();
$allowedQueryUser = trim(\OCP\Config::getSystemValue('vlantrusteduser', ''));

// This is to allow ScienceRepository/Zenodo to query for user matching orcid
if(empty($user)){
	$user = \OC_Chooser::checkIP();
}
if(empty($user) || $user!=$allowedQueryUser){
	if(!OCA\FilesSharding\Lib::checkIP()){
		http_response_code(401);
		exit;
	}
}

$onlyVerified = isset($_GET['only_verified'])&&$_GET['only_verified']==='yes';
$hideHidden = isset($_GET['hide_hidden'])&&$_GET['hide_hidden']==='yes';
$onlyWithFreeQuota = isset($_GET['only_with_freequota'])&&$_GET['only_with_freequota']==='yes';
$onlyOwned = isset($_GET['only_owned'])&&$_GET['only_owned']==='yes';
$user = isset($_GET['userid'])?$_GET['userid']:\OCP\User::getUser();

$groups = OC_User_Group_Admin_Util::dbGetUserGroups($user, $onlyVerified, $hideHidden, $onlyWithFreeQuota,
		$onlyOwned);
\OCP\Util::writeLog('user_group_admin', 'Returning groups '.serialize($groups), \OC_Log::DEBUG);
OCP\JSON::encodedPrint($groups);
