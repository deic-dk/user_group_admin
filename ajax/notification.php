<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


$group = $_POST['group'];
   //ioanna

if($_POST['action'] == "acceptinvitation") {
        $result = OC_User_Group_Admin_Util::acceptInvitation($group, OCP\USER::getUser());
} elseif($_POST['action'] == "declineinvitation") {
        $result = OC_User_Group_Admin_Util::declineInvitation(OCP\USER::getUser(), $group);
}
?>

