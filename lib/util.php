<?php
/*
 * ownCloud - user_group_admin
 *
 * @author Christian Brinch
 * @copyright 2014 Christian Brinch, DeIC, <christian.brinch@deic.dk>
 *
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 *
 */
class OC_User_Group_Admin_Util {
	
	public static $HIDDEN_GROUP_OWNER = 'hidden_group_owner';
	public static $GROUP_INVITATION_OPEN = 0;
	public static $GROUP_INVITATION_ACCEPTED = 1;
	public static $GROUP_INVITATION_DECLINED = 2;
	
	/**
	 * @brief Try to create a new group
	 *
	 * @param string $gid
	 *        	The name of the group to create
	 * @return bool Tries to create a new group. If the group name already exists, false will
	 *         be returned.
	 */
	public static function dbCreateGroup($gid, $uid) {
		// Check for existence
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?" );
		$result = $stmt->execute(array($gid));
		if($result->fetchRow()){
			return false;
		}
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` = ?" );
		$result = $stmt->execute(array($gid));
		if($result->fetchRow()){
			return false;
		}
		$stmt = OC_DB::prepare ( "INSERT INTO `*PREFIX*user_group_admin_groups` ( `gid` , `owner` ) VALUES( ? , ? )" );
		$result = $stmt->execute(array(
				$gid,
				$uid
		));
		return $result;
	}

	public static function createGroup($gid, $uid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbCreateGroup($gid, $uid );
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions', array('userid'=>$uid,
					'name'=>urlencode($gid), 'action'=>'newGroup'), false, true,
					null, 'user_group_admin');
		}
		return $result ? true : false;
	}

	public static function createHiddenGroup($gid) {

		// Check for existence
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?" );
		$result = $stmt->execute ( array (
				$gid
		));
		if($result->fetchRow ()){
			return false;
		}
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` = ?" );
		$result = $stmt->execute ( array (
			$gid
		) );
		if($result->fetchRow()){
			return false;
		}

		// Add group and exit
		$stmt = OC_DB::prepare ( "INSERT INTO `*PREFIX*user_group_admin_groups` ( `gid`, `owner`, `hidden` ) VALUES( ?, ?, ? )" );
		$result = $stmt->execute ( array (
				$gid,
				self::$HIDDEN_GROUP_OWNER,
				'yes'
		) );

		return $result ? true : false;
	}

	/**
	 * @brief delete a group
	 *
	 * @param string $gid ID of the group to delete.
	 * @param strin $uid ID of the user. If not admin and not group owner, deletion is declined.
	 * @return bool Deletes a group and removes it from the group_user-table
	 */
	public static function dbDeleteGroup($gid) {
		$stmt = OC_DB::prepare("DELETE FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?");
		$stmt->execute(array($gid));
		$stmt = OC_DB::prepare("DELETE FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ?");
		$stmt->execute(array($gid));
		return true;
	}

	public static function deleteGroup($gid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbDeleteGroup($gid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions', array(
					'name'=>urlencode($gid), 'action'=>'deleteGroup'),false, true, null, 'user_group_admin');
		}
		return $result;
	}

/**
 * NOTICE: includes pending, but not declined users
 * @param unknown $uid
 * @param unknown $gid
 * @return boolean
 */
	public static function inGroup($uid, $gid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return self::dbInGroup($uid, $gid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('usersInGroup', array('gid'=>urlencode($gid)),
				false, true, null, 'user_group_admin');
			if(!empty($result) && sizeof($result)>0){
				foreach($result as $row){
					if($row['uid']==$uid){
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * @brief Add a user to a group
	 *
	 * @param string $uid
	 *        	Name of the user to add to group
	 * @param string $gid
	 *        	Name of the group in which add the user
	 * @return bool Adds a user to a group.
	 */
	public static function dbAddToGroup($uid, $gid) {
		$owner = self::getGroupOwner($gid);
		if(self::dbInGroup($uid, $gid, true)){
			return self::updateStatus($gid, $uid, $uid===$owner || self::dbHiddenGroupExists($gid)?self::$GROUP_INVITATION_ACCEPTED:self::$GROUP_INVITATION_OPEN);
		}
		$stmt = OC_DB::prepare("INSERT INTO `*PREFIX*user_group_admin_group_user` ( `gid`, `uid`, `verified`, `accept`, `decline`) VALUES( ?, ?, ?, ?, ?)" );
		if($uid===$owner || self::dbHiddenGroupExists($gid)){
			$stmt->execute ( array (
					$gid,
					$uid,
					self::$GROUP_INVITATION_ACCEPTED,
					'',
					''
			));
		}
		else{
			$accept = md5 ( $uid . time (). 1 );
			$decline = md5 ($uid . time () . 0);
			$stmt->execute ( array (
					$gid,
					$uid,
					self::$GROUP_INVITATION_OPEN,
					$accept,
					$decline
			) );
			self::sendVerification($uid, $accept, $decline, $gid);
		}
		return true;
	}

	public static function addToGroup($uid, $gid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbAddToGroup($uid, $gid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions', array(
					'name'=>urlencode($gid), 'userid'=>$uid, 'action'=>'newMember'),
					false, true, null, 'user_group_admin');
		}
		return $result;
	}

	/**
	 * Send invitation mail to a user.
	 */
	private static function sendVerification($uid, $accept, $decline, $gid) {
		$owner = self::getGroupOwner($gid);
		$ownerName = \OCP\User::getDisplayName($owner);
		$senderAddress = OCP\Config::getAppValue('user_group_admin', 'sender', '');
		$defaults = new \OCP\Defaults();
		$senderName = $defaults->getName();
		$name = OCP\User::getDisplayName($uid);
		$url = OCP\Config::getAppValue('user_group_admin', 'appurl', '');
		$subject = OCP\Config::getAppValue('user_group_admin', 'subject', '');
		$message = 'Dear '.$name.','."\n \n".'You have been invited to join the group "' .
			$gid . '" by ' . $ownerName . '. Click here to accept the invitation:'."\n".
			$url . $accept ."\n \n".'or click here to decline:'."\n\n".
			$url . $decline;
		try{
			\OCP\Util::sendMail($uid, $name, $subject, $message, $senderAddress, $senderName);
		}
		catch(\Exception $e){
			\OCP\Util::writeLog('User_Group_Admin',
				'A problem occurred while sending the e-mail. Please revisit your settings.',
				\OCP\Util::ERROR);
		}
	}

	/**
	 * NOTICE: includes pending users
	 * @param unknown $uid
	 * @param unknown $gid
	 * @return boolean
	 */
	public static function dbInGroup($uid, $gid, $includeDeclined=false){
		if($includeDeclined){
			$stmt = OC_DB::prepare ( "SELECT `uid`  FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ?" );
			$res = $stmt->execute(array($gid, $uid))->fetchOne();
		}
		else{
			$stmt = OC_DB::prepare ( "SELECT `uid`  FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? AND `verified` != ?" );
			$res = $stmt->execute(array($gid, $uid, self::$GROUP_INVITATION_DECLINED))->fetchOne();
		}
		\OCP\Util::writeLog('User_Group_Admin',
				'In group: '.$gid.'/'.$uid.'-->'.serialize($res),
				\OCP\Util::WARN);
		return !empty($res);
	}

	public static function dbUpdateStatus($gid, $uid, $status, $checkOpen=false) {
		$sql = "UPDATE `*PREFIX*user_group_admin_group_user` SET `verified` = '".
			$status."' WHERE `uid` = ? AND `gid` = ?";
		if($checkOpen){
			$sql .= " AND `verified` = ".self::$GROUP_INVITATION_OPEN;
		}
		$query = OC_DB::prepare($sql);
		$result = $query->execute( array (
				$uid,
				$gid
		));
		return $result;
	}
	
	public static function dbSetUserFreeQuota($gid, $quota) {
		$query = OC_DB::prepare ("UPDATE `*PREFIX*user_group_admin_groups` SET `user_freequota` = ? WHERE `gid` = ? " );
		$result = $query->execute( array (
				$quota,
				$gid
		));
		return $result;
	}
	
	public static function setUserFreeQuota($gid, $quota) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSetUserFreeQuota($gid, $quota);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions', array(
					'name'=>urlencode($gid), 'quota'=>$quota, 'action'=>'setUserFreeQuota'),
					false, true, null, 'user_group_admin');
		}
		return $result;
	}

	/**
	* Update user status
	*/
	public static function updateStatus($gid, $uid, $status, $checkOpen=false) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbUpdateStatus($gid, $uid, $status, $checkOpen);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions', array(
					'name'=>urlencode($gid), 'userid'=>$uid, 'status' => $status, 'action'=>'updateStatus',
					'checkOpen'=>($checkOpen?'yes':'no')),
					false, true, null, 'user_group_admin');
		}
		return $result;
	}

	/**
	 * @brief Removes a user from a group
	 *
	 * @param string $uid
	 *        	Name of the user to remove from group
	 * @param string $gid
	 *        	Name of the group from which remove the user
	 * @return bool removes the user from a group.
	 */
	public static function dbRemoveFromGroup($uid, $gid) {
		$stmt = OC_DB::prepare ( "DELETE FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ? AND `gid` = ?" );
		$stmt->execute ( array (
				$uid,
				$gid
		) );

		return true;
	}

	public static function removeFromGroup($uid, $gid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbRemoveFromGroup($uid,$gid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions', array(
					'name'=>urlencode($gid), 'userid'=>$uid, 'action'=>'leaveGroup'),false, true, null, 'user_group_admin');
		}
		return $result;
	}

	public static function dbGetOwnerGroups($owner, $with_freequota=false) {
		$stmt = OC_DB::prepare("SELECT * FROM `*PREFIX*user_group_admin_groups` WHERE `owner` = ?");
		$result = $stmt->execute(array($owner));
		$groups = array ();
		while($row = $result->fetchRow ()){
			if($with_freequota && empty($row['user_freequota'])){
				continue;
			}
			$groups[] = $row;
		}
		return $groups;
	}

	public static function getOwnerGroups($owner, $with_freequota=false) {
		if (!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGetOwnerGroups($owner, $with_freequota);
		}
		else{
		 	$result = \OCA\FilesSharding\Lib::ws('getOwnerGroups', Array('owner'=>$owner),
				false, true, null, 'user_group_admin');
		}
		return $result;
	}

	/**
	 * @brief Get all groups a user belongs to
	 *
	 * @param string $uid
	 * @return array with group names
	 *
	 *         This function fetches all groups a user belongs to. It does not check
	 *         if the user exists at all.
	 */
	public static function dbGetUserGroups($uid, $onlyVerified=false, $hideHidden=false, $onlyWithFreeQuota=false) {
		$sql = "SELECT * FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ?";
		if($onlyVerified){
			$sql .= " AND `verified` = ?";
			$stmt = OC_DB::prepare($sql);
			$result = $stmt->execute(array($uid, self::$GROUP_INVITATION_ACCEPTED));
		}
		else{
			$stmt = OC_DB::prepare($sql);
			$result = $stmt->execute(array($uid));
		}
		$groups = array();
		while($row = $result->fetchRow()){
			$groupInfo = self::dbGetGroupInfo($row["gid"]);
			if($hideHidden && $groupInfo['hidden']==='yes' &&
					$groupInfo['owner']!==$uid && !\OC_User::isAdminUser(\OC_User::getUser())){
				/* If the owner of a hidden group has been set to $uid, show the group.
				   Always show to admin. */
				continue;
			}
			if($onlyWithFreeQuota && empty($groupInfo['user_freequota'])){
				continue;
			}
			$row['owner'] = $groupInfo['owner'];
			$row['user_freequota'] = $groupInfo['user_freequota'];
			$groups[] = $row;
		}
		return $groups;
	}

	public static function getUserGroups($userid, $onlyVerified=false, $hideHidden=false, $onlyWithFreeQuota=false) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGetUserGroups($userid, $onlyVerified, $hideHidden, $onlyWithFreeQuota);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('getUserGroups',
				array(	'userid'=>$userid,
								'only_verified'=>!empty($onlyVerified)?'yes':'no',
								'hide_hidden'=>!empty($hideHidden)?'yes':'no',
								'only_with_freequota'=>!empty($onlyWithFreeQuota)?'yes':'no'
				),
				false, true, null, 'user_group_admin');
		}
		return $result;
	}
	
	private static function dbHiddenGroupExists($gid) {
		$query = OC_DB::prepare ( 'SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ? AND hidden = ?' );
		$result = $query->execute(array( $gid, 'yes' ))->fetchOne();
		if($result){
			return true;
		}
		return false;
	}
	
	public static function groupIsHidden($gid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return self::dbHiddenGroupExists($gid);
		}
		else{
			$groupInfo = self::getGroupInfo($gid);
			return $groupInfo['hidden']==='yes';
		}
	}

	/**
	 * @brief get a list of all users in a group
	 *
	 * @param string $gid
	 * @param string $search
	 * @param int $limit
	 * @param int $offset
	 * @return array of users
	 */
	public static function dbUsersInGroup($gid, $search = '', $limit = null, $offset = null) {
		$stmt = OC_DB::prepare ( 'SELECT * FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` LIKE ? AND `verified` != ?', $limit, $offset );
		$result = $stmt->execute(array(
				$gid,
				$search . '%',
				self::$GROUP_INVITATION_DECLINED
		));
		$users = array();
		$owner = self::dbGetGroupOwner($gid);
		while($row = $result->fetchRow()){
			$row['owner'] = $owner;
			$users[] = $row;
		}
		return $users;
	}

	public static function usersInGroup($gid, $search = '', $limit = null, $offset = null) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbUsersInGroup($gid, $search, $limit, $offset);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('usersInGroup', array('gid'=>urlencode($gid)),
				false, true, null, 'user_group_admin');
		}
		return $result;
	}

	public static function prepareUser($user) {
		$param = \OCP\Util::sanitizeHTML($user);
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$displayName = \OCP\User::getDisplayName($user);
			$name = \OCP\Util::sanitizeHTML($displayName);
		}
		else{
			$displayName = \OCA\FilesSharding\Lib::ws('getDisplayNames', array('search'=>$user),
				false, true, null, 'files_sharding');
			foreach ($displayName as $name) {
				$name = \OCP\Util::sanitizeHTML($name);
			}
		}
		return '<div class="avatar" data-user="' . $param . '"></div>'. '<strong style="font-size:92%">'.
			$name . '</strong>';
	}

	public static function getGroups($search = '', $limit = null, $offset = null ) {
		$query = OC_DB::prepare('SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` LIKE ?', $limit, $offset );
		$result = $query->execute (array ($search));
		$groups = array ();
		while ( $row = $result->fetchRow () ) {
			$groups [] = $row ['gid'];
		}
		return $groups;
		
	}
	
	public static function dbGetGroupInfo($group) {
		$stmt = OC_DB::prepare ( 'SELECT * FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?');
		$result = $stmt->execute(array($group));
		$row = $result->fetchRow();
		return $row;
	}
	
	public static function getGroupInfo($group) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGetGroupInfo($group);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('getGroupInfo', array('gid'=>urlencode($group)),
					false, true, null, 'user_group_admin');
		}
		return $result;
	}

	private static function dbGetGroupOwner($group) {
		$stmt = OC_DB::prepare ( 'SELECT `owner` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?');
		$result = $stmt->execute(array($group));
		$row = $result->fetchRow ();
		$owner = $row ['owner'];
		return $owner;
	}

	public static function getGroupOwner($group) {
		$info = self::getGroupInfo($group);
		return !empty($info)&&isset($info['owner'])?$info['owner']:null;
	}
	
	public static function getGroupUsageCharge($group) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGetGroupUsageCharge($group);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions',
					array('action'=>'getGroupUsageCharge', 'name'=>$group), false, true, null, 'user_group_admin');
		}
		return $result;
	}
	
	private static function dbGetGroupUsageCharge($group) {
		if(!\OCP\App::isEnabled('files_sharding') || !\OCP\App::isEnabled('files_accounting')){
			return 0;
		}
		$sql = 'SELECT * FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ?';
		$stmt = OC_DB::prepare($sql);
		$result = $stmt->execute(array($group));
		$charge = 0;
		while($row = $result->fetchRow()){
			$row = $result->fetchRow();
			$charges = \OCA\Files_Accounting\Storage_Lib::getChargeForUserServers($row['uid']);
			$charge += ((int)$row['files_usage']) * ((int)$charges['charge_home']);
		}
		return $charge;
	}
	
	public static function getGroupUsage($group, $user=null) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGetGroupUsage($group, $user);
		}
		else{
			$arr = array('action'=>'getGroupUsage', 'name'=>$group);
			if(!empty($user)){
				$arr['userid'] = $user;
			}
			$result = \OCA\FilesSharding\Lib::ws('groupActions',
					$arr, false, true, null, 'user_group_admin');
		}
		return $result;
	}
	
	private static function dbGetGroupUsage($group, $user=null) {
		$sql = 'SELECT `files_usage` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ?';
		$arr = array($group);
		if(!empty($user)){
			$sql .= 'AND `uid` = ?';
			$arr[] = $uid;
		}
		$stmt = OC_DB::prepare($sql);
		$result = $stmt->execute($arr);
		$usage = 0;
		while($row = $result->fetchRow()){
			$row = $result->fetchRow();
			$usage += (int)$row['files_usage'];
		}
		return $usage;
	}
	
	public static function updateGroupUsage($user, $group, $usage) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbUpdateGroupUsage($user, $group, $usage);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupActions',
					array('action'=>'updateGroupUsage', 'userid'=>$user, 'name'=>$group, 'usage'=>$usage),
					false, true, null, 'user_group_admin');
		}
		return $result;
	}
	
	public static function dbUpdateGroupUsage($user, $group, $usage) {
		$query = \OCP\DB::prepare ( "UPDATE `*PREFIX*user_group_admin_group_user` SET `files_usage` = ? WHERE `uid` = ? AND `gid` = ?" );
		$result = $query->execute ( array ($usage, $user, $group) );
		return $result;
	}
	
	public static function getGroup($fileId){
		$user = \OC_User::getUser();
		$groups = self::getUserGroups($user);
		$ret = array();
		foreach($groups as $group){
			\OC\Files\Filesystem::tearDown();
			$groupDir = '/'.$user.'/user_group_admin/'.$group['gid'];
			\OC\Files\Filesystem::init($user, $groupDir);
			$path = \OC\Files\Filesystem::getPath($fileId);
			if(!empty($path) && $path!=='files'){
				$ret['group'] = $group['gid'];
				$ret['path'] = $path;
				break; 
			}
		}
		if(count($groups)>0){
			\OC\Files\Filesystem::init($user, '/'.$user.'/files');
		}
		return $ret;
	}
	
}

