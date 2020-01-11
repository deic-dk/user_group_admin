<?php

/**
 * ownCloud - user_group_admin
 *
 * @author Christian Brinch
 * @copyright 2014 Christian Brinch, DeIC, <christian.brinch@deic.dk>
 *
 * @author Jorge Rafael García Ramos
 * @copyright 2012 Jorge Rafael García Ramos <kadukeitor@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();

if (isset($_GET['search'])) {
	$shareWith = array();
	$count = 0;
	$users = array();
	$limit = 0;
	$offset = 0;
	$max = 12;
	while ($count < $max && count($users) == $limit) {
		$limit = $max - $count;
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$users = OC_User::getDisplayNames($_GET['search'], $limit, $offset);
		}
		else{
			$users = \OCA\FilesSharding\Lib::ws('getDisplayNames', array('search'=>$_GET['search'],
					'limit'=>$limit, 'offset'=>$offset),
					false, true, null, 'files_sharding');
		}
		$offset += $limit;
		foreach($users as $user => $name) {
			if((!isset($_GET['itemShares']) ||
					!is_array($_GET['itemShares'][OCP\Share::SHARE_TYPE_USER]) ||
					!in_array($user, $_GET['itemShares'][OCP\Share::SHARE_TYPE_USER]))) {
				$shareWith[] = array('label' => $user.' ('.$name.')', 'value' => array('shareType' => OCP\Share::SHARE_TYPE_USER, 'shareWith' => $user));
				$count++;
			}
		}
	}
	OC_JSON::success(array('data' => $shareWith));
}

