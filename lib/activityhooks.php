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
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'created_self', 'created_by');		
	}
	public static function groupDelete($group, $uid) {
		$params = array($group, $uid);
		OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'deleted_self', 'deleted_by');
	}
	public static function dbGroupShare($group, $uid, $owner) {
		$params = array($group, $uid, $owner);
                OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'shared_user_self', 'shared_with_by');
	}
	public static function groupShare($group, $uid, $owner) {
                if(!\OCP\App::isEnabled('files_sharding')){
                        $result = self::dbGroupShare($group, $uid, $owner);
                }else {
                	$server = \OCA\FilesSharding\Lib::getServerForUser($uid, true);
                	$result = \OCA\FilesSharding\Lib::ws('groupShare', array('group'=>urlencode($group), 'userid'=>$uid, 'owner'=>$owner),
                                 false, true, $server, 'user_group_admin');
		}
                return $result;
        }
	public static function groupLeave($group, $uid, $owner) {
		$params = array($group, $uid, $owner);
                OC_User_Group_Hooks::addNotificationsForGroupAction($params, 'group', 'deleted_by', 'deleted_by');
	}
	public static function addNotificationsForGroupAction( $group, $activityType, $subject, $subjectBy) {
		if ($subject == 'shared_user_self') {
			$auser = $group[1];
			$user = $group[2];
		}else {	
			$user = $group[1];
			$auser = $group[1];
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
		$link = "/index.php/apps/user_group_admin"; 
	        $app = 'user_group_admin';	
		if ($streamSetting) {
			if ($subject == 'shared_user_self') {
				self::send($app, $subject, $path, '', array(), '', $link, $user, $user, $type, 40);
				self::send($app, 'shared_with_by', array($path[0],$user), '', array(), '', $link, $user, $auser, $type, 40); 

			}else if ($subject == 'deleted_self') {
				self::send($app, $subject, array($path[0]), '', array(), '', $link, $user, $auser,$type, 40); 
        	 	}else if ($subject == 'deleted_by'){
				self::send($app, 'deleted_self', array($path[0]), '', array(), '', $link, $user, $user, $type, 40);
				//TODO in case we want to notify the other user
				//self::send($app, $subject, array($path[0],$user), '', array(), '', $link, $user, $auser, $type, 40);   
			}else {
				self::send($app, $subject, array($path[0]), '', array(), '', $link, $user, $auser, $type, 40);
			}
		}
		if ($emailSetting) {
			$latestSend = time() + $emailSetting;
			if ($subject == 'shared_user_self') {
				\OCA\Activity\Data::storeMail($app, $subject, $path, $user, 'group', $latestSend);
				\OCA\Activity\Data::storeMail($app, 'shared_with_by', array($path[0],$user), $auser, 'group', $latestSend);
			}else if ($subject == 'deleted_self'){
				\OCA\Activity\Data::storeMail($app, $subject, array($path[0]), $auser, 'group', $latestSend);	
			}else if ($subject == 'deleted_by'){
				\OCA\Activity\Data::storeMail($app, 'deleted_self', array($path[0]), $user, 'group', $latestSend);
				//\OCA\Activity\Data::storeMail($app, $subject, array($path[0],$user), $auser, 'group', $latestSend);
			}else {
				\OCA\Activity\Data::storeMail($app, $subject, array($path[0]), $user, 'group', $latestSend);
			}
		}		 
	}

	public static function send($app, $subject, $subjectparams = array(), $message = '', $messageparams = array(), $file = '', $link = '', $user = '', $affecteduser = '', $type = '', $prio = Data::PRIORITY_MEDIUM) {
		$timestamp = time();

		// store in DB
		$query = \OCP\DB::prepare('INSERT INTO `*PREFIX*activity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
		$query->execute(array($app, $subject, serialize($subjectparams), $message, serialize($messageparams), $file, '', $user, $affecteduser, $timestamp, $prio, $type));

		// fire a hook so that other apps like notification systems can connect
		\OCP\Util::emitHook('OC_Activity', 'post_event', array('app' => $app, 'subject' => $subject, 'user' => $user, 'affecteduser' => $affecteduser, 'message' => $message, 'file' => $file, 'link'=> $link, 'prio' => $prio, 'type' => $type));

		return true;
	}




}


