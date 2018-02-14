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
		if(empty($group) || empty($uid) || empty($owner) ||
				$uid==\OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER){
			return;
		}
		$params = array($group, $uid, $owner);
		if($owner==\OC_User::getUser()){
			self::addNotificationsForGroupAction($params, 'group', 'shared_user_self');
			self::addNotificationsForGroupAction($params, 'group', 'shared_with_by');
		}
		else{
			self::addNotificationsForGroupAction($params, 'group', 'requested_user_self');
			self::addNotificationsForGroupAction($params, 'group', 'requested_with_by');
		}
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
	
	public static function dbGroupJoin($group, $uid, $owner, $externalUser = false) {
		$params = array($group, $uid, $owner);
		if($externalUser){
			self::addNotificationsForGroupAction($params, 'group', 'joined_user_self_external');
			self::addNotificationsForGroupAction($params, 'group', 'joined_with_by_external');
		}
		else{
			self::addNotificationsForGroupAction($params, 'group', 'joined_user_self');
			self::addNotificationsForGroupAction($params, 'group', 'joined_with_by');
		}
	}
	
	public static function groupJoin($group, $uid, $owner, $externalUser = false) {
		if(!\OCP\App::isEnabled('files_sharding')  || \OCA\FilesSharding\Lib::isMaster()){
			self::dbGroupJoin($group, $uid, $owner, $externalUser);
		}
		else{
			\OCA\FilesSharding\Lib::ws('groupJoin', array('group'=>urlencode($group),
					'userid'=>$uid, 'owner'=>$owner, 'externalUser'=>($externalUser?'yes':'no')),
					false, true, null, 'user_group_admin');
		}
	}
	
	public static function groupLeave($group, $uid, $owner) {
		$params = array($group, $uid, $owner);
		self::addNotificationsForGroupAction($params, 'group', 'deleted_by');
	}
	
	public static function addNotificationsForGroupAction($params, $activityType, $subject) {
		\OCP\Util::writeLog('User_Group_Admin', 'Adding notification: '.$subject.'-->'.serialize($params), \OCP\Util::WARN);
		if($subject == 'shared_user_self' || $subject == 'shared_with_by' ||
				$subject == 'joined_user_self' || $subject == 'joined_with_by' ||
				$subject == 'joined_user_self_external' || $subject == 'joined_with_by_external'){
			$affecteduser = $params[1];
			$user = $params[2];
		}
		elseif($subject == 'requested_user_self' || $subject == 'requested_with_by'){
			$affecteduser = $params[2];
			$user = $params[1];
		}
		else{	
			$user = $params[1];
			$affecteduser = $params[1];
		}
		$filteredStreamUsers = \OCA\Activity\UserSettings::filterUsersBySetting(array($user, $affecteduser), 'stream', 'group');
		$filteredEmailUsers = \OCA\Activity\UserSettings::filterUsersBySetting(array($user, $affecteduser), 'email', 'group');
		if($subject == 'shared_with_by' || $subject == 'joined_with_by' ||
				$subject == 'joined_with_by_external' || $subject == 'requested_with_by'){
			self::addNotificationsForUser(
				$user, $affecteduser, $subject, $params, true,
				!empty($filteredStreamUsers[$affecteduser]),
				!empty($filteredEmailUsers[$affecteduser]) ? $filteredEmailUsers[$affecteduser] : 0,
				\OCA\UserNotification\Data::PRIORITY_MEDIUM, $activityType
			);
		}
		else{
			self::addNotificationsForUser(
				$user, $affecteduser, $subject, $params, true,
				!empty($filteredStreamUsers[$user]),
				!empty($filteredEmailUsers[$user]) ? $filteredEmailUsers[$user] : 0,
				\OCA\UserNotification\Data::PRIORITY_MEDIUM, $activityType
			);
		}
	}
	
	protected static function addNotificationsForUser($user, $affecteduser, $subject, $params, $isFile,
			$streamSetting, $emailSetting, $priority , $type) {
		$link = OCP\Util::linkToAbsolute('user_group_admin', '');
		$app = 'user_group_admin';
		if($streamSetting){
			if($subject == 'shared_user_self' || $subject == 'joined_user_self' || $subject == 'joined_user_self_external' || $subject == 'requested_user_self'){
				self::send($app, $subject, $params, '', array(), '', $link, $user, $user, $type, \OCA\UserNotification\Data::PRIORITY_HIGH);
			}
			else if($subject == 'shared_with_by' || $subject == 'joined_with_by' ||
					$subject == 'requested_with_by' || $subject == 'joined_with_by_external') {
				\OCP\Util::writeLog('User_Group_Admin', 'PATH: '.serialize($params), \OCP\Util::WARN);
				self::send($app, $subject, $params, '', array(), '', $link, $user, $affecteduser, $type,
						\OCA\UserNotification\Data::PRIORITY_VERYHIGH); 
			}
			else if($subject == 'deleted_self') {
				self::send($app, $subject, array($params[0]), '', array(), '', $link, $user, $affecteduser,$type, \OCA\UserNotification\Data::PRIORITY_HIGH); 
			}
			else if($subject == 'deleted_by'){
				if($params[1]!==$params[2]){
					self::send($app, $subject, array($params[0], $user, $params[2]), '', array(), '', $link, $user, $params[2], $type, \OCA\UserNotification\Data::PRIORITY_HIGH);
				}
				self::send($app, $subject, array($params[0], $user, $params[2]), '', array(), '', $link, $user, $affecteduser, $type, \OCA\UserNotification\Data::PRIORITY_HIGH);   
			}
			else{
				self::send($app, $subject, array($params[0]), '', array(), '', $link, $user, $affecteduser, $type, \OCA\UserNotification\Data::PRIORITY_HIGH);
			}
		}
		if($emailSetting){
			$latestSend = time() + $emailSetting;
			if($subject == 'shared_user_self' || $subject == 'requested_user_self'){
				\OCA\Activity\Data::storeMail($app, $subject, $params, $user, 'group', $latestSend);
				\OCA\Activity\Data::storeMail($app, 'shared_with_by', array($params[0],$user), $affecteduser, 'group', $latestSend);
			}
			else if($subject == 'deleted_self'){
				\OCA\Activity\Data::storeMail($app, $subject, array($params[0]), $affecteduser, 'group', $latestSend);	
			}
			else if($subject == 'deleted_by'){
				\OCA\Activity\Data::storeMail($app, $subject, array($params[0],$user), $affecteduser, 'group', $latestSend);
			}
			else{
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


