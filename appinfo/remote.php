<?php

OCP\App::checkAppEnabled('chooser');
OCP\App::checkAppEnabled('user_group_admin');


if(preg_match("|^".OC::$WEBROOT."/groupfolders/|", $_SERVER['REQUEST_URI'])){
	$_SERVER['BASE_URI'] = OC::$WEBROOT."/groupfolders";
}
elseif(preg_match("|^".OC::$WEBROOT."/remote.php/groupfolders/|", $_SERVER['REQUEST_URI'])){
	$_SERVER['BASE_URI'] = OC::$WEBROOT."/remote.php/groupfolders";
}

$_SERVER['BASE_DIR'] = '/'.$_SERVER['PHP_AUTH_USER'].'/user_group_admin/';

include('chooser/appinfo/remote.php');

