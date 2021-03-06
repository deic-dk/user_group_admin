<?php

OCP\JSON::checkLoggedIn();
\OC::$session->close();
$l = OC_L10N::get('files');

// Load the files
$gid = isset($_GET['gid']) ? $_GET['gid'] : '';
$dir = isset($_GET['dir']) ? $_GET['dir'] : '';

$data = array();

try {
	$data['directory'] = $dir;
	$data['gid'] = $gid;
	$dir = \OC\Files\Filesystem::normalizePath('/'.$gid.$dir);
	$fs = \OCP\Files::getStorage('user_group_admin');
	\OCP\Util::writeLog('User_Group_Admin', 'DIR: '.$dir, \OCP\Util::INFO);
	$dirInfo = $fs->getFileInfo($dir);
	if (!$dirInfo || !$dirInfo->getType() === 'dir') {
		header("HTTP/1.0 404 Not Found");
		OCP\JSON::error(array('data' => $data));
		exit();
	}
	

	$permissions = $dirInfo->getPermissions();

	$sortAttribute = isset($_GET['sort']) ? $_GET['sort'] : 'name';
	$sortDirection = isset($_GET['sortdirection']) ? ($_GET['sortdirection'] === 'desc') : false;

	// make filelist

	//$files = \OCA\Files\Helper::getFiles($dir, $sortAttribute, $sortDirection);
	$content = $fs->getDirectoryContent($dir);
	$files = \OCA\Files\Helper::sortFiles($content, $sortAttribute, $sortDirection);
	$data['directory'] = $dir;
	$data['gid'] = $gid;
	$data['files'] = \OCA\Files\Helper::formatFileInfos($files);
	$data['permissions'] = $permissions;

	OCP\JSON::success(array('data' => $data));
} catch (\OCP\Files\StorageNotAvailableException $e) {
	OCP\JSON::error(array(
		'data' => array(
			'exception' => '\OCP\Files\StorageNotAvailableException',
			'message' => $l->t('Storage not available')
		)
	));
} catch (\OCP\Files\StorageInvalidException $e) {
	OCP\JSON::error(array(
		'data' => array(
			'exception' => '\OCP\Files\StorageInvalidException',
			'message' => $l->t('Storage invalid')
		)
	));
} catch (\Exception $e) {
	OCP\JSON::error(array(
		'data' => array(
			'exception' => '\Exception',
			'message' => $l->t('Unknown error')
		)
	));
}
