<?php

/**
 * ownCloud - user_group_admin
 *
 * @author Christian Brinch
 * @copyright 2014 Christian Brinch, DeIC, <christian.brinch@deic.dk>
 *  
 * Based on Group Custom app
 * @author Jorge Rafael García Ramos
 * @copyright 2012 Jorge Rafael García Ramos <kadukeitor@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
if($_POST['action']!="disableuser"){
	OCP\JSON::callCheck();
}

if(isset($_POST['group'])){
	$group = $_POST['group'];
	$owner = OC_User_Group_Admin_Util::getGroupOwner($group);
	$user = OCP\User::getUser();
	doAction($group, $owner, $user);
}

function checkOwner($user, $owner) {
	return OC_User::isAdminUser($user) || !empty($owner) && $user===$owner;
}

function isReadable($group, $owner, $user) {
	$info = OC_User_Group_Admin_Util::getGroupInfo($group);
	return checkOwner($user, $owner) ||
		empty($info['private']) || $info['private']!=='yes';
}

function canLeave($group, $owner, $user) {
	$info = OC_User_Group_Admin_Util::getGroupInfo($group);
	return (checkOwner($user, $owner) ||
		empty($info['hidden']) || $info['hidden']==='no') &&
		// External users cannot leave the group they were invited to.
		// To delete account they have to send a request to the group owner.
		!OC_User_Group_Admin_Util::ownerIsCurator($owner, $user);
}

function doAction($group, $owner, $user){
	
	switch ($_POST['action']) {
		case "addgroup":
			// Check for existence
			$owner = OC_User_Group_Admin_Util::getGroupOwner($group);
			if(empty($owner) || checkOwner($user, $owner)){
				$result = OC_User_Group_Admin_Util::createGroup($group, $user);
			}
			break;
		case "addmember":
			if(!empty($_POST['member'])){
				$member = $_POST['member'];
				$str = $member;
			}
			elseif(!empty($_POST['email'])){
				$member = OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER;
				$str = $_POST['email'];
			}
			//\OCP\Util::writeLog('User_Group_Admin', 'Adding member '.$member, \OCP\Util::WARN);
			if(!empty($member)) {
				$accept = md5($str . time (). 1 );
				$decline = md5($str . time () . 0);
				if(checkOwner($user, $owner)) {
					$result = OC_User_Group_Admin_Util::addToGroup($member, $group, $accept, $decline, false,
						empty($_POST['email'])?'':$_POST['email']);
				}
				else{
					$result = OC_User_Group_Admin_Util::addToGroup($member, $group, $accept, $decline, true,
						empty($_POST['email'])?'':$_POST['email']);
				}
			}
			break;
		case "leavegroup":
			if(canLeave($group, $owner, $user)){
				$result = OC_User_Group_Admin_Util::removeFromGroup($user, $group);
				OC_User_Group_Hooks::groupLeave($group, $user, $owner);
			}
			break;
		case "delgroup":
			if(checkOwner($user, $owner)){
				$result = OC_User_Group_Admin_Util::deleteGroup($group);
				OC_User_Group_Hooks::groupDelete($group, $user);
			}
			break;
		case "delmember":
			\OCP\Util::writeLog('User_Group_Admin', 'deleting user '.$_POST['member'].' <-- '.$user.' <-- '.$owner.' <-- '.$group, \OCP\Util::WARN);
			if(!empty($_POST['member']) && $_POST['member'] != OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER &&
				(checkOwner($user, $owner) || $_POST['member']==$user)){
				$result = OC_User_Group_Admin_Util::removeFromGroup($_POST['member'], $group);
				// We only allow disabling external users here.
				// Group owners of system groups may disable users via rmmem.php
				if(!empty($_POST['disable']) && OC_User_Group_Admin_Util::ownerIsCurator($owner, $_POST['member'])){
					\OCP\Util::writeLog('User_Group_Admin', 'Disabling invited user '.$_POST['member'], \OCP\Util::WARN);
					OC_User_Group_Admin_Util::disableUser($owner, $_POST['member']);
				}
			}
			elseif(isset($_POST['invitation_email']) && checkOwner($user, $owner)){
				\OCP\Util::writeLog('User_Group_Admin', 'Removing invited user '.$_POST['invitation_email'], \OCP\Util::WARN);
				$result = OC_User_Group_Admin_Util::removeFromGroup($_POST['member'], $group, $_POST['invitation_email']);
			}
			break;
		case "disableuser":
			\OCP\Util::writeLog('User_Group_Admin', 'disabling user '.$_POST['user'].' <-- '.$user.' <-- '.$owner, \OCP\Util::WARN);
			if(isset($_POST['user']) && $_POST['user']!=$user && checkOwner($user, $owner) &&
					OC_User_Group_Admin_Util::ownerIsCurator($owner, $_POST['user'])){
				$pending = OC_Preferences::getValue($owner, 'user_group_admin', \OCP\Util::$PENDING_VERIFY_PREFIX.$_POST['user'], '');
				if(empty($pending)){
					break;
				}
				$result = OC_User_Group_Admin_Util::removeFromGroup($_POST['user'], $group);
				OC_User_Group_Admin_Util::disableUser($owner, $_POST['user']);
			}
			break;
		case "setdescription":
			\OCP\Util::writeLog('User_Group_Admin', 'Adding description '.$user.':'.$owner, \OCP\Util::WARN);
			if(checkOwner($user, $owner)){
				$result = OC_User_Group_Admin_Util::setDescription($_POST['description'], $group);
			}
			break;
		case "setprivate":
			\OCP\Util::writeLog('User_Group_Admin', 'Setting '.$group.' private: '.$_POST['privategroup'].
			' : '.$user.' : '.$owner, \OCP\Util::WARN);
			if(checkOwner($user, $owner)){
				$result = OC_User_Group_Admin_Util::setPrivate($_POST['privategroup']!='no', $group);
			}
			break;
		case "setopen":
			\OCP\Util::writeLog('User_Group_Admin', 'Setting '.$group.' open: '.$_POST['opengroup'], \OCP\Util::WARN);
			if(checkOwner($user, $owner)){
				$result = OC_User_Group_Admin_Util::setOpen($_POST['opengroup']=='yes', $group);
			}
			break;
		case "showmembers":
			$result = (checkOwner($user, $owner) || OC_User_Group_Admin_Util::inGroup($user, $group));
			break;
		case "getinfo":
			$result = isReadable($group, $owner, $user);
			break;
		case "toggleshowowned":
			$result = checkOwner($user, $owner);
			break;
	}

	if(!empty($result)){
		switch ($_POST['action']) {
			case "addgroup":
				OC_User_Group_Hooks::groupCreate($group, $user);
				OCP\JSON::success();
				break;
			case "addmember":
				$memberRequest = !checkOwner($user, $owner);
				if(!empty($_POST['email']) && empty($_POST['member'])){
					OC_User_Group_Hooks::groupShare($group,
						OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER, $owner);
					if(OC_User_Group_Admin_Util::sendVerificationToExternal($_POST['email'], $accept, $decline, $group)){
						OCP\JSON::success();
					}
					else{
						OCP\JSON::error(array('data' => array('title'=> 'Mail error', 'message' => 'Mail could not be sent...')));
					}
				}
				else{
					OC_User_Group_Hooks::groupShare($group, $_POST['member'], $owner, $memberRequest, $accept, $decline);
					$groupInfo = OC_User_Group_Admin_Util::getGroupInfo($group);
					$tmpl = new OCP\Template("user_group_admin", "members");
					$tmpl->assign( 'group' , $group );
					$tmpl->assign( 'owner' , $groupInfo['owner'] );
					$tmpl->assign( 'description' , $groupInfo['description'] );
					$tmpl->assign( 'privategroup' , !empty($groupInfo['private'])&&$groupInfo['private']=='yes');
					$tmpl->assign( 'opengroup' , !empty($groupInfo['open'])&&$groupInfo['open']=='yes' );
					$tmpl->assign( 'members' , OC_User_Group_Admin_Util::usersInGroup( $group ) );
					$page = $tmpl->fetchPage();
					OCP\JSON::success(array('data' => array('page'=>$page)));
				}
				break;
			case "showmembers":
				$groupInfo = OC_User_Group_Admin_Util::getGroupInfo($group);
				$tmpl = new OCP\Template("user_group_admin", "members");
				$tmpl->assign( 'group' , $group );
				$tmpl->assign( 'owner' , $groupInfo['owner'] );
				$tmpl->assign( 'description' , $groupInfo['description'] );
				$members = OC_User_Group_Admin_Util::usersInGroup( $group );
				$tmpl->assign( 'privategroup' , !empty($groupInfo['private'])&&$groupInfo['private']=='yes');
				$tmpl->assign( 'opengroup' , !empty($groupInfo['open'])&&$groupInfo['open']=='yes' );
				$tmpl->assign( 'members' , $members );
				$page = $tmpl->fetchPage();
				$tmpl = new OCP\Template("user_group_admin", "freequota");
				$tmpl->assign( 'group' , $group );
				$tmpl->assign( 'user_freequota' , $groupInfo['user_freequota'] );
				$quotaPreset = OC_Appconfig::getValue('files', 'quota_preset', '1 GB, 5 GB, 10 GB');
				$quotaPresetArr = explode(',', $quotaPreset);
				$tmpl->assign( 'quota_preset' , $quotaPresetArr );
				$freequota_dropdown = $tmpl->fetchPage();
				OCP\JSON::success(array('data' => array('page'=>$page, 'freequota'=>$freequota_dropdown,
					'members'=>$members)));
				break;
			case "getinfo":
				OCP\JSON::success(array('data' => OC_User_Group_Admin_Util::getGroupInfo($group)));
				break;
			case "toggleshowowned":
				OCP\JSON::success(array('data' =>
					array('showowned'=>OC_User_Group_Admin_Util::toggleShowOwned($group))
				));
				break;
			default:
				OCP\JSON::success();
		}
	}
	else{
		switch ($_POST['action']) {
			case "addgroup":
				OCP\JSON::error(array('data' => array('title'=> 'Add Group' ,
					'message' => 'A group with this name already exists and you don\'t own it'))) ;
				break;
			case "addmember":
				OCP\JSON::error(array('data' => array('title'=> 'Add Member', 'message' => 'No permission')));
				break;
			case "disableuser":
				OCP\JSON::error(array('data' => array('title'=> 'No permission', 'message' => 'This user has already been accepted by you.')));
				break;
			default:
				OCP\JSON::error(array('data' => array('title'=> 'No permission', 'message' => 'Not admin, owner or member')));
		}
	}
}


