<?php

OC::$CLASSPATH['OC_User_Group_Admin_Backend'] ='apps/user_group_admin/lib/backend.php';
OC::$CLASSPATH['OC_User_Group_Admin_Util']    ='apps/user_group_admin/lib/util.php';
OC::$CLASSPATH['OC_User_Group_Admin_Hooks']   ='apps/user_group_admin/lib/hooks.php';
OC::$CLASSPATH['OC_User_Group_Hooks']   ='apps/user_group_admin/lib/activityhooks.php';
OC::$CLASSPATH['Hooks'] = 'apps/activity/lib/hooks.php';
OC::$CLASSPATH['Data'] = 'apps/activity/lib/data.php';
OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_User_Group_Admin_Hooks', 'post_deleteUser');
OC_Group::useBackend( new OC_User_Group_Admin_Backend() );
OCP\App::registerAdmin('user_group_admin', 'settings');


OCP\App::addNavigationEntry(
    array( 'id'    => 'user_group_admin',
           'order' => 4,
           'href'  => OCP\Util::linkTo( 'user_group_admin' , 'index.php' ),
           'icon'  => OCP\Util::imagePath( 'user_group_admin', 'nav-icon.png' ),
           'name'  => 'Teams' )
         );

