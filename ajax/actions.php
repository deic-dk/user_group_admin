<?php

/**
 * ownCloud - user_group_admin
 *
 * @author Christian Brinch
 * @copyright 2014 Christian Brinch, DeIC, <christian.brinch@deic.dk>
 *  
 * Based on Group Custom app
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

if ( isset($_POST['group']) ) {
  switch ($_POST['action']) {
    case "addgroup":
      $result = OC_User_Group_Admin_Util::createGroup( $_POST['group'], OC_User::getUser() ) ;
      break;
    case "addmember":
      if ( isset($_POST['member'])) $result = OC_User_Group_Admin_Util::addToGroup( $_POST['member'] , $_POST['group'] );
      break;
    case "leavegroup":
      $result = OC_User_Group_Admin_Util::removeFromGroup( OCP\User::getUser() , $_POST['group'] ) ;
      break;
    case "delgroup":
      $result = OC_User_Group_Admin_Util::deleteGroup( $_POST['group'] ) ;
      break;
    case "delmember":
      if ( isset($_POST['member'])) $result = OC_User_Group_Admin_Util::removeFromGroup( $_POST['member'] , $_POST['group'] ) ;
      break;
    case "showmembers":
      $result = true;
      break;
    case "showmemberships":
      $result = true;
      break;
	
  }
  		
  if ($result) {
    switch ($_POST['action']) {
      case "addgroup":
        $tmpl = new OCP\Template("user_group_admin", "part.group");
        $tmpl->assign( 'groups' , OC_User_Group_Admin_Util::getGroups() , true );
        $page = $tmpl->fetchPage();
        OCP\JSON::success(array('data' => array('page'=>$page)));
  	    break;
  	  case "addmember":  
        $tmpl = new OCP\Template("user_group_admin", "members");
        $tmpl->assign( 'group' , $_POST['group'] , false );
        $tmpl->assign( 'members' , OC_User_Group_Admin_Util::usersInGroup( $_POST['group'] ) , false );
        $page = $tmpl->fetchPage();
        OCP\JSON::success(array('data' => array('page'=>$page)));
  	    break;
	case "showmembers":
	$tmpl = new OCP\Template("user_group_admin", "members");
        $tmpl->assign( 'group' , $_POST['group'] , false );
        $tmpl->assign( 'members' , OC_User_Group_Admin_Util::usersInGroup( $_POST['group'] ) , false );
        $page = $tmpl->fetchPage();
        OCP\JSON::success(array('data' => array('page'=>$page)));
            break;
	case "showmemberships":
        $tmpl = new OCP\Template("user_group_admin", "memberships");
        $tmpl->assign( 'group' , $_POST['group'] , false );
        $tmpl->assign( 'members' , OC_User_Group_Admin_Util::usersInGroup( $_POST['group'] ) , false );
        $page = $tmpl->fetchPage();
        OCP\JSON::success(array('data' => array('page'=>$page)));
            break;

  	  default:
  	    OCP\JSON::success();
    }     
  } else {

    switch ($_POST['action']) {
      case "addgroup":
    	OCP\JSON::error(array('data' => array('title'=> 'Add Group'  , 'message' => 'This group name already exists in the database. Please choose another one.' ))) ;
	break;
      case "addmember":
	OCP\JSON::error(array('data' => array('title'=> 'Add Member'  , 'message' => 'Wrong name' ))) ;
	break;
	}
  }
}
