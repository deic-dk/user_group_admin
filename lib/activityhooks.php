<?php
use \OCP\Util;
class OC_User_Group_Hooks {
	 
	public static function groupCreate($params) {
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'file_created', 'created_self', 'created_by');		
	}
	public static function groupDelete($params) {
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'file_deleted', 'deleted_self', 'deleted_by');
	}
	public static function groupShare($group, $uid) {
		$params = array($group, $uid);
	 	OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'shared', 'shared_user_self', 'shared_with_by');	
	}
	public static function groupLeave($group, $uid) {
		$params = array($group, $uid);
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'file_deleted', 'deleted_by', 'deleted_by');
	}
	public static function addNotificationsForGroupAction( $group, $activityType, $subject, $subjectBy) {
		if ($activityType == 'shared') {
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

			OC_User_Group_Hooks::addNotificationsForUser(
				$user, $auser, $userSubject, 
				$group, true,
				true,
				true,
				40, $activityType 
			);
	}

	protected static function addNotificationsForUser($user, $auser, $subject, $path, $isFile, $streamSetting, $emailSetting, $priority , $type ) {
		$link = ''; 
	        $app = 'user_group_admin';	
		if ($streamSetting) {
			if ($type == 'shared') {
				OC_User_Group_Hooks::addData($app, $subject, $path, $user, $user, time(), 40, $type);
				OC_User_Group_Hooks::addData($app, 'shared_with_by', array($path[0],$user), $user, $auser, time(), 40, $type);	

			}else if ($type == 'file_deleted'){
				if ($subject == 'deleted_self') {
					OC_User_Group_Hooks::addData($app, $subject, array($path), $user, $auser, time(), 40, $type);
        		       	}else {
					OC_User_Group_Hooks::addData($app, 'deleted_self', $path, $user, $user, time(), 40, $type);
					OC_User_Group_Hooks::addData($app, $subject, array($path[0],$user), $user, $auser, time(), 40, $type);
				} 
			}else {
				OC_User_Group_Hooks::addData($app, $subject, array($path), $user, $auser, time(), 40, $type);

			}
		}

	}
	public static function addData($app, $subject, $path, $user, $auser, $time, $priority, $type){
		$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                $query->execute(array($app, $subject, serialize($path), '', none, none, '', $user, $auser, $time, $priority, $type));

	}


}



