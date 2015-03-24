<?php
$group=$_['group'];
$members = OC_User_Group_Admin_Util::usersInGroup( $group ) ;
$size = count($members); ?>
                <strong style='float:left;'>Members</strong><br>
<?php    foreach ($members as $member) {
                $groupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '1');
                $notgroupmembers = OC_User_Group_Admin_Util::searchUser($group, $member, '0');
                if($groupmembers){
                         $status = '';
               } elseif ($notgroupmembers) {
                         $status = 'Pending...';
               } else {
                        $status = 'Member declined the invitation';
                }
                $name = OC_User::getDisplayName($member) ;
                echo "<li data-member=$member title=\"".OC_User::getDisplayName($member)."\" ><i class=\"fa fa-user\"></i><div style='float:left;'>$name</div>
                <span class=\"member-actions\" style='float:right'>
                    <a href=# class='removemember' original-title=" . $l->t('Remove') . "><i class=\"icon icon-trash\"></i></a>
                </span>
                <span style='float:right'><i>($member)</i></span><br>
                <div style='float:right'><i>$status</i></div>
                </li><br>" ;
}  ?>




