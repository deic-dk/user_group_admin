<?php


OCP\JSON::checkLoggedIn();                                                                                                     
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


$groups = OC_User_Group_Admin_Util::getUserGroups(OC_User::getUser());
   //ioanna
        foreach ($groups as $group) {

            $verified = OC_User_Group_Admin_Util::searchUser($group, OC_User::getUser(), '0' );
            if ( $group != 'dtu.dk' && $verified  ) {


		if($_POST['action'] == "acceptinvitation") {
			$result = OC_User_Group_Admin_Util::acceptInvitation($group, OCP\USER::getUser());
        		echo "ok";
		} elseif($_POST['action'] == "declineinvitation") {
			$result = OC_User_Group_Admin_Util::declineInvitation(OCP\USER::getUser(), $group);
        		echo "not ok"; 		
		}
	}

   }
?>  
