<?php

if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!='/' &&
		strpos($_SERVER['REQUEST_URI'], "/js/")===false){

OC::$CLASSPATH['OC_User_Group_Admin_Backend'] ='apps/user_group_admin/lib/backend.php';
OC::$CLASSPATH['OC_User_Group_Admin_Util']    ='apps/user_group_admin/lib/util.php';
OC::$CLASSPATH['OC_User_Group_Admin_Hooks']   ='apps/user_group_admin/lib/hooks.php';
OC::$CLASSPATH['OC_User_Group_Hooks']   ='apps/user_group_admin/lib/activityhooks.php';
OC::$CLASSPATH['Hooks'] = 'apps/activity/lib/hooks.php';
OC::$CLASSPATH['Data'] = 'apps/activity/lib/data.php';
OC::$CLASSPATH['Activity']   ='apps/user_group_admin/lib/activity.php';

OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_User_Group_Admin_Hooks', 'post_deleteUser');
OC_Group::useBackend( new OC_User_Group_Admin_Backend() );
OCP\App::registerAdmin('user_group_admin', 'settings');

OC_User_Group_Hooks::register();
OCP\App::addNavigationEntry(
    array( 'id'    => 'user_group_admin',
           'order' => 4,
           'href'  => OCP\Util::linkTo( 'user_group_admin' , 'index.php' ),
//           'icon'  => OCP\Util::imagePath( 'user_group_admin', 'nav-icon.png' ),
           'name'  => 'Groups' )
         );

\OC::$server->getActivityManager()->registerExtension(function() {
	return new Activity(
		\OC::$server->query('L10NFactory'),
		\OC::$server->getURLGenerator(),
		\OC::$server->getActivityManager(),
		\OC::$server->getConfig()
	);
});

$user = \OCP\User::getUser();
$groups = OC_User_Group_Admin_Util::getUserGroups($user, true, false, true);
$order = 2;

foreach ($groups as $group){
	$fs = \OCP\Files::getStorage('user_group_admin');
	if(!$fs){
		\OCP\Util::writeLog('User_Group_Admin', 'Could not add navigation entry for '.$group['gid'], \OCP\Util::ERROR);
		break;
	}
	$dir = \OC\Files\Filesystem::normalizePath('/'.$group['gid']);
	$path = $fs->getLocalFile($dir);
	$parent = dirname($path);
	if(!file_exists($parent)){
		\OCP\Util::writeLog('User_Group_Admin', 'Creating group folder '.$parent, \OCP\Util::WARN);
		mkdir($parent, 0777, false);
	}
	if(!file_exists($path)){
		\OCP\Util::writeLog('User_Group_Admin', 'Creating group folder '.$path, \OCP\Util::WARN);
		mkdir($path, 0777, false);
	}
	$order += 1./100;
	\OCP\Util::writeLog('User_Group_Admin', 'Adding navigation entry '.$group['gid'].':'.$order, \OCP\Util::DEBUG);
	\OCA\Files\App::getNavigationManager()->add(
			array(
					"id" => 'user-groups_'.$group['gid'],
					"appname" => 'user_group_admin',
					"script" => 'list.php',
					"order" =>  $order,
					"name" => $group['gid'],
			)
	);
}

OCP\Util::addScript('user_group_admin','setview');
OCP\Util::addScript('user_group_admin','user_group_notification');

}
