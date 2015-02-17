<php?>


OCP\JSON::checkLoggedIn();                                                                                                     
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


if (isset($_GET['code'])  {

	switch ($_POST['action']) {
		case "acceptinvitation":	
		$result=OC_User_Group_Admin_Util::acceptInvitation($_GET['code']);
		break;

		case "declineinvitation":
		$result=OC_User_Group_Admin_Util::declineInvitation($_GET['code']);
		break;
	}
}

?>  
