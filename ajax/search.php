<?php


OCP\JSON::checkLoggedIn();                                                                                                     
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


if($_POST['action'] == "acceptinvitation") {
	$result = OC_User_Group_Admin_Util::acceptInvitation();
        echo "ok";
} elseif($_POST['action'] == "declineinvitation") {
	$result = OC_User_Group_Admin_Util::declineInvitation($_GET['code']);
        echo "not ok"; 		
	}

?>  
