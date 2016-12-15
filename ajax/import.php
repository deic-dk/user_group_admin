<?php
/**
 * ownCloud - user_group_admin
 *
 * @author Christian Brinch
 * @copyright 2014 Christian Brinch, DeIC, <christian.brinch@deic.dk>
 *
 * @author Jorge Rafael Garc\xc3\xada Ramos
 * @copyright 2012 Jorge Rafael Garc\xc3\xada Ramos <kadukeitor@gmail.com>
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
OCP\User::checkLoggedIn ();
OCP\App::checkAppEnabled ( 'user_group_admin' );

$import = true;
$failed = array();
if (isset($_FILES ['import_group_file'] ['tmp_name'])) {
	$from = $_FILES ['import_group_file'] ['tmp_name'];
	$content = file_get_contents ( $from );
	$data = json_decode($content, true);
	$group = $data['group'];
	$user = OCP\USER::getUser();
	$groupInfo = OC_User_Group_Admin_Util::getGroupInfo($group);
	if(!empty($groupInfo) && !empty($groupInfo['owner'])){
		$existingOwner = $groupInfo['owner'];
	}
	$owner = isset($data['owner'])?$data['owner']:$user;
	if(!empty($group) && is_array($data['members']) &&
			((empty($existingOwner) || $existingOwner===$user) && $owner===$user || OC_User::isAdminUser($user))){
		// Create group if it doesn't exist
		$result = OC_User_Group_Admin_Util::createGroup($group, $owner);
		if($result){
			$activity = OC_User_Group_Hooks::groupCreate($group, $user);
		}
		foreach($data['members'] as $member){
			if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
				$userExists = \OCP\User::userExists($member);
			}
			else{
				$userExists = \OCA\FilesSharding\Lib::ws('userExists', array('user_id'=>$member),
					false, true, null, 'files_sharding');
			}
			if($userExists){
				OC_User_Group_Admin_Util::addToGroup($member, $group);
				OC_User_Group_Hooks::groupShare($group, $member, $user);
			}
			else{
				$failed[] = $member;
			}
		}
	}
}
if(empty($failed)){
	if($owner!==$user &&  !OC_User::isAdminUser($user)){
		echo "<script type='text/javascript'>
			var r = confirm('You cannot create create groups owned by other than yourself.');
			if(r==true){
				window.location.href=OC.webroot+'/index.php/apps/user_group_admin';
			}
			else{
				window.location.href=OC.webroot+'/index.php/apps/user_group_admin';
			}
			</script>";
	}
	else{
		header('Location: ' . OCP\Util::linkToAbsolute('user_group_admin', 'index.php'));
	}
}
else{
	$n = count($failed);
	echo "<script type='text/javascript'>
			var r = confirm('".$n." user".($n===1?"":"s")." not added to the group because adding requires existence.');
			if(r==true){
				window.location.href=OC.webroot+'/index.php/apps/user_group_admin';
			}
			else{
				window.location.href=OC.webroot+'/index.php/apps/user_group_admin';
			}
			</script>";
}

