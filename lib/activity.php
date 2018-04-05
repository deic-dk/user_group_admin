<?php

use OC\L10N\Factory;
use OCP\Activity\IExtension;
use OCP\Activity\IManager;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Util;
use OCP\IURLGenerator;
use \OCP\User;

class Activity implements IExtension {
	const TYPE_GROUP = 'group';

	protected $l;
	protected $languageFactory;
	protected $URLGenerator;
	protected $activityManager;
	protected $config;
	protected $helper;

	public function __construct(Factory $languageFactory, IURLGenerator $URLGenerator, IManager $activityManager, IConfig $config) {
		$this->languageFactory = $languageFactory;
		$this->URLGenerator = $URLGenerator;
		$this->l = $this->getL10N();
		$this->activityManager = $activityManager;
		$this->config = $config;
	}
	/**
	 * @param string|null $languageCode
	 * @return IL10N
	 */
	protected function getL10N($languageCode = null) {
		return $this->languageFactory->get('user_group_admin', $languageCode);
	}
	/**
	 * The extension can return an array of additional notification types.
	 * If no additional types are to be added false is to be returned
	 *
	 * @param string $languageCode
	 * @return array|false
	 */
	public function getNotificationTypes($languageCode) {
		$l = $this->getL10N($languageCode);
		return [
			self::TYPE_GROUP => (string) $l->t('A group membership has <strong>changed</strong>'),
		];
	}
	/**
	 * For a given method additional types to be displayed in the settings can be returned.
	 * In case no additional types are to be added false is to be returned.
	 *
	 * @param string $method
	 * @return array|false
	 */
	public function getDefaultTypes($method) {
		if ($method === 'stream') {
			$settings = array();
			$settings[] = self::TYPE_GROUP;
			return $settings;
		}
		return false;
	}
	/**
	 * The extension can translate a given message to the requested languages.
	 * If no translation is available false is to be returned.
	 *
	 * @param string $app
	 * @param string $text
	 * @param array $params
	 * @param boolean $stripPath
	 * @param boolean $highlightParams
	 * @param string $languageCode
	 * @return string|false
	 */
	public function translate($app, $text, $params, $stripPath, $highlightParams, $languageCode) {
		if($app !== 'user_group_admin') {
			return false;
		}
		$preparedParams = $this->prepareParameters('user_group_admin',
			$params, $this->getSpecialParameterList('user_group_admin', $text),
			$stripPath, $highlightParams);
		switch($text){
			case 'created_self':
				return (string) $this->l->t('You created the group %1$s', $preparedParams);
			case 'deleted_self':
				return (string) $this->l->t('You deleted the group %1$s', $preparedParams);
			case 'shared_user_self':
				if($params[1]==OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER){
					return (string) $this->l->t('You sent out an invitation to the group %1$s', $preparedParams);
				}
				else{
					return (string) $this->l->t('You invited %2$s to the group %1$s', $preparedParams);
				}
			case 'requested_user_self':
				return (string) $this->l->t("You've requested to join the group %1$s", $preparedParams);
			case 'shared_with_by':
				$group = $params[0];
				$owner = $params[2];
				if(OC_User_Group_Admin_Util::groupIsHiddenOrOpen($group)){
					return (string) $this->l->t("You've been added to the group %1$s", $preparedParams);
				}
				else{
					return (string) $this->l->
						t('You have been invited by %3$s to join the group %1$s', $preparedParams).
						'<div class="invite_div" style="display:none">
							<a href="#" class="accept btn btn-default btn-flat" group="'.$group.'">'.$this->l->t('Accept').'</a>&nbsp
							<a href="#" class="decline btn btn-default btn-flat" group="'.$group.'">'.$this->l->t('Decline').'</a>
						</div>';
				}
			case 'requested_with_by':
				$group = $params[0];
				$user = $params[1];
				$owner = $params[2];
				if(OC_User_Group_Admin_Util::groupIsHiddenOrOpen($group)){
					return (string) $this->l->t("%2$s has been added to the group %1$s", $preparedParams);
				}
				else{
					return (string) $this->l->
						t('%2$s has requested to join the group %1$s', $preparedParams).
						'<div class="invite_div" style="display:none">
							<a href="#" class="accept btn btn-default btn-flat" userdisplayname="'.$user.'" user="'.$user.'" group="'.$group.'">'.$this->l->t('Accept').'</a>&nbsp
							<a href="#" class="decline btn btn-default btn-flat" userdisplayname="'.$user.'" user="'.$user.'" group="'.$group.'">'.$this->l->t('Decline').'</a>
						</div>';
				}
			case 'joined_user_self_external':
				$group = $params[0];
				$user = $params[1];
				return (string) $this->l->
					t('The external user %2$s has been signed up and added to the group %1$s', $preparedParams).
						'<div class="invite_div" style="display:none">
							<a href="#" class="verify accept btn btn-default btn-flat" userdisplayname="'.$user.'" user="'.$user.'" group="'.$group.'">'.$this->l->t('Verify').'</a>&nbsp
						</div>';
			case 'joined_user_self':
				return (string) $this->l->t('%2$s joined the group %1$s', $preparedParams);
			case 'joined_with_by':
				return (string) $this->l->t("You've joined the group %1$s", $preparedParams);
			case 'joined_with_by_external':
				return (string) $this->l->t("You've joined the group %1$s", $preparedParams);
			case 'deleted_by':
				$user = \OCP\User::getUser();
				if($user!=$params[1]){
					return (string) $this->l->t("%2$s left the group %1$s", $preparedParams);
				}
				else{
					return (string) $this->l->t("You left the group %1$s", $preparedParams);
				}
			default:
				return false;
		}
	}
	/**
	 * The extension can define the type of parameters for translation
	 *
	 * Currently known types are:
	 * * file		=> will strip away the path of the file and add a tooltip with it
	 * * username	=> will add the avatar of the user
	 *
	 * @param string $app
	 * @param string $text
	 * @return array|false
	 */
	function getSpecialParameterList($app, $text) {
		if ($app === 'user_group_admin') {
					return [
						0 => 'file',
						1 => 'username',
					];
			}
		return false;
	}

	public function prepareParameters($app, $params, $paramTypes = array(), $stripPath = false, $highlightParams = false) {
		$preparedParams = array();
		foreach ($params as $i => $param) {
			if (is_array($param)) {
				$preparedParams[] = $this->prepareArrayParameter($app, $param, $paramTypes[$i], $stripPath, $highlightParams);
			} else {
				$preparedParams[] = $this->prepareStringParameter($app, $param, isset($paramTypes[$i]) ? $paramTypes[$i] : '', $stripPath, $highlightParams);
			}
		}
		return $preparedParams;
	}
	/**
	 * Prepares a string parameter before we use it in the subject or message
	 *
	 * @param string $param
	 * @param string $paramType Type of parameter, if it needs special handling
	 * @param bool $stripPath Shall we remove the path from the filename
	 * @param bool $highlightParams
	 * @return string
	 */
	public function prepareStringParameter($app, $param, $paramType, $stripPath, $highlightParams) {
		if ($paramType === 'file') {
			return $this->prepareFileParam($app, $param, $stripPath, $highlightParams);
		} else if ($paramType === 'username') {
			return $this->prepareUserParam($app, $param, $highlightParams);
		}
		return $this->prepareParam($app, $param, $highlightParams);
	}
	/**
	 * Prepares an array parameter before we use it in the subject or message
	 *
	 * @param array $params
	 * @param string $paramType Type of parameters, if it needs special handling
	 * @param bool $stripPath Shall we remove the path from the filename
	 * @param bool $highlightParams
	 * @return string
	 */
	public function prepareArrayParameter($app, $params, $paramType, $stripPath, $highlightParams) {
		$parameterList = $plainParameterList = array();
		foreach ($params as $parameter) {
			if ($paramType === 'file') {
				$parameterList[] = (string) $this->prepareFileParam($app, $parameter, $stripPath, $highlightParams);
				$plainParameterList[] = (string) $this->prepareFileParam($app, $parameter, false, false);
			} else {
				$parameterList[] = (string) $this->prepareParam($app, $parameter, $highlightParams);
				$plainParameterList[] = (string) $this->prepareParam($app, $parameter, false);
			}
		}
		return (string) $this->joinParameterList($parameterList, $plainParameterList, $highlightParams);
	}
	/**
	 * Prepares a parameter for usage by adding highlights
	 *
	 * @param string $param
	 * @param bool $highlightParams
	 * @return string
	 */
	protected function prepareParam($app, $param, $highlightParams) {
		if ($highlightParams) {
			return '<strong>' .$param . '</strong>';
		} else {
			return $param;
		}
	}
	/**
	 * Prepares a user name parameter for usage
	 *
	 * Add an avatar to usernames
	 *
	 * @param string $param
	 * @param bool $highlightParams
	 * @return string
	 */
	protected function prepareUserParam($app, $param, $highlightParams) {
		$param = str_replace('<strong>', '', $param);
		$param = str_replace('</strong>', '', $param);
		$param = \OCP\Util::sanitizeHTML($param);
		$displayName = \OCP\User::getDisplayName($param);
		$displayName = \OCP\Util::sanitizeHTML($displayName);
		if ($highlightParams) {
			return '<div class="avatar" data-user="' . $param . '"></div>'
				. '<strong>' . $displayName . '</strong>';
		} else {
			return $displayName;
		}
	}
	/**
	 * Prepares a file parameter for usage
	 *
	 * Removes the path from filenames and adds highlights
	 *
	 * @param string $param
	 * @param bool $stripPath Shall we remove the path from the filename
	 * @param bool $highlightParams
	 * @return string
	 */
	protected function prepareFileParam($app, $param, $stripPath, $highlightParams) {
		if (!$highlightParams) {
			// Don't show name in bell-dropdown. It's anyway shown with icon.
			return "";//$param;
		}
		if ($app === 'user_group_admin') {
			return '<a class="filename" href="/index.php/apps/user_group_admin">' . $param . '</a>';
		}
		$title = ' title="' . $this->l->t('in %s', array(\OCP\Util::sanitizeHTML($path))) . '"';
		return '<a class="filename tooltip" href="/index.php/apps/user_group_admin"' . $title . '>' . \OCP\Util::sanitizeHTML($name) . '</a>';
	}


	/**
	 * Returns a list of grouped parameters
	 *
	 * 2 parameters are joined by "and":
	 * => A and B
	 * Up to 5 parameters are joined by "," and "and":
	 * => A, B, C, D and E
	 * More than 5 parameters are joined by "," and trimmed:
	 * => A, B, C and #n more
	 *
	 * @param array $parameterList
	 * @param array $plainParameterList
	 * @param bool $highlightParams
	 * @return string
	 */
	protected function joinParameterList($parameterList, $plainParameterList, $highlightParams) {
		if (empty($parameterList)) {
			return '';
		}
		$count = sizeof($parameterList);
		$lastItem = array_pop($parameterList);
		if ($count == 1){
			return $lastItem;
		}
		else if ($count == 2)
		{
			$firstItem = array_pop($parameterList);
			return $this->l->t("%s and %s", array($firstItem, $lastItem));
		}
		else if ($count <= 5)
		{
			$list = implode($this->l->t(', '), $parameterList);
			return $this->l->t("%s and %s", array($list, $lastItem));
		}
		$firstParams = array_slice($parameterList, 0, 3);
		$firstList = implode($this->l->t(', '), $firstParams);
		$trimmedParams = array_slice($plainParameterList, 3);
		$trimmedList = implode($this->l->t(', '), $trimmedParams);
		if ($highlightParams) {
			return $this->l->n(
				'%s and <strong class="tooltip" title="%s">%n more</strong>',
				'%s and <strong class="tooltip" title="%s">%n more</strong>',
				$count - 3,
				array($firstList, $trimmedList));
		}
		return $this->l->n("%s and %n more", "%s and %n more", $count - 3, array($firstList));
	}

	/**
	 * A string naming the css class for the icon to be used can be returned.
	 * If no icon is known for the given type false is to be returned.
	 *
	 * @param string $type
	 * @return string|false
	 */
	public function getTypeIcon($type) {
		if ($type == self::TYPE_GROUP) {
			return 'icon-users';}
	}
	/**
	 * The extension can define the parameter grouping by returning the index as integer.
	 * In case no grouping is required false is to be returned.
	 *
	 * @param array $activity
	 * @return integer|false
	 */
	public function getGroupParameter($activity) {
		return false;
	}
	/**
	 * The extension can define additional navigation entries. The array returned has to contain two keys 'top'
	 * and 'apps' which hold arrays with the relevant entries.
	 * If no further entries are to be added false is no be returned.
	 *
	 * @return array|false
	 */
		public function getNavigation() {
			}
	/**
	 * The extension can check if a customer filter (given by a query string like filter=abc) is valid or not.
	 *
	 * @param string $filterValue
	 * @return boolean
	 */
	public function isFilterValid($filterValue) {
		return true;
	}
	/**
	 * The extension can filter the types based on the filter if required.
	 * In case no filter is to be applied false is to be returned unchanged.
	 *
	 * @param array $types
	 * @param string $filter
	 * @return array|false
	 */
	public function filterNotificationTypes($types, $filter) {
		return false;
	}
	/**
	 * For a given filter the extension can specify the sql query conditions including parameters for that query.
	 * In case the extension does not know the filter false is to be returned.
	 * The query condition and the parameters are to be returned as array with two elements.
	 * E.g. return array('`app` = ? and `message` like ?', array('mail', 'ownCloud%'));
	 *
	 * @param string $filter
	 * @return array|false
	 */
	public function getQueryForFilter($filter) {
		return false;
	}
}

