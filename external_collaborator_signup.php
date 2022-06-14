<?php

//OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('user_group_admin');

//OCP\App::setActiveNavigationEntry( 'user_group_admin' );

OCP\Util::addStyle('core', 'styles');
OCP\Util::addStyle('core', 'jquery-ui-1.10.0.custom');
OCP\Util::addStyle('core', 'jquery.ocdialog');
OCP\Util::addStyle('user_group_admin', 'user_group_admin');
OCP\Util::addStyle('files', 'files');

OCP\Util::addScript('user_group_admin','script');
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');

function checkGroup(&$group, $user) {
	if($group["verified"]==OC_User_Group_Admin_Util::$GROUP_INVITATION_OPEN){
		\OCP\Util::writeLog('User_Group_Admin', 'Code: '.$_REQUEST['code'].'==='.$group["accept"], \OCP\Util::WARN);
		if($_REQUEST['code']===$group["accept"]){
			if(OC_User::userExists($group["invitation_email"])){
				$group['error'] = "User ".$group["invitation_email"]." already exists.";
				return false;
			}
			$tmpl = new OCP\Template('user_group_admin', 'signup', 'base');
			$missingFields = array();
			if(empty($_REQUEST['password'])){
				$missingFields[] = 'password';
			}
			elseif(file_exists('/usr/local/sbin/cracklib-check')){ // TODO: generalize this
				$password = trim($_REQUEST['password']);
				$cracklibCheck = shell_exec("echo $password | /usr/local/sbin/cracklib-check 2>&1 | xargs echo -n");
				OC_Log::write('ChangePassword','Checked password :'.$cracklibCheck, \OC_Log::WARN);
				if(substr($cracklibCheck, -4)!=": OK"){
					$missingFields[] = 'password';
					$tmpl->assign('password_error', $cracklibCheck);
				}
				$tmpl->assign('password', $_REQUEST['password']);
			}
			else{
				$tmpl->assign('password', $_REQUEST['password']);
			}
			if(empty($_REQUEST['fullname'])){
				$missingFields[] = 'fullname';
			}
			else{
				$tmpl->assign('fullname', $_REQUEST['fullname']);
			}
			if(empty($_REQUEST['address'])){
				$missingFields[] = 'address';
			}
			else{
				$tmpl->assign('address', $_REQUEST['address']);
			}
			if(!empty($_REQUEST['affiliation'])){
				$tmpl->assign('affiliation', $_REQUEST['affiliation']);
			}
			// Form not completed, direct to form, highlighting missing fields
			$tmpl->assign('email', $group["invitation_email"]);
			$tmpl->assign('code', $group["accept"]);
			$tmpl->assign('groupname', $group["gid"]);
			$tmpl->assign('groupowner', $group["owner"]);
			$tmpl->assign('missingfields', $missingFields);
			$tmpl->printPage();
			if(empty($missingFields) && !empty($group["invitation_email"])){
				// Create user
				$newuser = $group["invitation_email"];
				$owner = $group["owner"];
				OC_User::createUser($newuser, $_REQUEST['password']);
				if(\OCP\App::isEnabled('files_sharding') && \OCA\FilesSharding\Lib::isMaster()){
					// Set home server to that of the group owner
					$homeServerID = \OCA\FilesSharding\Lib::lookupServerIdForUser($owner);
					\OCA\FilesSharding\Lib::setServerForUser($newuser, $homeServerID,
						\OCA\FilesSharding\Lib::$USER_SERVER_PRIORITY_PRIMARY,
						\OCA\FilesSharding\Lib::$USER_ACCESS_ALL);
				}
				OC_Preferences::setValue($newuser, 'settings', 'email', $newuser);
				OC_User::setDisplayName($newuser, $_REQUEST['fullname']);
				OCP\Config::setUserValue($newuser, 'user_group_admin', 'address', $_REQUEST['address']);
				if(!empty($_REQUEST['affiliation'])){
					OCP\Config::setUserValue($newuser, 'user_group_admin', 'affiliation', $_REQUEST['affiliation']);
				}
				if(OCP\App::isEnabled('files_accounting')){
					// External users can default to lower freequota than regular users.
					$default_group_freequota = OCP\Config::getAppValue('user_group_admin', 'default_group_freequota', '');
					if(!empty($default_group_freequota)){
						OCP\Config::setUserValue($newuser, 'files_accounting', 'freequota', $default_group_freequota);
					}
				}
				$result = OC_User_Group_Admin_Util::updateStatus($group["gid"],
						OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER,
						OC_User_Group_Admin_Util::$GROUP_INVITATION_ACCEPTED, true, $newuser);
				OC_User_Group_Hooks::groupJoin($group["gid"], $newuser, $owner, true);
				$masterUrl = OC::$WEBROOT."/";
				if(\OCP\App::isEnabled('files_sharding') ){
					$masterUrl = \OCA\FilesSharding\Lib::getMasterURL();
				}
				\OC_Preferences::setValue($owner, 'user_group_admin', \OCP\Util::$PENDING_VERIFY_PREFIX.$newuser, OC_User_Group_Admin_Util::$GROUP_MEMBERSHIP_PENDING);
				//\OC_Response::redirect($masterUrl."?username=".$newuser);
				$theme = new \OC_Defaults();
				echo "<script type='text/javascript'>var url=window.location.href.replace('code=','nocode='); ".
						"redirect = function(ev){window.location.href='".$masterUrl."?username=".$newuser."';};".
						"popup = function(){OC.dialogs.alert('Welcome to  ". $theme->getTitle().
							". You will now be redirected to our service, where you can now log in with your username ".
							$newuser.", and your new password.', 'Welcome', redirect, true);};".
						"$('form#external_signup input[type=submit]').click(function(ev){ev.preventDefault();ev.stopPropagation();popup();});".
						"$('div.oc-dialog').on('dialogclose', function(event) {alert('closed');$('div.oc-dialog').remove();});".
						"popup();</script>";
			}
			return true;
		}
		if($_REQUEST['code']===$group["decline"]){
			if($_REQUEST['declined']=='yes'){
				// Membership declined
				$result = OC_User_Group_Admin_Util::updateStatus($group["gid"], $user,
						OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED, true);
				$tmpl = new OCP\Template('user_group_admin', 'declined', 'base');
				$tmpl->printPage();
				return $result;
			}
			else{
				// Confirm decline
				$tmpl = new OCP\Template('user_group_admin', 'decline', 'base');
				$tmpl->assign('email', $group["invitation_email"]);
				$tmpl->assign('groupname', $group["gid"]);
				$tmpl->printPage();
				return true;
			}
		}
	}
	return false;
}

$ret = false;
$error = "Invalid code.";

if(!empty($_REQUEST['code'])){
	// Check if it is an external invitation
	$groups = OC_User_Group_Admin_Util::getUserGroups(OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER);
	foreach($groups as $group){
		$ret = $ret || checkGroup($group, OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER);
		if(!empty($group['error'])){
			$error = $group['error'];
			\OCP\Util::writeLog('User_Group_Admin', $error.' '.$group["invitation_email"], \OCP\Util::WARN);
			break;
		}
	}
}

if(!$ret){
	$tmpl = new OCP\Template('user_group_admin', 'invalid', 'base');
	$tmpl->assign('error', $error);
	$tmpl->printPage();
}

