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
	$l = OC_L10N::get('user_group_admin');
	OC_User_Group_Hooks::register();
	OCP\App::addNavigationEntry(
	    array( 'id'    => 'user_group_admin',
	           'order' => 4,
	           'href'  => OCP\Util::linkTo( 'user_group_admin' , 'index.php' ),
	//           'icon'  => OCP\Util::imagePath( 'user_group_admin', 'nav-icon.png' ),
	           'name'  => $l->t('Groups') )
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
		if(!OC_User_Group_Admin_Util::createGroupFolder($group['gid'])){
			return false;
		}
		if((empty($group['hidden']) || $group['hidden']==='no') && $group['owner']!=$user){
			OC_User_Group_Admin_Util::shareGroupFolder($user, $group['owner'], $group['gid']);
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

if(!isset($_SERVER['REQUEST_URI']) ||
		strpos($_SERVER['REQUEST_URI'], OC::$WEBROOT ."/shared/")!==0 &&
		strpos($_SERVER['REQUEST_URI'], OC::$WEBROOT ."/public/")!==0 &&
		strpos($_SERVER['REQUEST_URI'], OC::$WEBROOT ."/files/")!==0 &&
		strpos($_SERVER['REQUEST_URI'], OC::$WEBROOT ."/remote.php/")!==0 &&
		strpos($_SERVER['REQUEST_URI'], OC::$WEBROOT ."/sharingin/")!==0 &&
		strpos($_SERVER['REQUEST_URI'], OC::$WEBROOT ."/sharingout/")!==0 &&
		strpos($_SERVER['REQUEST_URI'], OC::$WEBROOT ."/groupfolders/")!==0
		){
	OCP\Util::addScript('user_group_admin','setview');
	OCP\Util::addScript('user_group_admin','user_group_notification');
}

}

