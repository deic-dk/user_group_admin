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
OCP\JSON::callCheck();

if(isset($_POST['group'])){
	$group = $_POST['group'];
	$owner = OC_User_Group_Admin_Util::getGroupOwner($group);
	$user = OCP\User::getUser();
	doAction($group, $owner, $user);
}

function checkOwner($user, $owner) {
	return OC_User::isAdminUser($user) || !empty($owner) && $user===$owner;
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
			if(isset($_POST['member']) && checkOwner($user, $owner)) {
				$result = OC_User_Group_Admin_Util::addToGroup($_POST['member'], $group);
			}
			break;
		case "leavegroup":
			$result = OC_User_Group_Admin_Util::removeFromGroup($user, $group);
			OC_User_Group_Hooks::groupLeave($group, $user, $owner);
			break;
		case "delgroup":
			if(checkOwner($user, $owner)){
				$result = OC_User_Group_Admin_Util::deleteGroup($group);
				OC_User_Group_Hooks::groupDelete($group, $user);
			}
			break;
		case "delmember":
			if(isset($_POST['member']) && (checkOwner($user, $owner) || $_POST['member']==$user)){
				$result = OC_User_Group_Admin_Util::removeFromGroup($_POST['member'], $group) ;
			}
			break;
		case "showmembers":
			$result = (checkOwner($user, $owner) || OC_User_Group_Admin_Util::inGroup($user, $group));
			break;
		case "getinfo":
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
				OC_User_Group_Hooks::groupShare($group, $_POST['member'], $user);
				$tmpl = new OCP\Template("user_group_admin", "members");
				$tmpl->assign( 'group' , $group, false );
				$tmpl->assign( 'members' , OC_User_Group_Admin_Util::usersInGroup( $group ), false );
				$page = $tmpl->fetchPage();
				OCP\JSON::success(array('data' => array('page'=>$page)));
				break;
			case "showmembers":
				$tmpl = new OCP\Template("user_group_admin", "members");
				$tmpl->assign( 'group' , $group , false );
				$tmpl->assign( 'members' , OC_User_Group_Admin_Util::usersInGroup( $group ), false );
				$page = $tmpl->fetchPage();
				$tmpl = new OCP\Template("user_group_admin", "freequota");
				$tmpl->assign( 'group' , $group , false );
				$groupInfo = OC_User_Group_Admin_Util::getGroupInfo($group);
				$tmpl->assign( 'user_freequota' , $groupInfo['user_freequota'] );
				$quotaPreset = OC_Appconfig::getValue('files', 'quota_preset', '1 GB, 5 GB, 10 GB');
				$quotaPresetArr = explode(',', $quotaPreset);
				$tmpl->assign( 'quota_preset' , $quotaPresetArr );
				$freequota_dropdown = $tmpl->fetchPage();
				OCP\JSON::success(array('data' => array('page'=>$page, 'freequota'=>$freequota_dropdown)));
				break;
			case "getinfo":
				OCP\JSON::success(array('data' => OC_User_Group_Admin_Util::getGroupInfo($group)));
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
			default:
				OCP\JSON::error(array('data' => array('title'=> 'No permission', 'message' => 'Not admin, owner or member')));
		}
	}
}


