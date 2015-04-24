<?php
use \OCP\Util;
class OC_User_Group_Hooks {
	public static function groupCreate($params) {
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'file_created', 'created_self', 'created_by');		
	}
	public static function groupDelete($params) {
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'file_deleted', 'deleted_self', 'deleted_by');
	}
	public static function addNotificationsForGroupAction($group, $activityType, $subject, $subjectBy) {

		$user = OCP\USER::getUser ();

				$userSubject = $subject;

			OC_User_Group_Hooks::addNotificationsForUser(
				$user, $userSubject, 
				$group, true,
				true,
				true,
				40, $activityType 
			);
	}

	protected static function addNotificationsForUser($user, $subject, $path, $isFile, $streamSetting, $emailSetting, $priority , $type ) {
		$link = ''; 
		
		if ($streamSetting) {
	//		Data::send('user_group_admin', $subject, none, '', none, $path, $link, $user, $type, $priority);
			$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
                $query->execute(array('user_group_admin', $subject, serialize(array($path)), '', none, $path, '', OCP\USER::getUser (), OCP\USER::getUser (), time(), 40, $type));
		 Util::emitHook('OC_Activity', 'post_event', array('app' => 'files', 'subject' => 'created a group', 'user' => $user, 'affecteduser' => OCP\USER::getUser (), 'message' => 'created', 'file' => $path, 'link'=> $link, 'prio' => 40, 'type' => $type));
		}

	}


}



