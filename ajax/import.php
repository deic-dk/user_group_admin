<?php
/**
 * ownCloud - user_group_admin
 *
 * @author Christian Brinch
 * @copyright 2014 Christian Brinch, DeIC, <christian.brinch@deic.dk>
 *
 * @author Jorge Rafael Garc\xc3\xada Ramos
 * @copyright 2012 Jorge Rafael Garc\xc3\xada Ramos <kadukeitor@gmail.com>
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
OCP\User::checkLoggedIn ();
OCP\App::checkAppEnabled ( 'user_group_admin' );

$import = true;
if (isset ( $_FILES ['import_group_file'] ['tmp_name'] )) {
	$from = $_FILES ['import_group_file'] ['tmp_name'];
	$content = file_get_contents ( $from );
	$members = file ( $from, FILE_IGNORE_NEW_LINES );
	if (is_array ( $members )) {
		$group = $members [0];
		array_shift ( $members );
		$result = OC_User_Group_Admin_Util::createGroup ( $group );
		if ($result) {
			foreach ( $members as $member ) {
				if (OCP\User::userExists ( $member ) and OCP\User::getUser () != $member) {
					OC_User_Group_Admin_Util::addToGroup ( $member, $group, OCP\USER::getUser () );
				} elseif (OCP\User::userExists ( $member ) == false) {
					$import = false;
				}
			}
		}
	}
}
if ($import) {
	header ( 'Location: ' . OCP\Util::linkToAbsolute ( 'user_group_admin', 'index.php' ) );
} else {
	echo "<script type='text/javascript'>
                          var r = confirm('Some of the users were not added to the group because they do not have an account in Data Deic.');
                        if (r == true) {
                          window.location.href='https://test.data.deic.dk/index.php/apps/user_group_admin';
						} else {
       					  window.location.href='https://test.data.deic.dk/index.php/apps/user_group_admin';
						}
                          </script>";
}
