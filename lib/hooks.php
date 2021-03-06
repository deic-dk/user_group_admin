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

class OC_User_Group_Admin_Hooks
{

    public static function post_deleteUser( $parameters )
    {
        $uid = $parameters['uid'] ;

        // Delete the group
        $stmt = OC_DB::prepare( "DELETE FROM `*PREFIX*user_group_admin_groups` WHERE `owner` = ?" );
        $stmt->execute( array($uid) );

        // Delete the group-user relation
        $stmt = OC_DB::prepare( "DELETE FROM `*PREFIX*user_group_admin_group_user` WHERE `uid` = ?" );
        $stmt->execute( array($uid) );
        // TODO: If user owns groups, change ownership.

        return true ;

    }

}
