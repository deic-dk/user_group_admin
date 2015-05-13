<?php
$groupmembership=$_['group'];
$members = OC_User_Group_Admin_Util::usersInGroup( $groupmembership ) ;
$size = count($members);
                $stmt = OC_DB::prepare( "SELECT `owner` FROM `*PREFIX*user_group_admin_groups` WHERE `gid` = ?" );
                $result = $stmt->execute( array($groupmembership));
                $owners = array();
                while ($row = $result->fetchRow()) {
                        $owners[] = $row['owner'];

                }
                foreach ($owners as $member) {
                        $name = OC_User::getDisplayName($member) ;
                echo "<li data-member=$member><i class=\"fa fa-user\"></i><div class='left'> $name</div>
                        <span ><i>($member)</i></span><span style='position:relative; ' >Owner</span>
                </li><br>" ;
                }
                ////////////////////////
                foreach ($members as $member) {
                        $name = OC_User::getDisplayName($member) ;
                        echo "<br><li data-member=$member title=\"".OC_User::getDisplayName($member)."\"><i class=\"fa fa-user\"></i><div
class='left'>$name</div>
                        <span ><i>($member)</i></span>
                        </li>" ;
                }

