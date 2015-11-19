<?php

OC_Util::checkAdminUser();
OCP\JSON::callCheck();

$subject= $_POST['mailsubject']; 
	
if($_POST['action'] == "addsubject") {
        \OCP\Config::setAppValue('user_group_admin', 'subject', $subject);

}
