<div class="title-group"><?php echo "<img src=" . OCP\Util::imagePath( 'group_custom', 'group_edit.png' ) . ">" . $_['group'] ; ?></div>


<?php
if (OC_Group_Custom_Local::inGroup(OC_User::getUser() , $_['group'] ) ){
?> 
  
  <div id="block-members">
    <div class="title"><strong><?php echo $l->t('Owner')  ; ?></strong></div>
    <ul class="group members">
    <?php
      $stmt = OC_DB::prepare( "SELECT `owner` FROM `*PREFIX*groups_custom` WHERE `gid` = ?" ); 
      $result = $stmt->execute( array('Test group'));
      $owners = array();                                                                                                           
      while ($row = $result->fetchRow()) {                                                                                         
        $owners[] = $row['owner'];                                                                                                 
      }   
      foreach ($owners as $member) {
        echo "<li data-member=$member><img src=" . OCP\Util::imagePath( 'group_custom', 'user.png' ) . ">$member
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
                echo "<li data-member=$member><img src=" . OCP\Util::imagePath( 'group_custom', 'user.png' ) . ">$member
                                </li>" ;
            }
        
            // patch ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ( OCP\App::isEnabled('group_virtual') and OC_Group::inGroup(OC_User::getUser(),'admin') ){
                if ( OC_Group_Virtual::groupExists( $_['group'] ) ){
                    $members = OC_Group_Virtual::usersInGroup( $_['group'] ) ;
                    foreach ($members as $member) {
                        echo "<li data-member=$member><img src=" . OCP\Util::imagePath( 'group_custom', 'user.png' ) . ">$member</li>" ;
                    }
                }
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ?>

    </ul>
</div>

  
<?php

} else { ?>

<input type="text" id="mkgroup" placeholder="<?php echo $l->t('Member to add') ; ?>" />

<div id="block-members">
    <div class="title"><strong><?php echo $l->t('Members')  ; ?></strong></div>
    <ul class="group members">

        <?php
            
            $members = $_['members'] ;
            foreach ($members as $member) {
                echo "<li data-member=$member><img src=" . OCP\Util::imagePath( 'group_custom', 'user.png' ) . ">$member
                <span class=\"member-actions\">
                    <a href=# class='action remove member' original-title=" . $l->t('Remove') . "><img class='svg action remove member' title=Quit src=" . OCP\Util::imagePath( 'core', 'actions/delete.png' ) . "></a>
                </span>
                </li>" ;
            }
        
            // patch ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ( OCP\App::isEnabled('group_virtual') and OC_Group::inGroup(OC_User::getUser(),'admin') ){
                if ( OC_Group_Virtual::groupExists( $_['group'] ) ){
                    $members = OC_Group_Virtual::usersInGroup( $_['group'] ) ;
                    foreach ($members as $member) {
                        echo "<li data-member=$member><img src=" . OCP\Util::imagePath( 'group_custom', 'user.png' ) . ">$member</li>" ;
                    }
                }
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ?>

    </ul>
</div>

<?php } ?>

