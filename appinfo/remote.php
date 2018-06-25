<?php

OCP\App::checkAppEnabled('chooser');
OCP\App::checkAppEnabled('user_group_admin');

$group = "";

if(preg_match("|^".OC::$WEBROOT."/group/|", $_SERVER['REQUEST_URI'])){
	$group = preg_replace("|^".OC::$WEBROOT."/group/|", "", $_SERVER['REQUEST_URI']);
	$_SERVER['BASE_URI'] = OC::$WEBROOT."/group";
}
elseif(preg_match("|^".OC::$WEBROOT."/remote.php/group/|", $_SERVER['REQUEST_URI'])){
	$group = preg_replace("|^".OC::$WEBROOT."/remote.php/group/|", "", $_SERVER['REQUEST_URI']);
	$_SERVER['BASE_URI'] = OC::$WEBROOT."/remote.php/group";
}

OC_Log::write('chooser','Group dir access: '.$group.':'.\OCP\USER::getUser(), OC_Log::WARN);

$group = preg_replace("|/.*$|", "", $group);
$group = urldecode($group);

$groupDir = '/'.$_SERVER['PHP_AUTH_USER'].'/user_group_admin/'.$group;

$_SERVER['BASE_DIR'] = $groupDir;

include('chooser/appinfo/remote.php');

