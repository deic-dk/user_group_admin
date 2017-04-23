<?php

OC_Util::checkAdminUser();
OCP\JSON::callCheck();

$freequota= $_POST['freequota']; 
$subject= $_POST['mailsubject']; 
$sender = $_POST['mailsender'];

if($_POST['action'] == "addsubject") {
        \OCP\Config::setAppValue('user_group_admin', 'subject', $subject);
	\OCP\Config::setAppValue('user_group_admin', 'sender', $sender);

}
if($_POST['action'] == "setgroupdefaultfreequota") {
	\OCP\Config::setAppValue('user_group_admin', 'default_group_freequota', $freequota);
}
