<?php
$groupmembership=$_['group'];
$members = OC_User_Group_Admin_Util::usersInGroup( $groupmembership ) ;
foreach ($members as $member) {
        $owner = $member["owner"];
        break;
}
$name = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner><i class=\"fa fa-user\"></i><span class='left'> $name </span>
      <span class='normaltext'><i>($owner) </i></span><span class='ownertext' >Owner</span>
      </li>" ;
foreach ($members as $member) {
        $uid = $member["uid"];
        $name = OC_User_Group_Admin_Util::prepareUser($uid);
        echo "<br><li data-member=$uid title=\"".OC_User::getDisplayName($uid)."\"><i class=\"fa fa-user\"></i><span
class='left'>$name </span>
               <span class='normaltext'><i>($uid)</i></span>
               </li>" ;
}

