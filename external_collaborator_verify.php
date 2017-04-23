<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('user_group_admin');

if(empty($_GET['group']) || empty($_GET['user'])){
	return false;
}

$owner = OC_User_Group_Admin_Util::getGroupOwner($_GET['group']);
$user = \OC_User::getUser();
if($owner!=$user){
	return false;
}

//OCP\App::setActiveNavigationEntry( 'user_group_admin' );

OCP\Util::addStyle('core', 'styles');
OCP\Util::addStyle('user_group_admin', 'user_group_admin');
OCP\Util::addStyle('files', 'files');

OCP\Util::addScript('user_group_admin','script');
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');

$tmpl = new OCP\Template('user_group_admin', 'verify', 'base');
$tmpl->assign('group', $_GET['group']);
$user = $_GET['user'];
$tmpl->assign('user', $user);
$affiliation = OCP\Config::getUserValue($user, 'user_group_admin', 'affiliation');
$tmpl->assign('affiliation', $affiliation);
$address = OCP\Config::getUserValue($user, 'user_group_admin', 'address');
$tmpl->assign('address', $address);
$displayname = OC_User::getDisplayName($user);
$tmpl->assign('displayname', $displayname);
$email = OC_Preferences::getValue($user, 'settings', 'email');
$tmpl->assign('email', $email);

$owner = \OC_User::getUser();
$now = time();
$pending = OC_Preferences::getValue($owner, 'user_group_admin', 'pending_verify_'.$user, '');
if($pending==-1){
	\OC_Preferences::setValue($owner, 'user_group_admin', 'pending_verify_'.$user, $now);
	$pending = 'yes';
}
elseif(empty($pending) || $now>$pending+24*60*60){
	$pending = 'no';
	\OC_Preferences::deleteKey($owner, 'user_group_admin', 'pending_verify_'.$user);
	\OC_Preferences::setValue($user, 'user_group_admin', 'verified_by', $owner);
}
$tmpl->assign('verify_pending', $pending);

$tmpl->printPage();

