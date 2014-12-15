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

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();

if ( isset($_GET['group']) ) {

    $tmpl = new OCP\Template("user_group_admin", "part.member");
    $tmpl->assign( 'group' , $_GET['group'] , false );
    $tmpl->assign( 'members' ,  OC_User_Group_Admin_Util::usersInGroup( $_GET['group'] )  , false );
    $page = $tmpl->fetchPage();

    OCP\JSON::success(array('data' => array( 'members'=> OC_User_Group_Admin_Util::usersInGroup( $_GET['group'] )  ,  'page'=>$page)));

}
