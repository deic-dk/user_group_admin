<?php

OC_Util::checkAdminUser();
OCP\JSON::callCheck();

$subject= $_POST['mailsubject']; 
$sender = $_POST['mailsender'];
$url = $_POST['accepturl'];
	
if($_POST['action'] == "addsubject") {
        \OCP\Config::setAppValue('user_group_admin', 'subject', $subject);
	\OCP\Config::setAppValue('user_group_admin', 'sender', $sender);
	\OCP\Config::setAppValue('user_group_admin', 'appurl', $url);

}

