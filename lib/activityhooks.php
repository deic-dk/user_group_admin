<?php
use \OCP\Util;
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
	
	public static function groupCreate($group, $uid) {
		$params = array($group, $uid);
		self::addNotificationsForGroupAction($params, 'group', 'created_self');		
	}
	
	public static function groupDelete($group, $uid) {
		$params = array($group, $uid);
		self::addNotificationsForGroupAction($params, 'group', 'deleted_self');
	}
	
	public static function dbGroupShare($group, $uid, $owner) {
		$params = array($group, $uid, $owner);
		self::addNotificationsForGroupAction($params, 'group', 'shared_user_self');
		self::addNotificationsForGroupAction($params, 'group', 'shared_with_by');
	}
	
	public static function groupShare($group, $uid, $owner) {
		if(!\OCP\App::isEnabled('files_sharding')  || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbGroupShare($group, $uid, $owner);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('groupShare', array('group'=>urlencode($group),
					'userid'=>$uid, 'owner'=>$owner),
					false, true, null, 'user_group_admin');
		}
		return $result;
	}
	
	public static function groupLeave($group, $uid) {
		$params = array($group, $uid);
		self::addNotificationsForGroupAction($params, 'group', 'deleted_by');
	}
	
	public static function addNotificationsForGroupAction($params, $activityType, $subject) {
		if($subject == 'shared_user_self' || $subject == 'shared_with_by'){
			$auser = $params[1];
			$user = $params[2];
		}
		else{	
			$user = $params[1];
			$auser = $params[1];
		}
		$filteredStreamUsers = \OCA\Activity\UserSettings::filterUsersBySetting(array($user, $auser), 'stream', 'group');
		$filteredEmailUsers = \OCA\Activity\UserSettings::filterUsersBySetting(array($user, $auser), 'email', 'group');
		if ($subject == 'shared_with_by') {
			self::addNotificationsForUser(
				$user, $auser, $subject, $params, true,
				!empty($filteredStreamUsers[$auser]),
				!empty($filteredEmailUsers[$auser]) ? $filteredEmailUsers[$auser] : 0,
				\OCA\UserNotification\Data::PRIORITY_MEDIUM, $activityType
			);
		}
		else{
			self::addNotificationsForUser(
				$user, $auser, $subject, $params, true,
				!empty($filteredStreamUsers[$user]),
				!empty($filteredEmailUsers[$user]) ? $filteredEmailUsers[$user] : 0,
				\OCA\UserNotification\Data::PRIORITY_MEDIUM, $activityType
			);
		}
	}
	
	protected static function addNotificationsForUser($user, $auser, $subject, $params, $isFile,
			$streamSetting, $emailSetting, $priority , $type ) {
		$link = "/index.php/apps/user_group_admin"; 
		$app = 'user_group_admin';
		if ($streamSetting) {
			if ($subject == 'shared_user_self') {
				self::send($app, $subject, $params, '', array(), '', $link, $user, $user, $type, \OCA\UserNotification\Data::PRIORITY_HIGH);
			}
			else if ($subject == 'shared_with_by') {
				\OCP\Util::writeLog('User_Group_Admin', 'PATH: '.serialize($params), \OCP\Util::WARN);
				self::send($app, $subject, $params, '', array(), '', $link, $user, $auser, $type, \OCA\UserNotification\Data::PRIORITY_HIGH); 
			}
			else if ($subject == 'deleted_self') {
				self::send($app, $subject, array($params[0]), '', array(), '', $link, $user, $auser,$type, \OCA\UserNotification\Data::PRIORITY_HIGH); 
        	 	}else if ($subject == 'deleted_by'){
				self::send($app, 'deleted_self', array($params[0]), '', array(), '', $link, $user, $user, $type, \OCA\UserNotification\Data::PRIORITY_HIGH);
				//TODO in case we want to notify the other user
				//self::send($app, $subject, array($params[0],$user), '', array(), '', $link, $user, $auser, $type, \OCA\UserNotification\Data::PRIORITY_HIGH);   
			}
			else {
				self::send($app, $subject, array($params[0]), '', array(), '', $link, $user, $auser, $type, \OCA\UserNotification\Data::PRIORITY_HIGH);
			}
		}
		if ($emailSetting) {
			$latestSend = time() + $emailSetting;
			if ($subject == 'shared_user_self') {
				\OCA\Activity\Data::storeMail($app, $subject, $params, $user, 'group', $latestSend);
				\OCA\Activity\Data::storeMail($app, 'shared_with_by', array($params[0],$user), $auser, 'group', $latestSend);
			}
			else if ($subject == 'deleted_self'){
				\OCA\Activity\Data::storeMail($app, $subject, array($params[0]), $auser, 'group', $latestSend);	
			}
			else if ($subject == 'deleted_by'){
				\OCA\Activity\Data::storeMail($app, 'deleted_self', array($params[0]), $user, 'group', $latestSend);
				//\OCA\Activity\Data::storeMail($app, $subject, array($params[0],$user), $auser, 'group', $latestSend);
			}
			else {
				\OCA\Activity\Data::storeMail($app, $subject, array($params[0]), $user, 'group', $latestSend);
			}
		}
	}
	
	public static function send($app, $subject, $subjectparams = array(), $message = '',
			$messageparams = array(), $file = '', $link = '', $user = '', $affecteduser = '', $type = '',
			$prio = \OCA\UserNotification\Data::PRIORITY_MEDIUM) {
		$timestamp = time();
		// store in DB
		$query = \OCP\DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
		$query->execute(array($app, $subject, serialize($subjectparams), $message, serialize($messageparams), $file, '', $user, $affecteduser, $timestamp, $prio, $type));
		// fire a hook so that other apps like notification systems can connect
		\OCP\Util::emitHook('OC_Activity', 'post_event', array('app' => $app, 'subject' => $subject, 'user' => $user, 'affecteduser' => $affecteduser, 'message' => $message, 'file' => $file, 'link'=> '', 'prio' => $prio, 'type' => $type));
		return true;
	}
}


