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
	 	OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'shared', 'shared_user_self', 'shared_by');	
	}
	public static function addNotificationsForGroupAction( $group, $activityType, $subject, $subjectBy) {
		if ($activityType == 'shared') {
			$auser = $group[1];
			$user = OCP\USER::getUser ();
		}else {	
			$user = OCP\USER::getUser ();
			$auser = OCP\USER::getUser ();
			
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
		
		if ($streamSetting) {
			if ($type == 'shared') {
				$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                                $query->execute(array('user_group_admin', $subject, serialize($path), '', none, none, '', $user, $auser, time(), 40, $type));
			}else {
				$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                		$query->execute(array('user_group_admin', $subject, serialize(array($path)), '', none, none, '', $user, $auser, time(), 40, $type));
			}
		}

	}


}



