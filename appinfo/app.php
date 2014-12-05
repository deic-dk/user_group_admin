<?php

OC::$CLASSPATH['OC_Group_Custom']       ='apps/user_groupadmin/lib/group_custom.php';
OC::$CLASSPATH['OC_Group_Custom_Local'] ='apps/user_groupadmin/lib/group_custom_local.php';
OC::$CLASSPATH['OC_Group_Custom_Hooks'] ='apps/user_groupadmin/lib/hooks.php';

OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_Group_Custom_Hooks', 'post_deleteUser');
OC_Group::useBackend( new OC_Group_Custom() );

OCP\Util::addScript('user_groupadmin','script');
OCP\Util::addStyle ('user_groupadmin','style');


OCP\App::addNavigationEntry(
    array( 'id'    => 'user_groupadmin',
           'order' => 4,
           'href'  => OCP\Util::linkTo( 'user_groupadmin' , 'index.php' ),
           'icon'  => OCP\Util::imagePath( 'user_groupadmin', 'nav-icon.png' ),
           'name'  => 'My Groups' )
         );

