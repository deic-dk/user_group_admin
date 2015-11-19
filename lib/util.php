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
	private static $HIDDEN_GROUP_OWNER = 'hidden_group_owner';
	
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
		$result = $stmt->execute ( array (
				$gid 
		) );
		if ($result->fetchRow ()) {
			return false;
		}
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` = ?" );
		$result = $stmt->execute ( array (
				$gid 
		) );
		if ($result->fetchRow ()) {
			return false;
		}
		
		// Add group and exit
		$stmt = OC_DB::prepare ( "INSERT INTO `*PREFIX*user_group_admin_groups` ( `gid` , `owner` ) VALUES( ? , ? )" );
		$result = $stmt->execute ( array (
				$gid,
				$uid	
		) );
		return $result;
	}

	public static function createGroup($gid, $uid) {

		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = OC_User_Group_Admin_Util::dbCreateGroup($gid, $uid );
			\OCP\Util::writeLog('user_group_admin', 'notenable ', \OC_Log::WARN);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('newGroup', array('userid'=>$uid,
					'name'=>urlencode($gid)), false, true,
					null, 'user_group_admin');
			\OCP\Util::writeLog('user_group_admin', 'ENABLE CREATE GROUP', \OC_Log::WARN);
		}
		return $result ? true : false;
	}

	public static function createHiddenGroup($gid) {
		
		// Check for existence
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?" );
		$result = $stmt->execute ( array (
				$gid 
		) );
		if ($result->fetchRow ()) {
			return false;
		}
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` = ?" );
		$result = $stmt->execute ( array (
			$gid 
		) );
		if ($result->fetchRow ()) {
			return false;
		}
		
		// Add group and exit
		$stmt = OC_DB::prepare ( "INSERT INTO `*PREFIX*user_group_admin_groups` ( `gid` , `owner` ) VALUES( ? , ? )" );
		$result = $stmt->execute ( array (
				$gid,
				OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER 
		) );
		
		return $result ? true : false;
	}
	
	/**
	 * @brief delete a group
	 * 
	 * @param string $gid
	 *        	gid of the group to delete
	 * @return bool Deletes a group and removes it from the group_user-table
	 */
	public static function dbDeleteGroup($gid, $uid) {
		// Delete the group
		$stmt = OC_DB::prepare ( "DELETE FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ? AND `owner` = ?" );
		$stmt->execute ( array (
				$gid,
				$uid 
		) );
		// Delete the group-user relation
		$stmt = OC_DB::prepare ( "DELETE FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `owner` = ?" );
		$stmt->execute ( array (
				$gid,
				$uid 
		) );
		
		return true;
	}

	public static function deleteGroup($gid, $uid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbDeleteGroup($gid, $uid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('deleteGroup', array(
					'name'=>urlencode($gid), 'userid'=>$uid),false, true, null, 'user_group_admin');
		}
		return $result;	
	}
	
	/**
	 * @brief is user in group?
	 * 
	 * @param string $uid
	 *        	uid of the user
	 * @param string $gid
	 *        	gid of the group
	 * @return bool Checks whether the user is member of a group or not.
	 */
	public static function inGroup($uid, $gid) {
		$stmt = OC_DB::prepare ( "SELECT `uid` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? " );
		$result = $stmt->execute ( array (
				$gid,
				$uid 
		) );
		
		return $result->fetchRow () ? true : false;
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
	public static function dbAddToGroup($uid, $gid, $owner) {
		$group = self::searchGroup($gid, $uid);
                if (isset($group)) {
                        $inGroup = true;
                }else {
        		$inGroup = false;
		}          
		// No duplicate entries!
		if (!$inGroup) {
			$accept = md5 ( $uid . time (). 1 );
                        $decline = md5 ($uid . time () . 0);
			$stmt = OC_DB::prepare ( "INSERT INTO `*PREFIX*user_group_admin_group_user` ( `gid`, `uid`, `owner`, `verified`, `accept`, `decline`) VALUES( ?, ?, ?, ?, ?, ?)" );
			if (OC_User_Group_Admin_Util::hiddenGroupExists ( $gid )) {
				$stmt->execute ( array (
						$gid,
						$uid,
						OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER,
						'1',
						'',
						''	
				) );
			} else {
				$stmt->execute ( array (
						$gid,
						$uid,
						$owner,
						'0',
						$accept,
						$decline
				) );
				OC_User_Group_Admin_Util::sendVerification ( $uid, $accept, $decline, $gid, $owner );
			}
			
			return true;
		} else {
			return false;
		}
	}

	public static function addToGroup($uid, $gid, $owner) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
                        $result = self::dbAddToGroup($uid, $gid, $owner);
                }
                else{
                        $result = \OCA\FilesSharding\Lib::ws('newMember', array(
                                        'name'=>urlencode($gid), 'userid'=>$uid, 'owner'=>$owner),false, true, null, 'user_group_admin');
                }
                return true;	
	}
	
	/**
	 * Send invitation mail to a user.
	 */
	public static function sendVerification($uid, $accept, $decline, $gid, $owner) {
		$owner = \OCP\User::getDisplayName($owner);
                $senderAddress = OCP\Config::getAppValue('user_group_admin', 'sender', '');
                $defaults = new \OCP\Defaults();
                $senderName = $defaults->getName();
		$name = OCP\User::getDisplayName($uid);
		$url = OCP\Config::getAppValue('user_group_admin', 'appurl', ''); 
		$subject = OCP\Config::getAppValue('user_group_admin', 'subject', '');
		$message = 'Dear '.$name.','."\n \n".'You have been added to the group "' . $gid . '" by ' . $owner . '. Click here to accept the invitation:'."\n".
		$url . $accept ."\n \n".'or click here to decline:'."\n".
		$url . $decline;
		
		try {
                        \OCP\Util::sendMail(
                                $uid, $name,
                                $subject, $message,
                                $senderAddress, $senderName 
                        );
                } catch (\Exception $e) {
                        \OCP\Util::writeLog('User_Group_Admin', 'A problem occurred while sending the e-mail. Please revisit your settings.', \OCP\Util::ERROR);
                }

	}

	public static function dbSearchGroup($gid, $uid){
		$stmt = OC_DB::prepare ( "SELECT `owner`, `verified`  FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? " );
		$result = $stmt->execute ( array (
				$gid,
				$uid
		) );
                while ( $row = $result->fetchRow () ) {
                        $groupInfo =  array('owner' => $row ["owner"], 'status' => $row["verified"]);
                }
		if (!isset($groupInfo)) {
			return null;
		}else {
			return $groupInfo;
		}

	}

	public static function searchGroup($gid, $uid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
                        $result = self::dbSearchGroup($gid, $uid);
                }
		 else{
                        $result = \OCA\FilesSharding\Lib::ws('searchGroup', array(
                                        'name'=>urlencode($gid), 'userid'=>$uid),false, true, null, 'user_group_admin');
                }
                return $result;	
	}
	
	public static function dbUpdateStatus($gid, $uid, $status) {
		$query = OC_DB::prepare ("UPDATE `*PREFIX*user_group_admin_group_user` SET `verified` = '$status' WHERE `uid` = ? AND `gid` = ? " );
		$result = $query->execute( array (
				$uid,
				$gid
		));
		return $result;
	}
	
	/**
	* Update user status 
	*/		
	public static function updateStatus($gid, $uid, $status) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
                        $result = self::dbUpdateStatus($gid, $uid, $status);
                }
                else{
                        $result = \OCA\FilesSharding\Lib::ws('updateStatus', array(
                                        'name'=>urlencode($gid), 'userid'=>$uid, 'status' => $status),false, true, null, 'user_group_admin');
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
                        $result = \OCA\FilesSharding\Lib::ws('leaveGroup', array(
                                        'name'=>urlencode($gid), 'userid'=>$uid),false, true, null, 'user_group_admin');
                }
                return $result;

	}
        
	public static function dbGetOwnerGroups($owner) {
		$stmt = OC_DB::prepare ( "SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE  `owner` = ?" );
                $result = $stmt->execute ( array (
                               	$owner 
                	) );

                $groups = array ();
                while ( $row = $result->fetchRow () ) {
                        $groups [] = $row ["gid"];
                }

                return $groups;

	}

	public static function getOwnerGroups($owner) {
		if (!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGetOwnerGroups($owner);
		}else{
			//TODO
			$server = \OCA\FilesSharding\Lib::getServerForUser($owner, true);
		 	$result = \OCA\FilesSharding\Lib::ws('getOwnerGroups', Array('owner'=>$owner),
				false, true, null, 'user_group_admin');
		//	$result = array_unique(array_merge($result, $groups));
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
	public static function dbGetUserGroups($uid) {
		$stmt = OC_DB::prepare ( "SELECT `gid`, `verified`, `accept`, `decline` FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ? AND `owner` != ? " );
		$result = $stmt->execute ( array (
				$uid,
				OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER
		) );
		
		$groups = array ();
		while ( $row = $result->fetchRow () ) {
			$groups [] = array('group' => $row ["gid"], 'status' => $row["verified"], 'accept' => $row["accept"], 'decline' => $row["decline"]);
		}
		
		return $groups;
	}

	public static function getUserGroups($userid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGetUserGroups($userid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('getUserGroups', array('userid'=>$userid),
				 false, true, null, 'user_group_admin');
		}
		return $result;
	}
	
	/**
	 * @brief get a list of all groups
	 * 
	 * @param string $search        	
	 * @param int $limit        	
	 * @param int $offset        	
	 * @return array with group names
	 *        
	 *         Returns a list with all groups
	 */
	public static function getGroups($search = '', $limit = null, $offset = null) {
		$stmt = OC_DB::prepare ( 'SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` LIKE ? AND `owner` = ?', $limit, $offset );
		$result = $stmt->execute ( array (
				$search . '%',
				OCP\USER::getUser () 
		) );
		$groups = array ();
		while ( $row = $result->fetchRow () ) {
			$groups [] = $row ['gid'];
		}
		
		return $groups;
	}
	public static function hiddenGroupExists($gid) {
		$query = OC_DB::prepare ( 'SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` = ?' );
		$result = $query->execute ( array (
				$gid
				 
		) )->fetchOne ();
		if ($result) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * check if a group exists
	 * 
	 * @param string $gid        	
	 * @return bool
	 */
	public static function groupExists($gid) {
		$query = OC_DB::prepare ( 'SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ? AND `owner` = ?' );
		$result = $query->execute ( array (
				$gid,
				OCP\USER::getUser () 
		) )->fetchOne ();
		if ($result) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @brief get a list of all users in a group
	 * 
	 * @param string $gid        	
	 * @param string $search        	
	 * @param int $limit        	
	 * @param int $offset        	
	 * @return array with user ids
	 */
	public static function dbUsersInGroup($gid, $search = '', $limit = null, $offset = null) {
                $stmt = OC_DB::prepare ( 'SELECT `uid`, `verified`, `owner` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` LIKE ? AND `owner` != ?', $limit, $offset );
                $result = $stmt->execute ( array (
                                $gid,
                                $search . '%',
                                OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER
                ) );
                $users = array ();
                while ( $row = $result->fetchRow () ) {
                        $users [] = array('uid' => $row ["uid"], 'status' => $row["verified"], 'owner' => $row["owner"]);
                }
                return $users;
        }

        public static function usersInGroup($gid, $search = '', $limit = null, $offset = null) {
                if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
                        $result = self::dbUsersInGroup($gid, $search, $limit, $offset);
                }
                else{
                        $result = \OCA\FilesSharding\Lib::ws('getGroupUsers', array('gid'=>urlencode($gid)),
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

                return '<div class="avatar" data-user="' . $param . '"></div>'. '<strong style="font-size:92%">' . $name . '</strong>';
        }

}

