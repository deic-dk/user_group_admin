<?php
use \OCP\Util;
//OC::$CLASSPATH['UserSettings'] = OC::$SERVERROOT.'/apps/activity/lib/usersettings.php';
//OC::$CLASSPATH['Data']    = OC::$SERVERROOT.'/apps/activity/lib/data.php';

class OC_User_Group_Hooks {
		public static function register() {
		\OCP\Util::connectHook('OC_Activity', 'post_create', 'OC_User_Group_Hooks', 'groupCreate');
		\OCP\Util::connectHook('OC_Activity', 'delete', 'OC_User_Group_Hooks', 'groupDelete');
		\OCP\Util::connectHook('OC_Activity', 'post_shared', 'OC_User_Group_Hooks', 'groupShare');

		// hooking up the activity manager
		$am = \OC::$server->getActivityManager();
		$am->registerConsumer(function() {
			return new Consumer();
		});
	}

	public static function groupCreate($params) {
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'created_self', 'created_by');		
	}
	public static function groupDelete($params) {
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'deleted_self', 'deleted_by');
	}
	public static function groupShare($group, $uid) {
		$params = array($group, $uid);
	 	OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'shared_user_self', 'shared_with_by');	
	}
	public static function groupLeave($group, $uid) {
		$params = array($group, $uid);
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'deleted_by', 'deleted_by');
	}
	public static function addNotificationsForGroupAction( $group, $activityType, $subject, $subjectBy) {
		if ($subject == 'shared_user_self') {
			$auser = $group[1];
			$user = OCP\USER::getUser ();
		}else {	
			if ($subject == 'deleted_by') {
				$auser = $group[1];
				$user = OCP\USER::getUser ();
			}else {
				$user = OCP\USER::getUser ();
				$auser = OCP\USER::getUser ();
			}
		}
		$userSubject = $subject;
		$filteredStreamUsers = \OCA\Activity\UserSettings::filterUsersBySetting(array($user), 'stream', 'group');
                $filteredEmailUsers = \OCA\Activity\UserSettings::filterUsersBySetting(array($user), 'email', 'group');
		foreach (array($user) as $user) {

		//if (!empty($filteredStreamUsers) && !empty($filteredEmailUsers)) {
			 OC_User_Group_Hooks::addNotificationsForUser(
                                $user, $auser, $userSubject,
                                $group, true,
				!empty($filteredStreamUsers[$user]),
				!empty($filteredEmailUsers[$user]) ? $filteredEmailUsers[$user] : 0,
                                40, $activityType
                        );

		} 	
	}

	protected static function addNotificationsForUser($user, $auser, $subject, $path, $isFile, $streamSetting, $emailSetting, $priority , $type ) {
		$link = ''; 
	        $app = 'user_group_admin';	
		if ($streamSetting) {
			if ($subject == 'shared_user_self') {
				\OCA\Activity\Data::send($app, $subject, $path, '', array(), '', '', $user, $type, 40);
				\OCA\Activity\Data::send($app, 'shared_with_by', array($path[0],$user), '', array(), '', '', $auser, $type, 40); 

			}else if ($subject == 'deleted_self') {
				\OCA\Activity\Data::send($app, $subject, array($path), '', array(), '', '', $auser,$type, 40); 
        	 	}else if ($subject == 'deleted_by'){
				\OCA\Activity\Data::send($app, 'deleted_self', $path, '', array(), '', '', $user, $type, 40);
				\OCA\Activity\Data::send($app, $subject, array($path[0],$user), '', array(), '', '', $auser, $type, 40);   
			}else {
				\OCA\Activity\Data::send($app, $subject, array($path), '', array(), '', '', $auser, $type, 40);
			}
		}
		if ($emailSetting) {
			$latestSend = time() + $emailSetting;
			if ($subject == 'shared_user_self') {
				\OCA\Activity\Data::storeMail($app, $subject, $path, $user, 'group', $latestSend);
				\OCA\Activity\Data::storeMail($app, 'shared_with_by', array($path[0],$user), $auser, 'group', $latestSend);
			}else if ($subject == 'deleted_self'){
				\OCA\Activity\Data::storeMail($app, $subject, array($path), $auser, 'group', $latestSend);	
			}else if ($subject == 'deleted_by'){
				\OCA\Activity\Data::storeMail($app, 'deleted_self', array($path), $user, 'group', $latestSend);
				\OCA\Activity\Data::storeMail($app, $subject, array($path[0],$user), $auser, 'group', $latestSend);
			}else {
				\OCA\Activity\Data::storeMail($app, $subject, array($path), $user, 'group', $latestSend);
			}
		}		 
	}


}

