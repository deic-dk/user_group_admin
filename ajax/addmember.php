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

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();

$l = OC_L10N::get('user_group_admin');

if ( isset($_POST['member']) and isset($_POST['group']) ) {

    $result = OC_User_Group_Admin_Util::addToGroup( $_POST['member'] , $_POST['group'] ) ;

    if ($result) {

        $tmpl = new OCP\Template("user_group_admin", "part.member");
        $tmpl->assign( 'group' , $_POST['group'] , false );
        $tmpl->assign( 'members' , OC_User_Group_Admin_Util::usersInGroup( $_POST['group'] ) , false );
        $page = $tmpl->fetchPage();

        OCP\JSON::success(array('data' => array('page'=>$page)));

    } else {

        OCP\JSON::error(array('data' => array('title'=> $l->t('Add Member') , 'message' => 'error' ))) ;

    }

}
