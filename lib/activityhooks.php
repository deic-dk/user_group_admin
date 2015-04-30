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
		
		if ($streamSetting) {
			if ($type == 'shared') {
				$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                                $query->execute(array('user_group_admin', $subject, serialize($path), '', none, $path[0], '', $user, $user, time(), 40, $type));
				$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                                $query->execute(array('user_group_admin', 'shared_with_by', serialize(array($path[0],$user)), '', none, $path[0], '', $user, $auser, time(), 40, $type));
			}else if ($type == 'file_deleted'){
				if ($subject == 'deleted_self') {
					$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                                	$query->execute(array('user_group_admin', $subject, serialize(array($path)), '', none, none, '', $user, $auser, time(), 40, $type));
                        	}else {
					$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                                	$query->execute(array('user_group_admin', 'deleted_self', serialize($path), '', none, none, '', $user, $user, time(), 40, $type));
					$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                                	$query->execute(array('user_group_admin', $subject, serialize(array($path[0],$user)), '', none, $path[0], '', $user, $auser, time(), 40, $type));
				}
 
			}else {
				$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                		$query->execute(array('user_group_admin', $subject, serialize(array($path)), '', none, none, '', $user, $auser, time(), 40, $type));
			}
		}

	}


}



