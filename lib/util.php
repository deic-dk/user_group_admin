<?php
require '/usr/local/www/owncloud/3rdparty/phpmailer/phpmailer/PHPMailerAutoload.php';
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

class OC_User_Group_Admin_Util
{

		private static $HIDDEN_GROUP_OWNER = 'hidden_group_owner';

    /**
     * @brief Try to create a new group
     * @param  string $gid The name of the group to create
     * @return bool
     *
     * Tries to create a new group. If the group name already exists, false will
     * be returned.
     */
    public static function createGroup( $gid )
    {
        
        // Check for existence
        $stmt = OC_DB::prepare( "SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?" );
        $result = $stmt->execute( array( $gid ));
        if ( $result->fetchRow() ) { return false; }
        $stmt = OC_DB::prepare( "SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` = ?" );
        $result = $stmt->execute( array( $gid ));
        if ( $result->fetchRow() ) { return false; }

        // Add group and exit
        $stmt = OC_DB::prepare( "INSERT INTO `*PREFIX*user_group_admin_groups` ( `gid` , `owner` ) VALUES( ? , ? )" );
        $result = $stmt->execute( array( $gid , OCP\USER::getUser() ));
        



        return $result ? true : false;

    }

    public static function createHiddenGroup( $gid )
    {
        
        // Check for existence
        $stmt = OC_DB::prepare( "SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?" );
        $result = $stmt->execute( array( $gid ));
        if ( $result->fetchRow() ) { return false; }
        $stmt = OC_DB::prepare( "SELECT `gid` FROM `*PREFIX*groups` WHERE `gid` = ?" );
        $result = $stmt->execute( array( $gid ));
        if ( $result->fetchRow() ) { return false; }

        // Add group and exit
        $stmt = OC_DB::prepare( "INSERT INTO `*PREFIX*user_group_admin_groups` ( `gid` , `owner` ) VALUES( ? , ? )" );
        $result = $stmt->execute( array( $gid , OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER ));





        return $result ? true : false;

    }
    
    /**
     * @brief delete a group
     * @param  string $gid gid of the group to delete
     * @return bool
     *
     * Deletes a group and removes it from the group_user-table
     */
    public static function deleteGroup( $gid )
    {
        // Delete the group
        $stmt = OC_DB::prepare( "DELETE FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ? AND `owner` = ?" );
        $stmt->execute( array( $gid , OCP\USER::getUser() ));
        // Delete the group-user relation
        $stmt = OC_DB::prepare( "DELETE FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `owner` = ?" );
        $stmt->execute( array( $gid , OCP\USER::getUser() ));

        return true;
    }

    /**
     * @brief is user in group?
     * @param  string $uid uid of the user
     * @param  string $gid gid of the group
     * @return bool
     *
     * Checks whether the user is member of a group or not.
     */
    public static function inGroup( $uid, $gid )
    {
    	$stmt = OC_DB::prepare( "SELECT `uid` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? " );
        $result = $stmt->execute( array( $gid, $uid ));

        return $result->fetchRow() ? true : false ;
    }

    /**
     * @brief Add a user to a group
     * @param  string $uid Name of the user to add to group
     * @param  string $gid Name of the group in which add the user
     * @return bool
     *
     * Adds a user to a group.
     */
    public static function addToGroup( $uid, $gid, $owner )
    {
        // No duplicate entries!
        if ( ! OC_User_Group_Admin_Util::inGroup( $uid, $gid ) ) {
            $accept= md5($uid.time());
	    $decline = $uid.time();
            $stmt = OC_DB::prepare( "INSERT INTO `*PREFIX*user_group_admin_group_user` ( `gid`, `uid`, `owner`, `verified`, `accept`, `notification`,`decline` ) VALUES( ?, ?, ?, ?, ?, ?, ?)" );
            if(OC_User_Group_Admin_Util::hiddenGroupExists($gid)){
							$stmt->execute( array( $gid, $uid, OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER, '0', $accept, '0', $decline));
            }else{
							$stmt->execute( array( $gid, $uid, OCP\USER::getUser(), '0', $accept, '0', $decline));
						        OC_User_Group_Admin_Util::sendVerification( $uid, $accept, $decline, $gid, OCP\USER::getDisplayName() );
            }

            return true;
        } else {
            return false;
        }
    }
	//ioanna

    public static function sendVerification($uid, $accept, $decline, $gid, $owner) 
    {
	
    	$to = $uid;
	
	$subject = 'Group Invitation to Data DeiC';
        $message = 'You have been added to the group "'.$gid.'" by '.$owner.'. Click here to accept the invitation:
		https://test.data.deic.dk/index.php/apps/user_group_admin?code='.$accept.'
		 Click here to decline the invitation:
		https://test.data.deic.dk/index.php/apps/user_group_admin?code='.$decline;
       
        $headers = 'From: cloud@data.deic.dk' . "\r\n" .
        'Reply-To: cloud@data.deic.dk' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
         mail($to, $subject, $message, $headers, "-r ".$to);


}

//ioanna

	public static function acceptInvitation($gid, $uid) {
		 $query = OC_DB::prepare("UPDATE `*PREFIX*user_group_admin_group_user` SET `verified` = true WHERE `gid` = ? AND `uid` = ? ");                                           
	 	 $result = $query->execute( array($gid, $uid ));            
   		 return $result;
        }
//ioanna

	public static function declineInvitation($uid, $gid) {
 		$query = OC_DB::prepare("UPDATE `*PREFIX*user_group_admin_group_user` SET `verified` = '2' WHERE `uid` = ? AND `gid` = ? ");
                 $result = $query->execute( array($uid, $gid ));
               

                return $result;
	}
//ioanna
	 public static function searchUser($gid, $uid, $verified )
         {
         	$stmt = OC_DB::prepare( "SELECT `verified` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? AND `verified` = ?  "  );
        	$result = $stmt->execute( array($gid, $uid,  $verified ));

        	return $result->fetchRow() ? true : false ;
  
          }
	public static function acceptedUser($gid, $uid, $verified, $accept, $notification ) {
                $stmt = OC_DB::prepare( "SELECT `accept` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? AND `verified` = ? AND `accept` = ? AND `notification` = ?");
                $result = $stmt->execute( array($gid, $uid,  $verified, $accept, $notification ));

                return $result->fetchRow() ? true : false ;
 
          }

	public static function declinedUser($gid, $uid, $verified, $decline, $notification ) {
                $stmt = OC_DB::prepare( "SELECT `decline` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? AND `verified` = ? AND `decline` = ? AND `notification` = ?" );
                $result = $stmt->execute( array($gid, $uid,  $verified, $decline, $notification ));

                return $result->fetchRow() ? true : false ;

          }
        
	public static function isNotified($gid, $uid, $verified, $notification) {
		 $stmt = OC_DB::prepare( "SELECT `notification` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` = ? AND `verified` = ? AND `notification` = ?" );
                $result = $stmt->execute( array($gid, $uid, $verified, $notification ));

                return $result->fetchRow() ? true : false ;

          }
 

//ioanmna
	public static function Notification($uid, $gid, $accept, $decline) {
		$query = OC_DB::prepare("UPDATE `*PREFIX*user_group_admin_group_user` SET `notification` = true WHERE `gid` = ? AND `uid` = ? AND `accept` = ? OR `decline` = ?  ");
		 $result = $query->execute( array($gid, $uid, $accept, $decline ));
                 return $result;

	} 

    /**
     * @brief Removes a user from a group
     * @param  string $uid Name of the user to remove from group
     * @param  string $gid Name of the group from which remove the user
     * @return bool
     *
     * removes the user from a group.
     */
    public static function removeFromGroup( $uid, $gid )
    {
        $stmt = OC_DB::prepare( "DELETE FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ? AND `gid` = ?" );
        $stmt->execute( array( $uid, $gid));

        return true;
    }

    public static function removeSelf( $uid, $gid )
    {
        $stmt = OC_DB::prepare( "DELETE FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ? AND `gid` = ?" );
        $stmt->execute( array( $uid, $gid));

        return true;
    }
   

    /**
     * @brief Get all groups a user belongs to
     * @param  string $uid Name of the user
     * @return array  with group names
     *
     * This function fetches all groups a user belongs to. It does not check
     * if the user exists at all.
     */
    public static function getUserGroups( $uid )
    {
    	$stmt = OC_DB::prepare( "SELECT `gid` FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ? AND `owner` != ?" );
        $result = $stmt->execute( array( $uid, OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER ));

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
    public static function getGroups($search = '', $limit = null, $offset = null)
    {
        $stmt = OC_DB::prepare('SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` LIKE ? AND `owner` = ?', $limit, $offset);
        $result = $stmt->execute(array($search.'%',OCP\USER::getUser()));
        $groups = array();
        while ($row = $result->fetchRow()) {
            $groups[] = $row['gid'];
        }

        return $groups;
    }

    public static function hiddenGroupExists($gid)
    {
        $query = OC_DB::prepare('SELECT `gid` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ? AND `owner` = ?' );
        $result = $query->execute(array($gid,OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER))->fetchOne();
        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * check if a group exists
     * @param  string $gid
     * @return bool
     */    
    public static function groupExists($gid)
    {
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
    public static function usersInGroup($gid, $search = '', $limit = null, $offset = null)
    {
        $stmt = OC_DB::prepare('SELECT `uid` FROM `*PREFIX*user_group_admin_group_user` WHERE `gid` = ? AND `uid` LIKE ? AND `owner` != ?', $limit, $offset);
        $result = $stmt->execute(array($gid, $search.'%', OC_User_Group_Admin_Util::$HIDDEN_GROUP_OWNER));
        $users = array();
        while ($row = $result->fetchRow()) {
            $users[] = $row['uid'];
        }

        return $users;
    }

}
