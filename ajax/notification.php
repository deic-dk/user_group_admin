<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_group_admin');
OCP\JSON::callCheck();


$group = $_POST['group'];

if($_POST['action'] == "acceptinvitation") {
        $result = OC_User_Group_Admin_Util::updateStatus($group, OCP\USER::getUser(), 1);
} elseif($_POST['action'] == "declineinvitation") {
        $result = OC_User_Group_Admin_Util::updateStatus($group, OCP\USER::getUser(), 2);
}
?>

