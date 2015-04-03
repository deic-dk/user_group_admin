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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class OC_User_Group_Admin_Backend extends OC_Group_Backend
{

		private static $HIDDEN_GROUP_OWNER = 'hidden_group_owner';

    /**
     * @brief is user in group?
     * @param  string $uid uid of the user
     * @param  string $gid gid of the group
     * @return bool
     *
     * Checks whether the user is member of a group or not.
     */
    public function inGroup( $uid, $gid )
    {
    		// Not necessary to ask master, as a user is added to all groups set in his session, by files_sharding/filesessionhandler.php
        // check
        $stmt = OC_DB::prepare( "SELECT `uid` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? AND `owner` = ?" );
        $result = $stmt->execute( array( $gid, $uid , OCP\USER::getUser() ));
        
        return $result->fetchRow() ? true : false ;
    }

    /**
     * @brief Get all groups a user belongs to
     * @param  string $uid Name of the user
     * @return array  with group names
     *
     * This function fetches all groups a user belongs to. It does not check
     * if the user exists at all.
     */
    public function getUserGroups( $uid )
    {
    
    		// Not necessary to ask master, as a user is added to all groups set in his session, by files_sharding/filesessionhandler.php
    		
        // No magic!
        $stmt = OC_DB::prepare( "SELECT `gid` FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ?" );
        $result = $stmt->execute( array( $uid ));

        $groups = array();
        while ( $row = $result->fetchRow()) {
            $groups[] = $row["gid"];
        }

        return $groups;
    }

    /**
     * @brief get a list of all groups
     * @param  string $search
     * @param  int    $limit
     * @param  int    $offset
     * @return array  with group names
     *
     * Returns a list with all groups
     */
    public function getGroups($search = '', $limit = null, $offset = null)
    {
    
				if(\OCP\App::isEnabled('files_sharding') && !OCA\FilesSharding\Lib::isMaster()){
					$groups = \OCA\FilesSharding\Lib::ws('getGroups', array('search' => $_GET['search']));
					return $groups;
				}
    
        $stmt = OC_DB::prepare('SELECT `gid` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` LIKE ? AND (`owner` = ? OR `uid` = ? AND `owner` != ?)  GROUP BY `gid`', $limit, $offset);
        $user = OCP\USER::getUser();
        $result = $stmt->execute(array($search.'%', $user, $user, self::$HIDDEN_GROUP_OWNER));
        $groups = array();
        while ($row = $result->fetchRow()) {
					if(!in_array($row['gid'], $groups)){
						$groups[] = $row['gid'];
					}
        }

        return $groups;
    }

    /**
     * check if a group exists
     * @param  string $gid
     * @return bool
     */
    public function groupExists($gid)
    {
	    	if(\OCP\App::isEnabled('files_sharding') && !OCA\FilesSharding\Lib::isMaster()){
	    		$groupExists = \OCA\FilesSharding\Lib::ws('groupExists', array('group_id' => $gid));
	    		return $groupExists;
	    	}
	    	
        $query = OC_DB::prepare('SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ? AND `owner` = ?' );
        $result = $query->execute(array($gid,OCP\USER::getUser()))->fetchOne();
        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * @brief get a list of all users in a group
     * @param  string $gid
     * @param  string $search
     * @param  int    $limit
     * @param  int    $offset
     * @return array  with user ids
     */
    public function usersInGroup($gid, $search = '', $limit = null, $offset = null)
    {
    		if(\OCP\App::isEnabled('files_sharding') && !OCA\FilesSharding\Lib::isMaster()){
					$groups = \OCA\FilesSharding\Lib::ws('usersInGroup', array('group_id' => $gid, 'search' => $search,
							'limit' => $limit, 'offset' => $offset));
					return $groups;
				}

				$stmt = OC_DB::prepare('SELECT `uid` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` LIKE ?', $limit, $offset);
        $result = $stmt->execute(array($gid, $search.'%'));
        $users = array();
        while ($row = $result->fetchRow()) {
            $users[] = $row['uid'];
        }

        return $users;
    }

}
