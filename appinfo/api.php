<?php

OCP\App::checkAppEnabled('user_group_admin');
OCP\App::checkAppEnabled('files_sharding');

// Not necessary.
/*if(!\OCA\FilesSharding\Lib::isMaster()){
	$master = \OCA\FilesSharding\Lib::getMasterURL();
	\OC_Response::redirect($master);
}*/

$group = empty($_GET['group'])?'':$_GET['group'];
$owner = empty($group)?'':OC_User_Group_Admin_Util::getGroupOwner($group);
$user = OCP\User::getUser();
$member = empty($_GET['member'])?'':$_GET['member'];
$action = isset($_GET['action'])?$_GET['action']:null;

$msg = empty($owner)&&!in_array($action, ["disableUser", "enableUser", "createGroup"])?"No such group":"";

$result = false;
if(checkPermissions($user, $owner)){
	switch ($action) {
		case "disableUser":
			$result = disableUser();
			break;
		case "enableUser":
			$result = enableUser();
			break;
		case "removeFromGroup":
			$result = removeFromGroup();
			break;
		case "addToGroup":
			$result = addToGroup();
			break;
		case "listMembers":
			$result = listMembers();
			break;
		case "createGroup":
			$result = createGroup();
			break;
		case "deleteGroup":
			$result = deleteGroup();
			break;
	}
}
else{
	header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden", true, 403);
	OCP\JSON::error(empty($msg)?[]:['message'=>$msg]);
	exit;
}

if($result){
	if($action=="listMembers"){
		OCP\JSON::encodedPrint($result);
	}
	else{
		OCP\JSON::success(empty($msg)?[]:['message'=>$msg]);
	}
}
else{
	OCP\JSON::error(empty($msg)?[]:['message'=>$msg]);
}

// Basic permission checks
function checkPermissions() {
	global $action, $group, $owner, $user, $member;
	return !empty($user) && (!empty($group)||in_array($action, ["disableUser", "enableUser", "createGroup"])) &&
	
	($action=="listMembers" && OC_User_Group_Admin_Util::inGroup($user, $group) &&
			!OC_User_Group_Admin_Util::groupIsHidden($group) ||
			
		(OC_User::isAdminUser($user) || $user===$owner) &&
			(in_array($action, ["listMembers", "deleteGroup"]) ||
					!empty($member) && $member!=OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER) ||
			
			// Anyone can create a group, disabling user permissions is checked by setUserStatus()
		in_array($action, ["disableUser", "enableUser", "createGroup"])
	);
}

function disableUser(){
	return setUserStatus(false);
}

function enableUser(){
	return setUserStatus(true);
}

function setUserStatus($enabled){
	global $user, $member, $msg;
	$res = false;
	$tmpParts = explode('@', $member);
	if(count($tmpParts)>1){
		$memberSystemGroup = end($tmpParts);
		$memberSystemGroupOwner = OC_User_Group_Admin_Util::getGroupOwner($memberSystemGroup);
	}
	else{
		$msg = "User ID must be a valid email address";
		return false;
	}
	// Allow group owner of a system group (data stewards) to disable users who are members of the system group
	if(OC_User_Group_Admin_Util::groupIsHidden($memberSystemGroup) && $user==$memberSystemGroupOwner){
		if($enabled){
			$res = OC_User_Group_Admin_Util::enableUser($user, $member);
		}
		else{
			$res = OC_User_Group_Admin_Util::disableUser($user, $member);
		}
	}
	// Allow group owners to disable external members
	elseif(OC_User_Group_Admin_Util::ownerIsCurator($user, $member)){
		if($enabled){
			$res = OC_User_Group_Admin_Util::enableUser($user, $member);
		}
		else{
			$res = OC_User_Group_Admin_Util::disableUser($user, $member);
		}
	}
	else{
		$msg = "You don't have permissions for this.";
	}
	
	return $res;
}

function removeFromGroup(){
	global $group, $owner, $user, $member, $msg;
	$res = false;
	if(!OC_User_Group_Admin_Util::inGroup($member, $group)){
		$res = false;
		$msg = "User ".$member." is not member of group ".$group;
	}
	// We don't allow removing regular users from system groups.
	// Curators can remove external users from any group.
	elseif(!OC_User_Group_Admin_Util::groupIsHidden($group) ||
			OC_User_Group_Admin_Util::ownerIsCurator($owner, $member)){
		$res = OC_User_Group_Admin_Util::removeFromGroup($member, $group);
		$msg = "User ".$member." removed from group ".$group;
	}
	else{
		$msg = "Removing users from system groups is not allowed";
	}
	return $res;
}

function addToGroup(){
	global $group, $owner, $user, $member, $msg;
	$res = false;
	if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
		$userExists = \OCP\User::userExists($member);
	}
	else{
		$userExists = \OCA\FilesSharding\Lib::ws('userExists', array('user_id'=>$member),
				false, true, null, 'files_sharding');
	}
	if($userExists){
		if(OC_User_Group_Admin_Util::inGroup($member, $group)){
			$res = false;
			$msg = "User ".$member." is already member of group ".$group;
		}
		else{
			$tmpParts = explode('@', $member);
			if(count($tmpParts)>1){
				$memberSystemGroup = end($tmpParts);
				$memberSystemGroupOwner = OC_User_Group_Admin_Util::getGroupOwner($memberSystemGroup);
			}
			else{
				$memberSystemGroup = "";
				$memberSystemGroupOwner = "";
			}
			if(// Data stewards are allowed to add users of their institution to groups w/o asking them.
					OC_User_Group_Admin_Util::groupIsHidden($memberSystemGroup) &&
					$user==$memberSystemGroupOwner ||
					// Likewise for curators of external users.
					OC_User_Group_Admin_Util::ownerIsCurator($owner, $member)
				){
				$res = OC_User_Group_Admin_Util::addToGroup($member, $group, '', '', false, "", true);
				//OC_User_Group_Hooks::groupShare($group, $member, $owner);
			}
			else{
				$accept = md5($member . time (). 1 );
				$decline = md5($member . time () . 0);
				$res = OC_User_Group_Admin_Util::addToGroup($member, $group, $accept, $decline, false, "", false);
				OC_User_Group_Hooks::groupShare($group, $member, $owner);
			}
			$msg = "User ".$member." added to group ".$group;
		}
	}
	else{
		$msg = "No such user ".$member;
		$res = false;
	}
	return $res;
}

function listMembers(){
	global $group, $owner, $user, $member, $msg;
	$res = OC_User_Group_Admin_Util::usersInGroup($group);
	$ret = [];
	$ret['group'] = $group;
	foreach($res as $row){
		if(empty($ret['owner'])){
			$ret['owner'] = $row['owner'];
		}
		if(empty($ret['type']) && !empty($row['type'])){
			$ret['type'] = $row['type'];
		}
		break;
	}
	$ret['members'] = array_map(function($row){unset($row['gid']);unset($row['type']);unset($row['owner']);return $row;}, $res);
	return $ret;
}

function createGroup(){
	global $group, $owner, $user, $member, $msg;
	if(!empty(OC_User_Group_Admin_Util::getGroupInfo($group))){
		$msg = "Group ".$group." already exists.";
		return false;
	}
	$ret = OC_User_Group_Admin_Util::createGroup($group, $user);
	if($ret){
		OC_User_Group_Hooks::groupCreate($group, $owner);
	}
	$msg = $ret?("Group ".$group." created"):("Problem creating group ".$group);
	return $ret;
}

function deleteGroup(){
	global $group, $owner, $user, $member, $msg;
	if(empty(OC_User_Group_Admin_Util::getGroupInfo($group))){
		$msg = "Group ".$group." does not exist.";
		return false;
	}
	if(OC_User_Group_Admin_Util::groupIsHidden($group)){
		$msg = "You are not allowed to delete system groups.";
		return false;
	}
	$ret = OC_User_Group_Admin_Util::deleteGroup($group);
	if($ret){
		OC_User_Group_Hooks::groupDelete($group, $owner);
	}
	$msg = $ret?("Group ".$group." deleted"):("Problem deleting group ".$group);
	return $ret;
}
