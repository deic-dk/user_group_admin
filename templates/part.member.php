<div class="title-group"><?php echo "<i class=\"fa fa-users\"></i>" . $_['group'] ; ?></div>


<?php
if (OC_User_Group_Admin_Util::inGroup(OC_User::getUser() , $_['group'] ) ){
?> 
  
  <div id="block-members">
    <div class="title"><strong><?php echo $l->t('Owner')  ; ?></strong></div>
    <ul class="group members">
    <?php
      $stmt = OC_DB::prepare( "SELECT `owner` FROM `*PREFIX*user_group_admin` WHERE `gid` = ?" ); 
      $result = $stmt->execute( array($_['group']));
      $owners = array();                                                                                                           
      while ($row = $result->fetchRow()) {                                                                                         
        $owners[] = $row['owner'];                                                                                                 
      }   
      foreach ($owners as $member) {
        echo "<li data-member=$member><i class=\"fa fa-user\"></i> $member
              </li>" ;
      }
     ?>
     </ul>
  </div>




  <div id="block-members">
    <div class="title"><strong><?php echo $l->t('Members')  ; ?></strong></div>
    <ul class="group members">

        <?php
            
            $members = $_['members'] ;
            foreach ($members as $member) {
                echo "<li data-member=$member><i class=\"fa fa-user\"></i>$member
                                </li>" ;
            }
        
            // patch ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ( OCP\App::isEnabled('group_virtual') and OC_Group::inGroup(OC_User::getUser(),'admin') ){
                if ( OC_Group_Virtual::groupExists( $_['group'] ) ){
                    $members = OC_Group_Virtual::usersInGroup( $_['group'] ) ;
                    foreach ($members as $member) {
                        echo "<li data-member=$member><img src=" . OCP\Util::imagePath( 'user_group_admin', 'user.png' ) . ">$member</li>" ;
                    }
                }
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ?>

    </ul>
</div>

  
<?php

} else { ?>


<div id="block-members">
    <div class="title"><strong><?php echo $l->t('Members')  ; ?></strong></div>
    <ul class="group members">

        <?php
            
            $members = $_['members'] ;
            foreach ($members as $member) {
                echo "<li data-member=$member><i class=\"fa fa-user\"></i>$member
                <span class=\"member-actions\">
                    <a href=# class='action remove member' original-title=" . $l->t('Remove') . "><i class=\"fa fa-times\"></i></a>
                </span>
                </li>" ;
            }
        
            // patch ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ( OCP\App::isEnabled('group_virtual') and OC_Group::inGroup(OC_User::getUser(),'admin') ){
                if ( OC_Group_Virtual::groupExists( $_['group'] ) ){
                    $members = OC_Group_Virtual::usersInGroup( $_['group'] ) ;
                    foreach ($members as $member) {
                        echo "<li data-member=$member><img src=" . OCP\Util::imagePath( 'user_group_admin', 'user.png' ) . ">$member</li>" ;
                    }
                }
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ?>

      <li id="mkgroup_li"><i class="fa fa-user"></i><input class="groupinput" id="mkgroup" placeholder="Add member" val=""></li>
      <li id="add_member" class="add">+</li>

    </ul>
</div>

<?php } ?>

