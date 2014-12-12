<?php

/**
 * ownCloud - user_group_admin
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

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('user_group_admin');

if (isset($_FILES['import_group_file'])) {

    $from    = $_FILES['import_group_file']['tmp_name'];
        
    $content = file_get_contents ( $from ) ;
    $members = unserialize( $content ) ;

    if ( is_array( $members ) ){

        $group  = $members[0] ;
        array_shift($members);

        $result = OC_User_Group_Admin_Util::createGroup( $group ) ;
        if ( $result ) {
            foreach ( $members as $member ) {
                if ( OCP\User::userExists( $member ) and OCP\User::getUser() != $member ){
                    OC_User_Group_Admin_Util::addToGroup( $member , $group ) ;
                }
            }
        }

    }

    header( 'Location: ' . OCP\Util::linkToAbsolute( 'user_group_admin' , 'index.php' ) ) ;

}
