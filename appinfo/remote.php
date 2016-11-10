<?php

OCP\App::checkAppEnabled('chooser');
OCP\App::checkAppEnabled('user_group_admin');

//$group = preg_replace("/^\/remote.php\/groupdirs\/([^\/]*)\/*/", "$1", $_SERVER['REQUEST_URI']);
$groupDir = '/'.$_SERVER['PHP_AUTH_USER'].'/user_group_admin/';
$_SERVER['BASE_DIR'] = $groupDir;
$_SERVER['BASE_URI'] = OC::$WEBROOT."/remote.php/groupdirs";

include('chooser/appinfo/remote.php');

