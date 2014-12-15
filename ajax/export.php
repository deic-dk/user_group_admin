<?php

/**
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

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('user_group_admin');

$group = isset($_GET['group']) ? $_GET['group'] : null;

if ( isset($group) ) {
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: inline; filename=' . str_replace(' ', '_', $group) . '.ocg');

    $members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
    $data    = array_merge( array($group) , $members ) ;

    echo serialize($data) ;

}

